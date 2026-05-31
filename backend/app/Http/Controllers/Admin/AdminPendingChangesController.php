<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\Subject;
use App\Models\TuitionPreference;
use App\Models\TuitionPreferenceDay;
use App\Models\TuitionPreferenceLocation;
use App\Models\TutorEmergencyContact;
use App\Models\TutorPersonalInfo;
use App\Models\TutorProfile;
use App\Notifications\PendingChangeApprovedNotification;
use App\Notifications\PendingChangeRejectedNotification;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPendingChangesController extends Controller
{
    use LogsAdminActivity;

    public function index(): JsonResponse
    {
        $profiles = TutorProfile::whereNotNull('pending_changes')
            ->with([
                'user:id,name,email',
                'tuitionPreference:id,tutor_profile_id,district_id,expected_salary_min,expected_salary_max,total_experience_years,days_per_week,hours_per_day,tutoring_methods,preferred_classes,preferred_time,place_of_tutoring',
                'tuitionPreference.subjects:id,name',
                'tuitionPreference.district:id,name',
                'tuitionPreference.locations.area:id,name',
                'personalInfo:id,tutor_profile_id,gender,date_of_birth,religion,nationality,present_address,permanent_address,additional_phone,national_id,fathers_name,fathers_phone,mothers_name,mothers_phone',
                'emergencyContact:id,tutor_profile_id,name,relation,phone,address',
            ])
            ->get();

        // Batch-load all referenced subjects, areas and districts in one query each (eliminates N+1)
        $allSubjectIds  = $profiles->flatMap(fn($p) => $p->pending_changes['preferences']['subject_ids']  ?? [])->unique();
        $allLocationIds = $profiles->flatMap(fn($p) => $p->pending_changes['preferences']['location_ids'] ?? [])->unique();
        $allDistrictIds = $profiles->map(fn($p) => $p->pending_changes['preferences']['district_id'] ?? null)->filter()->unique();

        $subjectsMap  = Subject::whereIn('id', $allSubjectIds)->pluck('name', 'id');
        $areasMap     = Area::whereIn('id', $allLocationIds)->orderBy('name')->pluck('name', 'id');
        $districtsMap = District::whereIn('id', $allDistrictIds)->pluck('name', 'id');

        $mapped = $profiles->map(function (TutorProfile $profile) use ($subjectsMap, $areasMap, $districtsMap) {
            $changes = $profile->pending_changes;

            if (isset($changes['preferences']['subject_ids'])) {
                $changes['preferences']['_subject_names'] = $subjectsMap->only($changes['preferences']['subject_ids'])->values()->toArray();
            }
            if (isset($changes['preferences']['location_ids'])) {
                $changes['preferences']['_location_names'] = $areasMap->only($changes['preferences']['location_ids'])->values()->toArray();
            }
            if (isset($changes['preferences']['district_id'])) {
                $changes['preferences']['_district_name'] = $districtsMap[$changes['preferences']['district_id']] ?? null;
            }

            return [
                'id'           => $profile->id,
                'tutor_id'     => $profile->tutor_id,
                'user'         => $profile->user,
                'submitted_at' => $changes['submitted_at'] ?? $profile->updated_at,
                'pending'      => $changes,
                'live'         => [
                    'bio'               => $profile->bio,
                    'preferences'       => $profile->tuitionPreference,
                    'personal_info'     => $profile->personalInfo,
                    'emergency_contact' => $profile->emergencyContact,
                ],
            ];
        });

        return response()->json(['success' => true, 'data' => $mapped]);
    }

    public function approve(Request $request, int $id): JsonResponse
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

            // Apply emergency contact
            if (isset($changes['emergency_contact'])) {
                TutorEmergencyContact::updateOrCreate(
                    ['tutor_profile_id' => $profile->id],
                    $changes['emergency_contact']
                );
            }

            // Clear pending state
            $profile->update(['pending_changes' => null, 'pending_note' => null]);
        });

        $profile->user->notify(new PendingChangeApprovedNotification());

        $this->logActivity($request, 'approve_pending_changes', 'tutor_profile', $id,
            "Approved pending profile changes for tutor #{$id}");

        return response()->json(['success' => true, 'message' => 'Changes approved and applied to profile.']);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $data = $request->validate(['note' => 'nullable|string|max:500']);

        $profile  = TutorProfile::findOrFail($id);
        $pending  = $profile->pending_changes ?? [];
        $sections = array_keys(collect($pending)->except('submitted_at')->toArray());

        // Extract human-readable submitted values for the notification
        $fieldLabels = [
            'bio'               => 'Bio',
            'status'            => 'Status',
            'additional_phone'  => 'Additional Phone',
            'present_address'   => 'Present Address',
            'permanent_address' => 'Permanent Address',
            'national_id'       => 'National ID',
            'fathers_name'      => "Father's Name",
            'fathers_phone'     => "Father's Phone",
            'mothers_name'      => "Mother's Name",
            'mothers_phone'     => "Mother's Phone",
            'gender'            => 'Gender',
            'date_of_birth'     => 'Date of Birth',
            'religion'          => 'Religion',
            'nationality'       => 'Nationality',
            'facebook_url'      => 'Facebook URL',
            'linkedin_url'      => 'LinkedIn URL',
            'name'              => 'Name',
            'relation'          => 'Relation',
            'phone'             => 'Phone',
            'address'           => 'Address',
        ];

        $submitted = [];
        foreach (['bio', 'status'] as $f) {
            if (!empty($pending[$f])) {
                $submitted[] = ['field' => $fieldLabels[$f], 'value' => (string) $pending[$f]];
            }
        }
        foreach ($pending['personal_info'] ?? [] as $f => $v) {
            if ($v !== null && $v !== '') {
                $submitted[] = ['field' => $fieldLabels[$f] ?? ucwords(str_replace('_', ' ', $f)), 'value' => (string) $v];
            }
        }
        foreach ($pending['emergency_contact'] ?? [] as $f => $v) {
            if ($v !== null && $v !== '') {
                $submitted[] = ['field' => $fieldLabels[$f] ?? ucwords(str_replace('_', ' ', $f)), 'value' => (string) $v];
            }
        }

        $profile->update([
            'pending_changes' => null,
            'pending_note'    => $data['note'] ?? null,
        ]);

        $profile->user->notify(new PendingChangeRejectedNotification(
            note:      $data['note'] ?? null,
            sections:  $sections,
            submitted: $submitted,
        ));

        $this->logActivity($request, 'reject_pending_changes', 'tutor_profile', $id,
            "Rejected pending profile changes for tutor #{$id}" . ($data['note'] ? ": {$data['note']}" : ''));

        return response()->json(['success' => true, 'message' => 'Changes rejected.']);
    }
}
