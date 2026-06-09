<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Notifications\AdminTicketClaimNotification;
use App\Notifications\AdminTicketClaimEmailNotification;
use App\Notifications\TicketRepliedNotification;
use App\Notifications\TicketRepliedEmailNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\TicketStatusUpdatedEmailNotification;
use App\Models\User;
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
        if ($request->search && $request->search_by) {
            $term = $request->search;
            match ($request->search_by) {
                'ticket_number' => $query->where('ticket_number', 'like', '%' . $term . '%'),
                'user_email'    => $query->whereHas('user', fn($uq) => $uq->where('email', 'like', '%' . $term . '%')),
                'user_phone'    => $query->whereHas('user', fn($uq) => $uq->where('phone', 'like', '%' . $term . '%')),
                'user_id'       => $query->whereHas('user', function ($uq) use ($term) {
                    $uq->whereHas('tutorProfile',   fn($q) => $q->where('tutor_id',   'like', '%' . $term . '%'))
                       ->orWhereHas('guardianProfile', fn($q) => $q->where('guardian_id', 'like', '%' . $term . '%'));
                }),
                default         => null,
            };
        }

        $query->with('assignedAdmin:id,name');
        $tickets = $query->paginate(15);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function show(int $id): JsonResponse
    {
        $ticket = SupportTicket::with([
            'user:id,name,email,role',
            'assignedAdmin:id,name',
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

        if ($ticket->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You must claim this ticket before updating its status or priority.',
            ], 403);
        }

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

        if ($ticket->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You must claim this ticket before you can reply.',
            ], 403);
        }

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

    public function claim(int $id): JsonResponse
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->assigned_to !== null) {
            return response()->json(['success' => false, 'message' => 'This ticket is already claimed.'], 422);
        }

        $actor = Auth::user();
        $ticket->update(['assigned_to' => $actor->id]);
        $ticket->load('assignedAdmin:id,name');

        $this->notifyAllAdmins($ticket, $actor, 'claimed');

        return response()->json(['success' => true, 'data' => $ticket->fresh(['assignedAdmin'])]);
    }

    public function unclaim(int $id): JsonResponse
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You can only unclaim a ticket you have claimed.'], 403);
        }

        $actor = Auth::user();
        $ticket->update(['assigned_to' => null]);

        $this->notifyAllAdmins($ticket, $actor, 'unclaimed');

        return response()->json(['success' => true, 'data' => $ticket->fresh(['assignedAdmin'])]);
    }

    private function notifyAllAdmins(SupportTicket $ticket, $actor, string $action): void
    {
        User::where('role', 'super_admin')->each(function ($admin) use ($ticket, $actor, $action) {
            $admin->notify(new AdminTicketClaimNotification($ticket, $actor, $action));      // DB: immediate
            $admin->notify(new AdminTicketClaimEmailNotification($ticket, $actor, $action)); // Email: queued
        });
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
