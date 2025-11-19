---
title: Recursos Filament
description: Documentación técnica de los recursos de FilamentPHP
---

## Estructura de un Recurso

Los recursos de Filament definen:
- **Formularios** para crear/editar
- **Tablas** para listar
- **Acciones** para operaciones

### Ubicación

```
app/Filament/Resources/
├── AfiliacionResource.php
├── AfiliacionResource/
│   └── Pages/
│       ├── CreateAfiliacion.php
│       ├── EditAfiliacion.php
│       └── ListAfiliaciones.php
├── UserResource.php
├── DependenciaResource.php
└── AreaResource.php
```

---

## AfiliacionResource

### Archivo Principal
`app/Filament/Resources/AfiliacionResource.php`

### Configuración Base

```php
class AfiliacionResource extends Resource
{
    protected static ?string $model = Afiliacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Gestión';
    protected static ?int $navigationSort = 1;
}
```

### Formulario

El formulario usa **Tabs** para organizar los campos:

```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Tabs::make('Afiliación')
                ->tabs([
                    Tab::make('Datos del Contratista')
                        ->schema([
                            // Campos de información personal
                        ]),
                    Tab::make('Información del Contrato')
                        ->schema([
                            // Campos del contrato
                        ]),
                    Tab::make('Información ARL')
                        ->schema([
                            // Campos de ARL
                        ]),
                    Tab::make('Estado y Observaciones')
                        ->schema([
                            // Campos de estado
                        ]),
                ])
                ->columnSpanFull(),
        ]);
}
```

### Tab 1: Datos del Contratista

```php
Tab::make('Datos del Contratista')
    ->schema([
        Section::make('Información Personal')
            ->schema([
                TextInput::make('nombre_contratista')
                    ->label('Nombre Completo')
                    ->required()
                    ->maxLength(255),

                Select::make('tipo_documento')
                    ->options([
                        'CC' => 'Cédula de Ciudadanía',
                        'CE' => 'Cédula de Extranjería',
                        'PP' => 'Pasaporte',
                        'TI' => 'Tarjeta de Identidad',
                    ])
                    ->required(),

                TextInput::make('numero_documento')
                    ->unique(ignoreRecord: true)
                    ->required(),

                DatePicker::make('fecha_nacimiento')
                    ->maxDate(now()->subYears(18)),

                TextInput::make('telefono_contratista')
                    ->tel(),

                TextInput::make('email_contratista')
                    ->email()
                    ->required(),
            ])
            ->columns(2),

        Section::make('Seguridad Social')
            ->schema([
                TextInput::make('eps')
                    ->label('EPS'),
                TextInput::make('afp')
                    ->label('AFP'),
            ])
            ->columns(2),
    ])
```

### Tab 2: Información del Contrato

```php
Tab::make('Información del Contrato')
    ->schema([
        Section::make('Datos del Contrato')
            ->schema([
                TextInput::make('numero_contrato')
                    ->required(),

                Select::make('dependencia_id')
                    ->relationship('dependencia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                Select::make('area_id')
                    ->relationship('area', 'nombre', function ($query, $get) {
                        return $query->where('dependencia_id', $get('dependencia_id'));
                    })
                    ->searchable()
                    ->preload(),

                Textarea::make('objeto_contractual')
                    ->required()
                    ->rows(3),
            ])
            ->columns(2),

        Section::make('Valores y Duración')
            ->schema([
                TextInput::make('valor_contrato')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                TextInput::make('honorarios_mensual')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('ibc', $state * 0.4);
                    }),

                TextInput::make('ibc')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(),

                DatePicker::make('fecha_inicio')
                    ->required(),

                DatePicker::make('fecha_fin')
                    ->required()
                    ->after('fecha_inicio'),

                FileUpload::make('contrato_pdf_o_word')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ])
                    ->maxSize(10240)
                    ->directory('contratos'),
            ])
            ->columns(2),
    ])
```

### Tab 3: Información ARL

```php
Tab::make('Información ARL')
    ->schema([
        TextInput::make('nombre_arl')
            ->label('Nombre de la ARL'),

        Select::make('tipo_riesgo')
            ->options([
                'I' => 'Nivel I',
                'II' => 'Nivel II',
                'III' => 'Nivel III',
                'IV' => 'Nivel IV',
                'V' => 'Nivel V',
            ]),

        TextInput::make('numero_afiliacion_arl'),

        DatePicker::make('fecha_afiliacion_arl'),

        DatePicker::make('fecha_terminacion_afiliacion'),

        FileUpload::make('pdf_arl')
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(10240)
            ->directory('afiliaciones/pdfs')
            ->visible(fn () => auth()->user()->hasRole(['super_admin', 'SSST'])),
    ])
    ->columns(2)
```

