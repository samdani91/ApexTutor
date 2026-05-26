<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConnectionRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $connections = $request->user()->guardianProfile->connectionRequests()
            ->with(['tutorProfile.user:id,name,avatar'])->paginate(10);
        return response()->json(['success' => true, 'data' => $connections]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tutor_profile_id' => 'required|exists:tutor_profiles,id',
            'requirement_id'   => 'nullable|exists:tuition_requirements,id',
            'guardian_message' => 'nullable|string|max:1000',
        ]);
        $connection = $request->user()->guardianProfile->connectionRequests()->create($data);
        return response()->json(['success' => true, 'data' => $connection, 'message' => 'Connection request sent.'], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $connection = $request->user()->guardianProfile->connectionRequests()->with(['tutorProfile','requirement'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $connection]);
    }
}
