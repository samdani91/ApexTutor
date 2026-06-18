@extends('emails.layout', [
  'accentColor' => '#0F2E5C',
  'icon' => '&#9998;',
  'headline' => 'Profile Updated',
  'subheadline' => 'An admin has made changes to your profile',
  'preheader' => 'Your Apex Tutor tutor profile has been updated by an admin.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
    An admin on Apex Tutor has made updates to your tutor profile. These changes are now live on your profile.
  </p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Please log in to your dashboard to review the changes. If you did not expect this or believe there is an error,
    please contact our support team.
  </p>
  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    You can view your profile details by logging into your Apex Tutor dashboard.
  </p>
@endsection
