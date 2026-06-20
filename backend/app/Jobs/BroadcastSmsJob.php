<?php

namespace App\Jobs;

use App\Services\BulkSmsBdService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly array $numbers,
        private readonly string $message,
        private readonly int $chunkSize = 300,
    ) {}

    public function handle(BulkSmsBdService $sms): void
    {
        $sms->broadcast($this->numbers, $this->message, $this->chunkSize);
    }
}
