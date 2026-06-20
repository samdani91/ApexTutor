<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TuitionJobGuardianNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private static array $config = [
        'appointed' => [
            'subject'     => 'A tutor has been appointed for a demo class — Apex Tutor',
            'headline'    => 'Demo Class Appointed',
            'subheadline' => 'One step closer to finding a tutor',
            'icon'        => '&#128337;',
            'accent'      => '#7C3AED',
            'message'     => 'A tutor has been appointed for a demo class for your tuition job. Admin will coordinate with you for the demo schedule.',
            'db_message'  => 'A tutor has been appointed for a demo class for your job.',
        ],
        'confirmed' => [
            'subject'     => 'A tutor has been confirmed for your job — Apex Tutor',
            'headline'    => 'Tutor Confirmed!',
            'subheadline' => 'Your tuition has been arranged',
            'icon'        => '&#10003;',
            'accent'      => '#059669',
            'message'     => 'A tutor has been confirmed for your tuition job. A connection request has been created and the job is now closed. Our team will be in touch shortly.',
            'db_message'  => 'A tutor has been confirmed for your job. Connection request created.',
        ],
    ];

    public function __construct(
        public readonly string $event,
        public readonly string $jobTitle,
        public readonly string $jobPublicId,
        public readonly string $tutorName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cfg = self::$config[$this->event] ?? self::$config['confirmed'];

        return (new MailMessage)
            ->subject($cfg['subject'])
            ->view('emails.tuition-job-guardian-update', [
                'name'        => $notifiable->name,
                'event'       => $this->event,
                'jobTitle'    => $this->jobTitle,
                'jobPublicId' => $this->jobPublicId,
                'tutorName'   => $this->tutorName,
                'cfg'         => $cfg,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $cfg = self::$config[$this->event] ?? self::$config['confirmed'];

        return [
            'type'         => 'tuition_job_guardian_update',
            'event'        => $this->event,
            'job_title'    => $this->jobTitle,
            'job_public_id'=> $this->jobPublicId,
            'tutor_name'   => $this->tutorName,
            'message'      => $cfg['db_message'],
        ];
    }
}
