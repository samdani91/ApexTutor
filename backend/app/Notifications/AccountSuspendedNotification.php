<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account has been suspended — Apex Tutor')
            ->view('emails.account-suspended', [
                'name'   => $notifiable->name,
                'reason' => $this->reason,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'account_suspended',
            'message' => 'Your account has been suspended by an administrator.',
        ];
    }
}
