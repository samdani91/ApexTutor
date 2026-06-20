<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TuitionJobApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private static array $config = [
        'shortlisted'  => [
            'subject'    => 'You\'ve been shortlisted — Apex Tutor',
            'headline'   => 'You\'ve Been Shortlisted!',
            'subheadline'=> 'Great news on your application',
            'icon'        => '&#9733;',
            'accent'     => '#D97706',
            'message'    => 'Congratulations! You have been shortlisted for this tuition job. The guardian or admin will be in touch soon for the next steps.',
            'db_message' => 'You have been shortlisted for a tuition job.',
        ],
        'appointed'    => [
            'subject'    => 'You\'ve been appointed for a demo class — Apex Tutor',
            'headline'   => 'Demo Class Appointment',
            'subheadline'=> 'You\'re one step closer',
            'icon'        => '&#128337;',
            'accent'     => '#7C3AED',
            'message'    => 'You have been appointed for a demo class for this tuition job. Please be prepared and coordinate with the admin for the demo schedule.',
            'db_message' => 'You have been appointed for a demo class.',
        ],
        'connected'    => [
            'subject'    => 'Congratulations! You\'ve been confirmed as a tutor — Apex Tutor',
            'headline'   => 'You\'ve Been Confirmed!',
            'subheadline'=> 'Tuition has been arranged',
            'icon'        => '&#10003;',
            'accent'     => '#059669',
            'message'    => 'Congratulations! You have been confirmed as the tutor for this job. A connection request has been created. Our team will contact you with further details.',
            'db_message' => 'You have been confirmed as the tutor for a tuition job. Connection request created.',
        ],
        'not_selected' => [
            'subject'    => 'Application update — Apex Tutor',
            'headline'   => 'Application Update',
            'subheadline'=> 'Thank you for your interest',
            'icon'        => 'i',
            'accent'     => '#64748B',
            'message'    => 'Thank you for applying to this tuition job. Unfortunately, you were not selected for this position. We encourage you to keep applying to other opportunities.',
            'db_message' => 'You were not selected for a tuition job. Keep applying to other jobs!',
        ],
    ];

    public function __construct(
        public readonly string $status,
        public readonly string $jobTitle,
        public readonly string $jobPublicId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cfg = self::$config[$this->status] ?? self::$config['not_selected'];

        return (new MailMessage)
            ->subject($cfg['subject'])
            ->view('emails.tuition-job-application-status', [
                'name'       => $notifiable->name,
                'status'     => $this->status,
                'jobTitle'   => $this->jobTitle,
                'jobPublicId'=> $this->jobPublicId,
                'cfg'        => $cfg,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $cfg = self::$config[$this->status] ?? self::$config['not_selected'];
        $message = $cfg['db_message'];

        return [
            'type'         => 'tuition_job_application_status',
            'status'       => $this->status,
            'job_title'    => $this->jobTitle,
            'job_public_id'=> $this->jobPublicId,
            'message'      => $message,
        ];
    }
}
