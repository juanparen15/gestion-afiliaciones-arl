<?php

namespace App\Mail;

use App\Models\ActaNecesidad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActaNuevaSolicitudMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ActaNecesidad $acta) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva solicitud de Acta de Necesidad pendiente de revisión',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.acta-nueva-solicitud');
    }
}
