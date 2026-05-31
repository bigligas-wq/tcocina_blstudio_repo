@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Configuración del negocio</h2>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="business_name" class="form-label">Nombre del negocio</label>
                            <input type="text" class="form-control" id="business_name" name="business_name"
                                value="{{ old('business_name', optional($settings['business_name'] ?? null)->value) }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label for="business_phone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="business_phone" name="business_phone"
                                value="{{ old('business_phone', optional($settings['business_phone'] ?? null)->value) }}"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="business_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="business_email" name="business_email"
                                value="{{ old('business_email', optional($settings['business_email'] ?? null)->value) }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label for="business_address" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="business_address" name="business_address"
                                value="{{ old('business_address', optional($settings['business_address'] ?? null)->value) }}"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label for="delivery_fee" class="form-label">Costo de envío</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="delivery_fee"
                                name="delivery_fee"
                                value="{{ old('delivery_fee', optional($settings['delivery_fee'] ?? null)->value) }}"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label for="minimum_order_amount" class="form-label">Pedido mínimo</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                id="minimum_order_amount" name="minimum_order_amount"
                                value="{{ old('minimum_order_amount', optional($settings['minimum_order_amount'] ?? null)->value) }}"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label for="estimated_delivery_time" class="form-label">Tiempo estimado de entrega (min)</label>
                            <input type="number" step="1" min="1" class="form-control"
                                id="estimated_delivery_time" name="estimated_delivery_time"
                                value="{{ old('estimated_delivery_time', optional($settings['estimated_delivery_time'] ?? null)->value) }}"
                                required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
