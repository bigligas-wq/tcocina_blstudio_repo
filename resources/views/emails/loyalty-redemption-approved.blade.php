<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu premio fue aprobado</title>
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
                                 style="width:72px;height:72px;object-fit:contain;display:block;margin:0 auto 14px;border-radius:50%;background:#fff;padding:8px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                            <h1 style="margin:0;font-size:24px;line-height:1.25;color:#111827;">Tu premio fue aprobado 🎉</h1>
                            <p style="margin:10px 0 0 0;font-size:14px;color:#111827;">Acercate al local cuando quieras a retirarlo.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px 0;font-size:15px;">Hola {{ $userName ?: 'Cliente' }},</p>
                            <p style="margin:0 0 18px 0;font-size:15px;">Tu canje de álbum fue aprobado y tenés listo tu premio:</p>

                            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin:0 0 20px 0;">
                                <p style="margin:0;font-size:16px;font-weight:600;color:#111827;">{{ $rewardValue }}</p>
                            </div>

                            {{-- Instrucciones de canje (configurables por el admin) --}}
                            @if (!empty($redemptionInstructions))
                                <div style="background:#ecfdf5;border:1px solid #10b981;border-radius:10px;padding:16px 18px;margin:0 0 20px 0;">
                                    <p style="margin:0 0 8px 0;font-size:13px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:0.5px;">Cómo canjear tu premio</p>
                                    <p style="margin:0;font-size:15px;color:#065f46;line-height:1.6;">{{ $redemptionInstructions }}</p>
                                </div>
                            @else
                                <p style="margin:0 0 8px 0;font-size:14px;color:#4b5563;">Recordá que los premios no incluyen envío. Podés pasar a retirar por el local en cualquier momento.</p>
                            @endif
                            <p style="margin:0 0 22px 0;font-size:14px;color:#4b5563;">Si tenés dudas, escribinos por WhatsApp desde la web.</p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="border-radius:10px;background:#f59e0b;">
                                        <a href="{{ route('loyalty.dashboard') }}" style="display:inline-block;padding:12px 18px;font-size:14px;font-weight:700;color:#111827;text-decoration:none;">
                                            Ver mi álbum
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 24px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#6b7280;">
                                Este correo fue enviado automáticamente por T cocina.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
