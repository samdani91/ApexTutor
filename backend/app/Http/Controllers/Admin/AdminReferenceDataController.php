<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
