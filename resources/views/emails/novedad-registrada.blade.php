<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novedad registrada - Afiliación ARL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f59e0b; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .info-section { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #f59e0b; border-radius: 4px; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #6b7280; }
        .value { color: #111827; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
        .button { display: inline-block; padding: 12px 30px; background-color: #f59e0b; color: #ffffff !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $registradoPor = $afiliacion->novedad_registrada_por
            ? optional(\App\Models\User::find($afiliacion->novedad_registrada_por))->name
            : null;
    @endphp
    <div class="header">
        <h1>Novedad registrada - Afiliación ARL</h1>
    </div>

    <div class="content">
        <p>Estimado usuario SSST,</p>

        <p>Se registró una <strong>{{ $tipoNovedad }}</strong> en una afiliación ARL. Requiere la carga del PDF de novedad ARL y su aprobación.</p>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #f59e0b;">Contratista y contrato</h3>
            <div class="info-row"><span class="label">Contratista:</span> <span class="value">{{ $afiliacion->nombre_contratista }}</span></div>
            <div class="info-row"><span class="label">Documento:</span> <span class="value">{{ $afiliacion->tipo_documento }} {{ $afiliacion->numero_documento }}</span></div>
            <div class="info-row"><span class="label">No. Contrato:</span> <span class="value">{{ $afiliacion->numero_contrato }}</span></div>
            <div class="info-row"><span class="label">Dependencia:</span> <span class="value">{{ $afiliacion->dependencia?->nombre ?? $afiliacion->dependencia_nombre }}</span></div>
        </div>

        @if ($afiliacion->tiene_adicion)
            <div class="info-section">
                <h3 style="margin-top: 0; color: #f59e0b;">Adición</h3>
                <div class="info-row"><span class="label">Descripción:</span> <span class="value">{{ $afiliacion->descripcion_adicion ?: '-' }}</span></div>
                <div class="info-row"><span class="label">Valor:</span> <span class="value">{{ $afiliacion->valor_adicion !== null ? '$' . number_format((float) $afiliacion->valor_adicion, 0, ',', '.') : '-' }}</span></div>
                <div class="info-row"><span class="label">Fecha:</span> <span class="value">{{ optional($afiliacion->fecha_adicion)->format('d/m/Y') ?? '-' }}</span></div>
            </div>
        @endif

        @if ($afiliacion->tiene_prorroga)
            <div class="info-section">
                <h3 style="margin-top: 0; color: #f59e0b;">Prórroga</h3>
                <div class="info-row"><span class="label">Descripción:</span> <span class="value">{{ $afiliacion->descripcion_prorroga ?: '-' }}</span></div>
                <div class="info-row"><span class="label">Ampliación:</span> <span class="value">{{ (int) $afiliacion->meses_prorroga }} meses y {{ (int) $afiliacion->dias_prorroga }} días</span></div>
                <div class="info-row"><span class="label">Nueva fecha fin:</span> <span class="value">{{ optional($afiliacion->nueva_fecha_fin_prorroga)->format('d/m/Y') ?? '-' }}</span></div>
            </div>
        @endif

        <div class="info-section">
            <div class="info-row"><span class="label">Registrada por:</span> <span class="value">{{ $registradoPor ?? '-' }}</span></div>
            <div class="info-row"><span class="label">Fecha de registro:</span> <span class="value">{{ optional($afiliacion->novedad_registrada_at)->format('d/m/Y H:i') ?? '-' }}</span></div>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/admin/afiliacions/{{ $afiliacion->id }}" class="button">Revisar afiliación</a>
        </div>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Sistema de Gestión de Afiliaciones ARL</p>
    </div>
</body>
</html>
