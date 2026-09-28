<?php

namespace App\Filament\Resources\SolicitudBpimResource\Pages;

use App\Filament\Resources\SolicitudBpimResource;
use App\Services\BpimDocumentoService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSolicitudBpims extends ListRecords
{
    protected static string $resource = SolicitudBpimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_word_pendientes')
                ->label('Generar Word Pendientes')
                ->icon('heroicon-o-document-text')->color('info')
                ->requiresConfirmation()
                ->modalDescription('Se generarán documentos Word solo para solicitudes marcadas como "Aprobar" que aún no tengan Word.')
                ->action(function () {
                    $ids = $this->getModel()::where('aprobar', true)->where('aprobado', false)
                        ->whereNull('url_documento_word')->pluck('id')->toArray();

                    if (empty($ids)) {
                        Notification::make()->title('No hay solicitudes pendientes')->warning()->send();
                        return;
                    }

                    $r = app(BpimDocumentoService::class)->generarWordMasivo($ids);
                    Notification::make()->title('Generación completada')
                        ->body("Generados: {$r['exitosos']} · Omitidos: {$r['omitidos']} · Fallidos: {$r['fallidos']}")
                        ->success($r['fallidos'] === 0)->warning($r['fallidos'] > 0)->send();
                })
                ->badge(fn () => $this->getModel()::where('aprobar', true)->where('aprobado', false)
                    ->whereNull('url_documento_word')->count() ?: null)
                ->badgeColor('info'),

            Actions\Action::make('aprobar_firmar_masivo')
                ->label('Aprobar y Firmar Todo')
                ->icon('heroicon-o-check-badge')->color('success')
                ->requiresConfirmation()
                ->modalDescription('Se aprobarán todas las solicitudes pendientes con Word: firma, QR y PDF.')
                ->visible(fn () => Auth::user()->puede_aprobar_bpim)
                ->action(function () {
                    $ids = $this->getModel()::where('aprobar', true)->where('aprobado', false)
                        ->where('rechazado', false)->whereNotNull('url_documento_word')->pluck('id')->toArray();

                    if (empty($ids)) {
                        Notification::make()->title('No hay solicitudes para aprobar')->warning()->send();
                        return;
                    }

                    $r = app(BpimDocumentoService::class)->aprobarMasivoConFirmaYPdf($ids);
                    Notification::make()->title('Aprobación masiva completada')
                        ->body("Aprobados: {$r['exitosos']} · Fallidos: {$r['fallidos']}")
                        ->success($r['fallidos'] === 0)->warning($r['fallidos'] > 0)->send();
                })
                ->badge(fn () => Auth::user()->puede_aprobar_bpim
                    ? ($this->getModel()::where('aprobar', true)->where('aprobado', false)
                        ->where('rechazado', false)->whereNotNull('url_documento_word')->count() ?: null)
                    : null)
                ->badgeColor('success'),

            Actions\CreateAction::make()->label('Nueva Solicitud'),
        ];
    }
}
