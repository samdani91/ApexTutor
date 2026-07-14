<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReferralController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q'        => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = User::whereIn('role', ['tutor', 'guardian', 'student'])
            ->withCount('referrals')
            ->with(['tutorProfile:id,user_id,tutor_id', 'guardianProfile:id,user_id,guardian_id'])
            ->orderByDesc('referral_points');

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', $q)
                   ->orWhere('email', 'like', $q)
                   ->orWhereHas('tutorProfile', fn ($tp) => $tp->where('tutor_id', 'like', $q))
                   ->orWhereHas('guardianProfile', fn ($gp) => $gp->where('guardian_id', 'like', $q));
            });
        }

        $users = $query->paginate($request->integer('per_page', 15));

        $users->getCollection()->transform(fn ($u) => [
            'id'               => $u->id,
            'name'             => $u->name,
            'role'             => $u->role,
            'referral_code'    => $u->referral_code,
            'referral_points'  => $u->referral_points,
            'referrals_count'  => $u->referrals_count,
        ]);

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function show(int $userId): JsonResponse
    {
        $user = User::with(['tutorProfile:id,user_id,tutor_id', 'guardianProfile:id,user_id,guardian_id'])
            ->findOrFail($userId);

        $referrals = $user->referrals()
            ->select('id', 'name', 'role', 'email_verified_at', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'name'            => $user->name,
                'referral_code'   => $user->referral_code,
                'referral_points' => $user->referral_points,
                'referrals'       => $referrals,
            ],
        ]);
    }
}
