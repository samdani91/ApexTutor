<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminPendingProfileChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct(
        public readonly string $tutorName,
        public readonly int    $tutorProfileId,
        public readonly string $tutorId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->tutorName} submitted profile changes for review — Apex Tutor")
            ->view('emails.admin-pending-change', [
                'adminName' => $notifiable->name,
                'tutorName' => $this->tutorName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'pending_profile_change',
            'message'          => "{$this->tutorName} has submitted profile changes for review.",
            'tutor_profile_id' => $this->tutorProfileId,
            'tutor_id'         => $this->tutorId,
        ];
    }
}
