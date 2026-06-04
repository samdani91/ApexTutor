<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianReviewController extends Controller
{
    public function eligibility(Request $request, int $tutorProfileId): JsonResponse
    {
        $guardianProfile = $request->user()->guardianProfile;

        $connection = $guardianProfile->connectionRequests()
            ->where('tutor_profile_id', $tutorProfileId)
            ->where('status', 'confirmed')
            ->whereDoesntHave('review')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'eligible'             => $connection !== null,
                'connection_request_id' => $connection?->id,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tutor_profile_id'      => 'required|exists:tutor_profiles,id',
            'connection_request_id' => 'required|exists:connection_requests,id',
            'rating'                => 'required|integer|min:1|max:5',
            'review_text'           => 'nullable|string|max:1000',
        ]);

        $guardianProfile = $request->user()->guardianProfile;

        // Ensure the connection belongs to this guardian, links to the right tutor,
        // is in connected status, and has no review yet.
        $connection = $guardianProfile->connectionRequests()
            ->where('id', $data['connection_request_id'])
            ->where('tutor_profile_id', $data['tutor_profile_id'])
            ->where('status', 'confirmed')
            ->whereDoesntHave('review')
            ->firstOrFail();

        $review = Review::create([
            'tutor_profile_id'      => $data['tutor_profile_id'],
            'guardian_profile_id'   => $guardianProfile->id,
            'connection_request_id' => $connection->id,
            'rating'                => $data['rating'],
            'review_text'           => $data['review_text'] ?? null,
            'moderation_status'     => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $review,
            'message' => 'Review submitted. It will appear after moderation.',
        ], 201);
    }
}
