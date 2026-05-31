@extends('layouts.admin')

@section('title', 'Configuración del Laboratorio · BLStudio')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if (session('success'))
        <div class="lab-alert success">{{ session('success') }}</div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Configuración</h1>
        <p>Datos de contacto y de transferencia que ve el cliente en el modal de pago.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.admin.index') }}" class="lab-btn lab-btn-ghost">← Volver</a>
        </div>
    </div>

    @if (empty($settings['whatsapp']))
        <div class="lab-alert warning">
            ⚠ Falta configurar el WhatsApp. Sin él, el cliente no podrá enviar el pedido por WhatsApp.
        </div>
    @endif

    <form method="POST" action="{{ route('laboratorio.admin.config.update') }}">
        @csrf
        @method('PUT')

        <div class="lab-history-card">
            <div class="lab-section-head" style="margin-top:0;">
                <div>
                    <span class="lab-eyebrow">Contacto</span>
                    <h2 class="lab-display">Datos del developer</h2>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="lab-form-group">
                    <label>WhatsApp (formato internacional)</label>
                    <input type="text" name="whatsapp" value="{{ $settings['whatsapp'] }}" placeholder="+5491112345678">
                    <small style="color: var(--lab-text-muted);">Sin espacios ni guiones. Ej: +5491112345678</small>
                </div>
                <div class="lab-form-group">
                    <label>Email de notificaciones</label>
                    <input type="email" name="email" value="{{ $settings['email'] }}" required>
                </div>
            </div>
        </div>

        <div class="lab-history-card">
            <div class="lab-section-head" style="margin-top:0;">
                <div>
                    <span class="lab-eyebrow">Transferencia</span>
                    <h2 class="lab-display">Datos bancarios</h2>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="lab-form-group">
                    <label>Alias</label>
                    <input type="text" name="alias" value="{{ $settings['alias'] }}">
                </div>
                <div class="lab-form-group">
                    <label>CBU / CVU</label>
                    <input type="text" name="cbu" value="{{ $settings['cbu'] }}">
                </div>
                <div class="lab-form-group">
                    <label>Banco</label>
                    <input type="text" name="banco" value="{{ $settings['banco'] }}">
                </div>
                <div class="lab-form-group">
                    <label>Titular</label>
                    <input type="text" name="titular" value="{{ $settings['titular'] }}">
                </div>
            </div>
        </div>

        <div style="margin-top: 16px;">
            <button type="submit" class="lab-btn lab-btn-primary">Guardar configuración</button>
        </div>
    </form>
</div>
@endsection
