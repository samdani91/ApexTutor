@extends('emails.layout', [
  'accentColor' => '#059669',
  'icon' => '&#128247;',
  'headline' => 'Photo Approved',
  'subheadline' => 'Your profile picture is now live',
  'preheader' => 'Your Apex Tutor profile photo has been approved.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
    Great news. Your profile photo has been <strong style="color:#059669;">reviewed and approved</strong>
    by our admin team. Your new photo is now visible across the platform.
  </p>
  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your Apex Tutor dashboard to view your updated profile.
  </p>
@endsection
