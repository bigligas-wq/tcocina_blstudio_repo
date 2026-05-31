@php
    $devEmail = \App\Models\BusinessSetting::get('lab_developer_email', 'grandesligasarg@gmail.com');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nuevo pedido del Laboratorio</title>
</head>
<body style="font-family: -apple-system, system-ui, sans-serif; background: #0e0d0c; color: #e8e8e8; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #1a1814; border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 24px;">
        <div style="margin-bottom: 16px;">
            <span style="font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #f5a623;">BLStudio · Laboratorio</span>
            <h1 style="margin: 8px 0 4px; color: #fff; font-size: 28px; font-weight: 800;">Nuevo pedido recibido</h1>
            <p style="margin: 0; color: #9a9a9a;">Pedido <strong style="color:#fff;">{{ $order->order_number }}</strong></p>
        </div>

        <div style="background: #201e1b; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <p style="margin: 0 0 8px; color: #9a9a9a; font-size: 13px;">CLIENTE</p>
            <p style="margin: 0; color: #fff;"><strong>{{ $order->user->name }}</strong> · {{ $order->user->email }}</p>
        </div>

        <p style="color: #9a9a9a; font-size: 13px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px;">MEJORAS PEDIDAS</p>
        @foreach ($order->items as $item)
            <div style="background: #201e1b; border-left: 3px solid #f5a623; padding: 12px 16px; margin-bottom: 8px; border-radius: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <strong style="color:#fff;">{{ $item->nombre_snapshot }}</strong>
                    <span style="color:#f5a623; font-family: monospace; font-weight: 600;">USD {{ number_format($item->precio_usd_snapshot, 2) }}</span>
                </div>
                @if ($item->nota)
                    <p style="margin: 8px 0 0; color: #38b6ff; font-size: 14px; font-style: italic;">
                        &ldquo;{{ $item->nota }}&rdquo;
                    </p>
                @endif
            </div>
        @endforeach

        <div style="background: #1f1d1a; padding: 16px; border-radius: 8px; margin-top: 16px; text-align: right;">
            <span style="color: #9a9a9a; font-size: 13px;">TOTAL</span>
            <span style="color: #f5a623; font-family: monospace; font-size: 22px; font-weight: 800; margin-left: 12px;">USD {{ number_format($order->total_usd, 2) }}</span>
        </div>

        @if ($order->comprobante_url)
            <div style="margin-top: 16px;">
                <a href="{{ $order->comprobante_url }}" style="display: inline-block; background: #3ecf8e; color: #0e0d0c; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                    📎 Ver comprobante
                </a>
            </div>
        @endif

        <p style="color: #6a6a6a; margin-top: 24px; font-size: 12px;">
            Notificación enviada a {{ $devEmail }} desde el Laboratorio BLStudio en TCocina.
        </p>
    </div>
</body>
</html>
