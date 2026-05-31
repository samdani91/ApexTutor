<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private const ALLOWED_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->user()->tutorProfile->documents]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:university_id,nid,ssc_marksheet,hsc_marksheet,emergency_contact_nid',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $file     = $request->file('file');
        $realMime = mime_content_type($file->getRealPath());

        if (!in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, and PNG are accepted.'], 422);
        }

        $path = $file->store('documents', 'public');
        $doc  = $request->user()->tutorProfile->documents()->create([
            'type'      => $request->type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $realMime,
        ]);

        return response()->json(['success' => true, 'data' => $doc, 'message' => 'Document uploaded.'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $doc = $request->user()->tutorProfile->documents()->findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }
}
