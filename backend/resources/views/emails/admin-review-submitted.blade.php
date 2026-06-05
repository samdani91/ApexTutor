@extends('emails.layout', [
    'accentColor' => '#7c3aed',
    'icon'        => '★',
    'headline'    => 'New Review Submitted',
    'subheadline' => 'A guardian has submitted a review for moderation',
    'preheader'   => "{$guardianName} submitted a {$rating}-star review for {$tutorName}",
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">Hi <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
  A new review has been submitted and is awaiting moderation.
</p>
<div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:16px;margin:0 0 16px;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="font-size:12px;color:#7c3aed;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding-bottom:10px;">Review Details</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;width:100px;">Guardian</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $guardianName }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Tutor</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $tutorName }}</td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6b7280;padding:4px 0;">Rating</td>
      <td style="font-size:13px;color:#1f2937;font-weight:600;">{{ $rating }} / 5 stars</td>
    </tr>
  </table>
</div>
<p style="margin:0;font-size:14px;color:#9ca3af;">— The TutorKhujo System</p>
@endsection
