<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoadquisicioneResource\Pages;
use App\Filament\Resources\TipoadquisicioneResource\RelationManagers;
use App\Models\Tipoadquisicione;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TipoadquisicioneResource extends Resource
{
    protected static ?string $model = Tipoadquisicione::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Tipo de Adquisición';
    protected static ?string $pluralModelLabel = 'Tipos de Adquisición';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('dettipoadquisicion')
                ->label('Tipo de Adquisición')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dettipoadquisicion')
                    ->label('Tipo de Adquisición')
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
            'index' => Pages\ListTipoadquisiciones::route('/'),
            'create' => Pages\CreateTipoadquisicione::route('/create'),
            'edit' => Pages\EditTipoadquisicione::route('/{record}/edit'),
        ];
    }
}
