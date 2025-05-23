<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodigoReferidorMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $codigo;

    public function __construct(string $codigo)
    {
        $this->codigo = $codigo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de Verificación - Referidor'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.codigo-referidor',
            with: ['codigo' => $this->codigo],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
