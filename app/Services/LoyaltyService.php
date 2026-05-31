<?php

namespace App\Services;

use App\Models\LoyaltyRedemption;
use App\Models\LoyaltySetting;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLoyaltyMovement;
use App\Models\UserLoyaltyWallet;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function awardFromConfirmedOrder(Order $order, ?int $createdBy = null, bool $skipStatusCheck = false): void
    {
        if (!$order->user_id) {
            return;
        }

        if (!$skipStatusCheck && $order->status !== Order::STATUS_CONFIRMED) {
            return;
        }

        // Solo las hamburguesas (category_id = 1) suman figuritas al álbum.
        // Bebidas, postres, acompañamientos y combos no cuentan.
        $stickersToAward = max(0, (int) $order->items()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.category_id', 1)
            ->sum('order_items.quantity'));

        if ($stickersToAward <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $createdBy, $stickersToAward) {
            $exists = UserLoyaltyMovement::where('user_id', $order->user_id)
                ->where('order_id', $order->id)
                ->where('reason', 'order_confirmed')
                ->exists();

            if ($exists) {
                return;
            }

            $wallet = UserLoyaltyWallet::firstOrCreate(
                ['user_id' => $order->user_id],
                ['current_stickers' => 0, 'total_earned' => 0, 'total_redeemed' => 0]
            );

            $wallet->increment('current_stickers', $stickersToAward);
            $wallet->increment('total_earned', $stickersToAward);

            // Registrar también cuántas hamburguesas había en el pedido
            $totalItemsInOrder = (int) $order->items()->sum('quantity');

            UserLoyaltyMovement::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'created_by' => $createdBy,
                'delta' => $stickersToAward,
                'reason' => 'order_confirmed',
                'meta' => [
                    'order_number'    => $order->order_number,
                    'items_count'     => $totalItemsInOrder,
                    'burgers_count'   => $stickersToAward,
                ],
            ]);

            // Enviar notificación al usuario
            $user = User::find($order->user_id);
            if ($user) {
                $setting = LoyaltySetting::active();
                NotificationService::notifyStickersEarned($user, $order, $stickersToAward);

                // Notificar si está cerca de completar el álbum
                $wallet->refresh();
                if ($setting) {
                    $remaining = max(0, $setting->target_stickers - $wallet->current_stickers);
                    if ($remaining > 0 && $remaining <= 3) {
                        NotificationService::notifyNearCompletion($user, $remaining);
                    }
                }
            }
        });
    }

    public function canRedeem(User $user): bool
    {
        $wallet = UserLoyaltyWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['current_stickers' => 0, 'total_earned' => 0, 'total_redeemed' => 0]
        );

        $setting = LoyaltySetting::active();
        return $wallet->current_stickers >= $setting->target_stickers;
    }

    public function requestRedemption(User $user): LoyaltyRedemption
    {
        return DB::transaction(function () use ($user) {
            $setting = LoyaltySetting::active();
            $wallet = UserLoyaltyWallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet) {
                throw new \RuntimeException('Aun no acumulaste soles.');
            }

            if ($wallet->current_stickers < $setting->target_stickers) {
                throw new \RuntimeException('Todavia no alcanzaste el objetivo para canjear.');
            }

            $pending = LoyaltyRedemption::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($pending) {
                throw new \RuntimeException('Ya tenes un canje en proceso.');
            }

            $wallet->decrement('current_stickers', $setting->target_stickers);
            $wallet->increment('total_redeemed', $setting->target_stickers);

            $redemption = LoyaltyRedemption::create([
                'user_id' => $user->id,
                'stickers_spent' => $setting->target_stickers,
                'reward_snapshot' => [
                    'reward_type' => $setting->reward_type,
                    'reward_value' => $setting->reward_value,
                    'redemption_instructions' => $setting->redemption_instructions,
                    'coupon_code' => $setting->coupon_code,
                    'reward_category' => $setting->reward_category,
                ],
                'status' => 'pending',
            ]);

            UserLoyaltyMovement::create([
                'user_id' => $user->id,
                'order_id' => null,
                'created_by' => $user->id,
                'delta' => -$setting->target_stickers,
                'reason' => 'redemption_requested',
                'meta' => [
                    'redemption_id' => $redemption->id,
                    'reward' => $redemption->reward_snapshot,
                ],
            ]);

            // Notificar al usuario
            NotificationService::notifyRedemptionRequested($user, $redemption);

            return $redemption;
        });
    }

    /**
     * Devuelve impacto del pedido sobre el sistema de figuritas.
     *
     * @return array{has_stickers:bool,stickers_amount:int,wallet_current:int,has_pending_redemption:bool,redemption_id:?int,redemption_status:?string}
     */
    public function getOrderLoyaltyImpact(Order $order): array
    {
        $movement = UserLoyaltyMovement::where('order_id', $order->id)
            ->where('reason', 'order_confirmed')
            ->first();

        $stickersAmount = $movement ? (int) $movement->delta : 0;
        $hasStickers = $stickersAmount > 0;

        $walletCurrent = 0;
        $pendingRedemption = null;

        if ($order->user_id) {
            $wallet = UserLoyaltyWallet::where('user_id', $order->user_id)->first();
            $walletCurrent = $wallet ? (int) $wallet->current_stickers : 0;

            $pendingRedemption = LoyaltyRedemption::where('user_id', $order->user_id)
                ->whereIn('status', ['pending', 'approved'])
                ->latest('id')
                ->first();
        }

        return [
            'has_stickers'           => $hasStickers,
            'stickers_amount'        => $stickersAmount,
            'wallet_current'         => $walletCurrent,
            'has_pending_redemption' => (bool) $pendingRedemption,
            'redemption_id'          => $pendingRedemption?->id,
            'redemption_status'      => $pendingRedemption?->status,
        ];
    }

    /**
     * Revierte las figuritas otorgadas por un pedido.
     * Estrategias:
     *  - 'revert'                          → resta figuritas (permite negativo)
     *  - 'revert_and_cancel_redemption'    → cancela canje pendiente y devuelve esas figuritas también
     *  - 'keep'                            → no toca figuritas, solo deja log
     */
    public function revokeFromOrder(Order $order, ?int $createdBy = null, string $strategy = 'revert'): void
    {
        if (!$order->user_id) {
            return;
        }

        DB::transaction(function () use ($order, $createdBy, $strategy) {
            $movement = UserLoyaltyMovement::where('order_id', $order->id)
                ->where('reason', 'order_confirmed')
                ->first();

            if (!$movement) {
                return;
            }

            // Idempotencia: si ya fue revocado, no duplicar.
            $alreadyRevoked = UserLoyaltyMovement::where('order_id', $order->id)
                ->whereIn('reason', ['order_cancelled', 'order_cancelled_no_revoke'])
                ->exists();

            if ($alreadyRevoked) {
                return;
            }

            $amount = (int) $movement->delta;
            if ($amount <= 0) {
                return;
            }

            if ($strategy === 'keep') {
                UserLoyaltyMovement::create([
                    'user_id'    => $order->user_id,
                    'order_id'   => $order->id,
                    'created_by' => $createdBy,
                    'delta'      => 0,
                    'reason'     => 'order_cancelled_no_revoke',
                    'meta'       => [
                        'order_number'        => $order->order_number,
                        'original_stickers'   => $amount,
                        'admin_decision'      => 'keep',
                    ],
                ]);
                return;
            }

            $wallet = UserLoyaltyWallet::where('user_id', $order->user_id)->lockForUpdate()->first();
            if (!$wallet) {
                return;
            }

            if ($strategy === 'revert_and_cancel_redemption') {
                $pendingRedemption = LoyaltyRedemption::where('user_id', $order->user_id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if ($pendingRedemption) {
                    $this->cancelRedemptionInternal($pendingRedemption, $wallet, $createdBy, 'Pedido relacionado cancelado');
                }
            }

            // Restar figuritas (puede quedar negativo)
            $wallet->current_stickers = $wallet->current_stickers - $amount;
            $wallet->total_earned = max(0, $wallet->total_earned - $amount);
            $wallet->save();

            UserLoyaltyMovement::create([
                'user_id'    => $order->user_id,
                'order_id'   => $order->id,
                'created_by' => $createdBy,
                'delta'      => -$amount,
                'reason'     => 'order_cancelled',
                'meta'       => [
                    'order_number'   => $order->order_number,
                    'admin_decision' => $strategy,
                ],
            ]);
        });
    }

    /**
     * Cancela un canje pendiente o aprobado, devolviendo las figuritas al wallet.
     */
    public function cancelRedemption(LoyaltyRedemption $redemption, ?int $createdBy = null, string $reason = ''): void
    {
        DB::transaction(function () use ($redemption, $createdBy, $reason) {
            if (!in_array($redemption->status, ['pending', 'approved'], true)) {
                return;
            }
            $wallet = UserLoyaltyWallet::where('user_id', $redemption->user_id)->lockForUpdate()->first();
            if (!$wallet) {
                return;
            }
            $this->cancelRedemptionInternal($redemption, $wallet, $createdBy, $reason);
        });
    }

    private function cancelRedemptionInternal(LoyaltyRedemption $redemption, UserLoyaltyWallet $wallet, ?int $createdBy, string $reason): void
    {
        $stickers = (int) $redemption->stickers_spent;

        $wallet->increment('current_stickers', $stickers);
        $wallet->total_redeemed = max(0, $wallet->total_redeemed - $stickers);
        $wallet->save();

        $redemption->update([
            'status'           => 'cancelled',
            'cancelled_at'     => now(),
            'cancelled_reason' => $reason ?: null,
        ]);

        UserLoyaltyMovement::create([
            'user_id'    => $redemption->user_id,
            'order_id'   => null,
            'created_by' => $createdBy,
            'delta'      => $stickers,
            'reason'     => 'redemption_cancelled_by_admin',
            'meta'       => [
                'redemption_id' => $redemption->id,
                'reason'        => $reason,
            ],
        ]);
    }
}
