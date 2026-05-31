@extends('errors.layout')

@section('title', '500 - Error del servidor')
@section('variant', 'variant-danger')
@section('code')
    <span class="c1">5</span><span class="c2">0</span><span class="c3">0</span>
@endsection
@section('heading', 'Algo salió mal de nuestro lado')
@section('message')
    Tuvimos un problema procesando tu pedido. Ya lo registramos y nuestro equipo lo va a revisar.
    Probá recargar en unos segundos; si el problema persiste, escribinos.
@endsection
@section('tip')
    Mientras tanto podés volver al inicio o revisar el menú. Tu carrito y tu sesión siguen guardados.
@endsection
@section('icon', 'bx bx-error')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn-rocker-primary">
        <i class='bx bx-refresh'></i> Reintentar
    </a>
    <a href="{{ url('/') }}" class="btn-rocker-outline">
        <i class='bx bx-home-alt'></i> Ir al inicio
    </a>
@endsection
