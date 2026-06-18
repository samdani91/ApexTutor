@extends('emails.layout', [
  'accentColor' => '#DC2626',
  'icon' => '&#128247;',
  'headline' => 'Photo Not Approved',
  'subheadline' => 'Please upload a new profile photo',
  'preheader' => 'Your Apex Tutor profile photo was not approved.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Unfortunately, your profile photo could not be approved at this time. Please upload a new photo that meets our guidelines.
  </p>

  @if($note)
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
      <tr>
        <td style="background:#FEF2F2;border:1px solid #FECACA;border-left:4px solid #DC2626;border-radius:10px;padding:15px 17px;">
          <p style="margin:0 0 5px;font-size:11px;font-weight:800;color:#991B1B;text-transform:uppercase;letter-spacing:0.5px;">Admin Note</p>
          <p style="margin:0;font-size:14px;color:#7F1D1D;line-height:1.6;">{{ $note }}</p>
        </td>
      </tr>
    </table>
  @endif

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your Apex Tutor dashboard to upload a new photo. If you believe this decision is incorrect, please contact support.
  </p>
@endsection
