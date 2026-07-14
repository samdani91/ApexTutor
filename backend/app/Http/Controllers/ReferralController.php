<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $referrals = $user->referrals()
            ->select('id', 'name', 'role', 'email_verified_at', 'created_at')
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'name'       => $u->name,
                'role'       => $u->role,
                'verified'   => (bool) $u->email_verified_at,
                'joined_at'  => $u->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'referral_code'   => $user->referral_code,
                'referral_points' => $user->referral_points,
                'referrals'       => $referrals,
            ],
        ]);
    }
}
