<?php
namespace App\Http\Controllers;

use App\Models\PlatformFeedback;
use App\Models\User;
use App\Notifications\AdminNewFeedbackNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformFeedbackController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $feedback = PlatformFeedback::where('user_id', $request->user()->id)->first();
        return response()->json(['success' => true, 'data' => $feedback]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['quote' => 'required|string|min:20|max:500']);

        $user  = $request->user();
        $label = $this->resolveLabel($user);

        $feedback = PlatformFeedback::updateOrCreate(
            ['user_id' => $user->id],
            [
                'quote'             => $data['quote'],
                'display_label'     => $label,
                'moderation_status' => 'pending',
                'show_on_landing'   => false,
            ]
        );

        $notification = new AdminNewFeedbackNotification(
            userName:     $user->name,
            userRole:     $user->role,
            displayLabel: $label,
            quote:        $data['quote'],
            feedbackId:   $feedback->id,
        );

        User::where('role', 'super_admin')->get()->each(
            fn(User $admin) => $admin->notify($notification)
        );

        return response()->json([
            'success' => true,
            'data'    => $feedback,
            'message' => 'Thank you for your feedback! It will appear after admin review.',
        ]);
    }

    private function resolveLabel(\App\Models\User $user): string
    {
        if ($user->role === 'tutor') {
            $entry = $user->tutorProfile?->educationEntries()
                ->whereNotNull('university_id')
                ->with('university:id,name')
                ->orderByRaw("FIELD(level, 'phd', 'masters', 'bachelor')")
                ->first();
            $uni = $entry?->university?->name;
            return $uni ? "Tutor, {$uni}" : 'Tutor';
        }

        return ucfirst($user->role); // 'Guardian' or 'Student'
    }
}