### Tab 4: Estado y Observaciones

```php
Tab::make('Estado y Observaciones')
    ->schema([
        Select::make('estado')
            ->options([
                'pendiente' => 'Pendiente',
                'validado' => 'Validado',
                'rechazado' => 'Rechazado',
            ])
            ->default('pendiente')
            ->disabled(fn () => !auth()->user()->hasRole(['super_admin', 'SSST'])),

        Textarea::make('observaciones')
            ->rows(3),

        Textarea::make('motivo_rechazo')
            ->rows(3)
            ->visible(fn ($get) => $get('estado') === 'rechazado'),
    ])
```

---

## Tabla de Listado

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('numero_contrato')
                ->searchable()
                ->sortable(),

            TextColumn::make('nombre_contratista')
                ->searchable()
                ->sortable()
                ->wrap(),

            TextColumn::make('numero_documento')
                ->searchable()
                ->copyable(),

            TextColumn::make('dependencia.nombre')
                ->sortable(),

            TextColumn::make('area.nombre')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('valor_contrato')
                ->money('COP')
                ->sortable(),

            TextColumn::make('fecha_inicio')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('fecha_fin')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('tipo_riesgo')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'I' => 'success',
                    'II' => 'info',
                    'III' => 'warning',
                    'IV' => 'danger',
                    'V' => 'danger',
                }),

            TextColumn::make('estado')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pendiente' => 'warning',
                    'validado' => 'success',
                    'rechazado' => 'danger',
                }),

            TextColumn::make('creador.name')
                ->label('Creado por')
                ->toggleable(),

            TextColumn::make('created_at')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('created_at', 'desc');
}
```

---

## Filtros

```php
->filters([
    SelectFilter::make('estado')
        ->options([
            'pendiente' => 'Pendiente',
            'validado' => 'Validado',
            'rechazado' => 'Rechazado',
        ]),

    SelectFilter::make('dependencia_id')
        ->relationship('dependencia', 'nombre')
        ->searchable()
        ->preload(),

    SelectFilter::make('area_id')
        ->relationship('area', 'nombre')
        ->searchable(),

    SelectFilter::make('tipo_riesgo')
        ->options([
            'I' => 'Nivel I',
            'II' => 'Nivel II',
            'III' => 'Nivel III',
            'IV' => 'Nivel IV',
            'V' => 'Nivel V',
        ]),

    Filter::make('vigentes')
        ->query(fn (Builder $query) => $query->where('fecha_fin', '>=', now()))
        ->label('Contratos Vigentes')
        ->toggle(),

    Filter::make('por_vencer')
        ->query(fn (Builder $query) => $query->whereBetween('fecha_fin', [now(), now()->addDays(30)]))
        ->label('Por Vencer (30 días)')
        ->toggle(),

    TrashedFilter::make(),
])
```

---

## Acciones de Header

```php
->headerActions([
    // Descargar plantilla
    Action::make('descargarPlantilla')
        ->label('Descargar Plantilla')
        ->icon('heroicon-o-arrow-down-tray')
        ->action(function () {
            return Excel::download(
                new AfiliacionesTemplateExport,
                'plantilla_afiliaciones.xlsx'
            );
        }),

    // Exportar todo
    Action::make('exportarTodo')
        ->label('Exportar Todo')
        ->icon('heroicon-o-document-arrow-down')
        ->action(function () {
            return Excel::download(
                new AfiliacionesExport,
                'afiliaciones_' . now()->format('Y-m-d') . '.xlsx'
            );
        }),

    // Importar Excel
    Action::make('importar')
        ->label('Importar Excel')
        ->icon('heroicon-o-document-arrow-up')
        ->form([
            FileUpload::make('archivo')
                ->acceptedFileTypes([
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])
                ->required(),
        ])
        ->action(function (array $data) {
            $import = new AfiliacionesImport;
            Excel::import($import, storage_path('app/public/' . $data['archivo']));

            Notification::make()
                ->title('Importación completada')
                ->body("Creados: {$import->registrosCreados}, Actualizados: {$import->registrosActualizados}")
                ->success()
                ->send();
        }),
])
```

---

## Acciones de Fila

```php
->actions([
    ViewAction::make(),
    EditAction::make(),

    // Validar
    Action::make('validar')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->visible(fn (Afiliacion $record) =>
            $record->estado === 'pendiente' &&
            auth()->user()->hasRole(['super_admin', 'SSST'])
        )
        ->form([
            FileUpload::make('pdf_arl')
                ->label('PDF Certificado ARL')
                ->acceptedFileTypes(['application/pdf'])
                ->required()
                ->directory('afiliaciones/pdfs'),

            Textarea::make('observaciones')
                ->label('Observaciones')
                ->rows(3),
        ])
        ->action(function (Afiliacion $record, array $data) {
            $record->update([
                'estado' => 'validado',
                'pdf_arl' => $data['pdf_arl'],
                'observaciones' => $data['observaciones'] ?? $record->observaciones,
                'validated_by' => auth()->id(),
                'fecha_validacion' => now(),
            ]);

            Notification::make()
                ->title('Afiliación validada')
                ->success()
                ->send();
        }),

    // Rechazar
    Action::make('rechazar')
        ->icon('heroicon-o-x-circle')
        ->color('danger')
        ->visible(fn (Afiliacion $record) =>
            $record->estado === 'pendiente' &&
            auth()->user()->hasRole(['super_admin', 'SSST'])
        )
        ->form([
            Textarea::make('motivo_rechazo')
                ->label('Motivo del Rechazo')
                ->required()
                ->rows(4),
        ])
        ->action(function (Afiliacion $record, array $data) {
            $record->update([
                'estado' => 'rechazado',
                'motivo_rechazo' => $data['motivo_rechazo'],
                'validated_by' => auth()->id(),
                'fecha_validacion' => now(),
            ]);

            Notification::make()
                ->title('Afiliación rechazada')
                ->warning()
                ->send();
        }),

    RestoreAction::make(),
    DeleteAction::make(),
    ForceDeleteAction::make(),
])
```

---

## Acciones Masivas

```php
->bulkActions([
    BulkActionGroup::make([
        RestoreBulkAction::make(),
        DeleteBulkAction::make(),
        ForceDeleteBulkAction::make(),

        // Exportar seleccionados
        BulkAction::make('exportar')
            ->label('Exportar Seleccionados')
            ->icon('heroicon-o-document-arrow-down')
            ->action(function (Collection $records) {
                return Excel::download(
                    new AfiliacionesExport($records->pluck('id')->toArray()),
                    'afiliaciones_seleccionadas.xlsx'
                );
            }),
    ]),
])
```

---

## Control de Acceso por Dependencia

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()
        ->withoutGlobalScopes([SoftDeletingScope::class]);

    // Si no es admin o SSST, filtrar por dependencia
    if (!auth()->user()->hasRole(['super_admin', 'SSST'])) {
        $query->where('dependencia_id', auth()->user()->dependencia_id);

        // Opcionalmente filtrar también por área
        if (auth()->user()->area_id) {
            $query->where('area_id', auth()->user()->area_id);
        }
    }

    return $query;
}
```

