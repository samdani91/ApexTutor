<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['admin', 'super_admin'])
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            )
            ->when($request->sort === 'id_asc', fn($q) => $q->orderBy('id'), fn($q) => $q->orderByDesc('id'));

        return response()->json(['success' => true, 'data' => $query->paginate(10)]);
    }

    public function show(int $id): JsonResponse
    {
        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $admin]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => ['required', 'string', 'max:11', 'unique:users,phone', 'regex:/^01[3-9]\d{8}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $data['role']     = 'super_admin';
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json(['success' => true, 'data' => $user, 'message' => 'Admin account created.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ((int) $request->user()->id !== $id) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own account.'], 403);
        }

        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);

        $data = $request->validate([
            'name'     => 'sometimes|string|max:100',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'phone'    => 'nullable|string|max:20',
            'is_active'=> 'sometimes|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return response()->json(['success' => true, 'data' => $admin->fresh(), 'message' => 'Admin updated.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ((int) $request->user()->id === $id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 422);
        }

        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);
        $admin->tokens()->delete();
        $admin->delete();

        return response()->json(['success' => true, 'message' => 'Admin account deleted.']);
    }
}
