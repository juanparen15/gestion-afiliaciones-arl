<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

/**
 * Extrae el número de documento (cédula) del titular de un certificado ARL en PDF.
 *
 * Estrategia:
 *   1. Lee el texto del PDF (PDFs digitales como el de SURA).
 *   2. Busca candidatas de cédula priorizando las cercanas a palabras clave.
 *   3. Si el PDF no tiene texto (escaneado/imagen) → usa Gemini como respaldo.
 *
 * El emparejamiento final SIEMPRE se valida contra las cédulas reales en la BD,
 * por lo que el regex no necesita ser perfecto: solo proponer candidatas.
 */
class CertificadoArlExtractor
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key', '');
        $this->model  = (string) config('services.gemini.model', 'gemini-2.0-flash') ?: 'gemini-2.0-flash';
    }

    /**
     * @return array{texto: string, candidatas: array<int,string>, fuente: string}
     */
    public function extraer(string $rutaAbsoluta): array
    {
        $texto      = $this->extraerTexto($rutaAbsoluta);
        $candidatas = $this->candidatasDesdeTexto($texto);
        $fuente     = 'texto';

        // PDF escaneado / sin texto útil → respaldo con IA
        if (empty($candidatas) && $this->apiKey) {
            $cedulaIa = $this->extraerConGemini($rutaAbsoluta);
            if ($cedulaIa) {
                $candidatas = [$cedulaIa];
                $fuente     = 'ia';
            }
        }

        return [
            'texto'      => $texto,
            'candidatas' => $candidatas,
            'fuente'     => empty($candidatas) ? 'ninguna' : $fuente,
        ];
    }

    private function extraerTexto(string $ruta): string
    {
        // 1) pdftotext (poppler): rápido y soporta PDFs protegidos/cifrados (caso ARL)
        $texto = $this->extraerConPdftotext($ruta);
        if (trim($texto) !== '') {
            return $texto;
        }

        // 2) smalot/pdfparser: PDFs digitales sin protección
        try {
            $parser = new Parser();
            $pdf    = $parser->parseFile($ruta);
            return $pdf->getText() ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Extrae texto usando el binario pdftotext (poppler-utils) si está disponible.
     * Maneja PDFs protegidos con contraseña vacía, que es el caso de los certificados ARL.
     */
    private function extraerConPdftotext(string $ruta): string
    {
        if (! function_exists('exec')) {
            return '';
        }

        $bin = (string) config('services.pdftotext.bin', '') ?: 'pdftotext';

        try {
            $tmp = tempnam(sys_get_temp_dir(), 'arlcert_');
            if ($tmp === false) {
                return '';
            }

            $cmd = escapeshellarg($bin) . ' -q -enc UTF-8 ' . escapeshellarg($ruta) . ' ' . escapeshellarg($tmp);
            @exec($cmd, $salida, $codigo);

            $texto = @file_get_contents($tmp);
            @unlink($tmp);

            return ($codigo === 0 && $texto !== false) ? $texto : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Candidatas de cédula: primero las cercanas a palabras clave, luego el resto.
     *
     * @return array<int,string>
     */
    private function candidatasDesdeTexto(string $texto): array
    {
        if (trim($texto) === '') {
            return [];
        }

        $prioritarias = [];
        $respaldo     = [];

        // 1) Números cercanos a palabras clave de identificación
        $patronClave = '/(?:c[eé]dula|identificad[oa]|c\.?\s?c\.?|documento|identificaci[oó]n|n[uú]mero\s+de\s+identificaci[oó]n)\D{0,30}(\d[\d\.\s]{5,15}\d)/iu';
        if (preg_match_all($patronClave, $texto, $m)) {
            foreach ($m[1] as $num) {
                $limpio = preg_replace('/\D/', '', $num);
                if ($this->esCedulaValida($limpio)) {
                    $prioritarias[] = $limpio;
                }
            }
        }

        // 2) Respaldo: cualquier secuencia de 6 a 12 dígitos (admite puntos de miles)
        if (preg_match_all('/(?<!\d)(\d{1,3}(?:\.\d{3})+|\d{6,12})(?!\d)/', $texto, $m2)) {
            foreach ($m2[1] as $num) {
                $limpio = preg_replace('/\D/', '', $num);
                if ($this->esCedulaValida($limpio)) {
                    $respaldo[] = $limpio;
                }
            }
        }

        return array_values(array_unique(array_merge($prioritarias, $respaldo)));
    }

    private function esCedulaValida(string $numero): bool
    {
        $len = strlen($numero);
        return $len >= 6 && $len <= 12 && ltrim($numero, '0') !== '';
    }

    /**
     * Respaldo con Gemini para PDFs escaneados (sin capa de texto).
     */
    private function extraerConGemini(string $ruta): ?string
    {
        try {
            $contenido = @file_get_contents($ruta);
            if ($contenido === false) {
                return null;
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $payload = [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [
                        ['text' => 'Este es un certificado de afiliación a una ARL (Administradora de Riesgos Laborales) de Colombia. ' .
                            'Extrae ÚNICAMENTE el número de documento (cédula de ciudadanía) de la PERSONA afiliada/certificada, ' .
                            'NO el NIT de la empresa contratante. Responde solo los dígitos, sin puntos, espacios ni texto adicional. ' .
                            'Si no lo encuentras, responde NINGUNO.'],
                        ['inline_data' => [
                            'mime_type' => 'application/pdf',
                            'data'      => base64_encode($contenido),
                        ]],
                    ],
                ]],
                'generationConfig' => ['temperature' => 0],
            ];

            $res = Http::timeout(60)->post($url, $payload);
            if (! $res->successful()) {
                return null;
            }

            $texto = $res->json('candidates.0.content.parts.0.text', '');
            $limpio = preg_replace('/\D/', '', (string) $texto);

            return $this->esCedulaValida($limpio) ? $limpio : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
