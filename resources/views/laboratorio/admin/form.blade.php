@extends('layouts.admin')

@section('title', ($improvement->exists ? 'Editar' : 'Nueva') . ' mejora · Laboratorio')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if ($errors->any())
        <div class="lab-alert error">
            <strong>Revisá estos campos:</strong>
            <ul style="margin: 8px 0 0;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">{{ $improvement->exists ? 'Editar mejora' : 'Nueva mejora' }}</h1>
        <p>Definí cómo va a aparecer la mejora en el catálogo de tu cliente.</p>
    </div>

    <form action="{{ $improvement->exists ? route('laboratorio.admin.update', $improvement->id) : route('laboratorio.admin.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if ($improvement->exists) @method('PUT') @endif

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="lab-form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $improvement->nombre) }}" maxlength="120" required>
            </div>
            <div class="lab-form-group">
                <label>Icono (emoji)</label>
                <input type="text" name="icono" value="{{ old('icono', $improvement->icono) }}" maxlength="16" placeholder="🎨">
            </div>
        </div>

        <div class="lab-form-group">
            <label>Descripción corta (visible en la card)</label>
            <input type="text" name="descripcion_corta" value="{{ old('descripcion_corta', $improvement->descripcion_corta) }}" maxlength="160" required>
        </div>

        <div class="lab-form-group">
            <label>Descripción larga (visible en el modal preview)</label>
            <textarea name="descripcion_larga" rows="3" maxlength="2000">{{ old('descripcion_larga', $improvement->descripcion_larga) }}</textarea>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="lab-form-group">
                <label>Categoría</label>
                <select name="categoria" required>
                    @foreach (\App\Models\LabImprovement::CATEGORIAS as $cat)
                        <option value="{{ $cat }}" {{ old('categoria', $improvement->categoria) === $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="lab-form-group">
                <label>Precio (USD)</label>
                <input type="number" name="precio_usd" value="{{ old('precio_usd', $improvement->precio_usd) }}" step="0.01" min="0" required>
            </div>
            <div class="lab-form-group">
                <label>Estado</label>
                <select name="estado" required>
                    @foreach (\App\Models\LabImprovement::ESTADOS as $e)
                        <option value="{{ $e }}" {{ old('estado', $improvement->estado) === $e ? 'selected' : '' }}>
                            {{ ucfirst($e) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="lab-form-group">
                <label>Precio con descuento (USD) — opcional</label>
                <input type="number" name="precio_descuento_usd" value="{{ old('precio_descuento_usd', $improvement->precio_descuento_usd) }}" step="0.01" min="0">
                <small style="color: var(--lab-text-muted);">Si cargás esto, el cliente ve precio tachado + nuevo precio. Combinalo con "Descuento hasta" para timer.</small>
            </div>
            <div class="lab-form-group">
                <label>Descuento válido hasta</label>
                <input type="datetime-local" name="descuento_hasta" value="{{ old('descuento_hasta', optional($improvement->descuento_hasta)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="lab-form-group">
                <label>Tiempo estimado (horas)</label>
                <input type="number" name="tiempo_estimado_horas" value="{{ old('tiempo_estimado_horas', $improvement->tiempo_estimado_horas) }}" min="0" max="1000">
            </div>
            <div class="lab-form-group">
                <label>ROI estimado / impacto</label>
                <input type="text" name="roi_estimado" value="{{ old('roi_estimado', $improvement->roi_estimado) }}" maxlength="120" placeholder="+5% conversión / ahorra 4h al mes">
            </div>
        </div>

        <div class="lab-form-group">
            <label>
                <input type="hidden" name="es_destacada" value="0">
                <input type="checkbox" name="es_destacada" value="1" {{ old('es_destacada', $improvement->es_destacada) ? 'checked' : '' }}>
                <span style="color: var(--lab-amber); font-family: 'DM Mono', monospace; letter-spacing: 1.5px; font-size: 11px; margin-left: 6px;">★ MEJORA DESTACADA (banner principal)</span>
            </label>
            <small style="color: var(--lab-text-muted); display: block; margin-top: 4px;">Solo una mejora puede estar destacada al mismo tiempo.</small>
        </div>

        <div class="lab-form-group">
            <label>
                <input type="hidden" name="es_popular" value="0">
                <input type="checkbox" name="es_popular" value="1" {{ old('es_popular', $improvement->es_popular) ? 'checked' : '' }}>
                <span style="color: var(--lab-red); font-family: 'DM Mono', monospace; letter-spacing: 1.5px; font-size: 11px; margin-left: 6px;">🔥 POPULAR (badge "Popular" en la card)</span>
            </label>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px;">
            <div class="lab-form-group">
                <label>Imagen ANTES (captura actual)</label>
                @if ($improvement->imagen_antes_url)
                    <img src="{{ $improvement->imagen_antes_url }}" style="max-width:100%; border-radius: 8px; margin-bottom: 8px;">
                @endif
                <input type="file" name="imagen_antes_file" accept="image/*">
            </div>
            <div class="lab-form-group">
                <label>Imagen DESPUÉS (mockup mejorado)</label>
                @if ($improvement->imagen_despues_url)
                    <img src="{{ $improvement->imagen_despues_url }}" style="max-width:100%; border-radius: 8px; margin-bottom: 8px;">
                @endif
                <input type="file" name="imagen_despues_file" accept="image/*">
            </div>
        </div>

        <div class="lab-section-head" style="margin-top: 32px;">
            <div>
                <span class="lab-eyebrow">Beneficios</span>
                <h2 class="lab-display">Diferencias / bullets</h2>
            </div>
            <button type="button" class="lab-btn" onclick="addDiff()">＋ Agregar bullet</button>
        </div>

        <div id="diff-list">
            @php $existing = old('diferencias', $improvement->diferencias ?? []); @endphp
            @foreach ($existing as $i => $d)
                <div class="lab-form-group" style="display:grid; grid-template-columns: 120px 1fr auto; gap: 8px; align-items:end; margin-bottom: 8px;">
                    <div>
                        <label>Color</label>
                        <input type="color" name="diferencias[{{ $i }}][color]" value="{{ $d['color'] ?? '#3ecf8e' }}" style="height:40px;">
                    </div>
                    <div>
                        <label>Texto</label>
                        <input type="text" name="diferencias[{{ $i }}][texto]" value="{{ $d['texto'] ?? '' }}" maxlength="200">
                    </div>
                    <button type="button" class="lab-btn lab-btn-ghost" onclick="this.parentElement.remove()" style="height: 40px;">×</button>
                </div>
            @endforeach
        </div>

        <div style="display:flex; gap: 8px; margin-top: 24px;">
            <button type="submit" class="lab-btn lab-btn-primary">
                {{ $improvement->exists ? 'Guardar cambios' : 'Crear mejora' }}
            </button>
            <a href="{{ route('laboratorio.admin.index') }}" class="lab-btn lab-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let diffIdx = {{ count($existing) }};
function addDiff() {
    const list = document.getElementById('diff-list');
    const wrap = document.createElement('div');
    wrap.className = 'lab-form-group';
    wrap.style.cssText = 'display:grid; grid-template-columns: 120px 1fr auto; gap: 8px; align-items:end; margin-bottom: 8px;';
    wrap.innerHTML = `
        <div>
            <label>Color</label>
            <input type="color" name="diferencias[${diffIdx}][color]" value="#3ecf8e" style="height:40px;">
        </div>
        <div>
            <label>Texto</label>
            <input type="text" name="diferencias[${diffIdx}][texto]" maxlength="200">
        </div>
        <button type="button" class="lab-btn lab-btn-ghost" onclick="this.parentElement.remove()" style="height: 40px;">×</button>
    `;
    list.appendChild(wrap);
    diffIdx++;
}
</script>
@endpush
@endsection
