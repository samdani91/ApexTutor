<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allTypes = [
            // tutor
            'tutor_shortlisted', 'tutor_removed_from_shortlist',
            'connection_requested_tutor', 'tutor_contacted', 'tuition_confirmed',
            'pending_change_approved', 'pending_change_rejected',
            'video_reviewed', 'profile_edited_by_admin',
            'tutor_verified', 'tutor_verification_rejected',
            // guardian
            'connection_status_changed', 'review_approved', 'review_rejected',
            // shared
            'ticket_status_updated', 'ticket_replied',
        ];

        $request->validate([
            'page' => 'nullable|integer|min:1',
            'type' => 'nullable|in:' . implode(',', $allTypes),
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

        $paginated = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $paginated->getCollection()->map(fn($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ]),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
            'unread'  => $request->user()->unreadNotifications()->count(),
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
