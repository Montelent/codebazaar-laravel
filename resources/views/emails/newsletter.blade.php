<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Inter,Segoe UI,Helvetica,Arial,sans-serif;color:#0f172a;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
          {{-- Header --}}
          <tr>
            <td style="background:{{ $primary }};padding:20px 24px;">
              <a href="{{ $siteUrl }}" style="color:#ffffff;font-size:18px;font-weight:800;text-decoration:none;letter-spacing:-0.02em;">{{ $siteName }}</a>
            </td>
          </tr>
          {{-- Body --}}
          <tr>
            <td style="padding:28px 24px;font-size:15px;line-height:1.65;color:#334155;">
              {!! $bodyHtml !!}
            </td>
          </tr>
          {{-- Footer --}}
          <tr>
            <td style="padding:16px 24px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;">
              <p style="margin:0;font-size:12px;line-height:1.5;color:#94a3b8;">
                © {{ date('Y') }} {{ $siteName }}.
                <a href="{{ $siteUrl }}" style="color:#64748b;">{{ $siteUrl }}</a>
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
