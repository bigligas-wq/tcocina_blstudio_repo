<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabImprovement extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion_corta',
        'descripcion_larga',
        'categoria',
        'precio_usd',
        'precio_descuento_usd',
        'descuento_hasta',
        'icono',
        'es_destacada',
        'es_popular',
        'tiempo_estimado_horas',
        'roi_estimado',
        'estado',
        'imagen_antes',
        'imagen_despues',
        'diferencias',
    ];

    protected $casts = [
        'precio_usd'           => 'decimal:2',
        'precio_descuento_usd' => 'decimal:2',
        'descuento_hasta'      => 'datetime',
        'es_destacada'         => 'boolean',
        'es_popular'           => 'boolean',
        'diferencias'          => 'array',
    ];

    public const ESTADOS = ['borrador', 'publicada', 'archivada'];
    public const CATEGORIAS = ['visual', 'ux', 'performance', 'admin'];

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }

    public function scopePublicada($q)
    {
        return $q->where('estado', 'publicada');
    }

    public function scopeDestacada($q)
    {
        return $q->where('es_destacada', true);
    }

    public function getEsNuevaAttribute(): bool
    {
        return $this->created_at && $this->created_at->gt(now()->subDays(7));
    }

    public function getImagenAntesUrlAttribute(): ?string
    {
        return $this->imagen_antes ? asset('storage/' . $this->imagen_antes) : null;
    }

    public function getImagenDespuesUrlAttribute(): ?string
    {
        return $this->imagen_despues ? asset('storage/' . $this->imagen_despues) : null;
    }

    /**
     * Devuelve el precio efectivo considerando descuento si está vigente.
     */
    public function getPrecioEfectivoAttribute(): float
    {
        if ($this->tieneDescuentoActivo()) {
            return (float) $this->precio_descuento_usd;
        }
        return (float) $this->precio_usd;
    }

    public function tieneDescuentoActivo(): bool
    {
        if (!$this->precio_descuento_usd) return false;
        if (!$this->descuento_hasta) return true;
        return $this->descuento_hasta->isFuture();
    }

    public function getAhorroUsdAttribute(): float
    {
        if (!$this->tieneDescuentoActivo()) return 0;
        return (float) $this->precio_usd - (float) $this->precio_descuento_usd;
    }

    public function bundles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(LabBundle::class, 'lab_bundle_items')->withTimestamps();
    }

    public function wishlistedBy(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LabWishlistItem::class);
    }
}
