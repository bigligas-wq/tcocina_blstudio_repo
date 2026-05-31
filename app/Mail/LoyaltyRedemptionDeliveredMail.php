<?php

namespace App\Mail;

use App\Models\LoyaltyRedemption;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoyaltyRedemptionDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LoyaltyRedemption $redemption)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Disfrutá tu premio 🍔 — T cocina',
        );
    }

    public function content(): Content
    {
        $reward = $this->redemption->reward_snapshot ?? [];
        return new Content(
            view: 'emails.loyalty-redemption-delivered',
            with: [
                'userName'    => $this->redemption->user->name ?? 'Cliente',
                'rewardValue' => $reward['reward_value'] ?? 'tu premio',
            ],
        );
    }
}
