<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudBpim;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BpimDocumentosStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    protected function getStats(): array
    {
        $conWord = SolicitudBpim::whereNotNull('url_documento_word')->count();
        $conPdf = SolicitudBpim::whereNotNull('url_documento_pdf')->count();
        $emailsEnviados = SolicitudBpim::where('aprobado_enviado', true)->count();

        $sinWord = SolicitudBpim::where('aprobar', true)->whereNull('url_documento_word')->count();
        $sinPdf = SolicitudBpim::whereNotNull('url_documento_word')->whereNull('url_documento_pdf')->count();
        $sinEmail = SolicitudBpim::where('aprobado', true)->whereNotNull('url_documento_pdf')->where('aprobado_enviado', false)->count();

        return [
            Stat::make('Documentos Word', $conWord)
                ->description($sinWord > 0 ? "{$sinWord} pendientes de generar" : 'Todos generados')
                ->descriptionIcon('heroicon-o-document-text')
                ->color($sinWord > 0 ? 'warning' : 'success'),

            Stat::make('Documentos PDF', $conPdf)
                ->description($sinPdf > 0 ? "{$sinPdf} pendientes de convertir" : 'Todos convertidos')
                ->descriptionIcon('heroicon-o-document-arrow-down')
                ->color($sinPdf > 0 ? 'warning' : 'success'),

            Stat::make('Correos enviados', $emailsEnviados)
                ->description($sinEmail > 0 ? "{$sinEmail} pendientes de enviar" : 'Todos notificados')
                ->descriptionIcon('heroicon-o-envelope')
                ->color($sinEmail > 0 ? 'warning' : 'success'),
        ];
    }
}
