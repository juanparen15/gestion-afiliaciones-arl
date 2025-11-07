<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Awcodes\Overlook\Widgets\OverlookWidget;
use Illuminate\Support\Facades\Auth;

class AfiliacionesOverlookWidget extends OverlookWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Vista Rápida de Afiliaciones ARL';

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2; // 2 columnas para tarjetas MÁS grandes
    }

    protected function getGrid(): int
    {
        return 2; // Espaciado entre tarjetas
    }

    protected function getCards(): array
    {
        $user = Auth::user();

        // Filtrar por dependencia si no es super_admin o SSST
        $query = Afiliacion::query();
        if (!$user->hasRole(['super_admin', 'SSST'])) {
            if ($user->area_id) {
                $query->where('area_id', $user->area_id);
            } else {
                $query->where('dependencia_id', $user->dependencia_id);
            }
        }

        $total = $query->count();
        $pendientes = (clone $query)->where('estado', 'pendiente')->count();
        $validadas = (clone $query)->where('estado', 'validado')->count();
        $rechazadas = (clone $query)->where('estado', 'rechazado')->count();
        $vigentes = (clone $query)->where('fecha_fin', '>=', now())->count();
        $porVencer = (clone $query)
            ->where('fecha_fin', '>=', now())
            ->where('fecha_fin', '<=', now()->addDays(30))
            ->count();

        // Calcular tendencia (ejemplo simulado)
        $totalAnterior = max($total - 5, 0);
        $tendenciaTotal = $totalAnterior > 0 ? (($total - $totalAnterior) / $totalAnterior) * 100 : 0;

        return [
            $this->makeCard(
                label: 'Total Afiliaciones',
                value: number_format($total),
                icon: 'heroicon-o-user-group',
                iconColor: 'text-blue-500',
                iconSize: 'lg',
                trend: $tendenciaTotal > 0 ? '+' . number_format($tendenciaTotal, 1) . '%' : number_format($tendenciaTotal, 1) . '%',
                trendIcon: $tendenciaTotal >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
                trendColor: $tendenciaTotal >= 0 ? 'text-green-500' : 'text-red-500',
                url: '/admin/afiliacions'
            ),

            $this->makeCard(
                label: 'Pendientes de Validación',
                value: number_format($pendientes),
                icon: 'heroicon-o-clock',
                iconColor: 'text-orange-500',
                iconSize: 'lg',
                description: 'Requieren revisión SSST',
                url: '/admin/afiliacions?tableFilters[estado][value]=pendiente'
            ),

            $this->makeCard(
                label: 'Contratos Vigentes',
                value: number_format($vigentes),
                icon: 'heroicon-o-check-badge',
                iconColor: 'text-green-600',
                iconSize: 'lg',
                description: 'Activos actualmente',
                url: '/admin/afiliacions?tableFilters[vigentes][isActive]=true'
            ),

            $this->makeCard(
                label: 'Por Vencer (30 días)',
                value: number_format($porVencer),
                icon: 'heroicon-o-exclamation-triangle',
                iconColor: $porVencer > 0 ? 'text-orange-600' : 'text-gray-400',
                iconSize: 'lg',
                description: $porVencer > 0 ? 'Requieren atención urgente' : 'Sin contratos próximos a vencer',
                alert: $porVencer > 5,
                url: '/admin/afiliacions?tableFilters[por_vencer][isActive]=true'
            ),
        ];
    }

    protected function makeCard(
        string $label,
        string $value,
        string $icon = 'heroicon-o-document-text',
        string $iconColor = 'text-gray-500',
        string $iconSize = 'lg',
        ?string $description = null,
        ?string $trend = null,
        ?string $trendIcon = null,
        ?string $trendColor = null,
        ?string $url = null,
        bool $alert = false
    ): array {
        $card = [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'iconColor' => $iconColor,
            'iconSize' => $iconSize,
        ];

        if ($description) {
            $card['description'] = $description;
        }

        if ($trend) {
            $card['trend'] = $trend;
            $card['trendIcon'] = $trendIcon ?? 'heroicon-m-arrow-trending-up';
            $card['trendColor'] = $trendColor ?? 'text-green-500';
        }

        if ($url) {
            $card['url'] = $url;
        }

        if ($alert) {
            $card['extraAttributes'] = [
                'class' => 'ring-2 ring-orange-500 dark:ring-orange-400',
            ];
        }

        return $card;
    }
}
