<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntervaloResource\Pages;
use App\Filament\Resources\IntervaloResource\RelationManagers;
use App\Models\Intervalo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IntervaloResource extends Resource
{
    protected static ?string $model = Intervalo::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Intervalo';
    protected static ?string $pluralModelLabel = 'Intervalos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('intervalo')
                ->label('Intervalo')
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
                Tables\Columns\TextColumn::make('intervalo')
                    ->label('Intervalo')
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
            'index' => Pages\ListIntervalos::route('/'),
            'create' => Pages\CreateIntervalo::route('/create'),
            'edit' => Pages\EditIntervalo::route('/{record}/edit'),
        ];
    }
}
