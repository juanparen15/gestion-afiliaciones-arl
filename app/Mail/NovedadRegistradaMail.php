<?php

namespace App\Mail;

use App\Models\Afiliacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovedadRegistradaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Afiliacion $afiliacion,
        public string $tipoNovedad,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novedad registrada (' . $this->tipoNovedad . ') - Afiliación ARL',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.novedad-registrada',
            with: [
                'afiliacion'  => $this->afiliacion,
                'tipoNovedad' => $this->tipoNovedad,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
