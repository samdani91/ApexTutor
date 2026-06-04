<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function __construct(private readonly PendingProfileChangeService $pending) {}

    private array $fields = [
        'level', 'institute_name', 'degree_title', 'major_group', 'result',
        'year_of_passing', 'is_current', 'sort_order',
    ];

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
            'year_of_passing'=> 'nullable|integer|min:1970|max:' . date('Y'),
            'is_current'     => 'boolean',
            'sort_order'     => 'integer|min:0',
        ]);
        $profile = $request->user()->tutorProfile;
        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->queueEducationChange($profile, 'create', null, $data);
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Education change saved — pending admin review.'], 202);
        }

        $entry = $profile->educationEntries()->create($data);
        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Education entry added.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->tutorProfile;
        $entry = $profile->educationEntries()->findOrFail($id);
        $data = $request->validate([
            'level'          => 'in:phd,masters,bachelor,hsc,ssc,o_level,a_level,other',
            'institute_name' => 'string|max:255',
            'degree_title'   => 'string|max:150',
            'major_group'    => 'nullable|string|max:150',
            'result'         => 'nullable|string|max:100',
            'year_of_passing'=> 'nullable|integer|min:1970|max:' . date('Y'),
            'is_current'     => 'boolean',
        ]);
        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->queueEducationChange($profile, 'update', $entry->id, array_merge(
                $entry->only($this->fields),
                $data
            ));
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Education change saved — pending admin review.']);
        }

        $entry->update($data);
        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Entry updated.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->tutorProfile;
        $entry = $profile->educationEntries()->findOrFail($id);
        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->queueEducationChange($profile, 'delete', $entry->id, $entry->only($this->fields));
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Education removal saved — pending admin review.']);
        }

        $entry->delete();
        return response()->json(['success' => true, 'message' => 'Entry deleted.']);
    }

}
