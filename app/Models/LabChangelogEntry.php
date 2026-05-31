<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabChangelogEntry extends Model
{
    protected $fillable = [
        'tipo',
        'titulo',
        'cuerpo',
        'icono',
        'color',
        'lab_improvement_id',
        'visible',
        'publicado_en',
    ];

    protected $casts = [
        'visible'      => 'boolean',
        'publicado_en' => 'datetime',
    ];

    public const TIPOS = [
        'nueva_mejora' => 'Nueva mejora',
        'actualizacion' => 'Actualización',
        'publicado'    => 'Mejora publicada',
        'nota'         => 'Nota',
        'promo'        => 'Promo',
    ];

    public function improvement(): BelongsTo
    {
        return $this->belongsTo(LabImprovement::class, 'lab_improvement_id');
    }

    public function scopeVisible($q)
    {
        return $q->where('visible', true);
    }
}
