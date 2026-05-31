@extends('errors.layout')

@section('title', '419 - Sesión expirada')
@section('variant', 'variant-warning')
@section('code')
    <span class="c1">4</span><span class="c2">1</span><span class="c3">9</span>
@endsection
@section('heading', 'Tu sesión expiró por seguridad')
@section('message')
    Estuviste un rato sin actividad y por seguridad cerramos la sesión del formulario.
    No es un error grave: simplemente recargá la página o volvé a iniciar sesión y todo va a funcionar normal.
@endsection
@section('tip')
    Si estabas completando un formulario, los datos no se enviaron. Hacé clic en <strong>Recargar</strong> y volvé a intentarlo.
@endsection
@section('icon', 'bx bx-time-five')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn-rocker-primary">
        <i class='bx bx-refresh'></i> Recargar y reintentar
    </a>
    <a href="{{ route('login') }}" class="btn-rocker-outline">
        <i class='bx bx-log-in'></i> Iniciar sesión
    </a>
    <a href="{{ url('/') }}" class="btn-rocker-outline">
        <i class='bx bx-home-alt'></i> Inicio
    </a>
@endsection
