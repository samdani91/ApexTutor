<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminTicketClaimNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly User          $actor,
        public readonly string        $action, // 'claimed' | 'unclaimed'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $verb = $this->action === 'claimed' ? 'claimed' : 'unclaimed';
        return [
            'type'          => 'ticket_claim_update',
            'action'        => $this->action,
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'actor_id'      => $this->actor->id,
            'actor_name'    => $this->actor->name,
            'message'       => "{$this->actor->name} {$verb} ticket [{$this->ticket->ticket_number}]: {$this->ticket->subject}",
        ];
    }
}
