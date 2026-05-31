<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'postal_code',
        'reference',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return "{$this->street} {$this->number}, {$this->neighborhood}, {$this->city}, {$this->state} {$this->postal_code}";
    }
}
