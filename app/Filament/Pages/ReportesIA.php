<?php

namespace App\Filament\Pages;

use App\Services\AIReportService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ReportesIA extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Reportes IA';
    protected static ?string $navigationGroup = 'Inteligencia Artificial';
    protected static ?string $title           = 'Reportes con Inteligencia Artificial';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.pages.reportes-ia';

    public string  $pregunta  = '';
    public bool    $cargando  = false;
    public ?string $error     = null;

    /** @var array<int, array{rol: string, texto: string, hora: string}> */
    public array $historial = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }

    public function consultar(): void
    {
        $this->validate(['pregunta' => 'required|min:5|max:1000']);

        $this->cargando = true;
        $this->error    = null;

        $preguntaActual = $this->pregunta;
        $this->pregunta = '';

        // Agregar mensaje del usuario al historial
        $this->historial[] = [
            'rol'   => 'user',
            'texto' => $preguntaActual,
            'hora'  => now()->format('H:i'),
        ];

        try {
            // Pasar historial previo (sin el mensaje que acabamos de agregar)
            $historialParaIA = array_slice($this->historial, 0, -1);

            $resultado = app(AIReportService::class)->consultar($preguntaActual, $historialParaIA);

            if (isset($resultado['error'])) {
                $this->error = $resultado['error'];
                // Quitar el mensaje del usuario del historial si hubo error
                array_pop($this->historial);
            } else {
                [$textoLimpio, $opciones] = $this->parsearRespuesta($resultado['respuesta']);

                $this->historial[] = [
                    'rol'     => 'ia',
                    'texto'   => $textoLimpio,
                    'hora'    => now()->format('H:i'),
                    'opciones'=> $opciones,
                ];
            }
        } catch (\Throwable $e) {
            $this->error = 'Error inesperado: ' . $e->getMessage();
            array_pop($this->historial);
        } finally {
            $this->cargando = false;
        }
    }

    public function limpiarConversacion(): void
    {
        $this->historial = [];
        $this->pregunta  = '';
        $this->error     = null;
    }

    public function usarEjemplo(string $ejemplo): void
    {
        $this->pregunta = $ejemplo;
    }

    public function seleccionarOpcion(string $opcion): void
    {
        $this->pregunta = $opcion;
        $this->consultar();
    }

    /** Extrae etiquetas [OPT]...[/OPT] del texto de la IA y las devuelve separadas. */
    private function parsearRespuesta(string $texto): array
    {
        $opciones = [];
        preg_match_all('/\[OPT\](.*?)\[\/OPT\]/s', $texto, $matches);

        if (! empty($matches[1])) {
            $opciones    = array_values(array_filter(array_map('trim', $matches[1])));
            $textoLimpio = trim(preg_replace('/\[OPT\].*?\[\/OPT\]/s', '', $texto));
            // Limpiar líneas vacías extra que quedan
            $textoLimpio = preg_replace('/\n{3,}/', "\n\n", $textoLimpio);
        } else {
            $textoLimpio = $texto;
        }

        return [$textoLimpio, $opciones];
    }
}
