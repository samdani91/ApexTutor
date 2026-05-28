<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile()->with([
            'educationEntries', 'tuitionPreference.subjects',
            'tuitionPreference.days', 'tuitionPreference.locations',
            'personalInfo', 'emergencyContact', 'documents', 'teachingVideo',
            'travelAvailabilities',
        ])->firstOrFail();
        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function update(Request $request): JsonResponse
    {
        $data    = $request->validate(['bio' => 'nullable|string|max:2000', 'status' => 'nullable|in:active,inactive']);
        $profile = $request->user()->tutorProfile()->firstOrCreate(['user_id' => $request->user()->id]);

        if ($profile->is_verified) {
            $pending = $profile->pending_changes ?? [];
            foreach ($data as $key => $value) {
                $pending[$key] = $value;
            }
            $pending['submitted_at'] = now()->toISOString();
            $profile->update(['pending_changes' => $pending, 'pending_note' => null]);
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Changes saved — pending admin review.']);
        }

        $profile->update($data);
        return response()->json(['success' => true, 'data' => $profile, 'message' => 'Profile updated.']);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile()->withCount(['connectionRequests', 'reviews'])->first();

        if (!$profile) {
            return response()->json(['success' => true, 'data' => [
                'tutor_id'                   => null,
                'profile_completion'         => 0,
                'is_verified'                => false,
                'verification_status'        => 'not_started',
                'connection_requests_count'  => 0,
                'reviews_count'              => 0,
                'profile_views'              => 0,
                'rating'                     => null,
                'has_pending_changes'        => false,
                'pending_note'               => null,
            ]]);
        }

        return response()->json(['success' => true, 'data' => [
            'tutor_id'                  => $profile->tutor_id,
            'profile_completion'        => $profile->profile_completion_percent,
            'is_verified'               => $profile->is_verified,
            'verification_status'       => $profile->verification_status,
            'connection_requests_count' => $profile->connection_requests_count,
            'reviews_count'             => $profile->reviews_count,
            'profile_views'             => $profile->profile_view_count,
            'rating'                    => $profile->rating,
            'has_pending_changes'       => !is_null($profile->pending_changes),
            'pending_note'              => $profile->pending_note,
        ]]);
    }
}
