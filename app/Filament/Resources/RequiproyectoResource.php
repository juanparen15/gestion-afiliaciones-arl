<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequiproyectoResource\Pages;
use App\Filament\Resources\RequiproyectoResource\RelationManagers;
use App\Models\Requiproyecto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequiproyectoResource extends Resource
{
    protected static ?string $model = Requiproyecto::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Requiere Proyecto';
    protected static ?string $pluralModelLabel = 'Requiere Proyecto';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detproyeto')
                ->label('Requiere Proyecto')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detproyeto')
                    ->label('Requiere Proyecto')
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
            'index' => Pages\ListRequiproyectos::route('/'),
            'create' => Pages\CreateRequiproyecto::route('/create'),
            'edit' => Pages\EditRequiproyecto::route('/{record}/edit'),
        ];
    }
}
