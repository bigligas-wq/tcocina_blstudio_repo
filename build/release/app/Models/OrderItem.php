<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'selected_variants',
        'selected_options',
        'special_instructions',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'selected_variants' => 'array',
            'selected_options' => 'array',
        ];
    }

    // Relaciones
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getProductNameWithVariantsAttribute(): string
    {
        $name = $this->product->name;

        if ($this->customizations && isset($this->customizations['variants'])) {
            $variants = collect($this->customizations['variants'])->pluck('value')->join(', ');
            $name .= " ({$variants})";
        }

        return $name;
    }

    public function getSelectedOptionsTextAttribute(): string
    {
        if (!$this->customizations || !isset($this->customizations['options'])) {
            return '';
        }

        return collect($this->customizations['options'])->pluck('value')->join(', ');
    }
}
