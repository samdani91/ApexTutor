@php
  $verb        = $action === 'claimed' ? 'Claimed' : 'Unclaimed';
  $verbLower   = $action === 'claimed' ? 'claimed' : 'unclaimed';
  $accentColor = $action === 'claimed' ? '#0f2e5c' : '#b45309';
  $bgColor     = $action === 'claimed' ? '#f0f4ff' : '#fffbeb';
  $borderColor = $action === 'claimed' ? '#c7d2fe' : '#fde68a';
@endphp
@extends('emails.layout', [
    'accentColor' => $accentColor,
    'icon'        => $action === 'claimed' ? '&#128275;' : '&#128274;',
    'headline'    => "Ticket {$verb}",
    'subheadline' => $action === 'claimed'
        ? "{$actor->name} has taken ownership of a support ticket"
        : "{$actor->name} has released a support ticket",
    'preheader'   => "[{$ticket->ticket_number}] {$verb} by {$actor->name}",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  @if($isSelf)
    You have <strong>{{ $verbLower }}</strong> the following support ticket.
  @else
    <strong>{{ $actor->name }}</strong> has <strong>{{ $verbLower }}</strong> the following support ticket.
  @endif
</p>
<div style="background:{{ $bgColor }};border:1px solid {{ $borderColor }};border-radius:8px;padding:16px;margin:0 0 16px;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="font-size:12px;color:{{ $accentColor }};font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding-bottom:10px;">Ticket Details</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;width:90px;">Ticket #</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $ticket->ticket_number }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Subject</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $ticket->subject }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Status</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">{{ $action === 'claimed' ? 'Claimed by' : 'Released by' }}</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $actor->name }}</td>
    </tr>
  </table>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The TutorKhujo System</p>
@endsection
