<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Solicitud BPIM · Alcaldía de Puerto Boyacá</title>
    <link rel="icon" href="{{ asset('images/actas/logo-alcaldia.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .campo-requerido::after {
            content: " *";
            color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Encabezado -->
            <div class="bg-blue-800 text-white p-8 rounded-t-lg flex items-center gap-4">
                <img src="{{ asset('images/actas/logo-alcaldia.png') }}" alt="Alcaldía de Puerto Boyacá" class="h-14 w-auto flex-shrink-0">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-1">FORMULARIO SOLICITUD FICHA BPIM/BPIN</h1>
                    <p class="text-blue-100">Alcaldía Municipal de Puerto Boyacá, Boyacá</p>
                </div>
            </div>

            <!-- Formulario -->
            <form id="formulario-bpim" method="POST" action="{{ route('bpim.formulario.store') }}" class="bg-white shadow-lg rounded-b-lg">
                @csrf

                <div class="p-8 space-y-6">

                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-400 p-4">
                            <p class="text-red-700">{{ session('error') }}</p>
                        </div>
                    @endif

                    <!-- Autorización de Datos -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <h3 class="font-bold mb-2">AUTORIZACIÓN USO DE DATOS</h3>
                        <p class="text-sm mb-4">En cumplimiento de la Ley 1581 de 2012 y del Decreto 1377 de 2013, solicitamos su autorización para que la ALCALDIA MUNICIPAL DE PUERTO BOYACA pueda recopilar, almacenar y usar los datos proporcionados.</p>

                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="autorizacion_uso_datos" value="1"
                                   class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                                   {{ old('autorizacion_uso_datos') ? 'checked' : '' }} required>
                            <span class="campo-requerido">SÍ, autorizo el uso de mis datos</span>
                        </label>
                        @error('autorizacion_uso_datos')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Consecutivo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                            CONSECUTIVO DE LA SOLICITUD CON EL CÓDIGO DE LA DEPENDENCIA
                        </label>
                        <p class="text-xs text-gray-500 mb-2">EJEMPLO: SGA-SI-#-#-#</p>
                        <input type="text" name="consecutivo" value="{{ old('consecutivo') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="SGA-SI-001-2025-01" required>
                        @error('consecutivo')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dependencia -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">DEPENDENCIA</label>
                        <select name="dependencia" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Seleccione...</option>
                            <option value="SECRETARIA DE DESARROLLO" {{ old('dependencia') == 'SECRETARIA DE DESARROLLO' ? 'selected' : '' }}>SECRETARIA DE DESARROLLO</option>
                            <option value="SECRETARIA DE GOBIERNO" {{ old('dependencia') == 'SECRETARIA DE GOBIERNO' ? 'selected' : '' }}>SECRETARIA DE GOBIERNO</option>
                            <option value="SECRETARIA DE GENERAL" {{ old('dependencia') == 'SECRETARIA DE GENERAL' ? 'selected' : '' }}>SECRETARIA DE GENERAL</option>
                            <option value="SECRETARIA DE HACIENDA" {{ old('dependencia') == 'SECRETARIA DE HACIENDA' ? 'selected' : '' }}>SECRETARIA DE HACIENDA</option>
                            <option value="DIRECCION DE TRANSITO Y TRANSPORTE" {{ old('dependencia') == 'DIRECCION DE TRANSITO Y TRANSPORTE' ? 'selected' : '' }}>DIRECCION DE TRANSITO Y TRANSPORTE</option>
                            <option value="SECRETARIA DE PLANEACION" {{ old('dependencia') == 'SECRETARIA DE PLANEACION' ? 'selected' : '' }}>SECRETARIA DE PLANEACION</option>
                            <option value="SECRETARIA DE OBRAS PUBLICAS" {{ old('dependencia') == 'SECRETARIA DE OBRAS PUBLICAS' ? 'selected' : '' }}>SECRETARIA DE OBRAS PUBLICAS</option>
                            <option value="DESPACHO ALCALDE" {{ old('dependencia') == 'DESPACHO ALCALDE' ? 'selected' : '' }}>DESPACHO ALCALDE</option>
                            <option value="UMATA" {{ old('dependencia') == 'UMATA' ? 'selected' : '' }}>UMATA</option>
                            <option value="INSTITUTO MUNICIPAL DE RECREACIÓN Y DEPORTE" {{ old('dependencia') == 'INSTITUTO MUNICIPAL DE RECREACIÓN Y DEPORTE' ? 'selected' : '' }}>INSTITUTO MUNICIPAL DE RECREACIÓN Y DEPORTE</option>
                        </select>
                        @error('dependencia')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nombre del Solicitante -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                            NOMBRE DEL SECRETARIO DE DESPACHO O SUPERVISOR
                        </label>
                        <select name="nombre_solicitante" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Seleccione...</option>
                            <option value="DANIEL ANDRES MAHECHA ORDOÑEZ">DANIEL ANDRES MAHECHA ORDOÑEZ</option>
                            <option value="PEDRO ANTONIO CANO ALVAREZ">PEDRO ANTONIO CANO ALVAREZ</option>
                            <option value="JUAN PABLO SALAZAR SALAZAR">JUAN PABLO SALAZAR SALAZAR</option>
                            <option value="WILTON ALEXIS CULMAN CAMBEROS">WILTON ALEXIS CULMAN CAMBEROS</option>
                            <option value="ANGELICA ALEXANDRA RUBIANO GUTIERREZ">ANGELICA ALEXANDRA RUBIANO GUTIERREZ</option>
                            <option value="ANA JOSEFINA PEÑA SERNA">ANA JOSEFINA PEÑA SERNA</option>
                            <option value="JUAN DIEGO RONDON ALBAO">JUAN DIEGO RONDON ALBAO</option>
                            <option value="SANDRA PATRICIA CORRALES MORALES">SANDRA PATRICIA CORRALES MORALES</option>
                            <option value="FABIAN MURILLO MARIN">FABIAN MURILLO MARIN</option>
                            <option value="NASLY RAMIREZ SANCHEZ">NASLY RAMIREZ SANCHEZ</option>
                        </select>
                        @error('nombre_solicitante')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Selección de Proyecto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                            SELECCIONAR PROYECTO
                        </label>
                        <select id="proyecto_id" name="proyecto_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Seleccione un proyecto --</option>
                            @foreach($proyectos as $proyecto)
                                <option value="{{ $proyecto->id }}"
                                        data-nombre="{{ $proyecto->nombre_proyecto }}"
                                        data-bpim="{{ $proyecto->codigo_bpim }}"
                                        data-bpin="{{ $proyecto->codigo_bpin }}">
                                    {{ $proyecto->nombre_proyecto }} - BPIM: {{ $proyecto->codigo_bpim }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Los códigos y catálogo de ítems se cargarán al seleccionar el proyecto</p>
                    </div>

                    <!-- Nombre del Proyecto (readonly) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">NOMBRE DEL PROYECTO</label>
                        <input type="text" id="nombre_proyecto" name="nombre_proyecto" value="{{ old('nombre_proyecto') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                               readonly required>
                        @error('nombre_proyecto')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Códigos (readonly) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">CÓDIGO BPIM</label>
                            <input type="text" id="codigo_bpim" name="codigo_bpim" value="{{ old('codigo_bpim') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly required>
                            @error('codigo_bpim')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CÓDIGO BPIN</label>
                            <input type="text" id="codigo_bpin" name="codigo_bpin" value="{{ old('codigo_bpin') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                        </div>
                    </div>

                    <!-- Objeto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                            OBJETO DEL PROCESO CONTRACTUAL QUE SOLICITA
                        </label>
                        <p class="text-xs text-gray-500 mb-2">(DE ACUERDO A LA DISPONIBILIDAD DEL PROYECTO)</p>
                        <textarea name="objeto" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>{{ old('objeto') }}</textarea>
                        @error('objeto')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Items de Presupuesto -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-bold mb-4 campo-requerido">PRESUPUESTO DE LA SOLICITUD</h3>

                        <div id="presupuesto-container" class="space-y-4">
                            <!-- Template para items de presupuesto -->
                            <div class="presupuesto-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-semibold">Item #<span class="item-numero">1</span></h4>
                                    <button type="button" class="remove-item text-red-600 hover:text-red-800 hidden">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 campo-requerido">
                                            Seleccionar Item del Catálogo
                                        </label>
                                        <select name="presupuesto_items[0][item_catalogo_id]" class="item-catalogo-select w-full px-3 py-2 border border-gray-300 rounded" required disabled>
                                            <option value="">-- Primero seleccione un proyecto --</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Al seleccionar, se autocompletan descripción, unidad y valor unitario</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                        <textarea name="presupuesto_items[0][descripcion]" rows="2"
                                                  class="item-descripcion w-full px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly required></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                                            <input type="text" name="presupuesto_items[0][unidad]"
                                                   class="item-unidad w-full px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Unitario ($)</label>
                                            <input type="number" name="presupuesto_items[0][valor_unitario]" step="0.01" min="0"
                                                   class="valor-unitario w-full px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                            <input type="number" name="presupuesto_items[0][cantidad]" min="1"
                                                   class="cantidad w-full px-3 py-2 border border-gray-300 rounded" required>
                                            <p class="text-xs mt-1 cantidad-limite text-gray-500"></p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total ($)</label>
                                            <input type="number" name="presupuesto_items[0][valor_total]" step="0.01" min="0"
                                                   class="valor-total w-full px-3 py-2 border border-gray-300 rounded bg-gray-50" readonly required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-item" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            + Agregar Item de Presupuesto
                        </button>
                        @error('presupuesto_items')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- CDP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                            DISPONIBILIDAD PRESUPUESTAL (C.D.P)
                        </label>
                        <p class="text-xs text-gray-500 mb-2">EJEMPLO: 2023.CEN.01.010201</p>
                        <input type="text" name="cdp" value="{{ old('cdp') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        @error('cdp')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valores -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                                VALOR DEL C.D.P
                            </label>
                            <p class="text-xs text-gray-500 mb-2">(En números, sin comas, ni puntos, ni signos)</p>
                            <input type="number" name="valor_cdp" value="{{ old('valor_cdp') }}" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('valor_cdp')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                                VALOR DEL ESTUDIO PREVIO
                            </label>
                            <p class="text-xs text-gray-500 mb-2">(En números, sin comas, ni puntos, ni signos)</p>
                            <input type="number" name="valor_ep" value="{{ old('valor_ep') }}" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('valor_ep')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Objeto de Gasto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">OBJETO DEL GASTO (VER CDP)</label>
                        <input type="text" name="objeto_gasto" value="{{ old('objeto_gasto') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        @error('objeto_gasto')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fuente de Recursos -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">FUENTE DE RECURSOS (VER CDP)</label>
                        <input type="text" name="fuente_recursos" value="{{ old('fuente_recursos') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        @error('fuente_recursos')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Otra Fuente de Recurso -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">TIENE OTRA FUENTE DE RECURSO - SI - ESCRÍBALA</label>
                        <input type="text" name="otra_fuente_recurso" value="{{ old('otra_fuente_recurso') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Opcional">
                        @error('otra_fuente_recurso')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- MGA y CPC -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">PRODUCTO MGA (VER CDP)</label>
                            <input type="text" name="mga" value="{{ old('mga') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('mga')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">PRODUCTO CPC (VER CDP)</label>
                            <input type="text" name="cpc" value="{{ old('cpc') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('cpc')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Plan de Desarrollo Nacional -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-bold mb-4">PLAN DE DESARROLLO NACIONAL</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">SECTOR (VER ESQUEMA FINANCIERO MGA)</label>
                                <select name="pdn_sector" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CONGRESO">CONGRESO</option>
                                    <option value="PRESIDENCIA DE LA REPÚBLICA">PRESIDENCIA DE LA REPÚBLICA</option>
                                    <option value="PLANEACIÓN">PLANEACIÓN</option>
                                    <option value="INFORMACIÓN ESTADÍSTICA">INFORMACIÓN ESTADÍSTICA</option>
                                    <option value="EMPLEO PÚBLICO">EMPLEO PÚBLICO</option>
                                    <option value="RELACIONES EXTERIORES">RELACIONES EXTERIORES</option>
                                    <option value="JUSTICIA Y DEL DERECHO">JUSTICIA Y DEL DERECHO</option>
                                    <option value="HACIENDA">HACIENDA</option>
                                    <option value="DEFENSA Y POLICÍA">DEFENSA Y POLICÍA</option>
                                    <option value="AGRICULTURA Y DESARROLLO RURAL">AGRICULTURA Y DESARROLLO RURAL</option>
                                    <option value="SALUD Y PROTECCIÓN SOCIAL">SALUD Y PROTECCIÓN SOCIAL</option>
                                    <option value="MINAS Y ENERGÍA">MINAS Y ENERGÍA</option>
                                    <option value="EDUCACIÓN">EDUCACIÓN</option>
                                    <option value="TECNOLOGÍAS DE LA INFORMACIÓN Y LAS COMUNICACIONES">TECNOLOGÍAS DE LA INFORMACIÓN Y LAS COMUNICACIONES</option>
                                    <option value="TRANSPORTE">TRANSPORTE</option>
                                    <option value="AMBIENTE Y DESARROLLO SOSTENIBLE">AMBIENTE Y DESARROLLO SOSTENIBLE</option>
                                    <option value="CULTURA">CULTURA</option>
                                    <option value="COMERCIO, INDUSTRIA Y TURISMO">COMERCIO, INDUSTRIA Y TURISMO</option>
                                    <option value="TRABAJO">TRABAJO</option>
                                    <option value="INTERIOR">INTERIOR</option>
                                    <option value="CIENCIA, TECNOLOGÍA E INNOVACIÓN">CIENCIA, TECNOLOGÍA E INNOVACIÓN</option>
                                    <option value="VIVIENDA, CIUDAD Y TERRITORIO">VIVIENDA, CIUDAD Y TERRITORIO</option>
                                    <option value="INCLUSIÓN SOCIAL Y RECONCILIACIÓN">INCLUSIÓN SOCIAL Y RECONCILIACIÓN</option>
                                    <option value="DEPORTE Y RECREACIÓN">DEPORTE Y RECREACIÓN</option>
                                </select>
                                @error('pdn_sector')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">PROGRAMA (VER ESQUEMA FINANCIERO MGA)</label>
                                <input type="text" name="pdn_programa" value="{{ old('pdn_programa') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                @error('pdn_programa')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">SUBPROGRAMA (VER ESQUEMA FINANCIERO MGA)</label>
                                <select name="pdn_subprograma" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                    <option value="">Seleccione...</option>
                                    <option value="1000 - Intersubsectorial Gobierno">1000 - Intersubsectorial Gobierno</option>
                                    <option value="1003 - Planificación y estadística">1003 - Planificación y estadística</option>
                                    <option value="1002 - Relaciones Exteriores">1002 - Relaciones Exteriores</option>
                                    <option value="0800 - Intersubsectorial Justicia">0800 - Intersubsectorial Justicia</option>
                                    <option value="0100 - Intersubsectorial Defensa y Seguridad">0100 - Intersubsectorial Defensa y Seguridad</option>
                                    <option value="1100 - Intersubsectorial Agricultura y Desarrollo Rural">1100 - Intersubsectorial Agricultura y Desarrollo Rural</option>
                                    <option value="0300 - Intersubsectorial Salud">0300 - Intersubsectorial Salud</option>
                                    <option value="1900 - Intersubsectorial Minas y Energía">1900 - Intersubsectorial Minas y Energía</option>
                                    <option value="0700 - Intersubsectorial Educación">0700 - Intersubsectorial Educación</option>
                                    <option value="0400 - Intersubsectorial Comunicaciones">0400 - Intersubsectorial Comunicaciones</option>
                                    <option value="0600 - Intersubsectorial Transporte">0600 - Intersubsectorial Transporte</option>
                                    <option value="0900 - Intersubsectorial Ambiente">0900 - Intersubsectorial Ambiente</option>
                                    <option value="1603 - Arte y cultura">1603 - Arte y cultura</option>
                                    <option value="0200 - Intersubsectorial Industria y Comercio">0200 - Intersubsectorial Industria y Comercio</option>
                                    <option value="1300 - Intersubsectorial Trabajo y Bienestar Social">1300 - Intersubsectorial Trabajo y Bienestar Social</option>
                                    <option value="1400 - Intersubsectorial Vivienda y Desarrollo Territorial">1400 - Intersubsectorial Vivienda y Desarrollo Territorial</option>
                                    <option value="1500 - Intersubsectorial Desarrollo Social">1500 - Intersubsectorial Desarrollo Social</option>
                                    <option value="1604 - Recreación y Deporte">1604 - Recreación y Deporte</option>
                                </select>
                                @error('pdn_subprograma')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Plan de Desarrollo Municipal -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-bold mb-4">PLAN DE DESARROLLO MUNICIPAL</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">SECTOR</label>
                                <input type="text" name="pdm_sector" value="{{ old('pdm_sector') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                @error('pdm_sector')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">PROGRAMA</label>
                                <input type="text" name="pdm_programa" value="{{ old('pdm_programa') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                @error('pdm_programa')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">PRODUCTO</label>
                                <input type="text" name="pdm_producto" value="{{ old('pdm_producto') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                @error('pdm_producto')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto y Final -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-bold mb-4">INFORMACIÓN DE CONTACTO</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">
                                    Correo Electrónico Institucional
                                </label>
                                <input type="email" name="correo_solicitante" value="{{ old('correo_solicitante') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="usuario@puertoboyaca-boyaca.gov.co" required>
                                @error('correo_solicitante')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">TIEMPO CONTRACTUAL</label>
                                <input type="text" name="tiempo_contractual" value="{{ old('tiempo_contractual') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Ejemplo: 6 meses" required>
                                @error('tiempo_contractual')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">CÓDIGO DE ACTA DE NECESIDAD</label>
                                <p class="text-xs text-gray-500 mb-2">EJEMPLO: No 0###</p>
                                <input type="text" name="codigo_acta_necesidad" value="{{ old('codigo_acta_necesidad') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                @error('codigo_acta_necesidad')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 campo-requerido">NOMBRE COMPLETO QUIEN ELABORA LA SOLICITUD</label>
                                <input type="text" name="nombre_elabora" value="{{ old('nombre_elabora') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                @error('nombre_elabora')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botón Enviar -->
                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <button type="reset" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Limpiar Formulario
                        </button>
                        <button type="submit" class="px-6 py-3 bg-blue-800 text-white rounded-lg hover:bg-blue-900">
                            Enviar Solicitud
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Datos de proyectos e items del catálogo
        const proyectosData = {
            @foreach($proyectos as $proyecto)
                {{ $proyecto->id }}: {
                    nombre: "{{ $proyecto->nombre_proyecto }}",
                    codigo_bpim: "{{ $proyecto->codigo_bpim }}",
                    codigo_bpin: "{{ $proyecto->codigo_bpin ?? '' }}",
                    items: [
                        @foreach($proyecto->itemsCatalogo as $item)
                            {
                                id: {{ $item->id }},
                                codigo: "{{ $item->codigo }}",
                                nombre: "{{ $item->nombre }}",
                                descripcion: "{{ addslashes($item->descripcion) }}",
                                valor_unitario: {{ $item->valor_unitario }},
                                unidad: "{{ $item->unidad }}",
                                cantidad: {{ $item->cantidad ?? 0 }}
                            }{{ !$loop->last ? ',' : '' }}
                        @endforeach
                    ]
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        };
    </script>

    <script>
        // Script para agregar/remover items de presupuesto y calcular valores automáticamente
        let itemCount = 1;

        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('presupuesto-container');
            const newItem = container.querySelector('.presupuesto-item').cloneNode(true);

            newItem.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace('[0]', `[${itemCount}]`));
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                }
                if (input.classList.contains('cantidad')) {
                    input.classList.remove('border-red-500', 'border-2');
                    input.classList.add('border-gray-300');
                }
            });

            const cantidadLimiteP = newItem.querySelector('.cantidad-limite');
            if (cantidadLimiteP) {
                cantidadLimiteP.textContent = '';
                cantidadLimiteP.classList.remove('text-red-600', 'font-semibold');
            }

            newItem.querySelector('.item-numero').textContent = itemCount + 1;
            newItem.querySelector('.remove-item').classList.remove('hidden');

            container.appendChild(newItem);
            itemCount++;

            actualizarSelectsItemsCatalogo();

            setupItemCalculation(newItem);
            setupItemCatalogoSelect(newItem);
        });

        document.getElementById('presupuesto-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                if (document.querySelectorAll('.presupuesto-item').length > 1) {
                    e.target.closest('.presupuesto-item').remove();
                    renumberItems();
                }
            }
        });

        function renumberItems() {
            document.querySelectorAll('.presupuesto-item').forEach((item, index) => {
                item.querySelector('.item-numero').textContent = index + 1;
            });
        }

        function setupItemCalculation(item) {
            const valorUnitario = item.querySelector('.valor-unitario');
            const cantidad = item.querySelector('.cantidad');
            const valorTotal = item.querySelector('.valor-total');

            function calcularTotal() {
                const vu = parseFloat(valorUnitario.value) || 0;
                const c = parseInt(cantidad.value) || 0;
                valorTotal.value = (vu * c).toFixed(2);
            }

            cantidad.addEventListener('input', calcularTotal);
        }

        document.querySelectorAll('.presupuesto-item').forEach(setupItemCalculation);

        // Autocompletado de Proyectos
        const proyectoSelect = document.getElementById('proyecto_id');
        let itemsCatalogoProyecto = [];

        proyectoSelect?.addEventListener('change', function() {
            const proyectoId = this.value;

            if (proyectoId && proyectosData[proyectoId]) {
                const proyecto = proyectosData[proyectoId];

                document.getElementById('nombre_proyecto').value = proyecto.nombre;
                document.getElementById('codigo_bpim').value = proyecto.codigo_bpim;
                document.getElementById('codigo_bpin').value = proyecto.codigo_bpin;

                itemsCatalogoProyecto = proyecto.items || [];

                actualizarSelectsItemsCatalogo();
            } else {
                itemsCatalogoProyecto = [];
                document.getElementById('nombre_proyecto').value = '';
                document.getElementById('codigo_bpim').value = '';
                document.getElementById('codigo_bpin').value = '';
                actualizarSelectsItemsCatalogo();
            }
        });

        function actualizarSelectsItemsCatalogo() {
            const selects = document.querySelectorAll('.item-catalogo-select');

            selects.forEach((select) => {
                const valorActual = select.value;
                select.innerHTML = '';

                if (itemsCatalogoProyecto.length === 0) {
                    select.disabled = true;
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = '-- Primero seleccione un proyecto --';
                    select.appendChild(option);
                } else {
                    select.disabled = false;

                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = '-- Seleccione un ítem del proyecto --';
                    select.appendChild(defaultOption);

                    itemsCatalogoProyecto.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.dataset.descripcion = item.descripcion || '';
                        option.dataset.valor = item.valor_unitario || 0;
                        option.dataset.unidad = item.unidad || '';
                        option.dataset.cantidad = item.cantidad || 0;
                        option.textContent = `${item.nombre} - ${item.codigo} | ${item.unidad} | Límite: ${item.cantidad} | $${parseFloat(item.valor_unitario || 0).toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        select.appendChild(option);
                    });

                    if (valorActual) {
                        select.value = valorActual;
                    }
                }
            });
        }

        function setupItemCatalogoSelect(item) {
            const select = item.querySelector('.item-catalogo-select');
            const descripcionTextarea = item.querySelector('.item-descripcion');
            const valorUnitarioInput = item.querySelector('.valor-unitario');
            const unidadInput = item.querySelector('.item-unidad');
            const cantidadInput = item.querySelector('.cantidad');
            const cantidadLimiteP = item.querySelector('.cantidad-limite');

            function validarCantidad() {
                const selectedOption = select.options[select.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    cantidadInput.classList.remove('border-red-500', 'border-2');
                    cantidadInput.classList.add('border-gray-300');
                    cantidadLimiteP.textContent = '';
                    cantidadLimiteP.classList.remove('text-red-600', 'font-semibold');
                    return true;
                }

                const cantidadLimite = parseInt(selectedOption.dataset.cantidad) || 0;
                const cantidadIngresada = parseInt(cantidadInput.value) || 0;

                if (cantidadIngresada > cantidadLimite) {
                    cantidadInput.classList.remove('border-gray-300');
                    cantidadInput.classList.add('border-red-500', 'border-2');
                    cantidadLimiteP.textContent = `⚠️ Límite máximo: ${cantidadLimite} unidades. No puede exceder este valor.`;
                    cantidadLimiteP.classList.add('text-red-600', 'font-semibold');
                    return false;
                } else if (cantidadIngresada > 0) {
                    cantidadInput.classList.remove('border-red-500', 'border-2');
                    cantidadInput.classList.add('border-gray-300');
                    cantidadLimiteP.textContent = `Límite disponible: ${cantidadLimite} unidades`;
                    cantidadLimiteP.classList.remove('text-red-600', 'font-semibold');
                    cantidadLimiteP.classList.add('text-gray-500');
                    return true;
                } else {
                    cantidadInput.classList.remove('border-red-500', 'border-2');
                    cantidadInput.classList.add('border-gray-300');
                    cantidadLimiteP.textContent = `Límite disponible: ${cantidadLimite} unidades`;
                    cantidadLimiteP.classList.remove('text-red-600', 'font-semibold');
                    cantidadLimiteP.classList.add('text-gray-500');
                    return true;
                }
            }

            select?.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    descripcionTextarea.value = selectedOption.dataset.descripcion;
                    valorUnitarioInput.value = selectedOption.dataset.valor;
                    unidadInput.value = selectedOption.dataset.unidad;

                    validarCantidad();

                    if (cantidadInput && cantidadInput.value) {
                        const total = parseFloat(valorUnitarioInput.value) * parseInt(cantidadInput.value);
                        item.querySelector('.valor-total').value = total.toFixed(2);
                    }
                } else {
                    descripcionTextarea.value = '';
                    valorUnitarioInput.value = '';
                    unidadInput.value = '';
                    cantidadLimiteP.textContent = '';
                    cantidadLimiteP.classList.remove('text-red-600', 'font-semibold');
                }
            });

            cantidadInput?.addEventListener('input', function() {
                validarCantidad();
            });

            cantidadInput?.addEventListener('blur', function() {
                validarCantidad();
            });
        }

        document.querySelectorAll('.presupuesto-item').forEach(item => {
            setupItemCalculation(item);
            setupItemCatalogoSelect(item);
        });

        // Validación al enviar el formulario
        const formulario = document.getElementById('formulario-bpim');
        formulario?.addEventListener('submit', function(e) {
            let hayErrores = false;
            const items = document.querySelectorAll('.presupuesto-item');

            items.forEach((item, index) => {
                const select = item.querySelector('.item-catalogo-select');
                const cantidadInput = item.querySelector('.cantidad');
                const cantidadLimiteP = item.querySelector('.cantidad-limite');

                const selectedOption = select?.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const cantidadLimite = parseInt(selectedOption.dataset.cantidad) || 0;
                    const cantidadIngresada = parseInt(cantidadInput?.value) || 0;

                    if (cantidadIngresada > cantidadLimite) {
                        hayErrores = true;
                        cantidadInput.classList.add('border-red-500', 'border-2');
                        cantidadLimiteP.textContent = `⚠️ Límite máximo: ${cantidadLimite} unidades. No puede exceder este valor.`;
                        cantidadLimiteP.classList.add('text-red-600', 'font-semibold');

                        if (hayErrores && index === 0) {
                            cantidadInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                }
            });

            if (hayErrores) {
                e.preventDefault();
                alert('❌ No puede enviar el formulario. Algunos ítems exceden la cantidad límite disponible. Por favor, corrija los valores marcados en rojo.');
                return false;
            }

            return true;
        });
    </script>
</body>
</html>
