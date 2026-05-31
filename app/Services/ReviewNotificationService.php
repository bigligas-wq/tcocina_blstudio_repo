<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class ReviewNotificationService
{
    /**
     * Notify user about pending review.
     */
    public function notifyPendingReview(User $user, Order $order): void
    {
        // This will use the existing notification system
        // For now, we'll use a simple approach - can be enhanced later
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'review_request',
            'title' => '¡Tu pedido ha sido entregado!',
            'message' => "Deja tu reseña sobre los productos de tu pedido #{$order->order_number}",
            'data' => json_encode([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ]);

        // Dispatch notification event
        event(new \App\Events\NotificationCreated($notification));
    }

    /**
     * Notify user that review was approved.
     */
    public function notifyReviewApproved(User $user, ProductReview $review): void
    {
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'review_approved',
            'title' => 'Tu reseña ha sido aprobada',
            'message' => 'Tu reseña ya es visible para otros clientes',
            'data' => json_encode([
                'review_id' => $review->id,
                'product_id' => $review->product_id,
            ]),
        ]);

        event(new \App\Events\NotificationCreated($notification));
    }

    /**
     * Notify user that review was rejected.
     */
    public function notifyReviewRejected(User $user, ProductReview $review): void
    {
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'review_rejected',
            'title' => 'Tu reseña no fue aprobada',
            'message' => 'Tu reseña no cumple con nuestras políticas',
            'data' => json_encode([
                'review_id' => $review->id,
                'product_id' => $review->product_id,
            ]),
        ]);

        event(new \App\Events\NotificationCreated($notification));
    }
}
