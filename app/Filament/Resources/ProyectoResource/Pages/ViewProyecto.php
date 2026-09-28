<?php

namespace App\Filament\Resources\ProyectoResource\Pages;

use App\Filament\Resources\ProyectoResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewProyecto extends ViewRecord
{
    protected static string $resource = ProyectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información Principal del Proyecto')
                    ->schema([
                        Infolists\Components\TextEntry::make('nombre_proyecto')
                            ->label('Nombre del Proyecto')->weight(FontWeight::Bold)->size('lg')->columnSpanFull(),

                        Infolists\Components\TextEntry::make('codigo_bpim')->label('Código BPIM')->copyable()->badge(),
                        Infolists\Components\TextEntry::make('codigo_bpin')->label('Código BPIN')->copyable()->badge(),

                        Infolists\Components\TextEntry::make('estado')->badge()
                            ->color(fn ($record) => $record->estado_badge)
                            ->formatStateUsing(fn ($state) => ucfirst($state)),

                        Infolists\Components\TextEntry::make('descripcion')
                            ->label('Descripción')->columnSpanFull()->placeholder('Sin descripción'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Resumen de Ejecución Presupuestal')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('valor_total')
                                    ->label('Valor Total del Proyecto')->money('COP')->size('lg')
                                    ->weight(FontWeight::Bold)->color('primary'),

                                Infolists\Components\TextEntry::make('valor_ejecutado')
                                    ->label('Valor Ejecutado')->money('COP')->size('lg')
                                    ->weight(FontWeight::Bold)->color('success'),

                                Infolists\Components\TextEntry::make('valor_restante')
                                    ->label('Valor Restante')->money('COP')->size('lg')
                                    ->weight(FontWeight::Bold)->color('warning'),

                                Infolists\Components\TextEntry::make('porcentaje_ejecucion')
                                    ->label('% de Ejecución')->suffix('%')->size('lg')->weight(FontWeight::Bold)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                                    ->color(fn ($state) => match (true) {
                                        $state >= 90 => 'success',
                                        $state >= 70 => 'info',
                                        $state >= 50 => 'warning',
                                        default => 'gray',
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('valor_total_con_ajuste')
                            ->label('Valor Total con Ajuste')->money('COP')
                            ->visible(fn ($record) => $record->valor_total_con_ajuste > 0)
                            ->helperText('Valor ajustado manualmente'),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Capítulos del Proyecto')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('capitulos')
                            ->label('')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('nombre_completo')
                                                    ->label('Nombre del Capítulo')
                                                    ->weight(FontWeight::Bold)->size('md')->columnSpan(3),
                                            ]),

                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('valor_total')
                                                    ->label('Valor Total')->money('COP')
                                                    ->weight(FontWeight::SemiBold)->color('primary'),

                                                Infolists\Components\TextEntry::make('valor_ejecutado')
                                                    ->label('Ejecutado')->money('COP')
                                                    ->weight(FontWeight::SemiBold)->color('success'),

                                                Infolists\Components\TextEntry::make('valor_restante')
                                                    ->label('Restante')->money('COP')
                                                    ->weight(FontWeight::SemiBold)->color('warning'),

                                                Infolists\Components\TextEntry::make('items_count')
                                                    ->label('Cantidad de Ítems')
                                                    ->state(fn ($record) => $record->items->count())->badge(),
                                            ]),

                                        Infolists\Components\RepeatableEntry::make('items')
                                            ->label('Ítems del Capítulo')
                                            ->schema([
                                                Infolists\Components\Grid::make(6)
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('etapa')->label('Etapa')->badge()->placeholder('-'),
                                                        Infolists\Components\TextEntry::make('tipo')->label('Tipo')->badge()->placeholder('-'),
                                                        Infolists\Components\TextEntry::make('anio')->label('Año')->badge(),
                                                        Infolists\Components\TextEntry::make('unidad')->label('Unidad')->badge(),
                                                        Infolists\Components\TextEntry::make('cantidad')->label('Cantidad')
                                                            ->weight(FontWeight::SemiBold)
                                                            ->formatStateUsing(fn ($state) => number_format($state, 2)),
                                                        Infolists\Components\TextEntry::make('valor_unitario')->label('Valor Unitario')->money('COP'),
                                                    ]),

                                                Infolists\Components\TextEntry::make('item')
                                                    ->label('Ítem')->weight(FontWeight::SemiBold)->columnSpanFull(),

                                                Infolists\Components\TextEntry::make('descripcion')
                                                    ->label('Descripción')->columnSpanFull(),

                                                Infolists\Components\TextEntry::make('perfil')
                                                    ->label('Perfil Requerido')->columnSpanFull()
                                                    ->visible(fn ($state) => ! empty($state)),

                                                Infolists\Components\Grid::make(4)
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('valor_total')
                                                            ->label('Valor Total')->money('COP')
                                                            ->weight(FontWeight::SemiBold)->color('primary'),

                                                        Infolists\Components\TextEntry::make('valor_ejecutado')
                                                            ->label('Ejecutado')->money('COP')
                                                            ->weight(FontWeight::SemiBold)->color('success'),

                                                        Infolists\Components\TextEntry::make('valor_restante')
                                                            ->label('Restante')->money('COP')
                                                            ->weight(FontWeight::SemiBold)->color('warning'),

                                                        Infolists\Components\TextEntry::make('cantidad_ejecutada')
                                                            ->label('Cant. Ejecutada')
                                                            ->formatStateUsing(fn ($state) => number_format($state, 2))
                                                            ->suffix(fn ($record) => " {$record->unidad}"),
                                                    ]),
                                            ])
                                            ->contained(false)
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                            ])
                            ->contained(false),
                    ])
                    ->collapsed(false)
                    ->collapsible(),

                Infolists\Components\Section::make('Resumen de Ítems del Catálogo')
                    ->description('Resumen de los ítems del catálogo utilizados en este proyecto')
                    ->schema([
                        Infolists\Components\ViewEntry::make('resumen_items')
                            ->label('')
                            ->view('filament.infolists.proyecto-resumen-items'),
                    ])
                    ->collapsed(true)
                    ->collapsible(),

                Infolists\Components\Section::make('Información de Registro')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')->label('Fecha de Creación')->dateTime('d/m/Y H:i:s'),
                        Infolists\Components\TextEntry::make('updated_at')->label('Última Actualización')->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2)
                    ->collapsed(true)
                    ->collapsible(),
            ]);
    }
}
