@extends('layouts.admin')

@section('title', 'Bundles · BLStudio Lab')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if (session('success'))
        <div class="lab-alert success">{{ session('success') }}</div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Packs / Bundles</h1>
        <p>Combiná 2 o más mejoras a un precio especial. Sube el ticket promedio y le facilita la decisión al cliente.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.admin.bundles.create') }}" class="lab-btn lab-btn-primary">＋ Nuevo bundle</a>
            <a href="{{ route('laboratorio.admin.index') }}" class="lab-btn lab-btn-ghost">← Volver</a>
        </div>
    </div>

    @if ($bundles->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 40px;">
            <p style="color: var(--lab-text-muted); margin-bottom: 16px;">Todavía no creaste ningún bundle.</p>
            <a href="{{ route('laboratorio.admin.bundles.create') }}" class="lab-btn lab-btn-primary">Crear el primero</a>
        </div>
    @else
        @foreach ($bundles as $bundle)
            <div class="lab-bundle-card">
                <h3>{{ $bundle->icono }} {{ $bundle->nombre }}</h3>
                @if ($bundle->descripcion_corta)
                    <p class="bundle-desc">{{ $bundle->descripcion_corta }}</p>
                @endif
                <ul class="lab-bundle-improvements">
                    @foreach ($bundle->improvements as $imp)
                        <li>{{ $imp->icono }} {{ $imp->nombre }}</li>
                    @endforeach
                </ul>
                <div class="lab-bundle-price">
                    <div>
                        <div class="now"><small>USD</small> {{ number_format($bundle->precio_bundle_usd, 0) }}</div>
                        @if ($bundle->ahorro_usd > 0)
                            <div class="before">USD {{ number_format($bundle->precio_original, 0) }}</div>
                            <span class="lab-bundle-savings">Ahorrás USD {{ number_format($bundle->ahorro_usd, 0) }} · -{{ $bundle->ahorro_porcentaje }}%</span>
                        @endif
                    </div>
                    <div style="display:flex; gap: 8px; align-items: center;">
                        <span class="lab-badge {{ $bundle->estado === 'publicado' ? 'green' : 'muted' }}">{{ $bundle->estado }}</span>
                        <a href="{{ route('laboratorio.admin.bundles.edit', $bundle->id) }}" class="lab-btn">Editar</a>
                        <form action="{{ route('laboratorio.admin.bundles.destroy', $bundle->id) }}" method="POST" onsubmit="return confirm('¿Eliminar bundle?')">
                            @csrf
                            @method('DELETE')
                            <button class="lab-btn lab-btn-ghost" style="color: var(--lab-red);">×</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