---

## Widgets

### Stats Overview

```php
// app/Filament/Widgets/AfiliacionesStatsOverview.php
class AfiliacionesStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Afiliaciones', Afiliacion::count())
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Stat::make('Pendientes', Afiliacion::pendiente()->count())
                ->color('warning'),

            Stat::make('Validadas', Afiliacion::validado()->count())
                ->color('success'),

            Stat::make('Rechazadas', Afiliacion::rechazado()->count())
                ->color('danger'),

            Stat::make('Contratos Vigentes', Afiliacion::vigente()->count())
                ->color('success'),

            Stat::make('Por Vencer (30 días)', Afiliacion::porVencer(30)->count())
                ->color('warning'),
        ];
    }
}
```

### Chart Widget

```php
// app/Filament/Widgets/AfiliacionesPorEstadoChart.php
class AfiliacionesPorEstadoChart extends ChartWidget
{
    protected static ?string $heading = 'Afiliaciones por Estado';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        Afiliacion::pendiente()->count(),
                        Afiliacion::validado()->count(),
                        Afiliacion::rechazado()->count(),
                    ],
                    'backgroundColor' => ['#f59e0b', '#10b981', '#ef4444'],
                ],
            ],
            'labels' => ['Pendientes', 'Validadas', 'Rechazadas'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
```

---

## Próximos Pasos

- [Eventos y Listeners](/tecnica/eventos/)
- [Políticas y Permisos](/tecnica/permisos/)
