<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body style="margin:0;padding:0;background:#f4f2ed;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f2ed;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="100%" style="max-width:480px;background:#ffffff;border-radius:10px;border:1px solid #e5e1d8;overflow:hidden;">

          <!-- Brand bar -->
          <tr>
            <td style="background:#0f2e5c;padding:18px 32px;">
              <span style="font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">
                Tutor<span style="color:#60a5fa;">Khujo</span>
              </span>
            </td>
          </tr>

          <!-- Coloured accent header -->
          <tr>
            <td style="background:#dc2626;padding:28px 32px 24px;">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-right:14px;vertical-align:middle;">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:50%;text-align:center;line-height:44px;font-size:22px;">✕</div>
                  </td>
                  <td style="vertical-align:middle;">
                    <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;line-height:1.2;">Changes Not Approved</p>
                    <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.8);">Action required on your profile</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 16px;font-size:15px;color:#0f2e5c;font-weight:600;">Hello {{ $name }},</p>
              <p style="margin:0 0 20px;font-size:14px;color:#5a5247;line-height:1.7;">
                Unfortunately, your recent profile changes could not be approved at this time.
                Please review the feedback below and resubmit your changes.
              </p>

              @if($note)
              <!-- Admin note -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                <tr>
                  <td style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;border-radius:6px;padding:14px 16px;">
                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:0.5px;">Admin Note</p>
                    <p style="margin:0;font-size:14px;color:#7f1d1d;line-height:1.6;">{{ $note }}</p>
                  </td>
                </tr>
              </table>
              @endif

              @if(!empty($sections))
              <!-- Rejected sections -->
              <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#0f2e5c;">Sections requiring attention:</p>
              <table cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                <tr>
                  @foreach($sections as $section)
                  <td style="padding:4px 4px 4px 0;">
                    <span style="display:inline-block;background:#fee2e2;color:#b91c1c;font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px;">{{ $section }}</span>
                  </td>
                  @endforeach
                </tr>
              </table>
              @endif

              @if(!empty($submitted))
              <!-- Submitted values -->
              <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#0f2e5c;">Values you submitted:</p>
              <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e1d8;border-radius:6px;overflow:hidden;margin-bottom:20px;">
                @foreach($submitted as $i => $row)
                <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#faf9f7' }};">
                  <td style="padding:8px 12px;font-size:12px;font-weight:600;color:#5a5247;width:40%;border-bottom:{{ !$loop->last ? '1px solid #f0ece4' : 'none' }};">{{ $row['field'] }}</td>
                  <td style="padding:8px 12px;font-size:13px;color:#0f2e5c;border-bottom:{{ !$loop->last ? '1px solid #f0ece4' : 'none' }};">{{ $row['value'] }}</td>
                </tr>
                @endforeach
              </table>
              @endif

              <div style="border-top:1px solid #f0ece4;margin:20px 0;"></div>

              <p style="margin:0;font-size:13px;color:#8c7f6e;line-height:1.6;">
                Log in to your TutorKhujo dashboard to update your profile and resubmit for review.
                If you believe this decision is incorrect, please contact our support team.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:16px 32px;border-top:1px solid #f0ece4;background:#faf9f7;">
              <p style="margin:0;font-size:12px;color:#b0a899;">© {{ date('Y') }} TutorKhujo. This is an automated notification — please do not reply.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
