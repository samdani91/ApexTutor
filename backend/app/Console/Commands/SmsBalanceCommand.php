<?php

namespace App\Console\Commands;

use App\Services\BulkSmsBdService;
use Illuminate\Console\Command;

class SmsBalanceCommand extends Command
{
    protected $signature   = 'sms:balance';
    protected $description = 'Check the BulkSMSBD account balance';

    public function handle(BulkSmsBdService $sms): int
    {
        $result = $sms->getBalance();
        if ($result['success']) {
            $this->info('Balance: ' . $result['response']);
        } else {
            $this->error('Failed to fetch balance: ' . $result['response']);
        }
        return $result['success'] ? self::SUCCESS : self::FAILURE;
    }
}
