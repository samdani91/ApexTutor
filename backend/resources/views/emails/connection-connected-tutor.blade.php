@extends('emails.layout', [
  'accentColor' => '#059669',
  'icon' => '&#10003;',
  'headline' => 'New Tuition Arranged',
  'subheadline' => 'You have been connected with a guardian',
  'preheader' => "You have been connected with {$guardianName}.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Congratulations. You have been connected with <strong style="color:#0F2E5C;">{{ $guardianName }}</strong>.
    Our team has arranged the tuition and both parties have been notified.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:18px 20px;text-align:center;">
        <p style="margin:0 0 5px;font-size:12px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Connected With</p>
        <p style="margin:0;font-size:18px;font-weight:800;color:#14532D;">{{ $guardianName }}</p>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 18px;font-size:14px;color:#4A4332;line-height:1.7;">
    Please coordinate with the guardian to agree on a schedule, location and any other tuition details.
  </p>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your Apex Tutor dashboard to view connection details.
  </p>
@endsection
