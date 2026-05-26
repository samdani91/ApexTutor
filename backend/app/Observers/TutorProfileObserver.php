<?php
namespace App\Observers;

use App\Models\TutorProfile;

class TutorProfileObserver
{
    public function saved(TutorProfile $profile): void
    {
        $this->recalculateCompletion($profile);
    }

    private function recalculateCompletion(TutorProfile $profile): void
    {
        $profile->loadMissing(['educationEntries','tuitionPreference','personalInfo','emergencyContact','documents','teachingVideo']);
        $steps = [
            'education'  => $profile->educationEntries->count() > 0,
            'preference' => $profile->tuitionPreference !== null && $profile->tuitionPreference->preferred_classes !== null,
            'personal'   => $profile->personalInfo !== null && $profile->personalInfo->gender !== null,
            'emergency'  => $profile->emergencyContact !== null,
            'documents'  => $profile->documents->count() > 0,
            'video'      => $profile->teachingVideo !== null,
        ];
        $percent = (int)(array_sum($steps) / count($steps) * 100);
        $profile->updateQuietly(['profile_completion_percent' => $percent]);
    }
}
