<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Shortlist;
use App\Models\User;
use App\Notifications\TutorShortlistedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $list = $request->user()->guardianProfile->shortlists()->with([
            'tutorProfile:id,user_id,tutor_id,public_id,is_verified,rating,review_count,bio',
            'tutorProfile.user:id,name,avatar',
            'tutorProfile.tuitionPreference:id,tutor_profile_id,city,district_id,expected_salary_min,expected_salary_max,total_experience_years,preferred_classes',
            'tutorProfile.tuitionPreference.district:id,name',
        ])->get();
        return response()->json(['success' => true, 'data' => $list]);
    }

    public function store(Request $request, int $tutorProfileId): JsonResponse
    {
        $guardian = $request->user()->guardianProfile;
        $tutor    = \App\Models\TutorProfile::with('user:id,name')->findOrFail($tutorProfileId);

        [$shortlist, $created] = [
            Shortlist::firstOrCreate([
                'guardian_profile_id' => $guardian->id,
                'tutor_profile_id'    => $tutorProfileId,
            ]),
            false,
        ];

        $created = $shortlist->wasRecentlyCreated;

        if ($created) {
            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
            $notification = new TutorShortlistedNotification(
                guardianName:       $request->user()->name,
                guardianId:         $guardian->guardian_id ?? "G#{$guardian->id}",
                tutorName:          $tutor->user->name,
                tutorId:            $tutor->tutor_id ?? "T#{$tutor->id}",
                tutorProfileId:     $tutorProfileId,
                guardianProfileId:  $guardian->id,
            );
            foreach ($admins as $admin) {
                $admin->notify($notification);
            }
        }

        return response()->json(['success' => true, 'message' => 'Added to shortlist.']);
    }

    public function destroy(Request $request, int $tutorProfileId): JsonResponse
    {
        Shortlist::where('guardian_profile_id', $request->user()->guardianProfile->id)
            ->where('tutor_profile_id', $tutorProfileId)->delete();
        return response()->json(['success' => true, 'message' => 'Removed from shortlist.']);
    }
}
