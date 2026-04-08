<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContratoResource\Pages;
use App\Helpers\ColombiaApi;
use App\Imports\ContratosImport;
use App\Models\Contrato;
use App\Models\Dependencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ContratoResource extends Resource
{
    protected static ?string $model = Contrato::class;

    protected static ?string $navigationIcon   = 'heroicon-o-document-text';
    protected static ?string $navigationGroup  = 'Contratos';
    protected static ?string $navigationLabel  = 'Contratos SECOP';
    protected static ?string $modelLabel       = 'Contrato';
    protected static ?string $pluralModelLabel = 'Contratos';
    protected static ?int    $navigationSort   = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('SSST'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CATÁLOGOS REUTILIZABLES
    // ─────────────────────────────────────────────────────────────────────

    private static function moneyMask(): RawJs
    {
        return RawJs::make('$money($input, \'.\', \',\', 0)');
    }

    private static function dehydrateMoney(): \Closure
    {
        return fn ($state) => is_numeric($state)
            ? floatval($state)
            : floatval(str_replace(['.', ','], ['', ''], $state ?? 0));
    }

    public static function getOpcionesEstado(): array { return self::opcionesEstado(); }
    public static function getOpcionesTipoContrato(): array { return self::opcionesTipoContrato(); }

    private static function opcionesEstado(): array
    {
        // Valores exactos que usa SECOP II (columna K del Excel)
        return [
            'EN EJECUCION'             => 'En Ejecución',
            'EN EJECUCION CON ADICION' => 'En Ejec. con Adición',
            'TERMINADO'                => 'Terminado',
            'LIQUIDADO'                => 'Liquidado',
            'LIQUIDADO NO CERRADO'     => 'Liquidado No Cerrado',
            'SUSPENDIDO'               => 'Suspendido',
            'RESCINDIDO'               => 'Rescindido',
        ];
    }

    private static function opcionesTipoContrato(): array
    {
        // Valores exactos del SECOP II — columna CN del Excel
        return [
            'C1 Prestación de Servicios Profesionales'   => 'C1 — Prestación de Servicios Profesionales',
            'C2 Prestación de Servicios de Apoyo a la Gestión' => 'C2 — Apoyo a la Gestión',
            'C3 Obra Pública'                            => 'C3 — Obra Pública',
            'C4 Compraventa'                             => 'C4 — Compraventa',
            'C5 Suministro'                              => 'C5 — Suministro',
            'C6 Arrendamiento'                           => 'C6 — Arrendamiento',
            'C7 Consultoría'                             => 'C7 — Consultoría',
            'C8 Interventoría'                           => 'C8 — Interventoría',
            'C9 Interadministrativo'                     => 'C9 — Interadministrativo',
            'C10 Concesión'                              => 'C10 — Concesión',
            'C11 Fiducia'                                => 'C11 — Fiducia',
            'C12 Encargo Fiduciario'                     => 'C12 — Encargo Fiduciario',
            'Comodato'                                   => 'Comodato',
            'NO APLICA'                                  => 'No Aplica',
        ];
    }

    private static function opcionesClase(): array
    {
        // Valores exactos del SECOP II — columna CM del Excel
        return [
            'C1 PRESTACION DE SERVICIOS'  => 'C1 — Prestación de Servicios',
            'C2 CONSULTORIA'              => 'C2 — Consultoría',
            'C3 CONCESION'               => 'C3 — Concesión',
            'C4 OBRA PUBLICA'            => 'C4 — Obra Pública',
            'C5 ENCARGO FIDUCIARIO'      => 'C5 — Encargo Fiduciario',
            'C6 SUMINISTROS'             => 'C6 — Suministros',
            'C7 COMPRAVENTAS'            => 'C7 — Compraventas',
            'C8 FIDUCIA'                 => 'C8 — Fiducia',
            'C9 ARRENDAMIENTOS'          => 'C9 — Arrendamientos',
            'C10 DONACIONES'             => 'C10 — Donaciones',
            'C11 ASOCIACION'             => 'C11 — Asociación',
            'C12 INTERADMINISTRATIVOS'   => 'C12 — Interadministrativos',
            'C13 ACUERDOS MARCO'         => 'C13 — Acuerdos Marco',
            'C14 INTERVENTORIAS'         => 'C14 — Interventorías',
            'COMODATO'                   => 'Comodato',
        ];
    }

    private static function opcionesBancos(): array
    {
        return [
            'BANCOLOMBIA'    => 'Bancolombia',
            'DAVIVIENDA'     => 'Davivienda',
            'BBVA'           => 'BBVA',
            'BANCO BOGOTA'   => 'Banco de Bogotá',
            'BANCO POPULAR'  => 'Banco Popular',
            'AV VILLAS'      => 'AV Villas',
            'BANCO AGRARIO'  => 'Banco Agrario',
            'NEQUI'          => 'Nequi',
            'DAVIPLATA'      => 'Daviplata',
            'SCOTIABANK'     => 'Scotiabank Colpatria',
            'OTRO'           => 'Otro',
        ];
    }

    private static function opcionesAseguradoras(): array
    {
        return [
            'SEGUROS DEL ESTADO' => 'Seguros del Estado',
            'SURA'               => 'SURA',
            'MAPFRE'             => 'MAPFRE',
            'LIBERTY'            => 'Liberty Seguros',
            'ALLIANZ'            => 'Allianz',
            'POSITIVA'           => 'Positiva Compañía de Seguros',
            'QBE'                => 'QBE Seguros',
            'MUNDIAL'            => 'La Mundial',
            'OTRO'               => 'Otra',
        ];
    }

    private static function opcionesVigencias(): array
    {
        $years = range(date('Y'), 2008);
        return array_combine(array_map('strval', $years), array_map('strval', $years));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helper: recalcular fecha_terminacion
    // ─────────────────────────────────────────────────────────────────────
    private static function recalcularFechaTerminacion(Forms\Set $set, Forms\Get $get): void
    {
        $inicio = $get('fecha_inicio');
        $anos   = intval($get('plazo_anos')  ?? 0);
        $meses  = intval($get('plazo_meses') ?? 0);
        $dias   = intval($get('plazo_dias')  ?? 0);

        if ($inicio && ($anos + $meses + $dias > 0)) {
            $fin = Carbon::parse($inicio)
                ->addYears($anos)
                ->addMonths($meses)
                ->addDays($dias)
                ->subDay();
            $set('fecha_terminacion', $fin->format('Y-m-d'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // FORM — WIZARD
    // ─────────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([

                // ══════════════════════════════════════════════════════════
                // PASO 1 — Identificación del Contrato
                // ══════════════════════════════════════════════════════════
                Forms\Components\Wizard\Step::make('Identificación')
                    ->icon('heroicon-o-identification')
                    ->description('Datos generales, modalidad y objeto')
                    ->completedIcon('heroicon-o-check-circle')
                    ->schema([

                        Forms\Components\Section::make('Datos del Contrato')
                            ->icon('heroicon-o-document')
                            ->description('Información básica de identificación SECOP')
                            ->columns(3)
                            ->schema([
                                Forms\Components\Select::make('vigencia')
                                    ->label('Vigencia')
                                    ->options(self::opcionesVigencias())
                                    ->default(strval(date('Y')))
                                    ->required()
                                    ->native(false)
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-calendar'),

                                Forms\Components\TextInput::make('numero_contrato')
                                    ->label('N° Contrato')
                                    ->numeric()
                                    ->prefixIcon('heroicon-o-hashtag'),

                                Forms\Components\TextInput::make('id_contrato_secop')
                                    ->label('ID SECOP')
                                    ->placeholder('CO1.PCCNTR.XXXXXXX')
                                    ->prefixIcon('heroicon-o-link'),

                                Forms\Components\TextInput::make('numero_constancia_secop')
                                    ->label('N° Constancia SECOP')
                                    ->prefixIcon('heroicon-o-document-check'),

                                Forms\Components\Select::make('estado')
                                    ->label('Estado')
                                    ->options(self::opcionesEstado())
                                    ->default('ACTIVO')
                                    ->native(false)
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-flag'),

                                Forms\Components\Select::make('tipo_contrato')
                                    ->label('Tipo Contrato (SECOP)')
                                    ->options(self::opcionesTipoContrato())
                                    ->native(false)
                                    ->searchable()
                                    ->helperText('Código SECOP — columna CN del Excel')
                                    ->prefixIcon('heroicon-o-document-text'),

                                Forms\Components\Select::make('clase')
                                    ->label('Clase (SECOP)')
                                    ->options(self::opcionesClase())
                                    ->native(false)
                                    ->searchable()
                                    ->helperText('Código SECOP — columna CM del Excel')
                                    ->prefixIcon('heroicon-o-tag'),

                                Forms\Components\TextInput::make('modalidad')
                                    ->label('Modalidad')
                                    ->placeholder('Ej: CD-CPS, LIC, SASI, SMC, CMA...')
                                    ->helperText('Código corto de modalidad — columna CC del Excel')
                                    ->prefixIcon('heroicon-o-adjustments-horizontal'),

                                Forms\Components\TextInput::make('misional_apoyo')
                                    ->label('Misional / Apoyo')
                                    ->placeholder('Ej: 2.04 DE APOYO, NO APLICA, MISIONAL')
                                    ->helperText('Columna CP del Excel')
                                    ->prefixIcon('heroicon-o-building-office'),

                                Forms\Components\Select::make('profesional_encargado')
                                    ->label('Profesional Encargado')
                                    ->options(fn () => \App\Models\User::orderBy('name')->pluck('name', 'name')->toArray())
                                    ->searchable()
                                    ->native(false)
                                    ->allowHtml(false)
                                    ->prefixIcon('heroicon-o-user-circle')
                                    ->helperText('Profesional de la entidad que gestiona el contrato')
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Section::make('Dependencia Responsable')
                            ->icon('heroicon-o-building-library')
                            ->description('Dependencia interna que gestiona el contrato')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('dependencia_id')
                                    ->label('Dependencia')
                                    ->relationship('dependencia', 'nombre', fn ($query) => $query->where('activo', true)->orderBy('nombre'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live()
                                    ->prefixIcon('heroicon-o-building-library')
                                    ->placeholder('Seleccione la dependencia...')
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state) {
                                            $dep = Dependencia::find($state);
                                            if ($dep) {
                                                $set('dependencia_contrato', $dep->nombre);
                                            }
                                        }
                                    })
                                    ->helperText('Seleccione para auto-completar el nombre en SECOP'),

                                Forms\Components\TextInput::make('dependencia_contrato')
                                    ->label('Nombre en SECOP')
                                    ->prefixIcon('heroicon-o-pencil-square')
                                    ->placeholder('Se auto-completa al seleccionar la dependencia')
                                    ->helperText('Editable si el nombre difiere en SECOP'),
                            ]),

                        Forms\Components\Section::make('Entidad Contratante')
                            ->icon('heroicon-o-building-office-2')
                            ->description('Datos de la entidad que suscribe el contrato')
                            ->columns(3)
                            ->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('nit_entidad')
                                    ->label('NIT Entidad')
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\TextInput::make('entidad')
                                    ->label('Nombre Entidad')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('unidad_ejecucion')
                                    ->label('Unidad de Ejecución')
                                    ->columnSpan(3),
                            ]),

                        Forms\Components\Section::make('Objeto del Contrato')
                            ->icon('heroicon-o-document-text')
                            ->description('Descripción del objeto a contratar')
                            ->schema([
                                Forms\Components\Textarea::make('objeto')
                                    ->label('Objeto del Contrato')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->placeholder('Describa el objeto del contrato...'),

                                Forms\Components\TextInput::make('link_secop')
                                    ->label('Link SECOP')
                                    ->url()
                                    ->prefixIcon('heroicon-o-arrow-top-right-on-square')
                                    ->columnSpanFull()
                                    ->placeholder('https://www.secop.gov.co/...'),
                            ]),

                        Forms\Components\Section::make('Duración del Contrato')
                            ->icon('heroicon-o-calendar-days')
                            ->description('Defina el plazo y las fechas de ejecución — la fecha de terminación se calcula automáticamente')
                            ->columns(2)
                            ->schema([
                                // ── Plazo ─────────────────────────────────
                                Forms\Components\Grid::make(3)
                                    ->columnSpanFull()
                                    ->schema([
                                        Forms\Components\TextInput::make('plazo_anos')
                                            ->label('Años')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                                                self::recalcularFechaTerminacion($set, $get)
                                            ),

                                        Forms\Components\TextInput::make('plazo_meses')
                                            ->label('Meses')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                                                self::recalcularFechaTerminacion($set, $get)
                                            ),

                                        Forms\Components\TextInput::make('plazo_dias')
                                            ->label('Días')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefixIcon('heroicon-o-clock')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                                                self::recalcularFechaTerminacion($set, $get)
                                            ),
                                    ]),

                                // ── Fechas ────────────────────────────────
                                Forms\Components\DatePicker::make('fecha_contrato')
                                    ->label('Fecha de Suscripción')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-pencil'),

                                Forms\Components\DatePicker::make('fecha_aprobacion')
                                    ->label('Fecha de Aprobación')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-check-circle'),

                                Forms\Components\DatePicker::make('fecha_inicio')
                                    ->label('Fecha de Inicio')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-play')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                                        self::recalcularFechaTerminacion($set, $get)
                                    )
                                    ->helperText('Al ingresar la fecha se calcula la terminación'),

                                Forms\Components\DatePicker::make('fecha_terminacion')
                                    ->label('Fecha de Terminación')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-stop')
                                    ->helperText('Calculada automáticamente (editable)'),
                            ]),
                    ]),

                // ══════════════════════════════════════════════════════════
                // PASO 2 — Contratista
                // ══════════════════════════════════════════════════════════
                Forms\Components\Wizard\Step::make('Contratista')
                    ->icon('heroicon-o-user')
                    ->description('Datos de la persona natural, jurídica o consorcio')
                    ->completedIcon('heroicon-o-check-circle')
                    ->schema([

                        Forms\Components\Section::make('Tipo de Contratista')
                            ->icon('heroicon-o-users')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Select::make('_tipo_persona')
                                    ->label('¿Quién es el contratista?')
                                    ->options([
                                        'natural'   => 'Persona Natural',
                                        'juridica'  => 'Persona Jurídica',
                                        'consorcio' => 'Consorcio / Unión Temporal',
                                    ])
                                    ->default('natural')
                                    ->native(false)
                                    ->live()
                                    ->dehydrated(false)
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->helperText('Seleccione para mostrar los campos correspondientes'),
                            ]),

                        // ── Persona Natural ───────────────────────────────
                        Forms\Components\Section::make('Datos Persona Natural')
                            ->icon('heroicon-o-user')
                            ->description('Información de la persona natural contratista')
                            ->columns(3)
                            ->visible(fn (Forms\Get $get) => $get('_tipo_persona') !== 'juridica' && $get('_tipo_persona') !== 'consorcio')
                            ->schema([
                                Forms\Components\TextInput::make('nombre_persona_natural')
                                    ->label('Nombre Completo')
                                    ->placeholder('Ej: Juan Carlos Pérez González')
                                    ->prefixIcon('heroicon-o-user')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('genero')
                                    ->label('Género')
                                    ->options([
                                        'M'           => 'Masculino',
                                        'F'           => 'Femenino',
                                        'No Definido' => 'No definido',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-user-circle'),

                                Forms\Components\TextInput::make('cedula')
                                    ->label('Cédula')
                                    ->prefixIcon('heroicon-o-identification')
                                    ->extraInputAttributes(['inputmode' => 'numeric']),

                                Forms\Components\TextInput::make('lugar_expedicion_cedula')
                                    ->label('Lugar Expedición CC')
                                    ->prefixIcon('heroicon-o-map-pin'),

                                Forms\Components\DatePicker::make('fecha_expedicion_cedula')
                                    ->label('Fecha Expedición CC')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Forms\Components\Select::make('departamento_nacimiento')
                                    ->label('Departamento de Nacimiento')
                                    ->options(fn () => ColombiaApi::departamentos())
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->prefixIcon('heroicon-o-map')
                                    ->placeholder('Seleccione departamento...')
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('lugar_nacimiento', null)),

                                Forms\Components\Select::make('lugar_nacimiento')
                                    ->label('Municipio de Nacimiento')
                                    ->options(fn (Forms\Get $get) => ColombiaApi::ciudades($get('departamento_nacimiento') ?? ''))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->placeholder('Seleccione municipio...')
                                    ->disabled(fn (Forms\Get $get) => ! $get('departamento_nacimiento'))
                                    ->helperText('Seleccione primero el departamento'),

                                Forms\Components\DatePicker::make('fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Forms\Components\TextInput::make('correo_contratista')
                                    ->label('Correo')
                                    ->email()
                                    ->prefixIcon('heroicon-o-envelope'),

                                Forms\Components\Select::make('perfil')
                                    ->label('Perfil')
                                    ->options([
                                        'PROFESIONAL'   => 'Profesional',
                                        'ESPECIALIZACION'=> 'Con Especialización',
                                        'MAESTRIA'       => 'Con Maestría',
                                        'TECNOLOGO'      => 'Tecnólogo',
                                        'TECNICO'        => 'Técnico',
                                        'BACHILLER'      => 'Bachiller',
                                        'AUXILIAR'       => 'Auxiliar',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-academic-cap'),
                            ]),

                        Forms\Components\Section::make('Formación Académica')
                            ->icon('heroicon-o-academic-cap')
                            ->columns(3)
                            ->collapsed()
                            ->visible(fn (Forms\Get $get) => $get('_tipo_persona') !== 'juridica' && $get('_tipo_persona') !== 'consorcio')
                            ->schema([
                                Forms\Components\TextInput::make('titulo_profesional')
                                    ->label('Título Profesional')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('universidad')
                                    ->label('Universidad'),
                                Forms\Components\TextInput::make('titulo_bachiller')
                                    ->label('Título Bachiller')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('ano_bachiller')
                                    ->label('Año Bachiller')
                                    ->numeric(),
                                Forms\Components\TextInput::make('especializaciones')
                                    ->label('Especializaciones')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('universidad_posgrado')
                                    ->label('Universidad Posgrado'),
                            ]),

                        // ── Persona Jurídica ──────────────────────────────
                        Forms\Components\Section::make('Datos Persona Jurídica')
                            ->icon('heroicon-o-building-office')
                            ->description('Información de la empresa o entidad contratista')
                            ->columns(3)
                            ->visible(fn (Forms\Get $get) => $get('_tipo_persona') === 'juridica')
                            ->schema([
                                Forms\Components\TextInput::make('nombre_persona_juridica')
                                    ->label('Razón Social')
                                    ->prefixIcon('heroicon-o-building-office')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('nit_contratista')
                                    ->label('NIT')
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\TextInput::make('correo_contratista')
                                    ->label('Correo')
                                    ->email()
                                    ->prefixIcon('heroicon-o-envelope'),

                                Forms\Components\TextInput::make('telefono_contratista')
                                    ->label('Teléfono')
                                    ->prefixIcon('heroicon-o-phone'),

                                Forms\Components\TextInput::make('ciudad_contratista')
                                    ->label('Ciudad')
                                    ->prefixIcon('heroicon-o-map-pin'),

                                Forms\Components\TextInput::make('direccion_contratista')
                                    ->label('Dirección')
                                    ->prefixIcon('heroicon-o-home')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('entidad_bancaria')
                                    ->label('Entidad Bancaria')
                                    ->options(self::opcionesBancos())
                                    ->searchable()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-building-library'),

                                Forms\Components\Select::make('tipo_cuenta_bancaria')
                                    ->label('Tipo de Cuenta')
                                    ->options([
                                        'AHORROS'   => 'Ahorros',
                                        'CORRIENTE' => 'Corriente',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-credit-card'),

                                Forms\Components\TextInput::make('numero_cuenta_bancaria')
                                    ->label('N° Cuenta')
                                    ->prefixIcon('heroicon-o-hashtag'),
                            ]),

                        // ── Consorcio / UT ────────────────────────────────
                        Forms\Components\Section::make('Integrantes del Consorcio / UT')
                            ->icon('heroicon-o-user-group')
                            ->columns(3)
                            ->visible(fn (Forms\Get $get) => $get('_tipo_persona') === 'consorcio')
                            ->schema([
                                // Integrante 1
                                Forms\Components\Placeholder::make('_h1')
                                    ->label('Integrante 1')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('integrante_1_consorcio')
                                    ->label('Nombre')->columnSpan(2),
                                Forms\Components\TextInput::make('participacion_1')
                                    ->label('% Participación'),
                                Forms\Components\TextInput::make('doc_integrante_1')
                                    ->label('Documento'),
                                Forms\Components\TextInput::make('direccion_integrante_1')
                                    ->label('Dirección')->columnSpan(2),

                                // Integrante 2
                                Forms\Components\Placeholder::make('_h2')
                                    ->label('Integrante 2')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('integrante_2_consorcio')
                                    ->label('Nombre')->columnSpan(2),
                                Forms\Components\TextInput::make('participacion_2')
                                    ->label('% Participación'),
                                Forms\Components\TextInput::make('doc_integrante_2')
                                    ->label('Documento'),
                                Forms\Components\TextInput::make('direccion_integrante_2')
                                    ->label('Dirección')->columnSpan(2),

                                // Integrante 3
                                Forms\Components\Placeholder::make('_h3')
                                    ->label('Integrante 3 (opcional)')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('integrante_3_consorcio')
                                    ->label('Nombre')->columnSpan(2),
                                Forms\Components\TextInput::make('participacion_3')
                                    ->label('% Participación'),
                                Forms\Components\TextInput::make('doc_integrante_3')
                                    ->label('Documento'),
                                Forms\Components\TextInput::make('direccion_integrante_3')
                                    ->label('Dirección')->columnSpan(2),
                            ]),
                    ]),

                // ══════════════════════════════════════════════════════════
                // PASO 3 — Financiero
                // ══════════════════════════════════════════════════════════
                Forms\Components\Wizard\Step::make('Financiero')
                    ->icon('heroicon-o-currency-dollar')
                    ->description('Valores, CDP, CRP, adiciones y anticipo')
                    ->completedIcon('heroicon-o-check-circle')
                    ->schema([

                        Forms\Components\Section::make('Valor del Contrato')
                            ->icon('heroicon-o-banknotes')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('valor_contrato')
                                    ->label('Valor Contrato')
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->mask(self::moneyMask())
                                    ->stripCharacters('.,')
                                    ->dehydrateStateUsing(self::dehydrateMoney())
                                    ->placeholder('0')
                                    ->columnSpan(2),

                                Forms\Components\DatePicker::make('fecha_contrato')
                                    ->label('Fecha del Contrato')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),

                        Forms\Components\Section::make('CDP')
                            ->icon('heroicon-o-document-currency-dollar')
                            ->description('Certificado de Disponibilidad Presupuestal')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('numero_cdp')
                                    ->label('N° CDP')
                                    ->prefixIcon('heroicon-o-hashtag'),

                                Forms\Components\DatePicker::make('fecha_cdp')
                                    ->label('Fecha CDP')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),

                                Forms\Components\TextInput::make('valor_cdp')
                                    ->label('Valor CDP')
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->mask(self::moneyMask())
                                    ->stripCharacters('.,')
                                    ->dehydrateStateUsing(self::dehydrateMoney())
                                    ->placeholder('0'),
                            ]),

                        Forms\Components\Section::make('CRP')
                            ->icon('heroicon-o-document-check')
                            ->description('Certificado de Registro Presupuestal')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('numero_crp')
                                    ->label('N° CRP')
                                    ->prefixIcon('heroicon-o-hashtag'),

                                Forms\Components\DatePicker::make('fecha_crp')
                                    ->label('Fecha CRP')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),

                                Forms\Components\TextInput::make('valor_crp')
                                    ->label('Valor CRP')
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->mask(self::moneyMask())
                                    ->stripCharacters('.,')
                                    ->dehydrateStateUsing(self::dehydrateMoney())
                                    ->placeholder('0'),
                            ]),

                        Forms\Components\Section::make('Adiciones al Contrato')
                            ->icon('heroicon-o-plus-circle')
                            ->collapsed()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Placeholder::make('_ad1')->label('Adición 1')->columnSpanFull(),
                                Forms\Components\TextInput::make('valor_adicional_1')->label('Valor')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\DatePicker::make('fecha_adicional_1')->label('Fecha')
                                    ->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('plazo_dias_adicional_1')->label('Días adicionales')->numeric(),

                                Forms\Components\Placeholder::make('_ad2')->label('Adición 2')->columnSpanFull(),
                                Forms\Components\TextInput::make('valor_adicional_2')->label('Valor')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\DatePicker::make('fecha_adicional_2')->label('Fecha')
                                    ->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('plazo_dias_adicional_2')->label('Días adicionales')->numeric(),

                                Forms\Components\Placeholder::make('_ad3')->label('Adición 3')->columnSpanFull(),
                                Forms\Components\TextInput::make('valor_adicional_3')->label('Valor')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\DatePicker::make('fecha_adicional_3')->label('Fecha')
                                    ->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('plazo_dias_adicional_3')->label('Días adicionales')->numeric(),
                            ]),

                        Forms\Components\Fieldset::make('Prórrogas')
                            ->columns(4)
                            ->schema([
                                Forms\Components\Placeholder::make('_pr1')->label('Prórroga 1')->columnSpanFull(),
                                Forms\Components\DatePicker::make('fecha_prorroga_1')->label('Fecha')
                                    ->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('plazo_anos_prorroga_1')->label('Años')->numeric()->minValue(0),
                                Forms\Components\TextInput::make('plazo_meses_prorroga_1')->label('Meses')->numeric()->minValue(0),
                                Forms\Components\TextInput::make('plazo_dias_prorroga_1')->label('Días')->numeric()->minValue(0),

                                Forms\Components\Placeholder::make('_pr2')->label('Prórroga 2')->columnSpanFull(),
                                Forms\Components\DatePicker::make('fecha_prorroga_2')->label('Fecha')
                                    ->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('plazo_anos_prorroga_2')->label('Años')->numeric()->minValue(0),
                                Forms\Components\TextInput::make('plazo_meses_prorroga_2')->label('Meses')->numeric()->minValue(0),
                                Forms\Components\TextInput::make('plazo_dias_prorroga_2')->label('Días')->numeric()->minValue(0),

                                Forms\Components\Placeholder::make('_pr3')->label('Prórroga 3')->columnSpanFull(),
                                Forms\Components\DatePicker::make('fecha_prorroga_3')->label('Fecha')
                                    ->displayFormat('d/m/Y')->native(false),
                                Forms\Components\TextInput::make('plazo_anos_prorroga_3')->label('Años')->numeric()->minValue(0),
                                Forms\Components\TextInput::make('plazo_meses_prorroga_3')->label('Meses')->numeric()->minValue(0),
                                Forms\Components\TextInput::make('plazo_dias_prorroga_3')->label('Días')->numeric()->minValue(0),
                            ]),

                        Forms\Components\Section::make('Anticipo')
                            ->icon('heroicon-o-arrow-up-circle')
                            ->collapsed()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Toggle::make('tiene_anticipo')
                                    ->label('¿Tiene anticipo?')
                                    ->live()
                                    ->onColor('success')
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('tipo_anticipo')
                                    ->label('Tipo Anticipo')
                                    ->options([
                                        'ANTICIPO'          => 'Anticipo',
                                        'PAGO_ANTICIPADO'   => 'Pago Anticipado',
                                    ])
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('tiene_anticipo')),

                                Forms\Components\TextInput::make('porcentaje_anticipo')
                                    ->label('% Anticipo')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->visible(fn (Forms\Get $get) => $get('tiene_anticipo')),

                                Forms\Components\TextInput::make('valor_anticipo')
                                    ->label('Valor Anticipo')
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->mask(self::moneyMask())
                                    ->stripCharacters('.,')
                                    ->dehydrateStateUsing(self::dehydrateMoney())
                                    ->placeholder('0')
                                    ->visible(fn (Forms\Get $get) => $get('tiene_anticipo')),

                                Forms\Components\DatePicker::make('fecha_anticipo')
                                    ->label('Fecha Anticipo')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('tiene_anticipo')),
                            ]),

                        Forms\Components\Section::make('Fuentes de Recursos')
                            ->icon('heroicon-o-building-library')
                            ->collapsed()
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('recursos_sgp')->label('SGP')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\TextInput::make('recursos_sgr')->label('SGR')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\TextInput::make('recursos_pgn')->label('PGN')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\TextInput::make('otros_recursos')->label('Otros')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                                Forms\Components\TextInput::make('fuente_recurso')
                                    ->label('Fuente Recurso')
                                    ->placeholder('Ej: RECURSOS PROPIOS, SGR, SGP SALUD PUBLICA...')
                                    ->helperText('Columna GM del Excel'),
                                Forms\Components\Select::make('fuente_financiacion')
                                    ->label('Fuente Financiación')
                                    ->options([
                                        'FUNCIONAMIENTO' => 'Funcionamiento',
                                        'INVERSION'      => 'Inversión',
                                    ])
                                    ->native(false)
                                    ->helperText('Columna GN del Excel'),
                                Forms\Components\TextInput::make('codigo_rubro')->label('Código Rubro'),
                                Forms\Components\TextInput::make('nombre_rubro')->label('Nombre Rubro')->columnSpan(2),
                                Forms\Components\TextInput::make('valor_rubro')->label('Valor Rubro')
                                    ->prefix('$')->inputMode('decimal')->mask(self::moneyMask())
                                    ->stripCharacters('.,')->dehydrateStateUsing(self::dehydrateMoney())->placeholder('0'),
                            ]),
                    ]),

                // ══════════════════════════════════════════════════════════
                // PASO 4 — Supervisión & Pólizas
                // ══════════════════════════════════════════════════════════
                Forms\Components\Wizard\Step::make('Supervisión & Pólizas')
                    ->icon('heroicon-o-shield-check')
                    ->description('Supervisor designado y garantías del contrato')
                    ->completedIcon('heroicon-o-check-circle')
                    ->schema([

                        Forms\Components\Section::make('Supervisor')
                            ->icon('heroicon-o-user-circle')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('nombre_supervisor')
                                    ->label('Nombre Supervisor')
                                    ->prefixIcon('heroicon-o-user')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('identificacion_supervisor')
                                    ->label('Identificación')
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\TextInput::make('cargo_supervisor')
                                    ->label('Cargo')
                                    ->prefixIcon('heroicon-o-briefcase')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('tipo_supervision')
                                    ->label('Área de Supervisión')
                                    ->placeholder('Ej: GENERAL, PLANEACION, HACIENDA, SISTEMAS...')
                                    ->helperText('Oficina/área designada — columna FC del Excel')
                                    ->prefixIcon('heroicon-o-eye'),

                                Forms\Components\Select::make('tipo_vinculacion_supervisor')
                                    ->label('Tipo Vinculación')
                                    ->options([
                                        'INTERNO'      => 'Interno',
                                        'PLANTA'       => 'Planta',
                                        'PROVISIONAL'  => 'Provisional',
                                        'ENCARGO'      => 'Encargo',
                                        'CONTRATISTA'  => 'Contratista',
                                        'EXTERNO'      => 'Externo',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-link'),

                                Forms\Components\TextInput::make('oficina_supervisor')
                                    ->label('Oficina')
                                    ->prefixIcon('heroicon-o-building-office'),

                                Forms\Components\DatePicker::make('fecha_designacion_supervision')
                                    ->label('Fecha Designación')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),

                        Forms\Components\Section::make('Interventoría')
                            ->icon('heroicon-o-magnifying-glass')
                            ->columns(2)
                            ->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('cto_interventoria')
                                    ->label('Contrato Interventoría')
                                    ->prefixIcon('heroicon-o-document'),

                                Forms\Components\TextInput::make('nombre_interventor')
                                    ->label('Nombre Interventor')
                                    ->prefixIcon('heroicon-o-user'),

                                Forms\Components\TextInput::make('documento_interventor')
                                    ->label('Documento Interventor')
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\TextInput::make('direccion_interventor')
                                    ->label('Dirección')
                                    ->prefixIcon('heroicon-o-home'),
                            ]),

                        Forms\Components\Section::make('Pólizas')
                            ->icon('heroicon-o-shield-exclamation')
                            ->collapsed()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Select::make('compannia_aseguradora')
                                    ->label('Compañía Aseguradora')
                                    ->options(self::opcionesAseguradoras())
                                    ->searchable()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-building-office-2')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('nit_aseguradora')
                                    ->label('NIT Aseguradora')
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\TextInput::make('poliza_cumplimiento')
                                    ->label('Póliza Cumplimiento')
                                    ->prefixIcon('heroicon-o-document-check'),

                                Forms\Components\DatePicker::make('fecha_aprobacion_poliza')
                                    ->label('Fecha Aprobación')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Forms\Components\DatePicker::make('fecha_expedicion_poliza_cumplimiento')
                                    ->label('Fecha Expedición Cumplimiento')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Forms\Components\TextInput::make('vigencia_cumplimiento')
                                    ->label('Vigencia Cumplimiento'),

                                Forms\Components\TextInput::make('poliza_responsabilidad')
                                    ->label('Póliza Responsabilidad')
                                    ->prefixIcon('heroicon-o-document-check'),

                                Forms\Components\DatePicker::make('fecha_expedicion_poliza_responsabilidad')
                                    ->label('Fecha Expedición Resp.')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Forms\Components\TextInput::make('vigencia_responsabilidad')
                                    ->label('Vigencia Responsabilidad'),
                            ]),
                    ]),

                // ══════════════════════════════════════════════════════════
                // PASO 5 — Liquidación & Cierre
                // ══════════════════════════════════════════════════════════
                Forms\Components\Wizard\Step::make('Liquidación & Cierre')
                    ->icon('heroicon-o-check-badge')
                    ->description('Acta final, liquidación y observaciones')
                    ->completedIcon('heroicon-o-check-circle')
                    ->schema([

                        Forms\Components\Section::make('Acta de Liquidación')
                            ->icon('heroicon-o-document-check')
                            ->columns(3)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_acta_recibo_final')
                                    ->label('Acta Recibo Final')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-inbox-arrow-down'),

                                Forms\Components\DatePicker::make('fecha_acta_liquidacion')
                                    ->label('Acta Liquidación')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-document-check'),

                                Forms\Components\TextInput::make('valor_acta_liquidacion')
                                    ->label('Valor Liquidación')
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->mask(self::moneyMask())
                                    ->stripCharacters('.,')
                                    ->dehydrateStateUsing(self::dehydrateMoney())
                                    ->placeholder('0'),

                                Forms\Components\DatePicker::make('fecha_reversion_saldo')
                                    ->label('Fecha Reversión Saldo')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-arrow-path'),

                                Forms\Components\TextInput::make('valor_reversion')
                                    ->label('Valor Reversión')
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->mask(self::moneyMask())
                                    ->stripCharacters('.,')
                                    ->dehydrateStateUsing(self::dehydrateMoney())
                                    ->placeholder('0'),
                            ]),

                        Forms\Components\Section::make('Observaciones y Funciones')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Textarea::make('funciones')
                                    ->label('Funciones / Actividades')
                                    ->rows(3)
                                    ->placeholder('Describa las funciones o actividades a desarrollar...'),

                                Forms\Components\Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->rows(4)
                                    ->placeholder('Observaciones generales del contrato...'),
                            ]),
                    ]),

            ])
                ->skippable(true)
                ->persistStepInQueryString('paso')
                ->submitAction(new \Illuminate\Support\HtmlString(
                    '<button type="submit" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">'
                    . '<span class="fi-btn-label">Guardar Contrato</span></button>'
                ))
                ->columnSpanFull(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // --- Identificación ---
                Tables\Columns\TextColumn::make('numero_contrato')
                    ->label('N° Contrato')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('N° copiado')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),

                Tables\Columns\TextColumn::make('id_contrato_secop')
                    ->label('ID SECOP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('ID copiado')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('numero_constancia_secop')
                    ->label('N° Constancia')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Constancia copiada')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('vigencia')
                    ->label('Vigencia')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'EN EJECUCION', 'ACTIVO'                    => 'success',
                        'EN EJECUCION CON ADICION'                  => 'warning',
                        'LIQUIDADO'                                  => 'gray',
                        'LIQUIDADO NO CERRADO', 'LIQUIDADO_NO_CERRADO' => 'info',
                        'SUSPENDIDO'                                 => 'warning',
                        'TERMINADO', 'RESCINDIDO'                    => 'danger',
                        default                                      => 'primary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'EN EJECUCION'             => 'En Ejecución',
                        'EN EJECUCION CON ADICION' => 'Con Adición',
                        'LIQUIDADO NO CERRADO'     => 'Liq. No Cerrado',
                        default                    => ucwords(strtolower((string) $state)),
                    }),

                // --- Contratista ---
                Tables\Columns\TextColumn::make('nombre_persona_natural')
                    ->label('Contratista')
                    ->getStateUsing(fn ($record) =>
                        $record->nombre_persona_natural
                        ?? $record->nombre_persona_juridica
                        ?? $record->integrante_1_consorcio
                        ?? '—'
                    )
                    ->searchable(['nombre_persona_natural', 'nombre_persona_juridica'])
                    ->limit(30)
                    ->tooltip(fn ($record) =>
                        $record->nombre_persona_natural
                        ?? $record->nombre_persona_juridica
                        ?? $record->integrante_1_consorcio
                    )
                    ->description(fn ($record) =>
                        $record->cedula
                        ? 'C.C. ' . $record->cedula
                        : ($record->nit_contratista ? 'NIT ' . $record->nit_contratista : null)
                    ),

                // --- Objeto ---
                Tables\Columns\TextColumn::make('objeto')
                    ->label('Objeto')
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->objeto)
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                // --- Dependencia ---
                Tables\Columns\TextColumn::make('dependencia_contrato')
                    ->label('Dependencia')
                    ->getStateUsing(fn ($record) =>
                        $record->dependencia?->nombre ?? $record->dependencia_contrato ?? '—'
                    )
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) =>
                        $record->dependencia?->nombre ?? $record->dependencia_contrato
                    ),

                // --- Financiero ---
                Tables\Columns\TextColumn::make('valor_contrato')
                    ->label('Valor')
                    ->money('COP', locale: 'es_CO')
                    ->sortable()
                    ->alignment(\Filament\Support\Enums\Alignment::End),

                // --- Fechas ---
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_terminacion')
                    ->label('Terminación')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record): ?string => match (true) {
                        $record->fecha_terminacion === null                                    => null,
                        $record->fecha_terminacion->isPast()                                  => 'danger',
                        $record->fecha_terminacion->lte(now()->addDays(30))                   => 'warning',
                        default                                                               => 'success',
                    })
                    ->icon(fn ($record): ?string => match (true) {
                        $record->fecha_terminacion !== null && $record->fecha_terminacion->isPast()                  => 'heroicon-o-x-circle',
                        $record->fecha_terminacion !== null && $record->fecha_terminacion->lte(now()->addDays(30))   => 'heroicon-o-exclamation-triangle',
                        default                                                                                      => null,
                    }),

                // --- SECOP link ---
                Tables\Columns\IconColumn::make('link_secop')
                    ->label('SECOP')
                    ->icon(fn ($state) => $state ? 'heroicon-o-arrow-top-right-on-square' : 'heroicon-o-minus')
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->url(fn ($record) => $record->link_secop ?: null, true)
                    ->tooltip('Ver en SECOP'),

                // --- Toggleables ---
                Tables\Columns\TextColumn::make('tipo_contrato')
                    ->label('Tipo contrato')
                    ->badge()
                    ->color('indigo')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('modalidad')
                    ->label('Modalidad')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('numero_cdp')
                    ->label('N° CDP')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nombre_supervisor')
                    ->label('Supervisor')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->nombre_supervisor)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('profesional_encargado')
                    ->label('Profesional')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('vigencia', 'desc')
            ->filters([

                // ── Fila 1: Lo más usado ─────────────────────────────────────
                SelectFilter::make('vigencia')
                    ->label('Año / Vigencia')
                    ->options(self::opcionesVigencias())
                    ->native(false),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(self::opcionesEstado())
                    ->multiple()
                    ->native(false),

                SelectFilter::make('dependencia_id')
                    ->label('Dependencia ejecutora')
                    ->relationship('dependencia', 'nombre', fn ($query) => $query->where('activo', true)->orderBy('nombre'))
                    ->searchable()
                    ->preload()
                    ->native(false),

                // ── Fila 2: Clasificación ────────────────────────────────────
                SelectFilter::make('tipo_contrato')
                    ->label('Tipo de contrato')
                    ->options(self::opcionesTipoContrato())
                    ->multiple()
                    ->native(false)
                    ->searchable(),

                SelectFilter::make('clase')
                    ->label('Clase')
                    ->options(self::opcionesClase())
                    ->multiple()
                    ->native(false)
                    ->searchable(),

                SelectFilter::make('profesional_encargado')
                    ->label('Responsable / Profesional')
                    ->options(fn () => \App\Models\User::orderBy('name')->pluck('name', 'name')->toArray())
                    ->searchable()
                    ->native(false),

                // ── Fila 3: Rango de vencimiento + toggle rápido ─────────────
                Filter::make('fecha_terminacion')
                    ->label('Vencimiento del contrato')
                    ->columnSpan(2)
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Vence desde')->native(false)->displayFormat('d/m/Y')->columns(1),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Vence hasta')->native(false)->displayFormat('d/m/Y')->columns(1),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['desde'] ?? null, fn ($query, $v) => $query->whereDate('fecha_terminacion', '>=', $v))
                        ->when($data['hasta'] ?? null, fn ($query, $v) => $query->whereDate('fecha_terminacion', '<=', $v))
                    )
                    ->indicateUsing(fn (array $data): array => array_filter([
                        ($data['desde'] ?? null) ? 'Vence desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y') : null,
                        ($data['hasta'] ?? null) ? 'Vence hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y') : null,
                    ])),

                Filter::make('por_vencer')
                    ->label('Solo por vencer (30 días)')
                    ->query(fn (Builder $query) => $query->porVencer(30)),

                // ── Fila 4: Rango de inicio + toggle adiciones ───────────────
                Filter::make('fecha_inicio')
                    ->label('Inicio del contrato')
                    ->columnSpan(2)
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Inicia desde')->native(false)->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Inicia hasta')->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['desde'] ?? null, fn ($query, $v) => $query->whereDate('fecha_inicio', '>=', $v))
                        ->when($data['hasta'] ?? null, fn ($query, $v) => $query->whereDate('fecha_inicio', '<=', $v))
                    )
                    ->indicateUsing(fn (array $data): array => array_filter([
                        ($data['desde'] ?? null) ? 'Inicio desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y') : null,
                        ($data['hasta'] ?? null) ? 'Inicio hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y') : null,
                    ])),

                Filter::make('tiene_adiciones')
                    ->label('Solo con adiciones')
                    ->query(fn (Builder $query) => $query->whereNotNull('valor_adicional_1')),

                // ── Fila 5: Valor + fuente + papelera ────────────────────────
                Filter::make('valor_contrato')
                    ->label('Valor del contrato ($)')
                    ->columnSpan(1)
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->label('Mínimo ($)')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('max')
                            ->label('Máximo ($)')->numeric()->prefix('$'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['min'] ?? null, fn ($query, $v) => $query->where('valor_contrato', '>=', $v))
                        ->when($data['max'] ?? null, fn ($query, $v) => $query->where('valor_contrato', '<=', $v))
                    )
                    ->indicateUsing(fn (array $data): array => array_filter([
                        ($data['min'] ?? null) ? 'Valor ≥ $' . number_format($data['min'], 0, ',', '.') : null,
                        ($data['max'] ?? null) ? 'Valor ≤ $' . number_format($data['max'], 0, ',', '.') : null,
                    ])),

                SelectFilter::make('fuente_financiacion')
                    ->label('Fuente de recursos')
                    ->options(['FUNCIONAMIENTO' => 'Funcionamiento', 'INVERSION' => 'Inversión'])
                    ->multiple()
                    ->native(false),

                TrashedFilter::make()
                    ->label('Incluir eliminados')
                    ->native(false),

            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->headerActions([
                Tables\Actions\Action::make('importar')
                    ->label('Importar Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('vigencia')
                            ->label('Vigencia (hoja del Excel)')
                            ->options(self::opcionesVigencias())
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Forms\Components\FileUpload::make('archivo')
                            ->label('Archivo Excel')
                            ->helperText('Sube el archivo .xlsm, .xlsx o .xls — la validación se hace al importar.')
                            ->required()
                            ->disk('local')
                            ->directory('imports/contratos'),
                            // NOTA: NO usar ->acceptedFileTypes() NI ->extraInputAttributes(['accept'=>...])
                            // FilePond lee el atributo accept del input y valida MIME en cliente.
                            // Windows reporta .xlsm como application/zip → error falso positivo.
                            // La validación real (por extensión) se hace en el servidor, línea ~1285.
                    ])
                    ->action(function (array $data): void {
                        ini_set('memory_limit', '-1'); // Sin límite: PhpSpreadsheet necesita mucha RAM para XLSM grandes
                        try {
                            $archivo = is_array($data['archivo'])
                                ? array_values($data['archivo'])[0]
                                : $data['archivo'];

                            $filePath = Storage::disk('local')->path($archivo);

                            Log::info('[Contratos Import] Iniciando importación', [
                                'archivo'   => $archivo,
                                'filePath'  => $filePath,
                                'exists'    => file_exists($filePath),
                                'size'      => file_exists($filePath) ? filesize($filePath) : null,
                                'vigencia'  => $data['vigencia'],
                                'memory'    => memory_get_usage(true),
                            ]);

                            if (! file_exists($filePath)) {
                                throw new \Exception("No se encontró el archivo subido. Intente nuevamente.");
                            }

                            // Validar extensión del archivo
                            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                            if (! in_array($extension, ['xlsx', 'xlsm', 'xls'])) {
                                Storage::disk('local')->delete($archivo);
                                throw new \Exception("El archivo \".{$extension}\" no es un archivo de Excel válido. Solo se permiten archivos .xlsx, .xlsm o .xls.");
                            }

                            // Cargar hoja del año + LISTAS (para resolver fórmulas VLOOKUP)
                            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                            $reader->setLoadSheetsOnly([$data['vigencia'], 'LISTAS']);
                            $spreadsheet = $reader->load($filePath);

                            $import = new ContratosImport($data['vigencia']);
                            $import->processWorksheet($spreadsheet->getSheetByName($data['vigencia']));

                            $spreadsheet->disconnectWorksheets();
                            unset($spreadsheet);
                            gc_collect_cycles();

                            Log::info('[Contratos Import] Importación completada', [
                                'created' => $import->created,
                                'updated' => $import->updated,
                                'errors'  => count($import->errors),
                                'memory'  => memory_get_peak_usage(true),
                            ]);

                            $errorCount = count($import->errors);

                            if ($errorCount === 0) {
                                Notification::make()
                                    ->title('Importación completada')
                                    ->body("Vigencia {$data['vigencia']}: {$import->created} creados, {$import->updated} actualizados.")
                                    ->success()
                                    ->send();
                            } else {
                                // Generar Excel de errores
                                $errorUrl = self::generarExcelErrores($import->errors, $data['vigencia']);

                                Notification::make()
                                    ->title('Importación completada con errores')
                                    ->body(
                                        "Vigencia {$data['vigencia']}: {$import->created} creados, {$import->updated} actualizados. " .
                                        "{$errorCount} " . ($errorCount === 1 ? 'fila tuvo error' : 'filas tuvieron error') . ". " .
                                        "Descargue el reporte para conocer qué corregir."
                                    )
                                    ->warning()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('descargar_errores')
                                            ->label('Descargar reporte de errores')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->color('warning')
                                            ->url($errorUrl, shouldOpenInNewTab: true),
                                    ])
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Log::error('[Contratos Import] Error fatal', [
                                'message' => $e->getMessage(),
                                'class'   => get_class($e),
                                'file'    => $e->getFile(),
                                'line'    => $e->getLine(),
                                'trace'   => $e->getTraceAsString(),
                                'memory'  => memory_get_peak_usage(true),
                            ]);

                            Notification::make()
                                ->title('Error en la importación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reporte')
                    ->label('Reporte')
                    ->icon('heroicon-o-chart-bar-square')
                    ->color('info')
                    ->modalHeading('Generar Reporte de Contratos')
                    ->modalSubmitActionLabel('Generar y Descargar')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('vigencia')
                            ->label('Vigencia')
                            ->options(self::opcionesVigencias())
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\Select::make('periodo')
                            ->label('Agrupación temporal')
                            ->options([
                                'mensual'    => 'Mensual (por mes)',
                                'trimestral' => 'Trimestral (T1–T4)',
                                'semestral'  => 'Semestral (S1–S2)',
                                'anual'      => 'Anual (resumen total)',
                            ])
                            ->default('mensual')
                            ->required()
                            ->native(false),

                        Forms\Components\CheckboxList::make('incluir')
                            ->label('Incluir en el reporte')
                            ->options([
                                'cantidades'  => 'Cantidades (N° contratos)',
                                'valores'     => 'Valores de contratos ($)',
                                'adicionales' => 'Adiciones (N° y valor)',
                                'contratos'   => 'Detalle de contratos (hoja extra)',
                            ])
                            ->default(['cantidades', 'valores'])
                            ->columns(2)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $tmpFile  = \App\Helpers\ReporteContratos::generar(
                            $data['vigencia'],
                            $data['periodo'],
                            $data['incluir'] ?? []
                        );
                        $filename = "Reporte_Contratos_{$data['vigencia']}_"
                            . match ($data['periodo']) {
                                'mensual'    => 'Mensual',
                                'trimestral' => 'Trimestral',
                                'semestral'  => 'Semestral',
                                'anual'      => 'Anual',
                            } . '.xlsx';

                        return response()->streamDownload(function () use ($tmpFile) {
                            echo file_get_contents($tmpFile);
                            @unlink($tmpFile);
                        }, $filename, [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Eliminar contrato')
                        ->modalDescription('El registro se marcará como eliminado. Puede restaurarlo posteriormente.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->color('danger')
                        ->visible(fn () => Auth::user()?->hasRole('super_admin')),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename('contratos-' . now()->format('Y-m-d')),
                        ]),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasRole('super_admin')),
                ]),
            ])
            ->emptyStateHeading('No hay contratos registrados')
            ->emptyStateDescription('Importe un Excel o cree un contrato manualmente.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultPaginationPageOption(5);
    }

    // ─────────────────────────────────────────────────────────────────────
    // ELOQUENT QUERY
    // ─────────────────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPORTE DE ERRORES DE IMPORTACIÓN
    // ─────────────────────────────────────────────────────────────────────

    private static function generarExcelErrores(array $errors, string $vigencia): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Errores de importación');

        // ── Fila de título general ──
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', "⚠️  REPORTE DE ERRORES — Importación vigencia {$vigencia}   |   " . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '922B21']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Cabeceras ──
        $headers = [
            'A' => 'Fila en el Excel',
            'B' => 'N° Constancia SECOP',
            'C' => 'Contratista',
            'D' => '¿Qué pasó? (Problema encontrado)',
            'E' => '¿Qué debe hacer para corregirlo?',
            'F' => 'Campo en el Excel donde está el error',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}2", $label);
            $sheet->getStyle("{$col}2")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C0392B']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(35);

        // ── Filas de errores ──
        foreach ($errors as $i => $error) {
            $row = $i + 3;
            [$problema, $accion, $campoExcel] = self::traducirError($error['error_original'] ?? '');

            $sheet->setCellValue("A{$row}", $error['fila'] ?? '');
            $sheet->setCellValue("B{$row}", $error['numero_constancia_secop'] ?? $error['id_contrato_secop'] ?? '');
            $sheet->setCellValue("C{$row}", $error['contratista'] ?? '');
            $sheet->setCellValue("D{$row}", $problema);
            $sheet->setCellValue("E{$row}", $accion);
            $sheet->setCellValue("F{$row}", $campoExcel);

            $bgColor = ($row % 2 === 0) ? 'FDFEFE' : 'FADBD8';
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['vertical' => 'top', 'wrapText' => true],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
            $sheet->getRowDimension($row)->setRowHeight(-1);
        }

        // ── Anchos ──
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(42);
        $sheet->getColumnDimension('E')->setWidth(52);
        $sheet->getColumnDimension('F')->setWidth(30);

        // ── Bordes ──
        $lastRow = count($errors) + 2;
        $sheet->getStyle("A2:F{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'E5E7E9']]],
        ]);

        // ── Guardar ──
        Storage::disk('public')->makeDirectory('errores-importacion');
        $filename = "errores-contratos-{$vigencia}-" . now()->format('Ymd-His') . '.xlsx';
        $tmpPath  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpPath);
        Storage::disk('public')->put("errores-importacion/{$filename}", file_get_contents($tmpPath));
        @unlink($tmpPath);

        // Limpiar archivo script de diagnóstico si existe
        @unlink(base_path('read_errors.php'));

        return Storage::disk('public')->url("errores-importacion/{$filename}");
    }

    /** Devuelve [problema legible, acción a tomar, columna Excel afectada] */
    private static function traducirError(string $mensaje): array
    {
        // Mapa de nombres de columna BD → nombre amigable en el Excel
        $nombreCampo = [
            'nombre_rubro'      => 'NOMBRE DEL RUBRO (columna GP)',
            'nombre_proyecto'   => 'NOMBRE DEL PROYECTO (columna BW)',
            'programa'          => 'PROGRAMA (columna BY)',
            'subprograma'       => 'SUBPROGRAMA (columna BZ)',
            'producto_mga'      => 'PRODUCTO MGA (columna GJ)',
            'producto_cpc'      => 'PRODUCTO CPC (columna GK)',
            'objeto'            => 'OBJETO (columna V)',
            'descripcion_unspsc'=> 'DESCRIPCION CODIGO UNSPSC (columna DG)',
            'meta_plan_desarrollo' => 'META PLAN DE DESARROLLO (columna CB)',
            'observaciones'     => 'OBSERVACIONES',
            'funciones'         => 'FUNCIONES',
        ];

        if (str_contains($mensaje, 'Data too long for column') || str_contains($mensaje, 'right truncated')) {
            preg_match("/column '(.+?)' at/i", $mensaje, $m);
            $col = $m[1] ?? '';
            $nombreAmigable = $nombreCampo[$col] ?? "campo «{$col}»";
            return [
                "El texto del campo «{$nombreAmigable}» es demasiado largo para guardarlo.",
                "Abra el Excel, busque la columna {$nombreAmigable} en la fila indicada y acorte el texto. No es necesario eliminar información importante; puede resumirla.",
                $nombreAmigable,
            ];
        }

        if (str_contains($mensaje, 'Duplicate entry')) {
            return [
                'Este contrato ya estaba registrado con el mismo N° de Constancia SECOP.',
                'Verifique que el número de constancia no aparezca repetido en el Excel. Si es un contrato diferente, corrija el número antes de importar.',
                'N° CONSTANCIA SECOP (columna I)',
            ];
        }

        if (str_contains($mensaje, 'Incorrect date value') || str_contains($mensaje, 'date')) {
            return [
                'Una fecha está mal escrita o tiene un formato que el sistema no reconoce.',
                'Busque las columnas de fecha en esa fila y asegúrese de que la celda esté configurada como tipo "Fecha" en Excel (no como texto). El formato correcto es DD/MM/AAAA.',
                'Columna de fechas (verifique todas las fechas de esa fila)',
            ];
        }

        if (str_contains($mensaje, 'cannot be null') || str_contains($mensaje, 'NOT NULL')) {
            preg_match("/Column '(.+?)'/i", $mensaje, $m);
            $col = $nombreCampo[$m[1] ?? ''] ?? ($m[1] ?? 'un campo obligatorio');
            return [
                "Falta información obligatoria en el campo «{$col}».",
                "Ese campo no puede estar vacío. Llene el dato correspondiente en el Excel y vuelva a importar.",
                $col,
            ];
        }

        if (str_contains($mensaje, 'Out of range value')) {
            return [
                'Un número está fuera del rango permitido.',
                'Revise los valores numéricos de esa fila (valores en pesos, porcentajes, plazos). Verifique que no tenga ceros de más o un error de digitación.',
                'Columnas de valores numéricos',
            ];
        }

        return [
            'Ocurrió un error inesperado al guardar este registro.',
            'Revise todos los campos de esa fila buscando datos inusuales (caracteres especiales, celdas fusionadas, fórmulas con error). Si el problema persiste, comuníquelo al administrador del sistema.',
            'Verificar toda la fila',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContratos::route('/'),
            'create' => Pages\CreateContrato::route('/create'),
            'edit'   => Pages\EditContrato::route('/{record}/edit'),
        ];
    }
}
