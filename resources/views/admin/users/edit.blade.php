@extends('layouts.admin')

@section('title', 'Editar usuario - Admin')

@section('content')
@php
    $isSelf = auth()->id() === $user->id;
    $isEditableRole = in_array($user->role, $editableRoles, true);
@endphp

<div class="container-fluid py-3">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bx bx-arrow-back"></i>
        </a>
        <h3 class="mb-0">Editar usuario</h3>
        <span class="ms-3 text-muted">{{ $user->email }}</span>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-header"><strong>Datos básicos</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select" required {{ $isSelf ? 'disabled' : '' }}>
                            @foreach ($roleLabels as $key => $label)
                                @if ($key === 'developer' && auth()->user()->role !== 'developer')
                                    @continue
                                @endif
                                <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isSelf)
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <small class="text-muted">No podés cambiarte tu propio rol.</small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Dejá vacío para no cambiar">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Usuario activo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($isEditableRole)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Permisos del rol</strong>
                        <span class="badge bg-info text-dark ms-2">{{ $roleLabels[$user->role] ?? $user->role }}</span>
                    </div>
                    <small class="text-muted">Los cambios aplican a todos los usuarios con este rol.</small>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach ($groups as $groupKey => $group)
                            <div class="col-md-6 col-lg-4">
                                <h6 class="text-uppercase text-muted small mb-2">{{ $group['label'] }}</h6>
                                @foreach ($group['permissions'] as $permKey => $permLabel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permKey }}"
                                               id="perm_{{ $permKey }}"
                                               {{ ($rolePermissions[$permKey] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $permKey }}">
                                            {{ $permLabel }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center text-muted">
                        <i class="bx bx-info-circle me-2 fs-4"></i>
                        <div>
                            El rol <strong>{{ $roleLabels[$user->role] ?? $user->role }}</strong> usa permisos predefinidos no editables.
                            Solo los roles configurables (ej. cajero) permiten editar permisos individuales.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
