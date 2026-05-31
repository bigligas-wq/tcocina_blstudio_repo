<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'permission_key',
        'allowed',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];

    public static function allow(string $role, string $key, bool $allowed = true): void
    {
        static::updateOrCreate(
            ['role' => $role, 'permission_key' => $key],
            ['allowed' => $allowed]
        );
    }

    public static function isAllowed(string $role, string $key): bool
    {
        return (bool) static::where('role', $role)
            ->where('permission_key', $key)
            ->where('allowed', true)
            ->exists();
    }

    public static function forRole(string $role): array
    {
        return static::where('role', $role)
            ->pluck('allowed', 'permission_key')
            ->map(fn ($v) => (bool) $v)
            ->all();
    }
}
