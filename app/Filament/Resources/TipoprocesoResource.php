<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoprocesoResource\Pages;
use App\Filament\Resources\TipoprocesoResource\RelationManagers;
use App\Models\Tipoproceso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TipoprocesoResource extends Resource
{
    protected static ?string $model = Tipoproceso::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Tipo de Proceso';
    protected static ?string $pluralModelLabel = 'Tipos de Proceso';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('dettipoproceso')
                ->label('Tipo de Proceso')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dettipoproceso')
                    ->label('Tipo de Proceso')
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
            'index' => Pages\ListTipoprocesos::route('/'),
            'create' => Pages\CreateTipoproceso::route('/create'),
            'edit' => Pages\EditTipoproceso::route('/{record}/edit'),
        ];
    }
}
