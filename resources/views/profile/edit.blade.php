@extends('layouts.app')

@section('title', 'Mis datos - Tcocina')

@section('content')
    <main class="container py-4">
        <style>
            .address-card {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 12px;
                margin-bottom: 10px;
                background: #fff;
            }
            .address-line {
                font-size: .9rem;
                color: #6b7280;
                margin: 2px 0 0;
            }
            .address-edit-panel {
                display: none;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px dashed #d1d5db;
            }
            .address-edit-panel.show {
                display: block;
            }
        </style>
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-3">Mis datos</h2>
                        <p class="text-muted mb-3">Tus datos para completar pedidos más rápido.</p>

                        @php
                            $profileHasErrors = $errors->has('first_name') || $errors->has('last_name') || $errors->has('phone');
                        @endphp

                        <div class="address-card mb-0">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <strong>{{ trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: ($user->name ?? 'Sin nombre') }}</strong>
                                    <p class="address-line">{{ $user->phone ?: 'Sin teléfono cargado' }}</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-light border address-edit-toggle"
                                    data-target="profile-edit-panel" aria-label="Editar datos">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </div>

                            <div id="profile-edit-panel" class="address-edit-panel {{ $profileHasErrors ? 'show' : '' }}">
                                <form action="{{ route('profile.update') }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-6">
                                        <label class="form-label form-label-sm mb-1">Nombre</label>
                                        <input type="text" class="form-control form-control-sm" name="first_name"
                                            value="{{ old('first_name', $firstName) }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label form-label-sm mb-1">Apellido</label>
                                        <input type="text" class="form-control form-control-sm" name="last_name"
                                            value="{{ old('last_name', $lastName) }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label form-label-sm mb-1">Teléfono</label>
                                        <input type="text" class="form-control form-control-sm" name="phone"
                                            value="{{ old('phone', $user->phone) }}" required>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-sm btn-primary" type="submit">Guardar datos</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        @php
                            $addressHasErrors = $errors->has('name') || $errors->has('street') || $errors->has('number') ||
                                $errors->has('reference') || $errors->has('city') || $errors->has('state') ||
                                $errors->has('postal_code') || $errors->has('is_default');
                        @endphp
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="h5 mb-0">Direcciones guardadas</h3>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus me-1"></i>Agregar dirección
                            </button>
                        </div>

                        @forelse($addresses as $address)
                            <div class="address-card">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong>{{ $address->name }}</strong>
                                            @if ($address->is_default)
                                                <span class="badge text-bg-success">Predeterminada</span>
                                            @endif
                                        </div>
                                        <p class="address-line">
                                            {{ $address->street }} {{ $address->number }}
                                            @if($address->reference)
                                                · {{ $address->reference }}
                                            @endif
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border address-edit-toggle"
                                        data-target="address-edit-{{ $address->id }}" aria-label="Editar dirección">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>

                                <div id="address-edit-{{ $address->id }}" class="address-edit-panel">
                                    <form action="{{ route('profile.addresses.update', $address) }}" method="POST" class="row g-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-12 col-md-4">
                                            <label class="form-label form-label-sm mb-1">Nombre</label>
                                            <input type="text" class="form-control form-control-sm" name="name" value="{{ $address->name }}" required>
                                        </div>
                                        <div class="col-8 col-md-5">
                                            <label class="form-label form-label-sm mb-1">Calle</label>
                                            <input type="text" class="form-control form-control-sm" name="street" value="{{ $address->street }}" required>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <label class="form-label form-label-sm mb-1">Número</label>
                                            <input type="text" class="form-control form-control-sm" name="number" value="{{ $address->number }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label form-label-sm mb-1">Referencia</label>
                                            <input type="text" class="form-control form-control-sm" name="reference" value="{{ $address->reference }}" placeholder="Portón, timbre, etc.">
                                        </div>
                                        <input type="hidden" name="city" value="{{ $address->city ?: 'Olavarría' }}">
                                        <input type="hidden" name="state" value="{{ $address->state ?: 'Buenos Aires' }}">
                                        <input type="hidden" name="neighborhood" value="{{ $address->neighborhood }}">
                                        <input type="hidden" name="postal_code" value="{{ $address->postal_code }}">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @if (!$address->is_default)
                                            <form action="{{ route('profile.addresses.default', $address) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">Predeterminada</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('profile.addresses.destroy', $address) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('¿Eliminar esta dirección?')">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Todavía no tenés direcciones guardadas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddressModalLabel">Agregar dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="addAddressForm" action="{{ route('profile.addresses.store') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Nombre de dirección</label>
                            <input type="text" class="form-control" name="name" placeholder="Casa 1 / Trabajo"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="col-8">
                            <label class="form-label">Calle</label>
                            <input type="text" class="form-control" name="street" value="{{ old('street') }}" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-control" name="number" value="{{ old('number') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control" name="reference" value="{{ old('reference') }}"
                                placeholder="Portón negro, timbre 2">
                        </div>
                        <input type="hidden" name="city" value="Olavarría">
                        <input type="hidden" name="state" value="Buenos Aires">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                    id="newAddressDefault" {{ old('is_default') ? 'checked' : '' }}>
                                <label class="form-check-label" for="newAddressDefault">
                                    Marcar como predeterminada
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="addAddressForm" class="btn btn-primary">Guardar dirección</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.address-edit-toggle').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const panel = document.getElementById(targetId);
                    if (!panel) return;
                    panel.classList.toggle('show');
                });
            });

            @if ($addressHasErrors)
                if (window.bootstrap && bootstrap.Modal) {
                    const addAddressModalEl = document.getElementById('addAddressModal');
                    if (addAddressModalEl) {
                        const addAddressModal = new bootstrap.Modal(addAddressModalEl);
                        addAddressModal.show();
                    }
                }
            @endif
        });
    </script>
@endsection
