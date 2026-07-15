<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorProfile;
use App\Notifications\TutorVerificationApprovedNotification;
use App\Notifications\TutorVerificationRejectedNotification;
use App\Services\ReferralBonusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminVerificationController extends Controller
{
    public function __construct(private readonly ReferralBonusService $referralBonus) {}

    public function queue(Request $request): JsonResponse
    {
        $request->validate([
            'sort' => 'nullable|in:date_desc,date_asc,id_asc,id_desc',
        ]);

        $query = TutorProfile::with([
            'user:id,name,email',
            'documents',
            'tuitionPreference.subjects:id,name',
            'tuitionPreference.days',
            'tuitionPreference.locations',
        ])
        ->where('verification_status', 'pending')
        ->when($request->search, function ($q, $search) use ($request) {
            $by = $request->get('search_by', 'name');
            $q->where(function ($inner) use ($search, $by) {
                if ($by === 'id') {
                    $inner->where('tutor_id', 'like', "%{$search}%");
                } elseif ($by === 'email') {
                    $inner->whereHas('user', fn($uq) => $uq->where('email', 'like', "%{$search}%"));
                } else {
                    $inner->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                }
            });
        });

        match ($request->sort) {
            'date_asc' => $query->oldest(),
            'id_asc'   => $query->orderBy('tutor_id', 'asc'),
            'id_desc'  => $query->orderBy('tutor_id', 'desc'),
            default    => $query->latest(),
        };

        return response()->json(['success' => true, 'data' => $query->paginate($request->integer('per_page', 10))]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $tutor = TutorProfile::findOrFail($id);

        if ($tutor->verification_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Tutor is not in pending state.'], 422);
        }

        DB::transaction(function () use ($tutor, $request) {
            $tutor->update([
                'verification_status' => 'approved',
                'is_verified'         => true,
                'verified_at'         => now(),
                'verified_by'         => $request->user()->id,
                'status'              => 'active',
            ]);
            $tutor->documents()->update([
                'review_status' => 'approved',
                'reviewed_by'   => $request->user()->id,
                'reviewed_at'   => now(),
            ]);
        });

        // Notification outside transaction — email failure must not roll back approval
        try {
            if ($tutor->user) {
                $tutor->user->notify(new TutorVerificationApprovedNotification());
            }
        } catch (\Exception $e) {
            Log::error('Tutor verification approved notification failed', ['error' => $e->getMessage(), 'tutor' => $id]);
        }

        // Award the referral bonus only now that a human has vetted this account.
        if ($tutor->user) {
            $this->referralBonus->awardForSignup($tutor->user);
        }

        return response()->json(['success' => true, 'message' => 'Tutor approved.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data  = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $tutor = TutorProfile::findOrFail($id);

        if ($tutor->verification_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Tutor is not in pending state.'], 422);
        }

        $tutor->update([
            'verification_status' => 'rejected',
            'is_verified'         => false,
            'rejection_reason'    => $data['rejection_reason'],
        ]);

        try {
            if ($tutor->user) {
                $tutor->user->notify(new TutorVerificationRejectedNotification(
                    reason: $data['rejection_reason'],
                ));
            }
        } catch (\Exception $e) {
            Log::error('Tutor verification rejected notification failed', ['error' => $e->getMessage(), 'tutor' => $id]);
        }

        return response()->json(['success' => true, 'message' => 'Tutor rejected.']);
    }
}
