<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminPendingAvatarNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userName,
        public readonly string $userRole,
        public readonly int    $userId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'pending_avatar',
            'message'   => "{$this->userName} submitted a new profile picture for approval.",
            'user_id'   => $this->userId,
            'user_role' => $this->userRole,
        ];
    }
}
