<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeseResource\Pages;
use App\Filament\Resources\MeseResource\RelationManagers;
use App\Models\Mese;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MeseResource extends Resource
{
    protected static ?string $model = Mese::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Mes';
    protected static ?string $pluralModelLabel = 'Meses';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nommes')
                ->label('Mes')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nommes')
                    ->label('Mes')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListMeses::route('/'),
            'create' => Pages\CreateMese::route('/create'),
            'edit' => Pages\EditMese::route('/{record}/edit'),
        ];
    }
}
