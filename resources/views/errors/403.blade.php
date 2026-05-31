@extends('errors.layout')

@section('title', '403 - Acceso restringido')
@section('variant', 'variant-warning')
@section('code')
    <span class="c1">4</span><span class="c2">0</span><span class="c3">3</span>
@endsection
@section('heading', 'No tenés permisos para entrar acá')
@section('message')
    {{ $exception->getMessage() ?: 'Esta zona está reservada para usuarios con permisos especiales (por ejemplo, administradores o personal de cocina).' }}
@endsection
@section('tip')
    Si pensás que esto es un error, hablá con el administrador del sistema para que revise tus permisos.
@endsection
@section('icon', 'bx bx-shield-x')
@section('actions')
    <a href="{{ url('/') }}" class="btn-rocker-primary">
        <i class='bx bx-home-alt'></i> Volver al inicio
    </a>
    @auth
        <a href="javascript:history.back()" class="btn-rocker-outline">
            <i class='bx bx-arrow-back'></i> Atrás
        </a>
    @else
        <a href="{{ route('login') }}" class="btn-rocker-outline">
            <i class='bx bx-log-in'></i> Iniciar sesión
        </a>
    @endauth
@endsection
