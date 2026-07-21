<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Acta de Necesidad — Alcaldía de Puerto Boyacá</title>
    <style>
        :root {
            --verde: #16a34a; --rojo: #dc2626; --amarillo: #d97706; --gris: #6b7280;
            --tinta: #0f172a; --borde: #e5e7eb; --fondo: #f1f5f9; --panel: #ffffff;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: var(--fondo); color: var(--tinta); line-height: 1.55; padding: 24px; }
        .wrap { max-width: 640px; margin: 0 auto; }
        .brand { display: flex; align-items: center; gap: 14px; justify-content: center; margin: 12px 0 22px; }
        .brand img { height: 56px; width: auto; }
        .brand h1 { font-size: 16px; font-weight: 700; letter-spacing: .3px; text-align: center; }
        .card { background: var(--panel); border: 1px solid var(--borde); border-radius: 16px;
            box-shadow: 0 10px 30px rgba(2,6,23,.06); overflow: hidden; }
        .banner { padding: 28px 24px; text-align: center; color: #fff; }
        .banner.ok { background: linear-gradient(135deg, #16a34a, #15803d); }
        .banner.bad { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .banner.warn { background: linear-gradient(135deg, #d97706, #b45309); }
        .banner .ico { width: 60px; height: 60px; margin: 0 auto 10px; display: block; }
        .banner h2 { font-size: 20px; font-weight: 800; }
        .banner p { font-size: 13.5px; opacity: .92; margin-top: 4px; }
        .body { padding: 22px 24px 26px; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 11px 0; border-bottom: 1px solid var(--borde); }
        .row:last-child { border-bottom: 0; }
        .row .k { color: var(--gris); font-size: 13px; font-weight: 600; flex: 0 0 42%; }
        .row .v { text-align: right; font-size: 13.5px; font-weight: 600; }
        .pill { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .pill.ok { background: #dcfce7; color: #166534; }
        .pill.warn { background: #ffedd5; color: #9a3412; }
        .foot { text-align: center; color: var(--gris); font-size: 12px; margin-top: 18px; }
        .code { font-family: ui-monospace, monospace; font-size: 12.5px; letter-spacing: 1px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <img src="{{ asset('images/actas/logo-alcaldia.png') }}" alt="Alcaldía de Puerto Boyacá">
        <h1>Alcaldía Municipal de<br>Puerto Boyacá — Boyacá</h1>
    </div>

    @php
        $valido = $acta && $acta->estado === 'aprobado';
        $anulado = $acta && $acta->estado === 'anulado';
    @endphp

    <div class="card">
        @if($valido)
            <div class="banner ok">
                <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <h2>Documento verificado</h2>
                <p>Este acta de necesidad es auténtica y fue emitida oficialmente.</p>
            </div>
        @elseif($anulado)
            <div class="banner warn">
                <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                <h2>Acta anulada</h2>
                <p>Este documento existió pero fue anulado y ya no es válido.</p>
            </div>
        @else
            <div class="banner bad">
                <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <h2>Documento no válido</h2>
                <p>No se encontró un acta de necesidad auténtica con este código.</p>
            </div>
        @endif

        @if($acta && ($valido || $anulado))
            <div class="body">
                <div class="row"><span class="k">No. Acta</span><span class="v">0{{ $acta->consecutivo }}</span></div>
                <div class="row"><span class="k">Solicitante</span><span class="v">{{ $acta->nombre_solicitante }}</span></div>
                <div class="row"><span class="k">Dependencia</span><span class="v">{{ $acta->dependencia_texto }}{{ $acta->area_texto ? ' - '.$acta->area_texto : '' }}</span></div>
                <div class="row"><span class="k">Objeto</span><span class="v">{{ \Illuminate\Support\Str::limit($acta->objeto_contrato, 90) }}</span></div>
                <div class="row"><span class="k">Fecha</span><span class="v">{{ optional($acta->fecha_solicitud)->format('d/m/Y') }}</span></div>
                <div class="row"><span class="k">Estado</span><span class="v">
                    <span class="pill {{ $valido ? 'ok' : 'warn' }}">{{ ucfirst($acta->estado) }}</span>
                </span></div>
                <div class="row"><span class="k">Código</span><span class="v code">{{ $codigo }}</span></div>
            </div>
        @endif
    </div>

    <p class="foot">Verificación oficial — Sistema de Gestión, Alcaldía de Puerto Boyacá.<br>Este servicio permite validar la autenticidad de las actas de necesidad emitidas.</p>
</div>
</body>
</html>
