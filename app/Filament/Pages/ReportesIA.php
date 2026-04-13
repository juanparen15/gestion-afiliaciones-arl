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
    public ?string $respuesta = null;
    public bool    $cargando  = false;
    public ?string $error     = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }

    public function consultar(): void
    {
        $this->validate(['pregunta' => 'required|min:5|max:500']);

        $this->cargando  = true;
        $this->respuesta = null;
        $this->error     = null;

        try {
            $resultado = app(AIReportService::class)->consultar($this->pregunta);

            if (isset($resultado['error'])) {
                $this->error = $resultado['error'];
            } else {
                $this->respuesta = $resultado['respuesta'];
            }
        } catch (\Throwable $e) {
            $this->error = 'Error inesperado: ' . $e->getMessage();
        } finally {
            $this->cargando = false;
        }
    }

    public function ejemplos(): array
    {
        return [
            '¿Cuántos contratos hay activos este año?',
            '¿Qué dependencia tiene más contratos en ejecución?',
            '¿Cuáles contratos vencen en los próximos 30 días?',
            '¿Cuál es el valor total de contratos por dependencia?',
            '¿Cuántas afiliaciones ARL están próximas a vencer?',
            '¿Quiénes son los 5 contratistas con más contratos?',
        ];
    }

    public function usarEjemplo(string $ejemplo): void
    {
        $this->pregunta = $ejemplo;
    }
}
