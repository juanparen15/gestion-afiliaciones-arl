<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContratosSecopProximosVencerMail extends Mailable
{
    use SerializesModels;

    public array $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function envelope(): Envelope
    {
        $dependenciaNombre = $this->datos['dependencia']?->nombre ?? 'Sin dependencia';
        $cantidad          = $this->datos['contratos']->count();

        return new Envelope(
            subject: "Alerta SECOP: {$cantidad} contrato(s) próximo(s) a vencer - {$dependenciaNombre}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contratos-secop-proximos-vencer',
            with: [
                'dependencia' => $this->datos['dependencia'],
                'contratos'   => $this->datos['contratos'],
                'diasAlerta'  => $this->datos['diasAlerta'],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
