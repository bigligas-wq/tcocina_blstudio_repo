<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'discount_percentage',
        'value',
        'type',
        'code_length',
        'usage_limit',
        'used_count',
        'usage_count',
        'is_active',
        'allow_cash_discount',
        'valid_from',
        'valid_until',
        'description',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'code_length' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'usage_count' => 'integer',
        'is_active' => 'boolean',
        'allow_cash_discount' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)
            ->withPivot('discount_applied')
            ->withTimestamps();
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $percentage = $this->discount_percentage ?? $this->value ?? 0;
        return round($subtotal * ($percentage / 100), 2);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
        try {
            if (isset($this->attributes['usage_count']) || $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'usage_count')) {
                $this->increment('usage_count');
            }
        } catch (\Exception $e) {
        }
    }

    public function getUsedCountAttribute($value)
    {
        if (($value === null || $value === 0) && isset($this->attributes['usage_count'])) {
            return $this->attributes['usage_count'] ?? 0;
        }

        return $value ?? 0;
    }
}
