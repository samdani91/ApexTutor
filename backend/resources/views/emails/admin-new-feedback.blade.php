@extends('emails.layout', [
    'accentColor' => '#7c3aed',
    'icon'        => '&#128172;',
    'headline'    => 'New Platform Feedback',
    'subheadline' => 'A user has submitted feedback for moderation',
    'preheader'   => "{$userName} submitted platform feedback awaiting moderation",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  A new platform feedback submission is awaiting your moderation.
</p>
<div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:16px;margin:0 0 16px;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="font-size:12px;color:#7c3aed;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding-bottom:10px;">Submission Details</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;width:110px;">Submitted by</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $userName }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Role</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ ucfirst($userRole) }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Will appear as</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $displayLabel }}</td>
    </tr>
  </table>
</div>
<div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid #7c3aed;border-radius:4px;padding:14px 16px;margin:0 0 16px;">
  <p style="margin:0;font-size:14px;color:#374151;line-height:1.7;font-style:italic;">"{{ $quote }}"</p>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">Log in to the admin panel to approve or reject this feedback.</p>
<p style="margin:12px 0 0;font-size:14px;color:#9ca3af;">— The TutorKhujo System</p>
@endsection
