<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification
{
    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly TicketReply   $reply,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'ticket_replied',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'reply_id'      => $this->reply->id,
            'message'       => "Admin replied to your ticket [{$this->ticket->ticket_number}].",
        ];
    }
}
