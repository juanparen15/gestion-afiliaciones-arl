<?php

namespace App\Mail;

use App\Models\SolicitudBpim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BpimRechazadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SolicitudBpim $solicitud) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SOLICITUD BPIM RECHAZADA - ' . $this->solicitud->codigo . ' - Alcaldía de Puerto Boyacá',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bpim-rechazado');
    }
}
