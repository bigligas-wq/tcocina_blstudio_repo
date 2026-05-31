<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class LabCreditWallet extends Model
{
    protected $fillable = ['user_id', 'balance_usd'];

    protected $casts = [
        'balance_usd' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(LabCreditMovement::class);
    }

    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(['user_id' => $userId], ['balance_usd' => 0]);
    }

    public function credit(float $amount, string $description, ?int $grantedBy = null): LabCreditMovement
    {
        return DB::transaction(function () use ($amount, $description, $grantedBy) {
            $this->increment('balance_usd', $amount);
            return $this->movements()->create([
                'tipo'        => 'credito',
                'monto_usd'   => $amount,
                'descripcion' => $description,
                'granted_by_user_id' => $grantedBy,
            ]);
        });
    }

    public function debit(float $amount, string $description, ?int $labOrderId = null): LabCreditMovement
    {
        return DB::transaction(function () use ($amount, $description, $labOrderId) {
            if ($this->balance_usd < $amount) {
                throw new \RuntimeException('Saldo insuficiente.');
            }
            $this->decrement('balance_usd', $amount);
            return $this->movements()->create([
                'tipo'        => 'debito',
                'monto_usd'   => $amount,
                'descripcion' => $description,
                'lab_order_id' => $labOrderId,
            ]);
        });
    }
}
