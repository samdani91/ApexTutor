@extends('emails.layout', [
  'accentColor' => '#059669',
  'icon' => '&#10003;',
  'headline' => 'Changes Approved',
  'subheadline' => 'Your profile updates are now live',
  'preheader' => 'Your Apex Tutor profile changes have been approved.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
    Great news. Your recent profile changes have been <strong style="color:#059669;">reviewed and approved</strong>
    by our admin team. Your updated profile is now visible to guardians searching for tutors.
  </p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Keep your profile up to date to improve your chances of finding the right students.
  </p>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    You can view your live profile by logging in to your Apex Tutor dashboard.
  </p>
@endsection
