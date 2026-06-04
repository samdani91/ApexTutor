<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TutorVideoReviewedNotification extends Notification
{

    public function __construct(
        public readonly string  $videoTitle,
        public readonly string  $action,      // 'approved' | 'rejected'
        public readonly ?string $reviewNote = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->action === 'approved'
                ? "Your teaching video has been approved — TutorKhujo"
                : "Your teaching video was not approved — TutorKhujo"
            )
            ->view('emails.tutor-video-reviewed', [
                'name'       => $notifiable->name,
                'videoTitle' => $this->videoTitle,
                'action'     => $this->action,
                'reviewNote' => $this->reviewNote,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->action === 'approved'
            ? "Your teaching video \"{$this->videoTitle}\" has been approved and is now live."
            : "Your teaching video \"{$this->videoTitle}\" was not approved." .
              ($this->reviewNote ? " Reason: {$this->reviewNote}" : '');

        return [
            'type'    => 'video_reviewed',
            'action'  => $this->action,
            'message' => $message,
        ];
    }
}
