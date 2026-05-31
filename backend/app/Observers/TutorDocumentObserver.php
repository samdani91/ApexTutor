<?php
namespace App\Observers;

use App\Models\TutorDocument;

class TutorDocumentObserver
{
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
        app(TutorProfileObserver::class)->recalculate($doc->tutorProfile);
    }
}
