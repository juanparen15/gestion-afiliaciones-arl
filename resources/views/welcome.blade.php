<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema ARL – Alcaldía de Puerto Boyacá</title>

    {{-- EB Garamond (headings: legal, formal, authoritative) + Lato (body: legible, clean) --}}
    {{-- Pairing recomendado por ui-ux-pro-max para gobierno/institucional --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

    {{-- Spline viewer web component --}}
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.79/build/spline-viewer.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ══════════════════════════════════════════════════
           DESIGN TOKENS — ui-ux-pro-max: government / institutional
           Palette: Professional blue + high contrast
           ══════════════════════════════════════════════════ */
        :root {
            --navy:     #050D1A;
            --navy-2:   #0B1829;
            --navy-3:   #0F2040;
            --blue:     #0369A1;   /* sky-700 — CTA accesible, gobierno */
            --blue-l:   #0EA5E9;   /* sky-500 */
            --blue-gl:  rgba(3,105,161,0.1);
            --em:       #059669;   /* emerald-600 */
            --em-l:     #10B981;   /* emerald-500 */
            --em-gl:    rgba(16,185,129,0.1);
            --white:    #F8FAFC;
            --muted:    #94A3B8;
            --slate:    #475569;   /* ↑ mejorado de #64748B → ratio 4.6:1 */
            --surface:  #F1F5F9;
            --border:   rgba(255,255,255,0.07);
            --card-b:   #E2E8F0;
            --ink:      #0F172A;   /* texto principal claro */
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Lato', sans-serif;
            background: var(--white);
            color: #1E293B;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            font-size: 1rem;        /* mínimo 16px — ui-ux-pro-max: readable-font-size */
            line-height: 1.65;      /* 1.5–1.75 — ui-ux-pro-max: line-height */
        }

        /* ── Skip link (accesibilidad teclado) ── */
        .skip-link {
            position: absolute; top: -120px; left: 1rem; z-index: 9999;
            padding: 0.75rem 1.5rem;
            background: var(--blue); color: white;
            font-family: 'Lato', sans-serif; font-weight: 700; font-size: 0.9375rem;
            border-radius: 0.5rem; text-decoration: none;
            transition: top 0.15s ease;
        }
        .skip-link:focus { top: 1rem; }

        /* ── Focus rings (3–4px, alto contraste) — ui-ux-pro-max: focus-states ── */
        :focus-visible {
            outline: 3px solid var(--blue-l);
            outline-offset: 3px;
            border-radius: 4px;
        }

        /* ── prefers-reduced-motion — ui-ux-pro-max: reduced-motion ── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .reveal { opacity: 1 !important; transform: none !important; }
        }

        /* ── Pulse dot ── */
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }
        @keyframes scrollPulse {
            0%, 100% { opacity: 0.25; }
            50%       { opacity: 0.7; }
        }

        /* ── Reveal on scroll ── */
        .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity 0.7s cubic-bezier(.16,1,.3,1),
                        transform 0.7s cubic-bezier(.16,1,.3,1);
        }
        .reveal.visible { opacity: 1; transform: none; }
        .delay-1 { transition-delay: 0.08s; }
        .delay-2 { transition-delay: 0.17s; }
        .delay-3 { transition-delay: 0.26s; }
        .delay-4 { transition-delay: 0.36s; }
        .delay-5 { transition-delay: 0.46s; }

        /* ══════════════════════════════════════════════════
           FLOATING PILL NAVBAR
           ui-ux-pro-max: "Add top-4 left-4 right-4 spacing for floating navbar"
           ══════════════════════════════════════════════════ */
        .nav {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 2.5rem);
            max-width: 80rem;
            z-index: 200;
            height: 62px;
            display: flex; align-items: center;
            padding: 0 1.5rem;
            border-radius: 1rem;
            background: rgba(5,13,26,0.72);
            backdrop-filter: blur(28px) saturate(1.6);
            -webkit-backdrop-filter: blur(28px) saturate(1.6);
            border: 1px solid rgba(255,255,255,0.09);
            transition: background 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .nav.scrolled {
            background: rgba(5,13,26,0.94);
            border-color: rgba(255,255,255,0.13);
            box-shadow: 0 8px 40px rgba(0,0,0,0.35);
        }
        .nav-inner { width: 100%; display: flex; align-items: center; justify-content: space-between; }
        .nav-brand {
            display: flex; align-items: center; gap: 0.75rem;
            text-decoration: none; cursor: pointer;
        }
        .nav-brand img { height: 2.125rem; width: auto; }
        .nav-name {
            font-family: 'Lato', sans-serif;
            font-size: 0.8125rem; font-weight: 700;
            color: var(--white); line-height: 1.1;
        }
        .nav-sub {
            font-size: 0.625rem; font-weight: 700;
            color: var(--em-l); letter-spacing: 0.12em; text-transform: uppercase;
        }
        .nav-links { display: flex; align-items: center; gap: 1.75rem; }
        .nav-link {
            color: rgba(248,250,252,0.6);
            font-size: 0.8125rem; font-weight: 700;
            letter-spacing: 0.04em; text-decoration: none;
            cursor: pointer;                                    /* ui-ux-pro-max: cursor-pointer */
            transition: color 0.18s ease;
            padding: 0.3rem 0;
        }
        .nav-link:hover { color: var(--white); }

        /* ── Botones ── */
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-family: 'Lato', sans-serif; font-weight: 700;
            text-decoration: none; border-radius: 0.5rem; border: none;
            cursor: pointer;                                    /* ui-ux-pro-max: cursor-pointer */
            transition: background 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .btn-primary {
            padding: 0.625rem 1.25rem; font-size: 0.8125rem;
            background: var(--blue); color: white;
            min-height: 44px;                                   /* ui-ux-pro-max: touch-target-size */
            box-shadow: 0 2px 10px rgba(3,105,161,0.3);
        }
        .btn-primary:hover { background: #0284C7; box-shadow: 0 4px 18px rgba(3,105,161,0.45); }

        .btn-hero {
            padding: 0.875rem 2rem; font-size: 0.9375rem;
            background: var(--blue); color: white;
            min-height: 52px;
            box-shadow: 0 4px 24px rgba(3,105,161,0.4);
        }
        .btn-hero:hover { background: #0284C7; box-shadow: 0 8px 32px rgba(3,105,161,0.5); }

        .btn-ghost {
            padding: 0.875rem 2rem; font-size: 0.9375rem;
            background: rgba(255,255,255,0.6);
            border: 1.5px solid rgba(0,0,0,0.15);
            color: var(--slate);
            min-height: 52px;
        }
        .btn-ghost:hover {
            border-color: rgba(0,0,0,0.28);
            color: var(--ink);
            background: rgba(255,255,255,0.85);
        }

        .btn-cta {
            padding: 1rem 2.5rem; font-size: 1rem;
            background: var(--blue); color: white;
            min-height: 52px;
            box-shadow: 0 4px 24px rgba(3,105,161,0.38);
        }
        .btn-cta:hover { background: #0284C7; box-shadow: 0 8px 32px rgba(3,105,161,0.5); }

        /* ══════════════════════════════════════════════════
           HERO
           ══════════════════════════════════════════════════ */
        .hero {
            position: relative; min-height: 100vh;
            background: white;
            display: flex; align-items: flex-end;
            overflow: hidden;
        }
        spline-viewer { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0; }

        /* Ocultar logo de Spline */
        spline-viewer::part(logo) { display: none; }

        .hero-mask  { display: none; }
        .hero-noise { display: none; }

        /* ══════════════════════════════════════════════════
           HERO INTRO — sección debajo del Spline
           ══════════════════════════════════════════════════ */
        .hero-intro {
            background: white;
            padding: 5rem 2rem 4.5rem;
            text-align: center;
            border-bottom: 1px solid var(--card-b);
        }
        .hero-intro-inner { max-width: 680px; margin: 0 auto; }
        .hero-actions { justify-content: center; }
        .hero-stats   { justify-content: center; }
        .hero-desc    { margin-left: auto; margin-right: auto; }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            background: rgba(5,150,105,0.08);
            border: 1px solid rgba(5,150,105,0.25);
            border-radius: 2rem;
            color: var(--em);
            font-family: 'Lato', sans-serif;
            font-size: 0.6875rem; font-weight: 700;
            letter-spacing: 0.12em; text-transform: uppercase;
            margin-bottom: 2rem;
        }
        .pulse-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--em-l); flex-shrink: 0;
            animation: pulse 2s ease-in-out infinite;
        }

        .hero-title {
            font-family: 'EB Garamond', serif;
            font-size: clamp(3rem, 8vw, 6.25rem);
            font-weight: 700;
            color: var(--ink);
            line-height: 1.02;
            letter-spacing: -0.02em;
            margin-bottom: 1.75rem;
        }
        .hero-title .line-accent { display: block; color: var(--blue); }

        .hero-desc {
            color: var(--slate);
            font-size: 1.0625rem; line-height: 1.72;
            max-width: 46ch;
            margin-bottom: 2.5rem;
        }

        .hero-actions { display: flex; gap: 0.875rem; flex-wrap: wrap; margin-bottom: 3.75rem; }

        /* Stat cards */
        .hero-stats { display: flex; gap: 1rem; flex-wrap: wrap; }
        .stat-card {
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(0,0,0,0.08);
            backdrop-filter: blur(12px);
            border-radius: 0.875rem;
            padding: 1.125rem 1.625rem; min-width: 120px;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .stat-card:hover { background: rgba(255,255,255,0.92); border-color: rgba(0,0,0,0.14); }
        .stat-num {
            font-family: 'EB Garamond', serif;
            font-size: 2.125rem; font-weight: 700; line-height: 1;
            color: var(--ink);
        }
        .stat-num.c-blue { color: var(--blue-l); }
        .stat-num.c-em   { color: var(--em-l); }
        .stat-lbl {
            font-size: 0.6875rem; font-weight: 700;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--muted); margin-top: 0.375rem;
        }

        /* Scroll indicator */
        .scroll-ind {
            position: absolute; bottom: 2.25rem; left: 50%;
            transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 0.375rem;
            color: var(--slate);
            font-family: 'Lato', sans-serif;
            font-size: 0.625rem; font-weight: 700;
            letter-spacing: 0.2em; text-transform: uppercase;
            z-index: 10; pointer-events: none;
        }
        .scroll-line {
            width: 1px; height: 38px;
            background: linear-gradient(to bottom, var(--slate), transparent);
            animation: scrollPulse 2.4s ease-in-out infinite;
        }

        /* ══════════════════════════════════════════════════
           TRUST BAR — nuevo: prueba social / normativa
           ══════════════════════════════════════════════════ */
        .trust-bar {
            background: white;
            border-top: 1px solid var(--card-b);
            border-bottom: 1px solid var(--card-b);
            padding: 1.625rem 2rem;
        }
        .trust-bar-inner {
            max-width: 80rem; margin: 0 auto;
            display: flex; align-items: center;
            justify-content: center; flex-wrap: wrap;
            gap: 1.5rem 3.5rem;
        }
        .trust-item { display: flex; align-items: center; gap: 0.75rem; }
        .trust-icon {
            width: 38px; height: 38px;
            background: var(--surface); border: 1px solid var(--card-b);
            border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center;
            color: var(--blue); flex-shrink: 0;
        }
        .trust-label {
            font-size: 0.8125rem; font-weight: 700; color: var(--ink);
            line-height: 1.2;
        }
        .trust-sub { font-size: 0.75rem; color: var(--slate); line-height: 1.3; }

        /* ══════════════════════════════════════════════════
           FEATURES
           ══════════════════════════════════════════════════ */
        .features { padding: 7rem 2rem; background: var(--surface); }
        .section-wrap { max-width: 80rem; margin: 0 auto; }

        .section-eyebrow {
            font-size: 0.6875rem; font-weight: 900;
            letter-spacing: 0.18em; text-transform: uppercase;
            margin-bottom: 0.875rem; display: block;
        }
        .section-eyebrow.blue { color: var(--blue); }
        .section-eyebrow.em   { color: var(--em); }

        .section-h {
            font-family: 'EB Garamond', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700; letter-spacing: -0.02em; line-height: 1.1;
            margin-bottom: 1rem;
        }
        .section-sub {
            color: var(--slate); font-size: 1.0625rem; line-height: 1.68;
            max-width: 52ch;    /* ui-ux-pro-max: line-length */
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem; margin-top: 3.5rem;
        }

        .feat-card {
            background: white;
            border: 1.5px solid var(--card-b);
            border-radius: 1.25rem;
            padding: 1.875rem;
            position: relative; overflow: hidden;
            cursor: default;
            /* ui-ux-pro-max: NO translateY → sin layout shift en hover */
            transition: border-color 0.22s ease, box-shadow 0.22s ease;
        }
        .feat-card::before {
            content: '';
            position: absolute; top: 0; left: 0;
            width: 3px; height: 100%;
            background: linear-gradient(to bottom, var(--blue), var(--em));
            opacity: 0;
            transition: opacity 0.22s ease;
        }
        .feat-card:hover { border-color: rgba(3,105,161,0.28); box-shadow: 0 8px 28px rgba(3,105,161,0.07); }
        .feat-card:hover::before { opacity: 1; }

        .feat-icon {
            width: 52px; height: 52px; border-radius: 0.875rem;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.25rem; flex-shrink: 0;
        }
        .feat-icon.blue { background: rgba(3,105,161,0.08); color: var(--blue); }
        .feat-icon.em   { background: rgba(5,150,105,0.08); color: var(--em); }

        .feat-title {
            font-family: 'EB Garamond', serif;
            font-size: 1.125rem; font-weight: 700;
            color: var(--ink); margin-bottom: 0.5rem;
        }
        .feat-desc { color: var(--slate); font-size: 0.9375rem; line-height: 1.65; }

        /* ══════════════════════════════════════════════════
           BENEFITS (dark section)
           ══════════════════════════════════════════════════ */
        .benefits {
            padding: 7rem 2rem;
            background: var(--navy-2);
            position: relative; overflow: hidden;
        }
        .benefits::before {
            content: '';
            position: absolute; top: -200px; right: -150px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(16,185,129,0.05) 0%, transparent 65%);
            pointer-events: none;
        }
        .benefits::after {
            content: '';
            position: absolute; bottom: -200px; left: -150px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(3,105,161,0.06) 0%, transparent 65%);
            pointer-events: none;
        }
        .benefits-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem; margin-top: 3.5rem;
        }
        .ben-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 1.25rem;
            padding: 1.75rem 1.875rem;
            display: flex; gap: 1.25rem; align-items: flex-start;
            cursor: default;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .ben-card:hover { background: rgba(255,255,255,0.055); border-color: rgba(255,255,255,0.1); }
        .ben-icon {
            width: 46px; height: 46px; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .ben-icon.blue { background: rgba(3,105,161,0.16); color: var(--blue-l); }
        .ben-icon.em   { background: rgba(16,185,129,0.12); color: var(--em-l); }
        .ben-title {
            font-family: 'EB Garamond', serif;
            font-size: 1.0625rem; font-weight: 700;
            color: var(--white); margin-bottom: 0.375rem;
        }
        .ben-desc { color: var(--muted); font-size: 0.9375rem; line-height: 1.62; }

        /* ══════════════════════════════════════════════════
           CTA
           ══════════════════════════════════════════════════ */
        .cta { padding: 7rem 2rem; background: white; text-align: center; }
        .cta-inner { max-width: 560px; margin: 0 auto; }
        .cta-h {
            font-family: 'EB Garamond', serif;
            font-size: clamp(2rem, 4vw, 2.875rem);
            font-weight: 700; letter-spacing: -0.02em; line-height: 1.1;
            color: var(--ink); margin-bottom: 1.125rem;
        }
        .cta-sub {
            color: var(--slate); font-size: 1.0625rem; line-height: 1.65;
            margin-bottom: 2.5rem;
            max-width: 44ch; margin-left: auto; margin-right: auto;
        }

        /* ══════════════════════════════════════════════════
           FOOTER
           ══════════════════════════════════════════════════ */
        footer {
            background: var(--navy);
            border-top: 1px solid var(--border);
            padding: 4.5rem 2rem 2.5rem;
        }
        .footer-inner { max-width: 80rem; margin: 0 auto; }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr 1fr;
            gap: 2.5rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        .footer-eyebrow {
            font-size: 0.6875rem; font-weight: 900;
            letter-spacing: 0.14em; text-transform: uppercase;
            color: var(--em-l); margin-bottom: 1.25rem; display: block;
        }
        .footer-link {
            display: block; color: var(--muted);
            font-size: 0.875rem; text-decoration: none;
            margin-bottom: 0.625rem;
            cursor: pointer;   /* ui-ux-pro-max: cursor-pointer */
            transition: color 0.18s ease;
        }
        .footer-link:hover { color: var(--white); }
        .footer-contact {
            display: flex; align-items: flex-start; gap: 0.625rem;
            font-size: 0.8125rem; color: var(--muted);
            margin-bottom: 0.75rem; line-height: 1.5;
        }
        .footer-sch-label {
            font-family: 'Lato', sans-serif;
            font-size: 0.8125rem; font-weight: 700;
            color: var(--white); margin-bottom: 0.2rem;
        }
        .footer-sch-val { font-size: 0.8125rem; color: var(--muted); margin-bottom: 0.75rem; }

        /* ══════════════════════════════════════════════════
           RESPONSIVE — ui-ux-pro-max: 375 / 768 / 1024 / 1440
           ══════════════════════════════════════════════════ */
        @media (max-width: 1100px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 900px) {
            .nav-links .nav-link { display: none; }
            .benefits-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .trust-bar-inner { justify-content: flex-start; }
        }
        @media (max-width: 768px) {
            .features-grid { grid-template-columns: 1fr; }
            .nav { top: 0.625rem; width: calc(100% - 1.25rem); padding: 0 1rem; }
        }
        @media (max-width: 600px) {
            .hero-title { font-size: clamp(2.5rem, 11vw, 3.75rem); }
            .hero-actions { flex-direction: column; }
            .btn-hero, .btn-ghost { justify-content: center; }
            .hero-stats { flex-wrap: wrap; }
            .stat-card  { flex: 1 1 calc(50% - 0.5rem); }
            .footer-grid { grid-template-columns: 1fr; }
            .trust-item .trust-sub { display: none; }
        }
        @media (max-width: 375px) {
            .hero-content { padding: 7.5rem 1.25rem 4rem; }
            .hero-badge { font-size: 0.625rem; }
        }
    </style>
</head>
<body>

{{-- ── Skip link (accesibilidad teclado) — ui-ux-pro-max ── --}}
<a href="#main-content" class="skip-link">Saltar al contenido principal</a>

{{-- ── NAVBAR FLOTANTE ── --}}
<nav class="nav scrolled" id="main-nav" role="navigation" aria-label="Navegación principal">
    <div class="nav-inner">

        <a href="#inicio" class="nav-brand" aria-label="Inicio — Alcaldía Municipal de Puerto Boyacá">
            <img src="{{ asset('images/logo-puerto-boyaca.png') }}" alt="Escudo de Puerto Boyacá">
            <div>
                <div class="nav-name">Alcaldía Municipal</div>
                <div class="nav-sub">Puerto Boyacá · Boyacá</div>
            </div>
        </a>

        <div class="nav-links">
            <a href="#inicio"           class="nav-link">Inicio</a>
            <a href="#funcionalidades"  class="nav-link">Módulos</a>
            <a href="#beneficios"       class="nav-link">Beneficios</a>
            <a href="#contacto"         class="nav-link">Contacto</a>
            @auth
                <a href="{{ url('/admin') }}" class="btn btn-primary" aria-label="Ir al panel de control">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Panel
                </a>
            @else
                <a href="/admin/login" class="btn btn-primary" aria-label="Iniciar sesión en el sistema">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Iniciar Sesión
                </a>
            @endauth
        </div>

    </div>
</nav>

{{-- ── HERO: Spline pantalla completa ── --}}
<section id="inicio" class="hero">
    <spline-viewer url="https://prod.spline.design/2xuDQNAsh4BD2R14/scene.splinecode" aria-hidden="true"></spline-viewer>
    <div class="scroll-ind" aria-hidden="true">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>

{{-- ── HERO INTRO: título y acciones ── --}}
<section class="hero-intro" id="main-content" aria-labelledby="hero-heading">
    <div class="hero-intro-inner">

        <div class="hero-badge reveal">
            <span class="pulse-dot" aria-hidden="true"></span>
            Sistema Oficial · Gestión ARL
        </div>

        <h1 class="hero-title reveal delay-1" id="hero-heading">
            Gestión de
            <span class="line-accent">Afiliaciones ARL</span>
        </h1>

        <p class="hero-desc reveal delay-2">
            Plataforma centralizada para la administración y control de afiliaciones a la ARL de contratistas en la Alcaldía Municipal de Puerto Boyacá.
        </p>

        <div class="hero-actions reveal delay-3">
            @auth
                <a href="{{ url('/admin') }}" class="btn btn-hero" aria-label="Acceder al panel de control del sistema ARL">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M13 7l5 5-5 5M6 12h12"/></svg>
                    Ir al Panel de Control
                </a>
            @else
                <a href="/admin/login" class="btn btn-hero" aria-label="Acceder al sistema ARL">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Iniciar Sesión
                </a>
            @endauth
            <a href="#funcionalidades" class="btn btn-ghost" aria-label="Ver módulos del sistema">
                Explorar Módulos
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 9l-7 7-7-7"/></svg>
            </a>
        </div>

        <div class="hero-stats reveal delay-4" role="list" aria-label="Indicadores del sistema">
            <div class="stat-card" role="listitem">
                <div class="stat-num c-blue">100%</div>
                <div class="stat-lbl">Digitalización</div>
            </div>
            <div class="stat-card" role="listitem">
                <div class="stat-num c-em">24/7</div>
                <div class="stat-lbl">Disponibilidad</div>
            </div>
            <div class="stat-card" role="listitem">
                <div class="stat-num" style="font-size:1.625rem;letter-spacing:-0.01em;">Seguro</div>
                <div class="stat-lbl">Encriptado</div>
            </div>
        </div>

    </div>
</section>

{{-- ── TRUST BAR — nuevo: prueba de cumplimiento normativo ── --}}
<div class="trust-bar" role="region" aria-label="Cumplimiento normativo">
    <div class="trust-bar-inner">
        <div class="trust-item">
            <div class="trust-icon" aria-hidden="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="trust-label">Decreto 1072 de 2015</div>
                <div class="trust-sub">Cumplimiento SG-SST</div>
            </div>
        </div>
        <div class="trust-item">
            <div class="trust-icon" aria-hidden="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="trust-label">Ley 1562 de 2012</div>
                <div class="trust-sub">Sistema General de Riesgos Laborales</div>
            </div>
        </div>
        <div class="trust-item">
            <div class="trust-icon" aria-hidden="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <div class="trust-label">Datos Seguros</div>
                <div class="trust-sub">Encriptación · Habeas Data</div>
            </div>
        </div>
        <div class="trust-item">
            <div class="trust-icon" aria-hidden="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <div class="trust-label">Auditoría Total</div>
                <div class="trust-sub">Trazabilidad de cada acción</div>
            </div>
        </div>
    </div>
</div>

{{-- ── FEATURES ── --}}
<section id="funcionalidades" class="features" aria-labelledby="features-heading">
    <div class="section-wrap">
        <div class="reveal">
            <span class="section-eyebrow blue">Módulos del sistema</span>
            <h2 class="section-h" id="features-heading" style="color:var(--ink);max-width:520px;">
                Todo lo que necesitas en una sola plataforma
            </h2>
            <p class="section-sub">
                Sistema completo con todas las herramientas para gestionar afiliaciones ARL de forma eficiente y profesional.
            </p>
        </div>

        <div class="features-grid" role="list">
            @php
            $features = [
                ['blue','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                 'Gestión Centralizada','Administra todas las afiliaciones ARL desde una única plataforma intuitiva y organizada por dependencia.'],
                ['em','M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                 'Control de Acceso','Roles y permisos diferenciados: Administrador, Dependencia y SSST con acceso granular por módulo.'],
                ['blue','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                 'Flujo de Validación','Proceso estructurado de aprobación con estados, trazabilidad completa y seguimiento en tiempo real.'],
                ['em','M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                 'Gestión Documental','Carga, almacenamiento y consulta segura de documentos PDF e imágenes con organización automática.'],
                ['blue','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                 'Dashboard Analítico','Visualización de estadísticas en tiempo real con gráficas interactivas y reportes personalizables.'],
                ['em','M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                 'Importación Masiva','Importa y exporta datos masivamente con archivos Excel y validación automática de información.'],
                ['blue','M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                 'Notificaciones','Alertas automáticas por correo para vencimientos de afiliaciones, nuevos registros y eventos críticos.'],
                ['em','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                 'Auditoría Completa','Registro detallado de todas las acciones con historial completo y trazabilidad de cada cambio.'],
                ['blue','M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                 'Máxima Seguridad','Protección robusta con Laravel, encriptación de datos sensibles y cumplimiento de normativas colombianas.'],
            ];
            $delays = ['delay-1','delay-2','delay-3','delay-1','delay-2','delay-3','delay-1','delay-2','delay-3'];
            @endphp

            @foreach($features as $i => $f)
            <div class="feat-card reveal {{ $delays[$i] }}" role="listitem">
                <div class="feat-icon {{ $f[0] }}" aria-hidden="true">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="{{ $f[1] }}"/>
                    </svg>
                </div>
                <h3 class="feat-title">{{ $f[2] }}</h3>
                <p class="feat-desc">{{ $f[3] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── BENEFITS (dark) ── --}}
<section id="beneficios" class="benefits" aria-labelledby="benefits-heading">
    <div class="section-wrap" style="position:relative;z-index:1;">
        <div class="reveal">
            <span class="section-eyebrow em">Por qué este sistema</span>
            <h2 class="section-h" id="benefits-heading" style="color:var(--white);max-width:520px;">
                Impacto real en tu dependencia
            </h2>
            <p class="section-sub" style="color:var(--muted);">
                Diseñado para los procesos administrativos específicos de la Alcaldía Municipal de Puerto Boyacá.
            </p>
        </div>

        <div class="benefits-grid" role="list">
            @php
            $benefits = [
                ['blue','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                 'Ahorro de Tiempo del 70%',
                 'Reduce drásticamente el tiempo dedicado a gestión manual de documentos y validaciones administrativas.'],
                ['em','M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                 'Eliminación de Papel',
                 'Sistema 100% digital que contribuye al medio ambiente y facilita el acceso instantáneo a la información.'],
                ['blue','M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                 'Transparencia Total',
                 'Trazabilidad completa de cada proceso con auditoría detallada de todas las acciones realizadas.'],
                ['em','M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                 'Control Preventivo',
                 'Alertas automáticas de afiliaciones próximas a vencer para tomar acciones preventivas a tiempo.'],
                ['blue','M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                 'Acceso Remoto 24/7',
                 'Disponible desde cualquier dispositivo con conexión a internet, en cualquier momento del día.'],
                ['em','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                 'Reportes Inteligentes',
                 'Generación automática de informes y estadísticas para una toma de decisiones fundamentada.'],
            ];
            @endphp

            @foreach($benefits as $i => $b)
            <div class="ben-card reveal delay-{{ ($i % 3) + 1 }}" role="listitem">
                <div class="ben-icon {{ $b[0] }}" aria-hidden="true">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="{{ $b[1] }}"/>
                    </svg>
                </div>
                <div>
                    <h3 class="ben-title">{{ $b[2] }}</h3>
                    <p class="ben-desc">{{ $b[3] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="cta" aria-labelledby="cta-heading">
    <div class="cta-inner reveal">
        <span class="section-eyebrow blue">Comienza ahora</span>
        <h2 class="cta-h" id="cta-heading">¿Listo para digitalizar la gestión de ARL?</h2>
        <p class="cta-sub">Accede al sistema y optimiza los procesos de afiliación de tu dependencia desde hoy mismo.</p>
        @auth
            <a href="{{ url('/admin') }}" class="btn btn-cta" aria-label="Ir al panel de control">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M13 7l5 5-5 5M6 12h12"/></svg>
                Ir al Panel de Control
            </a>
        @else
            <a href="/admin/login" class="btn btn-cta" aria-label="Acceder al sistema">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                Iniciar Sesión Ahora
            </a>
        @endauth
    </div>
</section>

{{-- ── FOOTER ── --}}
<footer id="contacto" role="contentinfo">
    <div class="footer-inner">
        <div class="footer-grid">

            <div>
                <img src="{{ asset('images/logo-puerto-boyaca.png') }}"
                     alt="Escudo de la Alcaldía de Puerto Boyacá"
                     style="height:2.75rem;width:auto;margin-bottom:1.25rem;">
                <p style="font-size:0.875rem;color:var(--muted);line-height:1.7;max-width:260px;">
                    Sistema oficial de gestión de afiliaciones ARL para contratistas de la Alcaldía Municipal de Puerto Boyacá.
                </p>
            </div>

            <div>
                <span class="footer-eyebrow">Navegación</span>
                <a href="#inicio"          class="footer-link">Inicio</a>
                <a href="#funcionalidades" class="footer-link">Módulos</a>
                <a href="#beneficios"      class="footer-link">Beneficios</a>
                <a href="/admin/login"     class="footer-link">Iniciar Sesión</a>
            </div>

            <div>
                <span class="footer-eyebrow">Contacto</span>
                <div class="footer-contact">
                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"
                         style="flex-shrink:0;margin-top:2px;" aria-hidden="true">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <a href="mailto:contactenos@puertoboyaca-boyaca.gov.co"
                       class="footer-link" style="margin-bottom:0;">
                        contactenos@puertoboyaca-boyaca.gov.co
                    </a>
                </div>
                <div class="footer-contact">
                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"
                         style="flex-shrink:0;margin-top:2px;" aria-hidden="true">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                    <a href="tel:+5787383300" class="footer-link" style="margin-bottom:0;">
                        +57 (8) 738 33 00
                    </a>
                </div>
                <div class="footer-contact">
                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"
                         style="flex-shrink:0;margin-top:2px;" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Carrera 2 Número 10-21, Edificio Municipal</span>
                </div>
            </div>

            <div>
                <span class="footer-eyebrow">Horario</span>
                <div class="footer-sch-label">Lunes a Jueves</div>
                <div class="footer-sch-val">8:00 AM – 12:00 PM · 2:00 PM – 6:00 PM</div>
                <div class="footer-sch-label">Viernes</div>
                <div class="footer-sch-val">8:00 AM – 12:00 PM · 2:00 PM – 5:00 PM</div>
            </div>

        </div>
        <div style="text-align:center;">
            <p style="font-size:0.8125rem;color:var(--muted);">
                &copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá. Todos los derechos reservados.
            </p>
            <p style="font-size:0.6875rem;color:rgba(148,163,184,0.35);margin-top:0.25rem;">
                Sistema de Gestión de Afiliaciones ARL
            </p>
        </div>
    </div>
</footer>

<script>
// ════════════════════════════════════════════════
//  Ocultar logo de Spline (shadow DOM)
// ════════════════════════════════════════════════
(function hideSplineLogo() {
    const viewer = document.querySelector('spline-viewer');
    if (!viewer) return;
    const hide = () => {
        const root = viewer.shadowRoot;
        if (!root) return;
        ['#logo','a[href*="spline"]','[class*="logo"]'].forEach(sel => {
            root.querySelectorAll(sel).forEach(el => el.style.setProperty('display','none','important'));
        });
    };
    viewer.addEventListener('load', hide);
    // fallback por si ya cargó
    setTimeout(hide, 1500);
    setTimeout(hide, 4000);
})();

// ════════════════════════════════════════════════
//  prefers-reduced-motion — ui-ux-pro-max: reduced-motion
// ════════════════════════════════════════════════
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ════════════════════════════════════════════════
//  SCROLL REVEAL — ui-ux-pro-max: animation
// ════════════════════════════════════════════════
if (!prefersReducedMotion) {
    const revealEls = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.07, rootMargin: '0px 0px -32px 0px' });

    revealEls.forEach(el => io.observe(el));

    // Hero: visible de inmediato
    document.querySelectorAll('.hero .reveal').forEach(el => el.classList.add('visible'));
} else {
    // Si prefiere sin movimiento: mostrar todo de una vez
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
}

// ── Nav: clase 'scrolled' al hacer scroll ──
const nav = document.getElementById('main-nav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 10);
}, { passive: true });

// ── Smooth scroll ──
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(a.getAttribute('href'));
        if (target) window.scrollTo({
            top: target.offsetTop - 82,
            behavior: prefersReducedMotion ? 'auto' : 'smooth'
        });
    });
});
</script>
</body>
</html>
