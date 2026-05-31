<?php

namespace App\Mail;

use App\Models\LoyaltyRedemption;
use App\Models\LoyaltySetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoyaltyRedemptionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LoyaltyRedemption $redemption)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu premio fue aprobado 🎉 — T cocina',
        );
    }

    public function content(): Content
    {
        $reward = $this->redemption->reward_snapshot ?? [];
        // Obtener instrucciones de canje del setting activo (o del snapshot si existe)
        $redemptionInstructions = $this->redemption->reward_snapshot['redemption_instructions'] ?? null;
        if (empty($redemptionInstructions)) {
            try {
                $setting = LoyaltySetting::active();
                $redemptionInstructions = $setting->redemption_instructions;
            } catch (\Throwable $e) {
                $redemptionInstructions = null;
            }
        }
        return new Content(
            view: 'emails.loyalty-redemption-approved',
            with: [
                'userName'               => $this->redemption->user->name ?? 'Cliente',
                'rewardValue'            => $reward['reward_value'] ?? 'tu premio',
                'redemptionInstructions' => $redemptionInstructions,
            ],
        );
    }
}
