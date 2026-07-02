<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Cómo estuvo tu experiencia? - T Cocina</title>
</head>
<body style="margin:0;padding:0;background:#f8f9fa;font-family:Roboto,-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8f9fa;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" max-width="480" cellspacing="0" cellpadding="0" border="0" style="max-width:480px;width:100%;background:#ffffff;border-radius:16px;border:1px solid #e8eaed;box-shadow:0 4px 16px rgba(0,0,0,.08);">
                    <tr>
                        <td align="center" style="padding:36px 28px 28px;">
                            <!-- Google G -->
                            <img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png" alt="Google" width="92" height="30" style="display:block;margin:0 auto 16px;">

                            <h1 style="font-size:1.1rem;font-weight:500;color:#202124;margin:0 0 6px;line-height:1.3;text-align:center;">
                                ¿Cómo estuvo tu experiencia?
                            </h1>
                            <p style="font-size:.88rem;color:#5f6368;margin:0 0 20px;line-height:1.4;text-align:center;">
                                Pedido <strong style="color:#202124;">#{{ $order->order_number }}</strong> entregado
                            </p>

                            <!-- Stars image -->
                            <div style="text-align:center;margin-bottom:20px;">
                                <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="" width="0" height="0" style="display:none;">
                                <span style="font-size:2rem;letter-spacing:4px;">⭐⭐⭐⭐⭐</span>
                            </div>

                            <p style="font-size:.95rem;color:#5f6368;margin:0 0 4px;line-height:1.45;text-align:center;">
                                Nos ayudás muchísimo dejándonos tu reseña
                            </p>
                            <p style="font-size:.82rem;color:#80868b;margin:0 0 24px;line-height:1.4;text-align:center;">
                                Tu opinión nos ayuda a seguir mejorando
                            </p>

                            <!-- CTA Button -->
                            <a href="https://g.page/r/CepJ7XpQQOkyEBM/review" target="_blank" style="display:inline-block;padding:13px 28px;border-radius:20px;border:1px solid #dadce0;background:#fff;color:#1a73e8;font-size:.95rem;font-weight:500;text-decoration:none;text-align:center;">
                                <img src="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_16dp.png" alt="" width="16" height="16" style="vertical-align:middle;margin-right:6px;">
                                Escribir reseña en Google
                            </a>

                            <p style="font-size:.78rem;color:#9aa0a6;margin:16px 0 0;line-height:1.4;text-align:center;">
                                ¿Ya dejaste tu reseña? <a href="{{ route('thanks.completed', $order->order_number) }}" style="color:#1a73e8;text-decoration:underline;">Hacé clic aquí</a> para no recibir más recordatorios.
                            </p>
                            <p style="font-size:.78rem;color:#9aa0a6;margin:12px 0 0;line-height:1.4;text-align:center;">
                                ¿Tuviste algún problema? Escribinos directamente respondiendo a este email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 28px 20px;">
                            <p style="font-size:.72rem;color:#bdc1c6;margin:0;text-align:center;">
                                T Cocina &middot; Hamburguesas artesanales
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
