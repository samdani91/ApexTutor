<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminNewTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'new_support_ticket',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'category'      => $this->ticket->category,
            'user_id'       => $this->ticket->user_id,
            'user_name'     => $this->ticket->user->name,
            'message'       => "{$this->ticket->user->name} opened support ticket [{$this->ticket->ticket_number}]: {$this->ticket->subject}",
        ];
    }
}
