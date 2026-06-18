<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewTicketEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New support ticket [{$this->ticket->ticket_number}] — Apex Tutor")
            ->view('emails.admin-new-ticket', [
                'adminName'     => $notifiable->name,
                'ticket'        => $this->ticket,
                'userName'      => $this->ticket->user->name,
                'userEmail'     => $this->ticket->user->email,
                'categoryLabel' => $this->ticket->category_label,
            ]);
    }
}
