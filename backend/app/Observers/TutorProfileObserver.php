<?php
namespace App\Observers;

use App\Models\TutorProfile;
use App\Services\ProfileCompletionService;

class TutorProfileObserver
{
    public function __construct(private readonly ProfileCompletionService $completion) {}

    public function saved(TutorProfile $profile): void
    {
        $this->completion->recalculate($profile);
    }
}
