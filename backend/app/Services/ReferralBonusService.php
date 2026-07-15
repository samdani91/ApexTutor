<?php
namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\ReferralEarning;
use App\Models\User;
use App\Notifications\AdminReferralCodeUsedNotification;
use App\Notifications\ReferralBonusEarnedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Awards the signup referral bonus to whoever referred a user.
 *
 * Deliberately triggered on admin verification rather than email verification:
 * a verified email is a weak gate (disposable inboxes pass it trivially), so
 * awarding there let anyone farm points with fake accounts. Admin approval
 * means a human reviewed the account's documents first.
 */
class ReferralBonusService
{
    /**
     * Award the referrer of $user, if they were referred at signup.
     *
     * Safe to call more than once — the unique referred_user_id on
     * referral_earnings makes every call after the first a no-op.
     */
    public function awardForSignup(User $user): void
    {
        if (!$user->referred_by) {
            return;
        }

        $earning = DB::transaction(function () use ($user) {
            $earning = ReferralEarning::firstOrCreate(
                ['referred_user_id' => $user->id],
                ['referrer_id' => $user->referred_by, 'points' => (int) config('referral.signup_bonus_points', 5)]
            );

            if ($earning->wasRecentlyCreated) {
                User::where('id', $earning->referrer_id)->increment('referral_points', $earning->points);
            }

            return $earning;
        });

        if (!$earning->wasRecentlyCreated) {
            return;
        }

        // Notifications and SMS run after the transaction commits, and must never
        // break the caller (admin approval) if a channel fails.
        try {
            $referrer = User::find($earning->referrer_id);
            if (!$referrer) {
                return;
            }

            $referrer->notify(new ReferralBonusEarnedNotification($earning->points, $user->name));
            $this->smsReferralBonus($referrer, $user->name, $earning->points);

            $adminNotification = new AdminReferralCodeUsedNotification(
                referrerName:     $referrer->name,
                referredUserName: $user->name,
                referralCode:     $referrer->referral_code ?? '',
            );
            User::where('role', 'super_admin')->get()->each(fn ($admin) => $admin->notify($adminNotification));
        } catch (\Exception $e) {
            Log::error('Referral bonus notification failed', ['error' => $e->getMessage(), 'user' => $user->id]);
        }
    }

    private function smsReferralBonus(User $referrer, string $referredUserName, int $points): void
    {
        try {
            if (!$referrer->phone) return;

            $message = "Good news! {$referredUserName} joined Apex Tutor using your referral code. You earned {$points} points.";
            SendSmsJob::dispatch($referrer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Referral bonus SMS dispatch failed', ['referrer_id' => $referrer->id, 'error' => $e->getMessage()]);
        }
    }
}
