@extends('errors.layout')

@section('title', '401 - Necesitás iniciar sesión')
@section('variant', 'variant-info')
@section('code')
    <span class="c1">4</span><span class="c2">0</span><span class="c3">1</span>
@endsection
@section('heading', 'Necesitás iniciar sesión')
@section('message')
    Esta sección es solo para usuarios autenticados. Iniciá sesión con tu cuenta para continuar.
@endsection
@section('tip')
    Si ya tenés una cuenta, hacé clic en <strong>Iniciar sesión</strong>. Si no, podés crear una en segundos.
@endsection
@section('icon', 'bx bx-lock-alt')
@section('actions')
    <a href="{{ route('login') }}" class="btn-rocker-primary">
        <i class='bx bx-log-in'></i> Iniciar sesión
    </a>
    @if (Route::has('register'))
        <a href="{{ route('register') }}" class="btn-rocker-outline">
            <i class='bx bx-user-plus'></i> Crear cuenta
        </a>
    @endif
    <a href="{{ url('/') }}" class="btn-rocker-outline">
        <i class='bx bx-home-alt'></i> Inicio
    </a>
@endsection
