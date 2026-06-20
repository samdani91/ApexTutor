@extends('emails.layout', [
  'accentColor' => $cfg['accent'],
  'icon'        => $cfg['icon'],
  'headline'    => $cfg['headline'],
  'subheadline' => $cfg['subheadline'],
  'preheader'   => $cfg['db_message'],
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>

  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    {{ $cfg['message'] }}
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F8F6F0;border:1px solid #ECE6D5;border-radius:12px;padding:18px 20px;">
        <p style="margin:0 0 5px;font-size:12px;color:#8F8467;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Job</p>
        <p style="margin:0;font-size:16px;font-weight:800;color:#0F2E5C;">{{ $jobTitle }}</p>
        <p style="margin:4px 0 0;font-size:12px;color:#8F8467;">Job ID: #{{ $jobPublicId }}</p>
        <p style="margin:10px 0 0;font-size:13px;color:#4A4332;">
          Tutor: <strong style="color:#0F2E5C;">{{ $tutorName }}</strong>
        </p>
      </td>
    </tr>
  </table>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your <strong>Apex Tutor</strong> guardian dashboard to view the job details and manage your applicants.
  </p>
@endsection
