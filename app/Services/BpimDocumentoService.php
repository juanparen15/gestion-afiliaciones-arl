<?php

namespace App\Services;

use App\Models\SolicitudBpim;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Genera el documento Word de una Solicitud BPIM (plantilla oficial) y lo
 * convierte a PDF con LibreOffice. Port del DocumentoService de sistema-bpim,
 * adaptado a las convenciones de este proyecto (LibreOffice vía proc_open,
 * como ActaNecesidadDocGenerator; QR con endroid/qr-code).
 */
class BpimDocumentoService
{
    private string $plantillaPath;
    private string $carpetaBase;
    private string $rutaFirmaSecretario;
    private BpimVerificacionService $verificacionService;

    public function __construct()
    {
        $this->plantillaPath = storage_path('app/plantillas/plantilla_bpim.docx');
        $this->carpetaBase = 'documentos_bpim';
        $this->rutaFirmaSecretario = storage_path('app/firmas/firma_secretario.png');
        $this->verificacionService = app(BpimVerificacionService::class);
    }

    /**
     * Genera el Word de una solicitud. Si $forzarRegeneracion, borra el actual primero.
     */
    public function generarDocumentoWord(SolicitudBpim $solicitud, bool $forzarRegeneracion = false): array
    {
        if (! file_exists($this->plantillaPath)) {
            throw new Exception('La plantilla Word de BPIM no existe en: ' . $this->plantillaPath);
        }

        if (! $solicitud->codigo) {
            $solicitud->generarCodigo();
        }

        if (! $solicitud->usuario_elaboro) {
            $solicitud->update(['usuario_elaboro' => auth()->user()?->name ?? 'Sistema']);
        }

        if ($forzarRegeneracion && $solicitud->url_documento_word) {
            if (Storage::exists($solicitud->url_documento_word)) {
                Storage::delete($solicitud->url_documento_word);
            }
            $solicitud->update(['url_documento_word' => null]);
        }

        if (! $forzarRegeneracion && $solicitud->url_documento_word && Storage::exists($solicitud->url_documento_word)) {
            return ['success' => false, 'omitido' => true, 'mensaje' => 'El documento ya existe. Use "Regenerar Word" para reemplazarlo.'];
        }

        $rutaWord = $this->crearDocumentoWord($solicitud);

        $solicitud->update([
            'url_documento_word'     => $rutaWord,
            'fecha_generacion'       => now(),
            'usuario_ultima_edicion' => auth()->user()?->name,
        ]);

        return [
            'success' => true,
            'word'    => $rutaWord,
            'mensaje' => $forzarRegeneracion
                ? 'Documento Word regenerado exitosamente.'
                : 'Documento Word generado exitosamente.',
        ];
    }

    /** Regenera el Word por un funcionario: borra el PDF y vuelve a "pendiente". */
    public function regenerarWordPorFuncionario(SolicitudBpim $solicitud): array
    {
        if ($solicitud->url_documento_pdf) {
            if (Storage::exists($solicitud->url_documento_pdf)) {
                Storage::delete($solicitud->url_documento_pdf);
            }
            $solicitud->update([
                'url_documento_pdf'           => null,
                'aprobado'                    => false,
                'fecha_aprobacion'            => null,
                'fecha_aprobacion_secretario' => null,
                'usuario_aprobo'              => null,
                'fecha_generacion_pdf'        => null,
                'aprobado_enviado'            => false,
            ]);
        }

        $resultado = $this->generarDocumentoWord($solicitud, true);

        return [
            'success' => true,
            'word'    => $resultado['word'] ?? null,
            'mensaje' => 'Word regenerado. El PDF anterior fue eliminado y la solicitud requiere nueva aprobación.',
        ];
    }

