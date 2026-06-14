<?php
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminPendingAvatarController;
use App\Http\Controllers\Admin\AdminPendingChangesController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\AdminUserAvatarController;
use App\Http\Controllers\Admin\AdminReferenceDataController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminConnectionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminGuardianController;
use App\Http\Controllers\Admin\AdminPlatformFeedbackController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminTutorController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Guardian\ConnectionRequestController;
use App\Http\Controllers\Guardian\GuardianProfileController;
use App\Http\Controllers\Guardian\GuardianReviewController;
use App\Http\Controllers\Guardian\ShortlistController;
use App\Http\Controllers\Public\TutorPublicProfileController;
use App\Http\Controllers\Public\TutorSearchController;
use App\Http\Controllers\PlatformFeedbackController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Tutor\DocumentController;
use App\Http\Controllers\Tutor\EducationController;
use App\Http\Controllers\Tutor\TeachingVideoController;
use App\Http\Controllers\Tutor\TutorEmergencyContactController;
use App\Http\Controllers\Tutor\TutorPersonalInfoController;
use App\Http\Controllers\Tutor\TravelAvailabilityController;
use App\Http\Controllers\Tutor\TuitionPreferenceController;
use App\Http\Controllers\Tutor\TutorProfileController;
use App\Http\Controllers\Tutor\TutorReviewController;
use App\Http\Controllers\User\AvatarController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;

