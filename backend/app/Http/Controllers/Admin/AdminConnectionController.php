<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminConnectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $connections = ConnectionRequest::with(['guardianProfile.user:id,name','tutorProfile.user:id,name'])
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $connections]);
    }

    public function show(int $id): JsonResponse
    {
        $conn = ConnectionRequest::with(['guardianProfile','tutorProfile','requirement'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $conn]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:pending,admin_reviewing,tutor_contacted,connected,declined,closed']);
        $conn = ConnectionRequest::findOrFail($id);
        if ($data['status'] === 'connected') {
            $data['connected_at'] = now();
        }
        $conn->update($data);
        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function addNotes(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['admin_notes' => 'required|string']);
        ConnectionRequest::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'Notes added.']);
    }
}
