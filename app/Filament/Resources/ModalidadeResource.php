<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModalidadeResource\Pages;
use App\Filament\Resources\ModalidadeResource\RelationManagers;
use App\Models\Modalidade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModalidadeResource extends Resource
{
    protected static ?string $model = Modalidade::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Modalidad';
    protected static ?string $pluralModelLabel = 'Modalidades';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detmodalidad')
                ->label('Modalidad')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('codigo')
                ->label('Código'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detmodalidad')
                    ->label('Modalidad')
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
            'index' => Pages\ListModalidades::route('/'),
            'create' => Pages\CreateModalidade::route('/create'),
            'edit' => Pages\EditModalidade::route('/{record}/edit'),
        ];
    }
}
