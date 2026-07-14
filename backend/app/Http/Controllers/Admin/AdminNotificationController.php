<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $adminTypes = ['tutor_shortlisted','connection_requested','pending_profile_change','pending_video','review_submitted','new_support_ticket','ticket_claim_update','new_tuition_job','referral_code_used'];
        $request->validate([
            'type' => 'nullable|in:' . implode(',', $adminTypes),
            'sort' => 'nullable|in:newest,oldest',
        ]);

        $query = $request->user()->notifications();

        if ($request->type) {
            $query->where('data->type', $request->type);
        }

        if ($request->sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $paginated = $query->paginate($request->integer('per_page', 10), ['*'], 'page', $request->integer('page', 1));

        $items = collect($paginated->items())->map(fn($n) => [
            'id'         => $n->id,
            'data'       => $n->data,
            'read_at'    => $n->read_at,
            'created_at' => $n->created_at,
        ]);

        return response()->json([
            'success'      => true,
            'data'         => $items,
            'meta'         => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
            'unread'       => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
