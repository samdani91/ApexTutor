<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TuitionPreference;
use App\Models\TuitionPreferenceDay;
use App\Models\TuitionPreferenceLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $pref = $request->user()->tutorProfile->tuitionPreference()->with(['subjects','days','locations'])->first();
        return response()->json(['success' => true, 'data' => $pref]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tutoring_methods'    => 'nullable|array',
            'place_of_tutoring'   => 'nullable|array',
            'tutoring_styles'     => 'nullable|array',
            'preferred_curricula' => 'nullable|array',
            'preferred_classes'   => 'nullable|array',
            'city'                => 'nullable|string|max:100',
            'district_id'         => 'nullable|integer|exists:districts,id',
            'expected_salary_min' => 'nullable|integer|min:0',
            'expected_salary_max' => 'nullable|integer|min:0',
            'total_experience_years' => 'nullable|integer|min:0',
            'experience_details'  => 'nullable|string',
            'days_per_week'               => 'nullable|integer|min:1|max:7',
            'hours_per_day'               => 'nullable|numeric|min:0.5|max:8',
            'preferred_time'              => 'nullable|array',
            'preferred_time.*'            => 'in:morning,afternoon,evening,night',
            'tutoring_method_description' => 'nullable|string|max:2000',
            'subject_ids'         => 'nullable|array',
            'subject_ids.*'       => 'integer|exists:subjects,id',
            'days'                => 'nullable|array',
            'days.*.day'          => 'required|in:sat,sun,mon,tue,wed,thu,fri',
            'days.*.time_from'    => 'nullable|date_format:H:i',
            'days.*.time_to'      => 'nullable|date_format:H:i',
            'locations'           => 'nullable|array',
            'locations.*.area_name' => 'required|string|max:150',
        ]);

        $profile = $request->user()->tutorProfile;
        $pref = TuitionPreference::updateOrCreate(
            ['tutor_profile_id' => $profile->id],
            collect($data)->except(['subject_ids','days','locations'])->toArray()
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
        if (isset($data['locations'])) {
            $pref->locations()->delete();
            foreach ($data['locations'] as $loc) {
                TuitionPreferenceLocation::create(['tuition_preference_id' => $pref->id, 'area_name' => $loc['area_name']]);
            }
        }

        return response()->json(['success' => true, 'data' => $pref->load(['subjects','days','locations']), 'message' => 'Preferences saved.']);
    }
}
