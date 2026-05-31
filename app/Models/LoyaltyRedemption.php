<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'stickers_spent',
        'reward_snapshot',
        'status',
        'approved_by',
        'approved_at',
        'delivered_at',
        'client_seen_approved_at',
        'client_seen_delivered_at',
        'cancelled_at',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'stickers_spent' => 'integer',
            'reward_snapshot' => 'array',
            'approved_at' => 'datetime',
            'delivered_at' => 'datetime',
            'client_seen_approved_at' => 'datetime',
            'client_seen_delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
