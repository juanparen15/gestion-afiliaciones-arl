<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AprobacionMasivaCertificados;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Procesa en segundo plano un lote grande de certificados ARL y notifica
 * al usuario validador cuando termina.
 */
class ProcesarCertificadosArlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Sin reintentos: evita doble validación si algo falla a mitad. */
    public int $tries = 1;

    /** Hasta 1 hora para lotes muy grandes. */
    public int $timeout = 3600;

    /**
     * @param  array<int,string>  $rutas
     */
    public function __construct(
        public array $rutas,
        public int $validadorId
    ) {}

    public function handle(AprobacionMasivaCertificados $servicio): void
    {
        $resultado = $servicio->procesar($this->rutas, $this->validadorId);

        $usuario = User::find($this->validadorId);
        if (! $usuario) {
            return;
        }

        $tipo = count($resultado['aprobadas']) > 0 ? 'success' : 'warning';

        Notification::make()
            ->{$tipo}()
            ->title(count($resultado['aprobadas']) . ' afiliación(es) aprobada(s)')
            ->body(AprobacionMasivaCertificados::resumen($resultado))
            ->sendToDatabase($usuario);
    }

    /**
     * Si el job falla por completo, avisar al usuario.
     */
    public function failed(\Throwable $e): void
    {
        $usuario = User::find($this->validadorId);
        if (! $usuario) {
            return;
        }

        Notification::make()
            ->danger()
            ->title('Error en la aprobación masiva')
            ->body('Ocurrió un error procesando los certificados. Intente nuevamente o contacte al administrador.')
            ->sendToDatabase($usuario);
    }
}
