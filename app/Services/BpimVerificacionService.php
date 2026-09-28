<?php

namespace App\Services;

use App\Models\DocumentoVerificacion;
use App\Models\SolicitudBpim;
use Illuminate\Support\Facades\Storage;

/**
 * Crea y valida los registros de verificación (QR + hash SHA-256) de las
 * Solicitudes BPIM aprobadas. Espejo de DocumentoVerificacionService del
 * sistema-bpim original, adaptado a endroid/qr-code (ya usado por Actas
 * de Necesidad) en vez de simplesoftwareio/qrcode.
 */
class BpimVerificacionService
{
    /** Crea el registro de verificación para una solicitud aprobada. */
    public function crearVerificacion(SolicitudBpim $solicitud, string $hashDocumento = 'temp'): DocumentoVerificacion
    {
        return DocumentoVerificacion::create([
            'solicitud_bpim_id'   => $solicitud->id,
            'codigo_verificacion' => DocumentoVerificacion::generarCodigoVerificacion(),
            'hash_documento'      => $hashDocumento === 'temp' ? hash('sha256', 'temporal') : $hashDocumento,
            'fecha_firma'         => now(),
            'firmado_por'         => auth()->user()?->name ?? 'Sistema',
            'ip_firma'            => request()->ip(),
            'metadata'            => [
                'codigo_solicitud' => $solicitud->codigo,
                'nombre_proyecto'  => $solicitud->nombre_proyecto,
                'dependencia'      => $solicitud->dependencia,
            ],
        ]);
    }

    /** Genera un PNG temporal (ruta absoluta) con el QR de verificación. */
    public function generarQrPng(DocumentoVerificacion $verificacion): string
    {
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $qr = new \Endroid\QrCode\QrCode(
            data: $verificacion->getUrlVerificacion(),
            size: 300,
            margin: 4,
        );

        $carpeta = storage_path('app/temp/qr');
        if (! is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $ruta = $carpeta . '/qr_' . $verificacion->codigo_verificacion . '.png';
        $writer->write($qr)->saveToFile($ruta);

        return $ruta;
    }

    /** Calcula el hash SHA-256 de un archivo del disco 'local'. */
    public function calcularHashDocumento(string $rutaRelativa): string
    {
        return hash('sha256', Storage::get($rutaRelativa) ?? '');
    }

    /** Verifica la autenticidad de un código de verificación. */
    public function verificarDocumento(string $codigoVerificacion): array
    {
        $verificacion = DocumentoVerificacion::where('codigo_verificacion', $codigoVerificacion)
            ->where('activo', true)
            ->with('solicitud')
            ->first();

        if (! $verificacion || ! $verificacion->solicitud) {
            return ['valido' => false, 'verificacion' => null];
        }

        $verificacion->registrarVerificacion();

        return ['valido' => true, 'verificacion' => $verificacion];
    }
}
