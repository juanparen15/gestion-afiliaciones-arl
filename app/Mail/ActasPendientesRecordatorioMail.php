<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ActasPendientesRecordatorioMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Collection $actas) {}

    public function envelope(): Envelope
    {
        $n = $this->actas->count();
        return new Envelope(
            subject: "Recordatorio: {$n} acta(s) de necesidad pendiente(s) de revisión",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.actas-pendientes-recordatorio');
    }
}
