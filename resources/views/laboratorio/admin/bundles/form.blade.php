@extends('layouts.admin')

@section('title', ($bundle->exists ? 'Editar' : 'Nuevo') . ' bundle · BLStudio Lab')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if ($errors->any())
        <div class="lab-alert error">
            <ul style="margin:0;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">{{ $bundle->exists ? 'Editar bundle' : 'Nuevo bundle' }}</h1>
        <p>Elegí 2 o más mejoras y un precio combinado. Mostrale al cliente cuánto se ahorra.</p>
    </div>

    <form action="{{ $bundle->exists ? route('laboratorio.admin.bundles.update', $bundle->id) : route('laboratorio.admin.bundles.store') }}" method="POST">
        @csrf
        @if ($bundle->exists) @method('PUT') @endif

        <div class="lab-history-card">
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                <div class="lab-form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $bundle->nombre) }}" maxlength="120" required>
                </div>
                <div class="lab-form-group">
                    <label>Icono (emoji)</label>
                    <input type="text" name="icono" value="{{ old('icono', $bundle->icono) }}" maxlength="16" placeholder="📦">
                </div>
            </div>

            <div class="lab-form-group">
                <label>Descripción corta (visible al cliente)</label>
                <input type="text" name="descripcion_corta" value="{{ old('descripcion_corta', $bundle->descripcion_corta) }}" maxlength="200">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="lab-form-group">
                    <label>Precio del bundle (USD)</label>
                    <input type="number" name="precio_bundle_usd" value="{{ old('precio_bundle_usd', $bundle->precio_bundle_usd) }}" step="0.01" min="0" required>
                </div>
                <div class="lab-form-group">
                    <label>Estado</label>
                    <select name="estado" required>
                        @foreach (\App\Models\LabBundle::ESTADOS as $e)
                            <option value="{{ $e }}" {{ old('estado', $bundle->estado) === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="lab-history-card">
            <div class="lab-section-head" style="margin-top:0;">
                <div>
                    <span class="lab-eyebrow">Contenido</span>
                    <h2 class="lab-display">Mejoras incluidas</h2>
                </div>
                <small style="color: var(--lab-text-muted);">Tildá al menos 2.</small>
            </div>

            @php $selectedIds = old('improvement_ids', $bundle->improvements->pluck('id')->all()); @endphp
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                @foreach ($allImprovements as $imp)
                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: var(--lab-surface-2); border-radius: 8px; cursor: pointer;">
                        <input type="checkbox" name="improvement_ids[]" value="{{ $imp->id }}" {{ in_array($imp->id, $selectedIds) ? 'checked' : '' }}>
                        <span style="flex: 1; color: #fff;">{{ $imp->icono }} {{ $imp->nombre }}</span>
                        <span class="lab-mono" style="color: var(--lab-amber);">USD {{ number_format($imp->precio_usd, 0) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex; gap: 8px;">
            <button type="submit" class="lab-btn lab-btn-primary">{{ $bundle->exists ? 'Guardar bundle' : 'Crear bundle' }}</button>
            <a href="{{ route('laboratorio.admin.bundles') }}" class="lab-btn lab-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
