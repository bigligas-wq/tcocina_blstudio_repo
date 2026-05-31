<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Nueva idea propuesta</title></head>
<body style="font-family: -apple-system, system-ui, sans-serif; background: #0e0d0c; color: #e8e8e8; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #1a1814; border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 24px;">
        <span style="font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #a78bfa;">BLStudio · Laboratorio</span>
        <h1 style="margin: 8px 0 16px; color: #fff; font-size: 26px; font-weight: 800;">💡 Nueva idea propuesta</h1>
        <p style="color: #9a9a9a; margin-bottom: 16px;">
            De <strong style="color:#fff;">{{ $cliente->name }}</strong> ({{ $cliente->email }}):
        </p>
        @if ($idea)
        <div style="background: #201e1b; padding: 16px; border-left: 3px solid #a78bfa; border-radius: 4px; margin-bottom: 16px;">
            <p style="margin: 0; color: #fff; white-space: pre-wrap; line-height: 1.6;">{{ $idea }}</p>
        </div>
        @endif
        @if ($imagenUrl)
        <div style="margin-bottom: 8px;">
            <span style="font-size: 12px; color: #9a9a9a; text-transform: uppercase; letter-spacing: 1px;">Imagen adjunta</span>
        </div>
        <img src="{{ $imagenUrl }}" alt="Idea adjunta" style="max-width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,.07);">
        @endif
    </div>
</body>
</html>
