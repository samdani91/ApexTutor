<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConnectionConnectedTutorNotification extends Notification
{
    public function __construct(
        public readonly string $guardianName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You've been connected with a guardian — TutorKhujo")
            ->view('emails.connection-connected-tutor', [
                'name'         => $notifiable->name,
                'guardianName' => $this->guardianName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'connection_connected',
            'guardian_name' => $this->guardianName,
            'message'       => "You've been connected with {$this->guardianName}. Our team will be in touch to arrange tuition.",
        ];
    }
}
