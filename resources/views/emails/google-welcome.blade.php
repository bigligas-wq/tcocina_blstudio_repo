<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a T cocina</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#f59e0b 0%,#facc15 100%);padding:28px 24px 20px;text-align:center;">
                            <img src="{{ config('app.url') }}/images/Tsinfondo.png"
                                 alt="T cocina"
                                 width="72"
                                 style="width:72px;height:72px;object-fit:contain;display:block;margin:0 auto 14px;border-radius:50%;background:#1e40af;padding:8px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                            <h1 style="margin:0;font-size:24px;line-height:1.25;color:#111827;">Bienvenido a T cocina 🍔🍟</h1>
                            <p style="margin:10px 0 0 0;font-size:14px;color:#111827;">Tu cuenta ya esta lista y tu album de figuritas se activo correctamente.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px 0;font-size:15px;">Hola {{ $userName ?: 'Cliente' }}, gracias por registrarte con Google.</p>
                            <p style="margin:0 0 18px 0;font-size:15px;">Así funciona tu album de T cocina:</p>
                            <ul style="margin:0 0 18px 20px;padding:0;font-size:15px;line-height:1.75;">
                                <li>Por cada combo comprado recibís 1 figurita, van a figurar en tu álbum una vez que confirmemos tu pedido.</li>
                                <li>Cuando completás la meta de figuritas vas a poder solicitar el canje del premio vigente.</li>
                                <li>Las figuritas que sobren se conservan para el siguiente pedido.</li>
                                <li>El premio vigente puede cambiar y siempre lo vas a ver en tu panel.</li>
                            </ul>
                            <p style="margin:0 0 8px 0;font-size:14px;color:#4b5563;">Importante: los premios no incluyen envío. También podés retirar por el local.</p>
                            <p style="margin:0 0 22px 0;font-size:14px;color:#4b5563;">Si tenés dudas, escribinos por WhatsApp desde la web.</p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="border-radius:10px;background:#f59e0b;">
                                        <a href="{{ route('loyalty.dashboard') }}" style="display:inline-block;padding:12px 18px;font-size:14px;font-weight:700;color:#111827;text-decoration:none;">
                                            Ver mi album
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 24px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#6b7280;">
                                Este correo fue enviado automaticamente por T cocina.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
