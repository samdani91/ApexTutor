<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\Subject;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminReferenceDataController extends Controller
{
    // ── Subjects ──────────────────────────────────────────────────────────

    public function subjects(Request $request): JsonResponse
    {
        $subjects = Subject::when($request->class_level, fn($q) => $q->where('class_level', $request->class_level))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('class_level')->orderBy('name')
            ->paginate($request->integer('per_page', 10));
        return response()->json(['success' => true, 'data' => $subjects]);
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'name_bn'     => 'nullable|string|max:100',
            'class_level' => 'required|string|max:50',
            'medium'      => 'nullable|string|max:20',
        ]);
        $subject = Subject::create($data);
        return response()->json(['success' => true, 'data' => $subject, 'message' => 'Subject created.'], 201);
    }

    public function updateSubject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'name_bn'     => 'nullable|string|max:100',
            'class_level' => 'sometimes|string|max:50',
            'medium'      => 'nullable|string|max:20',
        ]);
        Subject::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'Subject updated.']);
    }

    public function destroySubject(int $id): JsonResponse
    {
        Subject::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Subject deleted.']);
    }

    // ── Districts ─────────────────────────────────────────────────────────

    public function districts(Request $request): JsonResponse
    {
        $districts = District::with('areas')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $districts]);
    }

    public function storeDistrict(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100|unique:districts,name',
            'name_bn'  => 'nullable|string|max:100',
            'division' => 'nullable|string|max:50',
        ]);
        $district = District::create($data);
        return response()->json(['success' => true, 'data' => $district, 'message' => 'District created.'], 201);
    }

    public function updateDistrict(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:100|unique:districts,name,' . $id,
            'name_bn'  => 'nullable|string|max:100',
            'division' => 'nullable|string|max:50',
        ]);
        District::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'District updated.']);
    }

    public function destroyDistrict(int $id): JsonResponse
    {
        District::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'District deleted.']);
    }

    // ── Areas ─────────────────────────────────────────────────────────────

    public function storeArea(Request $request): JsonResponse
    {
        $data = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name'        => 'required|string|max:100',
        ]);
        $area = Area::create($data);
        return response()->json(['success' => true, 'data' => $area, 'message' => 'Area created.'], 201);
    }

    public function updateArea(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        Area::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'Area updated.']);
    }

    public function destroyArea(int $id): JsonResponse
    {
        Area::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Area deleted.']);
    }

    // ── Universities ──────────────────────────────────────────────────────

    public function universities(Request $request): JsonResponse
    {
        $universities = University::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->district, fn($q, $d) => $q->where('district', $d))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->orderByRaw("FIELD(type,'public','private')")
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));
        return response()->json(['success' => true, 'data' => $universities]);
    }

    public function storeUniversity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255|unique:universities,name',
            'district' => 'required|string|max:100',
            'type'     => 'required|in:public,private',
        ]);
        $university = University::create($data);
        return response()->json(['success' => true, 'data' => $university, 'message' => 'University added.'], 201);
    }

    public function updateUniversity(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255|unique:universities,name,' . $id,
            'district' => 'sometimes|string|max:100',
            'type'     => 'sometimes|in:public,private',
        ]);
        University::findOrFail($id)->update($data);
        return response()->json(['success' => true, 'message' => 'University updated.']);
    }

    public function destroyUniversity(int $id): JsonResponse
    {
        $university = University::findOrFail($id);
        if ($university->logo) {
            Storage::disk('public')->delete($university->logo);
        }
        $university->delete();
        return response()->json(['success' => true, 'message' => 'University deleted.']);
    }

    public function uploadLogo(Request $request, int $id): JsonResponse
    {
        $request->validate(['logo' => 'required|image|mimes:jpeg,png,webp|max:512']);
        $university = University::findOrFail($id);

        if ($university->logo) {
            Storage::disk('public')->delete($university->logo);
        }

        $path = $request->file('logo')->store("university-logos/{$id}", 'public');
        $university->update(['logo' => $path]);

        return response()->json(['success' => true, 'logo_url' => Storage::disk('public')->url($path)]);
    }

    public function removeLogo(int $id): JsonResponse
    {
        $university = University::findOrFail($id);
        if ($university->logo) {
            Storage::disk('public')->delete($university->logo);
            $university->update(['logo' => null]);
        }
        return response()->json(['success' => true, 'message' => 'Logo removed.']);
    }
}
