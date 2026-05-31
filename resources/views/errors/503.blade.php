@extends('errors.layout')

@section('title', '503 - En mantenimiento')
@section('variant', 'variant-warning')
@section('code')
    <span class="c1">5</span><span class="c2">0</span><span class="c3">3</span>
@endsection
@section('heading', 'Estamos haciendo mejoras')
@section('message')
    El sitio está temporalmente en mantenimiento. Volvé en unos minutos: vamos a estar online enseguida con todo funcionando mejor.
@endsection
@section('tip')
    Si esto te urge, podés escribirnos y te respondemos lo antes posible.
@endsection
@section('icon', 'bx bx-cog bx-spin')
@section('actions')
    <a href="{{ url('/') }}" class="btn-rocker-primary">
        <i class='bx bx-refresh'></i> Reintentar
    </a>
@endsection
