<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entries = $request->user()->tutorProfile->educationEntries;
        return response()->json(['success' => true, 'data' => $entries]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'level'          => 'required|in:phd,masters,bachelor,hsc,ssc,o_level,a_level,other',
            'institute_name' => 'required|string|max:255',
            'degree_title'   => 'required|string|max:150',
            'major_group'    => 'nullable|string|max:150',
            'result'         => 'nullable|string|max:100',
            'year_of_passing'=> 'nullable|integer|min:1970|max:2030',
            'is_current'     => 'boolean',
            'sort_order'     => 'integer|min:0',
        ]);
        $entry = $request->user()->tutorProfile->educationEntries()->create($data);
        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Education entry added.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $entry = $request->user()->tutorProfile->educationEntries()->findOrFail($id);
        $data = $request->validate([
            'level'          => 'in:phd,masters,bachelor,hsc,ssc,o_level,a_level,other',
            'institute_name' => 'string|max:255',
            'degree_title'   => 'string|max:150',
            'major_group'    => 'nullable|string|max:150',
            'result'         => 'nullable|string|max:100',
            'year_of_passing'=> 'nullable|integer',
            'is_current'     => 'boolean',
        ]);
        $entry->update($data);
        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Entry updated.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $entry = $request->user()->tutorProfile->educationEntries()->findOrFail($id);
        $entry->delete();
        return response()->json(['success' => true, 'message' => 'Entry deleted.']);
    }
}
