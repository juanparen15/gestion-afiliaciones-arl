<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamiliaResource\Pages;
use App\Filament\Resources\FamiliaResource\RelationManagers;
use App\Models\Familia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamiliaResource extends Resource
{
    protected static ?string $model = Familia::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Clasificación UNSPSC';
    protected static ?string $modelLabel = 'Familia';
    protected static ?string $pluralModelLabel = 'Familias';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('segmento_id')
                ->label('Segmento')
                ->relationship('segmento', 'detsegmento')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('detfamilia')
                ->label('Familia')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detfamilia')
                    ->label('Familia')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('segmento.detsegmento')
                    ->label('Segmento')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('segmento_id')
                    ->label('Segmento')
                    ->relationship('segmento', 'detsegmento')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListFamilias::route('/'),
            'create' => Pages\CreateFamilia::route('/create'),
            'edit' => Pages\EditFamilia::route('/{record}/edit'),
        ];
    }
}
