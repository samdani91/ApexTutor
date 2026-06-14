<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformFeedback;
use App\Notifications\FeedbackStatusChangedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPlatformFeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status   = $request->query('status', 'pending');
        $feedbacks = PlatformFeedback::with('user:id,name,avatar,role')
            ->when($status !== 'all', fn($q) => $q->where('moderation_status', $status))
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $feedbacks]);
    }

    public function approve(int $id): JsonResponse
    {
        $feedback = PlatformFeedback::with('user')->findOrFail($id);
        $feedback->update(['moderation_status' => 'approved', 'show_on_landing' => true]);
        $feedback->user?->notify(new FeedbackStatusChangedNotification('approved'));
        return response()->json(['success' => true, 'message' => 'Feedback approved and added to landing page.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $feedback = PlatformFeedback::with('user')->findOrFail($id);
        $feedback->update(['moderation_status' => 'rejected', 'show_on_landing' => false]);
        $feedback->user?->notify(new FeedbackStatusChangedNotification('rejected'));
        return response()->json(['success' => true, 'message' => 'Feedback rejected.']);
    }
}
