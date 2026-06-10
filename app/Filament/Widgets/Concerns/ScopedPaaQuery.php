<?php

namespace App\Filament\Widgets\Concerns;

use App\Filament\Pages\PaaDashboard;
use App\Models\Planadquisicione;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Helper para los widgets del PaaDashboard: aplica el scope por rol del usuario
 * y los filtros de la barra global. Requiere que el widget use
 * Filament\Widgets\Concerns\InteractsWithPageFilters ($this->filters).
 */
trait ScopedPaaQuery
{
    protected function paaFilters(): array
    {
        $filters = $this->filters ?? [];
        // La vigencia siempre se aplica; si la barra aún no la fijó, usar la más reciente.
        $filters['vigencia'] = $filters['vigencia'] ?? PaaDashboard::vigenciaActual();

        return $filters;
    }

    protected function planQuery(): Builder
    {
        return Planadquisicione::query()
            ->visibleTo(Auth::user())
            ->applyDashboardFilters($this->paaFilters());
    }

    protected function vigenciaFiltro(): int
    {
        return (int) $this->paaFilters()['vigencia'];
    }

    /** ¿El usuario ve todos los planes (super_admin/SSST)? */
    protected function veTodo(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('super_admin') || $user?->hasRole('SSST'));
    }
}
