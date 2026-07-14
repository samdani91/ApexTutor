<?php
namespace App\Services;

use App\Models\TuitionPreference;
use App\Models\TuitionPreferenceDay;
use App\Models\TuitionPreferenceLocation;
use App\Models\TutorEmergencyContact;
use App\Models\TutorPersonalInfo;
use App\Models\TutorProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Applies an approved pending_changes payload to the real profile tables.
 *
 * File deletions are intentionally deferred to AFTER the DB transaction commits
 * so a mid-transaction failure cannot leave the filesystem in an inconsistent
 * state (files deleted but DB rows still present).
 */
class PendingProfileChangeApplier
{
    public function apply(TutorProfile $profile): void
    {
        $changes      = $profile->pending_changes ?? [];
        $filesToDelete = [];

        DB::transaction(function () use ($profile, $changes, &$filesToDelete) {
            $topFiles      = $this->applyTopLevel($profile, $changes);
            $this->applyPreferences($profile, $changes);
            $this->applyPersonalInfo($profile, $changes);
            $this->applyEmergencyContact($profile, $changes);
            $this->applyEducation($profile, $changes);
            $docFiles      = $this->applyDocuments($profile, $changes);
            $filesToDelete = array_merge($topFiles, $docFiles);

            $profile->update(['pending_changes' => null, 'pending_note' => null]);
        });

        // Delete physical files only after the transaction is safely committed.
        foreach ($filesToDelete as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    // ── Sections ─────────────────────────────────────────────────────────────

    private function applyTopLevel(TutorProfile $profile, array $changes): array
    {
        $toDelete = [];

        $fields = collect($changes)->only(['bio', 'status'])->filter(fn($v) => $v !== null)->toArray();
        if ($fields) {
            $profile->update($fields);
        }

        if (isset($changes['avatar'])) {
            $user      = $profile->user;
            $oldAvatar = $user->avatar;
            $user->avatar         = $user->pending_avatar ?? $changes['avatar']['path'] ?? null;
            $user->pending_avatar = null;
            $user->save();
            if ($oldAvatar) {
                $toDelete[] = $oldAvatar;
            }
        }

        if (isset($changes['name'])) {
            $user = $profile->user;
            $user->name         = $user->pending_name ?? $changes['name'];
            $user->pending_name = null;
            $user->save();
        }

        return $toDelete;
    }

    private function applyPreferences(TutorProfile $profile, array $changes): void
    {
        if (!isset($changes['preferences'])) {
            return;
        }

        $prefs = $changes['preferences'];
        $pref  = TuitionPreference::updateOrCreate(
            ['tutor_profile_id' => $profile->id],
            collect($prefs)->except(['subject_ids', 'location_ids', 'days'])->toArray()
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

    private function applyPersonalInfo(TutorProfile $profile, array $changes): void
    {
        if (isset($changes['personal_info'])) {
            TutorPersonalInfo::updateOrCreate(
                ['tutor_profile_id' => $profile->id],
                $changes['personal_info']
            );
        }
    }

    private function applyEmergencyContact(TutorProfile $profile, array $changes): void
    {
        if (isset($changes['emergency_contact'])) {
            TutorEmergencyContact::updateOrCreate(
                ['tutor_profile_id' => $profile->id],
                $changes['emergency_contact']
            );
        }
    }

    private function applyEducation(TutorProfile $profile, array $changes): void
    {
        foreach ($changes['education']['changes'] ?? [] as $change) {
            $action  = $change['action'] ?? null;
            $entryId = $change['id'] ?? null;
            $data    = $change['data'] ?? [];

            match ($action) {
                'delete' => $profile->educationEntries()->whereKey($entryId)->delete(),
                'update' => $profile->educationEntries()->whereKey($entryId)->update($data),
                'create' => $profile->educationEntries()->create($data),
                default  => null,
            };
        }
    }

    /**
     * Returns paths of files that should be deleted after the transaction commits.
     */
    private function applyDocuments(TutorProfile $profile, array $changes): array
    {
        $toDelete = [];

        foreach ($changes['documents']['delete'] ?? [] as $docId) {
            $doc = $profile->documents()->whereKey($docId)->first();
            if (!$doc) {
                continue;
            }
            $toDelete[] = $doc->file_path;
            $doc->delete();
        }

        foreach ($changes['documents']['upsert'] ?? [] as $type => $docData) {
            foreach ($profile->documents()->where('type', $type)->get() as $existing) {
                $toDelete[] = $existing->file_path;
                $existing->delete();
            }
            $profile->documents()->create($docData + ['review_status' => 'approved']);
        }

        return $toDelete;
    }
}
