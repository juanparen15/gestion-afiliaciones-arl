<?php

namespace App\Filament\Resources\PlanadquisicioneResource\Pages;

use App\Exports\PlanadquisicioneSecopExport;
use App\Filament\Resources\PlanadquisicioneResource;
use App\Models\Planadquisicione;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ListPlanadquisiciones extends ListRecords
{
    protected static string $resource = PlanadquisicioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarSecop')
                ->label('Exportar a SECOP II')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => Auth::user()?->hasRole('super_admin') ?? false)
                ->form([
                    Select::make('vigencia')
                        ->label('Vigencia (Año)')
                        ->options(function (): array {
                            $driver = DB::getDriverName();
                            $yearExpr = $driver === 'sqlite'
                                ? "CAST(strftime('%Y', created_at) AS INTEGER)"
                                : 'YEAR(created_at)';

                            $años = Planadquisicione::selectRaw("{$yearExpr} as year")
                                ->distinct()->orderBy('year', 'desc')->pluck('year', 'year')->toArray();

                            return $años ?: [date('Y') => date('Y')];
                        })
                        ->default((int) date('Y'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $vigencia = (int) $data['vigencia'];

                    return Excel::download(
                        new PlanadquisicioneSecopExport($vigencia),
                        "plan-adquisiciones-secop-{$vigencia}.xlsx"
                    );
                }),

            Actions\CreateAction::make(),
        ];
    }
}
