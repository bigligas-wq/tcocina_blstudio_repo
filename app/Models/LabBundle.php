<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LabBundle extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion_corta',
        'icono',
        'precio_bundle_usd',
        'estado',
    ];

    protected $casts = [
        'precio_bundle_usd' => 'decimal:2',
    ];

    public const ESTADOS = ['borrador', 'publicado', 'archivado'];

    public function improvements(): BelongsToMany
    {
        return $this->belongsToMany(LabImprovement::class, 'lab_bundle_items')
            ->withTimestamps();
    }

    public function scopePublicado($q)
    {
        return $q->where('estado', 'publicado');
    }

    public function getPrecioOriginalAttribute(): float
    {
        return (float) $this->improvements->sum('precio_usd');
    }

    public function getAhorroUsdAttribute(): float
    {
        return max(0, $this->precio_original - (float) $this->precio_bundle_usd);
    }

    public function getAhorroPorcentajeAttribute(): int
    {
        $orig = $this->precio_original;
        if ($orig <= 0) return 0;
        return (int) round(($this->ahorro_usd / $orig) * 100);
    }
}
