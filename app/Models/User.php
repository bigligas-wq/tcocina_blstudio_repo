<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'phone',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var list<string>
     */
    protected $guarded = ['role'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function loyaltyWallet(): HasOne
    {
        return $this->hasOne(UserLoyaltyWallet::class);
    }

    public function loyaltyMovements(): HasMany
    {
        return $this->hasMany(UserLoyaltyMovement::class);
    }

    public function loyaltyRedemptions(): HasMany
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    // Scopes
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cambiar el rol del usuario de forma segura.
     * Este método debe ser usado solo por el administrador.
     *
     * @param string $newRole
     * @return bool
     */
    public function changeRole(string $newRole): bool
    {
        $allowed = array_keys(config('permissions.roles', [
            'customer' => '', 'admin' => '', 'kitchen' => '',
        ]));

        if (!in_array($newRole, $allowed, true)) {
            throw new \InvalidArgumentException('Rol inválido: ' . $newRole);
        }

        $this->role = $newRole;
        return $this->save();
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCajero(): bool
    {
        return $this->role === 'cajero';
    }

    public function isKitchen(): bool
    {
        return $this->role === 'kitchen';
    }

    /**
     * Developer y admin tienen todo. El resto consulta role_permissions.
     */
    public function hasPermission(string $key): bool
    {
        if (in_array($this->role, ['developer', 'admin'], true)) {
            return true;
        }

        return RolePermission::isAllowed($this->role, $key);
    }
}
