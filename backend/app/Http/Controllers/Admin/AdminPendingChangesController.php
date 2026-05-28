<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\TuitionPreference;
use App\Models\TuitionPreferenceDay;
use App\Models\TuitionPreferenceLocation;
use App\Models\TutorPersonalInfo;
use App\Models\TutorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPendingChangesController extends Controller
{
    public function index(): JsonResponse
    {
        $profiles = TutorProfile::whereNotNull('pending_changes')
            ->with([
                'user:id,name,email',
                'tuitionPreference:id,tutor_profile_id,district_id,expected_salary_min,expected_salary_max,total_experience_years,days_per_week,hours_per_day,tutoring_methods,preferred_classes,preferred_time,place_of_tutoring',
                'tuitionPreference.subjects:id,name',
                'tuitionPreference.district:id,name',
                'tuitionPreference.locations.area:id,name',
                'personalInfo:id,tutor_profile_id,gender,date_of_birth,religion,nationality,present_address,permanent_address,additional_phone,national_id,fathers_name,mothers_name',
            ])
            ->get()
            ->map(function (TutorProfile $profile) {
                $changes = $profile->pending_changes;

                // Resolve subject names
                if (isset($changes['preferences']['subject_ids'])) {
                    $changes['preferences']['_subject_names'] = \App\Models\Subject
                        ::whereIn('id', $changes['preferences']['subject_ids'])
                        ->pluck('name')->toArray();
                }

                // Resolve area names for pending location_ids
                if (isset($changes['preferences']['location_ids'])) {
                    $changes['preferences']['_location_names'] = \App\Models\Area
                        ::whereIn('id', $changes['preferences']['location_ids'])
                        ->orderBy('name')->pluck('name')->toArray();
                }

                // Resolve district name for pending district_id
                if (isset($changes['preferences']['district_id'])) {
                    $district = \App\Models\District::find($changes['preferences']['district_id']);
                    $changes['preferences']['_district_name'] = $district?->name;
                }

                return [
                    'id'           => $profile->id,
                    'tutor_id'     => $profile->tutor_id,
                    'user'         => $profile->user,
                    'submitted_at' => $changes['submitted_at'] ?? $profile->updated_at,
                    'pending'      => $changes,
                    'live'         => [
                        'bio'          => $profile->bio,
                        'preferences'  => $profile->tuitionPreference,
                        'personal_info'=> $profile->personalInfo,
                    ],
                ];
            });

        return response()->json(['success' => true, 'data' => $profiles]);
    }

    public function approve(int $id): JsonResponse
    {
        $profile = TutorProfile::findOrFail($id);
        $changes = $profile->pending_changes ?? [];

        DB::transaction(function () use ($profile, $changes) {
            // Apply top-level fields (bio, status)
            $topLevel = collect($changes)->only(['bio', 'status'])->filter(fn($v) => $v !== null)->toArray();
            if ($topLevel) {
                $profile->update($topLevel);
            }

            // Apply tuition preferences
            if (isset($changes['preferences'])) {
                $prefs = $changes['preferences'];
                $pref  = TuitionPreference::updateOrCreate(
                    ['tutor_profile_id' => $profile->id],
                    collect($prefs)->except(['subject_ids', 'days', 'locations'])->toArray()
                );
                if (isset($prefs['subject_ids'])) {
                    $pref->subjects()->sync($prefs['subject_ids']);
                }
                if (isset($prefs['days'])) {
                    $pref->days()->delete();
                    foreach ($prefs['days'] as $day) {
                        TuitionPreferenceDay::create(['tuition_preference_id' => $pref->id] + $day);
                    }
                }
                if (isset($prefs['location_ids'])) {
                    $pref->locations()->delete();
                    foreach ($prefs['location_ids'] as $areaId) {
                        TuitionPreferenceLocation::create([
                            'tuition_preference_id' => $pref->id,
                            'area_id'               => $areaId,
                        ]);
                    }
                }
            }

            // Apply personal info
            if (isset($changes['personal_info'])) {
                TutorPersonalInfo::updateOrCreate(
                    ['tutor_profile_id' => $profile->id],
                    $changes['personal_info']
                );
            }

            // Clear pending state
            $profile->update(['pending_changes' => null, 'pending_note' => null]);
        });

        return response()->json(['success' => true, 'message' => 'Changes approved and applied to profile.']);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $data = $request->validate(['note' => 'nullable|string|max:500']);

        TutorProfile::findOrFail($id)->update([
            'pending_changes' => null,
            'pending_note'    => $data['note'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Changes rejected.']);
    }
}
