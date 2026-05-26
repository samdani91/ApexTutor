<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionRequirementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reqs = $request->user()->guardianProfile->tuitionRequirements()->with(['subjects','district'])->paginate(10);
        return response()->json(['success' => true, 'data' => $reqs]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_name'          => 'required|string|max:150',
            'medium'                => 'required|in:bangla_medium,english_medium,english_version',
            'class_level'           => 'required|string|max:50',
            'district_id'           => 'nullable|exists:districts,id',
            'city'                  => 'nullable|string|max:150',
            'area'                  => 'nullable|string|max:255',
            'preferred_tutor_gender'=> 'in:male,female,no_preference',
            'days_per_week'         => 'nullable|integer|min:1|max:7',
            'preferred_days'        => 'nullable|array',
            'hours_per_day'         => 'nullable|numeric|min:0.5|max:12',
            'salary_min'            => 'nullable|integer|min:0',
            'salary_max'            => 'nullable|integer|min:0',
            'place_of_tutoring'     => 'nullable|array',
            'special_notes'         => 'nullable|string|max:1000',
            'subject_ids'           => 'nullable|array',
            'subject_ids.*'         => 'exists:subjects,id',
        ]);
        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);
        $req = $request->user()->guardianProfile->tuitionRequirements()->create($data);
        if ($subjectIds) {
            $req->subjects()->sync($subjectIds);
        }
        return response()->json(['success' => true, 'data' => $req->load(['subjects','district']), 'message' => 'Requirement posted.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $req = $request->user()->guardianProfile->tuitionRequirements()->findOrFail($id);
        $data = $request->validate(['status' => 'in:open,in_progress,connected,closed', 'special_notes' => 'nullable|string']);
        $req->update($data);
        return response()->json(['success' => true, 'data' => $req, 'message' => 'Updated.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->guardianProfile->tuitionRequirements()->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }
}
