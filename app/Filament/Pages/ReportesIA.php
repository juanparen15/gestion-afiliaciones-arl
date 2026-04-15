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
                $this->historial[] = [
                    'rol'   => 'ia',
                    'texto' => $resultado['respuesta'],
                    'hora'  => now()->format('H:i'),
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
}
