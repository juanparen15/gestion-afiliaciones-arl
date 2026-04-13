<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos SECOP Próximos a Vencer</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1d4ed8; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .header.urgente { background-color: #dc2626; }
        .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .summary-box { background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .summary-box.urgente { background-color: #fef2f2; border-color: #fecaca; }
        .summary-number { font-size: 36px; font-weight: bold; color: #1d4ed8; }
        .summary-number.urgente { color: #dc2626; }
        .contract-card { background-color: white; padding: 15px; margin: 15px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #1d4ed8; }
        .contract-card.urgente { border-left-color: #dc2626; }
        .contract-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 10px; }
        .contract-title { font-weight: bold; color: #111827; font-size: 15px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .badge-danger  { background-color: #fee2e2; color: #dc2626; }
        .badge-warning { background-color: #fef3c7; color: #d97706; }
        .badge-info    { background-color: #dbeafe; color: #1d4ed8; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .info-item { padding: 5px 0; }
        .label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { color: #111827; font-weight: 500; font-size: 14px; }
        .objeto-box { margin-top: 10px; padding: 8px 10px; background: #f3f4f6; border-radius: 4px; font-size: 13px; color: #374151; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
        .divider { border-top: 1px solid #e5e7eb; margin: 20px 0; }
        .tag-adicion { display: inline-block; padding: 2px 8px; background: #fef3c7; color: #92400e; border-radius: 4px; font-size: 11px; font-weight: bold; margin-left: 6px; }
        @media (max-width: 600px) { .info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
@php
    $total    = $contratos->count();
    $urgentes = $contratos->filter(fn($c) => $c->fechaEfectivaCierre() && now()->diffInDays($c->fechaEfectivaCierre(), false) <= 7)->count();
@endphp

<div class="header {{ $urgentes > 0 ? 'urgente' : '' }}">
    <h1 style="margin:0;">⚠️ Alerta: Contratos SECOP Próximos a Vencer</h1>
    <p style="margin:8px 0 0; opacity:.9;">Alcaldía Municipal de Puerto Boyacá</p>
</div>

<div class="content">
    <div class="summary-box {{ $urgentes > 0 ? 'urgente' : '' }}">
        <div class="summary-number {{ $urgentes > 0 ? 'urgente' : '' }}">{{ $total }}</div>
        <div>contrato(s) de <strong>{{ $dependencia?->nombre ?? 'Sin dependencia' }}</strong><br>vencen en los próximos <strong>{{ $diasAlerta }} días</strong></div>
        @if($urgentes > 0)
            <div style="margin-top:10px; color:#dc2626; font-weight:bold;">{{ $urgentes }} contrato(s) vencen en menos de 7 días</div>
        @endif
    </div>

    <p>Estimado funcionario,</p>
    <p>Los siguientes contratos SECOP de la dependencia <strong>{{ $dependencia?->nombre ?? 'Sin dependencia' }}</strong> requieren atención inmediata:</p>

    @foreach($contratos as $contrato)
        @php
            $cierre        = $contrato->fechaEfectivaCierre();
            $diasRestantes = $cierre ? (int) now()->diffInDays($cierre, false) : 0;
            $esUrgente     = $diasRestantes <= 7;
            $badgeClass    = $diasRestantes <= 7 ? 'badge-danger' : ($diasRestantes <= 15 ? 'badge-warning' : 'badge-info');
            $urgencia      = $diasRestantes <= 7 ? 'Urgente' : ($diasRestantes <= 15 ? 'Próximo' : 'Normal');
            $contratista   = $contrato->getNombreContratista() ?? 'Sin nombre';
        @endphp

        <div class="contract-card {{ $esUrgente ? 'urgente' : '' }}">
            <div class="contract-header">
                <div>
                    <span class="contract-title">{{ $contratista }}</span>
                    @if($contrato->tieneAdiciones())
                        <span class="tag-adicion">Con adición</span>
                    @endif
                </div>
                <span class="badge {{ $badgeClass }}">{{ $diasRestantes }} días – {{ $urgencia }}</span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="label">No. Contrato</div>
                    <div class="value">{{ $contrato->numero_contrato }}</div>
                </div>
                <div class="info-item">
                    <div class="label">ID SECOP</div>
                    <div class="value">{{ $contrato->id_contrato_secop ?? '–' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Fecha Inicio</div>
                    <div class="value">{{ $contrato->fecha_inicio?->format('d/m/Y') ?? '–' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Fecha Vencimiento Efectiva</div>
                    <div class="value" style="color:{{ $esUrgente ? '#dc2626' : '#111827' }}; font-weight:bold;">
                        {{ $cierre?->format('d/m/Y') ?? '–' }}
                        @if($contrato->fecha_terminacion && $cierre && $cierre->ne($contrato->fecha_terminacion))
                            <span style="font-size:11px; color:#6b7280;">(base: {{ $contrato->fecha_terminacion->format('d/m/Y') }})</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Valor Contrato</div>
                    <div class="value">$ {{ number_format($contrato->valor_contrato ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Supervisor</div>
                    <div class="value">{{ $contrato->nombre_supervisor ?? 'No asignado' }}</div>
                </div>
            </div>

            @if($contrato->objeto)
                <div class="objeto-box">
                    <span style="font-size:11px; color:#6b7280; text-transform:uppercase;">Objeto: </span>
                    {{ Str::limit($contrato->objeto, 160) }}
                </div>
            @endif
        </div>
    @endforeach

    <div class="divider"></div>

    <div style="text-align:center;">
        <p style="color:#6b7280;">Ingrese al sistema para gestionar estos contratos (prórroga, adición o liquidación).</p>
        <a href="{{ config('app.url') }}/admin/contratos"
           style="display:inline-block; padding:12px 30px; background-color:#1d4ed8; color:#ffffff !important; text-decoration:none; border-radius:5px; margin:20px 0; font-weight:bold; font-size:15px;">
            Ver Contratos SECOP en el Sistema
        </a>
    </div>

    <div style="background-color:#eff6ff; border:1px solid #bfdbfe; padding:15px; border-radius:8px; margin-top:20px;">
        <strong style="color:#1e40af;">Recordatorio:</strong>
        <p style="margin:10px 0 0; color:#1e40af; font-size:14px;">
            Los contratos que no sean prorrogados, adicionados o liquidados antes de su fecha de vencimiento
            pueden generar incumplimientos normativos y afectar la continuidad de la prestación del servicio.
        </p>
    </div>
</div>

<div class="footer">
    <p>Correo automático generado por el Sistema de Gestión ARL – Alcaldía de Puerto Boyacá.</p>
    <p>Por favor no responda a este correo.</p>
    <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
</div>
</body>
</html>
