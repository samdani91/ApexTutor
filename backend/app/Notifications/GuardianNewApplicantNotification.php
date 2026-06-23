<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuardianNewApplicantNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $tutorName,
        public readonly string $tutorId,
        public readonly string $jobTitle,
        public readonly string $jobPublicId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New applicant for your tuition job — Apex Tutor")
            ->view('emails.guardian-new-applicant', [
                'name'        => $notifiable->name,
                'tutorName'   => $this->tutorName,
                'tutorId'     => $this->tutorId,
                'jobTitle'    => $this->jobTitle,
                'jobPublicId' => $this->jobPublicId,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'new_job_applicant',
            'tutor_name'    => $this->tutorName,
            'tutor_id'      => $this->tutorId,
            'job_title'     => $this->jobTitle,
            'job_public_id' => $this->jobPublicId,
            'message'       => "Tutor {$this->tutorName} ({$this->tutorId}) applied to your tuition job: {$this->jobTitle} (#{$this->jobPublicId}).",
        ];
    }
}
