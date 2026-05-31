@extends('layouts.admin')

@section('title', 'Wishlist · Laboratorio')

@include('laboratorio._head')

@section('content')
<div class="lab-app">
    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Tu wishlist</h1>
        <p>Mejoras que guardaste para más adelante. Cuando quieras, las pasás al carrito.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.index') }}" class="lab-btn lab-btn-ghost">← Volver al catálogo</a>
        </div>
    </div>

    @if ($items->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 40px;">
            <p style="color: var(--lab-text-muted); margin-bottom: 16px;">Todavía no guardaste ninguna mejora.</p>
            <a href="{{ route('laboratorio.index') }}" class="lab-btn lab-btn-primary">Ver catálogo</a>
        </div>
    @else
        <div class="lab-grid">
            @foreach ($items as $imp)
                @php
                    $catClass = match($imp->categoria) {
                        'visual' => 'amber', 'ux' => 'blue', 'performance' => 'purple', 'admin' => 'green', default => 'muted',
                    };
                @endphp
                <div class="lab-card"
                     data-improvement-id="{{ $imp->id }}"
                     data-nombre="{{ $imp->nombre }}"
                     data-precio="{{ $imp->precio_efectivo }}"
                     data-icono="{{ $imp->icono }}"
                     data-categoria="{{ $imp->categoria }}">

                    <button class="lab-wishlist-btn saved" data-action="wishlist" data-id="{{ $imp->id }}">♥</button>

                    <div style="display:flex; gap: 8px; align-items: flex-start; padding-right: 40px;">
                        <div class="lab-card-icon">{{ $imp->icono ?: '✨' }}</div>
                        <div class="lab-card-badges">
                            <span class="lab-badge {{ $catClass }}">{{ $imp->categoria }}</span>
                        </div>
                    </div>

                    <h3>{{ $imp->nombre }}</h3>
                    <p class="lab-card-desc">{{ $imp->descripcion_corta }}</p>

                    <div class="lab-card-footer">
                        <div class="lab-card-price">
                            <small>USD</small>
                            {{ number_format($imp->precio_efectivo, 0) }}
                        </div>
                        <a href="{{ route('laboratorio.index') }}#improvement-{{ $imp->id }}" class="lab-btn lab-btn-primary" style="font-size:12px;">Ver en catálogo</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
window.LAB_URLS = { wishlistToggle: @json(route('laboratorio.wishlist.toggle')) };
</script>
<script src="{{ asset('js/laboratorio.js') }}?v={{ filemtime(public_path('js/laboratorio.js')) }}"></script>
@endpush
@endsection
