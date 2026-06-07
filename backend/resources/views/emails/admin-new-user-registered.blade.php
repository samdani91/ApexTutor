@extends('emails.layout', [
    'accentColor' => '#0f2e5c',
    'icon'        => '👤',
    'headline'    => 'New ' . $roleLabel . ' Registered',
    'subheadline' => 'A new user has verified their email and joined TutorKhujo',
    'preheader'   => "{$userName} joined as a {$roleLabel}",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  A new user has just verified their email and created an account on TutorKhujo.
</p>
<div style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;padding:16px;margin:0 0 16px;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="font-size:12px;color:#0f2e5c;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding-bottom:10px;">Account Details</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;width:80px;">Name</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $userName }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Email</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $userEmail }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Role</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $roleLabel }}</td>
    </tr>
  </table>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The TutorKhujo System</p>
@endsection
