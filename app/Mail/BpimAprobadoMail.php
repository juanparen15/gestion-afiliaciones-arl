<?php

namespace App\Mail;

use App\Models\SolicitudBpim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BpimAprobadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SolicitudBpim $solicitud) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALCALDÍA DE PUERTO BOYACÁ - SOLICITUD BPIM APROBADA - ' . $this->solicitud->codigo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bpim-aprobado');
    }

    public function attachments(): array
    {
        if (! $this->solicitud->url_documento_pdf || ! Storage::exists($this->solicitud->url_documento_pdf)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->solicitud->url_documento_pdf)
                ->as('BPIM_' . $this->solicitud->codigo . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
