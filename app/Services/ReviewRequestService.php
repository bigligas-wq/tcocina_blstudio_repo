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
     */
    public function sendReviewRequest(Order $order): void
    {
        if (!$order->user) {
            return;
        }

        // Send email
        Mail::to($order->user->email)->send(new ReviewRequestMail($order));
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
