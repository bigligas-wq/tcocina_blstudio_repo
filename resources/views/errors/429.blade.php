@extends('errors.layout')

@section('title', '429 - Demasiadas solicitudes')
@section('variant', 'variant-warning')
@section('code')
    <span class="c1">4</span><span class="c2">2</span><span class="c3">9</span>
@endsection
@section('heading', 'Tranqui, vas muy rápido')
@section('message')
    Detectamos demasiadas solicitudes en muy poco tiempo desde tu conexión.
    Esperá unos segundos y volvé a intentarlo.
@endsection
@section('tip')
    Esto suele pasar al recargar muchas veces seguidas. Esperá un momento y se restablece automáticamente.
@endsection
@section('icon', 'bx bx-stopwatch')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn-rocker-primary">
        <i class='bx bx-refresh'></i> Reintentar
    </a>
    <a href="{{ url('/') }}" class="btn-rocker-outline">
        <i class='bx bx-home-alt'></i> Inicio
    </a>
@endsection
