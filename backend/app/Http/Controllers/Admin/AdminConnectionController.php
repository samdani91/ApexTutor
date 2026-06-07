<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Notifications\ConnectionStatusChangedNotification;
use App\Notifications\ConnectionConfirmedTutorNotification;
use App\Notifications\TutorContactedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminConnectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $connections = ConnectionRequest::with(['guardianProfile.user:id,name','tutorProfile.user:id,name'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('guardianProfile.user', fn($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tutorProfile.user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10));
        return response()->json(['success' => true, 'data' => $connections]);
    }

    public function show(int $id): JsonResponse
    {
        $conn = ConnectionRequest::with([
            'guardianProfile.user:id,name,email,phone',
            'tutorProfile.user:id,name,email,phone',
        ])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $conn]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:pending,admin_reviewing,tutor_contacted,confirmed,declined,closed']);
        $conn = ConnectionRequest::with([
            'guardianProfile.user:id,name,email',
            'tutorProfile.user:id,name,email',
        ])->findOrFail($id);

        if ($data['status'] === 'confirmed') {
            $data['confirmed_at'] = now();
        }

        $oldStatus = $conn->status;
        $conn->update($data);

        $notifiableStatuses = ['admin_reviewing', 'tutor_contacted', 'confirmed', 'declined', 'closed'];
        if ($data['status'] !== $oldStatus && in_array($data['status'], $notifiableStatuses)) {
            try {
                $tutorName    = $conn->tutorProfile?->user?->name ?? 'your tutor';
                $guardianName = $conn->guardianProfile?->user?->name ?? 'the guardian';
                $guardianUser = $conn->guardianProfile?->user;
                $tutorUser    = $conn->tutorProfile?->user;

                if ($guardianUser) {
                    $guardianUser->notify(new ConnectionStatusChangedNotification(
                        status:    $data['status'],
                        tutorName: $tutorName,
                    ));
                }

                if ($data['status'] === 'confirmed' && $tutorUser) {
                    $tutorUser->notify(new ConnectionConfirmedTutorNotification(
                        guardianName: $guardianName,
                    ));
                }
                if ($data['status'] === 'tutor_contacted' && $tutorUser) {
                    $tutorUser->notify(new TutorContactedNotification(
                        guardianName: $guardianName,
                    ));
                }
            } catch (\Exception $e) {
                Log::error('Connection status notification failed', ['error' => $e->getMessage(), 'connection' => $id]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function addNotes(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['admin_notes' => 'required|string|max:2000']);
        ConnectionRequest::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'Notes added.']);
    }
}
