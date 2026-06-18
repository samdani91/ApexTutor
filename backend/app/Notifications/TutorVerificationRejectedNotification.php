<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TutorVerificationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Profile verification update — Apex Tutor')
            ->view('emails.tutor-rejected', [
                'name'   => $notifiable->name,
                'reason' => $this->reason,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'tutor_verification_rejected',
            'reason'  => $this->reason,
            'message' => 'Your tutor profile verification was not approved. Please review the feedback and resubmit.',
        ];
    }
}
