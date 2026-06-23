@extends('emails.layout', [
  'accentColor' => '#0F2E5C',
  'icon'        => '&#128203;',
  'headline'    => 'New Applicant',
  'subheadline' => 'A tutor has applied to your job',
  'preheader'   => "Tutor {$tutorName} ({$tutorId}) applied to your job: {$jobTitle}.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>

  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    A tutor has applied to one of your tuition jobs. You can view their profile and shortlist them from your dashboard.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F8F6F0;border:1px solid #ECE6D5;border-radius:12px;padding:18px 20px;">

        <p style="margin:0 0 12px;font-size:12px;color:#8F8467;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Applicant</p>
        <p style="margin:0;font-size:16px;font-weight:800;color:#0F2E5C;">{{ $tutorName }}</p>
        <p style="margin:4px 0 0;font-size:12px;color:#8F8467;">Tutor ID: <strong style="color:#0F2E5C;">{{ $tutorId }}</strong></p>

        <hr style="margin:14px 0;border:none;border-top:1px solid #ECE6D5;" />

        <p style="margin:0 0 5px;font-size:12px;color:#8F8467;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Your Job</p>
        <p style="margin:0;font-size:15px;font-weight:700;color:#0F2E5C;">{{ $jobTitle }}</p>
        <p style="margin:4px 0 0;font-size:12px;color:#8F8467;">Job ID: <strong style="color:#0F2E5C;">#{{ $jobPublicId }}</strong></p>
      </td>
    </tr>
  </table>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your <strong>Apex Tutor</strong> guardian dashboard to view the applicant's profile and manage your shortlist.
  </p>
@endsection
