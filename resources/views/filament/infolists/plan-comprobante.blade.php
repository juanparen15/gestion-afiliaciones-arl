@php
    $p = $getRecord();
    $val = fn ($v) => ($v === null || $v === '') ? '—' : $v;
    $anio = optional($p->created_at)->format('Y');
@endphp

<div id="comprobante-plan"
     style="background:#ffffff; color:#1f2937; padding:28px 32px; border-radius:10px; border:1px solid #e5e7eb; font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; max-width:1000px; margin:0 auto;">

    {{-- Encabezado --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; border-bottom:3px solid #1d4ed8; padding-bottom:14px; margin-bottom:18px;">
        <div>
            <div style="font-size:1.15rem; font-weight:800; color:#1d4ed8; letter-spacing:.02em;">ALCALDÍA DE PUERTO BOYACÁ</div>
            <div style="font-size:.85rem; color:#6b7280; margin-top:2px;">Plan Anual de Adquisiciones · Comprobante de registro</div>
        </div>
        <div style="text-align:right; min-width:140px;">
            <div style="font-size:.7rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">N° de Registro</div>
            <div style="font-size:1.6rem; font-weight:800; color:#1d4ed8; line-height:1;">{{ $val($p->id_vigencia) }}</div>
            <div style="font-size:.8rem; color:#374151; margin-top:2px;">Vigencia {{ $anio }}</div>
        </div>
    </div>

    {{-- Descripción --}}
    <div style="margin-bottom:16px;">
        <div style="font-size:.68rem; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px;">Descripción del Contrato</div>
        <div style="font-size:.95rem; font-weight:600; color:#111827; line-height:1.35;">{{ $p->descripcioncont }}</div>
    </div>

    {{-- Datos generales --}}
    @php
        $datos = [
            ['Dependencia', $p->dependencia?->nombre],
            ['Área', $p->area?->nombre],
            ['Código BPIM', $p->codbpim],
            ['Registrado por', $p->user?->name],
            ['Valor Estimado', $p->valorestimadocont ? '$ ' . $p->valorestimadocont : null],
            ['Valor Vigencia', $p->valorestimadovig ? '$ ' . $p->valorestimadovig : null],
            ['Duración (meses)', $p->duracont],
            ['Fecha de registro', optional($p->created_at)->format('d/m/Y')],
        ];
    @endphp
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px 18px; margin-bottom:20px;">
        @foreach ($datos as [$label, $value])
            <div>
                <div style="font-size:.66rem; color:#6b7280; text-transform:uppercase; letter-spacing:.03em;">{{ $label }}</div>
                <div style="font-size:.88rem; color:#111827; font-weight:500; margin-top:1px;">{{ $val($value) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Clasificación del proceso --}}
    <div style="font-size:.8rem; font-weight:700; color:#1d4ed8; border-bottom:1px solid #e5e7eb; padding-bottom:5px; margin-bottom:12px; text-transform:uppercase; letter-spacing:.03em;">Clasificación del Proceso</div>
    @php
        $clasif = [
            ['Tipo de Adquisición', $p->tipoadquisicione?->dettipoadquisicion],
            ['Modalidad', $p->modalidade?->detmodalidad],
            ['Tipo de Zona', $p->tipozona?->tipozona],
            ['Estado Vigencia', $p->estadovigencia?->detestadovigencia],
            ['Vigencia Futura', $p->vigenfutura?->detvigencia],
            ['Fuente', $p->fuente?->detfuente],
            ['Mes de Inicio', $p->mese?->nommes],
            ['Intervalo', $p->intervalo?->intervalo],
            ['Prioridad', $p->tipoprioridade?->detprioridad],
            ['Req. Proyecto', $p->requiproyecto?->detproyeto],
            ['Req. POA-I', $p->requipoai?->detpoai],
            ['Tipo de Proceso', $p->tipoproceso?->dettipoproceso],
        ];
    @endphp
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px 18px; margin-bottom:22px;">
        @foreach ($clasif as [$label, $value])
            <div>
                <div style="font-size:.66rem; color:#6b7280; text-transform:uppercase; letter-spacing:.03em;">{{ $label }}</div>
                <div style="font-size:.86rem; color:#111827; margin-top:1px;">{{ $val($value) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Clasificación UNSPSC --}}
    <div style="font-size:.8rem; font-weight:700; color:#1d4ed8; border-bottom:1px solid #e5e7eb; padding-bottom:5px; margin-bottom:10px; text-transform:uppercase; letter-spacing:.03em;">Clasificación UNSPSC</div>
    <table style="width:100%; border-collapse:collapse; font-size:.78rem; color:#1f2937;">
        <thead>
            <tr style="background:#1d4ed8; color:#ffffff;">
                <th style="border:1px solid #cbd5e1; padding:7px 10px; text-align:left;">Segmento</th>
                <th style="border:1px solid #cbd5e1; padding:7px 10px; text-align:left;">Familia</th>
                <th style="border:1px solid #cbd5e1; padding:7px 10px; text-align:left;">Clase</th>
                <th style="border:1px solid #cbd5e1; padding:7px 10px; text-align:left;">Producto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($p->items as $i => $item)
                <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f1f5f9' }};">
                    <td style="border:1px solid #e2e8f0; padding:7px 10px; vertical-align:top;">{{ $item->segmento_nombre ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0; padding:7px 10px; vertical-align:top;">{{ $item->familia_nombre ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0; padding:7px 10px; vertical-align:top;">{{ $item->clase_nombre ?? '—' }}</td>
                    <td style="border:1px solid #e2e8f0; padding:7px 10px; vertical-align:top;">{{ $item->producto_nombre ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border:1px solid #e2e8f0; padding:10px; text-align:center; color:#6b7280;">Sin clasificaciones UNSPSC registradas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pie --}}
    <div style="margin-top:18px; padding-top:10px; border-top:1px solid #e5e7eb; font-size:.7rem; color:#9ca3af; text-align:right;">
        Generado el {{ now()->format('d/m/Y H:i') }} · Sistema de Gestión — Alcaldía de Puerto Boyacá
    </div>
</div>
