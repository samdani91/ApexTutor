<?php
namespace App\Http\Controllers;

use App\Models\ReferralEarning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // "Earned" tracks whether the bonus actually paid out (i.e. the referred
        // tutor cleared admin verification) — email-verified alone no longer
        // means the referrer got points, so reporting that would mislead.
        $earnedIds = ReferralEarning::where('referrer_id', $user->id)
            ->pluck('referred_user_id')
            ->flip();

        $referrals = $user->referrals()
            ->select('id', 'name', 'role', 'created_at')
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'name'      => $u->name,
                'role'      => $u->role,
                'earned'    => $earnedIds->has($u->id),
                'joined_at' => $u->created_at,
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
