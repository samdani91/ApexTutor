<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRepliedEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly TicketReply   $reply,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New reply on your ticket [{$this->ticket->ticket_number}] — TutorKhujo")
            ->view('emails.ticket-replied', [
                'name'        => $notifiable->name,
                'ticket'      => $this->ticket,
                'replyBody'   => $this->reply->body,
                'replierName' => $this->reply->user->name,
            ]);
    }
}
