<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PaaStatsOverview;
use App\Filament\Widgets\PlanesPorAreaChart;
use App\Filament\Widgets\PlanesPorMesChart;
use App\Filament\Widgets\PlanesPorTipoAdquisicionChart;
use App\Filament\Widgets\PlanesValorPorDependenciaChart;
use App\Filament\Widgets\PlanesVinculadosContratoChart;
use App\Models\Area;
use App\Models\Dependencia;
use App\Models\Planadquisicione;
use App\Models\Tipoadquisicione;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\Auth;

class PaaDashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Plan de Adquisiciones';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard Plan de Adquisiciones';
    protected static ?int $navigationSort = 0;
    protected static string $routePath = 'paa-dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->schema([
                    Select::make('vigencia')
                        ->label('Vigencia (Año)')
                        ->options(fn (): array => $this->vigenciaOptions())
                        ->default(static::vigenciaActual())
                        ->selectablePlaceholder(false),
                    Select::make('dependencia_id')
                        ->label('Dependencia')
                        ->options(fn (): array => Dependencia::orderBy('nombre')->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todas'),
                    Select::make('area_id')
                        ->label('Oficina productora (Área)')
                        ->options(fn (): array => Area::orderBy('nombre')->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todas'),
                    Select::make('tipoadquisicione_id')
                        ->label('Tipo de adquisición')
                        ->options(fn (): array => Tipoadquisicione::orderBy('dettipoadquisicion')->pluck('dettipoadquisicion', 'id')->toArray())
                        ->placeholder('Todos'),
                ])
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4]),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            PaaStatsOverview::class,
            PlanesValorPorDependenciaChart::class,
            PlanesPorAreaChart::class,
            PlanesPorTipoAdquisicionChart::class,
            PlanesPorMesChart::class,
            PlanesVinculadosContratoChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'md' => 2, 'xl' => 3];
    }

    /** Años con datos, descendente. Si no hay, el año actual. */
    protected function vigenciaOptions(): array
    {
        $años = Planadquisicione::query()
            ->get(['created_at'])
            ->map(fn ($p) => (int) $p->created_at?->format('Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $opts = $años->mapWithKeys(fn ($y) => [$y => $y])->toArray();

        return $opts ?: [(int) date('Y') => (int) date('Y')];
    }

    /** Año más reciente con datos (default del filtro de vigencia). */
    public static function vigenciaActual(): int
    {
        $latest = Planadquisicione::max('created_at');

        return $latest ? (int) date('Y', strtotime((string) $latest)) : (int) date('Y');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        // Visible para quien tiene permiso de ver planes.
        return $user?->can('view_any_planadquisicione') ?? false;
    }
}
