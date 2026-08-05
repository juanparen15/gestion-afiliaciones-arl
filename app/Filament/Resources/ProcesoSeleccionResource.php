<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcesoSeleccionResource\Pages;
use App\Filament\Resources\ProcesoSeleccionResource\RelationManagers;
use App\Models\ProcesoSeleccion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcesoSeleccionResource extends Resource
{
    protected static ?string $model = ProcesoSeleccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Contratación';

    protected static ?string $navigationLabel = 'Procesos de Selección';

    protected static ?string $modelLabel = 'proceso de selección';

    protected static ?string $pluralModelLabel = 'procesos de selección';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('modalidad')
                    ->label('Modalidad')
                    ->options(ProcesoSeleccion::MODALIDADES)
                    ->native(false)->required(),
                Forms\Components\TextInput::make('consecutivo')
                    ->label('Consecutivo (N°)')->maxLength(50),
                Forms\Components\DatePicker::make('fecha')->label('Fecha')->native(false),
                Forms\Components\TextInput::make('estado')->label('Estado')->maxLength(100),
                Forms\Components\Textarea::make('objeto')
                    ->label('Objeto (abreviado)')->rows(2)->columnSpanFull(),
                Forms\Components\Select::make('dependencia_id')
                    ->label('Dependencia')
                    ->relationship('dependencia', 'nombre')
                    ->searchable()->preload(),
                Forms\Components\Select::make('elaborador_id')
                    ->label('Quién elaboró')
                    ->relationship('elaborador', 'nombre')
                    ->searchable()->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')->required()
                            ->dehydrateStateUsing(fn ($s) => mb_strtoupper(trim((string) $s))),
                    ]),
                Forms\Components\TextInput::make('consecutivo_paa')
                    ->label('Consecutivo PAA')
                    ->placeholder('Ej: 2026-221')
                    ->helperText('Formato AÑO-N° Reg. Vincula con el Plan de Adquisiciones.'),
                Forms\Components\Textarea::make('observaciones')
                    ->label('Observaciones')->rows(2)->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consecutivo')->label('N°')->badge()->color('primary')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('modalidad')->label('Modalidad')->badge()
                    ->formatStateUsing(fn ($state) => ProcesoSeleccion::MODALIDADES[$state] ?? $state)
                    ->color('info')->sortable(),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('objeto')->label('Objeto')->limit(45)->tooltip(fn ($record) => $record->objeto)->searchable(),
                Tables\Columns\TextColumn::make('dependencia_texto')->label('Dependencia')->badge()->color('gray')->placeholder('-'),
                Tables\Columns\TextColumn::make('consecutivo_paa')->label('PAA')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('elaborador.nombre')->label('Elaboró')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()->placeholder('-')->toggleable(),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('modalidad')->label('Modalidad')->options(ProcesoSeleccion::MODALIDADES),
                Tables\Filters\SelectFilter::make('dependencia_id')->label('Dependencia')->relationship('dependencia', 'nombre')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('elaborador_id')->label('Elaboró')->relationship('elaborador', 'nombre')->searchable()->preload(),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProcesoSeleccions::route('/'),
            'create' => Pages\CreateProcesoSeleccion::route('/create'),
            'edit' => Pages\EditProcesoSeleccion::route('/{record}/edit'),
        ];
    }
}
