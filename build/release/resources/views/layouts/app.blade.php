<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TecoCina - Hamburguesas Artesanales')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Beach Theme CSS -->
    <link href="{{ asset('css/custom-beach-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="bg-white @if (request()->routeIs('home') ||
        request()->routeIs('catalog') ||
        request()->routeIs('category') ||
        request()->routeIs('product') ||
        request()->routeIs('cart') ||
        request()->routeIs('checkout') ||
        request()->routeIs('order.confirmation')) order-flow-bg @endif">
    <!-- Header Navigation -->
    <header class="bg-white shadow-sm border-bottom sticky-top">
        <div class="container">
            <div class="position-relative d-flex justify-content-between align-items-center"
                style="min-height: 88px; padding: 8px 0;">
                <!-- Left: Brand text -->
                <div class="d-flex align-items-center">


                    <span class="fs-4 fw-bold text-beach-dark">TeCocina</span>

                </div>

                <!-- Absolute centered Logo -->
                <div class="position-absolute top-50 start-50 translate-middle">
                    <a href="{{ route('catalog') }}" class="text-decoration-none d-flex align-items-center">
                        <img src="{{ asset('images/log.png') }}" alt="TeCocina" style="height: 52px;"
                            onerror="this.onerror=null; this.src='{{ asset('productos/logo-sol.png') }}';" />
                    </a>
                </div>

                <!-- Right: Cart & Menu -->
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Cart Button -->
                    <button id="cartToggle" class="btn position-relative text-beach-dark hover-beach-primary">
                        <i class="fas fa-shopping-cart fs-5"></i>
                        <span id="cartBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-beach-accent text-white d-none">0</span>
                    </button>



                    <!-- User Menu -->
                    @auth
                        <div class="dropdown">
                            <button class="btn text-beach-dark hover-beach-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mi Perfil</a>
                                </li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-list me-2"></i>Mis Pedidos</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                @if (Auth::user()->role === 'admin')
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i
                                                class="fas fa-cog me-2"></i>Administración</a></li>
                                @endif
                                @if (in_array(Auth::user()->role, ['admin', 'kitchen']))
                                    <li><a class="dropdown-item" href="{{ route('kitchen.index') }}"><i
                                                class="fas fa-fire me-2"></i>Cocina</a></li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>

            @if (request()->routeIs('catalog'))
                <div class="d-none d-md-flex flex-fill mx-4 justify-content-center pb-2">
                    <div class="position-relative" style="width: 100%; max-width: 520px;">
                        <input type="search" id="searchInput" placeholder="Buscar hamburguesas, combos..."
                            class="form-control ps-5"
                            style="border: 1px solid var(--beach-border); background-color: white;" />
                        <div class="position-absolute top-50 start-0 translate-middle-y ps-3">
                            <i class="fas fa-search text-beach-brown"></i>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mobile Menu -->
            @unless (request()->routeIs('catalog') || request()->routeIs('home'))
                <div id="mobileMenu" class="d-md-none border-top bg-white">
                    <div class="container py-3">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('catalog') }}" class="btn btn-outline-beach-primary">
                                        <i class="fas fa-utensils me-2"></i>Menú
                                    </a>
                                    @auth
                                        <a href="{{ route('cart') }}" class="btn btn-outline-beach-primary">
                                            <i class="fas fa-shopping-cart me-2"></i>Carrito
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-beach-accent text-white mt-5">
        <div class="py-5">
            <div class="container">
                <div class="row gy-4">
                    <!-- Brand / About -->
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-beach-primary rounded-3 d-flex align-items-center justify-content-center me-2"
                                style="width: 40px; height: 40px;">
                                <i class="fas fa-hamburger text-dark fs-5"></i>
                            </div>
                            <h5 class="mb-0">TeCocina</h5>
                        </div>
                        <p class="mb-3 small">Hamburguesas artesanales, ingredientes frescos y combos para todos los
                            gustos.</p>
                        <div class="small">
                            <div class="mb-1"><i
                                    class="fas fa-phone me-2"></i>{{ \App\Models\BusinessSetting::get('business_phone', '') }}
                            </div>
                            <div class="mb-1"><i
                                    class="fas fa-envelope me-2"></i>{{ \App\Models\BusinessSetting::get('business_email', '') }}
                            </div>
                            <div><i
                                    class="fas fa-map-marker-alt me-2"></i>{{ \App\Models\BusinessSetting::get('business_address', '') }}
                            </div>
                        </div>
                    </div>

                    <!-- Información legal -->
                    <div class="col-6 col-md-2">
                        <h6 class="text-uppercase fw-bold small mb-3">Información</h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2"><a href="#"
                                    class="text-white text-decoration-none">Privacidad</a></li>
                            <li class="mb-2"><a href="#" class="text-white text-decoration-none">Términos y
                                    Condiciones</a></li>
                            <li class="mb-2"><a href="#" class="text-white text-decoration-none">Envíos y
                                    Devoluciones</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Preguntas Frecuentes</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Atención al Cliente -->
                    <div class="col-6 col-md-3">
                        <h6 class="text-uppercase fw-bold small mb-3">Atención al Cliente</h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2"><a href="#" class="text-white text-decoration-none">Medios de
                                    Pago</a></li>
                            <li class="mb-2"><a href="#" class="text-white text-decoration-none">Horarios y
                                    Cobertura</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Contacto</a></li>
                        </ul>
                    </div>

                    <!-- Newsletter / Social -->
                    <div class="col-md-3">
                        <h6 class="text-uppercase fw-bold small mb-3">Novedades</h6>
                        <form class="d-flex gap-2 mb-3" onsubmit="event.preventDefault();">
                            <input type="email" class="form-control form-control-sm" placeholder="Tu email"
                                aria-label="email">
                            <button class="btn btn-beach-primary btn-sm" type="submit">Suscribirme</button>
                        </form>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-beach-dark text-white-50 py-3">
            <div
                class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small">
                <div>&copy; 2025 GrandesLigasAR. Todos los derechos reservados.</div>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white text-decoration-none">Privacidad</a>
                    <a href="#" class="text-white text-decoration-none">Términos</a>
                    <a href="#" class="text-white text-decoration-none">Envíos</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Offcanvas Cart (Global) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
        <div class="offcanvas-header bg-white border-bottom">
            <h5 class="offcanvas-title text-beach-dark">Tu Carrito</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <!-- Cart Items -->
            <div id="cartItems" class="flex-fill overflow-auto p-3">
                <!-- Cart items will be dynamically inserted here -->
            </div>

            <!-- Cart Footer -->
            <div id="cartFooter" class="border-top p-3 bg-white">
                <div class="d-grid gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-medium text-beach-dark">Total:</span>
                        <span id="cartTotal" class="h5 fw-bold text-beach-primary mb-0">$0</span>
                    </div>
                    <button id="proceedToCart" class="btn btn-beach-primary w-100">
                        Ver Carrito Completo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>

</html>
