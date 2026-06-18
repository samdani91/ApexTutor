<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $tutorName,
        public readonly int    $rating,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your review has been published — Apex Tutor")
            ->view('emails.review-approved', [
                'name'      => $notifiable->name,
                'tutorName' => $this->tutorName,
                'rating'    => $this->rating,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'review_approved',
            'tutor_name' => $this->tutorName,
            'rating'     => $this->rating,
            'message'    => "Your review for {$this->tutorName} has been approved and is now publicly visible.",
        ];
    }
}
