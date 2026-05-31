<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductConfiguration extends Model
{
    protected $fillable = [
        'name',
        'value',
        'price_modifier',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getFormattedPriceModifierAttribute(): string
    {
        if ($this->price_modifier == 0) {
            return '';
        }

        $sign = $this->price_modifier >= 0 ? '+' : '';
        return " ({$sign}\$" . number_format($this->price_modifier, 2) . ')';
    }

    public function getDisplayValueAttribute(): string
    {
        return $this->value . $this->formatted_price_modifier;
    }
}
