<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone'   => ['required', 'string', 'max:20', 'regex:/^(\+88)?01[3-9]\d{8}$/'],
            'purpose' => 'required|in:registration,login,password_reset',
        ]);

        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'phone'      => $request->phone,
            'code'       => hash('sha256', $code),
            'purpose'    => $request->purpose,
            'expires_at' => now()->addMinutes(10),
        ]);

        // TODO: integrate SMS provider (e.g. Twilio, bKash SMS) to send $code to $request->phone
        return response()->json(['success' => true, 'message' => 'OTP sent.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'phone'   => ['required', 'string', 'max:20', 'regex:/^(\+88)?01[3-9]\d{8}$/'],
            'code'    => 'required|string|digits:6',
            'purpose' => 'required|in:registration,login,password_reset',
        ]);

        // Per-phone brute-force protection: max 5 failed attempts per 10 minutes
        $rateLimitKey = 'otp_verify:' . $request->phone;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $otp = OtpCode::where('phone', $request->phone)
            ->where('code', hash('sha256', $request->code))
            ->where('purpose', $request->purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            RateLimiter::hit($rateLimitKey, 600); // 10-minute window
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        RateLimiter::clear($rateLimitKey);
        $otp->update(['used_at' => now()]);

        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $user->update(['phone_verified_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified.']);
    }
}
