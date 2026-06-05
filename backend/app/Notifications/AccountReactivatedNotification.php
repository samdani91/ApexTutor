<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountReactivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account has been reactivated — TutorKhujo')
            ->view('emails.account-reactivated', [
                'name' => $notifiable->name,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'account_reactivated',
            'message' => 'Your account has been reactivated. You can now log in again.',
        ];
    }
}
