<?php

namespace App\Services;

use App\Models\Afiliacion;
use Illuminate\Support\Facades\Storage;

/**
 * Procesa un lote de certificados ARL en PDF: lee la cédula de cada uno,
 * la empareja con la afiliación pendiente correspondiente y la valida.
 *
 * Lo usan tanto la acción de Filament (modo síncrono) como el Job en cola
 * (modo segundo plano), para no duplicar la lógica.
 */
class AprobacionMasivaCertificados
{
    public function __construct(
        private CertificadoArlExtractor $extractor
    ) {}

    /**
     * @param  array<int,string>  $rutas       Rutas relativas (disk public) de los PDFs subidos.
     * @param  int                $validadorId ID del usuario que aprueba (validated_by).
     * @return array{aprobadas: array<int,string>, sinCoincidir: array<int,string>, sinCedula: int}
     */
    public function procesar(array $rutas, int $validadorId): array
    {
        $disk = Storage::disk('public');

        // Mapa de afiliaciones pendientes por cédula normalizada
        $mapa = [];
        foreach (Afiliacion::where('estado', 'pendiente')->get() as $af) {
            $key = preg_replace('/\D/', '', (string) $af->numero_documento);
            if ($key !== '') {
                $mapa[$key] = $af;
            }
        }

        $aprobadas    = [];
        $sinCoincidir = [];
        $sinCedula    = 0;

        foreach ($rutas as $ruta) {
            $candidatas = $this->extractor->extraer($disk->path($ruta))['candidatas'] ?? [];

            if (empty($candidatas)) {
                $sinCedula++;
                $disk->delete($ruta);
                continue;
            }

            // Primera candidata que corresponda a una pendiente disponible
            $match = null;
            foreach ($candidatas as $cedula) {
                if (isset($mapa[$cedula])) {
                    $match = $mapa[$cedula];
                    unset($mapa[$cedula]); // un PDF por afiliación
                    break;
                }
            }

            if (! $match) {
                $sinCoincidir[] = $candidatas[0];
                $disk->delete($ruta);
                continue;
            }

            $match->update([
                'estado'           => 'validado',
                'validated_by'     => $validadorId,
                'fecha_validacion' => now(),
                'motivo_rechazo'   => null,
                'pdf_arl'          => $ruta,
            ]);

            $aprobadas[] = $match->numero_documento . ' - ' . $match->nombre_contratista;
        }

        return [
            'aprobadas'    => $aprobadas,
            'sinCoincidir' => $sinCoincidir,
            'sinCedula'    => $sinCedula,
        ];
    }

    /**
     * Construye el texto de resumen a partir del resultado de procesar().
     *
     * @param  array{aprobadas: array<int,string>, sinCoincidir: array<int,string>, sinCedula: int}  $resultado
     */
    public static function resumen(array $resultado): string
    {
        $cuerpo = '✅ Aprobadas: ' . count($resultado['aprobadas']);

        if (! empty($resultado['sinCoincidir'])) {
            $cuerpo .= ' | ⚠️ Sin afiliación pendiente: ' . count($resultado['sinCoincidir'])
                . ' (cédulas: ' . implode(', ', array_slice($resultado['sinCoincidir'], 0, 10)) . ')';
        }

        if ($resultado['sinCedula'] > 0) {
            $cuerpo .= ' | ❌ No se pudo leer la cédula: ' . $resultado['sinCedula'] . ' PDF(s)';
        }

        return $cuerpo;
    }
}
