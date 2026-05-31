@extends('layouts.admin')

@section('title', 'Gestionar Laboratorio · BLStudio')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if (session('success'))
        <div class="lab-alert success">{{ session('success') }}</div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Gestionar mejoras</h1>
        <p>Cargá, editá y publicá las mejoras que va a ver el cliente en su Laboratorio.</p>

        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.admin.create') }}" class="lab-btn lab-btn-primary">
                ＋ Nueva mejora
            </a>
            <a href="{{ route('laboratorio.admin.bundles') }}" class="lab-btn">
                📦 Bundles
            </a>
            <a href="{{ route('laboratorio.admin.changelog') }}" class="lab-btn">
                📰 Changelog
            </a>
            <a href="{{ route('laboratorio.admin.credits') }}" class="lab-btn">
                💎 Créditos
            </a>
            <a href="{{ route('laboratorio.admin.orders') }}" class="lab-btn">
                📋 Pedidos
            </a>
            <a href="{{ route('laboratorio.admin.config') }}" class="lab-btn lab-btn-ghost">
                ⚙ Configuración
            </a>
            <a href="{{ route('laboratorio.index') }}" class="lab-btn lab-btn-ghost">
                👁 Ver como cliente
            </a>
        </div>
    </div>

    <div class="lab-section-head">
        <div>
            <span class="lab-eyebrow">Catálogo</span>
            <h2 class="lab-display">Mejoras cargadas · {{ $improvements->count() }}</h2>
        </div>
    </div>

    @if ($improvements->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 40px;">
            <p style="color: var(--lab-text-muted); margin-bottom: 16px;">Todavía no cargaste ninguna mejora.</p>
            <a href="{{ route('laboratorio.admin.create') }}" class="lab-btn lab-btn-primary">Cargar la primera</a>
        </div>
    @else
        <table class="lab-table">
            <thead>
                <tr>
                    <th>Mejora</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Destacada</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($improvements as $imp)
                    <tr>
                        <td>
                            <strong style="color:#fff;">{{ $imp->icono }} {{ $imp->nombre }}</strong><br>
                            <small style="color: var(--lab-text-muted);">{{ $imp->descripcion_corta }}</small>
                        </td>
                        <td>
                            @php
                                $catClass = match($imp->categoria) {
                                    'visual' => 'amber',
                                    'ux' => 'blue',
                                    'performance' => 'purple',
                                    'admin' => 'green',
                                    default => 'muted',
                                };
                            @endphp
                            <span class="lab-badge {{ $catClass }}">{{ $imp->categoria }}</span>
                        </td>
                        <td class="lab-mono" style="color: var(--lab-amber); font-weight:600;">
                            USD {{ number_format($imp->precio_usd, 2) }}
                        </td>
                        <td>
                            @php
                                $estadoClass = match($imp->estado) {
                                    'publicada' => 'green',
                                    'borrador' => 'muted',
                                    'archivada' => 'red',
                                    default => 'muted',
                                };
                            @endphp
                            <span class="lab-badge {{ $estadoClass }}">{{ $imp->estado }}</span>
                        </td>
                        <td>
                            @if ($imp->es_destacada)
                                <span class="lab-badge amber">★ Destacada</span>
                            @else
                                <span style="color: var(--lab-text-dim);">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('laboratorio.admin.edit', $imp->id) }}" class="lab-btn" style="font-size:11px; padding:6px 12px;">Editar</a>
                            <form action="{{ route('laboratorio.admin.destroy', $imp->id) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('¿Eliminar esta mejora?');">
                                @csrf
                                @method('DELETE')
                                <button class="lab-btn lab-btn-ghost" style="font-size:11px; padding:6px 12px; color: var(--lab-red);">×</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
