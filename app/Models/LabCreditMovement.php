<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabCreditMovement extends Model
{
    protected $fillable = [
        'lab_credit_wallet_id',
        'tipo',
        'monto_usd',
        'descripcion',
        'lab_order_id',
        'granted_by_user_id',
    ];

    protected $casts = [
        'monto_usd' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(LabCreditWallet::class, 'lab_credit_wallet_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}
