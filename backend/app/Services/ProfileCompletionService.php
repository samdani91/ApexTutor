<?php
namespace App\Services;

use App\Models\TutorProfile;

/**
 * Explicit profile-completion calculator.
 *
 * Replaces the hidden observer side-effect with a service that callers
 * invoke after relevant profile sections change.  The observer still
 * delegates here so existing call-sites continue to work.
 */
class ProfileCompletionService
{
    // university_id removed from platform; NID weighted at 10 pts to keep total at 25
    // (NID: 10 + ssc: 5 + hsc: 5 + emergency_nid: 5 = 25 pts, matching the old 5×5 budget)
    private const DOCUMENT_SCORES = [
        'nid'                  => 10,
        'ssc_marksheet'        =>  5,
        'hsc_marksheet'        =>  5,
        'emergency_contact_nid'=>  5,
    ];

    public function recalculate(TutorProfile $profile): void
    {
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

        // Bio — 5 pts
        if (!empty($profile->bio)) {
            $pts += 5;
        }

        // Education — 10 pts
        if ($profile->educationEntries->isNotEmpty()) {
            $pts += 10;
        }

        // Tuition preferences — 20 pts
        $pref = $profile->tuitionPreference;
        if ($pref && $pref->preferred_classes) {
            $pts += 10;
            if ($pref->subjects->isNotEmpty())  { $pts += 5; }
            if ($pref->locations->isNotEmpty()) { $pts += 5; }
        }

        // Personal info — 15 pts
        if ($info = $profile->personalInfo) {
            if (!empty($info->gender))           { $pts += 5; }
            if (!empty($info->date_of_birth))    { $pts += 3; }
            if (!empty($info->present_address))  { $pts += 3; }
            if (!empty($info->additional_phone)) { $pts += 2; }
            if (!empty($info->national_id))      { $pts += 2; }
        }

        // Emergency contact — 10 pts
        if ($profile->emergencyContact) {
            $pts += 10;
        }

        // Documents — 25 pts total (up to 5 types × 5 pts each, max 25)
        $uploaded = $profile->documents->pluck('type')->all();
        foreach (self::DOCUMENT_SCORES as $type => $score) {
            if (in_array($type, $uploaded, true)) {
                $pts += $score;
            }
        }

        // Teaching video — 15 pts
        if ($profile->teachingVideo) {
            $pts += 15;
        }

        $profile->updateQuietly(['profile_completion_percent' => min(100, $pts)]);
    }
}
