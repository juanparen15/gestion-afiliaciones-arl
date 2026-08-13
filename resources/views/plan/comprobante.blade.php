<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Plan de Adquisiciones · N° {{ $plan->id_vigencia }}</title>
    <link rel="icon" href="{{ asset('images/actas/logo-alcaldia.png') }}" type="image/png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Colores solo en HEX/RGB (sin oklch) para que html2canvas no falle. */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: #eef2f7;
            color: #1f2937;
            padding: 24px 12px 60px;
            -webkit-font-smoothing: antialiased;
        }
        .barra {
            max-width: 900px; margin: 0 auto 16px; display: flex; gap: 10px; justify-content: flex-end;
        }
        .btn {
            border: 0; border-radius: 8px; padding: 10px 16px; font-size: 14px; font-weight: 600;
            cursor: pointer; color: #ffffff;
        }
        .btn-print { background: #0f2f5f; }
        .btn-png { background: #16a34a; }
        .btn:disabled { opacity: .6; cursor: default; }

        #comprobante {
            max-width: 900px; margin: 0 auto; background: #ffffff; color: #1f2937;
            border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;
        }
        .cab {
            background: #4b5563; color: #ffffff; padding: 20px 28px; display: flex; align-items: center; gap: 16px;
        }
        .cab img { width: 54px; height: 54px; }
        .cab h1 { font-size: 18px; font-weight: 700; }
        .cab p { font-size: 12px; color: #d1d5db; margin-top: 2px; }

        .cuerpo { padding: 22px 28px; }
        .seccion-titulo {
            font-size: 13px; font-weight: 700; color: #0f2f5f; text-transform: uppercase; letter-spacing: .4px;
            border-bottom: 2px solid #e5e7eb; padding-bottom: 6px; margin: 22px 0 14px;
        }
        .desc { font-size: 15px; font-weight: 600; color: #111827; line-height: 1.4; margin-bottom: 6px; }
        .grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px 18px;
        }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .dato .k { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .3px; }
        .dato .v { font-size: 14px; color: #111827; margin-top: 2px; word-break: break-word; }
        .full { grid-column: 1 / -1; }

        .item {
            border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: #f9fafb;
        }
        .pie { text-align: center; font-size: 11px; color: #9ca3af; padding: 14px; border-top: 1px solid #e5e7eb; }

        @media (max-width: 720px) {
            .grid, .grid-3, .item { grid-template-columns: repeat(2, 1fr); }
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .barra { display: none; }
            #comprobante { border: 0; border-radius: 0; max-width: 100%; }
            .item { break-inside: avoid; }
        }
    </style>
</head>
<body>
    @php
        $money = fn ($v) => '$ ' . number_format((float) $v, 0, ',', '.');
        $dur = trim($plan->duracont . ' ' . optional($plan->intervalo)->intervalo);
    @endphp

    <div class="barra">
        <button class="btn btn-png" id="btnPng" onclick="descargarPng()">Descargar PNG</button>
        <button class="btn btn-print" onclick="window.print()">Imprimir / PDF</button>
    </div>

    <div id="comprobante">
        <div class="cab">
            <img src="{{ asset('images/actas/logo-alcaldia.png') }}" alt="Escudo">
            <div>
                <h1>Plan Anual de Adquisiciones</h1>
                <p>Alcaldía de Puerto Boyacá · Comprobante de registro del plan</p>
            </div>
        </div>

        <div class="cuerpo">
            <div class="seccion-titulo">Descripción del Contrato</div>
            <div class="desc">{{ $plan->descripcioncont }}</div>

            <div class="grid" style="margin-top:14px">
                <div class="dato"><div class="k">N° de Registro</div><div class="v">{{ $plan->id_vigencia }}</div></div>
                <div class="dato"><div class="k">Dependencia</div><div class="v">{{ optional($plan->dependencia)->nombre ?? '—' }}</div></div>
                <div class="dato"><div class="k">Área</div><div class="v">{{ optional($plan->area)->nombre ?? '—' }}</div></div>
                <div class="dato"><div class="k">Vigencia</div><div class="v">{{ $plan->vigencia ?? '—' }}</div></div>

                <div class="dato"><div class="k">Código BPIM</div><div class="v">{{ $plan->codbpim ?: '—' }}</div></div>
                <div class="dato"><div class="k">Valor Estimado</div><div class="v">{{ $money($plan->valorestimadocont) }}</div></div>
                <div class="dato"><div class="k">Valor Vigencia</div><div class="v">{{ $money($plan->valorestimadovig) }}</div></div>
                <div class="dato"><div class="k">Duración</div><div class="v">{{ $dur ?: '—' }}</div></div>

                <div class="dato full"><div class="k">Registrado por</div><div class="v">{{ optional($plan->user)->name ?? '—' }}</div></div>
            </div>

            <div class="seccion-titulo">Clasificación del Proceso</div>
            <div class="grid">
                <div class="dato"><div class="k">Tipo de Adquisición</div><div class="v">{{ optional($plan->tipoadquisicione)->dettipoadquisicion ?? '—' }}</div></div>
                <div class="dato"><div class="k">Modalidad</div><div class="v">{{ optional($plan->modalidade)->detmodalidad ?? '—' }}</div></div>
                <div class="dato"><div class="k">Tipo de Zona</div><div class="v">{{ optional($plan->tipozona)->tipozona ?? '—' }}</div></div>
                <div class="dato"><div class="k">Estado Vigencia</div><div class="v">{{ optional($plan->estadovigencia)->detestadovigencia ?? '—' }}</div></div>

                <div class="dato"><div class="k">Vigencia Futura</div><div class="v">{{ optional($plan->vigenfutura)->detvigencia ?? '—' }}</div></div>
                <div class="dato"><div class="k">Fuente</div><div class="v">{{ optional($plan->fuente)->detfuente ?? '—' }}</div></div>
                <div class="dato"><div class="k">Mes de Inicio</div><div class="v">{{ optional($plan->mese)->nommes ?? '—' }}</div></div>
                <div class="dato"><div class="k">Intervalo</div><div class="v">{{ optional($plan->intervalo)->intervalo ?? '—' }}</div></div>

                <div class="dato"><div class="k">Prioridad</div><div class="v">{{ optional($plan->tipoprioridade)->detprioridad ?? '—' }}</div></div>
                <div class="dato"><div class="k">Req. Proyecto</div><div class="v">{{ optional($plan->requiproyecto)->detproyeto ?? '—' }}</div></div>
                <div class="dato"><div class="k">Req. POA-I</div><div class="v">{{ optional($plan->requipoai)->detpoai ?? '—' }}</div></div>
            </div>

            @if ($plan->items->isNotEmpty())
                <div class="seccion-titulo">Clasificación UNSPSC</div>
                @foreach ($plan->items as $item)
                    <div class="item">
                        <div class="dato"><div class="k">Segmento</div><div class="v">{{ $item->segmento_nombre ?? '—' }}</div></div>
                        <div class="dato"><div class="k">Familia</div><div class="v">{{ $item->familia_nombre ?? '—' }}</div></div>
                        <div class="dato"><div class="k">Clase</div><div class="v">{{ $item->clase_nombre ?? '—' }}</div></div>
                        <div class="dato"><div class="k">Producto</div><div class="v">{{ $item->producto_nombre ?? '—' }}</div></div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="pie">Generado el {{ now()->translatedFormat('d \d\e F \d\e Y, g:i a') }} · Alcaldía de Puerto Boyacá</div>
    </div>

    <script>
        async function descargarPng() {
            const btn = document.getElementById('btnPng');
            const el = document.getElementById('comprobante');
            btn.disabled = true; btn.textContent = 'Generando...';
            try {
                const canvas = await html2canvas(el, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    useCORS: true,
                    logging: false,
                    windowWidth: el.scrollWidth,
                    windowHeight: el.scrollHeight,
                });
                const link = document.createElement('a');
                link.download = 'plan-comprobante-{{ $plan->id_vigencia }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (e) {
                alert('No se pudo generar el PNG. Use "Imprimir / PDF".');
                console.error(e);
            } finally {
                btn.disabled = false; btn.textContent = 'Descargar PNG';
            }
        }
    </script>
</body>
</html>