    /**
     * Aprueba, crea la verificación (QR + hash), regenera el Word con firma
     * y QR, y genera el PDF final.
     */
    public function aprobarConFirmaYPdf(SolicitudBpim $solicitud): array
    {
        $solicitud->aprobarSolicitud();

        $verificacion = $this->verificacionService->crearVerificacion($solicitud);
        $solicitud->load('verificacion');

        $this->generarDocumentoWord($solicitud, true);
        $rutaPdf = $this->convertirWordAPdf($solicitud);

        $solicitud->update([
            'url_documento_pdf'    => $rutaPdf,
            'fecha_generacion_pdf' => now(),
        ]);

        $verificacion->update(['hash_documento' => $this->verificacionService->calcularHashDocumento($rutaPdf)]);

        return [
            'success'              => true,
            'mensaje'              => 'Documento aprobado y firmado. Se generó el Word con firma, QR de verificación y el PDF final.',
            'pdf'                  => $rutaPdf,
            'codigo_verificacion'  => $verificacion->codigo_verificacion,
            'url_verificacion'     => $verificacion->getUrlVerificacion(),
        ];
    }

    /** Aprobación masiva (usado por la acción del listado). */
    public function aprobarMasivoConFirmaYPdf(array $solicitudesIds): array
    {
        $resultados = ['exitosos' => 0, 'fallidos' => 0, 'errores' => []];

        foreach ($solicitudesIds as $id) {
            try {
                $this->aprobarConFirmaYPdf(SolicitudBpim::findOrFail($id));
                $resultados['exitosos']++;
            } catch (Exception $e) {
                $resultados['fallidos']++;
                $resultados['errores'][] = ['solicitud_id' => $id, 'error' => $e->getMessage()];
                report($e);
            }
        }

        return $resultados;
    }

    /** Generación masiva de Word (solo faltantes). */
    public function generarWordMasivo(array $solicitudesIds): array
    {
        $resultados = ['exitosos' => 0, 'omitidos' => 0, 'fallidos' => 0, 'errores' => []];

        foreach ($solicitudesIds as $id) {
            try {
                $resultado = $this->generarDocumentoWord(SolicitudBpim::findOrFail($id), false);
                $resultado['success'] ? $resultados['exitosos']++ : $resultados['omitidos']++;
            } catch (Exception $e) {
                $resultados['fallidos']++;
                $resultados['errores'][] = ['solicitud_id' => $id, 'error' => $e->getMessage()];
                report($e);
            }
        }

        return $resultados;
    }

    /** Rechazo masivo. */
    public function rechazarMasivo(array $solicitudesIds, string $motivo): array
    {
        $resultados = ['exitosos' => 0, 'fallidos' => 0, 'errores' => []];

        foreach ($solicitudesIds as $id) {
            try {
                SolicitudBpim::findOrFail($id)->rechazarSolicitud($motivo);
                $resultados['exitosos']++;
            } catch (Exception $e) {
                $resultados['fallidos']++;
                $resultados['errores'][] = ['solicitud_id' => $id, 'error' => $e->getMessage()];
            }
        }

        return $resultados;
    }

    /** Rellena la plantilla y guarda el .docx en el disco por defecto. Devuelve la ruta relativa. */
    private function crearDocumentoWord(SolicitudBpim $solicitud): string
    {
        $tp = new TemplateProcessor($this->plantillaPath);
        $solicitud->load(['presupuestoItems', 'verificacion']);

        foreach ($this->obtenerVariablesPlantilla($solicitud) as $variable => $valor) {
            try {
                $tp->setValue($variable, $valor);
            } catch (Exception $e) {
                // La plantilla no tiene esa variable; se ignora.
            }
        }

        $this->insertarTablaPresupuesto($tp, $solicitud);

        if ($solicitud->aprobado && $solicitud->verificacion) {
            $this->agregarFirmaYQr($tp, $solicitud);
        } else {
            $this->limpiarPlaceholdersFirma($tp);
        }

        $nombreArchivo = 'BPIM_' . ($solicitud->codigo ?: $solicitud->id) . '_' . uniqid() . '.docx';
        $rutaTemporal = storage_path('app/temp/' . $nombreArchivo);
        if (! is_dir(dirname($rutaTemporal))) {
            mkdir(dirname($rutaTemporal), 0755, true);
        }
        $tp->saveAs($rutaTemporal);

        $rutaFinal = $this->carpetaBase . '/' . $nombreArchivo;
        Storage::put($rutaFinal, file_get_contents($rutaTemporal));
        @unlink($rutaTemporal);

        return $rutaFinal;
    }

