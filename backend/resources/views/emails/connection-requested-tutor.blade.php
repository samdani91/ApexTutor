@extends('emails.layout', [
    'accentColor' => '#1a6b4a',
    'icon'        => '🔗',
    'headline'    => 'New Connection Request',
    'subheadline' => 'A guardian wants to connect with you',
    'preheader'   => "{$guardianName} sent you a connection request on TutorKhujo",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  <strong style="color:#1a6b4a;">{{ $guardianName }}</strong> has sent you a connection request on TutorKhujo.
  Our team will review the request and be in touch shortly.
</p>
@if($guardianMessage)
<div style="background:#f0fdf4;border-left:4px solid #16a34a;border-radius:6px;padding:14px 16px;margin:0 0 16px;">
  <p style="margin:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#15803d;">Their message</p>
  <p style="margin:0;font-size:14px;line-height:1.6;color:#166534;font-style:italic;">"{{ $guardianMessage }}"</p>
</div>
@endif
<p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#6b7280;">
  Make sure your profile is complete and up to date so the guardian can learn more about you.
</p>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The TutorKhujo Team</p>
@endsection
