<!DOCTYPE html>
<html lang="lv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paroles atjaunošana</title>
</head>
<body style="margin:0;padding:0;background:#0f0f0f;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f0f;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#1a1a1a;border-radius:16px;border:1px solid #2a2a2a;overflow:hidden;">
        <tr>
          <td style="background:#7c3aed;padding:28px 40px;text-align:center;">
            <div style="font-size:28px;font-weight:900;color:#fff;letter-spacing:1px;">🎮 GameMate</div>
          </td>
        </tr>
        <tr>
          <td style="padding:36px 40px;">
            <h2 style="color:#e5e7eb;font-size:22px;margin:0 0 12px;">Sveiks, {{ $userName }}!</h2>
            <p style="color:#9ca3af;font-size:15px;line-height:1.7;margin:0 0 28px;">
              Mēs saņēmām pieprasījumu atjaunot tava GameMate konta paroli. Nospied zemāk esošo pogu, lai izveidotu jaunu paroli.
            </p>
            <table cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center">
                  <a href="{{ $resetUrl }}"
                     style="display:inline-block;background:#7c3aed;color:#fff;text-decoration:none;
                            font-weight:700;font-size:15px;padding:14px 36px;border-radius:10px;">
                    Atjaunot paroli
                  </a>
                </td>
              </tr>
            </table>
            <p style="color:#6b7280;font-size:12px;margin:28px 0 0;text-align:center;line-height:1.6;">
              Šī saite ir derīga 60 minūtes.<br>
              Ja neredzat pogu, kopējiet šo saiti pārlūkprogrammā:<br>
              <span style="color:#a78bfa;">{{ $resetUrl }}</span>
            </p>
            <p style="color:#4b5563;font-size:11px;margin:20px 0 0;text-align:center;">
              Ja jūs neprasījāt paroles atjaunošanu, varat ignorēt šo e-pastu.
            </p>
          </td>
        </tr>
        <tr>
          <td style="padding:16px 40px;border-top:1px solid #2a2a2a;text-align:center;">
            <span style="color:#4b5563;font-size:11px;">© {{ date('Y') }} GameMate</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
