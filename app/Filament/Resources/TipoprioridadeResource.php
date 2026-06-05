<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoprioridadeResource\Pages;
use App\Filament\Resources\TipoprioridadeResource\RelationManagers;
use App\Models\Tipoprioridade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TipoprioridadeResource extends Resource
{
    protected static ?string $model = Tipoprioridade::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Tipo de Prioridad';
    protected static ?string $pluralModelLabel = 'Tipos de Prioridad';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detprioridad')
                ->label('Tipo de Prioridad')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detprioridad')
                    ->label('Tipo de Prioridad')
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
            'index' => Pages\ListTipoprioridades::route('/'),
            'create' => Pages\CreateTipoprioridade::route('/create'),
            'edit' => Pages\EditTipoprioridade::route('/{record}/edit'),
        ];
    }
}
