<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudBpim;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class BpimActividadRecienteWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Actividad reciente')
            ->query(SolicitudBpim::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable()->weight('bold')->copyable()->placeholder('-'),
                Tables\Columns\TextColumn::make('nombre_solicitante')->label('Solicitante')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('nombre_proyecto')->label('Proyecto')->searchable()->limit(40)
                    ->tooltip(fn ($record) => $record->nombre_proyecto),
                Tables\Columns\TextColumn::make('valor_cdp')->label('Valor')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('estado')->badge()->color(fn ($record) => $record->estado_color),
                Tables\Columns\IconColumn::make('url_documento_pdf')->label('PDF')->boolean()
                    ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable()->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.solicitud-bpims.edit', $record)),
            ])
            ->paginated([5, 10, 25]);
    }
}
