<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminGuardianJobRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $requestType, // 'demo_requested' | 'confirm_requested'
        public readonly string $guardianName,
        public readonly string $tutorName,
        public readonly string $jobTitle,
        public readonly string $jobPublicId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->requestType === 'demo_requested') {
            $message = "{$this->guardianName} has requested a demo class appointment for tutor {$this->tutorName} on job: {$this->jobTitle}.";
        } else {
            $message = "{$this->guardianName} has requested confirmation of tutor {$this->tutorName} for job: {$this->jobTitle}.";
        }

        return [
            'type'           => 'guardian_job_request',
            'request_type'   => $this->requestType,
            'guardian_name'  => $this->guardianName,
            'tutor_name'     => $this->tutorName,
            'job_title'      => $this->jobTitle,
            'job_public_id'  => $this->jobPublicId,
            'message'        => $message,
        ];
    }
}
