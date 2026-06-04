<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\GuardianProfile;
use App\Models\OtpCode;
use App\Models\TutorProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'email'           => 'required|email|max:255',
            'phone'           => ['required', 'string', 'max:20', 'regex:/^(\+88)?01[3-9]\d{8}$/'],
            'password'        => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role'            => 'required|in:tutor,guardian,student',
        ]);

        // Clean up stale unverified accounts older than 7 days so the user can re-register
        User::whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDays(7))
            ->where(fn($q) => $q->where('email', $request->email)->orWhere('phone', $request->phone))
            ->each(function ($stale) {
                $stale->tutorProfile()?->forceDelete();
                $stale->guardianProfile()?->forceDelete();
                $stale->tokens()->delete();
                $stale->forceDelete();
            });

        // Now enforce uniqueness against verified accounts only
        $request->validate([
            'email' => 'unique:users,email',
            'phone' => 'unique:users,phone',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
            ]);

            if ($user->isTutor()) {
                TutorProfile::create(['user_id' => $user->id]);
            } else {
                GuardianProfile::create(['user_id' => $user->id, 'account_type' => $user->role]);
            }

            return $user;
        });

        // Outside transaction — a mail failure should not roll back user creation
        $this->sendEmailOtp($user->email, 'email_verification');

        return response()->json([
            'success' => true,
            'data'    => [
                'pending_verification' => true,
                'email'                => $this->maskEmail($user->email),
            ],
            'message' => 'Account created. Please verify your email to continue.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        if (!$user->is_active) {
            return response()->json([
                'success'   => false,
                'suspended' => true,
                'message'   => 'Account suspended.',
            ], 403);
        }
        // Revoke all previous sessions before issuing a new one
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'success' => true,
            'data'    => ['user' => $user],
            'message' => 'Login successful.',
        ])->cookie('auth_token', $token, 60 * 24 * 7, '/', null, config('session.secure', false), true, false, 'Lax');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $rateLimitKey = 'otp_verify_email:' . $request->email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $otp = OtpCode::where('email', $request->email)
            ->where('code', hash('sha256', $request->code))
            ->where('purpose', 'email_verification')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            RateLimiter::hit($rateLimitKey, 600);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        RateLimiter::clear($rateLimitKey);
        $otp->update(['used_at' => now()]);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user->update(['email_verified_at' => now()]);
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => ['user' => $user->fresh()],
            'message' => 'Email verified. Welcome!',
        ])->cookie('auth_token', $token, 60 * 24 * 7, '/', null, config('session.secure', false), true, false, 'Lax');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_verified_at) {
            return response()->json(['success' => false, 'message' => 'Email already verified.'], 422);
        }

        $this->sendEmailOtp($user->email, 'email_verification');
        return response()->json(['success' => true, 'message' => 'Verification code resent.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out.'])
            ->withoutCookie('auth_token');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->user()]);
    }

    private function sendEmailOtp(string $email, string $purpose): void
    {
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'email'      => $email,
            'code'       => hash('sha256', $code),
            'purpose'    => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);
        try {
            Mail::to($email)->send(new OtpMail($code, $purpose));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP mail failed', ['email' => $email, 'error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to send verification email. Please try again.');
        }
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = min(2, strlen($local));
        $masked  = substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible));
        return $masked . '@' . $domain;
    }
}
