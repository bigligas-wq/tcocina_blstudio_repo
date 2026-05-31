<?php

namespace App\Services;

use App\Models\LoyaltyRedemption;
use App\Models\LoyaltySetting;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLoyaltyMovement;
use App\Models\UserNotification;

class NotificationService
{
    /**
     * Notificación: Canje solicitado
     */
    public static function notifyRedemptionRequested(User $user, LoyaltyRedemption $redemption): void
    {
        $rewardValue = $redemption->reward_snapshot['reward_value'] ?? 'tu premio';

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'loyalty',
            'title' => 'Solicitaste canjear tu álbum',
            'message' => "Tu solicitud para canjear {$rewardValue} fue enviada. Te avisaremos cuando esté aprobado.",
            'action_url' => route('loyalty.dashboard'),
            'action_text' => 'Ver mi álbum',
            'meta' => [
                'redemption_id' => $redemption->id,
                'reward_value' => $rewardValue,
            ],
        ]);
    }

    /**
     * Notificación: Canje aprobado
     */
    public static function notifyRedemptionApproved(User $user, LoyaltyRedemption $redemption): void
    {
        $rewardValue = $redemption->reward_snapshot['reward_value'] ?? 'tu premio';

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'success',
            'title' => '¡Tu canje fue aprobado! 🎉',
            'message' => "Tu premio {$rewardValue} está listo. Pasá a retirarlo por el local.",
            'action_url' => route('loyalty.dashboard'),
            'action_text' => 'Ver detalles',
            'meta' => [
                'redemption_id' => $redemption->id,
                'reward_value' => $rewardValue,
            ],
        ]);
    }

    /**
     * Notificación: Canje entregado
     */
    public static function notifyRedemptionDelivered(User $user, LoyaltyRedemption $redemption): void
    {
        $rewardValue = $redemption->reward_snapshot['reward_value'] ?? 'tu premio';

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'success',
            'title' => '¡Disfrutá tu premio! 🍔',
            'message' => "Tu canje de {$rewardValue} fue entregado. ¡Esperamos que lo disfrutes!",
            'action_url' => route('loyalty.dashboard'),
            'action_text' => 'Ver mi álbum',
            'meta' => [
                'redemption_id' => $redemption->id,
                'reward_value' => $rewardValue,
            ],
        ]);
    }

    /**
     * Notificación: Figuritas ganadas por pedido confirmado
     */
    public static function notifyStickersEarned(User $user, Order $order, int $stickersCount): void
    {
        if ($stickersCount <= 0) {
            return;
        }

        $wallet = $user->loyaltyWallet;
        $currentStickers = $wallet ? $wallet->current_stickers : 0;
        $setting = LoyaltySetting::active();
        $targetStickers = $setting->target_stickers;
        $remaining = max(0, $targetStickers - $currentStickers);

        $title = $stickersCount === 1
            ? '¡Ganaste 1 figurita! ☀️'
            : "¡Ganaste {$stickersCount} figuritas! ☀️";

        $message = $remaining > 0
            ? "Tu pedido #{$order->order_number} te dio {$stickersCount} figurita" . ($stickersCount > 1 ? 's' : '') . ". Te faltan {$remaining} para completar tu álbum."
            : "¡Completaste tu álbum! 🎉 Ya podés solicitar el canje de tu premio.";

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'loyalty',
            'title' => $title,
            'message' => $message,
            'action_url' => route('loyalty.dashboard'),
            'action_text' => 'Ver mi álbum',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'stickers_earned' => $stickersCount,
                'current_stickers' => $currentStickers,
                'target_stickers' => $targetStickers,
                'remaining' => $remaining,
            ],
        ]);
    }

    /**
     * Notificación: Estás cerca de completar el álbum
     */
    public static function notifyNearCompletion(User $user, int $remaining): void
    {
        if ($remaining <= 0 || $remaining > 3) {
            return; // Solo notificar cuando faltan 1, 2 o 3 figuritas
        }

        $setting = LoyaltySetting::active();
        $rewardValue = $setting->reward_value;

        $messages = [
            1 => "¡Solo te falta 1 figurita para canjear {$rewardValue}! Hacé un pedido y completá tu álbum.",
            2 => "¡Estás a 2 figuritas de canjear {$rewardValue}! Ya casi llegás.",
            3 => "¡Estás a 3 figuritas de completar tu álbum! El premio {$rewardValue} te espera.",
        ];

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'warning',
            'title' => "Estás a {$remaining} figurita" . ($remaining > 1 ? 's' : '') . " de completar el álbum",
            'message' => $messages[$remaining] ?? "¡Ya casi completás tu álbum! Hacé otro pedido para llegar a la meta.",
            'action_url' => route('catalog'),
            'action_text' => 'Hacer pedido',
            'meta' => [
                'remaining' => $remaining,
                'target_stickers' => $setting->target_stickers,
            ],
        ]);
    }

    /**
     * Notificación: Pedido en preparación
     */
    public static function notifyOrderPreparing(User $user, Order $order): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => '🔥 ¡Tu burger está en la plancha!',
            'message' => "Tu pedido #{$order->order_number} está siendo preparado. ¡Ya casi!",
            'action_url' => route('orders.tracking', $order->order_number),
            'action_text' => 'Ver seguimiento',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    /**
     * Notificación: Pedido en camino
     */
    public static function notifyOrderOnTheWay(User $user, Order $order): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => '🛵 ¡Tu pedido está en camino!',
            'message' => "Tu pedido #{$order->order_number} salió para tu domicilio. ¡Ya llega!",
            'action_url' => route('orders.tracking', $order->order_number),
            'action_text' => 'Ver seguimiento',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    /**
     * Notificación: Pedido entregado
     */
    public static function notifyOrderDelivered(User $user, Order $order): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'success',
            'title' => '🛵 ¡Tu pedido fue entregado!',
            'message' => "Tu pedido #{$order->order_number} fue entregado. ¡Que lo disfrutes!",
            'action_url' => route('orders.tracking', $order->order_number),
            'action_text' => 'Ver detalle',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    /**
     * Notificación: Pedido confirmado
     */
    public static function notifyOrderConfirmed(User $user, Order $order): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Pedido confirmado ✅',
            'message' => "Tu pedido #{$order->order_number} fue confirmado. Te avisaremos cuando esté listo.",
            'action_url' => route('orders.tracking', $order->order_number),
            'action_text' => 'Ver seguimiento',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
            ],
        ]);
    }

    /**
     * Notificación: Pedido listo para retirar/enviar
     */
    public static function notifyOrderReady(User $user, Order $order): void
    {
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'success',
            'title' => '¡Tu pedido está listo! 🍔',
            'message' => "Tu pedido #{$order->order_number} está listo. ¡Ya casi llega!",
            'action_url' => route('orders.tracking', $order->order_number),
            'action_text' => 'Ver seguimiento',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    /**
     * Contar notificaciones no leídas
     */
    public static function unreadCount(User $user): int
    {
        return UserNotification::forUser($user->id)->unread()->count();
    }
}
