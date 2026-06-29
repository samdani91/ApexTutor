<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private const ALLOWED_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    // university_id removed from platform — only school/college-level documents accepted
    private const ALLOWED_DOC_TYPES = ['nid', 'ssc_marksheet', 'hsc_marksheet', 'emergency_contact_nid'];

    public function __construct(private readonly PendingProfileChangeService $pending) {}

    public function index(Request $request): JsonResponse
    {
        $documents = $request->user()->tutorProfile->documents->map(function ($doc) {
            $doc->file_url = rtrim(config('app.url'), '/') . '/private-storage/' . strtr(base64_encode($doc->file_path), '+/', '-_');
            return $doc;
        });
        return response()->json(['success' => true, 'data' => $documents]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', self::ALLOWED_DOC_TYPES),
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $file     = $request->file('file');
        $realMime = mime_content_type($file->getRealPath());

        if (!in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, and PNG are accepted.'], 422);
        }

        $path    = $file->store('documents', 'public');
        $profile = $request->user()->tutorProfile;
        $payload = [
            'type'      => $request->type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $realMime,
        ];

        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->stageDocumentUpsert($profile, $request->type, $payload);
            return response()->json([
                'success' => true,
                'pending' => true,
                'data'    => $payload + ['id' => null, 'review_status' => 'pending_review'],
                'message' => 'Document uploaded — pending admin review.',
            ], 202);
        }

        $existing = $profile->documents()->where('type', $request->type)->get();
        foreach ($existing as $doc) {
            Storage::disk('public')->delete($doc->file_path);
            $doc->delete();
        }

        $doc = $profile->documents()->create($payload);
        $doc->file_url = rtrim(config('app.url'), '/') . '/private-storage/' . strtr(base64_encode($doc->file_path), '+/', '-_');

        return response()->json(['success' => true, 'data' => $doc, 'message' => 'Document uploaded.'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->tutorProfile;
        $doc     = $profile->documents()->findOrFail($id);

        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->stageDocumentDelete($profile, $doc->id);
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Document removal saved — pending admin review.']);
        }

        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }
}
