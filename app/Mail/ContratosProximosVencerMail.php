<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContratosProximosVencerMail extends Mailable
{
    use SerializesModels;

    public array $datos;

    /**
     * Create a new message instance.
     */
    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $dependenciaNombre = $this->datos['dependencia']?->nombre ?? 'Sin dependencia';
        $cantidadContratos = $this->datos['afiliaciones']->count();

        return new Envelope(
            subject: "Alerta: {$cantidadContratos} contrato(s) próximo(s) a vencer - {$dependenciaNombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contratos-proximos-vencer',
            with: [
                'dependencia' => $this->datos['dependencia'],
                'afiliaciones' => $this->datos['afiliaciones'],
                'diasAlerta' => $this->datos['diasAlerta'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
