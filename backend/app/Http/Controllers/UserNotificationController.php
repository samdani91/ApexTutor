<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page'    => 'nullable|integer|min:1',
            'type'    => 'nullable|in:approved,rejected,shortlisted',
            'sort'    => 'nullable|in:newest,oldest',
        ]);

        $typeMap = [
            'approved'    => 'pending_change_approved',
            'rejected'    => 'pending_change_rejected',
            'shortlisted' => 'tutor_shortlisted',
        ];

        $query = $request->user()->notifications();

        if ($request->type && isset($typeMap[$request->type])) {
            $query->where('data->type', $typeMap[$request->type]);
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
