<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ConnectionStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private static array $subjects = [
        'admin_reviewing'  => 'We\'re reviewing your connection request — Apex Tutor',
        'tutor_contacted'  => 'We\'ve contacted the tutor on your behalf — Apex Tutor',
        'confirmed'        => 'Your tuition has been confirmed — Apex Tutor',
        'declined'         => 'Connection request update — Apex Tutor',
        'closed'           => 'Your connection has been closed — Apex Tutor',
    ];

    public function __construct(
        public readonly string  $status,
        public readonly string  $tutorName,
        public readonly ?string $adminNote = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = self::$subjects[$this->status] ?? 'Connection update — Apex Tutor';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.connection-status-changed', [
                'name'      => $notifiable->name,
                'status'    => $this->status,
                'tutorName' => $this->tutorName,
                'adminNote' => $this->adminNote,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $messages = [
            'admin_reviewing' => "Your connection request with {$this->tutorName} is under review.",
            'tutor_contacted' => "We've contacted {$this->tutorName} on your behalf.",
            'confirmed'       => "Your tuition with {$this->tutorName} has been confirmed!",
            'declined'        => "Your connection request with {$this->tutorName} was declined.",
            'closed'          => "Your connection with {$this->tutorName} has been closed.",
        ];

        return [
            'type'       => 'connection_status_changed',
            'status'     => $this->status,
            'tutor_name' => $this->tutorName,
            'admin_note' => $this->adminNote,
            'message'    => $messages[$this->status] ?? "Your connection status has been updated.",
        ];
    }
}
