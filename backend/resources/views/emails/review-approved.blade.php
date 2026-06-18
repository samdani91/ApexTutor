@extends('emails.layout', [
  'accentColor' => '#D97706',
  'icon' => '&#9733;',
  'headline' => 'Your Review Is Published',
  'subheadline' => 'Thank you for your feedback',
  'preheader' => "Your review for {$tutorName} has been published.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Your review for <strong style="color:#0F2E5C;">{{ $tutorName }}</strong> has been
    <strong style="color:#059669;">approved</strong> by our moderation team and is now publicly visible on Apex Tutor.
  </p>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
      <td style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:18px 20px;text-align:center;">
        <p style="margin:0 0 7px;font-size:13px;color:#92400E;font-weight:700;">Your Rating For {{ $tutorName }}</p>
        <p style="margin:0;font-size:26px;line-height:1;">
          @for ($i = 1; $i <= 5; $i++)
            <span style="color:{{ $i <= $rating ? '#F59E0B' : '#D1D5DB' }};">&#9733;</span>
          @endfor
        </p>
        <p style="margin:6px 0 0;font-size:13px;color:#78350F;">{{ $rating }} / 5</p>
      </td>
    </tr>
  </table>

  <p style="margin:0;font-size:14px;color:#4A4332;line-height:1.7;">
    Your honest feedback helps other guardians make informed decisions. Thank you for helping the Apex Tutor community.
  </p>
@endsection
