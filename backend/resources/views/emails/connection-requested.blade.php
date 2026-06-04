@extends('emails.layout', [
  'accentColor' => '#0F2E5C',
  'icon' => '&#8599;',
  'headline' => 'New Connection Request',
  'subheadline' => 'Action required',
  'preheader' => "{$guardianName} requested a connection with {$tutorName}.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    A guardian has submitted a connection request through TutorKhujo.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#F0F7FF;border:1px solid #BFDBFE;border-radius:12px;padding:16px 18px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:800;color:#1E40AF;text-transform:uppercase;letter-spacing:0.5px;">Guardian</p>
        <p style="margin:0 0 12px;font-size:15px;font-weight:800;color:#0F2E5C;">{{ $guardianName }}</p>
        <p style="margin:0 0 4px;padding-top:12px;border-top:1px solid #DBEAFE;font-size:11px;font-weight:800;color:#1E40AF;text-transform:uppercase;letter-spacing:0.5px;">Requested Tutor</p>
        <p style="margin:0;font-size:15px;font-weight:800;color:#0F2E5C;">{{ $tutorName }}</p>
        @if($guardianMessage)
          <p style="margin:12px 0 4px;padding-top:12px;border-top:1px solid #DBEAFE;font-size:11px;font-weight:800;color:#1E40AF;text-transform:uppercase;letter-spacing:0.5px;">Guardian Message</p>
          <p style="margin:0;font-size:14px;color:#374151;line-height:1.6;font-style:italic;">"{{ $guardianMessage }}"</p>
        @endif
      </td>
    </tr>
  </table>

  <p style="margin:0;font-size:14px;color:#4A4332;line-height:1.7;">
    Please log in to the admin dashboard to review this request and take appropriate action.
  </p>
@endsection
