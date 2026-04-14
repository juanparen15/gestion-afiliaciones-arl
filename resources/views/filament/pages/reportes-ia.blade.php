<x-filament-panels::page>
@php
    $uid = 'ia_' . substr(md5(uniqid()), 0, 8);

    /* Embers (light mode) — blue sparks */
    $emberColors = ['30,64,175', '59,130,246', '96,165,250', '147,197,253', '79,70,229', '99,102,241'];
    $embers = [];
    for ($i = 0; $i < 24; $i++) {
        $c   = $emberColors[array_rand($emberColors)];
        $sz  = round(mt_rand(15, 40) / 10, 1);
        $embers[] = [
            'x'     => mt_rand(2, 98),
            'sz'    => $sz,
            'c'     => $c,
            'g'     => (int)(($sz * mt_rand(22, 48)) / 10),
            'dur'   => round(mt_rand(35, 70) / 10, 1),
            'del'   => round(mt_rand(0, 90) / 10, 1),
            'drift' => mt_rand(-35, 35),
        ];
    }

    /* Fireflies (dark mode) */
    $ffColors = ['96,165,250', '147,197,253', '165,180,252', '196,181,253', '125,211,252', '167,243,208'];
    $fireflies = [];
    for ($i = 0; $i < 26; $i++) {
        $c  = $ffColors[array_rand($ffColors)];
        $sz = round(mt_rand(20, 55) / 10, 1);
        $g  = (int)(($sz * mt_rand(30, 60)) / 10);
        $fireflies[] = [
            'x'  => mt_rand(2, 97),
            'y'  => mt_rand(4, 96),
            'sz' => $sz,
            'g'  => $g,
            'c'  => $c,
            'tw' => round(mt_rand(18, 50) / 10, 1),
            'del'=> round(mt_rand(0, 60) / 10, 1),
            'dr' => round(mt_rand(65, 150) / 10, 1),
        ];
    }

    $ejemplosDetalle = [
        ['sc'=>'#60a5fa','body'=>'¿Cuántos contratos hay activos este año?'],
        ['sc'=>'#a78bfa','body'=>'¿Qué dependencia tiene más contratos en ejecución?'],
        ['sc'=>'#f59e0b','body'=>'¿Cuáles contratos vencen en los próximos 30 días?'],
        ['sc'=>'#34d399','body'=>'¿Cuál es el valor total de contratos por dependencia?'],
        ['sc'=>'#f472b6','body'=>'¿Cuántas afiliaciones ARL están próximas a vencer?'],
        ['sc'=>'#fb923c','body'=>'¿Quiénes son los 5 contratistas con más contratos?'],
    ];
@endphp

