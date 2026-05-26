<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelAvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->user()->tutorProfile->travelAvailabilities()->with('district')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'district_id'    => 'required|exists:districts,id',
            'from_date'      => 'required|date|after_or_equal:today',
            'to_date'        => 'required|date|after:from_date',
            'open_to_tuitions' => 'boolean',
            'notes'          => 'nullable|string|max:500',
        ]);
        $entry = $request->user()->tutorProfile->travelAvailabilities()->create($data);
        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Travel availability added.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $entry = $request->user()->tutorProfile->travelAvailabilities()->findOrFail($id);
        $data = $request->validate([
            'from_date'        => 'date',
            'to_date'          => 'date|after:from_date',
            'open_to_tuitions' => 'boolean',
            'notes'            => 'nullable|string|max:500',
        ]);
        $entry->update($data);
        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Updated.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->tutorProfile->travelAvailabilities()->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }
}
