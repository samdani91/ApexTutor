<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Queues a birthday-wish SMS for every active user whose date of birth falls
 * today (Asia/Dhaka). Meant to run once a day from cron; an idempotency guard
 * makes accidental double runs harmless.
 *
 * Delivery goes through SendSmsJob (ShouldQueue), and BulkSmsBdService appends
 * the standard "Regards, Apex Tutor Team" footer — don't add it here.
 *
 * Note: date_of_birth only exists on tutor_personal_infos, so in practice this
 * reaches tutors. Guardians have no birthday field yet; when one is added,
 * extend the query below.
 */
class SendBirthdayWishesCommand extends Command
{
    protected $signature   = 'sms:birthday-wishes';
    protected $description = 'Queue a birthday-wish SMS for every user whose birthday is today';

    private const MESSAGE = 'Happy Birthday! Wishing you a joyful, healthy, and successful year ahead.';

    public function handle(): int
    {
        // The app runs in UTC; birthdays are a Bangladesh-local concept.
        $today = Carbon::now('Asia/Dhaka');

        // Once per (Dhaka) day, even if the cron fires again.
        if (!Cache::add('birthday-sms-sent:' . $today->toDateString(), true, now()->addHours(36))) {
            $this->info('Birthday wishes already sent today — skipping.');
            return self::SUCCESS;
        }

        // Month/day pairs to match. On Feb 28 of a non-leap year, include the
        // Feb 29 birthdays that would otherwise never get a wish.
        $dates = [[$today->month, $today->day]];
        if ($today->month === 2 && $today->day === 28 && !$today->isLeapYear()) {
            $dates[] = [2, 29];
        }

        $users = User::where('is_active', true)
            ->whereNotNull('phone')
            ->whereHas('tutorProfile.personalInfo', function ($q) use ($dates) {
                $q->where(function ($q) use ($dates) {
                    foreach ($dates as [$month, $day]) {
                        $q->orWhere(fn ($qq) => $qq->whereMonth('date_of_birth', $month)->whereDay('date_of_birth', $day));
                    }
                });
            })
            ->get(['id', 'name', 'phone']);

        foreach ($users as $user) {
            SendSmsJob::dispatch($user->phone, self::MESSAGE);
        }

        Log::info('Birthday SMS queued', ['date' => $today->toDateString(), 'count' => $users->count()]);
        $this->info("Queued birthday SMS for {$users->count()} user(s).");

        return self::SUCCESS;
    }
}
