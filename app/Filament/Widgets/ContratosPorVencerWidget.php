<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ContratosPorVencerWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    protected function getTableHeading(): ?string
    {
        return 'Contratos Próximos a Vencer (30 días)';
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $query = Afiliacion::query()
            ->where('fecha_fin', '>=', now())
            ->where('fecha_fin', '<=', now()->addDays(30))
            ->orderBy('fecha_fin', 'asc');

        if (!$user->hasRole('super_admin')) {
            $query->where('dependencia_id', $user->dependencia_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('numero_contrato')
                    ->label('No. Contrato')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre_contratista')
                    ->label('Contratista')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('dependencia.nombre')
                    ->label('Dependencia')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: !$user->hasRole('super_admin')),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->fecha_fin <= now()->addDays(7) ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Días Restantes')
                    ->getStateUsing(fn ($record) => now()->diffInDays($record->fecha_fin))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 7 => 'danger',
                        $state <= 15 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'validado' => 'success',
                        'rechazado' => 'danger',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.admin.resources.afiliacions.edit', $record)),
            ]);
    }
}
