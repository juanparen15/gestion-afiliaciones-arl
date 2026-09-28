<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudBpimResource\Pages;
use App\Mail\BpimAprobadoMail;
use App\Mail\BpimRechazadoMail;
use App\Models\ItemCatalogo;
use App\Models\Proyecto;
use App\Models\SolicitudBpim;
use App\Services\BpimDocumentoService;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SolicitudBpimResource extends Resource
{
    protected static ?string $model = SolicitudBpim::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Solicitudes BPIM';
    protected static ?string $modelLabel = 'Solicitud BPIM';
    protected static ?string $pluralModelLabel = 'Solicitudes BPIM';
    protected static ?string $navigationGroup = 'Gestión de BPIM';
    protected static ?int $navigationSort = 30;

    /** Todo el módulo BPIM es exclusivo de super_admin. */
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información Básica')
                ->schema([
                    Forms\Components\TextInput::make('codigo')->label('Código')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('consecutivo')->label('Consecutivo')->maxLength(50),
                    Forms\Components\TextInput::make('dependencia')->label('Dependencia')->required()->maxLength(255),
                    Forms\Components\TextInput::make('cartera')->label('Cartera')->maxLength(100),
                ])->columns(2),

            Forms\Components\Section::make('Información del Solicitante')
                ->schema([
                    Forms\Components\TextInput::make('nombre_solicitante')->label('Nombre del Solicitante')->required()->maxLength(255),
                    Forms\Components\TextInput::make('correo_solicitante')->label('Correo Solicitante')
                        ->email()->required()->maxLength(255)->suffixIcon('heroicon-m-envelope')
                        ->helperText('Debe ser correo institucional @puertoboyaca-boyaca.gov.co'),
                    Forms\Components\TextInput::make('correo_notificacion')->label('Correo para Notificaciones')->email()->maxLength(255),
                ])->columns(2),

            Forms\Components\Section::make('Información del Proyecto')
                ->schema([
                    Forms\Components\Select::make('proyecto_id')
                        ->label('Seleccionar Proyecto')
                        ->options(fn () => Proyecto::orderBy('nombre_proyecto')->get()
                            ->mapWithKeys(fn ($p) => [$p->id => "{$p->nombre_proyecto} - BPIM: {$p->codigo_bpim}"])->toArray())
                        ->searchable()->preload()->required()->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state && ($p = Proyecto::find($state))) {
                                $set('nombre_proyecto', $p->nombre_proyecto);
                                $set('codigo_bpim', $p->codigo_bpim);
                                $set('codigo_bpin', $p->codigo_bpin);
                            }
                        })
                        ->helperText('Autocompleta el nombre del proyecto y los códigos BPIM/BPIN')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('nombre_proyecto')->label('Nombre del Proyecto')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\TextInput::make('codigo_bpim')->label('Código BPIM')->maxLength(100),
                    Forms\Components\TextInput::make('codigo_bpin')->label('Código BPIN')->maxLength(100),
                    Forms\Components\Textarea::make('objeto')->label('Objeto')->required()->rows(3)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Información Financiera')
                ->schema([
                    Forms\Components\TextInput::make('cdp')->label('CDP')->maxLength(100),
                    Forms\Components\TextInput::make('valor_cdp')->label('Valor CDP')->numeric()->prefix('$')->inputMode('decimal'),
                    Forms\Components\TextInput::make('valor_ep')->label('Valor EP')->numeric()->prefix('$')->inputMode('decimal'),
                    Forms\Components\TextInput::make('objeto_gasto')->label('Objeto de Gasto')->maxLength(255),
                    Forms\Components\TextInput::make('fuente_recursos')->label('Fuente de Recursos')->maxLength(255)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Clasificación Presupuestal')
                ->schema([
                    Forms\Components\TextInput::make('mga')->label('MGA')->maxLength(100),
                    Forms\Components\TextInput::make('cpc')->label('CPC')->maxLength(100),
                    Forms\Components\TextInput::make('otra_fuente_recurso')->label('Otra Fuente de Recurso')->maxLength(255)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Plan de Desarrollo Nacional')
                ->schema([
                    Forms\Components\TextInput::make('pdn_sector')->label('Sector PDN')->maxLength(255),
                    Forms\Components\TextInput::make('pdn_programa')->label('Programa PDN')->maxLength(255),
                    Forms\Components\TextInput::make('pdn_subprograma')->label('Subprograma PDN')->maxLength(255),
                ])->columns(2)->collapsible(),

            Forms\Components\Section::make('Plan de Desarrollo Municipal')
                ->schema([
                    Forms\Components\TextInput::make('pdm_sector')->label('Sector PDM')->maxLength(255),
                    Forms\Components\TextInput::make('pdm_programa')->label('Programa PDM')->maxLength(255),
                    Forms\Components\TextInput::make('pdm_producto')->label('Producto PDM')->maxLength(255),
                ])->columns(2)->collapsible(),

            Forms\Components\Section::make('Ítems de Presupuesto')
                ->schema([
                    Forms\Components\Repeater::make('presupuestoItems')
                        ->label('')
                        ->relationship('presupuestoItems')
                        ->schema([
                            Forms\Components\Select::make('item_catalogo_id')
                                ->label('Seleccionar del Catálogo')
                                ->relationship('itemCatalogo', 'nombre')
                                ->searchable()->preload()->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state && ($item = ItemCatalogo::find($state))) {
                                        $set('descripcion', $item->descripcion);
                                        $set('valor_unitario', $item->valor_unitario);
                                        $set('unidad', $item->unidad);
                                    }
                                })
                                ->helperText('Seleccione un ítem para autocompletar descripción, unidad y valor')
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('descripcion')->label('Descripción')->required()->rows(2)->columnSpanFull(),
                            Forms\Components\TextInput::make('valor_unitario')->label('Valor Unitario')->numeric()->prefix('$')->required()->live(onBlur: true),
                            Forms\Components\TextInput::make('unidad')->label('Unidad')->maxLength(50)->placeholder('Ej: Mes, Unidad'),
                            Forms\Components\TextInput::make('cantidad')->label('Cantidad')->numeric()->required()->live(onBlur: true),
                            Forms\Components\TextInput::make('valor_total')->label('Valor Total')->numeric()->prefix('$')->required()->disabled()->dehydrated(),
                        ])
                        ->columns(3)
                        ->orderColumn('orden')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['descripcion'] ?? null)
                        ->addActionLabel('Agregar Ítem de Presupuesto')
                        ->defaultItems(0)
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (is_array($state)) {
                                foreach ($state as $index => $item) {
                                    if (isset($item['valor_unitario'], $item['cantidad'])) {
                                        $set("presupuestoItems.{$index}.valor_total", $item['valor_unitario'] * $item['cantidad']);
                                    }
                                }
                            }
                        }),
                ])
                ->columnSpanFull()
                ->collapsible(),

            Forms\Components\Section::make('Información Adicional')
                ->schema([
                    Forms\Components\TextInput::make('codigo_acta_necesidad')->label('Código Acta de Necesidad')->maxLength(100),
                    Forms\Components\TextInput::make('nombre_elabora')->label('Nombre Quien Elabora')->maxLength(255),
                ])->columns(2)->collapsible(),

            Forms\Components\Section::make('Estado y Control')
                ->schema([
                    Forms\Components\Toggle::make('aprobar')->label('Marcar para Aprobar')->helperText('Active para que quede disponible en la revisión.'),
                    Forms\Components\Toggle::make('check')->label('Verificado'),
                    Forms\Components\Placeholder::make('usuario_aprobo')
                        ->label('Aprobado por')
                        ->content(fn ($record) => $record?->usuario_aprobo ?: '—')
                        ->visible(fn ($record) => (bool) $record?->aprobado),
                    Forms\Components\Textarea::make('motivo_rechazo')->label('Motivo de Rechazo')->rows(3)->columnSpanFull()
                        ->visible(fn ($record) => (bool) $record?->rechazado)->disabled(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable()->sortable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('dependencia')->label('Dependencia')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('nombre_solicitante')->label('Solicitante')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('nombre_proyecto')->label('Proyecto')->searchable()->limit(30)
                    ->tooltip(fn ($record) => $record->nombre_proyecto),
                Tables\Columns\TextColumn::make('valor_cdp')->label('Valor CDP')->money('COP')->sortable(),

                Tables\Columns\IconColumn::make('url_documento_word')->label('Word')->boolean()
                    ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')->falseColor('gray'),

                Tables\Columns\IconColumn::make('url_documento_pdf')->label('PDF')->boolean()
                    ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')->falseColor('gray'),

                Tables\Columns\TextColumn::make('estado')->badge()->color(fn ($record) => $record->estado_color),

                Tables\Columns\TextColumn::make('usuario_aprobo')->label('Aprobado por')->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')->label('Fecha Creación')->dateTime('d/m/Y H:i')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'borrador' => $query->where('aprobar', false)->where('aprobado', false),
                            'pendiente' => $query->where('aprobar', true)->where('aprobado', false)->where('rechazado', false),
                            'aprobado' => $query->where('aprobado', true),
                            'rechazado' => $query->where('rechazado', true),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('generar_word')
                        ->label('Generar Word')
                        ->icon('heroicon-o-document-text')->color('info')
                        ->requiresConfirmation()
                        ->modalDescription('Se generará un documento Word nuevo con los datos actuales de la solicitud.')
                        ->visible(fn ($record) => empty($record->url_documento_word))
                        ->action(function ($record) {
                            try {
                                $r = app(BpimDocumentoService::class)->generarDocumentoWord($record, false);
                                Notification::make()->title($r['success'] ? '¡Word generado!' : 'Documento ya existe')
                                    ->body($r['mensaje'])->success($r['success'])->warning(! $r['success'])->send();
                            } catch (Exception $e) {
                                Notification::make()->title('Error al generar Word')->body($e->getMessage())->danger()->persistent()->send();
                            }
                        }),

                    Tables\Actions\Action::make('regenerar_word')
                        ->label('Regenerar Word')
                        ->icon('heroicon-o-arrow-path')->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('Se eliminará el Word actual (y el PDF, si existe) y se generará uno nuevo. Si estaba aprobada, requerirá nueva aprobación.')
                        ->visible(fn ($record) => ! empty($record->url_documento_word)
                            && (Auth::user()->puede_aprobar_bpim || Auth::user()->hasRole('super_admin')))
                        ->action(function ($record) {
                            try {
                                $r = app(BpimDocumentoService::class)->regenerarWordPorFuncionario($record);
                                Notification::make()->title('¡Word regenerado!')->body($r['mensaje'])->success()->send();
                            } catch (Exception $e) {
                                Notification::make()->title('Error al regenerar Word')->body($e->getMessage())->danger()->persistent()->send();
                            }
                        }),

                    Tables\Actions\Action::make('descargar_word')
                        ->label('Descargar Word')->icon('heroicon-o-arrow-down-tray')->color('gray')
                        ->visible(fn ($record) => ! empty($record->url_documento_word))
                        ->action(fn ($record) => Storage::exists($record->url_documento_word)
                            ? response()->download(Storage::path($record->url_documento_word), 'BPIM_' . $record->codigo . '.docx')
                            : Notification::make()->title('Error')->body('El archivo no existe')->danger()->send()),

                    Tables\Actions\Action::make('aprobar_firmar')
                        ->label('Aprobar y Firmar')
                        ->icon('heroicon-o-check-badge')->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar y firmar la solicitud BPIM')
                        ->modalDescription('Se agregará su firma, se generará el código QR de verificación, se regenerará el Word y se generará el PDF final.')
                        ->visible(fn ($record) => Auth::user()->puede_aprobar_bpim
                            && ! $record->aprobado && ! $record->rechazado && ! empty($record->url_documento_word))
                        ->action(function ($record) {
                            try {
                                $r = app(BpimDocumentoService::class)->aprobarConFirmaYPdf($record);
                                Notification::make()->title('¡Documento aprobado y firmado!')
                                    ->body($r['mensaje'] . "\n\nCódigo de verificación: " . $r['codigo_verificacion'])
                                    ->success()->duration(12000)->send();
                            } catch (Exception $e) {
                                Notification::make()->title('Error al aprobar')->body($e->getMessage())->danger()->persistent()->send();
                            }
                        }),

                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-circle')->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('motivo_rechazo')->label('Motivo del Rechazo')->required()->rows(4)
                                ->placeholder('Explique detalladamente el motivo del rechazo...'),
                        ])
                        ->visible(fn ($record) => Auth::user()->puede_aprobar_bpim
                            && ! $record->rechazado && ! empty($record->url_documento_word))
                        ->action(function ($record, array $data) {
                            $record->rechazarSolicitud($data['motivo_rechazo']);
                            Notification::make()->title('Solicitud rechazada')->warning()->send();
                        }),

                    Tables\Actions\Action::make('enviar_email_aprobacion')
                        ->label('Enviar Email de Aprobación')
                        ->icon('heroicon-o-envelope')->color('info')
                        ->requiresConfirmation()
                        ->modalDescription(fn ($record) => "Se enviará el email con el PDF adjunto a: {$record->correo_solicitante}")
                        ->visible(fn ($record) => $record->aprobado && ! empty($record->url_documento_pdf) && ! $record->aprobado_enviado)
                        ->action(function ($record) {
                            if (static::enviarCorreoAprobacion($record)) {
                                Notification::make()->title('¡Email enviado!')->success()->send();
                            } else {
                                Notification::make()->title('No se pudo enviar el email')->danger()->send();
                            }
                        }),

                    Tables\Actions\Action::make('enviar_email_rechazo')
                        ->label('Enviar Email de Rechazo')
                        ->icon('heroicon-o-envelope')->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription(fn ($record) => "Se enviará el email de rechazo a: {$record->correo_solicitante}")
                        ->visible(fn ($record) => $record->rechazado && ! $record->rechazo_enviado)
                        ->action(function ($record) {
                            if (static::enviarCorreoRechazo($record)) {
                                Notification::make()->title('¡Email enviado!')->success()->send();
                            } else {
                                Notification::make()->title('No se pudo enviar el email')->danger()->send();
                            }
                        }),

                    Tables\Actions\Action::make('descargar_pdf')
                        ->label('Descargar PDF')->icon('heroicon-o-arrow-down-tray')->color('danger')
                        ->visible(fn ($record) => ! empty($record->url_documento_pdf))
                        ->action(fn ($record) => Storage::exists($record->url_documento_pdf)
                            ? response()->download(Storage::path($record->url_documento_pdf), 'BPIM_' . $record->codigo . '.pdf')
                            : Notification::make()->title('Error')->body('El archivo no existe')->danger()->send()),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('generar_word_masivo')
                        ->label('Generar Word (solo faltantes)')
                        ->icon('heroicon-o-document-text')->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $r = app(BpimDocumentoService::class)->generarWordMasivo($records->pluck('id')->toArray());
                            Notification::make()->title('Generación masiva completada')
                                ->body("Generados: {$r['exitosos']} · Omitidos: {$r['omitidos']} · Fallidos: {$r['fallidos']}")
                                ->success($r['fallidos'] === 0)->warning($r['fallidos'] > 0)->send();
                        }),

                    Tables\Actions\BulkAction::make('aprobar_firmar_seleccionadas')
                        ->label('Aprobar y Firmar Seleccionadas')
                        ->icon('heroicon-o-check-badge')->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => Auth::user()->puede_aprobar_bpim)
                        ->action(function ($records) {
                            $validas = $records->filter(fn ($r) => ! $r->aprobado && ! $r->rechazado && ! empty($r->url_documento_word));
                            $r = app(BpimDocumentoService::class)->aprobarMasivoConFirmaYPdf($validas->pluck('id')->toArray());
                            Notification::make()->title('Aprobación masiva completada')
                                ->body("Aprobados: {$r['exitosos']} · Fallidos: {$r['fallidos']}")
                                ->success($r['fallidos'] === 0)->warning($r['fallidos'] > 0)->send();
                        }),

                    Tables\Actions\BulkAction::make('rechazar_seleccionadas')
                        ->label('Rechazar Seleccionadas')
                        ->icon('heroicon-o-x-circle')->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('motivo_rechazo')->label('Motivo del Rechazo (aplica a todas)')->required()->rows(4),
                        ])
                        ->visible(fn () => Auth::user()->puede_aprobar_bpim)
                        ->action(function ($records, array $data) {
                            $validas = $records->filter(fn ($r) => ! $r->rechazado);
                            $r = app(BpimDocumentoService::class)->rechazarMasivo($validas->pluck('id')->toArray(), $data['motivo_rechazo']);
                            Notification::make()->title('Rechazo masivo completado')
                                ->body("Rechazadas: {$r['exitosos']} · Fallidas: {$r['fallidos']}")->warning()->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** Envía el correo de aprobación (con PDF) y registra el resultado. */
    public static function enviarCorreoAprobacion(SolicitudBpim $solicitud): bool
    {
        try {
            Mail::to($solicitud->correo_solicitante)->send(new BpimAprobadoMail($solicitud));
            $solicitud->marcarComoEnviado();
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    /** Envía el correo de rechazo y registra el resultado. */
    public static function enviarCorreoRechazo(SolicitudBpim $solicitud): bool
    {
        try {
            Mail::to($solicitud->correo_solicitante)->send(new BpimRechazadoMail($solicitud));
            $solicitud->update(['rechazo_enviado' => true]);
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSolicitudBpims::route('/'),
            'create' => Pages\CreateSolicitudBpim::route('/create'),
            'view'   => Pages\ViewSolicitudBpim::route('/{record}'),
            'edit'   => Pages\EditSolicitudBpim::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('aprobar', true)->where('aprobado', false)->where('rechazado', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
