<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string        $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'ticket_status_updated',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'old_status'    => $this->oldStatus,
            'new_status'    => $this->ticket->status,
            'message'       => "Your ticket [{$this->ticket->ticket_number}] status changed from {$this->formatStatus($this->oldStatus)} to {$this->formatStatus($this->ticket->status)}.",
        ];
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
