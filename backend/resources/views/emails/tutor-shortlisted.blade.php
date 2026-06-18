@extends('emails.layout', [
  'accentColor' => '#7C3AED',
  'icon' => '&hearts;',
  'headline' => "You've Been Shortlisted",
  'subheadline' => 'A guardian is interested in you',
  'preheader' => "{$guardianName} shortlisted you on Apex Tutor.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Exciting news. <strong style="color:#0F2E5C;">{{ $guardianName }}</strong> has added you to their shortlist on Apex Tutor.
    This means they are considering you as a tutor for their student.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:12px;padding:18px 20px;text-align:center;">
        <p style="margin:0 0 5px;font-size:12px;color:#6D28D9;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Shortlisted By</p>
        <p style="margin:0;font-size:18px;font-weight:800;color:#4C1D95;">{{ $guardianName }}</p>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 18px;font-size:14px;color:#4A4332;line-height:1.7;">
    Our team will reach out to both parties to arrange a suitable time and discuss the tuition requirements.
  </p>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Make sure your profile is complete and up to date to make the best impression.
  </p>
@endsection
