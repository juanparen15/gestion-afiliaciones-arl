<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Enviada · BPIM - Alcaldía de Puerto Boyacá</title>
    <link rel="icon" href="{{ asset('images/actas/logo-alcaldia.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl w-full space-y-8">
            <!-- Mensaje de éxito -->
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="bg-green-600 p-6">
                    <div class="flex items-center justify-center">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-center text-3xl font-bold text-white">
                        ¡Solicitud Enviada Exitosamente!
                    </h1>
                </div>

                <div class="p-8">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Su solicitud ha sido recibida y está pendiente de revisión por parte de la Secretaría de Planeación.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-b border-gray-200 py-4 my-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles de la Solicitud:</h2>

                        <dl class="grid grid-cols-1 gap-4">
                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Consecutivo</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $solicitud->consecutivo }}</dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Dependencia</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $solicitud->dependencia }}</dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Solicitante</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $solicitud->nombre_solicitante }}</dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Proyecto</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $solicitud->nombre_proyecto }}</dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Código BPIM</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $solicitud->codigo_bpim }}</dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Valor Total CDP</dt>
                                <dd class="mt-1 text-lg font-semibold text-green-600">
                                    ${{ number_format($solicitud->valor_cdp, 0, ',', '.') }}
                                </dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Correo de Notificación</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $solicitud->correo_solicitante }}</dd>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 rounded">
                                <dt class="text-sm font-medium text-gray-500">Fecha de Envío</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $solicitud->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($solicitud->presupuestoItems->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Items de Presupuesto:</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Unit.</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($solicitud->presupuestoItems as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->descripcion }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            ${{ number_format($item->valor_unitario, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $item->cantidad }} {{ $item->unidad }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                                            ${{ number_format($item->valor_total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Próximos Pasos:</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Su solicitud será revisada por la Secretaría de Planeación Municipal</li>
                                        <li>Recibirá una notificación por correo electrónico con el resultado</li>
                                        <li>En caso de aprobación, se generará automáticamente el documento BPIM</li>
                                        <li>El proceso puede tomar entre 2 a 5 días hábiles</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <a href="{{ route('bpim.formulario.index') }}"
                           class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nueva Solicitud
                        </a>
                        <button onclick="window.print()"
                                class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimir Comprobante
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>Alcaldía Municipal de Puerto Boyacá, Boyacá</p>
                <p>Oficina de Sistemas - {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
