<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequipoaiResource\Pages;
use App\Filament\Resources\RequipoaiResource\RelationManagers;
use App\Models\Requipoai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequipoaiResource extends Resource
{
    protected static ?string $model = Requipoai::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Requiere POA-I';
    protected static ?string $pluralModelLabel = 'Requiere POA-I';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('detpoai')
                ->label('Requiere POA-I')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('detpoai')
                    ->label('Requiere POA-I')
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
            'index' => Pages\ListRequipoais::route('/'),
            'create' => Pages\CreateRequipoai::route('/create'),
            'edit' => Pages\EditRequipoai::route('/{record}/edit'),
        ];
    }
}
