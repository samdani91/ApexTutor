<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TutorProfileEditedByAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your tutor profile has been updated — Apex Tutor')
            ->view('emails.tutor-profile-edited-admin', [
                'name' => $notifiable->name,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'profile_edited_by_admin',
            'message' => 'An admin has updated your tutor profile.',
        ];
    }
}
