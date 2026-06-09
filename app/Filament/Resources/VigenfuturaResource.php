<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VigenfuturaResource\Pages;
use App\Filament\Resources\VigenfuturaResource\RelationManagers;
use App\Models\Vigenfutura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VigenfuturaResource extends Resource
{
    protected static ?string $model = Vigenfutura::class;
    protected static ?string $navigationIcon = 'heroicon-o-forward';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Vigencia Futura';
    protected static ?string $pluralModelLabel = 'Vigencias Futuras';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detvigencia')
                ->label('Vigencia Futura')
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
                Tables\Columns\TextColumn::make('detvigencia')
                    ->label('Vigencia Futura')
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
            'index' => Pages\ListVigenfuturas::route('/'),
            'create' => Pages\CreateVigenfutura::route('/create'),
            'edit' => Pages\EditVigenfutura::route('/{record}/edit'),
        ];
    }
}
