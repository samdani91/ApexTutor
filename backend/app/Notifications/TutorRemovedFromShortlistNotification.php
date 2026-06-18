<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TutorRemovedFromShortlistNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $guardianName,
        public readonly int    $guardianProfileId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->guardianName} removed you from their shortlist — Apex Tutor")
            ->view('emails.tutor-removed-from-shortlist', [
                'name'         => $notifiable->name,
                'guardianName' => $this->guardianName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                => 'tutor_removed_from_shortlist',
            'guardian_name'       => $this->guardianName,
            'guardian_profile_id' => $this->guardianProfileId,
            'message'             => "{$this->guardianName} has removed you from their shortlist.",
        ];
    }
}
