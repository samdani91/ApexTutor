<?php
namespace App\Observers;

use App\Models\TutorDocument;
use App\Services\ProfileCompletionService;

class TutorDocumentObserver
{
    public function __construct(private readonly ProfileCompletionService $completion) {}

    public function saved(TutorDocument $doc): void
    {
        $this->trigger($doc);
    }

    public function deleted(TutorDocument $doc): void
    {
        $this->trigger($doc);
    }

    private function trigger(TutorDocument $doc): void
    {
        $this->completion->recalculate($doc->tutorProfile);
    }
}