// Health check — no auth, no throttle
Route::get('health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]));

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login',    [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('verify-email',        [AuthController::class, 'verifyEmail'])->middleware('throttle:10,1');
    Route::post('resend-verification', [AuthController::class, 'resendVerification'])->middleware('throttle:5,1');
    Route::post('forgot-password',     [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('verify-reset-otp',    [AuthController::class, 'verifyResetOtp'])->middleware('throttle:10,1');
    Route::post('reset-password',      [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    Route::post('otp/send',   [OtpController::class, 'send'])->middleware('throttle:5,1');
    Route::post('otp/verify', [OtpController::class, 'verify'])->middleware('throttle:10,1');
    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// User account — any authenticated user
Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
    Route::post('user/avatar',   [AvatarController::class,      'store']);
    Route::put('user/profile',   [UserProfileController::class,  'update']);

    // Password change (2-step: request OTP → confirm)
    Route::post('user/password/request-change', [UserProfileController::class, 'requestPasswordChange'])
        ->middleware('throttle:3,1');
    Route::put('user/password', [UserProfileController::class, 'changePassword'])
        ->middleware('throttle:5,1');

    // Email change (2-step: request OTP → confirm)
    Route::post('user/email/request-change', [UserProfileController::class, 'requestEmailChange'])
        ->middleware('throttle:3,1');
    Route::put('user/email/confirm', [UserProfileController::class, 'confirmEmailChange'])
        ->middleware('throttle:5,1');

    // Notifications (tutors & guardians)
    Route::get('notifications',             [UserNotificationController::class, 'index']);
    Route::put('notifications/read-all',    [UserNotificationController::class, 'markAllRead']);
    Route::put('notifications/{id}/read',   [UserNotificationController::class, 'markRead']);

    // Support tickets (tutors & guardians)
    Route::get('tickets/counts',      [TicketController::class, 'counts']);
    Route::get('tickets',             [TicketController::class, 'index']);
    Route::post('tickets',            [TicketController::class, 'store']);
    Route::get('tickets/{id}',        [TicketController::class, 'show']);
    Route::post('tickets/{id}/reply', [TicketController::class, 'reply']);
});

// Public search & tutor profiles
Route::middleware('throttle:60,1')->group(function () {
    Route::get('landing/stats',        [TutorSearchController::class, 'landingStats']);
    Route::get('landing/testimonials', [TutorSearchController::class, 'landingTestimonials']);
    Route::prefix('search')->group(function () {
        Route::get('tutors',    [TutorSearchController::class, 'search']);
        Route::get('resolve',   [TutorSearchController::class, 'resolve']);
        Route::get('subjects',      [TutorSearchController::class, 'subjects']);
        Route::get('districts',     [TutorSearchController::class, 'districts']);
        Route::get('areas',         [TutorSearchController::class, 'areas']);
        Route::get('universities',  [TutorSearchController::class, 'universities']);
    });
    Route::get('tutors/{publicId}',         [TutorPublicProfileController::class, 'show']);
    Route::get('tutors/{publicId}/reviews', [TutorPublicProfileController::class, 'reviews']);
});

// Tutor routes
Route::middleware(['auth:sanctum', 'active.user', 'verified', 'role:tutor'])->prefix('tutor')->group(function () {
    Route::get('profile',   [TutorProfileController::class, 'show']);
    Route::put('profile',   [TutorProfileController::class, 'update']);
    Route::get('dashboard',             [TutorProfileController::class, 'dashboard']);
    Route::get('confirmed-tuitions',    [TutorProfileController::class, 'confirmedTuitions']);
    Route::apiResource('education', EducationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('preferences', [TuitionPreferenceController::class, 'show']);
    Route::put('preferences', [TuitionPreferenceController::class, 'upsert']);
    Route::get('documents',        [DocumentController::class, 'index']);
    Route::post('documents',       [DocumentController::class, 'store']);
    Route::delete('documents/{id}', [DocumentController::class, 'destroy']);
    Route::get('personal-info',  [TutorPersonalInfoController::class, 'show']);
    Route::put('personal-info',  [TutorPersonalInfoController::class, 'upsert']);
    Route::get('emergency-contact', [TutorEmergencyContactController::class, 'show']);
    Route::put('emergency-contact', [TutorEmergencyContactController::class, 'upsert']);
    Route::get('videos',           [TeachingVideoController::class, 'index']);
    Route::post('videos',          [TeachingVideoController::class, 'store']);
    Route::put('videos/{id}',      [TeachingVideoController::class, 'update']);
    Route::delete('videos/{id}',   [TeachingVideoController::class, 'destroy']);
    Route::apiResource('travel', TravelAvailabilityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('reviews', [TutorReviewController::class, 'index']);
});

// Platform feedback — any verified authenticated user
Route::middleware(['auth:sanctum', 'active.user', 'verified'])->group(function () {
    Route::get('feedback/platform',  [PlatformFeedbackController::class, 'show']);
    Route::post('feedback/platform', [PlatformFeedbackController::class, 'store']);
});

// Guardian routes
Route::middleware(['auth:sanctum', 'active.user', 'verified', 'role:guardian,student'])->prefix('guardian')->group(function () {
    Route::get('profile',    [GuardianProfileController::class, 'show']);
    Route::put('profile',    [GuardianProfileController::class, 'update']);
    Route::post('profile/nid',   [GuardianProfileController::class, 'uploadNid']);
    Route::delete('profile/nid', [GuardianProfileController::class, 'deleteNid']);
    Route::get('connections',              [ConnectionRequestController::class, 'index']);
    Route::post('connections',             [ConnectionRequestController::class, 'store']);
    Route::get('connections/{id}',         [ConnectionRequestController::class, 'show']);
    Route::get('confirmed-tuitions',       [ConnectionRequestController::class, 'confirmed']);
    Route::get('shortlist',    [ShortlistController::class, 'index']);
    Route::post('shortlist/{tutor_profile_id}',   [ShortlistController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::delete('shortlist/{tutor_profile_id}', [ShortlistController::class, 'destroy'])
        ->middleware('throttle:10,1');
    Route::get('reviews/eligibility/{tutor_profile_id}', [GuardianReviewController::class, 'eligibility']);
    Route::post('reviews', [GuardianReviewController::class, 'store'])->middleware('throttle:5,1');
});

// Admin routes
Route::middleware(['auth:sanctum', 'active.user', 'role:super_admin', 'log.admin'])->prefix('admin')->group(function () {
    Route::get('dashboard',           [AdminDashboardController::class, 'index']);
    Route::get('dashboard/analytics', [AdminDashboardController::class, 'analytics']);

    // Admin user management
    Route::get('admins',            [AdminUserController::class, 'index']);
    Route::get('admins/{id}',       [AdminUserController::class, 'show']);
    Route::post('admins',           [AdminUserController::class, 'store']);
    Route::put('admins/{id}',       [AdminUserController::class, 'update']);

    Route::get('tutors',             [AdminTutorController::class, 'index']);
    Route::get('tutors/{tutorId}',                             [AdminTutorController::class, 'show']);
    Route::put('tutors/{tutorId}',                             [AdminTutorController::class, 'update']);
    Route::put('tutors/{tutorId}/status',                      [AdminTutorController::class, 'updateStatus']);
    Route::post('tutors/{tutorId}/documents',                  [AdminTutorController::class, 'uploadDocument']);
    Route::delete('tutors/{tutorId}/documents/{docId}',        [AdminTutorController::class, 'deleteDocument']);
    Route::put('tutors/{tutorId}/videos/{videoId}',            [AdminTutorController::class, 'updateVideo']);
    Route::put('tutors/{tutorId}/videos/{videoId}/review',     [AdminTutorController::class, 'reviewVideo']);
    Route::delete('tutors/{tutorId}/videos/{videoId}',         [AdminTutorController::class, 'deleteVideo']);

    Route::get('guardians',                        [AdminGuardianController::class, 'index']);
    Route::get('guardians/{guardianId}',           [AdminGuardianController::class, 'show']);
    Route::put('guardians/{guardianId}',           [AdminGuardianController::class, 'update']);
    Route::put('guardians/{guardianId}/status',    [AdminGuardianController::class, 'updateStatus']);
    Route::post('guardians/{guardianId}/nid',      [AdminGuardianController::class, 'uploadNid']);
    Route::delete('guardians/{guardianId}/nid',    [AdminGuardianController::class, 'deleteNid']);

    Route::get('verifications',              [AdminVerificationController::class, 'queue']);
    Route::put('verifications/{id}/approve', [AdminVerificationController::class, 'approve']);
    Route::put('verifications/{id}/reject',  [AdminVerificationController::class, 'reject']);

    Route::get('connections',              [AdminConnectionController::class, 'index']);
    Route::get('connections/{id}',         [AdminConnectionController::class, 'show']);
    Route::put('connections/{id}/status',  [AdminConnectionController::class, 'updateStatus']);
    Route::post('connections/{id}/notes',  [AdminConnectionController::class, 'addNotes']);

    Route::get('pending-changes',              [AdminPendingChangesController::class, 'index']);
    Route::put('pending-changes/{id}/approve', [AdminPendingChangesController::class, 'approve']);
    Route::put('pending-changes/{id}/reject',  [AdminPendingChangesController::class, 'reject']);

    Route::get('pending-avatars',              [AdminPendingAvatarController::class, 'index']);
    Route::put('pending-avatars/{id}/approve', [AdminPendingAvatarController::class, 'approve']);
    Route::put('pending-avatars/{id}/reject',  [AdminPendingAvatarController::class, 'reject']);

    // Admin direct avatar management (replace / remove on profile views)
    Route::post('users/{id}/avatar',   [AdminUserAvatarController::class, 'replace']);
    Route::delete('users/{id}/avatar', [AdminUserAvatarController::class, 'remove']);

    Route::get('reviews',             [AdminReviewController::class, 'all']);
    Route::get('reviews/pending',     [AdminReviewController::class, 'pending']);
    Route::put('reviews/{id}/approve',[AdminReviewController::class, 'approve']);
    Route::put('reviews/{id}/reject', [AdminReviewController::class, 'reject']);

    Route::get('feedback',                  [AdminPlatformFeedbackController::class, 'index']);
    Route::get('feedback/user/{userId}',    [AdminPlatformFeedbackController::class, 'forUser']);
    Route::put('feedback/{id}/approve',     [AdminPlatformFeedbackController::class, 'approve']);
    Route::put('feedback/{id}/reject',      [AdminPlatformFeedbackController::class, 'reject']);

    Route::get('reference/subjects',              [AdminReferenceDataController::class, 'subjects']);
    Route::post('reference/subjects',             [AdminReferenceDataController::class, 'storeSubject']);
    Route::put('reference/subjects/{id}',         [AdminReferenceDataController::class, 'updateSubject']);
    Route::delete('reference/subjects/{id}',      [AdminReferenceDataController::class, 'destroySubject']);
    Route::get('reference/districts',             [AdminReferenceDataController::class, 'districts']);
    Route::post('reference/districts',            [AdminReferenceDataController::class, 'storeDistrict']);
    Route::put('reference/districts/{id}',        [AdminReferenceDataController::class, 'updateDistrict']);
    Route::delete('reference/districts/{id}',     [AdminReferenceDataController::class, 'destroyDistrict']);
    Route::post('reference/areas',                [AdminReferenceDataController::class, 'storeArea']);
    Route::put('reference/areas/{id}',            [AdminReferenceDataController::class, 'updateArea']);
    Route::delete('reference/areas/{id}',         [AdminReferenceDataController::class, 'destroyArea']);
    Route::get('reference/universities',                    [AdminReferenceDataController::class, 'universities']);
    Route::post('reference/universities',                   [AdminReferenceDataController::class, 'storeUniversity']);
    Route::put('reference/universities/{id}',               [AdminReferenceDataController::class, 'updateUniversity']);
    Route::delete('reference/universities/{id}',            [AdminReferenceDataController::class, 'destroyUniversity']);
    Route::post('reference/universities/{id}/logo',         [AdminReferenceDataController::class, 'uploadLogo']);
    Route::delete('reference/universities/{id}/logo',       [AdminReferenceDataController::class, 'removeLogo']);

    Route::get('audit-log',         [AdminAuditLogController::class, 'index']);
    Route::get('audit-log/actions', [AdminAuditLogController::class, 'actions']);

    Route::get('notifications',             [AdminNotificationController::class, 'index']);
    Route::put('notifications/read-all',    [AdminNotificationController::class, 'markAllRead']);
    Route::put('notifications/{id}/read',   [AdminNotificationController::class, 'markRead']);

    Route::get('tickets',                    [AdminTicketController::class, 'index']);
    Route::get('tickets/counts',             [AdminTicketController::class, 'counts']);
    Route::get('tickets/{id}',               [AdminTicketController::class, 'show']);
    Route::put('tickets/{id}/status',        [AdminTicketController::class, 'updateStatus']);
    Route::post('tickets/{id}/claim',        [AdminTicketController::class, 'claim']);
    Route::post('tickets/{id}/unclaim',      [AdminTicketController::class, 'unclaim']);
    Route::post('tickets/{id}/reply',        [AdminTicketController::class, 'reply']);
});
