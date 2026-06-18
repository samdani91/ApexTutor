<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TutorVerificationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your profile has been verified — Apex Tutor')
            ->view('emails.tutor-verified', [
                'name' => $notifiable->name,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'tutor_verified',
            'message' => 'Congratulations! Your tutor profile has been verified and is now live.',
        ];
    }
}
