@extends('emails.layout', [
    'accentColor' => '#0f2e5c',
    'icon'        => '💬',
    'headline'    => 'New Reply on Your Ticket',
    'subheadline' => 'The support team has responded to your ticket',
    'preheader'   => "New reply on ticket [{$ticket->ticket_number}]",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  <strong>{{ $replierName }}</strong> has replied to your support ticket <strong>[{{ $ticket->ticket_number }}]</strong>.
</p>
<div style="background:#f9fafb;border-left:3px solid #0f2e5c;padding:12px 16px;margin:0 0 16px;border-radius:0 4px 4px 0;">
  <p style="margin:0 0 4px;font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">{{ $replierName }} wrote:</p>
  <p style="margin:0;font-size:14px;color:#374151;line-height:1.6;">{{ $replyBody }}</p>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The TutorKhujo Support Team</p>
@endsection
