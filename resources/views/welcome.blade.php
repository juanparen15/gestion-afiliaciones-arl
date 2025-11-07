<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema ARL - Alcaldía de Puerto Boyacá</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .gradient-blue-green {
            background: linear-gradient(135deg, var(--color-primary-blue) 0%, var(--color-primary-green) 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--color-primary-blue) 0%, var(--color-primary-green) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .blob {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: blob-animation 8s ease-in-out infinite;
        }

        @keyframes blob-animation {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            25% { border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%; }
            50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
            75% { border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%; }
        }

        .fade-in {
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(51, 102, 204, 0.2);
        }
    </style>
</head>
<body class="antialiased bg-gray-50">

    <!-- Navigation -->
    <nav class="fixed w-full bg-white/90 backdrop-blur-md shadow-sm z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo y Título -->
                <div class="flex items-center space-x-4">
                    <img src="{{ asset('images/logo-puerto-boyaca.png') }}" alt="Alcaldía de Puerto Boyacá" class="h-12 w-auto">
                    <div class="hidden lg:block">
                        <div class="text-base font-bold" style="color: var(--color-primary-blue);">Alcaldía Municipal</div>
                        <div class="text-sm font-semibold" style="color: var(--color-primary-green);">Puerto Boyacá - Boyacá</div>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#inicio" class="text-gray-700 hover:text-[#3366cc] transition-colors font-semibold text-sm">Inicio</a>
                    <a href="#funcionalidades" class="text-gray-700 hover:text-[#3366cc] transition-colors font-semibold text-sm">Funcionalidades</a>
                    <a href="#beneficios" class="text-gray-700 hover:text-[#3366cc] transition-colors font-semibold text-sm">Beneficios</a>
                    <a href="#contacto" class="text-gray-700 hover:text-[#3366cc] transition-colors font-semibold text-sm">Contacto</a>

                    @auth
                        <a href="{{ url('/admin') }}" class="inline-flex items-center px-6 py-3 rounded-lg text-white font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 gradient-blue-green">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Panel Administrativo
                        </a>
                    @else
                        <a href="/admin/login" class="inline-flex items-center px-6 py-3 rounded-lg text-white font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 gradient-blue-green">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Iniciar Sesión
                        </a>
                    @endauth
                </div>

                <!-- Mobile Button -->
                <div class="md:hidden">
                    @auth
                        <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2.5 rounded-lg text-white text-sm font-bold shadow-lg gradient-blue-green">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Panel
                        </a>
                    @else
                        <a href="/admin/login" class="inline-flex items-center px-5 py-2.5 rounded-lg text-white text-sm font-bold shadow-lg gradient-blue-green">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Ingresar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <!-- Decorative Background -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 right-10 w-96 h-96 bg-[#3366cc] opacity-10 blob"></div>
            <div class="absolute bottom-10 left-10 w-80 h-80 bg-[#008000] opacity-10 blob" style="animation-delay: -4s;"></div>
        </div>

        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="fade-in">
                    <div class="inline-block px-4 py-2 bg-blue-50 rounded-full mb-6">
                        <span class="text-[#3366cc] font-bold text-sm">Sistema Oficial de Gestión ARL</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-gray-900 mb-6 leading-tight">
                        Gestión de
                        <span class="gradient-text block">Afiliaciones ARL</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-4 leading-relaxed">
                        Sistema web especializado para la administración y control de afiliaciones a la ARL de contratistas en la Alcaldía Municipal de Puerto Boyacá.
                    </p>

                    <p class="text-lg text-gray-500 mb-8">
                        Centraliza, digitaliza y optimiza todos los procesos de gestión documental de riesgos laborales.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        @auth
                            <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl text-white font-bold text-lg shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 gradient-blue-green">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                Ir al Panel de Control
                            </a>
                        @else
                            <a href="/admin/login" class="inline-flex items-center justify-center px-8 py-4 rounded-xl text-white font-bold text-lg shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 gradient-blue-green">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Iniciar Sesión
                            </a>
                        @endauth

                        <a href="#funcionalidades" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-white text-gray-800 font-bold text-lg border-2 border-gray-200 hover:border-[#3366cc] hover:bg-gray-50 transition-all duration-300 shadow-md">
                            Conocer Más
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Hero Image/Card -->
                <div class="relative fade-in" style="animation-delay: 0.2s;">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <!-- Gradient Background -->
                        <div class="gradient-blue-green p-8">
                            <div class="bg-white rounded-xl p-8 shadow-lg">
                                <div class="space-y-6">
                                    <!-- Stat Item -->
                                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                        <div>
                                            <div class="text-sm text-gray-600 font-semibold">Gestión Digital</div>
                                            <div class="text-3xl font-extrabold text-[#3366cc]">100%</div>
                                        </div>
                                        <div class="w-12 h-12 bg-[#3366cc] rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Stat Item -->
                                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                        <div>
                                            <div class="text-sm text-gray-600 font-semibold">Disponibilidad</div>
                                            <div class="text-3xl font-extrabold text-[#008000]">24/7</div>
                                        </div>
                                        <div class="w-12 h-12 bg-[#008000] rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Stat Item -->
                                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                        <div>
                                            <div class="text-sm text-gray-600 font-semibold">Seguridad</div>
                                            <div class="text-2xl font-extrabold text-[#3366cc]">Garantizada</div>
                                        </div>
                                        <div class="w-12 h-12 bg-[#3366cc] rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features/Funcionalidades Section -->
    <section id="funcionalidades" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-2 bg-blue-50 rounded-full mb-4">
                    <span class="text-[#3366cc] font-bold text-sm">Potentes Funcionalidades</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                    Todo lo que Necesitas
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Sistema completo con todas las herramientas para gestionar afiliaciones ARL de manera eficiente y profesional
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature Card 1 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Gestión Centralizada</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Administra todas las afiliaciones ARL de contratistas desde una única plataforma intuitiva y fácil de usar
                    </p>
                </div>

                <!-- Feature Card 2 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Control de Acceso</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Sistema de roles y permisos con niveles de acceso diferenciados: Administrador, Dependencia y SSST
                    </p>
                </div>

                <!-- Feature Card 3 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Flujo de Validación</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Proceso estructurado de aprobación con estados, trazabilidad completa y seguimiento en tiempo real
                    </p>
                </div>

                <!-- Feature Card 4 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Gestión Documental</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Carga, almacenamiento y consulta segura de documentos PDF e imágenes con organización automática
                    </p>
                </div>

                <!-- Feature Card 5 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Dashboard Analítico</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Visualización de estadísticas en tiempo real con gráficas interactivas y reportes personalizables
                    </p>
                </div>

                <!-- Feature Card 6 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Importación Masiva</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Importa y exporta datos masivamente mediante archivos Excel con validación automática de información
                    </p>
                </div>

                <!-- Feature Card 7 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Notificaciones</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Sistema automático de notificaciones por correo electrónico para eventos importantes y vencimientos
                    </p>
                </div>

                <!-- Feature Card 8 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Auditoría Completa</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Registro detallado de todas las acciones realizadas en el sistema con historial completo de cambios
                    </p>
                </div>

                <!-- Feature Card 9 -->
                <div class="bg-white p-8 rounded-2xl border-2 border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl gradient-blue-green flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Máxima Seguridad</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Protección robusta con Laravel, encriptación de datos sensibles y cumplimiento de normativas
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="beneficios" class="py-20 bg-gradient-to-br from-blue-50 to-green-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-2 bg-white rounded-full mb-4 shadow-sm">
                    <span class="gradient-text font-bold text-sm">Beneficios Clave</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                    ¿Por Qué Este Sistema?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Optimiza los procesos administrativos y mejora la eficiencia de tu dependencia
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Benefit 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-[#3366cc]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Ahorro de Tiempo</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Reduce hasta un 70% el tiempo dedicado a gestión manual de documentos y validaciones
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Benefit 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-[#008000]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Eliminación de Papel</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Sistema 100% digital que contribuye al medio ambiente y facilita el acceso a la información
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Benefit 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-[#3366cc]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Transparencia Total</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Trazabilidad completa de procesos con auditoría de todas las acciones realizadas
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Benefit 4 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-[#008000]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Control Efectivo</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Alertas automáticas de contratos próximos a vencer para tomar acciones preventivas
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Benefit 5 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-[#3366cc]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Acceso Remoto</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Disponible 24/7 desde cualquier dispositivo con conexión a internet
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Benefit 6 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-[#008000]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Reportes Inteligentes</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Generación automática de informes y estadísticas para la toma de decisiones
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- CTA Section -->
    <section class="py-20 relative overflow-hidden">
        <div class="absolute inset-0 gradient-blue-green opacity-95"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">
                ¿Listo para Digitalizar la Gestión de ARL?
            </h2>
            <p class="text-xl text-white/90 mb-10 leading-relaxed">
                Accede al sistema ahora y comienza a optimizar los procesos de afiliación de tu dependencia
            </p>

            @auth
                <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-10 py-4 rounded-xl bg-white text-[#3366cc] font-bold text-lg shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Ir al Panel de Control
                </a>
            @else
                <a href="/admin/login" class="inline-flex items-center justify-center px-10 py-4 rounded-xl bg-white text-[#3366cc] font-bold text-lg shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Iniciar Sesión Ahora
                </a>
            @endauth
        </div>
    </section>

    <!-- Contact/Footer Section -->
    <footer id="contacto" class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                <!-- Column 1: Logo & Info -->
                <div class="lg:col-span-1">
                    <img src="{{ asset('images/logo-puerto-boyaca.png') }}" alt="Alcaldía de Puerto Boyacá" class="h-16 w-auto mb-4">
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Sistema oficial de gestión de afiliaciones ARL para contratistas de la Alcaldía Municipal de Puerto Boyacá
                    </p>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-[#3366cc]">Navegación</h4>
                    <ul class="space-y-3">
                        <li><a href="#inicio" class="text-gray-400 hover:text-white transition-colors text-sm">Inicio</a></li>
                        <li><a href="#funcionalidades" class="text-gray-400 hover:text-white transition-colors text-sm">Funcionalidades</a></li>
                        <li><a href="#beneficios" class="text-gray-400 hover:text-white transition-colors text-sm">Beneficios</a></li>
                        <li><a href="/admin/login" class="text-gray-400 hover:text-white transition-colors text-sm">Iniciar Sesión</a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-[#008000]">Contacto</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <span class="text-gray-400">contactenos@puertoboyaca-boyaca.gov.co</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            <span class="text-gray-400">+57 (8) 738 33 00</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-400">Edificio Municipal Carrera 2 Número 10-21</span>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Schedule -->
                <div>
                    <h4 class="text-lg font-bold mb-4 text-[#3366cc]">Horario de Atención</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li class="font-semibold text-white">Lunes a Jueves</li>
                        <li>8:00 AM - 12:00 PM</li>
                        <li>2:00 PM - 6:00 PM</li>
                        <li class="font-semibold text-white mt-3">Viernes</li>
                        <li>8:00 AM - 12:00 PM</li>
                        <li>2:00 PM - 5:00 PM</li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-gray-800 pt-8">
                <div class="text-center">
                    <p class="text-gray-400 text-sm">
                        &copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá. Todos los derechos reservados.
                    </p>
                    <p class="text-gray-500 text-xs mt-2">
                        Sistema de Gestión de Afiliaciones ARL
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Smooth Scroll Script -->
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add active class to nav on scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('nav a[href^="#"]');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('text-[#3366cc]');
                if (link.getAttribute('href').slice(1) === current) {
                    link.classList.add('text-[#3366cc]');
                }
            });
        });
    </script>
</body>
</html>
