<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminChangeRequestController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = ProfileChangeRequest::with('tutorProfile.user:id,name,email')
            ->whereIn('status', ['pending', 'review_pending'])
            ->latest()
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $requests]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $req = ProfileChangeRequest::findOrFail($id);

        if ($req->status === 'review_pending') {
            // Tutor finished editing — re-verify the profile and clear the request
            $req->tutorProfile->update([
                'is_verified'         => true,
                'verification_status' => 'approved',
                'verified_at'         => now(),
                'verified_by'         => $request->user()->id,
            ]);
            $req->tutorProfile->documents()->update([
                'review_status' => 'approved',
                'reviewed_by'   => $request->user()->id,
                'reviewed_at'   => now(),
            ]);
            $req->delete();
            return response()->json(['success' => true, 'message' => 'Profile re-verified successfully.']);
        }

        // Initial unlock approval
        $req->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        $req->tutorProfile->update([
            'is_verified'         => false,
            'verification_status' => 'pending',
            'verified_at'         => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Request approved. Profile is now unlocked.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['admin_note' => 'required|string|max:500']);
        $req  = ProfileChangeRequest::findOrFail($id);
        $req->update([
            'status'      => 'rejected',
            'admin_note'  => $data['admin_note'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Request rejected.']);
    }
}
