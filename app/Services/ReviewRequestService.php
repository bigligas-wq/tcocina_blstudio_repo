<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewRequestMail;
use App\Mail\ReviewReminderMail;

class ReviewRequestService
{
    /**
     * Send review request email after order is delivered.
     * Anti-spam: nunca enviar 2 veces por el mismo pedido,
     * ni molestar al mismo teléfono/usuario más de 1 vez cada 7 días.
     */
    public function sendReviewRequest(Order $order): void
    {
        // 0. Nunca enviar si el usuario ya confirmó que dejó reseña en Google
        if ($order->user && $order->user->google_review_completed_at) {
            return;
        }

        // 1. Anti-spam per-pedido
        if ($order->review_prompt_sent_at) {
            return;
        }

        $phone = $order->contact_phone;
        $promptedRecently = false;

        // 2. Anti-spam per-usuario logueado (cooldown 7 días)
        if ($order->user && $order->user->review_prompted_at) {
            if ($order->user->review_prompted_at->diffInDays(now()) < 7) {
                $promptedRecently = true;
            }
        }

        // 3. Anti-spam per-teléfono (cooldown 7 días) — funciona para invitados
        if (!$promptedRecently && $phone) {
            $recent = Order::where('contact_phone', $phone)
                ->whereNotNull('review_prompt_sent_at')
                ->where('id', '!=', $order->id)
                ->where('review_prompt_sent_at', '>=', now()->subDays(7))
                ->exists();
            if ($recent) {
                $promptedRecently = true;
            }
        }

        if ($promptedRecently) {
            return;
        }

        // 4. Enviar email (solo si hay email)
        $email = $order->user?->email;
        if ($email) {
            try {
                Mail::to($email)->send(new ReviewRequestMail($order));
            } catch (\Throwable $e) {
                \Log::warning('Review request email failed: ' . $e->getMessage());
            }
        }

        // 5. Marcar flags para no repetir
        $order->update(['review_prompt_sent_at' => now()]);
        if ($order->user) {
            $order->user->update(['review_prompted_at' => now()]);
        }
    }

    /**
     * Send reminder for pending reviews.
     */
    public function sendReviewReminder(User $user, Order $order): void
    {
        Mail::to($user->email)->send(new ReviewReminderMail($order));
    }

    /**
     * Mark order items as reviewed.
     */
    public function markOrderAsReviewed(Order $order, User $user): void
    {
        $order->orderItems()->whereNull('reviewed_at')->update([
            'reviewed_at' => now(),
        ]);
    }
}
