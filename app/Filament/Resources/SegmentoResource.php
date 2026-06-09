<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SegmentoResource\Pages;
use App\Filament\Resources\SegmentoResource\RelationManagers;
use App\Models\Segmento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SegmentoResource extends Resource
{
    protected static ?string $model = Segmento::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Clasificación UNSPSC';
    protected static ?string $modelLabel = 'Segmento';
    protected static ?string $pluralModelLabel = 'Segmentos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detsegmento')
                ->label('Segmento')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detsegmento')
                    ->label('Segmento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('familias_count')
                    ->counts('familias')
                    ->label('Familias')
                    ->badge(),
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
            'index' => Pages\ListSegmentos::route('/'),
            'create' => Pages\CreateSegmento::route('/create'),
            'edit' => Pages\EditSegmento::route('/{record}/edit'),
        ];
    }
}
