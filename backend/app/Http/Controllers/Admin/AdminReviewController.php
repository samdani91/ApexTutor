<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Notifications\ReviewApprovedNotification;
use App\Notifications\ReviewRejectedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminReviewController extends Controller
{
    public function pending(Request $request): JsonResponse
    {
        $reviews = Review::with(['tutorProfile.user:id,name','guardianProfile.user:id,name'])
            ->where('moderation_status', 'pending')->paginate($request->integer('per_page', 10));
        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function all(Request $request): JsonResponse
    {
        $reviews = Review::with(['tutorProfile.user:id,name','guardianProfile.user:id,name'])
            ->when($request->status, fn($q) => $q->where('moderation_status', $request->status))
            ->when($request->search, function ($q, $search) {
                $q->whereHas('tutorProfile.user', fn($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('guardianProfile.user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            })
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10));
        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $review = Review::with([
            'tutorProfile.user:id,name',
            'guardianProfile.user:id,name,email',
        ])->findOrFail($id);

        if ($review->moderation_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Review is not in pending state.'], 422);
        }

        $review->update(['moderation_status' => 'approved', 'moderated_by' => $request->user()->id, 'moderated_at' => now()]);

        $profile = $review->tutorProfile;
        $avg = $profile->reviews()->where('moderation_status', 'approved')->avg('rating');
        $profile->update([
            'rating'       => round($avg, 2),
            'review_count' => $profile->reviews()->where('moderation_status', 'approved')->count(),
        ]);

        try {
            $guardianUser = $review->guardianProfile?->user;
            $tutorName    = $review->tutorProfile?->user?->name ?? 'the tutor';
            if ($guardianUser) {
                $guardianUser->notify(new ReviewApprovedNotification(
                    tutorName: $tutorName,
                    rating:    $review->rating,
                ));
            }
        } catch (\Exception $e) {
            Log::error('Review approved notification failed', ['error' => $e->getMessage(), 'review' => $id]);
        }

        return response()->json(['success' => true, 'message' => 'Review approved.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['moderation_note' => 'required|string|max:2000']);
        $review = Review::with([
            'tutorProfile.user:id,name',
            'guardianProfile.user:id,name,email',
        ])->findOrFail($id);

        if ($review->moderation_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Review is not in pending state.'], 422);
        }

        $review->update([
            'moderation_status' => 'rejected',
            'moderation_note'   => $request->moderation_note,
            'moderated_by'      => $request->user()->id,
            'moderated_at'      => now(),
        ]);

        try {
            $guardianUser = $review->guardianProfile?->user;
            $tutorName    = $review->tutorProfile?->user?->name ?? 'the tutor';
            if ($guardianUser) {
                $guardianUser->notify(new ReviewRejectedNotification(
                    tutorName:       $tutorName,
                    moderationNote:  $request->moderation_note,
                ));
            }
        } catch (\Exception $e) {
            Log::error('Review rejected notification failed', ['error' => $e->getMessage(), 'review' => $id]);
        }

        return response()->json(['success' => true, 'message' => 'Review rejected.']);
    }
}
