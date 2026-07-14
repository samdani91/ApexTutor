<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\GuardianProfile;
use App\Models\OtpCode;
use App\Models\ReferralEarning;
use App\Models\TutorProfile;
use App\Models\User;
use App\Notifications\AdminNewUserRegisteredNotification;
use App\Notifications\AdminReferralCodeUsedNotification;
use App\Notifications\ReferralBonusEarnedNotification;
use App\Services\BulkSmsBdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => ['required', 'string', 'max:150', 'regex:/^[A-Za-z\s.]+$/'],
            'email'           => 'required|email|max:255',
            'phone'           => ['required', 'digits:11'],
            'password'        => ['required', 'confirmed', 'min:6'],
            'role'            => 'required|in:tutor,guardian,student',
            'referral_code'   => 'nullable|string|max:20',
        ], [
            'name.regex'  => 'Name can only contain letters, spaces, and dots.',
            'phone.digits' => 'Phone number must be exactly 11 digits.',
        ]);

        $referrer = null;
        if ($request->filled('referral_code')) {
            $referrer = $this->resolveReferrer($request->referral_code);
            if (!$referrer) {
                throw ValidationException::withMessages(['referral_code' => ['Invalid referral code.']]);
            }
        }

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

        // Layer 1: if the email already exists but was never verified, refresh the
        // account details and resend the OTP instead of rejecting with a 422.
        $existingUnverified = User::where('email', $request->email)
            ->whereNull('email_verified_at')
            ->first();

        if ($existingUnverified) {
            // Only block if the requested phone belongs to a *verified* user that isn't this record.
            $phoneConflict = User::where('phone', $request->phone)
                ->where('id', '!=', $existingUnverified->id)
                ->whereNotNull('email_verified_at')
                ->exists();

            if ($phoneConflict) {
                throw ValidationException::withMessages(['phone' => ['The phone number is already registered.']]);
            }

            $previousRole = $existingUnverified->role;

            DB::transaction(function () use ($request, $existingUnverified, $previousRole, $referrer) {
                $existingUnverified->update([
                    'name'     => $request->name,
                    'phone'    => $request->phone,
                    'password' => Hash::make($request->password),
                    'role'     => $request->role,
                ]);

                if ($referrer && !$existingUnverified->referred_by) {
                    $existingUnverified->update(['referred_by' => $referrer->id]);
                }

                // Swap the profile record when the user changes role on retry.
                if ($previousRole !== $request->role) {
                    if ($previousRole === 'tutor') {
                        $existingUnverified->tutorProfile()?->forceDelete();
                    } else {
                        $existingUnverified->guardianProfile()?->forceDelete();
                    }

                    if ($request->role === 'tutor') {
                        TutorProfile::create(['user_id' => $existingUnverified->id]);
                    } else {
                        GuardianProfile::create(['user_id' => $existingUnverified->id, 'account_type' => $request->role]);
                    }
                }
            });

            $this->sendEmailOtp($existingUnverified->email, 'email_verification');

            return response()->json([
                'success' => true,
                'data'    => [
                    'pending_verification' => true,
                    'email'                => $this->maskEmail($existingUnverified->email),
                ],
                'message' => 'A new verification code has been sent to your email.',
            ]);
        }

        // Now enforce uniqueness against verified accounts only
        $request->validate([
            'email' => 'unique:users,email',
            'phone' => 'unique:users,phone',
        ]);

        $user = DB::transaction(function () use ($request, $referrer) {
            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'role'        => $request->role,
                'referred_by' => $referrer?->id,
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

        $user->email_verified_at = now();
        $user->save();

        if ($user->referred_by) {
            $this->awardReferralBonus($user);
        }

        // Notify all admins — email + platform notification
        $admins = User::where('role', 'super_admin')->get();
        $notification = new AdminNewUserRegisteredNotification(
            userName:  $user->name,
            userEmail: $user->email,
            userRole:  $user->role,
            userId:    $user->id,
        );
        foreach ($admins as $admin) {
            $admin->notify($notification);
        }

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

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->whereNotNull('email_verified_at')
            ->first();

        // Always return the same success shape to avoid email enumeration
        if (!$user) {
            return response()->json([
                'success' => true,
                'data'    => ['email' => $this->maskEmail($request->email)],
                'message' => 'If that email is registered, a reset code has been sent.',
            ]);
        }

        $this->sendEmailOtp($user->email, 'password_reset');

        return response()->json([
            'success' => true,
            'data'    => ['email' => $this->maskEmail($user->email)],
            'message' => 'A password reset code has been sent to your email.',
        ]);
    }

    public function verifyResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $rateLimitKey = 'reset_otp_verify:' . $request->email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $otp = OtpCode::where('email', $request->email)
            ->where('code', hash('sha256', $request->code))
            ->where('purpose', 'password_reset')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            RateLimiter::hit($rateLimitKey, 600);
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        RateLimiter::clear($rateLimitKey);

        return response()->json(['success' => true, 'message' => 'Code verified.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'                 => 'required|email',
            'code'                  => 'required|string|size:6',
            'password'              => ['required', 'confirmed', 'min:6'],
            'password_confirmation' => 'required|string',
        ]);

        $rateLimitKey = 'reset_password:' . $request->email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $otp = OtpCode::where('email', $request->email)
            ->where('code', hash('sha256', $request->code))
            ->where('purpose', 'password_reset')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            RateLimiter::hit($rateLimitKey, 600);
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        RateLimiter::clear($rateLimitKey);
        $otp->update(['used_at' => now()]);
        $user->update(['password' => Hash::make($data['password'])]);
        $user->tokens()->delete();

        return response()->json(['success' => true, 'message' => 'Password reset successfully. Please log in with your new password.']);
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

    private function resolveReferrer(string $code): ?User
    {
        $code = strtoupper(trim($code));

        return User::where(function ($q) use ($code) {
            $q->whereHas('tutorProfile', fn ($tp) => $tp->where('tutor_id', 'TUT-' . $code))
              ->orWhereHas('guardianProfile', fn ($gp) => $gp->where('guardian_id', 'GRD-' . $code));
        })->whereNotNull('email_verified_at')->where('is_active', true)->first();
    }

    private function awardReferralBonus(User $user): void
    {
        $earning = DB::transaction(function () use ($user) {
            $earning = ReferralEarning::firstOrCreate(
                ['referred_user_id' => $user->id],
                ['referrer_id' => $user->referred_by, 'points' => (int) config('referral.signup_bonus_points', 5)]
            );

            if ($earning->wasRecentlyCreated) {
                User::where('id', $earning->referrer_id)->increment('referral_points', $earning->points);
            }

            return $earning;
        });

        if (!$earning->wasRecentlyCreated) {
            return;
        }

        // Notifications and SMS run after the transaction commits
        $referrer = User::find($earning->referrer_id);
        if (!$referrer) {
            return;
        }

        $referrer->notify(new ReferralBonusEarnedNotification($earning->points, $user->name));
        $this->smsReferralBonus($referrer, $user->name, $earning->points);

        $adminNotification = new AdminReferralCodeUsedNotification(
            referrerName:     $referrer->name,
            referredUserName: $user->name,
            referralCode:     $referrer->referral_code ?? '',
        );
        User::where('role', 'super_admin')->get()->each(fn ($admin) => $admin->notify($adminNotification));
    }

    private function smsReferralBonus(User $referrer, string $referredUserName, int $points): void
    {
        try {
            if (!$referrer->phone) return;

            $message = "Good news! {$referredUserName} joined Apex Tutor using your referral code. You earned {$points} points.";
            app(BulkSmsBdService::class)->send($referrer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Referral bonus SMS failed', ['referrer_id' => $referrer->id, 'error' => $e->getMessage()]);
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
