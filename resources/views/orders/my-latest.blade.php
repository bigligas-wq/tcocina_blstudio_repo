@extends('layouts.app')

@section('title', 'Seguí tu pedido — TCocina')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Bangers&display=swap');

    .myped-page {
        min-height: calc(100vh - 64px);
        background: linear-gradient(160deg, #0c1929 0%, #0f2744 55%, #0c1929 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .myped-wrap {
        width: 100%;
        max-width: 420px;
        text-align: center;
    }

    .myped-logo {
        width: 108px;
        height: 108px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(0, 150, 199, 0.45);
        box-shadow: 0 0 0 6px rgba(0, 150, 199, 0.08), 0 8px 32px rgba(0, 0, 0, 0.35);
        margin-bottom: 1.4rem;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .myped-title {
        font-family: 'Bangers', cursive;
        font-size: clamp(2.6rem, 7vw, 3.2rem);
        letter-spacing: 1.5px;
        color: #e8edf5;
        line-height: 1;
        margin-bottom: .45rem;
    }

    .myped-sub {
        color: rgba(200, 214, 232, 0.55);
        font-size: .9rem;
        margin-bottom: 1.8rem;
        line-height: 1.5;
    }

    .myped-card {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 18px;
        padding: 1.6rem 1.5rem;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        text-align: left;
    }

    .myped-card-label {
        color: #c8d6e8;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: .3rem;
    }

    .myped-card-hint {
        color: rgba(200, 214, 232, 0.45);
        font-size: .78rem;
        margin-bottom: 1.1rem;
        line-height: 1.55;
    }

    .myped-input {
        background: rgba(255, 255, 255, 0.06) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #e8edf5 !important;
        border-radius: 10px !important;
        padding: .7rem 1rem !important;
        font-size: 1.05rem !important;
        letter-spacing: .1em !important;
        width: 100%;
        margin-bottom: .85rem;
        transition: border-color .2s, box-shadow .2s;
    }

    .myped-input::placeholder {
        color: rgba(200, 214, 232, 0.25) !important;
        letter-spacing: .05em !important;
    }

    .myped-input:focus {
        outline: none !important;
        border-color: rgba(0, 150, 199, 0.55) !important;
        box-shadow: 0 0 0 3px rgba(0, 150, 199, 0.14) !important;
        background: rgba(255, 255, 255, 0.09) !important;
    }

    .myped-btn {
        width: 100%;
        padding: .75rem 1rem;
        background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%);
        border: none;
        border-radius: 10px;
        color: #fff;
        font-weight: 700;
        font-size: .98rem;
        letter-spacing: .3px;
        cursor: pointer;
        transition: opacity .2s, transform .15s;
    }

    .myped-btn:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    .myped-btn:active {
        transform: translateY(0);
    }

    .myped-input.input-error {
        border-color: rgba(248, 113, 113, 0.6) !important;
        box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.12) !important;
    }

    .myped-error {
        display: flex;
        align-items: flex-start;
        gap: .5rem;
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.3);
        border-radius: 10px;
        padding: .7rem .9rem;
        margin-top: .6rem;
        color: #fca5a5;
        font-size: .82rem;
        line-height: 1.45;
    }

    .myped-error i { flex-shrink: 0; margin-top: 1px; }

    .myped-btn:disabled {
        opacity: .65;
        cursor: not-allowed;
        transform: none !important;
    }
</style>
@endpush

@section('content')
<div class="myped-page">
    <div class="myped-wrap">

        <img src="{{ asset('images/tcocinalogin-sm.png') }}?v=2"
             alt="T Cocina"
             class="myped-logo"
             onerror="this.onerror=null;this.src='{{ asset('images/log.png') }}';">

        <h1 class="myped-title">Seguí tu pedido</h1>
        <p class="myped-sub">Ingresá el código y mirá en qué paso está tu pedido.</p>

        <div class="myped-card">
            <div class="myped-card-label">Código de pedido</div>
            <div class="myped-card-hint">
                Lo encontrás en el WhatsApp que te enviamos al confirmar.
                Formato: <strong style="color:#93c5fd;">ORD-XXXXXXXX</strong>
            </div>

            <form id="tracking-search-form">
                <input
                    type="text"
                    id="order-number-input"
                    class="myped-input"
                    placeholder="ORD-XXXXXXXX"
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                >
                <button class="myped-btn" id="tracking-btn" type="submit">
                    <i class="fas fa-search me-2"></i>Buscar pedido
                </button>
                <div id="tracking-error" class="myped-error d-none">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="tracking-error-msg">Ingresá el código de pedido (ej: ORD-AB12CD34)</span>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
(function () {
    var form  = document.getElementById('tracking-search-form');
    var input = document.getElementById('order-number-input');
    var error = document.getElementById('tracking-error');
    var errorMsg = document.getElementById('tracking-error-msg');
    var btn   = document.getElementById('tracking-btn');

    function showError(msg) {
        errorMsg.textContent = msg;
        error.classList.remove('d-none');
        input.classList.add('input-error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search me-2"></i>Buscar pedido';
    }

    function clearError() {
        error.classList.add('d-none');
        input.classList.remove('input-error');
    }

    input.addEventListener('input', clearError);

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        // Strip # and * that WhatsApp adds around the code, then normalize
        var raw = input.value.replace(/[#*]/g, '').trim().toUpperCase();

        if (!raw) {
            showError('Ingresá el código de pedido (ej: ORD-AB12CD34)');
            return;
        }

        var code = raw.startsWith('ORD-') ? raw : 'ORD-' + raw;
        clearError();

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Buscando...';

        try {
            var res = await fetch('/api/pedido/' + encodeURIComponent(code) + '/estado');
            if (!res.ok) {
                showError('No encontramos ningún pedido con ese código. Revisá que esté bien escrito.');
                return;
            }
            window.location.href = '/pedido/' + encodeURIComponent(code) + '/seguimiento';
        } catch (_) {
            showError('Hubo un problema al buscar el pedido. Intentá de nuevo.');
        }
    });
})();
</script>
@endsection
