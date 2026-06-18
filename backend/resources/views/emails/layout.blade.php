@php
  $accentColor = $accentColor ?? '#0F2E5C';
  $icon = $icon ?? 'i';
  $headline = $headline ?? 'Apex Tutor Update';
  $subheadline = $subheadline ?? null;
  $preheader = $preheader ?? $headline;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="x-apple-disable-message-reformatting" />
  <title>{{ $headline }}</title>
</head>
<body style="margin:0;padding:0;background:#F5F1E8;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#0F2E5C;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    {{ $preheader }}
  </div>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#F5F1E8;padding:34px 14px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:620px;background:#ffffff;border:1px solid #ECE6D5;border-radius:16px;overflow:hidden;box-shadow:0 20px 46px rgba(15,46,92,0.12);">
          <tr>
            <td style="background:#051736;padding:22px 28px;">
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="vertical-align:middle;">
                    <table cellpadding="0" cellspacing="0" role="presentation">
                      <tr>
                        <td style="width:42px;height:42px;background:#F4B942;border-radius:10px;text-align:center;vertical-align:middle;">
                          <span style="font-size:15px;line-height:42px;font-weight:800;color:#051736;letter-spacing:0.2px;">AT</span>
                        </td>
                        <td style="padding-left:12px;vertical-align:middle;">
                          <p style="margin:0;font-size:21px;font-weight:800;color:#ffffff;letter-spacing:-0.3px;">Apex Tutor</p>
                          <p style="margin:3px 0 0;font-size:12px;color:#D6DEEC;">Verified tuition connections</p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="background:{{ $accentColor }};padding:28px;">
              <table cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="padding-right:14px;vertical-align:middle;">
                    <div style="width:48px;height:48px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.22);border-radius:12px;text-align:center;line-height:48px;font-size:22px;font-weight:700;color:#ffffff;">
                      {!! $icon !!}
                    </div>
                  </td>
                  <td style="vertical-align:middle;">
                    <p style="margin:0;font-size:22px;font-weight:800;color:#ffffff;line-height:1.2;">{{ $headline }}</p>
                    @if($subheadline)
                      <p style="margin:6px 0 0;font-size:14px;color:rgba(255,255,255,0.82);line-height:1.4;">{{ $subheadline }}</p>
                    @endif
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:30px 28px 28px;">
              @yield('content')
            </td>
          </tr>

          <tr>
            <td style="background:#FBF9F3;border-top:1px solid #ECE6D5;padding:18px 28px;">
              <p style="margin:0 0 4px;font-size:12px;color:#8F8467;line-height:1.5;">
                &copy; {{ date('Y') }} Apex Tutor. This is an automated notification. Please do not reply.
              </p>
              <p style="margin:0;font-size:12px;color:#B8AC91;line-height:1.5;">
                Built for Bangladesh tuition search.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
