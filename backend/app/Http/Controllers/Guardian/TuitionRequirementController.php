<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionRequirementController extends Controller
{
    // Valid transitions: only these moves are allowed
    private const STATUS_TRANSITIONS = [
        'open'        => ['in_progress', 'closed'],
        'in_progress' => ['confirmed', 'closed'],
        'confirmed'   => ['closed'],
        'closed'      => [],
    ];

    public function index(Request $request): JsonResponse
    {
        $reqs = $request->user()->guardianProfile->tuitionRequirements()->with(['subjects', 'district'])->paginate(10);
        return response()->json(['success' => true, 'data' => $reqs]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_name'           => 'required|string|max:150',
            'medium'                 => 'required|in:bangla_medium,english_medium,english_version',
            'class_level'            => 'required|string|max:50',
            'district_id'            => 'nullable|exists:districts,id',
            'city'                   => 'nullable|string|max:150',
            'area'                   => 'nullable|string|max:255',
            'preferred_tutor_gender' => 'nullable|in:male,female,no_preference',
            'days_per_week'          => 'nullable|integer|min:1|max:7',
            'preferred_days'         => 'nullable|array',
            'hours_per_day'          => 'nullable|numeric|min:0.5|max:12',
            'salary_min'             => 'nullable|integer|min:0',
            'salary_max'             => 'nullable|integer|min:0',
            'place_of_tutoring'      => 'nullable|array',
            'special_notes'          => 'nullable|string|max:1000',
            'subject_ids'            => 'nullable|array',
            'subject_ids.*'          => 'exists:subjects,id',
        ]);

        // Prevent duplicate open requirements for the same student + class
        $duplicate = $request->user()->guardianProfile->tuitionRequirements()
            ->where('student_name', $data['student_name'])
            ->where('class_level', $data['class_level'])
            ->whereIn('status', ['open', 'in_progress'])
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'An active requirement for this student and class already exists.',
            ], 409);
        }

        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        $req = $request->user()->guardianProfile->tuitionRequirements()->create($data);
        if ($subjectIds) {
            $req->subjects()->sync($subjectIds);
        }

        return response()->json([
            'success' => true,
            'data'    => $req->load(['subjects', 'district']),
            'message' => 'Requirement posted.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $req  = $request->user()->guardianProfile->tuitionRequirements()->findOrFail($id);
        $data = $request->validate([
            'status'        => 'sometimes|in:open,in_progress,connected,closed',
            'special_notes' => 'nullable|string|max:1000',
        ]);

        // Enforce valid status transitions
        if (isset($data['status']) && $data['status'] !== $req->status) {
            $allowed = self::STATUS_TRANSITIONS[$req->status] ?? [];
            if (!in_array($data['status'], $allowed, true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot transition from '{$req->status}' to '{$data['status']}'.",
                ], 422);
            }
        }

        $req->update($data);
        return response()->json(['success' => true, 'data' => $req, 'message' => 'Updated.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->guardianProfile->tuitionRequirements()->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }
}
