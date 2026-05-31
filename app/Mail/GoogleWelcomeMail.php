<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GoogleWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a T cocina: tu album ya esta activo 🍔🍟',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.google-welcome',
            with: [
                'userName' => $this->user->name,
            ],
        );
    }
}
