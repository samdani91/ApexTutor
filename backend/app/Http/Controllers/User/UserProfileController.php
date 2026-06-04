<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);
        // Email changes require OTP re-verification — use requestEmailChange instead
        $user->update($data);
        return response()->json(['success' => true, 'data' => $user->fresh(), 'message' => 'Profile updated.']);
    }

    /**
     * Step 1: Send OTP to new email address.
     */
    public function requestEmailChange(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate(['email' => 'required|email|max:255|unique:users,email,' . $user->id]);

        if ($request->email === $user->email) {
            return response()->json(['success' => false, 'message' => 'This is already your current email.'], 422);
        }

        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'email'      => $request->email,
            'code'       => hash('sha256', $code),
            'purpose'    => 'email_change',
            'expires_at' => now()->addMinutes(15),
        ]);
        $user->update(['pending_email' => $request->email]);

        try {
            Mail::to($request->email)->send(new OtpMail($code, 'email_verification'));
        } catch (\Exception $e) {
            Log::error('Email change OTP failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to send verification email.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Verification code sent to your new email address.']);
    }

    /**
     * Step 2: Verify OTP and apply the new email.
     */
    public function confirmEmailChange(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate(['code' => 'required|string|digits:6']);

        if (!$user->pending_email) {
            return response()->json(['success' => false, 'message' => 'No pending email change found.'], 422);
        }

        $rateLimitKey = 'otp_email_change:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $otp = OtpCode::where('email', $user->pending_email)
            ->where('code', hash('sha256', $request->code))
            ->where('purpose', 'email_change')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            RateLimiter::hit($rateLimitKey, 600);
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        RateLimiter::clear($rateLimitKey);
        $otp->update(['used_at' => now()]);
        $user->update(['email' => $user->pending_email, 'pending_email' => null]);

        return response()->json(['success' => true, 'data' => $user->fresh(), 'message' => 'Email updated successfully.']);
    }

    /**
     * Step 1: Validate current password and send OTP to email.
     */
    public function requestPasswordChange(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'current_password' => ['required', 'string', function ($attr, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Current password is incorrect.');
                }
            }],
        ]);

        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'email'      => $user->email,
            'code'       => hash('sha256', $code),
            'purpose'    => 'password_change',
            'expires_at' => now()->addMinutes(10),
        ]);
        try {
            Mail::to($user->email)->send(new OtpMail($code, 'password_change'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP mail failed', ['email' => $user->email, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to send verification email. Please try again.'], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'pending_verification' => true,
                'email'                => $this->maskEmail($user->email),
            ],
            'message' => 'Verification code sent to your email.',
        ]);
    }

    /**
     * Step 2: Verify OTP and change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'current_password' => ['required', 'string', function ($attr, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Current password is incorrect.');
                }
            }],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'otp_code'         => 'required|string|size:6',
        ]);

        $rateLimitKey = 'otp_change_password:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $otp = OtpCode::where('email', $user->email)
            ->where('code', hash('sha256', $request->otp_code))
            ->where('purpose', 'password_change')
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
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete(); // Revoke all sessions for security

        return response()->json(['success' => true, 'message' => 'Password changed. Please log in again.']);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = min(2, strlen($local));
        $masked  = substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible));
        return $masked . '@' . $domain;
    }
}
