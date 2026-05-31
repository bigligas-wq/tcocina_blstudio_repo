<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderItem extends Model
{
    protected $fillable = [
        'lab_order_id',
        'lab_improvement_id',
        'nombre_snapshot',
        'precio_usd_snapshot',
        'nota',
        'estado',
        'activado_at',
    ];

    protected $casts = [
        'precio_usd_snapshot' => 'decimal:2',
        'activado_at'         => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function improvement(): BelongsTo
    {
        return $this->belongsTo(LabImprovement::class, 'lab_improvement_id');
    }
}
