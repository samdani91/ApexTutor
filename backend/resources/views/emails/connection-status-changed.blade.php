@php
  $configs = [
    'admin_reviewing' => ['bg' => '#2563EB', 'icon' => '...', 'headline' => 'Request Under Review', 'sub' => 'Our team is looking into it'],
    'tutor_contacted' => ['bg' => '#7C3AED', 'icon' => '&#8599;', 'headline' => 'Tutor Has Been Contacted', 'sub' => 'We are arranging your tuition'],
    'confirmed' => ['bg' => '#059669', 'icon' => '&#10003;', 'headline' => 'Tuition Confirmed', 'sub' => 'Your tuition has been arranged'],
    'declined' => ['bg' => '#DC2626', 'icon' => '&#10005;', 'headline' => 'Request Declined', 'sub' => 'We are sorry for the inconvenience'],
    'closed' => ['bg' => '#64748B', 'icon' => '&#10005;', 'headline' => 'Connection Closed', 'sub' => 'This connection has ended'],
  ];
  $c = $configs[$status] ?? ['bg' => '#0F2E5C', 'icon' => 'i', 'headline' => 'Connection Update', 'sub' => ''];
  $bodies = [
    'admin_reviewing' => "We received your connection request with <strong style=\"color:#0F2E5C;\">{$tutorName}</strong> and our team is currently reviewing it. We will be in touch shortly.",
    'tutor_contacted' => "Great news. We reached out to <strong style=\"color:#0F2E5C;\">{$tutorName}</strong> on your behalf. We will update you once we hear back.",
    'confirmed' => "Your tuition with <strong style=\"color:#0F2E5C;\">{$tutorName}</strong> has been confirmed. You can find this tutor in your <strong>Confirmed Tuitions</strong> section on TutorKhujo.",
    'declined' => "Unfortunately, your connection request with <strong style=\"color:#0F2E5C;\">{$tutorName}</strong> could not be fulfilled at this time.",
    'closed' => "Your connection with <strong style=\"color:#0F2E5C;\">{$tutorName}</strong> has been closed.",
  ];
  $bodyText = $bodies[$status] ?? 'Your connection status has been updated.';
@endphp

@extends('emails.layout', [
  'accentColor' => $c['bg'],
  'icon' => $c['icon'],
  'headline' => $c['headline'],
  'subheadline' => $c['sub'],
  'preheader' => $c['headline'] . ' on TutorKhujo.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">{!! $bodyText !!}</p>

  @if($adminNote)
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
      <tr>
        <td style="background:#FEFCE8;border:1px solid #FDE047;border-left:4px solid #D97706;border-radius:10px;padding:15px 17px;">
          <p style="margin:0 0 5px;font-size:11px;font-weight:800;color:#854D0E;text-transform:uppercase;letter-spacing:0.5px;">Note From Admin</p>
          <p style="margin:0;font-size:14px;color:#713F12;line-height:1.6;">{{ $adminNote }}</p>
        </td>
      </tr>
    </table>
  @endif

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your TutorKhujo dashboard to view your connection details.
  </p>
@endsection
