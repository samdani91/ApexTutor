<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TuitionPreference;
use App\Models\TuitionPreferenceDay;
use App\Models\TuitionPreferenceLocation;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionPreferenceController extends Controller
{
    public function __construct(private readonly PendingProfileChangeService $pending) {}

    public function show(Request $request): JsonResponse
    {
        $pref = $request->user()->tutorProfile->tuitionPreference()->with(['subjects','days','locations'])->first();
        return response()->json(['success' => true, 'data' => $pref]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tutoring_methods'            => 'nullable|array',
            'place_of_tutoring'           => 'nullable|array',
            'tutoring_styles'             => 'nullable|array',
            'preferred_curricula'         => 'nullable|array',
            'preferred_classes'           => 'nullable|array',
            'preferred_groups'            => 'nullable|array',
            'preferred_groups.*'          => 'in:science,business_studies,humanities',
            'district_id'                 => 'nullable|integer|exists:districts,id',
            'expected_salary_min'         => 'nullable|integer|min:0',
            'expected_salary_max'         => 'nullable|integer|min:0',
            'total_experience_years'      => 'nullable|integer|min:0',
            'experience_details'          => 'nullable|string',
            'days_per_week'               => 'nullable|integer|min:1|max:7',
            'hours_per_day'               => 'nullable|numeric|min:0.5|max:8',
            'preferred_time'              => 'nullable|array',
            'preferred_time.*'            => 'in:morning,afternoon,evening,night',
            'tutoring_method_description' => 'nullable|string|max:2000',
            'subject_ids'                 => 'nullable|array',
            'subject_ids.*'               => 'integer|exists:subjects,id',
            'days'                        => 'nullable|array',
            'days.*.day'                  => 'required|in:sat,sun,mon,tue,wed,thu,fri',
            'days.*.time_from'            => 'nullable|date_format:H:i',
            'days.*.time_to'              => 'nullable|date_format:H:i',
            'location_ids'                => 'nullable|array',
            'location_ids.*'              => 'integer|exists:areas,id',
        ]);

        $profile = $request->user()->tutorProfile;

        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->merge($profile, 'preferences', $data);
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Preferences saved — pending admin review.']);
        }

        // Bug fix: was excluding 'locations' (non-existent key) — must exclude 'location_ids'
        $pref = TuitionPreference::updateOrCreate(
            ['tutor_profile_id' => $profile->id],
            collect($data)->except(['subject_ids', 'days', 'location_ids'])->toArray()
        );

        if (isset($data['subject_ids'])) {
            $pref->subjects()->sync($data['subject_ids']);
        }
        if (isset($data['days'])) {
            $pref->days()->delete();
            foreach ($data['days'] as $day) {
                TuitionPreferenceDay::create(['tuition_preference_id' => $pref->id] + $day);
            }
        }
        if (isset($data['location_ids'])) {
            $pref->locations()->delete();
            foreach ($data['location_ids'] as $areaId) {
                TuitionPreferenceLocation::create(['tuition_preference_id' => $pref->id, 'area_id' => $areaId]);
            }
        }

        return response()->json(['success' => true, 'data' => $pref->load(['subjects', 'days', 'locations']), 'message' => 'Preferences saved.']);
    }
}
