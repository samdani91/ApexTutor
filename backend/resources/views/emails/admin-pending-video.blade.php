@extends('emails.layout', [
  'accentColor' => '#7C3AED',
  'icon' => '&#9654;',
  'headline' => 'New Teaching Video Uploaded',
  'subheadline' => 'Pending your review before going live',
  'preheader' => '{{ $tutorName }} has uploaded a teaching video that needs your review on TutorKhujo.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $adminName }},</p>
  <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
    <strong style="color:#0F2E5C;">{{ $tutorName }}</strong> has uploaded a new teaching video that requires your
    review before it can appear on their public profile.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F5F3FF;border:1px solid #DDD6FE;border-left:4px solid #7C3AED;border-radius:10px;padding:15px 17px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:800;color:#5B21B6;text-transform:uppercase;letter-spacing:0.5px;">Video Title</p>
        <p style="margin:0;font-size:14px;color:#0F2E5C;font-weight:600;">{{ $videoTitle }}</p>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Log in to the admin panel, open the tutor's profile and navigate to <strong>Teaching Videos</strong>
    to watch and approve or reject the submission.
  </p>
  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    This notification was sent because a verified tutor uploaded a teaching video pending moderation.
  </p>
@endsection
