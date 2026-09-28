@php
    $proyecto = $getRecord();
    $itemsResumen = [];

    foreach ($proyecto->capitulos as $capitulo) {
        foreach ($capitulo->items as $item) {
            if ($item->item_catalogo_id) {
                if (! isset($itemsResumen[$item->item_catalogo_id])) {
                    $itemsResumen[$item->item_catalogo_id] = [
                        'item_catalogo' => $item->itemCatalogo,
                        'usos' => 0,
                        'cantidad_total' => 0,
                        'valor_solicitado' => 0,
                        'valor_catalogo' => $item->itemCatalogo?->valor_unitario * $item->itemCatalogo?->cantidad ?? 0,
                        'valor_disponible' => $item->itemCatalogo?->valor_restante ?? 0,
                    ];
                }

                $itemsResumen[$item->item_catalogo_id]['usos']++;
                $itemsResumen[$item->item_catalogo_id]['cantidad_total'] += $item->cantidad;
                $itemsResumen[$item->item_catalogo_id]['valor_solicitado'] += $item->valor_total;
            }
        }
    }
@endphp

@if (count($itemsResumen) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ítem del Catálogo</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usos en Proyecto</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad Total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor Solicitado</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor Catálogo</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Disponible</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($itemsResumen as $resumen)
                    @php
                        $itemCatalogo = $resumen['item_catalogo'];
                        $porcentajeUso = $resumen['valor_catalogo'] > 0
                            ? ($resumen['valor_solicitado'] / $resumen['valor_catalogo']) * 100
                            : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $itemCatalogo->codigo ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            <div class="font-semibold">{{ $itemCatalogo->nombre ?? 'Sin nombre' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $itemCatalogo->unidad ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $resumen['usos'] }} {{ $resumen['usos'] == 1 ? 'vez' : 'veces' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100">
                            {{ number_format($resumen['cantidad_total'], 2) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                            ${{ number_format($resumen['valor_solicitado'], 2, '.', ',') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                            ${{ number_format($resumen['valor_catalogo'], 2, '.', ',') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold {{ $resumen['valor_disponible'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            ${{ number_format($resumen['valor_disponible'], 2, '.', ',') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                            @if ($resumen['valor_disponible'] < 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Excedido</span>
                            @elseif ($porcentajeUso >= 90)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">Crítico</span>
                            @elseif ($porcentajeUso >= 70)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Alerta</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Normal</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">TOTAL:</td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                        ${{ number_format(collect($itemsResumen)->sum('valor_solicitado'), 2, '.', ',') }}
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p>No hay ítems del catálogo utilizados en este proyecto.</p>
    </div>
@endif