    /** Variables de texto de la plantilla oficial (ver storage/app/plantillas/plantilla_bpim.docx). */
    private function obtenerVariablesPlantilla(SolicitudBpim $solicitud): array
    {
        return [
            'CODIGO'              => (string) ($solicitud->codigo ?? ''),
            'CONSECUTIVO'         => (string) ($solicitud->consecutivo ?? ''),
            'FECHA'               => Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'DEPENDENCIA'         => (string) $solicitud->dependencia,
            'NOMBRE_SOLICITANTE'  => (string) $solicitud->nombre_solicitante,
            'NOMBRE_PROYECTO'     => (string) $solicitud->nombre_proyecto,
            'CODIGO_BPIM'         => (string) ($solicitud->codigo_bpim ?? ''),
            'CODIGO_BPIN'         => (string) ($solicitud->codigo_bpin ?? ''),
            'OBJETO'              => (string) $solicitud->objeto,
            'CDP'                 => (string) ($solicitud->cdp ?? ''),
            'VALOR_CDP'           => $this->formatearMoneda((float) ($solicitud->valor_cdp ?? 0)),
            'VALOR_EP'            => $this->formatearMoneda((float) ($solicitud->valor_ep ?? 0)),
            'OBJETO_GASTO'        => (string) ($solicitud->objeto_gasto ?? ''),
            'FUENTE_RECURSOS'     => (string) ($solicitud->fuente_recursos ?? ''),
            'MGA'                 => (string) ($solicitud->mga ?? ''),
            'CPC'                 => (string) ($solicitud->cpc ?? ''),
            'PDN_SECTOR'          => (string) ($solicitud->pdn_sector ?? ''),
            'PDN_PROGRAMA'        => (string) ($solicitud->pdn_programa ?? ''),
            'PDN_SUBPROGRAMA'     => (string) ($solicitud->pdn_subprograma ?? ''),
            'PDM_SECTOR'          => (string) ($solicitud->pdm_sector ?? ''),
            'PDM_PROGRAMA'        => (string) ($solicitud->pdm_programa ?? ''),
            'ELABORO'             => (string) ($solicitud->usuario_elaboro ?? $solicitud->nombre_elabora ?? ''),
            'REVISO'              => (string) ($solicitud->usuario_aprobo ?? 'Pendiente de aprobación'),
            'CODIGO_VERIFICACION' => (string) ($solicitud->verificacion->codigo_verificacion ?? ''),
        ];
    }

    /** Inserta la tabla de ítems de presupuesto (placeholder DESCRIPCION clonado por fila). */
    private function insertarTablaPresupuesto(TemplateProcessor $tp, SolicitudBpim $solicitud): void
    {
        $items = $solicitud->presupuestoItems;

        try {
            if ($items->isEmpty()) {
                $tp->cloneRow('DESCRIPCION', 1);
                $tp->setValue('DESCRIPCION#1', 'No hay ítems de presupuesto');
                $tp->setValue('VALOR_UNITARIO#1', '-');
                $tp->setValue('MESES#1', '-');
                $tp->setValue('VALOR_TOTAL#1', '-');
                return;
            }

            $tp->cloneRow('DESCRIPCION', $items->count());
            foreach ($items as $i => $item) {
                $n = $i + 1;
                $tp->setValue("DESCRIPCION#$n", $item->descripcion ?? '');
                $tp->setValue("VALOR_UNITARIO#$n", $this->formatearMoneda((float) $item->valor_unitario));
                $tp->setValue("MESES#$n", (string) ($item->cantidad ?? 0));
                $tp->setValue("VALOR_TOTAL#$n", $this->formatearMoneda((float) $item->valor_total));
            }
        } catch (Exception $e) {
            Log::warning('BPIM: no se pudo insertar la tabla de presupuesto', ['error' => $e->getMessage()]);
        }
    }

