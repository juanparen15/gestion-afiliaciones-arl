<?php

namespace App\Mail;

use App\Models\ActaNecesidad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ActaAprobadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ActaNecesidad $acta) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALCALDÍA DE PUERTO BOYACÁ - ACTA DE NECESIDAD - ACTA No 0' . $this->acta->consecutivo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.acta-aprobada');
    }

    public function attachments(): array
    {
        if (! $this->acta->pdf_path || ! Storage::disk('public')->exists($this->acta->pdf_path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->acta->pdf_path)
                ->as('ACTA DE NECESIDAD No 0' . $this->acta->consecutivo . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
