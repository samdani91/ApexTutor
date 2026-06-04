<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ConnectionConfirmedTutorNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject("Your tuition has been confirmed — TutorKhujo")
            ->view('emails.connection-confirmed-tutor', [
                'name'         => $notifiable->name,
                'guardianName' => $this->guardianName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'tuition_confirmed',
            'guardian_name' => $this->guardianName,
            'message'       => "Your tuition with {$this->guardianName} has been confirmed. Our team will be in touch to finalise the arrangement.",
        ];
    }
}
