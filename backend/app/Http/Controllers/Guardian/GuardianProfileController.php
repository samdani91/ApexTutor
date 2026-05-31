<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuardianProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->guardianProfile()->with('user:id,name,email,phone,address,avatar')->first();
        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_type'            => 'sometimes|in:guardian,student',
            'occupation'              => 'nullable|string|max:100',
            'relationship_to_student' => 'nullable|string|max:50',
            'nid_number'              => 'nullable|string|max:30',
            // user fields
            'name'    => 'sometimes|string|max:150',
            'phone'   => 'sometimes|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $userFields    = array_intersect_key($data, array_flip(['name', 'phone', 'address']));
        $profileFields = array_diff_key($data, $userFields);

        if ($userFields) {
            $request->user()->update($userFields);
        }
        $request->user()->guardianProfile->update($profileFields);

        $profile = $request->user()->guardianProfile()->with('user:id,name,email,phone,address,avatar')->first();
        return response()->json(['success' => true, 'data' => $profile, 'message' => 'Profile updated.']);
    }

    public function uploadNid(Request $request): JsonResponse
    {
        $request->validate([
            'nid_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $file     = $request->file('nid_document');
        $realMime = mime_content_type($file->getRealPath());

        if (!in_array($realMime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type.'], 422);
        }

        $profile = $request->user()->guardianProfile;

        if ($profile->nid_document) {
            Storage::disk('public')->delete($profile->nid_document);
        }

        // Scope to user subdirectory to prevent enumeration
        $path = $file->store('nid_documents/' . $request->user()->id, 'public');
        $profile->update(['nid_document' => $path]);

        return response()->json([
            'success' => true,
            'data'    => ['nid_document_url' => Storage::disk('public')->url($path)],
            'message' => 'NID document uploaded.',
        ]);
    }

    public function deleteNid(Request $request): JsonResponse
    {
        $profile = $request->user()->guardianProfile;
        if ($profile->nid_document) {
            Storage::disk('public')->delete($profile->nid_document);
            $profile->update(['nid_document' => null]);
        }
        return response()->json(['success' => true, 'message' => 'NID document removed.']);
    }
}
