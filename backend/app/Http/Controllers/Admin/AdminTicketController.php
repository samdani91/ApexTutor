<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Notifications\TicketRepliedNotification;
use App\Notifications\TicketRepliedEmailNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\TicketStatusUpdatedEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with('user:id,name,email,role')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->search) {
            $q = '%' . $request->search . '%';
            $query->where(fn($sq) =>
                $sq->where('subject', 'like', $q)
                   ->orWhere('ticket_number', 'like', $q)
                   ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $q)->orWhere('email', 'like', $q))
            );
        }

        $tickets = $query->paginate(15);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function show(int $id): JsonResponse
    {
        $ticket = SupportTicket::with([
            'user:id,name,email,role',
            'replies.user:id,name,role',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $ticket]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status'   => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $oldStatus = $ticket->status;

        $updates = ['status' => $data['status']];
        if (isset($data['priority'])) {
            $updates['priority'] = $data['priority'];
        }
        if ($data['status'] === 'resolved' && $oldStatus !== 'resolved') {
            $updates['resolved_at'] = now();
        }
        if ($data['status'] === 'closed' && $oldStatus !== 'closed') {
            $updates['closed_at'] = now();
        }
        if (in_array($data['status'], ['open', 'in_progress'])) {
            $updates['resolved_at'] = null;
            $updates['closed_at']   = null;
        }

        $ticket->update($updates);

        if ($oldStatus !== $ticket->status) {
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus));      // DB: immediate
            $ticket->user->notify(new TicketStatusUpdatedEmailNotification($ticket, $oldStatus)); // Email: queued
        }

        return response()->json(['success' => true, 'data' => $ticket->fresh()]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $ticket = SupportTicket::with('user:id,name,email')->findOrFail($id);

        $data = $request->validate([
            'body'        => 'required|string|max:5000',
            'is_internal' => 'sometimes|boolean',
        ]);

        $reply = TicketReply::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'body'        => $data['body'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        if (!($data['is_internal'] ?? false)) {
            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }
            $reply->load('user:id,name,role');
            $ticket->user->notify(new TicketRepliedNotification($ticket, $reply));      // DB: immediate
            $ticket->user->notify(new TicketRepliedEmailNotification($ticket, $reply)); // Email: queued
        }

        $reply->load('user:id,name,role');

        return response()->json(['success' => true, 'data' => $reply], 201);
    }

    public function counts(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
            'total'       => SupportTicket::count(),
        ]]);
    }
}
