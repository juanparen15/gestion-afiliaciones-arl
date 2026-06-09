<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanadquisicioneResource\Pages;
use App\Filament\Resources\PlanadquisicioneResource\RelationManagers\ContratosRelationManager;
use App\Models\{Area, Clase, Familia, Planadquisicione, Producto, Segmento};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                        Forms\Components\Select::make('area_id')->label('Área')->required()->searchable()->preload()
                            ->options(function () {
                                $user = Auth::user();

                                // super_admin y SSST pueden elegir cualquier área.
                                if (! $user || $user->hasRole('super_admin') || $user->hasRole('SSST')) {
                                    return Area::orderBy('nombre')->pluck('nombre', 'id');
                                }

                                // Con área asignada: solo su área.
                                if ($user->area_id) {
                                    return Area::where('id', $user->area_id)->pluck('nombre', 'id');
                                }

                                // Sin área pero con dependencia: las áreas de su dependencia.
                                if ($user->dependencia_id) {
                                    return Area::where('dependencia_id', $user->dependencia_id)->orderBy('nombre')->pluck('nombre', 'id');
                                }

                                return [];
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
                        Forms\Components\Select::make('segmento_id')->label('Segmento')
                            ->options(fn () => Segmento::orderBy('detsegmento')->pluck('detsegmento', 'id'))
                            ->live()->searchable()->dehydrated(false)
                            ->afterStateUpdated(function (Set $set) {
                                $set('familia_id', null);
                                $set('clase_id', null);
                            }),
                        Forms\Components\Select::make('familia_id')->label('Familia')
                            ->options(fn (Get $get) => Familia::when($get('segmento_id'), fn ($q) => $q->where('segmento_id', $get('segmento_id')))->orderBy('detfamilia')->pluck('detfamilia', 'id'))
                            ->live()->searchable()->dehydrated(false)
                            ->afterStateUpdated(function (Set $set) {
                                $set('clase_id', null);
                            }),
                        Forms\Components\Select::make('clase_id')->label('Clase')
                            ->options(fn (Get $get) => Clase::when($get('familia_id'), fn ($q) => $q->where('familia_id', $get('familia_id')))->orderBy('detclase')->pluck('detclase', 'id'))
                            ->live()->searchable()->dehydrated(false),
                        Forms\Components\Select::make('productos')->label('Productos UNSPSC')->multiple()->relationship('productos', 'detproducto')
                            ->options(fn (Get $get) => Producto::when($get('clase_id'), fn ($q) => $q->where('clase_id', $get('clase_id')))->orderBy('detproducto')->pluck('detproducto', 'id'))
                            ->searchable()->preload(false),
                        Forms\Components\Select::make('clases')->label('Clases UNSPSC')->multiple()->relationship('clases', 'detclase')
                            ->options(fn (Get $get) => Clase::when($get('familia_id'), fn ($q) => $q->where('familia_id', $get('familia_id')))->orderBy('detclase')->pluck('detclase', 'id'))
                            ->searchable()->preload(false),
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

        // Sin área pero con dependencia: los planes de todas las áreas de su dependencia.
        if ($user->dependencia_id) {
            return $query->whereHas('area', fn (Builder $q) => $q->where('dependencia_id', $user->dependencia_id));
        }

        // Sin área ni dependencia: no ve ningún plan.
        return $query->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descripcioncont')->label('Descripción')->searchable()->sortable()->limit(60)->tooltip(fn ($record) => $record->descripcioncont),
                Tables\Columns\TextColumn::make('valorestimadocont')->label('Valor Estimado')->sortable(),
                Tables\Columns\TextColumn::make('area.nombre')->label('Área')->sortable()->searchable(),
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
                Tables\Actions\ViewAction::make(),
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
            'edit'   => Pages\EditPlanadquisicione::route('/{record}/edit'),
        ];
    }
}
