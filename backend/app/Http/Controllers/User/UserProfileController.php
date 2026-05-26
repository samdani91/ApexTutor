<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);
        $user->update($data);
        return response()->json(['success' => true, 'data' => $user->fresh(), 'message' => 'Profile updated.']);
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
        $user->update(['password' => bcrypt($request->password)]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = min(2, strlen($local));
        $masked  = substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible));
        return $masked . '@' . $domain;
    }
}
