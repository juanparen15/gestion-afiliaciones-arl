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

        $proteger = (bool) config('services.actas.proteger_pdf', true);
        $this->convertirAPdf($docxTmp, $pdfAbs, $proteger);

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

        $docxTmp = tempnam(sys_get_temp_dir(), 'acta_') . '.docx';
        $tp->saveAs($docxTmp);

        // QR de verificación como imagen FLOTANTE en la celda FIRMA (no crece la tabla).
        if (! empty($datos['url_verificacion'])) {
            $qrPng = $this->generarQrPng($datos['url_verificacion']);
            if ($qrPng && is_file($qrPng)) {
                $this->insertarQrFlotante($docxTmp, $qrPng);
                @unlink($qrPng);
            }
        }

        return $docxTmp;
    }

    /**
     * Inserta el QR como imagen flotante (anclada) sobre la celda FIRMA del documento,
     * clonando el patrón de la firma del alcalde. No modifica el flujo de la tabla.
     */
    private function insertarQrFlotante(string $docxPath, string $qrPng): void
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($docxPath) !== true) {
                return;
            }

            // 1) Agregar la imagen del QR al paquete
            $zip->addFile($qrPng, 'word/media/qr_acta.png');

            // 2) Relación en document.xml.rels
            $relsName = 'word/_rels/document.xml.rels';
            $rels = $zip->getFromName($relsName);
            $rid = 'rIdQrActa';
            if ($rels !== false && strpos($rels, $rid) === false) {
                $rels = str_replace(
                    '</Relationships>',
                    '<Relationship Id="' . $rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/qr_acta.png"/></Relationships>',
                    $rels
                );
                $zip->addFromString($relsName, $rels);
            }

            // 3) Anclar el dibujo en el párrafo de la celda FIRMA (4a celda de la fila de datos)
            $doc = $zip->getFromName('word/document.xml');
            $i = strpos($doc, 'ACTA DE NECESIDAD');
            $i = $i !== false ? strpos($doc, 'FIRMA', $i) : false; // encabezado FIRMA
            // La celda FIRMA de datos: 4a celda de la fila que contiene el nombre del solicitante.
            // Se ancla en la última celda de esa fila.
            $anchorPar = strpos($doc, '</w:tr>', (int) $i); // fin de la fila de encabezados
            $rowStart  = $anchorPar !== false ? strpos($doc, '<w:tr', $anchorPar) : false; // fila de datos
            if ($rowStart !== false) {
                $rowEnd = strpos($doc, '</w:tr>', $rowStart);
                $insertPos = strrpos(substr($doc, 0, $rowEnd), '</w:p>');
                if ($insertPos !== false) {
                    $cx = 620000; $cy = 620000; // ~0.68in
                    $drawing = '<w:r><w:drawing><wp:anchor behindDoc="0" distT="0" distB="0" distL="0" distR="0" simplePos="0" locked="0" layoutInCell="1" allowOverlap="1" relativeHeight="5">'
                        . '<wp:simplePos x="0" y="0"/>'
                        . '<wp:positionH relativeFrom="column"><wp:posOffset>60000</wp:posOffset></wp:positionH>'
                        . '<wp:positionV relativeFrom="paragraph"><wp:posOffset>-40000</wp:posOffset></wp:positionV>'
                        . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>'
                        . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
                        . '<wp:wrapNone/>'
                        . '<wp:docPr id="777" name="QR"/>'
                        . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                        . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="777" name="qr_acta.png"/><pic:cNvPicPr/></pic:nvPicPr>'
                        . '<pic:blipFill><a:blip r:embed="' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
                        . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic>'
                        . '</a:graphicData></a:graphic></wp:anchor></w:drawing></w:r>';
                    $doc = substr($doc, 0, $insertPos) . $drawing . substr($doc, $insertPos);
                    $zip->addFromString('word/document.xml', $doc);
                }
            }

            $zip->close();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Genera un PNG temporal con el código QR de verificación. */
    private function generarQrPng(string $contenido): ?string
    {
        try {
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $qr = new \Endroid\QrCode\QrCode(
                data: $contenido,
                size: 240,
                margin: 4,
            );
            $tmp = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            $writer->write($qr)->saveToFile($tmp);
            return $tmp;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Convierte un .docx a PDF usando LibreOffice headless.
     *
     * Si $proteger es true, exporta el PDF cifrado con permisos de SOLO IMPRESIÓN
     * (sin modificar, sin copiar/extraer contenido → no se puede tomar la firma),
     * usando el FilterData del filtro writer_pdf_Export de LibreOffice.
     */
    private function convertirAPdf(string $docxAbs, string $pdfDestino, bool $proteger = true): void
    {
        $bin    = config('services.libreoffice.bin', 'soffice');
        $outDir = dirname($pdfDestino);

        // Perfil de usuario único para permitir instancias concurrentes de LibreOffice
        $profileDir = str_replace('\\', '/', sys_get_temp_dir() . '/lo_acta_' . uniqid());
        $loProfile  = 'file:///' . ltrim($profileDir, '/');

        // El acta es un formulario de UNA sola página (no se usa PageRange: en algunas
        // versiones de LibreOffice rompe la conversión por línea de comandos).
        if ($proteger) {
            // Exportar cifrado: solo impresión, sin modificar ni copiar/extraer.
            $filterData = [
                'EncryptFile'                           => ['type' => 'boolean', 'value' => 'true'],
                'PermissionPassword'                    => ['type' => 'string',  'value' => $this->ownerPassword()],
                'Printing'                              => ['type' => 'long',    'value' => '2'],   // 2 = impresión alta resolución permitida
                'Changes'                               => ['type' => 'long',    'value' => '0'],   // 0 = ningún cambio permitido
                'EnableCopyingOfContent'                => ['type' => 'boolean', 'value' => 'false'], // no copiar/extraer (ni la firma)
                'EnableTextAccessForAccessibilityTools' => ['type' => 'boolean', 'value' => 'false'],
            ];
            $convertTo = 'pdf:writer_pdf_Export:' . json_encode($filterData, JSON_UNESCAPED_SLASHES);
        } else {
            $convertTo = 'pdf';
        }

        $cmd = [
            $bin,
            '--headless',
            '--nofirststartwizard',
            '-env:UserInstallation=' . $loProfile,
            '--convert-to', $convertTo,
            '--outdir', $outDir,
            $docxAbs,
        ];

        $salida = $this->ejecutar($cmd, 90);

        // El PDF sale con el basename del docx en $outDir
        $generado = $outDir . DIRECTORY_SEPARATOR . pathinfo($docxAbs, PATHINFO_FILENAME) . '.pdf';

        if (is_file($generado) && realpath($generado) !== realpath($pdfDestino)) {
            @rename($generado, $pdfDestino);
        }

        if (! is_file($pdfDestino)) {
            throw new \RuntimeException(
                'No se pudo generar el PDF del acta con LibreOffice. Verifique que soffice esté instalado y accesible. Salida: ' . $salida
            );
        }

        // Garantía: si se pidió proteger pero LibreOffice no cifró, avisar
        if ($proteger && ! $this->pdfEstaCifrado($pdfDestino)) {
            report(new \RuntimeException('El acta se generó pero LibreOffice no aplicó el cifrado (versión sin soporte de FilterData).'));
        }
    }

    /** Ejecuta LibreOffice con proc_open y timeout, devuelve su salida. */
    private function ejecutar(array $cmd, int $timeout): string
    {
        $process = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (! is_resource($process)) {
            throw new \RuntimeException('No se pudo iniciar el proceso de LibreOffice.');
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
            throw new \RuntimeException("LibreOffice superó el tiempo límite de {$timeout}s.");
        }
        proc_close($process);

        return trim($output);
    }

    /** Contraseña de propietario determinística (no se expone al usuario). */
    private function ownerPassword(): string
    {
        return substr(hash('sha256', config('app.key') . '|actas-necesidad'), 0, 32);
    }

    /** ¿El PDF quedó cifrado (tiene diccionario /Encrypt)? */
    private function pdfEstaCifrado(string $ruta): bool
    {
        $contenido = @file_get_contents($ruta);
        return $contenido !== false && str_contains($contenido, '/Encrypt');
    }

    private function slug(string $s): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '_', $s);
    }
}
