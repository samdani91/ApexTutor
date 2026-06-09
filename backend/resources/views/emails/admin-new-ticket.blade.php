@extends('emails.layout', [
    'accentColor' => '#0f2e5c',
    'icon'        => '🎫',
    'headline'    => 'New Support Ticket',
    'subheadline' => 'A user has opened a new support ticket',
    'preheader'   => "Ticket [{$ticket->ticket_number}]: {$ticket->subject}",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $adminName }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  A new support ticket has been submitted and requires your attention.
</p>
<div style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;padding:16px;margin:0 0 16px;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="font-size:12px;color:#0f2e5c;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding-bottom:10px;">Ticket Details</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;width:90px;">Ticket #</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $ticket->ticket_number }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">From</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $userName }} ({{ $userEmail }})</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Subject</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $ticket->subject }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Category</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $categoryLabel }}</td>
    </tr>
  </table>
</div>
<div style="background:#f9fafb;border-left:3px solid #0f2e5c;padding:12px 16px;margin:0 0 16px;border-radius:0 4px 4px 0;">
  <p style="margin:0 0 4px;font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Message</p>
  <p style="margin:0;font-size:14px;color:#374151;line-height:1.6;">{{ $ticket->description }}</p>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The TutorKhujo System</p>
@endsection
