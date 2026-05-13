<?php

namespace App\Mail;

use App\Models\Afiliacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovedadRechazadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Afiliacion $afiliacion
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Adición/Prórroga Rechazada — Contrato ' . $this->afiliacion->numero_contrato,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.novedad-rechazada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
