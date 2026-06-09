@php($items = $getRecord()->items)
<div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; font-size:0.78rem; color:#111827;">
        <thead>
            <tr style="background:#1d4ed8; color:#ffffff;">
                <th style="border:1px solid #d1d5db; padding:6px 10px; text-align:left;">Segmento</th>
                <th style="border:1px solid #d1d5db; padding:6px 10px; text-align:left;">Familia</th>
                <th style="border:1px solid #d1d5db; padding:6px 10px; text-align:left;">Clase</th>
                <th style="border:1px solid #d1d5db; padding:6px 10px; text-align:left;">Producto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
                <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f3f4f6' }};">
                    <td style="border:1px solid #d1d5db; padding:6px 10px;">{{ $item->segmento_nombre ?? '—' }}</td>
                    <td style="border:1px solid #d1d5db; padding:6px 10px;">{{ $item->familia_nombre ?? '—' }}</td>
                    <td style="border:1px solid #d1d5db; padding:6px 10px;">{{ $item->clase_nombre ?? '—' }}</td>
                    <td style="border:1px solid #d1d5db; padding:6px 10px;">{{ $item->producto_nombre ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border:1px solid #d1d5db; padding:8px 10px; text-align:center; color:#6b7280;">Sin clasificaciones UNSPSC</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
