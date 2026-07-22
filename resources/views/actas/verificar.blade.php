@php
    $heroId = 'hv_' . substr(md5('acta-verificacion'), 0, 8);

    $valido  = $acta && $acta->estado === 'aprobado';
    $anulado = $acta && $acta->estado === 'anulado';

    if ($valido) {
        $accent = '#16a34a'; $accentSoft = 'rgba(22,163,74,.12)'; $accentBorder = 'rgba(21,128,61,.35)';
        $titulo = 'Documento Verificado';
        $sub = 'Este documento fue generado por el Sistema de Gestión de la Alcaldía Municipal de Puerto Boyacá y su autenticidad ha sido confirmada.';
    } elseif ($anulado) {
        $accent = '#d97706'; $accentSoft = 'rgba(217,119,6,.12)'; $accentBorder = 'rgba(180,83,9,.35)';
        $titulo = 'Acta Anulada';
        $sub = 'Este documento existió y fue emitido, pero posteriormente fue anulado y ya no es válido.';
    } else {
        $accent = '#dc2626'; $accentSoft = 'rgba(220,38,38,.12)'; $accentBorder = 'rgba(185,28,28,.35)';
        $titulo = 'Documento no válido';
        $sub = 'No se encontró un acta de necesidad auténtica asociada a este código de verificación.';
    }

    $emberColors = ['201,168,76', '230,180,60', '210,150,30', '180,140,40', '235,190,70'];
    $embers = [];
    for ($i = 0; $i < 26; $i++) {
        $c = $emberColors[array_rand($emberColors)];
        $sz = round(mt_rand(15, 42) / 10, 1);
        $embers[] = ['x' => mt_rand(2, 98), 'sz' => $sz, 'c' => $c, 'g' => (int)(($sz * mt_rand(25, 50)) / 10),
            'dur' => round(mt_rand(35, 75) / 10, 1), 'del' => round(mt_rand(0, 90) / 10, 1), 'drift' => mt_rand(-40, 40)];
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Autenticidad · Alcaldía de Puerto Boyacá</title>
    <link rel="icon" href="{{ asset('images/actas/logo-alcaldia.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        @keyframes bv-up { from { opacity:0; transform:translateY(20px) } to { opacity:1; transform:translateY(0) } }
        @keyframes bv-pop { from { opacity:0; transform:scale(.55) } to { opacity:1; transform:scale(1) } }
        @keyframes bv-glow { 0%,100% { box-shadow:0 0 0 0 {{ $accentSoft }} } 65% { box-shadow:0 0 0 14px rgba(201,168,76,0) } }
        @keyframes bv-float-a { 0%{transform:translate(0,0) scale(1)} 25%{transform:translate(-22px,18px) scale(1.08)} 55%{transform:translate(14px,-14px) scale(.94)} 80%{transform:translate(-8px,22px) scale(1.04)} 100%{transform:translate(0,0) scale(1)} }
        @keyframes bv-float-b { 0%{transform:translate(0,0) scale(1)} 30%{transform:translate(18px,-22px) scale(1.12)} 60%{transform:translate(-12px,10px) scale(.88)} 85%{transform:translate(8px,-14px) scale(1.06)} 100%{transform:translate(0,0) scale(1)} }
        @keyframes bv-ember-rise { 0%{transform:translateY(0) translateX(0) scale(1);opacity:0} 8%{opacity:.9} 80%{opacity:.4} 100%{transform:translateY(-300px) translateX(var(--drift,20px)) scale(.2);opacity:0} }
        .bv-a1 { animation:bv-up .6s cubic-bezier(.16,1,.3,1) both }
        .bv-icon-ring { animation: bv-pop .7s .08s cubic-bezier(.34,1.56,.64,1) both, bv-glow 3s 1.2s ease-in-out infinite; }
        .bv-hero { position:relative; overflow:hidden; border-radius:1.375rem; padding:2.5rem 1.5rem 2.25rem; text-align:center; background:#fff; border:1px solid rgba(0,0,0,.06); box-shadow:0 4px 24px rgba(0,0,0,.06); }
        .bv-hero-overlay { position:absolute; inset:0; pointer-events:none; z-index:1; background:radial-gradient(ellipse 72% 80% at 50% 45%, rgba(255,255,255,.68) 0%, rgba(255,255,255,.35) 55%, transparent 100%); }
        .bv-fire-base { position:absolute; inset:0; pointer-events:none; z-index:0; background:radial-gradient(ellipse 85% 55% at 50% 100%, rgba(201,168,76,.20) 0%, rgba(230,190,90,.10) 50%, transparent 100%); }
        .bv-orb-a { animation: bv-float-a 11s ease-in-out infinite; }
        .bv-orb-b { animation: bv-float-b 14s ease-in-out infinite; }
        .bv-ember { position:absolute; border-radius:50%; pointer-events:none; bottom:-4px; will-change:transform,opacity; animation: bv-ember-rise var(--dur,5s) var(--del,0s) ease-in infinite; }
        .bv-rule { display:flex; align-items:center; gap:.75rem; }
        .bv-rule-line { flex:1; height:1px; background:#e2e8f0; }
        .bv-rule-label { font-size:.625rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; white-space:nowrap; color:#94a3b8; }
        .section-card { background:rgba(255,255,255,.9); border-radius:1rem; border:1px solid rgba(0,0,0,.08); overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); position:relative; transition:transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s ease; }
        .section-card::before { content:''; position:absolute; top:0; left:0; right:0; height:2.5px; background:var(--card-accent,{{ $accent }}); transform:scaleX(0); transform-origin:left; transition:transform .28s ease; z-index:1; }
        .section-card:hover::before { transform:scaleX(1); }
        .section-head { border-bottom:1px solid rgba(0,0,0,.06); padding:.625rem 1.25rem; display:flex; align-items:center; gap:.5rem; }
        .section-head-label { font-size:.575rem; font-weight:700; color:{{ $accent }}; text-transform:uppercase; letter-spacing:.12em; opacity:.9; }
        .field-label { font-size:.6875rem; font-weight:500; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.2rem; }
        .field-value { font-size:.875rem; font-weight:600; color:#0f172a; }
        .mono { font-family:'SF Mono','Fira Code','Courier New',monospace; }
        @media(prefers-reduced-motion:reduce) { .bv-a1,.bv-icon-ring,.bv-orb-a,.bv-orb-b,.bv-ember,.section-card { animation:none; transition:none; opacity:.95; transform:none; } }
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
                <lord-icon src="https://cdn.lordicon.com/fihkmkwt.json" trigger="loop" delay="3000" stroke="bold" colors="primary:#6b7280,secondary:#9ca3af" style="width:15px;height:15px;"></lord-icon>
                Portal de Verificación
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 py-6 space-y-4">

        <div class="bv-hero bv-a1" id="{{ $heroId }}">
            <canvas id="{{ $heroId }}_canvas" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:.4;"></canvas>
            <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
                <div class="bv-orb-a" style="position:absolute;width:280px;height:280px;top:-70px;right:-50px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.28),transparent 70%);filter:blur(28px);"></div>
                <div class="bv-orb-b" style="position:absolute;width:200px;height:200px;bottom:-50px;left:-40px;border-radius:50%;background:radial-gradient(circle,rgba(201,140,20,.30),transparent 70%);filter:blur(26px);"></div>
                @foreach($embers as $e)
                <div class="bv-ember" style="left:{{ $e['x'] }}%;width:{{ $e['sz'] }}px;height:{{ $e['sz'] }}px;background:rgb({{ $e['c'] }});box-shadow:0 0 {{ $e['g'] }}px {{ $e['g'] }}px rgba({{ $e['c'] }},.6);--drift:{{ $e['drift'] }}px;--dur:{{ $e['dur'] }}s;--del:{{ $e['del'] }}s;"></div>
                @endforeach
            </div>
            <div class="bv-fire-base"></div>
            <div class="bv-hero-overlay"></div>
            <div style="position:relative;z-index:2;">
                <div class="bv-icon-ring" style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;margin-bottom:1.125rem;background:{{ $accentSoft }};border:1.5px solid {{ $accentBorder }};">
                    @if($valido)
                        <lord-icon src="https://cdn.lordicon.com/xjsqfzte.json" trigger="loop" delay="800" stroke="bold" colors="primary:{{ $accent }},secondary:{{ $accent }}" style="width:50px;height:50px;"></lord-icon>
                    @else
                        <lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop" delay="800" stroke="bold" colors="primary:{{ $accent }},secondary:{{ $accent }}" style="width:48px;height:48px;"></lord-icon>
                    @endif
                </div>
                <p style="color:#92710d;font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;margin:0 0 .4rem;">Verificador de Autenticidad</p>
                <h1 style="color:#0f172a;font-size:1.5rem;font-weight:700;letter-spacing:-.02em;line-height:1.25;margin:0 0 .875rem;">{{ $titulo }}</h1>
                <p style="color:#334155;font-size:.875rem;font-weight:500;line-height:1.65;margin:0 auto;max-width:440px;">{{ $sub }}</p>
            </div>
        </div>

        @if($acta && ($valido || $anulado))
            <div class="bv-rule">
                <div class="bv-rule-line"></div>
                <span class="bv-rule-label">Información del acta</span>
                <div class="bv-rule-line"></div>
            </div>

            <div class="section-card" style="--card-accent:{{ $accent }};">
                <div class="section-head">
                    <lord-icon src="https://cdn.lordicon.com/wxnxiano.json" trigger="loop" delay="2000" stroke="bold" colors="primary:{{ $accent }},secondary:{{ $accent }}" style="width:20px;height:20px;flex-shrink:0;"></lord-icon>
                    <span class="section-head-label">Acta de Necesidad</span>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-4">
                    <div>
                        <p class="field-label">N.° de acta</p>
                        <span class="inline-block px-2.5 py-0.5 text-xs font-bold rounded-full border" style="background:{{ $accentSoft }};color:{{ $accent }};border-color:{{ $accentBorder }};">0{{ $acta->consecutivo }}</span>
                    </div>
                    <div>
                        <p class="field-label">Estado</p>
                        <p class="field-value" style="color:{{ $accent }};">{{ ucfirst($acta->estado) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="field-label">Fecha de solicitud</p>
                        <p class="field-value">{{ optional($acta->fecha_solicitud)->format('d/m/Y') ?: '-' }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="field-label">Dependencia</p>
                        <p class="field-value">{{ $acta->dependencia_texto }}{{ $acta->area_texto ? ' - '.$acta->area_texto : '' }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="field-label">Solicitante</p>
                        <p class="field-value">{{ $acta->nombre_solicitante ?: '-' }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="field-label">Objeto del contrato</p>
                        <p class="field-value" style="font-weight:500;">{{ $acta->objeto_contrato ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="section-card" style="--card-accent:#c9a84c;">
                <div class="section-head">
                    <lord-icon src="https://cdn.lordicon.com/fihkmkwt.json" trigger="loop" delay="2000" stroke="bold" colors="primary:{{ $accent }},secondary:{{ $accent }}" style="width:20px;height:20px;flex-shrink:0;"></lord-icon>
                    <span class="section-head-label">Integridad del Documento</span>
                </div>
                <div class="p-5 space-y-3">
                    <div class="rounded-xl p-3" style="background:rgba(0,0,0,.03);border:1px solid rgba(0,0,0,.06);">
                        <p class="field-label">Código de verificación</p>
                        <p class="text-xs mono break-all leading-relaxed mt-1" style="color:#334155;">{{ $codigo }}</p>
                    </div>
                    <div class="rounded-xl p-3" style="background:rgba(0,0,0,.03);border:1px solid rgba(0,0,0,.06);">
                        <p class="field-label">Documento generado el</p>
                        <p class="field-value mt-1">{{ optional($acta->fecha_generado ?? $acta->fecha_aprobado)->format('d/m/Y \a \l\a\s H:i') ?: '-' }}</p>
                    </div>
                    @if($anulado && $acta->motivo_anulacion)
                    <div class="rounded-xl p-3" style="background:rgba(217,119,6,.06);border:1px solid rgba(217,119,6,.2);">
                        <p class="field-label" style="color:#b45309;">Motivo de anulación</p>
                        <p class="field-value mt-1" style="color:#7c2d12;font-weight:500;">{{ $acta->motivo_anulacion }}</p>
                    </div>
                    @endif
                </div>
            </div>
        @endif

    </main>

    <footer class="max-w-2xl mx-auto px-4 sm:px-6 pb-10 pt-4 text-center">
        <p class="text-xs leading-relaxed" style="color:#94a3b8;">
            Verificación provista por la <span class="font-medium" style="color:{{ $accent }};">Alcaldía Municipal de Puerto Boyacá</span> · Sistema de Gestión<br>
            Este servicio permite validar la autenticidad de las actas de necesidad emitidas.
        </p>
    </footer>

    <script>
    (function() {
        var ID = '{{ $heroId }}', hero = document.getElementById(ID), canvas = document.getElementById(ID + '_canvas');
        if (!canvas || !hero) return;
        var ctx = canvas.getContext('2d'), raf, pts = [], mouse = { x:-9999, y:-9999 }, tick = 0;
        function resize() { canvas.width = hero.offsetWidth; canvas.height = hero.offsetHeight; }
        resize(); window.addEventListener('resize', function() { resize(); pts = []; init(); });
        hero.addEventListener('mousemove', function(e) { var r = hero.getBoundingClientRect(); mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top; });
        hero.addEventListener('mouseleave', function() { mouse.x = -9999; mouse.y = -9999; });
        function init() { pts = []; for (var i=0;i<20;i++){ var spd=Math.random()*.45+.2, ang=Math.random()*Math.PI*2; pts.push({x:Math.random()*canvas.width,y:Math.random()*canvas.height,vx:Math.cos(ang)*spd,vy:Math.sin(ang)*spd,r:Math.random()*1.4+.4,phase:Math.random()*Math.PI*2}); } }
        init();
        function draw() {
            tick += .016; ctx.clearRect(0,0,canvas.width,canvas.height); var dc = 'rgba(180,140,30,';
            pts.forEach(function(p){ var mx=mouse.x-p.x,my=mouse.y-p.y,md=Math.sqrt(mx*mx+my*my);
                if(md<160&&md>1){p.vx+=(mx/md)*.016;p.vy+=(my/md)*.016;}
                var sp=Math.sqrt(p.vx*p.vx+p.vy*p.vy); if(sp>1.2){p.vx*=.93;p.vy*=.93;} if(sp<.15){p.vx*=1.07;p.vy*=1.07;}
                p.x+=p.vx; p.y+=p.vy; if(p.x<0||p.x>canvas.width)p.vx*=-1; if(p.y<0||p.y>canvas.height)p.vy*=-1;
                var pa=.25+.2*Math.sin(tick*1.2+p.phase); ctx.beginPath(); ctx.arc(p.x,p.y,p.r,0,Math.PI*2); ctx.fillStyle=dc+pa+')'; ctx.fill(); });
            for(var a=0;a<pts.length;a++)for(var b=a+1;b<pts.length;b++){ var dx=pts[a].x-pts[b].x,dy=pts[a].y-pts[b].y,d=Math.sqrt(dx*dx+dy*dy);
                if(d<80){ ctx.beginPath(); ctx.moveTo(pts[a].x,pts[a].y); ctx.lineTo(pts[b].x,pts[b].y); ctx.strokeStyle=dc+(.18*(1-d/110))+')'; ctx.lineWidth=.55; ctx.stroke(); } }
            raf = requestAnimationFrame(draw);
        }
        draw();
        if ('IntersectionObserver' in window) new IntersectionObserver(function(e){ if(!e[0].isIntersecting)cancelAnimationFrame(raf); else draw(); }).observe(hero);
    })();
    </script>
</body>
</html>
