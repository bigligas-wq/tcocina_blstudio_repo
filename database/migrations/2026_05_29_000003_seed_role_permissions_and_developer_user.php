<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

return new class extends Migration {
    public function up(): void
    {
        $now = Carbon::now();
        $config = config('permissions');
        $groups = $config['groups'] ?? [];
        $defaults = $config['defaults'] ?? [];

        $allKeys = [];
        foreach ($groups as $group) {
            foreach (array_keys($group['permissions'] ?? []) as $key) {
                $allKeys[] = $key;
            }
        }

        $rows = [];
        foreach ($defaults as $role => $allowedSet) {
            $allowedKeys = $allowedSet === '*' ? $allKeys : (array) $allowedSet;
            $allowedLookup = array_flip($allowedKeys);

            foreach ($allKeys as $key) {
                $rows[] = [
                    'role'           => $role,
                    'permission_key' => $key,
                    'allowed'        => isset($allowedLookup[$key]),
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('role_permissions')->upsert(
                $rows,
                ['role', 'permission_key'],
                ['allowed', 'updated_at']
            );
        }

        // Crear/actualizar el usuario developer (BLStudio)
        $existing = DB::table('users')->where('email', 'blstudio@tcocina.org')->first();
        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update([
                'role'       => 'developer',
                'is_active'  => true,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('users')->insert([
                'name'       => 'BLStudio',
                'email'      => 'blstudio@tcocina.org',
                'password'   => Hash::make('Admin2026'),
                'role'       => 'developer',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')->truncate();
        // No borramos el user developer en down — el dueño puede decidir
    }
};
