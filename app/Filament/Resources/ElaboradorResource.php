<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElaboradorResource\Pages;
use App\Filament\Resources\ElaboradorResource\RelationManagers;
use App\Models\Elaborador;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ElaboradorResource extends Resource
{
    protected static ?string $model = Elaborador::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Contratación';

    protected static ?string $navigationLabel = 'Equipo de Contratación';

    protected static ?string $modelLabel = 'elaborador';

    protected static ?string $pluralModelLabel = 'elaboradores';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => mb_strtoupper(trim((string) $state)))
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('cargo')
                    ->label('Cargo (opcional)')->maxLength(255),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cargo')->label('Cargo')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('procesos_count')->counts('procesos')->label('Procesos')->badge(),
                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean(),
            ])
            ->defaultSort('nombre')
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListElaboradors::route('/'),
            'create' => Pages\CreateElaborador::route('/create'),
            'edit' => Pages\EditElaborador::route('/{record}/edit'),
        ];
    }
}
