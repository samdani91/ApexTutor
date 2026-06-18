@extends('emails.layout', [
  'accentColor' => '#DC2626',
  'icon' => '&#10005;',
  'headline' => 'Verification Not Approved',
  'subheadline' => 'Please review the feedback below',
  'preheader' => 'Your Apex Tutor profile verification was not approved.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    We reviewed your tutor profile and we are unable to verify it at this time. Please review the feedback below and update your profile accordingly.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#FEF2F2;border:1px solid #FECACA;border-left:4px solid #DC2626;border-radius:10px;padding:15px 17px;">
        <p style="margin:0 0 6px;font-size:11px;font-weight:800;color:#991B1B;text-transform:uppercase;letter-spacing:0.5px;">Reason For Rejection</p>
        <p style="margin:0;font-size:14px;color:#7F1D1D;line-height:1.6;">{{ $reason }}</p>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 18px;font-size:14px;color:#4A4332;line-height:1.7;">
    You can update your profile and resubmit for verification at any time. Make sure all required information is complete and your documents are clear and legible.
  </p>

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your Apex Tutor dashboard to update your profile and resubmit.
  </p>
@endsection
