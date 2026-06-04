<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AdminActivityLog::with('admin:id,name,email,role')
            ->when($request->search, fn($q, $s) =>
                $q->where('description', 'like', "%{$s}%")
                  ->orWhereHas('admin', fn($uq) => $uq->where('name', 'like', "%{$s}%"))
            )
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->admin_id, fn($q) => $q->where('admin_id', $request->admin_id))
            ->when($request->target_type, fn($q) => $q->where('target_type', $request->target_type))
            ->orderByDesc('created_at');

        return response()->json(['success' => true, 'data' => $query->paginate($request->integer('per_page', 10))]);
    }

    public function actions(): JsonResponse
    {
        $actions = AdminActivityLog::distinct()->pluck('action')->sort()->values();
        return response()->json(['success' => true, 'data' => $actions]);
    }
}
