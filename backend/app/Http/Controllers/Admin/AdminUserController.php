<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['admin', 'super_admin'])
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            )
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->sort === 'id_asc', fn($q) => $q->orderBy('id'), fn($q) => $q->orderByDesc('id'));

        return response()->json(['success' => true, 'data' => $query->paginate(10)]);
    }

    public function show(int $id): JsonResponse
    {
        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $admin]);
    }
}
