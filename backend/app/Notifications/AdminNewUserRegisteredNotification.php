<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewUserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $userRole,
        public readonly int    $userId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = match ($this->userRole) {
            'guardian' => 'Guardian',
            'student'  => 'Student',
            'tutor'    => 'Tutor',
            default    => ucfirst($this->userRole),
        };

        return (new MailMessage)
            ->subject("New {$roleLabel} registered — TutorKhujo")
            ->view('emails.admin-new-user-registered', [
                'name'      => $notifiable->name,
                'userName'  => $this->userName,
                'userEmail' => $this->userEmail,
                'roleLabel' => $roleLabel,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $roleLabel = match ($this->userRole) {
            'guardian' => 'Guardian',
            'student'  => 'Student',
            'tutor'    => 'Tutor',
            default    => ucfirst($this->userRole),
        };

        return [
            'type'       => 'new_user_registered',
            'user_id'    => $this->userId,
            'user_name'  => $this->userName,
            'user_email' => $this->userEmail,
            'user_role'  => $this->userRole,
            'message'    => "{$this->userName} just verified their email and joined as a {$roleLabel}.",
        ];
    }
}
