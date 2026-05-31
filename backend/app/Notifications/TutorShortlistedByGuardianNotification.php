<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TutorShortlistedByGuardianNotification extends Notification
{
    public function __construct(
        public readonly string $guardianName,
        public readonly int    $guardianProfileId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->guardianName} shortlisted you — TutorKhujo")
            ->view('emails.tutor-shortlisted', [
                'name'         => $notifiable->name,
                'guardianName' => $this->guardianName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                => 'tutor_shortlisted',
            'guardian_name'       => $this->guardianName,
            'guardian_profile_id' => $this->guardianProfileId,
            'message'             => "{$this->guardianName} has added you to their shortlist.",
        ];
    }
}
