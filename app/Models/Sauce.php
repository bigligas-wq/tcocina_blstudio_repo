<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sauce extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeSauces($query)
    {
        return $query->where('type', 'sauce');
    }

    public function scopeDips($query)
    {
        return $query->where('type', 'dip');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
