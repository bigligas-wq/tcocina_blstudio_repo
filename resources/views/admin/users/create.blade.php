@extends('layouts.admin')

@section('title', 'Nuevo usuario - Admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bx bx-arrow-back"></i>
        </a>
        <h3 class="mb-0">Nuevo usuario</h3>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <small class="text-muted">Mínimo 6 caracteres.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select" required>
                            <option value="">— Elegí un rol —</option>
                            @foreach ($roleLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Crear usuario</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
