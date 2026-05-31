<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LabIdeaProposedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $cliente;
    public string $idea;
    public ?string $imagenPath;

    public function __construct(User $cliente, string $idea, ?string $imagenPath = null)
    {
        $this->cliente = $cliente;
        $this->idea = $idea;
        $this->imagenPath = $imagenPath;
    }

    public function build()
    {
        $imagenUrl = $this->imagenPath ? asset('storage/' . $this->imagenPath) : null;

        $mail = $this->subject('💡 Nueva idea propuesta — Laboratorio')
            ->view('emails.lab-idea-proposed')
            ->with('imagenUrl', $imagenUrl);

        if ($this->imagenPath) {
            $diskPath = storage_path('app/public/' . $this->imagenPath);
            if (file_exists($diskPath)) {
                $mail->attach($diskPath, ['as' => basename($this->imagenPath)]);
            }
        }

        return $mail;
    }
}
