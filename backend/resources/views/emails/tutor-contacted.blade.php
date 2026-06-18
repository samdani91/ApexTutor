@extends('emails.layout', [
    'accentColor' => '#0f5c8a',
    'icon'        => '📞',
    'headline'    => 'You Have Been Contacted',
    'subheadline' => 'Our team has reached out regarding a tuition request',
    'preheader'   => "Apex Tutor has contacted you about a tuition from {$guardianName}",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  Our team has contacted you regarding a tuition request from
  <strong style="color:#0f5c8a;">{{ $guardianName }}</strong>.
</p>
<p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#6b7280;">
  Please ensure your phone is reachable and check the Apex Tutor platform for updates on this connection.
</p>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The Apex Tutor Team</p>
@endsection
