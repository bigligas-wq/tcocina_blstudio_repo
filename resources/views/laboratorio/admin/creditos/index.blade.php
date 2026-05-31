@extends('layouts.admin')

@section('title', 'Créditos · BLStudio Lab')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if (session('success'))
        <div class="lab-alert success">{{ session('success') }}</div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Créditos del Lab</h1>
        <p>Otorgales saldo a tus clientes (bonos, créditos por feedback, ajustes). Lo usan automáticamente al pagar pedidos.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.admin.index') }}" class="lab-btn lab-btn-ghost">← Volver</a>
        </div>
    </div>

    <div class="lab-history-card">
        <div class="lab-section-head" style="margin-top:0;">
            <div>
                <span class="lab-eyebrow">Otorgar</span>
                <h2 class="lab-display">Sumar créditos a un cliente</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('laboratorio.admin.credits.grant') }}">
            @csrf
            <div style="display:grid; grid-template-columns: 2fr 1fr 2fr auto; gap: 12px; align-items: end;">
                <div class="lab-form-group" style="margin-bottom: 0;">
                    <label>Cliente</label>
                    <select name="user_id" required>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="lab-form-group" style="margin-bottom: 0;">
                    <label>Monto (USD)</label>
                    <input type="number" name="monto_usd" step="0.01" min="0.01" required>
                </div>
                <div class="lab-form-group" style="margin-bottom: 0;">
                    <label>Descripción / motivo</label>
                    <input type="text" name="descripcion" maxlength="200" required placeholder="Ej: bono de bienvenida">
                </div>
                <button type="submit" class="lab-btn lab-btn-primary">Otorgar</button>
            </div>
        </form>
    </div>

    <div class="lab-section-head">
        <div>
            <span class="lab-eyebrow">Wallets</span>
            <h2 class="lab-display">Saldos actuales</h2>
        </div>
    </div>

    @if ($wallets->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 40px;">
            <p style="color: var(--lab-text-muted);">Ningún cliente tiene créditos todavía.</p>
        </div>
    @else
        <table class="lab-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Balance</th>
                    <th>Últimos movimientos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($wallets as $w)
                    <tr>
                        <td><strong style="color:#fff;">{{ $w->user->name }}</strong><br><small style="color: var(--lab-text-muted);">{{ $w->user->email }}</small></td>
                        <td class="lab-mono" style="color: var(--lab-green); font-size: 18px; font-weight: 600;">USD {{ number_format($w->balance_usd, 2) }}</td>
                        <td>
                            @foreach ($w->movements as $m)
                                <div style="color: var(--lab-text-muted); font-size: 12px;">
                                    {{ $m->tipo === 'credito' ? '+' : '−' }}USD {{ number_format($m->monto_usd, 2) }} · {{ $m->descripcion }}
                                </div>
                            @endforeach
                            @if ($w->movements->isEmpty())
                                <span style="color: var(--lab-text-dim);">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
