<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemCatalogoResource\Pages;
use App\Models\ItemCatalogo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ItemCatalogoResource extends Resource
{
    protected static ?string $model = ItemCatalogo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Catálogo de Ítems';
    protected static ?string $modelLabel = 'Ítem del Catálogo';
    protected static ?string $pluralModelLabel = 'Ítems del Catálogo';
    protected static ?string $navigationGroup = 'Gestión de BPIM';
    protected static ?int $navigationSort = 20;

    /** Todo el módulo BPIM es exclusivo de super_admin. */
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica del Ítem')
                    ->schema([
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código del Ítem')->required()->unique(ignoreRecord: true)->maxLength(100)
                            ->placeholder('Ej: ITEM-001')->helperText('Código único identificador del ítem'),

                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Ítem')->required()->maxLength(255)->placeholder('Nombre corto del ítem'),

                        Forms\Components\Toggle::make('activo')->label('Activo')->default(true)
                            ->helperText('Solo los ítems activos estarán disponibles para selección'),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción Completa')->required()->rows(3)->columnSpanFull()
                            ->placeholder('Descripción detallada del ítem'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo de Ítem')
                            ->options([
                                'SUMINISTRO' => 'Suministro', 'SERVICIO' => 'Servicio', 'BIEN' => 'Bien',
                                'OBRA' => 'Obra', 'CONSULTORÍA' => 'Consultoría',
                            ])
                            ->searchable()->placeholder('Seleccione el tipo'),

                        Forms\Components\Select::make('etapa')
                            ->label('Etapa')
                            ->options([
                                'PRECONTRACTUAL' => 'Precontractual', 'CONTRACTUAL' => 'Contractual',
                                'POSCONTRACTUAL' => 'Poscontractual', 'EJECUCIÓN' => 'Ejecución',
                            ])
                            ->searchable()->placeholder('Seleccione la etapa'),

                        Forms\Components\TextInput::make('perfil')
                            ->label('Perfil Profesional')->maxLength(255)
                            ->placeholder('Ej: Profesional en Ingeniería')->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Información Financiera')
                    ->schema([
                        Forms\Components\Select::make('proyecto_origen_id')
                            ->label('Proyecto de Origen')->relationship('proyectoOrigen', 'nombre_proyecto')
                            ->searchable()->preload()->nullable()
                            ->helperText('Proyecto del cual proviene este ítem')->columnSpanFull(),

                        Forms\Components\TextInput::make('valor_unitario')
                            ->label('Valor Unitario')->required()->numeric()->prefix('$')->placeholder('0.00')
                            ->helperText('Valor por unidad del ítem'),

                        Forms\Components\TextInput::make('unidad')
                            ->label('Unidad de Medida')->required()->maxLength(50)
                            ->placeholder('Ej: Mes, Unidad, Kg, Hora')->helperText('Unidad en la que se mide el ítem'),

                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad Disponible/Límite')->required()->numeric()->minValue(0)->default(0)
                            ->helperText('Cantidad máxima disponible de este ítem'),

                        Forms\Components\TextInput::make('valor_ejecutado')
                            ->label('Valor Ejecutado')->numeric()->prefix('$')->disabled()->dehydrated(false)
                            ->helperText('Se calcula automáticamente desde los proyectos'),

                        Forms\Components\TextInput::make('valor_restante')
                            ->label('Valor Restante')->numeric()->prefix('$')->disabled()->dehydrated(false)
                            ->helperText('Se calcula automáticamente'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable()->sortable()->copyable()->weight('bold'),

                Tables\Columns\TextColumn::make('nombre')->label('Ítem')->searchable()->sortable()->limit(40)
                    ->tooltip(fn ($record) => $record->nombre),

                Tables\Columns\TextColumn::make('proyectoOrigen.nombre_proyecto')
                    ->label('Proyecto Origen')->searchable()->sortable()->limit(30)
                    ->tooltip(fn ($record) => $record->proyectoOrigen?->nombre_proyecto)->toggleable(),

                Tables\Columns\TextColumn::make('tipo')->label('Tipo')->searchable()->sortable()->badge()->color('info'),
                Tables\Columns\TextColumn::make('etapa')->label('Etapa')->searchable()->sortable()->badge()->color('warning'),
                Tables\Columns\TextColumn::make('unidad')->label('Unidad')->searchable()->sortable()->badge(),

                Tables\Columns\TextColumn::make('cantidad')->label('Cantidad Límite')->sortable()->alignCenter()->badge()->color('info'),
                Tables\Columns\TextColumn::make('valor_unitario')->label('Valor Unitario')->money('COP')->sortable(),

                Tables\Columns\TextColumn::make('valor_ejecutado')->label('Ejecutado')->money('COP')->sortable()
                    ->color('success')->toggleable(),

                Tables\Columns\TextColumn::make('valor_restante')->label('Restante')->money('COP')->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state == 0 ? 'warning' : 'success'))->toggleable(),

                Tables\Columns\TextColumn::make('usos_count')->label('Usos')->counts('usos')->sortable()->badge()
                    ->tooltip('Cantidad de veces que se ha usado en proyectos'),

                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean()->sortable(),

                Tables\Columns\TextColumn::make('created_at')->label('Fecha Creación')->dateTime('d/m/Y')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'SUMINISTRO' => 'Suministro', 'SERVICIO' => 'Servicio', 'BIEN' => 'Bien',
                        'OBRA' => 'Obra', 'CONSULTORÍA' => 'Consultoría',
                    ]),

                Tables\Filters\SelectFilter::make('etapa')
                    ->options([
                        'PRECONTRACTUAL' => 'Precontractual', 'CONTRACTUAL' => 'Contractual',
                        'POSCONTRACTUAL' => 'Poscontractual', 'EJECUCIÓN' => 'Ejecución',
                    ]),

                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')->placeholder('Todos')->trueLabel('Solo Activos')->falseLabel('Solo Inactivos'),

                Tables\Filters\Filter::make('con_presupuesto')
                    ->label('Con Presupuesto Disponible')
                    ->query(fn (Builder $query) => $query->where('valor_restante', '>', 0)),

                Tables\Filters\Filter::make('sin_presupuesto')
                    ->label('Sin Presupuesto')
                    ->query(fn (Builder $query) => $query->where('valor_restante', '<=', 0)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('recalcular_valores')
                        ->label('Recalcular Valores')
                        ->icon('heroicon-o-calculator')->color('info')
                        ->action(function ($record) {
                            $record->recalcularValores();
                            Notification::make()->title('Valores Recalculados')
                                ->body('Los valores del ítem han sido recalculados exitosamente.')->success()->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),

                    Tables\Actions\BulkAction::make('activar')
                        ->label('Activar Seleccionados')->icon('heroicon-o-check-circle')->color('success')
                        ->action(function ($records) {
                            $records->each->update(['activo' => true]);
                            Notification::make()->title('Ítems Activados')->success()->send();
                        }),

                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar Seleccionados')->icon('heroicon-o-x-circle')->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['activo' => false]);
                            Notification::make()->title('Ítems Desactivados')->warning()->send();
                        }),
                ]),
            ])
            ->defaultSort('codigo', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemCatalogos::route('/'),
            'create' => Pages\CreateItemCatalogo::route('/create'),
            'edit' => Pages\EditItemCatalogo::route('/{record}/edit'),
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
