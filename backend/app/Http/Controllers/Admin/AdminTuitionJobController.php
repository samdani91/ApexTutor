<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TuitionJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTuitionJobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'   => 'nullable|in:open,closed',
            'q'        => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = TuitionJob::with([
            'district:id,name',
            'area:id,name',
            'subjects:id,name',
            'guardianProfile.user:id,name,email',
        ])
        ->withCount('applications')
        ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->q) {
            $q = '%' . $request->q . '%';
            $query->where(function ($sq) use ($q) {
                $sq->where('title', 'like', $q)
                   ->orWhere('public_id', 'like', $q);
            });
        }

        $jobs = $query->paginate($request->integer('per_page', 15));

        return response()->json(['success' => true, 'data' => $jobs]);
    }

    public function show(string $publicId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)
            ->with(['district:id,name', 'area:id,name', 'subjects:id,name', 'guardianProfile.user:id,name,email'])
            ->withCount('applications')
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $job]);
    }

    public function close(string $publicId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        $job->update(['status' => 'closed']);
        return response()->json(['success' => true, 'message' => 'Job closed.']);
    }

    public function reopen(string $publicId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        $job->update(['status' => 'open']);
        return response()->json(['success' => true, 'message' => 'Job reopened.']);
    }
}
