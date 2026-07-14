<?php
namespace App\Services;

use App\Models\Area;
use App\Models\District;
use App\Models\Subject;
use App\Models\TutorProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Builds the admin-facing pending-changes payload.
 *
 * Resolves subject/area/district IDs to human-readable names, attaches
 * file URLs for documents, and shapes the live vs. pending diff structure
 * that AdminPendingChanges.vue consumes.
 */
class PendingProfileChangePresenter
{
    /**
     * Batch-load all name maps needed to resolve IDs across many profiles at once,
     * then present each profile.  Eliminates the N+1 for subject/area/district lookups.
     *
     * @param  Collection<TutorProfile>  $profiles
     */
    public function presentMany(Collection $profiles): array
    {
        [$subjectsMap, $areasMap, $districtsMap] = $this->buildLookupMaps($profiles);

        return $profiles
            ->map(fn($p) => $this->present($p, $subjectsMap, $areasMap, $districtsMap))
            ->values()
            ->all();
    }

    public function present(
        TutorProfile $profile,
        Collection   $subjectsMap,
        Collection   $areasMap,
        Collection   $districtsMap
    ): array {
        $changes = $profile->pending_changes ?? [];
        $changes = $this->resolveNames($changes, $subjectsMap, $areasMap, $districtsMap);
        $changes = $this->attachDocumentUrls($changes);

        $profile->documents->each(function ($doc) {
            if ($doc->file_path) {
                $doc->file_url = rtrim(config('app.url'), '/') . '/serve.php?f=' . rtrim(strtr(base64_encode($doc->file_path), '+/', '-_'), '=');
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
                'name'              => $profile->user->name,
                'avatar_url'        => $profile->user->avatar_url,
                'preferences'       => $profile->tuitionPreference,
                'education'         => $profile->educationEntries,
                'documents'         => $profile->documents,
                'personal_info'     => $profile->personalInfo,
                'emergency_contact' => $profile->emergencyContact,
            ],
        ];
    }

    // ── Name resolution ───────────────────────────────────────────────────────

    private function resolveNames(
        array      $changes,
        Collection $subjectsMap,
        Collection $areasMap,
        Collection $districtsMap
    ): array {
        if (isset($changes['preferences']['subject_ids'])) {
            $changes['preferences']['_subject_names'] = $subjectsMap
                ->only($changes['preferences']['subject_ids'])
                ->values()
                ->unique()
                ->sort()
                ->values()
                ->all();
        }

        if (isset($changes['preferences']['location_ids'])) {
            $changes['preferences']['_location_names'] = $areasMap
                ->only($changes['preferences']['location_ids'])
                ->values()
                ->all();
        }

        if (isset($changes['preferences']['district_id'])) {
            $changes['preferences']['_district_name'] = $districtsMap->get(
                $changes['preferences']['district_id']
            );
        }

        return $changes;
    }

    private function attachDocumentUrls(array $changes): array
    {
        foreach ($changes['documents']['upsert'] ?? [] as $type => $doc) {
            if (!empty($doc['file_path'])) {
                $changes['documents']['upsert'][$type]['file_url'] =
                    rtrim(config('app.url'), '/') . '/serve.php?f=' . rtrim(strtr(base64_encode($doc['file_path']), '+/', '-_'), '=');
            }
        }

        return $changes;
    }

    // ── Rejection summary ─────────────────────────────────────────────────────

    /**
     * Build a human-readable list of submitted field values for the rejection notification.
     * Called by AdminPendingChangesController::reject().
     */
    public function buildRejectionSummary(array $pending): array
    {
        static $labels = [
            'bio' => 'Bio', 'status' => 'Status',
            'additional_phone' => 'Additional Phone', 'present_address' => 'Present Address',
            'permanent_address' => 'Permanent Address', 'national_id' => 'National ID',
            'fathers_name' => "Father's Name", 'fathers_phone' => "Father's Phone",
            'mothers_name' => "Mother's Name", 'mothers_phone' => "Mother's Phone",
            'gender' => 'Gender', 'date_of_birth' => 'Date of Birth',
            'religion' => 'Religion', 'nationality' => 'Nationality',
            'facebook_url' => 'Facebook URL', 'linkedin_url' => 'LinkedIn URL',
            'name' => 'Name', 'relation' => 'Relation', 'phone' => 'Phone', 'address' => 'Address',
        ];

        $rows = [];

        foreach (['bio', 'status', 'name'] as $field) {
            if (!empty($pending[$field])) {
                $rows[] = ['field' => $labels[$field], 'value' => (string) $pending[$field]];
            }
        }
        foreach ($pending['personal_info'] ?? [] as $field => $value) {
            if ($value !== null && $value !== '') {
                $rows[] = ['field' => $labels[$field] ?? ucwords(str_replace('_', ' ', $field)), 'value' => (string) $value];
            }
        }
        foreach ($pending['emergency_contact'] ?? [] as $field => $value) {
            if ($value !== null && $value !== '') {
                $rows[] = ['field' => $labels[$field] ?? ucwords(str_replace('_', ' ', $field)), 'value' => (string) $value];
            }
        }

        return $rows;
    }

    // ── Lookup map builder ────────────────────────────────────────────────────

    private function buildLookupMaps(Collection $profiles): array
    {
        $subjectIds  = $profiles->flatMap(fn($p) => $p->pending_changes['preferences']['subject_ids']  ?? [])->unique();
        $locationIds = $profiles->flatMap(fn($p) => $p->pending_changes['preferences']['location_ids'] ?? [])->unique();
        $districtIds = $profiles->map(fn($p) => $p->pending_changes['preferences']['district_id'] ?? null)->filter()->unique();

        return [
            Subject::whereIn('id', $subjectIds)->pluck('name', 'id'),
            Area::whereIn('id', $locationIds)->orderBy('name')->pluck('name', 'id'),
            District::whereIn('id', $districtIds)->pluck('name', 'id'),
        ];
    }
}
