@extends('emails.layout', [
  'accentColor' => '#D97706',
  'icon' => '&#9998;',
  'headline' => 'Profile Changes Submitted',
  'subheadline' => 'A tutor is waiting for your review',
  'preheader' => '{{ $tutorName }} has submitted profile changes that need your review on TutorKhujo.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $adminName }},</p>
  <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
    <strong style="color:#0F2E5C;">{{ $tutorName }}</strong> has submitted changes to their tutor profile
    that require your review before going live.
  </p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Please log in to the admin panel and open <strong>Pending Changes</strong> to review and approve or reject the submission.
  </p>
  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    This notification was sent because a verified tutor submitted profile changes that require moderation.
  </p>
@endsection
