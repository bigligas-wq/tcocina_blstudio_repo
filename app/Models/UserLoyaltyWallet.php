<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoyaltyWallet extends Model
{
    protected $fillable = [
        'user_id',
        'current_stickers',
        'total_earned',
        'total_redeemed',
    ];

    protected function casts(): array
    {
        return [
            'current_stickers' => 'integer',
            'total_earned' => 'integer',
            'total_redeemed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
