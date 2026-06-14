<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userName,
        public readonly string $userRole,
        public readonly string $displayLabel,
        public readonly string $quote,
        public readonly int    $feedbackId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->userName} submitted platform feedback — TutorKhujo")
            ->view('emails.admin-new-feedback', [
                'name'         => $notifiable->name,
                'userName'     => $this->userName,
                'userRole'     => $this->userRole,
                'displayLabel' => $this->displayLabel,
                'quote'        => $this->quote,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'new_platform_feedback',
            'feedback_id'   => $this->feedbackId,
            'user_name'     => $this->userName,
            'user_role'     => $this->userRole,
            'display_label' => $this->displayLabel,
            'message'       => "{$this->userName} submitted platform feedback awaiting moderation.",
        ];
    }
}
