<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConnectionRequestedTutorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string  $guardianName,
        public readonly int     $connectionId,
        public readonly ?string $guardianMessage = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->guardianName} wants to connect with you — TutorKhujo")
            ->view('emails.connection-requested-tutor', [
                'name'            => $notifiable->name,
                'guardianName'    => $this->guardianName,
                'guardianMessage' => $this->guardianMessage,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'connection_requested_tutor',
            'guardian_name'    => $this->guardianName,
            'connection_id'    => $this->connectionId,
            'guardian_message' => $this->guardianMessage,
            'message'          => "{$this->guardianName} has sent you a connection request.",
        ];
    }
}
