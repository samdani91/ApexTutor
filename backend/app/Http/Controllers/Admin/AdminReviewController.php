<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function pending(): JsonResponse
    {
        $reviews = Review::with(['tutorProfile.user:id,name','guardianProfile.user:id,name'])
            ->where('moderation_status', 'pending')->paginate(20);
        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update(['moderation_status' => 'approved', 'moderated_by' => $request->user()->id, 'moderated_at' => now()]);
        // Recalculate tutor rating
        $profile = $review->tutorProfile;
        $avg = $profile->reviews()->avg('rating');
        $profile->update(['rating' => round($avg, 2), 'review_count' => $profile->reviews()->count()]);
        return response()->json(['success' => true, 'message' => 'Review approved.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['moderation_note' => 'required|string']);
        Review::findOrFail($id)->update([
            'moderation_status' => 'rejected',
            'moderation_note'   => $request->moderation_note,
            'moderated_by'      => $request->user()->id,
            'moderated_at'      => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Review rejected.']);
    }
}
