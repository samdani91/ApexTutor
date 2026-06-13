<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TutorShortlistedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $guardianName,
        public readonly string $guardianId,
        public readonly string $tutorName,
        public readonly string $tutorId,
        public readonly int    $tutorProfileId,
        public readonly int    $guardianProfileId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'tutor_shortlisted',
            'guardian_name'      => $this->guardianName,
            'guardian_id'        => $this->guardianId,
            'tutor_name'         => $this->tutorName,
            'tutor_id'           => $this->tutorId,
            'tutor_profile_id'   => $this->tutorProfileId,
            'guardian_profile_id'=> $this->guardianProfileId,
            'message'            => "{$this->guardianName} ({$this->guardianId}) shortlisted {$this->tutorName} ({$this->tutorId}). Please contact both parties to arrange tuition.",
        ];
    }
}
