<?php
namespace App\Observers;

use App\Models\TutorProfile;

class TutorProfileObserver
{
    public function saved(TutorProfile $profile): void
    {
        $this->recalculate($profile);
    }

    public function recalculate(TutorProfile $profile): void
    {
        // Always reload relations so partial updates (e.g. a new document) are reflected
        $profile->load([
            'educationEntries',
            'tuitionPreference.subjects',
            'tuitionPreference.locations',
            'personalInfo',
            'emergencyContact',
            'documents',
            'teachingVideo',
        ]);

        $pts = 0;

        // ── Bio (5 pts) ───────────────────────────────────────────
        if (!empty($profile->bio)) $pts += 5;

        // ── Education (10 pts) ────────────────────────────────────
        if ($profile->educationEntries->count() > 0) $pts += 10;

        // ── Tuition Preferences (20 pts) ─────────────────────────
        $pref = $profile->tuitionPreference;
        if ($pref && $pref->preferred_classes) {
            $pts += 10;                                             // basic prefs
            if ($pref->subjects->count() > 0)  $pts += 5;         // subjects added
            if ($pref->locations->count() > 0) $pts += 5;         // areas selected
        }

        // ── Personal Info (15 pts) ────────────────────────────────
        $info = $profile->personalInfo;
        if ($info) {
            if (!empty($info->gender))           $pts += 5;
            if (!empty($info->date_of_birth))    $pts += 3;
            if (!empty($info->present_address))  $pts += 3;
            if (!empty($info->additional_phone)) $pts += 2;
            if (!empty($info->national_id))      $pts += 2;
        }

        // ── Emergency Contact (10 pts) ────────────────────────────
        if ($profile->emergencyContact) $pts += 10;

        // ── Documents (25 pts — 5 pts each) ──────────────────────
        $uploaded = $profile->documents->pluck('type')->toArray();
        foreach (['university_id', 'nid', 'ssc_marksheet', 'hsc_marksheet', 'emergency_contact_nid'] as $type) {
            if (in_array($type, $uploaded)) $pts += 5;
        }

        // ── Teaching Video (15 pts) ───────────────────────────────
        if ($profile->teachingVideo) $pts += 15;

        $profile->updateQuietly(['profile_completion_percent' => min(100, $pts)]);
    }
}
