<?php

namespace App\Jobs;

use App\Services\BulkSmsBdService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sends a single SMS off the request cycle.
 *
 * Used for SMS that are a *side effect* of some other action (a tutor being
 * appointed/confirmed, a referral bonus being awarded) — nobody is waiting on
 * the delivery result, so a slow or failing BulkSMSBD must not block the
 * admin's request.
 *
 * Deliberately NOT used by AdminSmsController::send(), where the admin is
 * explicitly sending an SMS and needs the pass/fail result back immediately.
 */
class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly string $number,
        private readonly string $message,
    ) {}

    public function handle(BulkSmsBdService $sms): void
    {
        $sms->send($this->number, $this->message);
    }
}
