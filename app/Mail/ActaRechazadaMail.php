<?php

namespace App\Mail;

use App\Models\ActaNecesidad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActaRechazadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ActaNecesidad $acta) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ACTA DE NECESIDAD RECHAZADA, ACTA No ' . ($this->acta->consecutivo ?: $this->acta->id) . ' - Alcaldía de Puerto Boyacá, Boyacá',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.acta-rechazada');
    }
}
