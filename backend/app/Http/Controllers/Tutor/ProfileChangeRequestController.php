<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileChangeRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile;

        // When the profile is locked (verified), 'approved' requests are stale —
        // they belong to a previous edit cycle where the profile was already re-locked.
        // Only return pending/rejected so the tutor can see status or resubmit.
        $statuses = $profile->is_verified
            ? ['pending', 'rejected']
            : ['pending', 'approved', 'review_pending', 'rejected'];

        $latest = ProfileChangeRequest::where('tutor_profile_id', $profile->id)
            ->whereIn('status', $statuses)
            ->latest()
            ->first();

        return response()->json(['success' => true, 'data' => $latest]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);

        $profile = $request->user()->tutorProfile;

        // Block duplicate pending requests
        $pending = ProfileChangeRequest::where('tutor_profile_id', $profile->id)
            ->where('status', 'pending')
            ->exists();
        if ($pending) {
            return response()->json(['success' => false, 'message' => 'You already have a pending change request.'], 422);
        }

        $req = ProfileChangeRequest::create([
            'tutor_profile_id' => $profile->id,
            'reason'           => $data['reason'],
            'status'           => 'pending',
        ]);

        return response()->json(['success' => true, 'data' => $req, 'message' => 'Change request submitted.'], 201);
    }

    public function doneEditing(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile;

        $req = ProfileChangeRequest::where('tutor_profile_id', $profile->id)
            ->where('status', 'approved')
            ->latest()
            ->firstOrFail();

        $req->update(['status' => 'review_pending']);

        return response()->json(['success' => true, 'data' => $req, 'message' => 'Profile submitted for re-review.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile;

        $req = ProfileChangeRequest::where('tutor_profile_id', $profile->id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $req->delete();

        return response()->json(['success' => true, 'message' => 'Change request cancelled.']);
    }
}
