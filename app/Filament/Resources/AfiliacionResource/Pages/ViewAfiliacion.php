<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Filament\Resources\AfiliacionResource;
use App\Models\Afiliacion;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewAfiliacion extends ViewRecord
{
    protected static string $resource = AfiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(fn(Afiliacion $record) => $record->estado !== 'validado' || Auth::user()->hasRole(['super_admin', 'SSST'])),
        ];
    }
}
