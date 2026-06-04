<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPendingVideoNotification extends Notification
{

    public function __construct(
        public readonly string $tutorName,
        public readonly int    $tutorProfileId,
        public readonly string $videoTitle
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->tutorName} uploaded a teaching video for review — TutorKhujo")
            ->view('emails.admin-pending-video', [
                'adminName'  => $notifiable->name,
                'tutorName'  => $this->tutorName,
                'videoTitle' => $this->videoTitle,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'pending_video',
            'message'          => "{$this->tutorName} uploaded a teaching video \"{$this->videoTitle}\" for review.",
            'tutor_profile_id' => $this->tutorProfileId,
        ];
    }
}
