<?php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\AdminNewTicketNotification;
use App\Notifications\AdminNewTicketEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TicketController extends Controller
{
    public function counts(): JsonResponse
    {
        $count = SupportTicket::where('user_id', Auth::id())
            ->whereNotIn('status', ['closed'])
            ->count();

        return response()->json(['success' => true, 'data' => ['active' => $count]]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::where('user_id', Auth::id())
            ->with(['replies' => fn($q) => $q->where('is_internal', false)->with('user:id,name,role')])
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(10);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject'     => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'category'    => 'required|in:account,technical,tuition,other',
        ]);

        $ticket = SupportTicket::create([
            ...$data,
            'user_id'       => Auth::id(),
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'status'        => 'open',
            'priority'      => 'low',
        ]);

        $ticket->load('user:id,name,email');

        User::where('role', 'super_admin')->each(function ($admin) use ($ticket) {
            $admin->notify(new AdminNewTicketNotification($ticket));      // DB: immediate
            $admin->notify(new AdminNewTicketEmailNotification($ticket)); // Email: queued
        });

        return response()->json(['success' => true, 'data' => $ticket], 201);
    }

    public function show(int $id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', Auth::id())
            ->with([
                'replies' => fn($q) => $q->where('is_internal', false)->with('user:id,name,role'),
            ])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $ticket]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', Auth::id())->findOrFail($id);

        if ($ticket->status === 'closed') {
            return response()->json(['success' => false, 'message' => 'Closed tickets cannot receive new replies.'], 422);
        }

        $data = $request->validate(['body' => 'required|string|max:5000']);

        $reply = TicketReply::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'body'        => $data['body'],
            'is_internal' => false,
        ]);

        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'in_progress', 'resolved_at' => null]);
        }

        $reply->load('user:id,name,role');

        return response()->json(['success' => true, 'data' => $reply], 201);
    }
}