@verbatim
<style>
/* ── Keyframes ───────────────────────────────── */
@keyframes ia-up  { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
@keyframes ia-pop { from{opacity:0;transform:scale(.55)} to{opacity:1;transform:scale(1)} }
@keyframes ia-glow{0%,100%{box-shadow:0 0 0 0 rgba(96,165,250,.5)} 65%{box-shadow:0 0 0 14px rgba(96,165,250,0)}}
@keyframes ia-pulse{0%,100%{opacity:.6} 50%{opacity:1}}

.ia-a1{animation:ia-up .6s cubic-bezier(.16,1,.3,1) both}
.ia-a2{animation:ia-up .6s .12s cubic-bezier(.16,1,.3,1) both}
.ia-a3{animation:ia-up .6s .24s cubic-bezier(.16,1,.3,1) both}
.ia-icon-ring{animation:ia-pop .7s .08s cubic-bezier(.34,1.56,.64,1) both,ia-glow 3s 1.2s ease-in-out infinite}

/* ── Hero ─────────────────────────────────────── */
.ia-hero{
    position:relative;overflow:hidden;border-radius:1.125rem;
    padding:2rem 1.25rem 1.75rem;text-align:center;
    background:linear-gradient(155deg,#060f22 0%,#091830 50%,#060e20 100%);
}
@media(min-width:540px){.ia-hero{border-radius:1.375rem;padding:2.75rem 2.25rem 2.5rem;}}
html:not(.dark) .ia-hero{background:#ffffff;border:1px solid rgba(0,0,0,.06);box-shadow:0 4px 24px rgba(0,0,0,.06);}
html:not(.dark) .ia-orb-blue{background:radial-gradient(circle,rgba(30,64,175,.28),transparent 70%) !important;}
html:not(.dark) .ia-orb-gold{background:radial-gradient(circle,rgba(99,102,241,.22),transparent 70%) !important;}
html:not(.dark) .ia-fire-base{display:block;background:radial-gradient(ellipse 85% 55% at 50% 100%,rgba(59,130,246,.18) 0%,rgba(99,102,241,.08) 50%,transparent 100%);}
.ia-fire-base{display:none;position:absolute;inset:0;pointer-events:none;z-index:0;}

/* ── Hero text ───────────────────────────────── */
.ia-title{font-size:1.25rem;font-weight:700;letter-spacing:-.02em;line-height:1.25;margin:0 0 .75rem;}
@media(min-width:540px){.ia-title{font-size:1.625rem;}}
.ia-subtitle-wrap{max-width:none;}
@media(min-width:540px){.ia-subtitle-wrap{max-width:440px;margin-left:auto;margin-right:auto;}}

/* ── Divider ─────────────────────────────────── */
.ia-rule{display:flex;align-items:center;gap:.75rem;margin-bottom:.875rem;}
.ia-rule-line{flex:1;height:1px;}
.ia-rule-label{font-size:.625rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;}

/* ── Cards grid ──────────────────────────────── */
.ia-grid{display:grid;grid-template-columns:1fr;gap:.625rem;}
@media(min-width:560px){.ia-grid{grid-template-columns:repeat(2,1fr);gap:.75rem;}}

/* ── Card item ───────────────────────────────── */
.ia-card{
    border-radius:1rem;padding:.875rem 1rem;cursor:pointer;
    transition:transform .35s cubic-bezier(.16,1,.3,1),box-shadow .35s ease;
    position:relative;overflow:hidden;transform-style:preserve-3d;
    background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);
    display:flex;align-items:flex-start;gap:.875rem;text-align:left;width:100%;
}
.ia-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2.5px;background:var(--sc,#60a5fa);transform:scaleX(0);transform-origin:left;transition:transform .28s ease;z-index:1;}
.ia-card:hover::before{transform:scaleX(1);}
.ia-card::after{content:'';position:absolute;inset:0;border-radius:inherit;background:radial-gradient(circle at var(--mx,50%) var(--my,50%),rgba(255,255,255,.12) 0%,transparent 62%);opacity:0;transition:opacity .3s;pointer-events:none;z-index:0;}
.ia-card:hover::after{opacity:1;}
html:not(.dark) .ia-card{background:rgba(255,255,255,.82);border-color:rgba(0,0,0,.08);box-shadow:0 2px 8px rgba(0,0,0,.06);}
html:not(.dark) .ia-card::after{background:radial-gradient(circle at var(--mx,50%) var(--my,50%),rgba(96,165,250,.10) 0%,transparent 62%);}

/* ── Card badge ──────────────────────────────── */
.ia-badge{width:32px;height:32px;border-radius:.5rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:700;background:var(--ib,rgba(96,165,250,.12));border:1px solid var(--ibc,rgba(96,165,250,.25));color:var(--sc,#60a5fa);margin-top:.1rem;}
.ia-card-tag{display:inline-block;font-size:.575rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--sc,#60a5fa);opacity:.8;margin-bottom:.2rem;}

/* ── Color tokens ────────────────────────────── */
.t-h{color:#f1f5f9} .t-s{color:#94a3b8} .t-m{color:#cbd5e1} .t-blue{color:#60a5fa}
.t-ct{color:#f1f5f9;font-size:.875rem;font-weight:600;margin:0 0 .2rem;line-height:1.3}
.t-cb{color:#94a3b8;font-size:.8rem;margin:0;line-height:1.5}
.t-dl{color:#475569}
.ia-rule-line-c{background:rgba(255,255,255,.08)}
html:not(.dark) .t-h{color:#0f172a} html:not(.dark) .t-s{color:#64748b}
html:not(.dark) .t-m{color:#334155} html:not(.dark) .t-blue{color:#1d4ed8}
html:not(.dark) .t-ct{color:#0f172a} html:not(.dark) .t-cb{color:#475569}
html:not(.dark) .t-dl{color:#94a3b8} html:not(.dark) .ia-rule-line-c{background:#e2e8f0}

/* ── Hero overlay ────────────────────────────── */
.ia-hero-overlay{position:absolute;inset:0;pointer-events:none;z-index:1;background:radial-gradient(ellipse 75% 85% at 50% 50%,rgba(3,8,20,.84) 0%,rgba(3,8,20,.52) 50%,transparent 100%);}
html:not(.dark) .ia-hero-overlay{background:radial-gradient(ellipse 72% 80% at 50% 45%,rgba(255,255,255,.68) 0%,rgba(255,255,255,.35) 55%,transparent 100%);}
html:not(.dark) .ia-canvas-el{opacity:.4 !important;}

/* ── Embers (light) ──────────────────────────── */
@keyframes ia-ember{0%{transform:translateY(0) translateX(0) scale(1);opacity:0} 8%{opacity:.9} 80%{opacity:.4} 100%{transform:translateY(-300px) translateX(var(--drift,20px)) scale(.2);opacity:0}}
.ia-ember{position:absolute;border-radius:50%;pointer-events:none;bottom:-4px;will-change:transform,opacity;animation:ia-ember var(--dur,5s) var(--del,0s) ease-in infinite;}
html.dark .ia-ember{display:none;}
html:not(.dark) .ia-firefly{opacity:0 !important;}

/* ── Title shadow ────────────────────────────── */
.ia-title-shadow{text-shadow:0 2px 24px rgba(0,0,0,.88),0 1px 4px rgba(0,0,0,.65);}
html:not(.dark) .ia-title-shadow{text-shadow:0 1px 12px rgba(30,64,175,.15),0 2px 4px rgba(0,0,0,.08);}

/* ── Fireflies (dark) ────────────────────────── */
@keyframes ia-twinkle{0%,100%{opacity:.04;transform:scale(.3)} 45%,55%{opacity:1;transform:scale(1.3)}}
@keyframes ia-drift{0%,100%{transform:translate(0,0)} 30%{transform:translate(10px,-16px)} 65%{transform:translate(-8px,-10px)}}
.ia-firefly{position:absolute;border-radius:50%;pointer-events:none;will-change:transform,opacity;animation:ia-twinkle var(--tw) var(--del) ease-in-out infinite,ia-drift var(--dr) var(--del) ease-in-out infinite;}

/* ── Orb animations ──────────────────────────── */
@keyframes ia-float-a{0%{transform:translate(0,0) scale(1)} 25%{transform:translate(-20px,18px) scale(1.08)} 55%{transform:translate(14px,-14px) scale(.94)} 80%{transform:translate(-8px,20px) scale(1.04)} 100%{transform:translate(0,0) scale(1)}}
@keyframes ia-float-b{0%{transform:translate(0,0) scale(1)} 30%{transform:translate(18px,-20px) scale(1.12)} 60%{transform:translate(-12px,10px) scale(.88)} 85%{transform:translate(8px,-14px) scale(1.06)} 100%{transform:translate(0,0) scale(1)}}
.ia-orb-blue{animation:ia-float-a 11s ease-in-out infinite;}
.ia-orb-gold{animation:ia-float-b 14s ease-in-out infinite;}

/* ── Form elements ───────────────────────────── */
.ia-form-section{border-radius:1rem;padding:1.25rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);}
html:not(.dark) .ia-form-section{background:#fff;border-color:rgba(0,0,0,.08);box-shadow:0 2px 8px rgba(0,0,0,.06);}
.ia-textarea{width:100%;border-radius:.75rem;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);padding:.875rem 1rem;font-size:.875rem;line-height:1.6;color:#f1f5f9;resize:none;outline:none;transition:border-color .2s,box-shadow .2s;}
.ia-textarea::placeholder{color:rgba(148,163,184,.6);}
.ia-textarea:focus{border-color:rgba(96,165,250,.6);box-shadow:0 0 0 3px rgba(96,165,250,.12);}
html:not(.dark) .ia-textarea{background:#f8fafc;border-color:rgba(0,0,0,.1);color:#0f172a;}
html:not(.dark) .ia-textarea::placeholder{color:#94a3b8;}
html:not(.dark) .ia-textarea:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12);}

/* ── Submit button ───────────────────────────── */
.ia-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.25rem;border-radius:.625rem;font-size:.875rem;font-weight:600;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:none;cursor:pointer;transition:opacity .2s,transform .15s,box-shadow .2s;box-shadow:0 2px 12px rgba(37,99,235,.35);}
.ia-btn:hover{opacity:.92;transform:translateY(-1px);box-shadow:0 4px 18px rgba(37,99,235,.45);}
.ia-btn:active{transform:translateY(0);}
.ia-btn:disabled{opacity:.55;cursor:not-allowed;transform:none;}
.ia-btn-ghost{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .875rem;border-radius:.625rem;font-size:.875rem;font-weight:500;background:transparent;border:1px solid rgba(255,255,255,.14);color:#94a3b8;cursor:pointer;transition:background .2s,color .2s;}
.ia-btn-ghost:hover{background:rgba(255,255,255,.08);color:#cbd5e1;}
html:not(.dark) .ia-btn-ghost{border-color:rgba(0,0,0,.1);color:#475569;}
html:not(.dark) .ia-btn-ghost:hover{background:#f1f5f9;color:#334155;}

/* ── Skeleton ─────────────────────────────────── */
@keyframes ia-shimmer{0%{background-position:-400px 0} 100%{background-position:400px 0}}
.ia-skeleton-line{height:.75rem;border-radius:.375rem;background:linear-gradient(90deg,rgba(255,255,255,.06) 25%,rgba(255,255,255,.12) 50%,rgba(255,255,255,.06) 75%);background-size:400px 100%;animation:ia-shimmer 1.4s ease-in-out infinite;}
html:not(.dark) .ia-skeleton-line{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:400px 100%;}

/* ── Response chat ───────────────────────────── */
.ia-bubble-user{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:.875rem .875rem .875rem .25rem;}
.ia-bubble-ai{background:rgba(37,99,235,.12);border:1px solid rgba(96,165,250,.2);border-radius:.875rem .875rem .25rem .875rem;}
html:not(.dark) .ia-bubble-user{background:#f8fafc;border-color:rgba(0,0,0,.08);}
html:not(.dark) .ia-bubble-ai{background:rgba(219,234,254,.5);border-color:rgba(59,130,246,.2);}

/* ── Error ───────────────────────────────────── */
.ia-error-box{border-radius:1rem;padding:1.125rem 1.25rem;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);}
html:not(.dark) .ia-error-box{background:rgba(254,226,226,.6);border-color:rgba(252,165,165,.5);}

/* ── Reduced motion ──────────────────────────── */
@media(prefers-reduced-motion:reduce){
    .ia-a1,.ia-a2,.ia-a3,.ia-icon-ring,.ia-orb-blue,.ia-orb-gold,.ia-firefly{animation:none;opacity:.6;transform:none;}
    .ia-card{transition:none;}
    .ia-ember{animation:none;}
}
</style>
@endverbatim

<div style="display:flex;flex-direction:column;gap:1.375rem;padding:.25rem 0;">

    {{-- ══════════════════ HERO ══════════════════ --}}
    <div class="ia-hero ia-a1" id="{{ $uid }}_hero">
        <canvas id="{{ $uid }}_canvas" class="ia-canvas-el"
            oncontextmenu="return false"
            style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:.5;-webkit-user-select:none;user-select:none;"></canvas>

        <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
            <div class="ia-orb-blue"
                style="position:absolute;width:280px;height:280px;top:-70px;right:-50px;border-radius:50%;background:radial-gradient(circle,rgba(30,64,175,.55),transparent 70%);filter:blur(28px);">
            </div>
            <div class="ia-orb-gold"
                style="position:absolute;width:200px;height:200px;bottom:-50px;left:-40px;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.3),transparent 70%);filter:blur(26px);">
            </div>

            @foreach($embers as $e)
                <div class="ia-ember" style="left:{{ $e['x'] }}%;width:{{ $e['sz'] }}px;height:{{ $e['sz'] }}px;background:rgb({{ $e['c'] }});box-shadow:0 0 {{ $e['g'] }}px {{ $e['g'] }}px rgba({{ $e['c'] }},.6),0 0 {{ $e['g']*2 }}px {{ $e['g']*3 }}px rgba(59,130,246,.2);--drift:{{ $e['drift'] }}px;--dur:{{ $e['dur'] }}s;--del:{{ $e['del'] }}s;"></div>
            @endforeach

            @foreach($fireflies as $f)
                <div class="ia-firefly" style="left:{{ $f['x'] }}%;top:{{ $f['y'] }}%;width:{{ $f['sz'] }}px;height:{{ $f['sz'] }}px;background:rgb({{ $f['c'] }});box-shadow:0 0 {{ $f['g'] }}px {{ $f['g']*2 }}px rgba({{ $f['c'] }},.55),0 0 {{ $f['g']*4 }}px {{ $f['g']*3 }}px rgba({{ $f['c'] }},.18);--tw:{{ $f['tw'] }}s;--del:{{ $f['del'] }}s;--dr:{{ $f['dr'] }}s;"></div>
            @endforeach
        </div>

        <div class="ia-fire-base"></div>
        <div class="ia-hero-overlay"></div>

        <div style="position:relative;z-index:2;">
            <div class="ia-icon-ring"
                style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:rgba(96,165,250,.12);border:1.5px solid rgba(96,165,250,.35);margin-bottom:1.125rem;">
                <svg style="width:28px;height:28px;color:#60a5fa" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                </svg>
            </div>

            <p class="t-blue" style="font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;margin:0 0 .4rem;text-shadow:0 0 18px rgba(96,165,250,.65),0 2px 8px rgba(0,0,0,.7);">
                Inteligencia Artificial
            </p>

            <h1 class="ia-title t-h ia-title-shadow">Reportes con IA</h1>

            <p class="t-m ia-subtitle-wrap" style="font-size:.875rem;line-height:1.65;margin:.5rem auto 0;text-align:center;">
                Consulta en lenguaje natural sobre
                <span class="t-blue" style="font-weight:500;">contratos SECOP</span> y
                <span class="t-blue" style="font-weight:500;">afiliaciones ARL</span>.
                Los datos son en tiempo real.
            </p>
        </div>
    </div>

    {{-- ══════════════════ PREGUNTAS SUGERIDAS ══════════════════ --}}
    <div class="ia-a2">
        <div class="ia-rule">
            <div class="ia-rule-line ia-rule-line-c"></div>
            <span class="ia-rule-label t-dl">Preguntas sugeridas — haz clic para usarlas</span>
            <div class="ia-rule-line ia-rule-line-c"></div>
        </div>

        <div class="ia-grid" id="{{ $uid }}_cards">
            @foreach($ejemplosDetalle as $p)
                <button
                    type="button"
                    wire:click="usarEjemplo('{{ $p['body'] }}')"
                    class="ia-card"
                    style="--sc:{{ $p['sc'] }};padding:.875rem 1rem .875rem 1.125rem;align-items:center;">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $p['sc'] }};flex-shrink:0;box-shadow:0 0 6px 2px {{ $p['sc'] }}55;"></div>
                    <p class="t-cb" style="margin:0;font-size:.82rem;line-height:1.5;flex:1;text-align:left;">{{ $p['body'] }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════ FORMULARIO ══════════════════ --}}
    <div class="ia-form-section ia-a3">
        <form wire:submit="consultar">
            <textarea
                wire:model="pregunta"
                rows="3"
                placeholder="Ej: ¿Cuántos contratos están activos este año y cuál es su valor total?"
                class="ia-textarea">
            </textarea>

            @error('pregunta')
                <p style="margin-top:.5rem;font-size:.75rem;color:#f87171;display:flex;align-items:center;gap:.25rem;">
                    <svg style="width:14px;height:14px;flex-shrink:0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror

            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.875rem;flex-wrap:wrap;gap:.5rem;">
                <div style="display:flex;align-items:center;gap:.625rem;">
                    <button type="submit" class="ia-btn" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="consultar" style="display:flex;align-items:center;gap:.5rem;">
                            <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                            Consultar con IA
                        </span>
                        <span wire:loading.flex wire:target="consultar" style="align-items:center;gap:.5rem;">
                            <svg style="width:16px;height:16px" class="animate-spin" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                            Analizando datos...
                        </span>
                    </button>

                    @if($respuesta || $error)
                        <button
                            type="button"
                            class="ia-btn-ghost"
                            wire:click="$set('respuesta', null); $set('error', null); $set('pregunta', '')">
                            <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            Nueva consulta
                        </button>
                    @endif
                </div>

                <span style="font-size:.7rem;color:#475569;letter-spacing:.05em;">Powered by Gemini AI</span>
            </div>
        </form>
    </div>

    {{-- ══════════════════ SKELETON ══════════════════ --}}
    <div wire:loading wire:target="consultar" class="ia-form-section">
        <div style="display:flex;align-items:center;gap:.875rem;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,.08);">
            <div style="width:32px;height:32px;border-radius:.5rem;" class="ia-skeleton-line"></div>
            <div style="flex:1;display:flex;flex-direction:column;gap:.5rem;">
                <div class="ia-skeleton-line" style="width:9rem;"></div>
                <div class="ia-skeleton-line" style="width:13rem;height:.625rem;"></div>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:.75rem;">
            <div class="ia-skeleton-line" style="width:100%;"></div>
            <div class="ia-skeleton-line" style="width:82%;"></div>
            <div class="ia-skeleton-line" style="width:100%;"></div>
            <div class="ia-skeleton-line" style="width:68%;"></div>
            <div class="ia-skeleton-line" style="width:91%;"></div>
            <div class="ia-skeleton-line" style="width:55%;"></div>
        </div>
    </div>

    {{-- ══════════════════ ERROR ══════════════════ --}}
    @if($error)
        <div class="ia-error-box">
            <div style="display:flex;align-items:flex-start;gap:.875rem;">
                <div style="width:32px;height:32px;border-radius:.5rem;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;color:#f87171" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.875rem;font-weight:600;color:#fca5a5;margin:0 0 .5rem;">Error al procesar la consulta</p>
                    <pre style="font-size:.72rem;color:#fca5a5;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);border-radius:.5rem;padding:.75rem;overflow-x:auto;white-space:pre-wrap;word-break:break-word;font-family:monospace;margin:0;line-height:1.6;">{{ $error }}</pre>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════ RESPUESTA ══════════════════ --}}
    @if($respuesta)
        <div class="ia-form-section ia-a1"
             x-data x-init="setTimeout(()=>$el.scrollIntoView({behavior:'smooth',block:'nearest'}),100)">

            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:.875rem;margin-bottom:1.125rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,.08);">
                <div style="width:32px;height:32px;border-radius:.5rem;background:rgba(96,165,250,.12);border:1px solid rgba(96,165,250,.25);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;color:#60a5fa" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                </div>
                <div>
                    <p style="font-size:.875rem;font-weight:600;margin:0 0 .1rem;" class="t-h">Respuesta del Asistente IA</p>
                    <p style="font-size:.75rem;margin:0;" class="t-s">Datos en tiempo real del sistema</p>
                </div>
            </div>

            {{-- Pregunta --}}
            <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.875rem;">
                <div style="width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
                    <svg style="width:13px;height:13px;" class="t-s" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <div class="ia-bubble-user" style="flex:1;padding:.75rem 1rem;">
                    <p style="font-size:.875rem;line-height:1.65;margin:0;" class="t-m">{{ $pregunta }}</p>
                </div>
            </div>

            {{-- Respuesta IA --}}
            <div style="display:flex;align-items:flex-start;gap:.75rem;">
                <div style="width:28px;height:28px;border-radius:50%;background:rgba(37,99,235,.2);border:1px solid rgba(96,165,250,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
                    <svg style="width:13px;height:13px;color:#60a5fa" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                </div>
                <div class="ia-bubble-ai" style="flex:1;padding:.875rem 1rem;">
                    <div style="font-size:.875rem;line-height:1.75;" class="t-m">
                        {!! nl2br(e($respuesta)) !!}
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- ══════════════════ API KEY WARNING ══════════════════ --}}
    @if(empty(config('services.gemini.key')))
        <div style="border-radius:1rem;padding:1rem 1.25rem;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);display:flex;align-items:flex-start;gap:.875rem;">
            <svg style="width:20px;height:20px;color:#f59e0b;flex-shrink:0;margin-top:.1rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z"/></svg>
            <div>
                <p style="font-size:.875rem;font-weight:600;color:#f59e0b;margin:0 0 .3rem;">API Key no configurada</p>
                <p style="font-size:.8rem;color:#94a3b8;margin:0;line-height:1.6;">
                    Agrega <code style="background:rgba(245,158,11,.12);padding:.1rem .4rem;border-radius:.3rem;font-family:monospace;color:#fbbf24;">GEMINI_API_KEY=tu_clave</code>
                    en el archivo <code style="background:rgba(245,158,11,.12);padding:.1rem .4rem;border-radius:.3rem;font-family:monospace;color:#fbbf24;">.env</code> del servidor.
                </p>
            </div>
        </div>
    @endif

</div>

<script>
(function(){
    var UID   = '{{ $uid }}';
    var hero  = document.getElementById(UID+'_hero');
    var canvas= document.getElementById(UID+'_canvas');
    if(!canvas||!hero) return;

    var ctx=canvas.getContext('2d'), raf, pts=[],
        mouse={x:-9999,y:-9999}, tick=0;

    function resize(){canvas.width=hero.offsetWidth;canvas.height=hero.offsetHeight;}
    resize();
    window.addEventListener('resize',function(){resize();pts=[];init();});

    hero.addEventListener('mousemove',function(e){var r=hero.getBoundingClientRect();mouse.x=e.clientX-r.left;mouse.y=e.clientY-r.top;});
    hero.addEventListener('mouseleave',function(){mouse.x=-9999;mouse.y=-9999;});

    function init(){
        pts=[];
        for(var i=0;i<20;i++){
            var spd=Math.random()*.45+.2, ang=Math.random()*Math.PI*2;
            pts.push({x:Math.random()*canvas.width,y:Math.random()*canvas.height,vx:Math.cos(ang)*spd,vy:Math.sin(ang)*spd,r:Math.random()*1.4+.4,phase:Math.random()*Math.PI*2});
        }
    }
    init();

    function isDark(){return document.documentElement.classList.contains('dark');}

    function draw(){
        tick+=.016;
        ctx.clearRect(0,0,canvas.width,canvas.height);
        var dark=isDark();
        var dc=dark?'rgba(96,165,250,':'rgba(37,99,235,';

        pts.forEach(function(p){
            var mx=mouse.x-p.x, my=mouse.y-p.y, md=Math.sqrt(mx*mx+my*my);
            if(md<160&&md>1){p.vx+=(mx/md)*.016;p.vy+=(my/md)*.016;}
            var sp=Math.sqrt(p.vx*p.vx+p.vy*p.vy);
            if(sp>1.2){p.vx*=.93;p.vy*=.93;}
            if(sp<.15){p.vx*=1.07;p.vy*=1.07;}
            p.x+=p.vx; p.y+=p.vy;
            if(p.x<0||p.x>canvas.width)p.vx*=-1;
            if(p.y<0||p.y>canvas.height)p.vy*=-1;
            var pa=.45+.3*Math.sin(tick*1.2+p.phase);
            ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
            ctx.fillStyle=dc+pa+')';ctx.fill();
        });

        for(var a=0;a<pts.length;a++)
            for(var b=a+1;b<pts.length;b++){
                var dx=pts[a].x-pts[b].x, dy=pts[a].y-pts[b].y;
                var d=Math.sqrt(dx*dx+dy*dy);
                if(d<80){ctx.beginPath();ctx.moveTo(pts[a].x,pts[a].y);ctx.lineTo(pts[b].x,pts[b].y);ctx.strokeStyle=dc+(.28*(1-d/100))+')';ctx.lineWidth=.55;ctx.stroke();}
            }

        raf=requestAnimationFrame(draw);
    }
    draw();

    if('IntersectionObserver' in window)
        new IntersectionObserver(function(e){if(!e[0].isIntersecting)cancelAnimationFrame(raf);else draw();}).observe(hero);

    /* 3D Tilt on cards */
    var hasHover=window.matchMedia('(hover:hover) and (pointer:fine)').matches;
    if(!hasHover) return;
    document.querySelectorAll('#'+UID+'_cards .ia-card').forEach(function(el){
        el.addEventListener('mousemove',function(e){
            var r=el.getBoundingClientRect();
            var rx=((e.clientY-r.top-r.height/2)/(r.height/2))*7;
            var ry=((r.width/2-(e.clientX-r.left))/(r.width/2))*7;
            var mx=((e.clientX-r.left)/r.width*100).toFixed(1)+'%';
            var my=((e.clientY-r.top)/r.height*100).toFixed(1)+'%';
            el.style.setProperty('--mx',mx);el.style.setProperty('--my',my);
            el.style.transition='transform .08s ease,box-shadow .08s ease';
            el.style.transform='perspective(800px) rotateX('+rx+'deg) rotateY('+ry+'deg) scale(1.03)';
            el.style.boxShadow='0 20px 45px rgba(0,0,0,.3),0 0 0 1px rgba(255,255,255,.07)';
        });
        el.addEventListener('mouseleave',function(){
            el.style.transition='transform .5s cubic-bezier(.16,1,.3,1),box-shadow .5s ease';
            el.style.transform='perspective(800px) rotateX(0deg) rotateY(0deg) scale(1)';
            el.style.boxShadow='';
        });
    });
})();
</script>
</x-filament-panels::page>
