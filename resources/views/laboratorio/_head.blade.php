@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600&family=Instrument+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/laboratorio.css') }}?v={{ filemtime(public_path('css/laboratorio.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/laboratorio-redesign.css') }}?v={{ filemtime(public_path('css/laboratorio-redesign.css')) }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js" defer></script>
@endpush
