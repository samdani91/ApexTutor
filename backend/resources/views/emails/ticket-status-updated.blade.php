@extends('emails.layout', [
    'accentColor' => '#0f2e5c',
    'icon'        => '🔄',
    'headline'    => 'Ticket Status Updated',
    'subheadline' => 'Your support ticket status has changed',
    'preheader'   => "Ticket [{$ticket->ticket_number}] is now {$newStatus}",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  The status of your support ticket has been updated by our team.
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
      <td style="font-size:13px;color:#1f2937;padding:4px 0;">Subject</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $ticket->subject }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Previous Status</td>
      <td style="font-size:13px;color:#6b7280;font-weight:600;">{{ $oldStatus }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">New Status</td>
      <td style="font-size:13px;color:#059669;font-weight:700;">{{ $newStatus }}</td>
    </tr>
  </table>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The Apex Tutor Support Team</p>
@endsection
