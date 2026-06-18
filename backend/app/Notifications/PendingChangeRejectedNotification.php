<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PendingChangeRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?string $note      = null,
        public readonly array   $sections  = [],
        public readonly array   $submitted = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sectionLabels = [
            'bio'               => 'Bio & Status',
            'preferences'       => 'Tuition Preferences',
            'personal_info'     => 'Personal Information',
            'emergency_contact' => 'Emergency Contact',
        ];

        $labels = array_map(
            fn($s) => $sectionLabels[$s] ?? ucwords(str_replace('_', ' ', $s)),
            $this->sections
        );

        return (new MailMessage)
            ->subject('Your profile changes were not approved — Apex Tutor')
            ->view('emails.pending-rejected', [
                'name'      => $notifiable->name,
                'note'      => $this->note,
                'sections'  => $labels,
                'submitted' => $this->submitted,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $sectionLabels = [
            'bio'               => 'Bio & Status',
            'preferences'       => 'Tuition Preferences',
            'personal_info'     => 'Personal Information',
            'emergency_contact' => 'Emergency Contact',
        ];

        return [
            'type'      => 'pending_change_rejected',
            'note'      => $this->note,
            'sections'  => array_map(
                fn($s) => $sectionLabels[$s] ?? ucwords(str_replace('_', ' ', $s)),
                $this->sections
            ),
            'submitted' => $this->submitted,
            'message'   => 'Your profile changes were not approved by the admin.',
        ];
    }
}
