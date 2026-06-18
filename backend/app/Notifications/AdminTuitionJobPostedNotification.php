<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminTuitionJobPostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $guardianName,
        public readonly string $jobTitle,
        public readonly string $publicId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'new_tuition_job',
            'guardian_name' => $this->guardianName,
            'job_title'     => $this->jobTitle,
            'public_id'     => $this->publicId,
            'message'       => "{$this->guardianName} posted a new tuition job: {$this->jobTitle}.",
        ];
    }
}
