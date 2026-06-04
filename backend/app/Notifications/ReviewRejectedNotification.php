<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string  $tutorName,
        public readonly ?string $moderationNote = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your review was not approved — TutorKhujo")
            ->view('emails.review-rejected', [
                'name'           => $notifiable->name,
                'tutorName'      => $this->tutorName,
                'moderationNote' => $this->moderationNote,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'review_rejected',
            'tutor_name'      => $this->tutorName,
            'moderation_note' => $this->moderationNote,
            'message'         => "Your review for {$this->tutorName} was not approved by our moderation team.",
        ];
    }
}
