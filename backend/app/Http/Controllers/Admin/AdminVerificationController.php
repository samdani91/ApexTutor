<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorProfile;
use App\Notifications\TutorVerificationApprovedNotification;
use App\Notifications\TutorVerificationRejectedNotification;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminVerificationController extends Controller
{
    use LogsAdminActivity;

    public function queue(Request $request): JsonResponse
    {
        $queue = TutorProfile::with([
            'user:id,name,email',
            'documents',
            'tuitionPreference.subjects:id,name',
            'tuitionPreference.days',
            'tuitionPreference.locations',
        ])
        ->where('verification_status', 'pending')
        ->when($request->search, function ($q, $search) {
            $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        })
        ->paginate($request->integer('per_page', 10));
        return response()->json(['success' => true, 'data' => $queue]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $tutor = TutorProfile::findOrFail($id);
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

        $this->logActivity($request, 'verify_tutor_approve', 'tutor_profile', $id,
            "Approved verification for tutor #{$id} ({$tutor->user?->name})");

        try {
            if ($tutor->user) {
                $tutor->user->notify(new TutorVerificationApprovedNotification());
            }
        } catch (\Exception $e) {
            Log::error('Tutor verification approved notification failed', ['error' => $e->getMessage(), 'tutor' => $id]);
        }

        return response()->json(['success' => true, 'message' => 'Tutor approved.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data  = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $tutor = TutorProfile::findOrFail($id);
        $tutor->update([
            'verification_status' => 'rejected',
            'is_verified'         => false,
            'rejection_reason'    => $data['rejection_reason'],
        ]);

        $this->logActivity($request, 'verify_tutor_reject', 'tutor_profile', $id,
            "Rejected verification for tutor #{$id}: {$data['rejection_reason']}");

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
