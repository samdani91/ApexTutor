<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\EducationEntry;
use App\Models\Subject;
use App\Models\TuitionPreference;
use App\Models\TuitionPreferenceDay;
use App\Models\TuitionPreferenceLocation;
use App\Models\TutorDocument;
use App\Models\TutorEmergencyContact;
use App\Models\TutorPersonalInfo;
use App\Models\TutorProfile;
use App\Notifications\PendingChangeApprovedNotification;
use App\Notifications\PendingChangeRejectedNotification;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                'educationEntries',
                'documents',
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
                $changes['preferences']['_subject_names'] = $subjectsMap->only($changes['preferences']['subject_ids'])
                    ->values()
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();
            }
            if (isset($changes['preferences']['location_ids'])) {
                $changes['preferences']['_location_names'] = $areasMap->only($changes['preferences']['location_ids'])->values()->toArray();
            }
            if (isset($changes['preferences']['district_id'])) {
                $changes['preferences']['_district_name'] = $districtsMap[$changes['preferences']['district_id']] ?? null;
            }

            if (isset($changes['documents']['upsert'])) {
                foreach ($changes['documents']['upsert'] as $type => $document) {
                    if (!empty($document['file_path'])) {
                        $changes['documents']['upsert'][$type]['file_url'] = Storage::disk('public')->url($document['file_path']);
                    }
                }
            }

            $profile->documents->each(function ($document) {
                if ($document->file_path) {
                    $document->file_url = Storage::disk('public')->url($document->file_path);
                }
            });

            return [
                'id'           => $profile->id,
                'tutor_id'     => $profile->tutor_id,
                'user'         => $profile->user,
                'submitted_at' => $changes['submitted_at'] ?? $profile->updated_at,
                'pending'      => $changes,
                'live'         => [
                    'bio'               => $profile->bio,
                    'preferences'       => $profile->tuitionPreference,
                    'education'         => $profile->educationEntries,
                    'documents'         => $profile->documents,
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

            if (isset($changes['education']['changes'])) {
                foreach ($changes['education']['changes'] as $change) {
                    $action = $change['action'] ?? null;
                    $entryId = $change['id'] ?? null;
                    $data = $change['data'] ?? [];

                    if ($action === 'delete' && $entryId) {
                        $profile->educationEntries()->whereKey($entryId)->delete();
                        continue;
                    }

                    if ($action === 'update' && $entryId) {
                        $profile->educationEntries()->whereKey($entryId)->update($data);
                        continue;
                    }

                    if ($action === 'create') {
                        $profile->educationEntries()->create($data);
                    }
                }
            }

            if (isset($changes['documents'])) {
                foreach ($changes['documents']['delete'] ?? [] as $docId) {
                    $doc = $profile->documents()->whereKey($docId)->first();
                    if (!$doc) continue;
                    Storage::disk('public')->delete($doc->file_path);
                    $doc->delete();
                }

                foreach ($changes['documents']['upsert'] ?? [] as $type => $docData) {
                    $existing = $profile->documents()->where('type', $type)->get();
                    foreach ($existing as $doc) {
                        Storage::disk('public')->delete($doc->file_path);
                        $doc->delete();
                    }
                    $profile->documents()->create($docData + ['review_status' => 'pending']);
                }
            }

            // Clear pending state
            $profile->update(['pending_changes' => null, 'pending_note' => null]);
        });

        try {
            $profile->user->notify(new PendingChangeApprovedNotification());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification failed (approve)', ['error' => $e->getMessage(), 'profile' => $id]);
        }

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

        try {
            $profile->user->notify(new PendingChangeRejectedNotification(
                note:      $data['note'] ?? null,
                sections:  $sections,
                submitted: $submitted,
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification failed (reject)', ['error' => $e->getMessage(), 'profile' => $id]);
        }

        $this->logActivity($request, 'reject_pending_changes', 'tutor_profile', $id,
            "Rejected pending profile changes for tutor #{$id}" . ($data['note'] ? ": {$data['note']}" : ''));

        return response()->json(['success' => true, 'message' => 'Changes rejected.']);
    }
}
