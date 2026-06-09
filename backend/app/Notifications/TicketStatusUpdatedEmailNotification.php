<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string        $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your ticket [{$this->ticket->ticket_number}] status updated — TutorKhujo")
            ->view('emails.ticket-status-updated', [
                'name'      => $notifiable->name,
                'ticket'    => $this->ticket,
                'oldStatus' => $this->formatStatus($this->oldStatus),
                'newStatus' => $this->formatStatus($this->ticket->status),
            ]);
    }

    private function formatStatus(string $status): string
    {
        return match($status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => ucfirst($status),
        };
    }
}
