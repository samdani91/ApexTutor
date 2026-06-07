<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminGuardianProfileUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $guardianName,
        public readonly string $guardianEmail,
        public readonly int    $guardianId,
        public readonly string $updateType,  // 'profile' | 'nid_document'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Guardian profile updated — TutorKhujo")
            ->view('emails.admin-guardian-profile-updated', [
                'adminName'    => $notifiable->name,
                'guardianName' => $this->guardianName,
                'updateType'   => $this->updateType,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->updateType === 'nid_document' ? 'NID document' : 'profile information';

        return [
            'type'          => 'guardian_profile_updated',
            'guardian_id'   => $this->guardianId,
            'guardian_name' => $this->guardianName,
            'update_type'   => $this->updateType,
            'message'       => "{$this->guardianName} updated their {$label}.",
        ];
    }
}
