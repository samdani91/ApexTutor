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

          <!-- Header -->
          <tr>
            <td style="background:#0f2e5c;padding:24px 32px;">
              <span style="font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">
                Tutor<span style="color:#60a5fa;">Khujo</span>
              </span>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:32px;">
              @if($purpose === 'email_verification')
                <h2 style="margin:0 0 8px;font-size:18px;font-weight:700;color:#0f2e5c;">Verify your email address</h2>
                <p style="margin:0 0 24px;font-size:14px;color:#5a5247;line-height:1.6;">
                  Thanks for joining TutorKhujo! Enter the code below to verify your email and activate your account.
                </p>
              @else
                <h2 style="margin:0 0 8px;font-size:18px;font-weight:700;color:#0f2e5c;">Password change request</h2>
                <p style="margin:0 0 24px;font-size:14px;color:#5a5247;line-height:1.6;">
                  A password change was requested for your account. Enter the code below to confirm.
                </p>
              @endif

              <!-- OTP box -->
              <div style="background:#f4f2ed;border-radius:8px;text-align:center;padding:28px 0;margin:0 0 24px;">
                <span style="font-size:40px;font-weight:700;letter-spacing:12px;color:#0f2e5c;font-family:'Courier New',monospace;">{{ $code }}</span>
              </div>

              <p style="margin:0 0 8px;font-size:13px;color:#8c7f6e;">
                This code expires in <strong>10 minutes</strong>.
              </p>
              <p style="margin:0;font-size:13px;color:#8c7f6e;">
                If you didn't request this, you can safely ignore this email.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:16px 32px;border-top:1px solid #f0ece4;">
              <p style="margin:0;font-size:12px;color:#b0a899;">© {{ date('Y') }} TutorKhujo. All rights reserved.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
