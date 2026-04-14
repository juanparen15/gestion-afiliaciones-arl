<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AfiliacionesPendientesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $afiliaciones
    ) {}

    public function envelope(): Envelope
    {
        $cantidad = $this->afiliaciones->count();

        return new Envelope(
            subject: "Recordatorio: {$cantidad} afiliación(es) pendiente(s) de validación - ARL",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.afiliaciones-pendientes',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
