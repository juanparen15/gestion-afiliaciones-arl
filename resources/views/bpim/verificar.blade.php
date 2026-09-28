@php
    $valido = $verificacion && $verificacion->activo && $verificacion->solicitud;
    $solicitud = $valido ? $verificacion->solicitud : null;

    if ($valido) {
        $accent = '#16a34a'; $accentSoft = 'rgba(22,163,74,.12)'; $accentBorder = 'rgba(21,128,61,.35)';
        $titulo = 'Documento Verificado';
        $sub = 'Este documento fue generado por el Sistema de Gestión de la Alcaldía Municipal de Puerto Boyacá y su autenticidad ha sido confirmada.';
    } else {
        $accent = '#dc2626'; $accentSoft = 'rgba(220,38,38,.12)'; $accentBorder = 'rgba(185,28,28,.35)';
        $titulo = 'Documento no válido';
        $sub = 'No se encontró una solicitud BPIM auténtica asociada a este código de verificación.';
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Autenticidad BPIM · Alcaldía de Puerto Boyacá</title>
    <link rel="icon" href="{{ asset('images/actas/logo-alcaldia.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .bv-hero { position:relative; overflow:hidden; border-radius:1.375rem; padding:2.5rem 1.5rem 2.25rem; text-align:center; background:#fff; border:1px solid rgba(0,0,0,.06); box-shadow:0 4px 24px rgba(0,0,0,.06); }
        .section-card { background:rgba(255,255,255,.9); border-radius:1rem; border:1px solid rgba(0,0,0,.08); overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .section-head { border-bottom:1px solid rgba(0,0,0,.06); padding:.625rem 1.25rem; display:flex; align-items:center; gap:.5rem; }
        .section-head-label { font-size:.575rem; font-weight:700; color:{{ $accent }}; text-transform:uppercase; letter-spacing:.12em; opacity:.9; }
        .field-label { font-size:.6875rem; font-weight:500; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.2rem; }
        .field-value { font-size:.875rem; font-weight:600; color:#0f172a; }
        .mono { font-family:'SF Mono','Fira Code','Courier New',monospace; }
        .bv-rule { display:flex; align-items:center; gap:.75rem; }
        .bv-rule-line { flex:1; height:1px; background:#e2e8f0; }
        .bv-rule-label { font-size:.625rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; white-space:nowrap; color:#94a3b8; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen antialiased">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/actas/logo-alcaldia.png') }}" alt="Alcaldía de Puerto Boyacá" class="h-9 w-auto">
                <span class="text-sm font-bold text-gray-700 leading-tight">Alcaldía de<br>Puerto Boyacá</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                Portal de Verificación BPIM
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 py-6 space-y-4">

        <div class="bv-hero">
            <div style="position:relative;z-index:2;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;margin-bottom:1.125rem;background:{{ $accentSoft }};border:1.5px solid {{ $accentBorder }};">
                    @if($valido)
                        <lord-icon src="https://cdn.lordicon.com/hcdqguwh.json" trigger="loop" state="loop-cycle" stroke="bold" colors="primary:{{ $accent }},secondary:{{ $accent }}" style="width:52px;height:52px;"></lord-icon>
                    @else
                        <lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop" delay="800" stroke="bold" colors="primary:{{ $accent }},secondary:{{ $accent }}" style="width:48px;height:48px;"></lord-icon>
                    @endif
                </div>
                <p style="color:#92710d;font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;margin:0 0 .4rem;">Verificador de Autenticidad</p>
                <h1 style="color:#0f172a;font-size:1.5rem;font-weight:700;letter-spacing:-.02em;line-height:1.25;margin:0 0 .875rem;">{{ $titulo }}</h1>
                <p style="color:#334155;font-size:.875rem;font-weight:500;line-height:1.65;margin:0 auto;max-width:440px;">{{ $sub }}</p>
            </div>
        </div>

        @if($valido)
            <div class="bv-rule">
                <div class="bv-rule-line"></div>
                <span class="bv-rule-label">Información de la solicitud</span>
                <div class="bv-rule-line"></div>
            </div>

            <div class="section-card">
                <div class="section-head">
                    <span class="section-head-label">Solicitud BPIM</span>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-4">
                    <div>
                        <p class="field-label">Código</p>
                        <span class="inline-block px-2.5 py-0.5 text-xs font-bold rounded-full border" style="background:{{ $accentSoft }};color:{{ $accent }};border-color:{{ $accentBorder }};">{{ $solicitud->codigo }}</span>
                    </div>
                    <div>
                        <p class="field-label">Estado</p>
                        <p class="field-value" style="color:{{ $accent }};">Aprobado</p>
                    </div>
                    <div class="col-span-2">
                        <p class="field-label">Fecha de firma</p>
                        <p class="field-value">{{ optional($verificacion->fecha_firma)->format('d/m/Y H:i') ?: '-' }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="field-label">Dependencia</p>
                        <p class="field-value">{{ $solicitud->dependencia }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="field-label">Proyecto</p>
                        <p class="field-value">{{ $solicitud->nombre_proyecto }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="field-label">Objeto</p>
                        <p class="field-value" style="font-weight:500;">{{ $solicitud->objeto }}</p>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-head">
                    <span class="section-head-label">Integridad del Documento</span>
                </div>
                <div class="p-5 space-y-3">
                    <div class="rounded-xl p-3" style="background:rgba(0,0,0,.03);border:1px solid rgba(0,0,0,.06);">
                        <p class="field-label">Código de verificación</p>
                        <p class="text-xs mono break-all leading-relaxed mt-1" style="color:#334155;">{{ $codigo }}</p>
                    </div>
                    <div class="rounded-xl p-3" style="background:rgba(0,0,0,.03);border:1px solid rgba(0,0,0,.06);">
                        <p class="field-label">Firmado por</p>
                        <p class="field-value mt-1">{{ $verificacion->firmado_por }}</p>
                    </div>
                </div>
            </div>
        @endif

    </main>

    <footer class="max-w-2xl mx-auto px-4 sm:px-6 pb-10 pt-4 text-center">
        <p class="text-xs leading-relaxed" style="color:#94a3b8;">
            Verificación provista por la <span class="font-medium" style="color:{{ $accent }};">Alcaldía Municipal de Puerto Boyacá</span> · Sistema de Gestión<br>
            Este servicio permite validar la autenticidad de las solicitudes BPIM emitidas.
        </p>
    </footer>
</body>
</html>
