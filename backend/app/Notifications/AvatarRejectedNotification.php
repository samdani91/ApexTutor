<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AvatarRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?string $note = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your profile photo was not approved — TutorKhujo')
            ->view('emails.avatar-rejected', [
                'name' => $notifiable->name,
                'note' => $this->note,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'avatar_rejected',
            'note'    => $this->note,
            'message' => 'Your profile photo submission was not approved.',
        ];
    }
}
