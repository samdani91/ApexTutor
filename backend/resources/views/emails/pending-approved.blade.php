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
            <td style="background:#059669;padding:28px 32px 24px;">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-right:14px;vertical-align:middle;">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;text-align:center;line-height:44px;font-size:22px;">✓</div>
                  </td>
                  <td style="vertical-align:middle;">
                    <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;line-height:1.2;">Changes Approved!</p>
                    <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.8);">Your profile is now live</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 16px;font-size:15px;color:#0f2e5c;font-weight:600;">Hello {{ $name }},</p>
              <p style="margin:0 0 16px;font-size:14px;color:#5a5247;line-height:1.7;">
                Great news! Your recent profile changes have been <strong style="color:#059669;">reviewed and approved</strong>
                by our admin team. Your updated profile is now visible to guardians searching for tutors.
              </p>
              <p style="margin:0;font-size:14px;color:#5a5247;line-height:1.7;">
                Keep your profile up to date to improve your chances of finding the right students.
              </p>

              <!-- Divider -->
              <div style="border-top:1px solid #f0ece4;margin:24px 0;"></div>

              <p style="margin:0;font-size:13px;color:#8c7f6e;line-height:1.6;">
                You can view your live profile by logging in to your TutorKhujo dashboard.
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
