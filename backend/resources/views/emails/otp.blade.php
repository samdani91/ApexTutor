@php
  $icon        = $purpose === 'email_verification' ? '&#9993;' : '&#128274;';
  $headline    = match($purpose) {
    'email_verification' => 'Verify Your Email',
    'password_reset'     => 'Reset Your Password',
    default              => 'Password Change Request',
  };
  $subheadline = match($purpose) {
    'email_verification' => 'Use this code to activate your account',
    'password_reset'     => 'Use this code to set a new password',
    default              => 'Use this code to confirm the change',
  };
@endphp
@extends('emails.layout', [
  'accentColor' => '#0F2E5C',
  'icon'        => $icon,
  'headline'    => $headline,
  'subheadline' => $subheadline,
  'preheader'   => 'Your TutorKhujo verification code is ' . $code,
])

@section('content')
  <p style="margin:0 0 18px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello,</p>
  @if($purpose === 'email_verification')
    <p style="margin:0 0 22px;font-size:14px;color:#4A4332;line-height:1.7;">
      Thanks for joining TutorKhujo. Enter the code below to verify your email and activate your account.
    </p>
  @elseif($purpose === 'password_reset')
    <p style="margin:0 0 22px;font-size:14px;color:#4A4332;line-height:1.7;">
      We received a request to reset the password for your TutorKhujo account. Enter the code below to proceed.
    </p>
  @else
    <p style="margin:0 0 22px;font-size:14px;color:#4A4332;line-height:1.7;">
      A password change was requested for your account. Enter the code below to confirm this request.
    </p>
  @endif

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 22px;">
    <tr>
      <td style="background:#FBF9F3;border:1px solid #ECE6D5;border-radius:12px;text-align:center;padding:28px 10px;">
        <span style="font-size:40px;font-weight:800;letter-spacing:12px;color:#0F2E5C;font-family:'Courier New',Courier,monospace;">{{ $code }}</span>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 6px;font-size:13px;color:#6B6248;line-height:1.6;">
    This code expires in <strong style="color:#0F2E5C;">10 minutes</strong>.
  </p>
  <p style="margin:0;font-size:13px;color:#8F8467;line-height:1.6;">
    If you did not request this, you can safely ignore this email.
  </p>
@endsection
