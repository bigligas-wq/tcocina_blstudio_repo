@extends('layouts.admin')

@section('title', 'Usuarios - Admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Usuarios</h3>
            <small class="text-muted">Gestioná los usuarios del sistema y sus permisos.</small>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Nuevo usuario
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->name }}</strong>
                                @if ($u->id === auth()->id())
                                    <span class="badge bg-info ms-1">vos</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{ $u->email }}</span></td>
                            <td>
                                @php
                                    $roleClass = match($u->role) {
                                        'developer' => 'bg-dark text-warning border border-warning',
                                        'admin'     => 'bg-primary',
                                        'cajero'    => 'bg-info text-dark',
                                        'kitchen'   => 'bg-warning text-dark',
                                        'delivery'  => 'bg-secondary',
                                        default     => 'bg-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $roleClass }}">
                                    {{ $roleLabels[$u->role] ?? $u->role }}
                                </span>
                            </td>
                            <td>
                                @if ($u->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @php
                                    $isProtected = $u->role === 'developer' && auth()->user()?->role !== 'developer';
                                @endphp
                                <a href="{{ route('admin.users.edit', $u->id) }}"
                                   class="btn btn-sm btn-outline-secondary {{ $isProtected ? 'disabled' : '' }}"
                                   @if($isProtected) tabindex="-1" aria-disabled="true" @endif>
                                    <i class="bx bx-edit"></i>
                                </a>
                                @if (!$isProtected && $u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay usuarios todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
