@extends('emails.layout', [
  'accentColor' => '#64748B',
  'icon' => '&#10005;',
  'headline' => 'Removed From Shortlist',
  'subheadline' => 'A guardian updated their shortlist',
  'preheader' => "{$guardianName} removed you from their shortlist.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    We wanted to let you know that <strong style="color:#0F2E5C;">{{ $guardianName }}</strong> has removed you from their shortlist on TutorKhujo.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:18px 20px;text-align:center;">
        <p style="margin:0 0 5px;font-size:12px;color:#64748B;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">Removed By</p>
        <p style="margin:0;font-size:18px;font-weight:800;color:#1E3A5F;">{{ $guardianName }}</p>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 18px;font-size:14px;color:#4A4332;line-height:1.7;">
    Do not be discouraged. There are many guardians actively searching for tutors like you. Keeping your profile complete and up to date increases your chances of being shortlisted again.
  </p>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your TutorKhujo dashboard to review your profile and keep it as strong as possible.
  </p>
@endsection
