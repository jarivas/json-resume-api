<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Recuperar contraseña</title>
    <style>
        body { background-color:#f6f8fa; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; margin:0; padding:20px; color:#0f172a; }
        .container { max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; padding:24px; box-shadow:0 1px 3px rgba(16,24,40,0.05); }
        .header { font-size:20px; font-weight:600; margin-bottom:8px; }
        .lead { color:#475569; margin-bottom:18px; line-height:1.5; }
        .btn { display:inline-block; background:#2563eb; color:#fff; padding:12px 20px; border-radius:6px; text-decoration:none; font-weight:600; }
        .muted { color:#64748b; font-size:13px; }
        .token { background:#f1f5f9; padding:12px; border-radius:6px; font-family:monospace; overflow-wrap:anywhere; }
        .footer { border-top:1px solid #e6edf3; margin-top:20px; padding-top:16px; color:#94a3b8; font-size:13px; }
    </style>
</head>
<body>
    @php
        $resetUrl = env('USER_PASSWORD_RESET_URL');
        $resetUrl .= "?token=$token";
    @endphp

    <div class="container">
        <div class="header">Recuperación de contraseña</div>

        <p class="lead">
            Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en {{ config('app.name') }}.
            Haz clic en el siguiente botón para crear una nueva contraseña. Este enlace expirará en {{ $expiresAt }}.
        </p>

        <p style="text-align:center; margin:20px 0;">
            <a href="{{ $resetUrl }}" class="btn" target="_blank" rel="noopener noreferrer">
                Restablecer contraseña
            </a>
        </p>

        <p class="muted">
            Si el botón anterior no funciona, copia y pega esta URL en tu navegador:
        </p>

        <p class="token">
            {{ $resetUrl }}
        </p>

        @if(isset($token))
            <p class="muted" style="margin-top:12px">O usa este token:</p>
            <div class="token">{{ $token }}</div>
        @endif

        <p class="muted" style="margin-top:18px">
            Si no solicitaste el restablecimiento de la contraseña, puedes ignorar este correo y no se realizará ningún cambio.
        </p>

        <div class="footer">
            Saludos,<br>
            El equipo de {{ config('app.name') }}
        </div>
    </div>
</body>
</html>