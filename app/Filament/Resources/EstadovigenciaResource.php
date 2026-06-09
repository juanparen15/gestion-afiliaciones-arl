<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstadovigenciaResource\Pages;
use App\Filament\Resources\EstadovigenciaResource\RelationManagers;
use App\Models\Estadovigencia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EstadovigenciaResource extends Resource
{
    protected static ?string $model = Estadovigencia::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Estado Vigencia';
    protected static ?string $pluralModelLabel = 'Estados Vigencia';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detestadovigencia')
                ->label('Estado Vigencia')
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
                Tables\Columns\TextColumn::make('detestadovigencia')
                    ->label('Estado Vigencia')
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
            'index' => Pages\ListEstadovigencias::route('/'),
            'create' => Pages\CreateEstadovigencia::route('/create'),
            'edit' => Pages\EditEstadovigencia::route('/{record}/edit'),
        ];
    }
}
