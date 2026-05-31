@extends('errors.layout')

@section('title', '404 - Página no encontrada')
@section('code')
    <span class="c1">4</span><span class="c2">0</span><span class="c3">4</span>
@endsection
@section('heading', '¡Ups! No encontramos esa página')
@section('message')
    El enlace que seguiste puede estar roto o la página puede haber sido movida.
    No te preocupes, te ayudamos a volver al camino correcto.
@endsection
@section('tip')
    Verificá que la URL esté bien escrita o usá los botones de abajo para volver al inicio.
@endsection
@section('icon', 'bx bx-search-alt')
@section('actions')
    <a href="{{ url('/') }}" class="btn-rocker-primary">
        <i class='bx bx-home-alt'></i> Ir al Inicio
    </a>
    <a href="javascript:history.back()" class="btn-rocker-outline">
        <i class='bx bx-arrow-back'></i> Volver
    </a>
@endsection
