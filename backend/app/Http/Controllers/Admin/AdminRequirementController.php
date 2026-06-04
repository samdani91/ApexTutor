<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TuitionRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRequirementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TuitionRequirement::with([
            'guardianProfile.user:id,name,email',
            'subjects:id,name',
            'district:id,name',
        ])
        ->when($request->search, function ($q, $search) {
            $q->where('student_name', 'like', "%{$search}%")
              ->orWhereHas('guardianProfile.user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
        })
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->when($request->class_level, fn($q) => $q->where('class_level', $request->class_level))
        ->orderByDesc('id');

        return response()->json(['success' => true, 'data' => $query->paginate($request->integer('per_page', 10))]);
    }

    public function show(int $id): JsonResponse
    {
        $req = TuitionRequirement::with([
            'guardianProfile.user',
            'subjects',
            'district',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $req]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:open,matched,closed']);
        TuitionRequirement::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}
