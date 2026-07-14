<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminReferralCodeUsedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $referrerName,
        public readonly string $referredUserName,
        public readonly string $referralCode,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'referral_code_used',
            'referrer_name'      => $this->referrerName,
            'referred_user_name' => $this->referredUserName,
            'referral_code'      => $this->referralCode,
            'message'            => "{$this->referredUserName} registered using {$this->referrerName}'s referral code ({$this->referralCode}).",
        ];
    }
}