    /** Inserta la firma del secretario y el QR de verificación como imágenes. */
    private function agregarFirmaYQr(TemplateProcessor $tp, SolicitudBpim $solicitud): void
    {
        try {
            if (file_exists($this->rutaFirmaSecretario)) {
                $tp->setImageValue('FIRMA_SECRETARIO', [
                    'path' => $this->rutaFirmaSecretario, 'width' => 220, 'height' => 130, 'ratio' => true,
                ]);
            } else {
                $tp->setValue('FIRMA_SECRETARIO', '[Firma digital]');
            }
        } catch (Exception $e) {
            Log::warning('BPIM: no se pudo insertar la firma', ['error' => $e->getMessage()]);
        }

        $qrTmp = null;
        try {
            $qrTmp = $this->verificacionService->generarQrPng($solicitud->verificacion);
            $tp->setImageValue('QR_VERIFICACION', ['path' => $qrTmp, 'width' => 150, 'height' => 150, 'ratio' => true]);
        } catch (Exception $e) {
            Log::warning('BPIM: no se pudo insertar el QR', ['error' => $e->getMessage()]);
            try {
                $tp->setValue('QR_VERIFICACION', '');
            } catch (Exception $e2) {
            }
        } finally {
            if ($qrTmp && is_file($qrTmp)) {
                @unlink($qrTmp);
            }
        }
    }

    private function limpiarPlaceholdersFirma(TemplateProcessor $tp): void
    {
        foreach (['FIRMA_SECRETARIO', 'QR_VERIFICACION', 'CODIGO_VERIFICACION'] as $var) {
            try {
                $tp->setValue($var, '');
            } catch (Exception $e) {
            }
        }
    }

    /** Convierte el Word de la solicitud a PDF con LibreOffice headless. Devuelve la ruta relativa. */
    private function convertirWordAPdf(SolicitudBpim $solicitud): string
    {
        $rutaWordAbs = Storage::path($solicitud->url_documento_word);
        $bin = config('services.libreoffice.bin', 'soffice');

        $profileDir = str_replace('\\', '/', sys_get_temp_dir() . '/lo_bpim_' . uniqid());
        $loProfile = 'file:///' . ltrim($profileDir, '/');
        $outDir = dirname($rutaWordAbs);

        $cmd = [
            $bin, '--headless', '--nofirststartwizard',
            '-env:UserInstallation=' . $loProfile,
            '--convert-to', 'pdf',
            '--outdir', $outDir,
            $rutaWordAbs,
        ];

        $salida = $this->ejecutarLibreOffice($cmd, 90);

        $generado = $outDir . DIRECTORY_SEPARATOR . pathinfo($rutaWordAbs, PATHINFO_FILENAME) . '.pdf';

        // Reintenta brevemente: en Windows el archivo puede reportarse como
        // generado antes de que LibreOffice suelte el handle por completo.
        $intentos = 0;
        while (! is_file($generado) && $intentos < 10) {
            usleep(200_000);
            $intentos++;
        }
        if (! is_file($generado) || filesize($generado) < 1) {
            throw new Exception('No se pudo generar el PDF con LibreOffice. Salida: ' . $salida);
        }

        $rutaFinal = $this->carpetaBase . '/' . pathinfo($rutaWordAbs, PATHINFO_FILENAME) . '.pdf';
        $destinoAbs = Storage::path($rutaFinal);

        if (realpath($generado) !== false && realpath($generado) === realpath($destinoAbs)) {
            // LibreOffice ya escribió directamente en la carpeta final.
        } elseif (! @rename($generado, $destinoAbs)) {
            Storage::put($rutaFinal, file_get_contents($generado));
            @unlink($generado);
        }

        return $rutaFinal;
    }

    private function ejecutarLibreOffice(array $cmd, int $timeout): string
    {
        $process = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (! is_resource($process)) {
            throw new Exception('No se pudo iniciar el proceso de LibreOffice.');
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $deadline = microtime(true) + $timeout;
        $output = '';
        $code = null;
        while (microtime(true) < $deadline) {
            $status = proc_get_status($process);
            if (! $status['running']) {
                $code = $status['exitcode'];
                break;
            }
            $output .= stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            usleep(200_000);
        }
        $output .= stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if ($code === null) {
            proc_terminate($process);
            proc_close($process);
            throw new Exception("LibreOffice superó el tiempo límite de {$timeout}s.");
        }
        proc_close($process);

        return trim($output);
    }

    private function formatearMoneda(?float $valor): string
    {
        return '$' . number_format($valor ?? 0, 0, ',', '.');
    }
}
