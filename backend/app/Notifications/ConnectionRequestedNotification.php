<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ConnectionRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $guardianName,
        public readonly string $tutorName,
        public readonly ?string $guardianMessage = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->guardianName} requested a connection — TutorKhujo")
            ->view('emails.connection-requested', [
                'name'            => $notifiable->name,
                'guardianName'    => $this->guardianName,
                'tutorName'       => $this->tutorName,
                'guardianMessage' => $this->guardianMessage,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'connection_requested',
            'guardian_name'    => $this->guardianName,
            'tutor_name'       => $this->tutorName,
            'guardian_message' => $this->guardianMessage,
            'message'          => "{$this->guardianName} has requested a connection with {$this->tutorName}.",
        ];
    }
}
