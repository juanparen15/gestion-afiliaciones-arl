<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos Próximos a Vencer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header-warning {
            background-color: #f59e0b;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .summary-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .summary-box.warning {
            background-color: #fffbeb;
            border-color: #fde68a;
        }
        .summary-number {
            font-size: 36px;
            font-weight: bold;
            color: #dc2626;
        }
        .summary-number.warning {
            color: #f59e0b;
        }
        .contract-card {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .contract-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .contract-title {
            font-weight: bold;
            color: #111827;
            font-size: 16px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #d97706;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #059669;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            padding: 5px 0;
        }
        .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .value {
            color: #111827;
            font-weight: 500;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php
        $totalContratos = $afiliaciones->count();
        $contratosUrgentes = $afiliaciones->filter(function($a) {
            $fechaVencimiento = $a->tiene_prorroga ? $a->nueva_fecha_fin_prorroga : $a->fecha_fin;
            return $fechaVencimiento && now()->diffInDays($fechaVencimiento, false) <= 7;
        })->count();
    @endphp

    <div class="header {{ $contratosUrgentes > 0 ? '' : 'header-warning' }}">
        <h1>Alerta de Contratos Próximos a Vencer</h1>
    </div>

    <div class="content">
        <div class="summary-box {{ $contratosUrgentes > 0 ? '' : 'warning' }}">
            <div class="summary-number {{ $contratosUrgentes > 0 ? '' : 'warning' }}">{{ $totalContratos }}</div>
            <div>contrato(s) próximo(s) a vencer en los próximos <strong>{{ $diasAlerta }} días</strong></div>
            @if($contratosUrgentes > 0)
                <div style="margin-top: 10px; color: #dc2626; font-weight: bold;">
                    {{ $contratosUrgentes }} contrato(s) vencen en menos de 7 días
                </div>
            @endif
        </div>

        <p>Estimado funcionario,</p>

        <p>
            Le informamos que los siguientes contratos de la dependencia
            <strong>{{ $dependencia?->nombre ?? 'Sin dependencia' }}</strong>
            están próximos a vencer y requieren su atención:
        </p>

        @foreach($afiliaciones as $afiliacion)
            @php
                $fechaVencimiento = $afiliacion->tiene_prorroga ? $afiliacion->nueva_fecha_fin_prorroga : $afiliacion->fecha_fin;
                $diasRestantes = $fechaVencimiento ? now()->diffInDays($fechaVencimiento, false) : 0;

                if ($diasRestantes <= 7) {
                    $badgeClass = 'badge-danger';
                    $urgencia = 'Urgente';
                } elseif ($diasRestantes <= 15) {
                    $badgeClass = 'badge-warning';
                    $urgencia = 'Próximo';
                } else {
                    $badgeClass = 'badge-success';
                    $urgencia = 'Normal';
                }
            @endphp

            <div class="contract-card">
                <div class="contract-header">
                    <span class="contract-title">{{ $afiliacion->nombre_contratista }}</span>
                    <span class="badge {{ $badgeClass }}">
                        {{ $diasRestantes }} días - {{ $urgencia }}
                    </span>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Documento</div>
                        <div class="value">{{ $afiliacion->tipo_documento }} {{ $afiliacion->numero_documento }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">No. Contrato</div>
                        <div class="value">{{ $afiliacion->numero_contrato }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Fecha Inicio</div>
                        <div class="value">{{ $afiliacion->fecha_inicio?->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Fecha Vencimiento</div>
                        <div class="value" style="color: {{ $diasRestantes <= 7 ? '#dc2626' : '#111827' }}; font-weight: bold;">
                            {{ $fechaVencimiento?->format('d/m/Y') }}
                            @if($afiliacion->tiene_prorroga)
                                <span style="font-size: 11px; color: #6b7280;">(Prórroga)</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="label">ARL</div>
                        <div class="value">{{ $afiliacion->nombre_arl }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Supervisor</div>
                        <div class="value">{{ $afiliacion->nombre_supervisor ?? 'No asignado' }}</div>
                    </div>
                </div>

                @if($afiliacion->objeto_contractual)
                    <div style="margin-top: 10px;">
                        <div class="label">Objeto Contractual</div>
                        <div class="value" style="font-size: 13px;">{{ Str::limit($afiliacion->objeto_contractual, 150) }}</div>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="divider"></div>

        <div style="text-align: center;">
            <p style="color: #6b7280;">
                Ingrese al sistema para gestionar estos contratos y realizar las acciones necesarias
                (renovación, prórroga o cierre).
            </p>
            <a href="{{ config('app.url') }}/admin/afiliacions" class="button">
                Ver Contratos en el Sistema
            </a>
        </div>

        <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <strong style="color: #1e40af;">Recordatorio importante:</strong>
            <p style="margin: 10px 0 0; color: #1e40af; font-size: 14px;">
                Los contratos que no sean renovados o prorrogados antes de su fecha de vencimiento
                podrían generar interrupciones en la cobertura ARL del contratista.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Este es un correo automático generado por el Sistema de Gestión de Afiliaciones ARL.</p>
        <p>Por favor no responda a este correo.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</body>
</html>
