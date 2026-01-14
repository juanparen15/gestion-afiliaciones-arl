<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AfiliacionResource\Pages;
use App\Models\Afiliacion;
use App\Models\Dependencia;
use App\Imports\AfiliacionesImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AfiliacionResource extends Resource
{
    protected static ?string $model = Afiliacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Afiliaciones';

    protected static ?string $modelLabel = 'Afiliación';

    protected static ?string $pluralModelLabel = 'Afiliaciones';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Información de la Afiliación')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Datos del Contratista')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Información Personal')
                                    ->schema([
                                        Forms\Components\TextInput::make('nombre_contratista')
                                            ->label('Nombre Completo del Contratista')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Forms\Components\Select::make('tipo_documento')
                                            ->label('Tipo de Documento')
                                            ->options([
                                                'CC' => 'Cédula de Ciudadanía',
                                                'CE' => 'Cédula de Extranjería',
                                                'PP' => 'Pasaporte',
                                                'TI' => 'Tarjeta de Identidad',
                                            ])
                                            ->required()
                                            ->default('CC')
                                            ->native(false),

                                        Forms\Components\TextInput::make('numero_documento')
                                            ->label('Número de Documento')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('fecha_nacimiento')
                                            ->label('Fecha de Nacimiento')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->maxDate(now()->subYears(18)),

                                        Forms\Components\TextInput::make('telefono_contratista')
                                            ->label('Número de Celular')
                                            ->required()
                                            ->tel()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('email_contratista')
                                            ->label('Correo Electrónico')
                                            ->required()
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Dirección de Residencia')
                                    ->schema([
                                        Forms\Components\TextInput::make('direccion_residencia')
                                            ->required()
                                            ->label('Dirección')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('barrio')
                                            ->label('Barrio')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Seguridad Social')
                                    ->schema([
                                        Forms\Components\TextInput::make('eps')
                                            ->label('EPS')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('afp')
                                            ->label('Fondo de Pensiones (AFP)')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Información del Contrato')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Section::make('Datos del Contrato')
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_contrato')
                                            ->label('Número de Contrato')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Select::make('dependencia_id')
                                            ->label('Dependencia / Secretaría')
                                            ->options(Dependencia::all()->pluck('nombre', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->live()
                                            ->disabled(fn() => Auth::user()->hasRole('Dependencia'))
                                            ->afterStateUpdated(function (Forms\Set $set) {
                                                $set('area_id', null);
                                            })
                                            ->default(fn() => Auth::user()?->dependencia_id),

                                        Forms\Components\Select::make('area_id')
                                            ->label('Área')
                                            ->relationship('area', 'nombre', function ($query, Forms\Get $get) {
                                                $dependenciaId = $get('dependencia_id');
                                                if ($dependenciaId) {
                                                    return $query->where('dependencia_id', $dependenciaId)->where('activo', true);
                                                }
                                                return $query->where('activo', true);
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->helperText('Seleccione primero una dependencia')
                                            ->disabled(fn(Forms\Get $get) => !$get('dependencia_id'))
                                            ->default(fn() => Auth::user()?->area_id),

                                        Forms\Components\Textarea::make('objeto_contractual')
                                            ->label('Objeto del Contrato')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('supervisor_contrato')
                                            ->label('Supervisor del Contrato')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Valores y Duración')
                                    ->schema([
                                        Forms\Components\TextInput::make('valor_contrato')
                                            ->label('Valor Total del Contrato')
                                            ->required()
                                            ->prefix('$')
                                            ->inputMode('decimal')
                                            ->mask(\Filament\Support\RawJs::make(<<<'JS'
                                                $money($input, '.', ',', 0)
                                            JS))
                                            ->stripCharacters('.,')
                                            ->dehydrateStateUsing(fn($state) => floatval(str_replace(['.', ','], '', $state ?? 0))),

                                        Forms\Components\TextInput::make('honorarios_mensual')
                                            ->label('Honorarios Mensuales')
                                            ->required()
                                            ->prefix('$')
                                            ->inputMode('decimal')
                                            ->mask(\Filament\Support\RawJs::make(<<<'JS'
                                                $money($input, '.', ',', 0)
                                            JS))
                                            ->stripCharacters('.,')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                // Limpiar el valor de separadores (puntos y comas) para cálculo
                                                $valorLimpio = floatval(str_replace(['.', ','], '', $state ?? 0));

                                                // Calcular IBC como 40% de los honorarios mensuales
                                                if ($valorLimpio > 0) {
                                                    $ibcCalculado = $valorLimpio * 0.40;
                                                    $salarioMinimo = config('constants.salario_minimo_legal', 1750905);

                                                    // Si el IBC calculado es menor al mínimo, usar el mínimo y notificar
                                                    if ($ibcCalculado < $salarioMinimo) {
                                                        // Formatear con separadores de miles antes de asignar
                                                        $set('ibc', number_format($salarioMinimo, 0, ',', '.'));

                                                        // Notificar al usuario del ajuste (solo cuando se ajusta)
                                                        Notification::make()
                                                            ->warning()
                                                            ->title('IBC ajustado al mínimo legal')
                                                            ->body("El IBC calculado (40% de honorarios = $" . number_format($ibcCalculado, 0, ',', '.') . ") era menor al salario mínimo legal vigente ($" . number_format($salarioMinimo, 0, ',', '.') . "). Se ha ajustado automáticamente al mínimo legal.")
                                                            ->duration(8000)
                                                            ->send();
                                                    } else {
                                                        // Si es mayor o igual al mínimo, usar el cálculo del 40% formateado
                                                        $set('ibc', number_format($ibcCalculado, 0, ',', '.'));
                                                    }
                                                }
                                            })
                                            ->dehydrateStateUsing(fn($state) => floatval(str_replace(['.', ','], '', $state ?? 0))),

                                        Forms\Components\TextInput::make('ibc')
                                            ->label('IBC (Ingreso Base de Cotización)')
                                            ->required()
                                            ->prefix('$')
                                            ->inputMode('decimal')
                                            ->mask(\Filament\Support\RawJs::make(<<<'JS'
                                                $money($input, '.', ',', 0)
                                            JS))
                                            ->stripCharacters('.,')
                                            ->helperText('El IBC mínimo debe ser el salario mínimo legal vigente en Colombia ($' . number_format(config('constants.salario_minimo_legal', 1750905), 0, ',', '.') . '). Se calcula como el 40% de los honorarios.')
                                            ->placeholder('Se calculará automáticamente')
                                            ->dehydrateStateUsing(fn($state) => floatval(str_replace(['.', ','], '', $state ?? 0))),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('meses_contrato')
                                                    ->label('Meses')
                                                    ->required()
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                        $fechaInicio = $get('fecha_inicio');
                                                        $meses = intval($state ?? 0);
                                                        $dias = intval($get('dias_contrato') ?? 0);

                                                        if ($fechaInicio) {
                                                            $fechaFin = \Carbon\Carbon::parse($fechaInicio)
                                                                ->addMonths($meses)
                                                                ->addDays($dias)
                                                                ->subDay();
                                                            $set('fecha_fin', $fechaFin->format('Y-m-d'));
                                                        }
                                                    }),

                                                Forms\Components\TextInput::make('dias_contrato')
                                                    ->label('Días')
                                                    ->required()
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                        $fechaInicio = $get('fecha_inicio');
                                                        $meses = intval($get('meses_contrato') ?? 0);
                                                        $dias = intval($state ?? 0);

                                                        if ($fechaInicio) {
                                                            $fechaFin = \Carbon\Carbon::parse($fechaInicio)
                                                                ->addMonths($meses)
                                                                ->addDays($dias)
                                                                ->subDay();
                                                            $set('fecha_fin', $fechaFin->format('Y-m-d'));
                                                        }
                                                    }),
                                            ])
                                            ->columnSpanFull(),

                                        Forms\Components\DatePicker::make('fecha_inicio')
                                            ->label('Fecha de Inicio')
                                            ->required()
                                            ->minDate(now()->addDay())
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $fechaInicio = $state;
                                                $meses = intval($get('meses_contrato') ?? 0);
                                                $dias = intval($get('dias_contrato') ?? 0);

                                                if ($fechaInicio) {
                                                    $fechaFin = \Carbon\Carbon::parse($fechaInicio)
                                                        ->addMonths($meses)
                                                        ->addDays($dias)
                                                        ->subDay();
                                                    $set('fecha_fin', $fechaFin->format('Y-m-d'));
                                                }
                                            }),

                                        Forms\Components\DatePicker::make('fecha_fin')
                                            ->label('Fecha de Finalización')
                                            ->required()
                                            ->minDate(now()->addDay())
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->after('fecha_inicio')
                                            ->helperText('Se calcula automáticamente según fecha de inicio + meses + días (editable)'),

                                        Forms\Components\FileUpload::make('contrato_pdf_o_word')
                                            ->label('Cargar Estudio Previo en PDF o Word')
                                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                            ->maxSize(10240) // 10MB
                                            ->directory('afiliaciones/contratos-pdf-word')
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->required()
                                            ->helperText('Suba el Estudio Previo en formato PDF o Word (máximo 10MB)'),
                                    ])
                                    ->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Información Adicional del Contrato')
                            ->icon('heroicon-o-document-plus')
                            ->schema([
                                Forms\Components\Section::make('Adición al Contrato')
                                    ->description('Complete esta sección si el contrato tiene una adición')
                                    ->schema([
                                        Forms\Components\Toggle::make('tiene_adicion')
                                            ->label('¿El contrato tiene adición?')
                                            ->live()
                                            ->default(false)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('descripcion_adicion')
                                            ->label('Descripción de la Adición')
                                            ->rows(3)
                                            ->visible(fn(Forms\Get $get) => $get('tiene_adicion'))
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('valor_adicion')
                                            ->label('Valor de la Adición')
                                            ->prefix('$')
                                            ->inputMode('decimal')
                                            ->mask(\Filament\Support\RawJs::make(<<<'JS'
                                                $money($input, '.', ',', 0)
                                            JS))
                                            ->stripCharacters('.,')
                                            ->dehydrateStateUsing(fn($state) => floatval(str_replace(['.', ','], '', $state ?? 0)))
                                            ->visible(fn(Forms\Get $get) => $get('tiene_adicion')),

                                        Forms\Components\DatePicker::make('fecha_adicion')
                                            ->label('Fecha de la Adición')
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->visible(fn(Forms\Get $get) => $get('tiene_adicion')),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('Prórroga del Contrato')
                                    ->description('Complete esta sección si el contrato tiene prórroga')
                                    ->schema([
                                        Forms\Components\Toggle::make('tiene_prorroga')
                                            ->label('¿El contrato tiene prórroga?')
                                            ->live()
                                            ->default(false)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('descripcion_prorroga')
                                            ->label('Descripción de la Prórroga')
                                            ->rows(3)
                                            ->visible(fn(Forms\Get $get) => $get('tiene_prorroga'))
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('meses_prorroga')
                                                    ->label('Meses de Prórroga')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0),

                                                Forms\Components\TextInput::make('dias_prorroga')
                                                    ->label('Días de Prórroga')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0),
                                            ])
                                            ->visible(fn(Forms\Get $get) => $get('tiene_prorroga'))
                                            ->columnSpanFull(),

                                        Forms\Components\DatePicker::make('nueva_fecha_fin_prorroga')
                                            ->label('Nueva Fecha de Finalización')
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->visible(fn(Forms\Get $get) => $get('tiene_prorroga')),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('Terminación Anticipada del Contrato')
                                    ->description('Complete esta sección si el contrato se terminó anticipadamente')
                                    ->schema([
                                        Forms\Components\Toggle::make('tiene_terminacion_anticipada')
                                            ->label('¿El contrato tiene terminación anticipada?')
                                            ->live()
                                            ->default(false)
                                            ->columnSpanFull(),

                                        Forms\Components\DatePicker::make('fecha_terminacion_anticipada')
                                            ->label('Fecha de Terminación Anticipada')
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->visible(fn(Forms\Get $get) => $get('tiene_terminacion_anticipada')),

                                        Forms\Components\Textarea::make('motivo_terminacion_anticipada')
                                            ->label('Motivo de la Terminación Anticipada')
                                            ->rows(3)
                                            ->visible(fn(Forms\Get $get) => $get('tiene_terminacion_anticipada'))
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ])
                            ->visible(fn(Forms\Get $get) => Auth::user()->hasRole(['super_admin', 'SSST'])),

                        Forms\Components\Tabs\Tab::make('Información ARL')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Section::make('Datos de la ARL')
                                    ->schema([
                                        Forms\Components\TextInput::make('nombre_arl')
                                            ->label('Nombre de la ARL')
                                            ->required()
                                            ->default('ARL SURA')
                                            ->maxLength(255)
                                            ->live(onBlur: true),

                                        Forms\Components\Textarea::make('observaciones_arl')
                                            ->label('Observaciones sobre la ARL')
                                            ->rows(3)
                                            ->placeholder('Ingrese observaciones adicionales sobre esta ARL...')
                                            ->helperText('Campo para documentar información adicional cuando se usa una ARL diferente a ARL SURA')
                                            ->visible(fn(Forms\Get $get) => $get('nombre_arl') && strtoupper(trim($get('nombre_arl'))) !== 'ARL SURA')
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('tipo_riesgo')
                                            ->label('Nivel de Riesgo')
                                            ->options([
                                                'I' => 'Nivel I - Riesgo Mínimo',
                                                'II' => 'Nivel II - Riesgo Bajo',
                                                'III' => 'Nivel III - Riesgo Medio',
                                                'IV' => 'Nivel IV - Riesgo Alto',
                                                'V' => 'Nivel V - Riesgo Máximo',
                                            ])
                                            ->required()
                                            ->default('I')
                                            ->native(false),

                                        Forms\Components\TextInput::make('numero_afiliacion_arl')
                                            ->label('Número de Afiliación ARL')
                                            // ->visible(fn($record) => $record && $record->estado === 'pendiente')
                                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('fecha_afiliacion_arl')
                                            ->label('Fecha de Afiliación ARL')
                                            ->displayFormat('d/m/Y')
                                            ->minDate(now()->addDay())
                                            // ->visible(fn($record) => $record && $record->estado === 'pendiente')
                                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->native(false),

                                        Forms\Components\DatePicker::make('fecha_terminacion_afiliacion')
                                            ->label('Fecha de Terminación de Afiliación ARL')
                                            ->displayFormat('d/m/Y')
                                            // ->visible(fn($record) => $record && $record->estado === 'pendiente')
                                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->native(false),

                                        Forms\Components\FileUpload::make('pdf_arl')
                                            ->label('PDF del Afiliado en Sistema ARL')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240) // 10MB
                                            ->directory('afiliaciones/pdfs-arl')
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->helperText('PDF generado en el sistema de ARL del afiliado')
                                            ->visible(fn($record) => $record && $record->estado === 'validado')
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Estado y Observaciones')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Forms\Components\Section::make('Estado de la Afiliación')
                                    ->schema([
                                        Forms\Components\Select::make('estado')
                                            ->label('Estado')
                                            ->options([
                                                'pendiente' => 'Pendiente de Validación',
                                                'validado' => 'Validado',
                                                'rechazado' => 'Rechazado',
                                            ])
                                            ->required(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->default('pendiente')
                                            ->native(false)
                                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->disabled(fn() => !Auth::user()->hasRole(['super_admin', 'SSST'])),

                                        Forms\Components\Textarea::make('observaciones')
                                            ->label('Observaciones')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->disabled(fn() => !Auth::user()->hasRole(['super_admin', 'SSST'])),

                                        Forms\Components\Textarea::make('motivo_rechazo')
                                            ->label('Motivo de Rechazo')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->visible(fn($get) => $get('estado') === 'rechazado' && Auth::user()->hasRole(['super_admin', 'SSST']))
                                            ->disabled(fn() => !Auth::user()->hasRole(['super_admin', 'SSST'])),
                                    ])
                                    ->columns(2)
                                    ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST'])),
                            ])
                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST'])),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_contrato')
                    ->label('No. Contrato')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nombre_contratista')
                    ->label('Contratista')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->nombre_contratista),

                Tables\Columns\TextColumn::make('numero_documento')
                    ->label('Documento')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dependencia.nombre')
                    ->label('Dependencia')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->badge(),

                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('Área')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('valor_contrato')
                    ->label('Valor Contrato')
                    ->money('COP')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => $record->fecha_fin <= now() ? 'danger' : ($record->fecha_fin <= now()->addDays(30) ? 'warning' : 'success'))
                    ->icon(fn($record) => $record->fecha_fin <= now()->addDays(30) ? 'heroicon-o-exclamation-triangle' : null),

                Tables\Columns\IconColumn::make('tiene_adicion')
                    ->label('Adición')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable()
                    ->tooltip(fn($record) => $record->tiene_adicion ? 'Tiene Adición' : 'Sin Adición'),

                Tables\Columns\IconColumn::make('tiene_prorroga')
                    ->label('Prórroga')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable()
                    ->tooltip(fn($record) => $record->tiene_prorroga ? 'Tiene Prórroga' : 'Sin Prórroga'),

                Tables\Columns\IconColumn::make('tiene_terminacion_anticipada')
                    ->label('Terminación Anticipada')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable()
                    ->tooltip(fn($record) => $record->tiene_terminacion_anticipada ? 'Terminación Anticipada' : 'Sin Terminación Anticipada'),

                Tables\Columns\TextColumn::make('tipo_riesgo')
                    ->label('Nivel Riesgo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'I' => 'success',
                        'II' => 'info',
                        'III' => 'warning',
                        'IV', 'V' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'validado' => 'success',
                        'rechazado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'validado' => 'Validado',
                        'rechazado' => 'Rechazado',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('pdf_arl')
                    ->label('PDF ARL')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable()
                    ->tooltip(fn($record) => $record->pdf_arl ? 'PDF Cargado' : 'Sin PDF'),

                Tables\Columns\TextColumn::make('creador.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'validado' => 'Validado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('dependencia_id')
                    ->label('Dependencia')
                    ->relationship('dependencia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('tipo_riesgo')
                    ->label('Nivel de Riesgo')
                    ->options([
                        'I' => 'Nivel I',
                        'II' => 'Nivel II',
                        'III' => 'Nivel III',
                        'IV' => 'Nivel IV',
                        'V' => 'Nivel V',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('vigentes')
                    ->label('Contratos Vigentes')
                    ->query(fn(Builder $query): Builder => $query->where('fecha_fin', '>=', now())),

                Tables\Filters\Filter::make('por_vencer')
                    ->label('Por Vencer (30 días)')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('fecha_fin', '>=', now())
                        ->where('fecha_fin', '<=', now()->addDays(30))),

                Tables\Filters\Filter::make('tiene_adicion')
                    ->label('Con Adición')
                    ->query(fn(Builder $query): Builder => $query->where('tiene_adicion', true)),

                Tables\Filters\Filter::make('tiene_prorroga')
                    ->label('Con Prórroga')
                    ->query(fn(Builder $query): Builder => $query->where('tiene_prorroga', true)),

                Tables\Filters\Filter::make('tiene_terminacion_anticipada')
                    ->label('Con Terminación Anticipada')
                    ->query(fn(Builder $query): Builder => $query->where('tiene_terminacion_anticipada', true)),

                Tables\Filters\TrashedFilter::make()
                    ->label('Registros Eliminados')
                    ->native(false)
                    ->placeholder('Sin eliminar')
                    ->trueLabel('Solo eliminados')
                    ->falseLabel('Con eliminados')
                    ->queries(
                        true: fn(Builder $query) => $query->onlyTrashed(),
                        false: fn(Builder $query) => $query->withTrashed(),
                        blank: fn(Builder $query) => $query->withoutTrashed(),
                    ),
            ])
            ->headerActions([
                Action::make('descargar_plantilla')
                    ->label('Descargar Plantilla')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn() => Auth::user()->hasRole('SSST') || Auth::user()->hasRole('super_admin'))
                    ->action(function () {
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\AfiliacionesTemplateExport(),
                            'plantilla_afiliaciones_arl.xlsx'
                        );
                    }),

                Action::make('exportar_todo')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    // ->visible(fn() => Auth::user()->hasRole('SSST'))
                    ->action(function () {
                        $query = \App\Models\Afiliacion::query()->with(['dependencia', 'area']);

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\AfiliacionesExport($query),
                            'afiliaciones_arl_' . date('Y-m-d_H-i-s') . '.xlsx'
                        );
                    }),

                Action::make('importar')
                    ->label('Importar Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->visible(fn() => Auth::user()->hasRole('SSST') || Auth::user()->hasRole('super_admin'))
                    ->form([
                        Forms\Components\FileUpload::make('archivo')
                            ->label('Archivo Excel')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                            ])
                            ->required()
                            ->maxSize(10240)
                            ->disk('local')
                            ->directory('temp-imports')
                            ->visibility('private')
                            ->helperText('Formatos aceptados: .xlsx, .xls, .csv (Máximo 10MB)'),
                    ])
                    ->action(function (array $data): void {
                        // Verificar horario permitido antes de importar
                        $horaActual = \Carbon\Carbon::now();
                        $hora = $horaActual->hour;
                        $minuto = $horaActual->minute;

                        // No permitir importaciones después de las 5:00 PM
                        if ($hora >= 17 || ($hora === 0 && $minuto === 0)) {
                            $horaFormateada = $horaActual->format('h:i A');

                            Notification::make()
                                ->danger()
                                ->title('Importación no disponible')
                                ->body("La importación de afiliaciones no está disponible después de las 5:00 PM. Hora actual: {$horaFormateada}. Por favor, intente nuevamente desde las 12:01 AM del día siguiente. Esta restricción permite contar con un día hábil para registrar la afiliación en el sistema externo de ARL.")
                                ->persistent()
                                ->send();

                            return;
                        }

                        try {
                            // Obtener la ruta completa del archivo usando Storage
                            $filePath = Storage::disk('local')->path($data['archivo']);

                            if (!file_exists($filePath)) {
                                throw new \Exception("No se pudo encontrar el archivo en: {$filePath}");
                            }

                            $import = new AfiliacionesImport();
                            Excel::import($import, $filePath);

                            $failures = $import->failures();

                            if ($failures->count() > 0) {
                                // Preparar datos para el export de errores
                                $erroresDetallados = [];
                                $resumenErrores = [];

                                foreach ($failures as $failure) {
                                    $fila = $failure->row();
                                    $errores = $failure->errors();
                                    $valores = $failure->values();

                                    foreach ($errores as $error) {
                                        // Identificar el campo con error del mensaje
                                        $campo = 'Desconocido';
                                        $accionRequerida = 'Revisar y corregir el dato';

                                        if (str_contains($error, 'número de contrato')) {
                                            $campo = 'no_contrato';
                                            $accionRequerida = 'Ingresar el número de contrato';
                                        } elseif (str_contains($error, 'objeto del contrato')) {
                                            $campo = 'objeto_contrato';
                                            $accionRequerida = 'Ingresar el objeto/descripción del contrato';
                                        } elseif (str_contains($error, 'secretaría') || str_contains($error, 'dependencia')) {
                                            $campo = 'secretaria';
                                            $accionRequerida = 'Ingresar la secretaría o dependencia';
                                        } elseif (str_contains($error, 'valor del contrato')) {
                                            $campo = 'valor_del_contrato';
                                            $accionRequerida = 'Ingresar el valor del contrato (solo números)';
                                        } elseif (str_contains($error, 'cédula')) {
                                            $campo = 'cc_contratista';
                                            $accionRequerida = 'Ingresar el número de cédula del contratista';
                                        } elseif (str_contains($error, 'nombre del contratista')) {
                                            $campo = 'contratista';
                                            $accionRequerida = 'Ingresar el nombre completo del contratista';
                                        } elseif (str_contains($error, 'fecha de inicio')) {
                                            $campo = 'fecha_ingreso_a_partir_de_acta_inicio';
                                            $accionRequerida = 'Ingresar la fecha de inicio del contrato';
                                        } elseif (str_contains($error, 'fecha de retiro') || str_contains($error, 'fecha de fin')) {
                                            $campo = 'fecha_retiro';
                                            $accionRequerida = 'Ingresar la fecha de finalización del contrato';
                                        } elseif (str_contains($error, 'honorarios')) {
                                            $campo = 'honorarios_mensual';
                                            $accionRequerida = 'Ingresar los honorarios mensuales (solo números)';
                                        } elseif (str_contains($error, 'correo')) {
                                            $campo = 'direccion_de_correo_electronica';
                                            $accionRequerida = 'Ingresar un correo electrónico válido';
                                        }

                                        $valorActual = isset($valores[$campo]) && $valores[$campo] !== null && $valores[$campo] !== ''
                                            ? $valores[$campo]
                                            : '(vacío)';

                                        $erroresDetallados[] = [
                                            'Fila Excel' => $fila,
                                            'Campo con Error' => $campo,
                                            'Descripción del Error' => $error,
                                            'Valor Actual' => $valorActual,
                                            'Acción Requerida' => $accionRequerida,
                                        ];

                                        // Agregar al resumen
                                        if (!isset($resumenErrores[$error])) {
                                            $resumenErrores[$error] = 0;
                                        }
                                        $resumenErrores[$error]++;
                                    }
                                }

                                // Guardar errores en sesión para descarga
                                session(['errores_importacion' => $erroresDetallados]);

                                // Crear mensaje detallado
                                $totalErrores = count($erroresDetallados);
                                $filasConError = $failures->count();

                                $mensajeDetallado = "Se encontraron {$totalErrores} errores en {$filasConError} filas.\n\n";
                                $mensajeDetallado .= "Errores más comunes:\n";

                                $contador = 0;
                                foreach (array_slice($resumenErrores, 0, 5, true) as $error => $cantidad) {
                                    $mensajeDetallado .= "• {$error} ({$cantidad} veces)\n";
                                    $contador++;
                                }

                                if (count($resumenErrores) > 5) {
                                    $mensajeDetallado .= "• ...y " . (count($resumenErrores) - 5) . " tipos de errores más\n";
                                }

                                Notification::make()
                                    ->warning()
                                    ->title('Importación completada con errores')
                                    ->body($mensajeDetallado)
                                    ->persistent()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('descargar_errores')
                                            ->button()
                                            ->label('Descargar Reporte de Errores')
                                            ->url(route('descargar-errores-importacion'))
                                            ->openUrlInNewTab(),
                                    ])
                                    ->send();
                            } else {
                                // Obtener estadísticas de la importación
                                $creados = $import->registrosCreados;
                                $actualizados = $import->registrosActualizados;
                                $ajustadosIBC = $import->registrosAjustadosIBC;
                                $total = $creados + $actualizados;

                                $mensaje = "Total procesados: {$total} registros\n";
                                $mensaje .= "• Nuevos creados: {$creados}\n";
                                $mensaje .= "• Actualizados: {$actualizados}";

                                if ($ajustadosIBC > 0) {
                                    $salarioMinimo = config('constants.salario_minimo_legal', 1423500);
                                    $mensaje .= "\n• IBC ajustados al mínimo legal: {$ajustadosIBC}\n";
                                    $mensaje .= "  (El IBC calculado era menor al salario mínimo legal vigente de $" . number_format($salarioMinimo, 0, ',', '.') . ")";
                                }

                                Notification::make()
                                    ->success()
                                    ->title('Importación exitosa')
                                    ->body($mensaje)
                                    ->send();
                            }

                            // Limpiar archivo temporal usando Storage
                            if (isset($data['archivo']) && Storage::disk('local')->exists($data['archivo'])) {
                                Storage::disk('local')->delete($data['archivo']);
                            }
                        } catch (\Exception $e) {
                            // Limpiar archivo temporal en caso de error
                            if (isset($data['archivo']) && Storage::disk('local')->exists($data['archivo'])) {
                                Storage::disk('local')->delete($data['archivo']);
                            }

                            Notification::make()
                                ->danger()
                                ->title('Error en la importación')
                                ->body('Ocurrió un error: ' . $e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),

                Tables\Actions\EditAction::make()
                    ->label('Editar'),

                Action::make('validar')
                    ->label('Validar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('pdf_arl')
                            ->label('PDF del Afiliado en ARL')
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->directory('afiliaciones/pdfs-arl')
                            ->helperText('Suba el PDF generado en el sistema de ARL del afiliado (máximo 10MB)')
                            ->downloadable()
                            ->previewable(),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones (Opcional)')
                            ->rows(3)
                            ->placeholder('Ingrese observaciones adicionales si las hay...')
                    ])
                    ->modalHeading('Validar Afiliación')
                    ->modalDescription('Por favor, suba el PDF del afiliado generado en el sistema de ARL.')
                    ->modalSubmitActionLabel('Validar Afiliación')
                    ->action(function (Afiliacion $record, array $data): void {
                        $record->update([
                            'estado' => 'validado',
                            'validated_by' => Auth::id(),
                            'fecha_validacion' => now(),
                            'motivo_rechazo' => null,
                            'pdf_arl' => $data['pdf_arl'],
                            'observaciones' => $data['observaciones'] ?? null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Afiliación Validada')
                            ->body('La afiliación ha sido validada exitosamente y el PDF ha sido guardado.')
                            ->send();
                    })
                    ->visible(fn(Afiliacion $record) => $record->estado === 'pendiente' && Auth::user()->hasRole(['SSST', 'super_admin'])),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Motivo del Rechazo')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describa el motivo por el cual se rechaza esta afiliación...')
                    ])
                    ->modalHeading('Rechazar Afiliación')
                    ->modalSubmitActionLabel('Rechazar')
                    ->action(function (Afiliacion $record, array $data): void {
                        $record->update([
                            'estado' => 'rechazado',
                            'validated_by' => Auth::id(),
                            'fecha_validacion' => now(),
                            'motivo_rechazo' => $data['motivo_rechazo'],
                        ]);

                        Notification::make()
                            ->warning()
                            ->title('Afiliación Rechazada')
                            ->body('La afiliación ha sido rechazada.')
                            ->send();
                    })
                    ->visible(fn(Afiliacion $record) => $record->estado === 'pendiente' && Auth::user()->hasRole(['SSST', 'super_admin'])),

                Tables\Actions\RestoreAction::make()
                    ->label('Restaurar')
                    ->color('success')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Afiliación Restaurada')
                            ->body('El registro ha sido restaurado exitosamente.')
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar Afiliación')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta afiliación? El registro se marcará como eliminado pero podrás restaurarlo después si es necesario.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Afiliación Eliminada')
                            ->body('El registro ha sido eliminado. Puedes restaurarlo usando el filtro "Registros Eliminados".')
                    ),

                Tables\Actions\ForceDeleteAction::make()
                    ->label('Eliminar Permanentemente')
                    ->modalHeading('Eliminar Permanentemente')
                    ->modalDescription('⚠️ ADVERTENCIA: Esta acción NO se puede deshacer. El registro se eliminará permanentemente de la base de datos y no podrá ser recuperado.')
                    ->modalSubmitActionLabel('Sí, eliminar permanentemente')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Registro Eliminado Permanentemente')
                            ->body('El registro ha sido eliminado de forma permanente y no puede ser recuperado.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Registros Restaurados')
                                ->body('Los registros seleccionados han sido restaurados exitosamente.')
                        ),

                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Registros Eliminados')
                                ->body('Los registros han sido eliminados. Puedes restaurarlos usando el filtro "Registros Eliminados".')
                        ),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Permanentemente')
                        ->modalDescription('⚠️ ADVERTENCIA: Esta acción NO se puede deshacer. Los registros se eliminarán permanentemente de la base de datos.')
                        ->visible(fn() => Auth::user()->hasRole(['super_admin', 'SSST']))
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Registros Eliminados Permanentemente')
                                ->body('Los registros han sido eliminados de forma permanente.')
                        ),

                    ExportBulkAction::make()
                        ->label('Exportar Seleccionados')
                        ->exports([
                            ExcelExport::make()
                                ->withFilename('afiliaciones_' . date('Y-m-d_H-i-s'))
                                ->withColumns([
                                    'No. Contrato' => 'numero_contrato',
                                    'Objeto Contrato' => 'objeto_contractual',
                                    'CC' => 'numero_documento',
                                    'Contratista' => 'nombre_contratista',
                                    'Supervisor del Contrato' => 'supervisor_contrato',
                                    'Valor del Contrato' => 'valor_contrato',
                                    'Meses' => 'meses_contrato',
                                    'Días' => 'dias_contrato',
                                    'Honorarios Mensual' => 'honorarios_mensual',
                                    'IBC' => 'ibc',
                                    'Fecha Inicio' => 'fecha_inicio',
                                    'Fecha Retiro' => 'fecha_fin',
                                    'Tiene Adición' => 'tiene_adicion',
                                    'Descripción Adición' => 'descripcion_adicion',
                                    'Valor Adición' => 'valor_adicion',
                                    'Fecha Adición' => 'fecha_adicion',
                                    'Tiene Prórroga' => 'tiene_prorroga',
                                    'Descripción Prórroga' => 'descripcion_prorroga',
                                    'Meses Prórroga' => 'meses_prorroga',
                                    'Días Prórroga' => 'dias_prorroga',
                                    'Nueva Fecha Fin Prórroga' => 'nueva_fecha_fin_prorroga',
                                    'Tiene Terminación Anticipada' => 'tiene_terminacion_anticipada',
                                    'Fecha Terminación Anticipada' => 'fecha_terminacion_anticipada',
                                    'Motivo Terminación Anticipada' => 'motivo_terminacion_anticipada',
                                    'Secretaría' => 'dependencia.nombre',
                                    'Área' => 'area.nombre',
                                    'Fecha de Nacimiento' => 'fecha_nacimiento',
                                    'Nivel de Riesgo' => 'tipo_riesgo',
                                    'No. Celular' => 'telefono_contratista',
                                    'Barrio' => 'barrio',
                                    'Dirección Residencia' => 'direccion_residencia',
                                    'EPS' => 'eps',
                                    'AFP' => 'afp',
                                    'Correo Electrónico' => 'email_contratista',
                                    'Fecha de Afiliación' => 'fecha_afiliacion_arl',
                                    'Fecha Terminación Afiliación' => 'fecha_terminacion_afiliacion',
                                    'ARL' => 'nombre_arl',
                                    'Estado' => 'estado',
                                ]),
                        ]),
                ]),
            ])
            ->emptyStateHeading('No hay afiliaciones registradas')
            ->emptyStateDescription('Comience creando una nueva afiliación.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAfiliacions::route('/'),
            'create' => Pages\CreateAfiliacion::route('/create'),
            'view' => Pages\ViewAfiliacion::route('/{record}'),
            'edit' => Pages\EditAfiliacion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Aplicar filtro de dependencia/área si no es super_admin o SSST
        if (!Auth::user()?->hasRole(['super_admin', 'SSST'])) {
            // Si el usuario tiene área, filtrar por área
            if (Auth::user()?->area_id) {
                $query->where('area_id', Auth::user()->area_id);
            } else {
                // Si solo tiene dependencia, filtrar por dependencia
                $query->where('dependencia_id', Auth::user()->dependencia_id);
            }
        }

        return $query;
    }
}
