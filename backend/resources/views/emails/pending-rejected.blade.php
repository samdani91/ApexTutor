@extends('emails.layout', [
  'accentColor' => '#DC2626',
  'icon' => '&#10005;',
  'headline' => 'Changes Not Approved',
  'subheadline' => 'Action required on your profile',
  'preheader' => 'Your Apex Tutor profile changes were not approved.',
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Unfortunately, your recent profile changes could not be approved at this time. Please review the feedback below and resubmit your changes.
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

  @if(!empty($sections))
    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#0F2E5C;">Sections Requiring Attention</p>
    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
      <tr>
        @foreach($sections as $section)
          <td style="padding:4px 4px 4px 0;">
            <span style="display:inline-block;background:#FEE2E2;color:#B91C1C;font-size:12px;font-weight:700;padding:5px 10px;border-radius:999px;">{{ $section }}</span>
          </td>
        @endforeach
      </tr>
    </table>
  @endif

  @if(!empty($submitted))
    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#0F2E5C;">Values You Submitted</p>
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border:1px solid #ECE6D5;border-radius:10px;overflow:hidden;margin-bottom:20px;">
      @foreach($submitted as $i => $row)
        <tr style="background:{{ $i % 2 === 0 ? '#FFFFFF' : '#FBF9F3' }};">
          <td style="padding:9px 12px;font-size:12px;font-weight:700;color:#4A4332;width:40%;border-bottom:{{ !$loop->last ? '1px solid #ECE6D5' : 'none' }};">{{ $row['field'] }}</td>
          <td style="padding:9px 12px;font-size:13px;color:#0F2E5C;border-bottom:{{ !$loop->last ? '1px solid #ECE6D5' : 'none' }};">{{ $row['value'] }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  <p style="margin:0;padding-top:18px;border-top:1px solid #ECE6D5;font-size:13px;color:#6B6248;line-height:1.6;">
    Log in to your Apex Tutor dashboard to update your profile and resubmit for review. If you believe this decision is incorrect, please contact support.
  </p>
@endsection
