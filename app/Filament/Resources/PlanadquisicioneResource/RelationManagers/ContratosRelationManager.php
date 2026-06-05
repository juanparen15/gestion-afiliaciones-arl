<?php

namespace App\Filament\Resources\PlanadquisicioneResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContratosRelationManager extends RelationManager
{
    protected static string $relationship = 'contratos';

    protected static ?string $title = 'Contratos vinculados';

    public function form(Form $form): Form
    {
        // La edición completa del contrato se hace en ContratoResource.
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_contrato')
            ->columns([
                Tables\Columns\TextColumn::make('numero_contrato')->label('N° Contrato')->searchable(),
                Tables\Columns\TextColumn::make('objeto')->label('Objeto')->limit(50)->tooltip(fn ($record) => $record->objeto),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('fecha_inicio')->label('Inicio')->date(),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['numero_contrato', 'objeto']),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make(),
            ]);
    }
}
