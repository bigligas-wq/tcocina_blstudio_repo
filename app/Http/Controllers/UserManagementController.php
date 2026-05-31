<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $roleLabels = config('permissions.roles', []);
        $users = User::orderByRaw("CASE role
                WHEN 'developer' THEN 1
                WHEN 'admin' THEN 2
                WHEN 'cajero' THEN 3
                WHEN 'kitchen' THEN 4
                WHEN 'delivery' THEN 5
                ELSE 6 END")
            ->orderBy('name')
            ->paginate(30);

        return view('admin.users.index', compact('users', 'roleLabels'));
    }

    public function create()
    {
        $roleLabels = config('permissions.roles', []);
        // No se permite crear otro developer desde la UI (única cuenta BLStudio)
        unset($roleLabels['developer'], $roleLabels['customer']);

        return view('admin.users.create', compact('roleLabels'));
    }

    public function store(Request $request)
    {
        $assignableRoles = array_keys(config('permissions.roles', []));
        $assignableRoles = array_diff($assignableRoles, ['developer', 'customer']);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', Rule::in($assignableRoles)],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $this->guardDeveloperEdit($user);

        $roleLabels = config('permissions.roles', []);
        $editableRoles = config('permissions.editable_roles', []);
        $groups = config('permissions.groups', []);
        $rolePermissions = RolePermission::forRole($user->role);

        return view('admin.users.edit', compact(
            'user', 'roleLabels', 'editableRoles', 'groups', 'rolePermissions'
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->guardDeveloperEdit($user);

        $assignableRoles = array_keys(config('permissions.roles', []));
        // El admin actual no puede degradarse a sí mismo
        $isSelf = auth()->id() === $user->id;
        $assignableRoles = array_diff($assignableRoles, ['developer']);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in($assignableRoles)],
            'is_active' => ['nullable', 'boolean'],
            'password'  => ['nullable', 'string', 'min:6'],
            'permissions' => ['array'],
        ]);

        if ($isSelf && $data['role'] !== $user->role) {
            return back()->withInput()->with('error', 'No podés cambiarte tu propio rol.');
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = (bool) ($data['is_active'] ?? false);
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Permisos: solo para roles editables (ej. cajero)
        $editableRoles = config('permissions.editable_roles', []);
        if (in_array($user->role, $editableRoles, true)) {
            $this->syncRolePermissions($user->role, $request->input('permissions', []));
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $this->guardDeveloperEdit($user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'No podés eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado.');
    }

    private function guardDeveloperEdit(User $user): void
    {
        // El developer solo puede ser editado por otro developer
        if ($user->role === 'developer' && auth()->user()?->role !== 'developer') {
            abort(403, 'Solo el developer puede modificar al developer.');
        }
    }

    private function syncRolePermissions(string $role, array $selected): void
    {
        $groups = config('permissions.groups', []);
        $allKeys = [];
        foreach ($groups as $group) {
            foreach (array_keys($group['permissions'] ?? []) as $k) {
                $allKeys[] = $k;
            }
        }

        $selectedLookup = array_flip($selected);
        foreach ($allKeys as $key) {
            RolePermission::allow($role, $key, isset($selectedLookup[$key]));
        }
    }
}
