<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminReviewSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $guardianName,
        public readonly string $tutorName,
        public readonly int    $rating,
        public readonly int    $reviewId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->guardianName} submitted a review — Apex Tutor")
            ->view('emails.admin-review-submitted', [
                'name'         => $notifiable->name,
                'guardianName' => $this->guardianName,
                'tutorName'    => $this->tutorName,
                'rating'       => $this->rating,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'review_submitted',
            'guardian_name' => $this->guardianName,
            'tutor_name'    => $this->tutorName,
            'rating'        => $this->rating,
            'review_id'     => $this->reviewId,
            'message'       => "{$this->guardianName} submitted a {$this->rating}-star review for {$this->tutorName}. Awaiting moderation.",
        ];
    }
}
