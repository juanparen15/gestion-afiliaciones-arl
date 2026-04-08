<?php

namespace App\Filament\Resources\ContratoResource\Pages;

use App\Filament\Resources\ContratoResource;
use App\Models\Contrato;
use App\Models\Dependencia;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListContratos extends ListRecords
{
    protected static string $resource = ContratoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Registro Rápido ──────────────────────────────────────────────
            Actions\Action::make('registro_rapido')
                ->label('Registro Rápido')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->modalHeading('Registro Rápido de Contrato')
                ->modalDescription('Ingresa los datos esenciales. Podrás completar CDP, pólizas, supervisión y más desde el formulario completo.')
                ->modalSubmitActionLabel('Guardar y completar')
                ->modalWidth('3xl')
                ->form([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('vigencia')
                            ->label('Vigencia')
                            ->options(array_combine(
                                array_map('strval', range(date('Y'), 2008)),
                                array_map('strval', range(date('Y'), 2008))
                            ))
                            ->default(strval(date('Y')))
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('numero_contrato')
                            ->label('N° de Contrato')
                            ->placeholder('Ej: 001-2026')
                            ->required()
                            ->columnSpan(2),
                    ]),

                    Forms\Components\Textarea::make('objeto')
                        ->label('Objeto del Contrato')
                        ->placeholder('Describe brevemente el objeto del contrato...')
                        ->rows(3)
                        ->required(),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('tipo_contrato')
                            ->label('Tipo de Contrato')
                            ->options(ContratoResource::getOpcionesTipoContrato())
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options(ContratoResource::getOpcionesEstado())
                            ->default('EN EJECUCION')
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('dependencia_id')
                            ->label('Dependencia')
                            ->options(fn () => Dependencia::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id'))
                            ->required()
                            ->native(false)
                            ->searchable(),
                    ]),

                    Forms\Components\Fieldset::make('Contratista')->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('_tipo_persona')
                                ->label('Tipo')
                                ->options([
                                    'natural'   => 'Persona Natural',
                                    'juridica'  => 'Persona Jurídica',
                                    'consorcio' => 'Consorcio / UT',
                                ])
                                ->default('natural')
                                ->live()
                                ->native(false),

                            Forms\Components\TextInput::make('nombre_persona_natural')
                                ->label('Nombre completo')
                                ->placeholder('Ej: Juan Carlos Pérez González')
                                ->visible(fn (Forms\Get $get) => $get('_tipo_persona') !== 'juridica' && $get('_tipo_persona') !== 'consorcio')
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('cedula')
                                ->label('Cédula')
                                ->placeholder('Sin puntos ni espacios')
                                ->visible(fn (Forms\Get $get) => $get('_tipo_persona') !== 'juridica' && $get('_tipo_persona') !== 'consorcio'),

                            Forms\Components\TextInput::make('nombre_persona_juridica')
                                ->label('Razón Social')
                                ->placeholder('Nombre de la empresa')
                                ->visible(fn (Forms\Get $get) => $get('_tipo_persona') === 'juridica')
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('nit')
                                ->label('NIT')
                                ->placeholder('Sin dígito de verificación')
                                ->visible(fn (Forms\Get $get) => $get('_tipo_persona') === 'juridica'),

                            Forms\Components\TextInput::make('nombre_consorcio')
                                ->label('Nombre del Consorcio / UT')
                                ->visible(fn (Forms\Get $get) => $get('_tipo_persona') === 'consorcio')
                                ->columnSpan(3),
                        ]),
                    ]),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('valor_contrato')
                            ->label('Valor del Contrato ($)')
                            ->prefix('$')
                            ->numeric()
                            ->placeholder('0'),

                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\DatePicker::make('fecha_terminacion')
                            ->label('Fecha de terminación')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ]),
                ])
                ->action(function (array $data, Actions\Action $action) {
                    unset($data['_tipo_persona']);

                    $contrato = Contrato::create($data);

                    Notification::make()
                        ->title('Contrato registrado')
                        ->body("N° {$contrato->numero_contrato} guardado. Completa los datos restantes.")
                        ->success()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('completar')
                                ->label('Completar datos')
                                ->url(ContratoResource::getUrl('edit', ['record' => $contrato]))
                                ->button(),
                        ])
                        ->send();

                    $action->redirect(ContratoResource::getUrl('edit', ['record' => $contrato]));
                }),

            // ── Actualizar estados ───────────────────────────────────────────
            Actions\Action::make('actualizar_estados')
                ->label('Actualizar Estados')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Actualizar estados de contratos')
                ->modalDescription('Se revisarán todos los contratos EN EJECUCION y EN EJECUCION CON ADICION y se actualizará su estado según la fecha de cierre efectiva (incluyendo adiciones y prórrogas).')
                ->modalSubmitActionLabel('Sí, actualizar')
                ->action(function () {
                    Artisan::call('contratos:actualizar-estados');
                    $output = Artisan::output();

                    Notification::make()
                        ->title('Estados actualizados')
                        ->body(trim($output))
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Formulario Completo'),
        ];
    }
}
