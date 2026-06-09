<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuenteResource\Pages;
use App\Filament\Resources\FuenteResource\RelationManagers;
use App\Models\Fuente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FuenteResource extends Resource
{
    protected static ?string $model = Fuente::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Fuente';
    protected static ?string $pluralModelLabel = 'Fuentes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detfuente')
                ->label('Fuente')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('codigo')
                ->label('Código')
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detfuente')
                    ->label('Fuente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->toggleable(),
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
            'index' => Pages\ListFuentes::route('/'),
            'create' => Pages\CreateFuente::route('/create'),
            'edit' => Pages\EditFuente::route('/{record}/edit'),
        ];
    }
}
