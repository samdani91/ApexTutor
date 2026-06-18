<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminTicketClaimEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly User          $actor,
        public readonly string        $action, // 'claimed' | 'unclaimed'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject())
            ->view('emails.admin-ticket-claim', [
                'name'     => $notifiable->name,
                'ticket'   => $this->ticket,
                'actor'    => $this->actor,
                'action'   => $this->action,
                'isSelf'   => $notifiable->id === $this->actor->id,
            ]);
    }

    private function subject(): string
    {
        $verb = $this->action === 'claimed' ? 'Claimed' : 'Unclaimed';
        return "Ticket [{$this->ticket->ticket_number}] {$verb} — Apex Tutor";
    }
}
