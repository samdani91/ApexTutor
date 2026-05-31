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
            <td style="background:#7c3aed;padding:28px 32px 24px;">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-right:14px;vertical-align:middle;">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:50%;text-align:center;line-height:44px;font-size:22px;">♥</div>
                  </td>
                  <td style="vertical-align:middle;">
                    <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;line-height:1.2;">You've been shortlisted!</p>
                    <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.8);">A guardian is interested in you</p>
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
                Exciting news! <strong style="color:#0f2e5c;">{{ $guardianName }}</strong> has added you to
                their shortlist on TutorKhujo. This means they are considering you as a tutor for their student.
              </p>

              <!-- Highlight box -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                <tr>
                  <td style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:16px 20px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:13px;color:#6d28d9;font-weight:600;">Shortlisted by</p>
                    <p style="margin:0;font-size:18px;font-weight:700;color:#4c1d95;">{{ $guardianName }}</p>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 16px;font-size:14px;color:#5a5247;line-height:1.7;">
                Our team will reach out to both parties to arrange a suitable time and discuss the tuition requirements.
              </p>

              <div style="border-top:1px solid #f0ece4;margin:20px 0;"></div>

              <p style="margin:0;font-size:13px;color:#8c7f6e;line-height:1.6;">
                Make sure your profile is complete and up to date to make the best impression.
                Log in to your TutorKhujo dashboard to review your profile.
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
