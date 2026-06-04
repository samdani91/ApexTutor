<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\Subject;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorProfileController extends Controller
{
    public function __construct(private readonly PendingProfileChangeService $pending) {}

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

        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->mergeTopLevel($profile, $data);
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Changes saved — pending admin review.']);
        }

        $profile->update($data);
        return response()->json(['success' => true, 'data' => $profile, 'message' => 'Profile updated.']);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile()
            ->with([
                'tuitionPreference.subjects:id,name',
                'tuitionPreference.district:id,name',
                'tuitionPreference.locations.area:id,name',
                'educationEntries',
                'documents',
                'personalInfo',
                'emergencyContact',
            ])
            ->withCount([
                'connectionRequests',
                'reviews',
                'connectionRequests as confirmed_tuitions_count' => fn($q) => $q->where('status', 'confirmed'),
            ])
            ->first();

        if (!$profile) {
            return response()->json(['success' => true, 'data' => [
                'tutor_id'                   => null,
                'profile_completion'         => 0,
                'is_verified'                => false,
                'verification_status'        => 'not_started',
                'connection_requests_count'  => 0,
                'confirmed_tuitions_count'   => 0,
                'reviews_count'              => 0,
                'profile_views'              => 0,
                'rating'                     => null,
                'has_pending_changes'        => false,
                'pending_note'               => null,
                'pending_changes'            => null,
                'live_profile'               => null,
            ]]);
        }

        $pendingChanges = $this->resolvePendingChanges($profile->pending_changes);

        return response()->json(['success' => true, 'data' => [
            'tutor_id'                  => $profile->tutor_id,
            'profile_completion'        => $profile->profile_completion_percent,
            'is_verified'               => $profile->is_verified,
            'verification_status'       => $profile->verification_status,
            'connection_requests_count' => $profile->connection_requests_count,
            'confirmed_tuitions_count'  => $profile->confirmed_tuitions_count,
            'reviews_count'             => $profile->reviews_count,
            'profile_views'             => $profile->profile_view_count,
            'rating'                    => $profile->rating,
            'has_pending_changes'       => !is_null($profile->pending_changes),
            'pending_note'              => $profile->pending_note,
            'pending_changes'           => $pendingChanges,
            'live_profile'              => [
                'bio'               => $profile->bio,
                'status'            => $profile->status,
                'preferences'       => $profile->tuitionPreference,
                'education'         => $profile->educationEntries,
                'documents'         => $profile->documents,
                'personal_info'     => $profile->personalInfo,
                'emergency_contact' => $profile->emergencyContact,
            ],
        ]]);
    }

    public function confirmedTuitions(Request $request): \Illuminate\Http\JsonResponse
    {
        $profile = $request->user()->tutorProfile;
        if (!$profile) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $tuitions = $profile->connectionRequests()
            ->where('status', 'confirmed')
            ->with([
                'guardianProfile:id,user_id,guardian_id',
                'guardianProfile.user:id,name,avatar,phone',
            ])
            ->latest('confirmed_at')
            ->get();

        return response()->json(['success' => true, 'data' => $tuitions]);
    }

    private function resolvePendingChanges(?array $changes): ?array
    {
        if (!$changes) {
            return null;
        }

        if (isset($changes['preferences']['subject_ids'])) {
            $changes['preferences']['_subject_names'] = Subject::whereIn('id', $changes['preferences']['subject_ids'])
                ->distinct()
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        }

        if (isset($changes['preferences']['location_ids'])) {
            $changes['preferences']['_location_names'] = Area::whereIn('id', $changes['preferences']['location_ids'])
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        }

        if (isset($changes['preferences']['district_id'])) {
            $changes['preferences']['_district_name'] = District::find($changes['preferences']['district_id'])?->name;
        }

        return $changes;
    }
}
