<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClaseResource\Pages;
use App\Filament\Resources\ClaseResource\RelationManagers;
use App\Models\Clase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClaseResource extends Resource
{
    protected static ?string $model = Clase::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Clasificación UNSPSC';
    protected static ?string $modelLabel = 'Clase';
    protected static ?string $pluralModelLabel = 'Clases';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('familia_id')
                ->label('Familia')
                ->relationship('familia', 'detfamilia')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('detclase')
                ->label('Clase')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detclase')
                    ->label('Clase')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('familia.detfamilia')
                    ->label('Familia')
                    ->searchable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListClases::route('/'),
            'create' => Pages\CreateClase::route('/create'),
            'edit' => Pages\EditClase::route('/{record}/edit'),
        ];
    }
}
