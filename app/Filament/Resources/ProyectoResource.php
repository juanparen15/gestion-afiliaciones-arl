<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProyectoResource\Pages;
use App\Models\Proyecto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ProyectoResource extends Resource
{
    protected static ?string $model = Proyecto::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Proyectos';
    protected static ?string $modelLabel = 'Proyecto';
    protected static ?string $pluralModelLabel = 'Proyectos';
    protected static ?string $navigationGroup = 'Gestión de BPIM';
    protected static ?int $navigationSort = 10;

    /** Todo el módulo BPIM es exclusivo de super_admin. */
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Principal del Proyecto')
                    ->description('Complete la información básica del proyecto BPIM/BPIN')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_proyecto')
                            ->label('Nombre del Proyecto')->required()->maxLength(255)->columnSpanFull()
                            ->placeholder('Ingrese el nombre completo del proyecto'),

                        Forms\Components\TextInput::make('codigo_bpim')->label('Código BPIM')->maxLength(100)
                            ->placeholder('Ej: BPIM-25-00-001-00001'),

                        Forms\Components\TextInput::make('codigo_bpin')->label('Código BPIN')->maxLength(100)
                            ->placeholder('Ej: 2025000100001'),

                        Forms\Components\Placeholder::make('valor_total_calculado')
                            ->label('Valor Total del Proyecto')
                            ->content(function (Get $get) {
                                $total = 0;
                                foreach (($get('capitulos') ?? []) as $capitulo) {
                                    foreach (($capitulo['items'] ?? []) as $item) {
                                        $total += floatval($item['valor_total'] ?? 0);
                                    }
                                }
                                return '$' . number_format($total, 2, '.', ',');
                            })
                            ->helperText('Se calcula automáticamente desde los capítulos e ítems'),

                        Forms\Components\TextInput::make('valor_total_con_ajuste')
                            ->label('Valor Total con Ajuste')->numeric()->prefix('$')
                            ->helperText('Si aplica un ajuste manual al valor total'),

                        Forms\Components\Select::make('estado')
                            ->label('Estado del Proyecto')
                            ->options(['activo' => 'Activo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'])
                            ->default('activo')->required(),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')->rows(3)->columnSpanFull()
                            ->placeholder('Descripción general del proyecto'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Capítulos del Proyecto')
                    ->description('Agregue los capítulos y sus ítems correspondientes')
                    ->schema([
                        Forms\Components\Repeater::make('capitulos')
                            ->relationship('capitulos')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_capitulo')
                                            ->label('Número de Capítulo')->numeric()->required()->default(1)
                                            ->helperText('Número del capítulo (1, 2, 3...)'),

                                        Forms\Components\TextInput::make('orden')
                                            ->label('Orden')->numeric()->default(0)
                                            ->helperText('Orden de visualización'),

                                        Forms\Components\Placeholder::make('valor_total_capitulo')
                                            ->label('Valor Total del Capítulo')
                                            ->content(fn ($get) => '$' . number_format(collect($get('items') ?? [])->sum('valor_total'), 2, '.', ',')),
                                    ]),

                                Forms\Components\Textarea::make('nombre_capitulo')
                                    ->label('Nombre Completo del Capítulo')->required()->rows(2)->columnSpanFull()
                                    ->placeholder('Ej: SUMINISTRAR KITS DE ASEO PERSONAL PARA LOS RECLUSOS DEL INSTITUTO PENITENCIARIO'),

                                Forms\Components\Repeater::make('items')
                                    ->relationship('items')
                                    ->label('Ítems del Capítulo')
                                    ->schema([
                                        Forms\Components\Grid::make(6)
                                            ->schema([
                                                Forms\Components\TextInput::make('etapa')->label('Etapa')->maxLength(100)->columnSpan(2)
                                                    ->placeholder('Ej: CONTRACTUAL'),
                                                Forms\Components\TextInput::make('tipo')->label('Tipo')->maxLength(100)->columnSpan(2)
                                                    ->placeholder('Ej: SERVICIO'),
                                                Forms\Components\TextInput::make('anio')->label('Año')->numeric()->default(date('Y'))->columnSpan(2),
                                            ]),

                                        Forms\Components\TextInput::make('item')->label('Ítem')->required()->maxLength(255)->columnSpanFull()
                                            ->placeholder('Nombre del ítem'),

                                        Forms\Components\Textarea::make('descripcion')->label('Descripción')->required()->rows(2)->columnSpanFull()
                                            ->placeholder('Descripción detallada del ítem'),

                                        Forms\Components\TextInput::make('perfil')->label('Perfil')->maxLength(255)->columnSpanFull()
                                            ->placeholder('Perfil profesional requerido (opcional)'),

                                        Forms\Components\Grid::make(5)
                                            ->schema([
                                                Forms\Components\TextInput::make('valor_unitario')
                                                    ->label('Valor Unitario')->numeric()->prefix('$')->required()->live(onBlur: true)
                                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                        $cantidad = $get('cantidad') ?? 0;
                                                        if ($cantidad > 0 && $state > 0) {
                                                            $set('valor_total', $state * $cantidad);
                                                            $set('valor_restante', $state * $cantidad);
                                                        }
                                                    }),

                                                Forms\Components\TextInput::make('unidad')->label('Unidad')->required()->maxLength(50)
                                                    ->placeholder('Ej: Mes, Unidad, Kg'),

                                                Forms\Components\TextInput::make('cantidad')
                                                    ->label('Cantidad')->numeric()->required()->live(onBlur: true)
                                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                        $valorUnitario = $get('valor_unitario') ?? 0;
                                                        if ($valorUnitario > 0 && $state > 0) {
                                                            $set('valor_total', $valorUnitario * $state);
                                                            $set('valor_restante', $valorUnitario * $state);
                                                        }
                                                    }),

                                                Forms\Components\TextInput::make('valor_total')
                                                    ->label('Valor Total')->numeric()->prefix('$')->disabled()->dehydrated()
                                                    ->helperText('Se calcula automáticamente'),

                                                Forms\Components\TextInput::make('orden')->label('Orden')->numeric()->default(0),
                                            ]),

                                        Forms\Components\Placeholder::make('valor_ejecutado_info')
                                            ->label('Información de Ejecución')
                                            ->content(fn ($record) => $record
                                                ? sprintf('Ejecutado: $%s | Restante: $%s',
                                                    number_format($record->valor_ejecutado ?? 0, 2, '.', ','),
                                                    number_format($record->valor_restante ?? 0, 2, '.', ','))
                                                : 'Aún no hay ejecución registrada')
                                            ->columnSpanFull()
                                            ->visible(fn ($record) => $record !== null),
                                    ])
                                    ->columns(1)
                                    ->orderColumn('orden')
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                                    ->addActionLabel('Agregar Ítem')
                                    ->defaultItems(0)
                                    ->columnSpanFull(),
                            ])
                            ->orderColumn('orden')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['numero_capitulo'], $state['nombre_capitulo'])
                                ? "Capítulo {$state['numero_capitulo']}: " . substr($state['nombre_capitulo'], 0, 50)
                                : null)
                            ->addActionLabel('Agregar Capítulo')
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->live(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_proyecto')->label('Nombre del Proyecto')
                    ->searchable()->sortable()->wrap()->weight('bold')->limit(50),

                Tables\Columns\TextColumn::make('codigo_bpim')->label('Código BPIM')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('codigo_bpin')->label('Código BPIN')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('valor_total')->label('Valor Total')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_ejecutado')->label('Ejecutado')->money('COP')->sortable(),

                Tables\Columns\TextColumn::make('porcentaje_ejecucion')
                    ->label('% Ejecución')->suffix('%')->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'info',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('capitulos_count')->label('Capítulos')->counts('capitulos')->sortable()->badge(),

                Tables\Columns\TextColumn::make('estado')->badge()
                    ->color(fn ($record) => $record->estado_badge)
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('created_at')->label('Fecha Creación')->dateTime('d/m/Y')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options(['activo' => 'Activo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado']),

                Tables\Filters\Filter::make('con_presupuesto')
                    ->label('Con Presupuesto Disponible')
                    ->query(fn (Builder $query) => $query->where('valor_restante', '>', 0)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProyectos::route('/'),
            'create' => Pages\CreateProyecto::route('/create'),
            'view' => Pages\ViewProyecto::route('/{record}'),
            'edit' => Pages\EditProyecto::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::activos()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
