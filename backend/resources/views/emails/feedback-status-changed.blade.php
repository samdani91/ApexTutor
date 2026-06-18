@php
  $approved = $status === 'approved';
  $accentColor = $approved ? '#059669' : '#DC2626';
  $icon = $approved ? '&#9989;' : '&#10060;';
  $headline = $approved ? 'Feedback Approved!' : 'Feedback Update';
  $subheadline = $approved ? 'Your testimonial is now live on Apex Tutor' : 'An update on your platform feedback';
  $preheader = $approved ? 'Your feedback has been approved and is visible on the landing page.' : 'Your platform feedback was reviewed by our team.';
@endphp

@extends('emails.layout', [
    'accentColor' => $accentColor,
    'icon'        => $icon,
    'headline'    => $headline,
    'subheadline' => $subheadline,
    'preheader'   => $preheader,
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>

@if($approved)
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Great news! Your platform feedback has been <strong style="color:#059669;">approved</strong> by our team
    and is now publicly visible in the testimonials section on the Apex Tutor landing page.
  </p>
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:12px;padding:18px 20px;text-align:center;">
        <p style="margin:0;font-size:15px;color:#065F46;font-weight:700;">Thank you for supporting Apex Tutor!</p>
        <p style="margin:8px 0 0;font-size:13px;color:#047857;line-height:1.6;">
          Your words help parents and students discover the right tutors for their needs.
        </p>
      </td>
    </tr>
  </table>
@else
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Thank you for taking the time to share your experience. After review, our moderation team was
    unable to approve your feedback at this time.
  </p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    You are welcome to update your feedback from your dashboard and resubmit — it will be reviewed again.
  </p>
@endif

<p style="margin:0;font-size:14px;color:#4A4332;line-height:1.7;">
  Thank you for being a valued member of the Apex Tutor community.
</p>
@endsection
