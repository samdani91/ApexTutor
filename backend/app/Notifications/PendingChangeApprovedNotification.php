<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingChangeApprovedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your profile changes have been approved — TutorKhujo')
            ->view('emails.pending-approved', [
                'name' => $notifiable->name,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'pending_change_approved',
            'message' => 'Your profile changes have been approved and are now live.',
        ];
    }
}
