<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanadquisicioneResource\Pages;
use App\Filament\Resources\PlanadquisicioneResource\RelationManagers\ContratosRelationManager;
use App\Models\{Area, Clase, Dependencia, Familia, Planadquisicione, Producto, Segmento};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PlanadquisicioneResource extends Resource
{
    protected static ?string $model = Planadquisicione::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Plan de Adquisiciones';
    protected static ?string $navigationLabel = 'Planes de Adquisición';
    protected static ?string $modelLabel = 'Plan de Adquisición';
    protected static ?string $pluralModelLabel = 'Planes de Adquisición';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Datos del Contrato')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('descripcioncont')->label('Descripción del Contrato')->required()->maxLength(500)->columnSpanFull(),
                        Forms\Components\TextInput::make('valorestimadocont')->label('Valor Estimado del Contrato')->required(),
                        Forms\Components\TextInput::make('valorestimadovig')->label('Valor Estimado Vigencia')->required(),
                        Forms\Components\TextInput::make('duracont')->label('Duración (meses)')->required(),
                        Forms\Components\TextInput::make('codbpim')->label('Código BPIM')->maxLength(50),
                        Forms\Components\Select::make('dependencia_id')->label('Dependencia')->required()->searchable()->preload()->live()
                            ->options(function () {
                                $user = Auth::user();

                                if (! $user || $user->hasRole('super_admin') || $user->hasRole('SSST')) {
                                    return Dependencia::orderBy('nombre')->pluck('nombre', 'id');
                                }

                                if ($user->dependencia_id) {
                                    return Dependencia::where('id', $user->dependencia_id)->pluck('nombre', 'id');
                                }

                                if ($user->area_id) {
                                    $depId = Area::where('id', $user->area_id)->value('dependencia_id');
                                    return Dependencia::where('id', $depId)->pluck('nombre', 'id');
                                }

                                return [];
                            })
                            ->afterStateUpdated(fn (Set $set) => $set('area_id', null)),
                        Forms\Components\Select::make('area_id')->label('Área (opcional)')->searchable()->preload()->nullable()
                            ->helperText('Solo si el plan corresponde a un área específica de la dependencia.')
                            ->options(function (Get $get) {
                                $depId = $get('dependencia_id');
                                if (! $depId) {
                                    return [];
                                }

                                $user = Auth::user();
                                $query = Area::where('dependencia_id', $depId);

                                // Un usuario de área solo puede elegir su propia área.
                                if ($user && $user->area_id && ! $user->hasRole('super_admin') && ! $user->hasRole('SSST')) {
                                    $query->where('id', $user->area_id);
                                }

                                return $query->orderBy('nombre')->pluck('nombre', 'id');
                            }),
                    ])->columns(2),

                Forms\Components\Wizard\Step::make('Clasificación')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('tipoadquisicione_id')->label('Tipo de Adquisición')->relationship('tipoadquisicione', 'dettipoadquisicion')->searchable()->preload()->required(),
                            Forms\Components\Select::make('modalidade_id')->label('Modalidad')->relationship('modalidade', 'detmodalidad')->searchable()->preload()->required(),
                            Forms\Components\Select::make('tipozona_id')->label('Tipo de Zona')->relationship('tipozona', 'tipozona')->searchable()->preload()->required(),
                            Forms\Components\Select::make('estadovigencia_id')->label('Estado Vigencia')->relationship('estadovigencia', 'detestadovigencia')->searchable()->preload()->required(),
                            Forms\Components\Select::make('vigenfutura_id')->label('Vigencia Futura')->relationship('vigenfutura', 'detvigencia')->searchable()->preload()->required(),
                            Forms\Components\Select::make('fuente_id')->label('Fuente')->relationship('fuente', 'detfuente')->searchable()->preload()->required(),
                            Forms\Components\Select::make('mese_id')->label('Mes de Inicio')->relationship('mese', 'nommes')->searchable()->preload()->required(),
                            Forms\Components\Select::make('intervalo_id')->label('Intervalo')->relationship('intervalo', 'intervalo')->searchable()->preload()->required(),
                            Forms\Components\Select::make('tipoprioridade_id')->label('Tipo de Prioridad')->relationship('tipoprioridade', 'detprioridad')->searchable()->preload()->required(),
                            Forms\Components\Select::make('requiproyecto_id')->label('Requiere Proyecto')->relationship('requiproyecto', 'detproyeto')->searchable()->preload()->required(),
                            Forms\Components\Select::make('requipoai_id')->label('Requiere POA-I')->relationship('requipoai', 'detpoai')->searchable()->preload()->required(),
                            Forms\Components\Select::make('tipoproceso_id')->label('Tipo de Proceso')->relationship('tipoproceso', 'dettipoproceso')->searchable()->preload(),
                        ]),
                    ]),

                Forms\Components\Wizard\Step::make('Clasificación UNSPSC')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->label('Clasificaciones UNSPSC')
                            ->helperText('Agrega una o varias clasificaciones. El producto es opcional (puedes dejarlo hasta la Clase).')
                            ->addActionLabel('Agregar clasificación')
                            ->defaultItems(1)
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => filled($state['clase_id'] ?? null)
                                ? optional(Clase::find($state['clase_id']))->detclase
                                : null)
                            ->schema([
                                Forms\Components\Select::make('segmento_id')->label('Segmento')
                                    ->options(fn () => Segmento::orderBy('detsegmento')->pluck('detsegmento', 'id'))
                                    ->searchable()->live()->dehydrated(false)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('familia_id', null);
                                        $set('clase_id', null);
                                        $set('producto_id', null);
                                    }),
                                Forms\Components\Select::make('familia_id')->label('Familia')
                                    ->options(fn (Get $get) => $get('segmento_id')
                                        ? Familia::where('segmento_id', $get('segmento_id'))->orderBy('detfamilia')->pluck('detfamilia', 'id')
                                        : [])
                                    ->searchable()->live()->dehydrated(false)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('clase_id', null);
                                        $set('producto_id', null);
                                    }),
                                Forms\Components\Select::make('clase_id')->label('Clase')
                                    ->options(fn (Get $get) => $get('familia_id')
                                        ? Clase::where('familia_id', $get('familia_id'))->orderBy('detclase')->pluck('detclase', 'id')
                                        : [])
                                    ->searchable()->live()->required()
                                    ->afterStateUpdated(fn (Set $set) => $set('producto_id', null)),
                                Forms\Components\Select::make('producto_id')->label('Producto (opcional)')
                                    ->options(fn (Get $get) => $get('clase_id')
                                        ? Producto::where('clase_id', $get('clase_id'))->orderBy('detproducto')->pluck('detproducto', 'id')
                                        : [])
                                    ->searchable()
                                    ->nullable(),
                            ])
                            // Al EDITAR/VER: reconstruir la cascada completa. Los datos importados pueden traer
                            // solo producto_id (sin clase_id), así que derivamos la clase desde el producto.
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                if (empty($data['clase_id']) && ! empty($data['producto_id']) && ($prod = Producto::find($data['producto_id']))) {
                                    $data['clase_id'] = $prod->clase_id;
                                }
                                if (! empty($data['clase_id']) && ($clase = Clase::find($data['clase_id']))) {
                                    $data['familia_id'] = $clase->familia_id;
                                    $data['segmento_id'] = optional(Familia::find($clase->familia_id))->segmento_id;
                                }
                                return $data;
                            })
                            // Al GUARDAR: solo persistir clase_id y producto_id (segmento/familia son ayudas visuales).
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => collect($data)->only(['clase_id', 'producto_id'])->all())
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => collect($data)->only(['clase_id', 'producto_id'])->all()),
                    ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        // super_admin y SSST ven todos los planes.
        if ($user->hasRole('super_admin') || $user->hasRole('SSST')) {
            return $query;
        }

        // Con área asignada: solo los planes de su área.
        if ($user->area_id) {
            return $query->where('area_id', $user->area_id);
        }

        // Sin área pero con dependencia: planes de su dependencia (directos) o de las áreas de su dependencia.
        if ($user->dependencia_id) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('dependencia_id', $user->dependencia_id)
                    ->orWhereHas('area', fn (Builder $a) => $a->where('dependencia_id', $user->dependencia_id));
            });
        }

        // Sin área ni dependencia: no ve ningún plan.
        return $query->whereRaw('1 = 0');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            ViewEntry::make('comprobante')
                ->hiddenLabel()
                ->view('filament.infolists.plan-comprobante')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_vigencia')->label('N° Reg.')->badge()->color('primary')->sortable(),
                Tables\Columns\TextColumn::make('descripcioncont')->label('Descripción')->searchable()->sortable()->limit(60)->tooltip(fn ($record) => $record->descripcioncont),
                Tables\Columns\TextColumn::make('valorestimadocont')->label('Valor Estimado')->sortable(),
                Tables\Columns\TextColumn::make('dependencia.nombre')->label('Dependencia')->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('area.nombre')->label('Área')->sortable()->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('mese.nommes')->label('Mes')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estadovigencia.detestadovigencia')->label('Estado Vigencia')->badge()
                    ->color(fn (?string $state): string => match (true) {
                        $state === null => 'gray',
                        str_contains(strtolower($state), 'vigente') => 'success',
                        str_contains(strtolower($state), 'cerrad') => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Vigencia')->date('Y')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Registrado por')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contratos_count')->counts('contratos')->label('Contratos')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vigencia')->label('Vigencia (Año)')
                    ->options(function () {
                        // Año derivado de created_at; compatible con SQLite (tests) y MySQL (prod)
                        $driver = DB::getDriverName();
                        $yearExpr = $driver === 'sqlite'
                            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
                            : 'YEAR(created_at)';
                        return Planadquisicione::selectRaw("{$yearExpr} as year")->distinct()->orderBy('year', 'desc')->pluck('year', 'year')->toArray();
                    })
                    ->query(fn (Builder $query, array $data) => empty($data['value']) ? $query : $query->whereYear('created_at', $data['value'])),
                Tables\Filters\SelectFilter::make('area_id')->label('Área')->relationship('area', 'nombre')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('estadovigencia_id')->label('Estado Vigencia')->relationship('estadovigencia', 'detestadovigencia'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record): string => static::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->withColumns([
                                    Column::make('descripcioncont')->heading('Descripción'),
                                    Column::make('valorestimadocont')->heading('Valor Estimado'),
                                    Column::make('valorestimadovig')->heading('Valor Vigencia'),
                                    Column::make('duracont')->heading('Duración (meses)'),
                                    Column::make('dependencia.nombre')->heading('Dependencia'),
                                    Column::make('area.nombre')->heading('Área'),
                                    Column::make('modalidade.detmodalidad')->heading('Modalidad'),
                                    Column::make('tipoadquisicione.dettipoadquisicion')->heading('Tipo de Adquisición'),
                                    Column::make('estadovigencia.detestadovigencia')->heading('Estado Vigencia'),
                                    Column::make('fuente.detfuente')->heading('Fuente'),
                                    Column::make('mese.nommes')->heading('Mes'),
                                    Column::make('codbpim')->heading('Código BPIM'),
                                    Column::make('created_at')->heading('Vigencia')->formatStateUsing(fn ($state) => $state?->format('Y')),
                                    Column::make('user.name')->heading('Registrado por'),
                                ])
                                ->withFilename('plan-adquisiciones-' . now()->format('Y-m-d')),
                        ]),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ContratosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlanadquisiciones::route('/'),
            'create' => Pages\CreatePlanadquisicione::route('/create'),
            'view'   => Pages\ViewPlanadquisicione::route('/{record}'),
            'edit'   => Pages\EditPlanadquisicione::route('/{record}/edit'),
        ];
    }
}
