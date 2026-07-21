<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActaNecesidadResource\Pages;
use App\Models\ActaNecesidad;
use App\Models\Area;
use App\Models\ConfiguracionActa;
use App\Models\Dependencia;
use App\Services\ActaNecesidadDocGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ActaNecesidadResource extends Resource
{
    protected static ?string $model = ActaNecesidad::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Actas de Necesidad';
    protected static ?string $modelLabel = 'Acta de Necesidad';
    protected static ?string $pluralModelLabel = 'Actas de Necesidad';
    protected static ?string $navigationGroup = 'Gestión';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la solicitud')
                ->description('Complete la información para la solicitud del acta de necesidad')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('dependencia_id')
                        ->label('Dependencia')
                        ->options(Dependencia::orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()->preload()->native(false)->required()
                        ->default(fn() => Auth::user()->dependencia_id)
                        ->live()
                        ->afterStateUpdated(fn(Forms\Set $set) => $set('area_id', null)),

                    Forms\Components\Select::make('area_id')
                        ->label('Área')
                        ->options(function (Forms\Get $get) {
                            $dep = $get('dependencia_id');
                            return $dep
                                ? Area::where('dependencia_id', $dep)->orderBy('nombre')->pluck('nombre', 'id')
                                : Area::orderBy('nombre')->pluck('nombre', 'id');
                        })
                        ->searchable()->preload()->native(false)
                        ->default(fn() => Auth::user()->area_id),

                    Forms\Components\TextInput::make('nombre_solicitante')
                        ->label('Nombre del Solicitante')->required()->maxLength(255)
                        ->default(fn() => Auth::user()->name),

                    Forms\Components\TextInput::make('email_solicitante')
                        ->label('Correo del Solicitante')->email()->maxLength(255)
                        ->default(fn() => Auth::user()->correo_institucional ?: Auth::user()->email)
                        ->helperText('Se usará para enviar el acta aprobada o el rechazo'),

                    Forms\Components\Textarea::make('objeto_contrato')
                        ->label('Objeto del Contrato')->required()->rows(2)->columnSpanFull(),

                    Forms\Components\TextInput::make('tipo_contrato')->label('Tipo de Contrato')->required(),
                    Forms\Components\TextInput::make('duracion')->label('Duración')->required(),

                    Forms\Components\TextInput::make('modalidad_seleccion')->label('Modalidad de Selección')->required(),

                    Forms\Components\Textarea::make('tipo_solicitud')
                        ->label('Tipo de Solicitud')
                        ->helperText('Especifique si requiere procedimiento de selección, adición, prórroga, cesión, otrosí o modificación')
                        ->rows(2)->columnSpanFull(),

                    Forms\Components\TextInput::make('numero_contrato_convenio')
                        ->label('Número de Contrato o Convenio (según aplique)'),

                    Forms\Components\TextInput::make('presupuesto_oficial')
                        ->label('Presupuesto Oficial')->numeric()->prefix('$')->required(),

                    Forms\Components\TextInput::make('codigo_bpim_bpin')
                        ->label('Línea de Plan de Desarrollo / Código BPIN - BPIM'),

                    Forms\Components\TextInput::make('codigo_paa')
                        ->label('Código Plan Anual de Adquisiciones (SIIPAA)'),

                    Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones (según aplique)')->rows(2)->columnSpanFull(),

                    Forms\Components\TextInput::make('nombre_completo')
                        ->label('Nombre completo de quien avala')->maxLength(255)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consecutivo')
                    ->label('No. Acta')
                    ->formatStateUsing(fn($state) => $state ? '0' . $state : '—')
                    ->sortable()->searchable(),

                Tables\Columns\TextColumn::make('nombre_solicitante')
                    ->label('Solicitante')->searchable()->limit(28),

                Tables\Columns\TextColumn::make('dependencia_texto')
                    ->label('Dependencia')->badge()->color('info')->toggleable(),

                Tables\Columns\TextColumn::make('objeto_contrato')
                    ->label('Objeto')->limit(40)->tooltip(fn($record) => $record->objeto_contrato)->toggleable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')->badge()
                    ->color(fn(string $state) => match ($state) {
                        'pendiente' => 'warning',
                        'aprobado'  => 'success',
                        'rechazado' => 'danger',
                        'anulado'   => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('fecha_solicitud')
                    ->label('Solicitado')->dateTime('d/m/Y H:i')->sortable(),

                Tables\Columns\TextColumn::make('aprobador.name')
                    ->label('Gestionado por')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(['pendiente' => 'Pendiente', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado'])
                    ->native(false),
                Tables\Filters\SelectFilter::make('dependencia_id')
                    ->label('Dependencia')->relationship('dependencia', 'nombre')
                    ->searchable()->preload()->native(false),
            ])
            ->actions([
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar acta de necesidad')
                    ->modalDescription('Se asignará el número de acta, se generará el PDF y se enviará al solicitante por correo.')
                    ->visible(fn(ActaNecesidad $record) => $record->estado === 'pendiente' && Auth::user()->puede_aprobar_actas)
                    ->action(fn(ActaNecesidad $record) => static::aprobar($record)),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Motivo del rechazo')->required()->rows(4)
                            ->placeholder('Describa el motivo por el cual se rechaza la solicitud...'),
                    ])
                    ->modalHeading('Rechazar acta de necesidad')
                    ->visible(fn(ActaNecesidad $record) => $record->estado === 'pendiente' && Auth::user()->puede_aprobar_actas)
                    ->action(fn(ActaNecesidad $record, array $data) => static::rechazar($record, $data['motivo_rechazo'])),

                Action::make('descargar')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->url(fn(ActaNecesidad $record) => $record->pdf_path ? Storage::disk('public')->url($record->pdf_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(ActaNecesidad $record) => $record->estado === 'aprobado' && $record->pdf_path),

                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-no-symbol')->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('motivo_anulacion')
                            ->label('Motivo de la anulación')->required()->rows(3),
                    ])
                    ->modalHeading('Anular acta de necesidad')
                    ->modalDescription('El acta quedará anulada y ya no será válida. Esta acción queda registrada en la auditoría.')
                    ->visible(fn(ActaNecesidad $record) => $record->estado === 'aprobado' && Auth::user()->puede_aprobar_actas)
                    ->action(fn(ActaNecesidad $record, array $data) => static::anular($record, $data['motivo_anulacion'])),

                Tables\Actions\EditAction::make()
                    ->visible(fn(ActaNecesidad $record) => $record->estado === 'pendiente'),
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                Action::make('exportar')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-table-cells')->color('success')
                    ->action(fn() => \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ActasNecesidadExport(static::getEloquentQuery()->with(['dependencia', 'area', 'aprobador'])->orderBy('consecutivo')),
                        'actas_necesidad_' . date('Y-m-d_H-i-s') . '.xlsx'
                    )),

                Action::make('config_firma')
                    ->label('Configuración de firma')
                    ->icon('heroicon-o-pencil-square')->color('gray')
                    ->visible(fn() => Auth::user()->puede_aprobar_actas || Auth::user()->hasRole('super_admin'))
                    ->fillForm(fn() => [
                        'label_alcalde' => ConfiguracionActa::actual()->label_alcalde,
                        'firma_alcalde_path' => ConfiguracionActa::actual()->firma_alcalde_path,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('label_alcalde')
                            ->label('Texto bajo la firma del alcalde')->required()
                            ->default('Vo Bo. Alcalde Municipal'),
                        Forms\Components\FileUpload::make('firma_alcalde_path')
                            ->label('Imagen de la firma del alcalde')
                            ->image()->directory('actas-necesidad/firmas')->disk('public')->visibility('public')
                            ->helperText('PNG con fondo transparente recomendado. Si se deja vacío, se usa la firma por defecto.'),
                    ])
                    ->action(function (array $data) {
                        $cfg = ConfiguracionActa::actual();
                        $cfg->update([
                            'label_alcalde' => $data['label_alcalde'],
                            'firma_alcalde_path' => $data['firma_alcalde_path'] ?? null,
                        ]);
                        Notification::make()->success()->title('Configuración de firma actualizada')->send();
                    }),
            ])
            ->emptyStateHeading('No hay actas de necesidad registradas')
            ->emptyStateIcon('heroicon-o-document-check');
    }

    /** Aprobar: asigna consecutivo, genera PDF, envía correo + notificación. */
    public static function aprobar(ActaNecesidad $record): void
    {
        $consecutivo = ActaNecesidad::siguienteConsecutivo();
        $cfg = ConfiguracionActa::actual();

        $record->consecutivo = $consecutivo;
        $record->estado = 'aprobado';
        $record->aprobado_por = Auth::id();
        $record->fecha_aprobado = now();
        $record->fecha_generado = now();
        $record->asegurarCodigoVerificacion();

        try {
            $pdfRel = app(ActaNecesidadDocGenerator::class)->generarPdf([
                'CODIGO'             => (string) $consecutivo,
                'FECHA_SOLICITADO'   => optional($record->fecha_solicitud)->translatedFormat('d \d\e F \d\e Y') ?? now()->translatedFormat('d \d\e F \d\e Y'),
                'DEPENDENCIA'        => $record->dependencia_texto,
                'AREA'               => $record->area_texto,
                'NOMBRE_SOLICITANTE' => (string) $record->nombre_solicitante,
                'OBJETO'             => (string) $record->objeto_contrato,
                'TIPO_CONTRATO'      => (string) $record->tipo_contrato,
                'DURACION'           => (string) $record->duracion,
                'MODALIDAD'          => (string) $record->modalidad_seleccion,
                'TIPO_SOLICITUD'     => (string) $record->tipo_solicitud,
                'NUMERO_CONTRATO'    => (string) $record->numero_contrato_convenio,
                'PRESUPUESTO'        => number_format((float) $record->presupuesto_oficial, 0, ',', '.'),
                'BPIM_BPIN'          => (string) $record->codigo_bpim_bpin,
                'CODIGO_PAA'         => (string) $record->codigo_paa,
                'OBSERVACIONES'      => (string) $record->observaciones,
                'label_alcalde'      => $cfg->label_alcalde,
                'firma_alcalde_path' => $cfg->firmaAbsoluta(),
                'url_verificacion'   => $record->urlVerificacion(),
            ]);
            $record->pdf_path = $pdfRel;
        } catch (\Throwable $e) {
            Notification::make()->danger()
                ->title('Error al generar el PDF del acta')
                ->body($e->getMessage())
                ->persistent()->send();
            return;
        }

        $record->save();

        // Correo con PDF adjunto
        if ($record->email_solicitante) {
            try {
                Mail::to($record->email_solicitante)->send(new \App\Mail\ActaAprobadaMail($record));
            } catch (\Throwable $e) {
                // no bloquear la aprobación si falla el correo
            }
        }

        // Notificación interna al creador
        if ($record->creador) {
            Notification::make()->success()
                ->title('Acta de necesidad aprobada')
                ->body("Su acta No 0{$consecutivo} fue aprobada y enviada a {$record->email_solicitante}.")
                ->sendToDatabase($record->creador);
        }

        Notification::make()->success()
            ->title('Acta aprobada — No 0' . $consecutivo)
            ->body('El PDF fue generado y enviado al solicitante.')
            ->send();
    }

    /** Rechazar: notifica por correo + interno. */
    public static function rechazar(ActaNecesidad $record, string $motivo): void
    {
        $record->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $motivo,
            'aprobado_por' => Auth::id(),
            'fecha_aprobado' => now(),
        ]);

        if ($record->email_solicitante) {
            try {
                Mail::to($record->email_solicitante)->send(new \App\Mail\ActaRechazadaMail($record));
            } catch (\Throwable $e) {
                // continuar
            }
        }

        if ($record->creador) {
            Notification::make()->warning()
                ->title('Acta de necesidad rechazada')
                ->body("Su solicitud fue rechazada. Motivo: {$motivo}")
                ->sendToDatabase($record->creador);
        }

        Notification::make()->warning()->title('Acta rechazada')->send();
    }

    /** Anular un acta aprobada. */
    public static function anular(ActaNecesidad $record, string $motivo): void
    {
        $record->update([
            'estado' => 'anulado',
            'motivo_anulacion' => $motivo,
            'anulado_por' => Auth::id(),
            'fecha_anulacion' => now(),
        ]);

        if ($record->creador) {
            Notification::make()->warning()
                ->title('Acta de necesidad anulada')
                ->body("El acta No 0{$record->consecutivo} fue anulada. Motivo: {$motivo}")
                ->sendToDatabase($record->creador);
        }

        Notification::make()->warning()->title('Acta anulada')->send();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Aprobadores y super_admin ven todas; el resto solo las de su dependencia/área
        if ($user && ! $user->puede_aprobar_actas && ! $user->hasRole('super_admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id);
                if ($user->dependencia_id) {
                    $q->orWhere('dependencia_id', $user->dependencia_id);
                }
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListActaNecesidads::route('/'),
            'create' => Pages\CreateActaNecesidad::route('/create'),
            'view'   => Pages\ViewActaNecesidad::route('/{record}'),
            'edit'   => Pages\EditActaNecesidad::route('/{record}/edit'),
        ];
    }
}
