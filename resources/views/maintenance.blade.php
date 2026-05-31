@extends('layouts.app')

@section('title', 'TCocina - Hoy el local esta cerrado')

@section('content')
    <div class="container py-5">
        <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 60vh;">
            <img src="{{ asset('productos/fondo/tcocinapng.png') }}" alt="TCocina" style="width:140px;height:auto;filter:grayscale(100%);opacity:.9;display:block;margin:0 auto"/>
            <h3 class="mt-3 fw-bold">{{ $businessSettings['site_offline_title'] ?? 'T cocina' }}</h3>
            <h5 class="fw-semibold">{{ $businessSettings['site_offline_message'] ?? 'Por el momento no estamos tomando pedidos.' }}</h5>
            @php
                $wa = $businessSettings['whatsapp_number'] ?? null;
                $waUrl = $wa ? ('https://wa.me/'.preg_replace('/[^0-9]/','',$wa)) : null;
            @endphp
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-success" style="border-radius:999px;padding:.6rem 1.2rem"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
            @endif
        </div>
    </div>
@endsection
