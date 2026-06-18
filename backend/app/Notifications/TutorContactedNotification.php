<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TutorContactedNotification extends Notification implements ShouldQueue
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
            ->subject("You have been contacted for a tuition — Apex Tutor")
            ->view('emails.tutor-contacted', [
                'name'         => $notifiable->name,
                'guardianName' => $this->guardianName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'tutor_contacted',
            'guardian_name' => $this->guardianName,
            'message'       => "Our team has contacted you regarding a tuition request from {$this->guardianName}.",
        ];
    }
}
