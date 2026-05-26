<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:20', 'purpose' => 'required|in:registration,login,password_reset']);
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'phone' => $request->phone,
            'code' => $code,
            'purpose' => $request->purpose,
            'expires_at' => now()->addMinutes(10),
        ]);
        // TODO: integrate SMS provider
        return response()->json(['success' => true, 'message' => 'OTP sent.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required', 'code' => 'required', 'purpose' => 'required']);
        $otp = OtpCode::where('phone', $request->phone)
            ->where('code', $request->code)
            ->where('purpose', $request->purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
        if (!$otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }
        $otp->update(['used_at' => now()]);
        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $user->update(['phone_verified_at' => now()]);
        }
        return response()->json(['success' => true, 'message' => 'OTP verified.']);
    }
}
