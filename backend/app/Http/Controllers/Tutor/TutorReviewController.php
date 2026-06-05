<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'sort'   => 'nullable|in:newest,oldest,highest,lowest',
            'status' => 'nullable|in:approved,pending,rejected',
        ]);

        $profile = $request->user()->tutorProfile;

        $query = Review::where('tutor_profile_id', $profile->id)
            ->with('guardianProfile.user:id,name,avatar')
            ->when($request->status, fn($q) => $q->where('moderation_status', $request->status));

        match ($request->sort) {
            'oldest'  => $query->oldest(),
            'highest' => $query->orderByDesc('rating'),
            'lowest'  => $query->orderBy('rating'),
            default   => $query->latest(),
        };

        $paginated = $query->paginate(10);

        // Aggregate stats (approved reviews only)
        $approved = Review::where('tutor_profile_id', $profile->id)
            ->where('moderation_status', 'approved');

        $stats = [
            'total'    => Review::where('tutor_profile_id', $profile->id)->count(),
            'approved' => (clone $approved)->count(),
            'pending'  => Review::where('tutor_profile_id', $profile->id)->where('moderation_status', 'pending')->count(),
            'average'  => round((clone $approved)->avg('rating') ?? 0, 1),
            'by_rating' => (clone $approved)
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $paginated,
            'stats'   => $stats,
        ]);
    }
}
