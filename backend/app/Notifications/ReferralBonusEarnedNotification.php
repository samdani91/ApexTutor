<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralBonusEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $points,
        public readonly string $referredUserName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You earned referral points — Apex Tutor')
            ->view('emails.referral-bonus-earned', [
                'name'             => $notifiable->name,
                'points'           => $this->points,
                'referredUserName' => $this->referredUserName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'referral_bonus_earned',
            'points'             => $this->points,
            'referred_user_name' => $this->referredUserName,
            'message'            => "{$this->referredUserName} joined using your referral code — you earned {$this->points} points!",
        ];
    }
}
