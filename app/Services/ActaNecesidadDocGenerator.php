<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Genera el documento del Acta de Necesidad a partir de la plantilla .docx
 * (idéntica al formato oficial) y lo convierte a PDF con LibreOffice.
 */
class ActaNecesidadDocGenerator
{
    /** Ruta de la plantilla normalizada (macros ${}). */
    private string $plantilla;

    public function __construct()
    {
        $this->plantilla = resource_path('document-templates/acta_necesidad.docx');
    }

    /**
     * Genera el PDF del acta y devuelve la ruta relativa (disk public) donde quedó guardado.
     *
     * @param  array  $datos  Campos del acta (ver claves abajo).
     * @return string  Ruta relativa en disk 'public', ej: actas-necesidad/pdf/ACTA-0826.pdf
     */
    public function generarPdf(array $datos): string
    {
        $docxTmp = $this->generarDocx($datos);

        $nombre  = 'ACTA-0' . ($datos['CODIGO'] ?? 'SN');
        $pdfRel  = 'actas-necesidad/pdf/' . $this->slug($nombre) . '.pdf';
        $pdfAbs  = Storage::disk('public')->path($pdfRel);

        Storage::disk('public')->makeDirectory('actas-necesidad/pdf');

        $this->convertirAPdf($docxTmp, $pdfAbs);

        @unlink($docxTmp);

        return $pdfRel;
    }

    /**
     * Rellena la plantilla y devuelve la ruta absoluta del .docx temporal generado.
     */
    public function generarDocx(array $datos): string
    {
        $tp = new TemplateProcessor($this->plantilla);

        $texto = [
            'DEPENDENCIA'       => $datos['DEPENDENCIA'] ?? '',
            'AREA'              => $datos['AREA'] ?? '',
            'NOMBRE_SOLICITANTE'=> $datos['NOMBRE_SOLICITANTE'] ?? '',
            'OBJETO'            => $datos['OBJETO'] ?? '',
            'TIPO_CONTRATO'     => $datos['TIPO_CONTRATO'] ?? '',
            'DURACION'          => $datos['DURACION'] ?? '',
            'MODALIDAD'         => $datos['MODALIDAD'] ?? '',
            'TIPO_SOLICITUD'    => $datos['TIPO_SOLICITUD'] ?? '',
            'NUMERO_CONTRATO'   => $datos['NUMERO_CONTRATO'] ?? '',
            'PRESUPUESTO'       => $datos['PRESUPUESTO'] ?? '',
            'BPIM_BPIN'         => $datos['BPIM_BPIN'] ?? '',
            'CODIGO_PAA'        => $datos['CODIGO_PAA'] ?? '',
            'OBSERVACIONES'     => $datos['OBSERVACIONES'] ?? '',
            'CODIGO'            => $datos['CODIGO'] ?? '',
            'FECHA_SOLICITADO'  => $datos['FECHA_SOLICITADO'] ?? '',
            'label_alcalde'     => $datos['label_alcalde'] ?? 'Vo Bo. Alcalde Municipal',
        ];

        foreach ($texto as $macro => $valor) {
            $tp->setValue($macro, (string) $valor);
        }

        // Firma del alcalde (imagen configurable). Si no hay, usa la firma por defecto.
        $firma = $datos['firma_alcalde_path'] ?? null;
        if (! $firma || ! is_file($firma)) {
            $default = public_path('images/actas/firma-alcalde.png');
            $firma = is_file($default) ? $default : null;
        }
        if ($firma && is_file($firma)) {
            $tp->setImageValue('firma_alcalde', [
                'path'   => $firma,
                'width'  => 120,
                'height' => 45,
                'ratio'  => true,
            ]);
        } else {
            $tp->setValue('firma_alcalde', '');
        }

        $docxTmp = tempnam(sys_get_temp_dir(), 'acta_') . '.docx';
        $tp->saveAs($docxTmp);

        return $docxTmp;
    }

    /**
     * Convierte un .docx a PDF usando LibreOffice headless.
     */
    private function convertirAPdf(string $docxAbs, string $pdfDestino): void
    {
        $bin     = config('services.libreoffice.bin', 'soffice');
        $outDir  = dirname($pdfDestino);
        $tmpProf = tempnam(sys_get_temp_dir(), 'lo_') . '_prof';

        // LibreOffice genera el PDF con el mismo basename del docx en outDir
        $cmd = escapeshellarg($bin)
            . ' -env:UserInstallation=' . escapeshellarg('file:///' . str_replace('\\', '/', $tmpProf))
            . ' --headless --convert-to pdf --outdir ' . escapeshellarg($outDir)
            . ' ' . escapeshellarg($docxAbs);

        @exec($cmd . ' 2>&1', $salida, $codigo);

        // El PDF sale con el basename del docx
        $generado = $outDir . DIRECTORY_SEPARATOR . pathinfo($docxAbs, PATHINFO_FILENAME) . '.pdf';

        if (is_file($generado)) {
            if (realpath($generado) !== realpath($pdfDestino)) {
                @rename($generado, $pdfDestino);
            }
        }

        if (! is_file($pdfDestino)) {
            throw new \RuntimeException(
                'No se pudo generar el PDF del acta con LibreOffice. Verifique que soffice esté instalado y accesible. Salida: '
                . implode("\n", (array) $salida)
            );
        }
    }

    private function slug(string $s): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '_', $s);
    }
}
