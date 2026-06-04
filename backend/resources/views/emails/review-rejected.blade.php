@extends('emails.layout', [
  'accentColor' => '#7C3AED',
  'icon' => '&#10005;',
  'headline' => 'Review Not Approved',
  'subheadline' => 'Feedback from our moderation team',
  'preheader' => "Your review for {$tutorName} was not approved.",
])

@section('content')
  <p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $name }},</p>
  <p style="margin:0 0 20px;font-size:14px;color:#4A4332;line-height:1.7;">
    Your review for <strong style="color:#0F2E5C;">{{ $tutorName }}</strong> was reviewed by our moderation team and could not be published at this time.
  </p>

  @if($moderationNote)
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
      <tr>
        <td style="background:#FEF2F2;border:1px solid #FECACA;border-left:4px solid #DC2626;border-radius:10px;padding:15px 17px;">
          <p style="margin:0 0 5px;font-size:11px;font-weight:800;color:#991B1B;text-transform:uppercase;letter-spacing:0.5px;">Reason</p>
          <p style="margin:0;font-size:14px;color:#7F1D1D;line-height:1.6;">{{ $moderationNote }}</p>
        </td>
      </tr>
    </table>
  @endif

  <p style="margin:0;font-size:14px;color:#4A4332;line-height:1.7;">
    If you believe this is an error or have questions, please contact our support team. We appreciate your understanding.
  </p>
@endsection
