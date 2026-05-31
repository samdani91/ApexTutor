<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            'code'       => $code,
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

        $otp = OtpCode::where('email', $user->pending_email)
            ->where('code', $request->code)
            ->where('purpose', 'email_change')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

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
            'captcha_token' => 'required|string',
        ]);

        $this->verifyCaptcha($request->captcha_token, $request->ip());

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'email'      => $user->email,
            'code'       => $code,
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

        $otp = OtpCode::where('email', $user->email)
            ->where('code', $request->otp_code)
            ->where('purpose', 'password_change')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        $otp->update(['used_at' => now()]);
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete(); // Revoke all sessions for security

        return response()->json(['success' => true, 'message' => 'Password changed. Please log in again.']);
    }

    private function verifyCaptcha(string $token, string $ip): void
    {
        $secret = config('services.recaptcha.secret');
        if (empty($secret)) {
            Log::warning('reCAPTCHA secret not configured — skipping verification.');
            return;
        }

        $result    = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ])->json();

        $threshold = (float) config('services.recaptcha.threshold', 0.5);

        if (!($result['success'] ?? false) || ($result['score'] ?? 0) < $threshold) {
            Log::info('reCAPTCHA failed', [
                'score'  => $result['score']   ?? 0,
                'errors' => $result['error-codes'] ?? [],
                'ip'     => $ip,
            ]);
            throw ValidationException::withMessages([
                'captcha_token' => ['Security verification failed. Please refresh the page and try again.'],
            ]);
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
