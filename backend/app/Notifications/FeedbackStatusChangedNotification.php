<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $status, // 'approved' | 'rejected'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->status === 'approved'
            ? 'Your feedback is now on the landing page — Apex Tutor'
            : 'Update on your platform feedback — Apex Tutor';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.feedback-status-changed', [
                'name'   => $notifiable->name,
                'status' => $this->status,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->status === 'approved'
            ? 'Your platform feedback has been approved and is now visible on the landing page. Thank you!'
            : 'Your platform feedback has been reviewed and was not approved at this time.';

        return [
            'type'    => 'feedback_status_changed',
            'status'  => $this->status,
            'message' => $message,
        ];
    }
}
