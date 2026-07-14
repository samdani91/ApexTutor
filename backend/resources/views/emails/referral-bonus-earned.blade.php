@extends('emails.layout', [
  'accentColor' => '#059669',
  'icon'        => '&#127942;',
  'headline'    => 'You Earned Referral Points!',
  'subheadline' => 'Someone joined using your code',
  'preheader'   => "{$referredUserName} joined using your referral code — you earned {$points} points!",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>

  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Great news! <strong>{{ $referredUserName }}</strong> just joined Apex Tutor using your referral code, and you've earned referral points as a thank-you.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F8F6F0;border:1px solid #ECE6D5;border-radius:12px;padding:18px 20px;">
        <p style="margin:0 0 5px;font-size:12px;color:#8F8467;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Points Earned</p>
        <p style="margin:0;font-size:16px;font-weight:800;color:#0F2E5C;">+{{ $points }} points</p>
      </td>
    </tr>
  </table>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your <strong>Apex Tutor</strong> dashboard to see your referral code and total points balance.
  </p>
@endsection
