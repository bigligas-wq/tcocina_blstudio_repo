@extends('layouts.admin')

@section('title', 'Changelog · BLStudio Lab')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if (session('success'))
        <div class="lab-alert success">{{ session('success') }}</div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Changelog</h1>
        <p>Lo que el cliente ve cuando entra al Lab. Avisalé las novedades, los lanzamientos y las promos.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.admin.index') }}" class="lab-btn lab-btn-ghost">← Volver</a>
        </div>
    </div>

    <div class="lab-history-card">
        <div class="lab-section-head" style="margin-top:0;">
            <div>
                <span class="lab-eyebrow">Publicar</span>
                <h2 class="lab-display">Nueva entrada</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('laboratorio.admin.changelog.store') }}">
            @csrf
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="lab-form-group">
                    <label>Tipo</label>
                    <select name="tipo" required>
                        @foreach (\App\Models\LabChangelogEntry::TIPOS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lab-form-group">
                    <label>Icono (emoji)</label>
                    <input type="text" name="icono" maxlength="16" placeholder="🚀">
                </div>
                <div class="lab-form-group">
                    <label>Mejora vinculada (opcional)</label>
                    <select name="lab_improvement_id">
                        <option value="">— Ninguna —</option>
                        @foreach (\App\Models\LabImprovement::orderBy('nombre')->get() as $imp)
                            <option value="{{ $imp->id }}">{{ $imp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="lab-form-group">
                <label>Título</label>
                <input type="text" name="titulo" required maxlength="160" placeholder="Ej: Nueva mejora: Buscador inteligente">
            </div>
            <div class="lab-form-group">
                <label>Cuerpo (opcional)</label>
                <textarea name="cuerpo" rows="2" maxlength="2000" placeholder="Detalle breve para el cliente"></textarea>
            </div>
            <button type="submit" class="lab-btn lab-btn-primary">Publicar entrada</button>
        </form>
    </div>

    <div class="lab-section-head">
        <div>
            <span class="lab-eyebrow">Histórico</span>
            <h2 class="lab-display">Entradas publicadas</h2>
        </div>
    </div>

    @if ($entries->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 30px;">
            <p style="color: var(--lab-text-muted);">Todavía no hay entradas.</p>
        </div>
    @else
        @foreach ($entries as $entry)
            <div class="lab-changelog-entry" style="background: var(--lab-surface); border: 1px solid var(--lab-border); border-radius: 10px; padding: 14px; margin-bottom: 8px;">
                <div class="lab-changelog-dot">{{ $entry->icono ?: '•' }}</div>
                <div class="lab-changelog-body">
                    <h4>{{ $entry->titulo }}</h4>
                    @if ($entry->cuerpo)
                        <p>{{ $entry->cuerpo }}</p>
                    @endif
                    <div class="lab-changelog-meta">
                        {{ $entry->publicado_en->format('d/m/Y H:i') }} · {{ \App\Models\LabChangelogEntry::TIPOS[$entry->tipo] ?? $entry->tipo }}
                        @if ($entry->improvement)
                            · <span style="color: var(--lab-amber);">{{ $entry->improvement->nombre }}</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('laboratorio.admin.changelog.destroy', $entry->id) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                    @csrf
                    @method('DELETE')
                    <button class="lab-btn lab-btn-ghost" style="color: var(--lab-red); font-size: 11px; padding: 4px 10px;">×</button>
                </form>
            </div>
        @endforeach

        {{ $entries->links() }}
    @endif
</div>
@endsection
