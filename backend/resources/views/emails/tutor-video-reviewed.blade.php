@php
  $isApproved = $action === 'approved';
@endphp

@extends('emails.layout', [
  'accentColor' => $isApproved ? '#059669' : '#DC2626',
  'icon'        => $isApproved ? '&#10003;' : '&#10005;',
  'headline'    => $isApproved ? 'Video Approved!' : 'Video Not Approved',
  'subheadline' => $isApproved ? 'Your teaching video is now live' : 'Action required on your teaching video',
  'preheader'   => $isApproved
    ? 'Your teaching video has been approved and is now visible on your TutorKhujo profile.'
    : 'Your teaching video was not approved. Please review the feedback.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>

  @if($isApproved)
    <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
      Great news! Your teaching video <strong style="color:#0F2E5C;">"{{ $videoTitle }}"</strong> has been
      <strong style="color:#059669;">reviewed and approved</strong>. It is now visible to guardians on your
      public profile.
    </p>
    <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
      Upload more videos to showcase your teaching style and attract more students.
    </p>
  @else
    <p style="margin:0 0 16px;font-size:14px;color:#4A4332;line-height:1.7;">
      Unfortunately, your teaching video <strong style="color:#0F2E5C;">"{{ $videoTitle }}"</strong> could not
      be approved at this time.
    </p>
    @if($reviewNote)
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
        <tr>
          <td style="background:#FEF2F2;border:1px solid #FECACA;border-left:4px solid #DC2626;border-radius:10px;padding:15px 17px;">
            <p style="margin:0 0 5px;font-size:11px;font-weight:800;color:#991B1B;text-transform:uppercase;letter-spacing:0.5px;">Reviewer Note</p>
            <p style="margin:0;font-size:14px;color:#7F1D1D;line-height:1.6;">{{ $reviewNote }}</p>
          </td>
        </tr>
      </table>
    @endif
    <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
      You can remove this video and upload a new one from your TutorKhujo dashboard.
    </p>
  @endif

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your dashboard to manage your teaching videos.
  </p>
@endsection
