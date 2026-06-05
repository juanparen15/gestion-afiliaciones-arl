<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipozonaResource\Pages;
use App\Filament\Resources\TipozonaResource\RelationManagers;
use App\Models\Tipozona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TipozonaResource extends Resource
{
    protected static ?string $model = Tipozona::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Tipo de Zona';
    protected static ?string $pluralModelLabel = 'Tipos de Zona';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('tipozona')
                ->label('Tipo de Zona')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipozona')
                    ->label('Tipo de Zona')
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
            'index' => Pages\ListTipozonas::route('/'),
            'create' => Pages\CreateTipozona::route('/create'),
            'edit' => Pages\EditTipozona::route('/{record}/edit'),
        ];
    }
}
