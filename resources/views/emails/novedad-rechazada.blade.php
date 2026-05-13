<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adición/Prórroga Rechazada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
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
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .info-section {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #dc2626;
            border-radius: 4px;
        }
        .motivo-section {
            background-color: #fff1f2;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #dc2626;
            border-radius: 4px;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
        }
        .value {
            color: #111827;
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
            background-color: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
            margin: 2px 3px;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Adición/Prórroga Rechazada</h1>
    </div>

    <div class="content">
        <p>Estimado(a) usuario(a),</p>

        <p>Le informamos que la novedad registrada (adición/prórroga) para el siguiente contrato ha sido <strong>rechazada</strong> por el equipo de SSST.</p>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #dc2626;">Información del Contrato</h3>
            <div class="info-row">
                <span class="label">No. Contrato:</span>
                <span class="value">{{ $afiliacion->numero_contrato }}</span>
            </div>
            <div class="info-row">
                <span class="label">Contratista:</span>
                <span class="value">{{ $afiliacion->nombre_contratista }}</span>
            </div>
            <div class="info-row">
                <span class="label">Objeto:</span>
                <span class="value">{{ $afiliacion->objeto_contractual }}</span>
            </div>
            <div class="info-row">
                <span class="label">Vigencia:</span>
                <span class="value">{{ $afiliacion->fecha_inicio?->format('d/m/Y') }} - {{ $afiliacion->fecha_fin?->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #dc2626;">Novedad Registrada</h3>
            <div class="info-row">
                <span class="label">Tipo(s) de novedad:</span>
                <span class="value">
                    @if($afiliacion->tiene_adicion)
                        <span class="badge badge-red">Adición</span>
                    @endif
                    @if($afiliacion->tiene_prorroga)
                        <span class="badge badge-red">Prórroga</span>
                    @endif
                </span>
            </div>
            @if($afiliacion->tiene_adicion && $afiliacion->valor_adicion)
            <div class="info-row">
                <span class="label">Valor adición:</span>
                <span class="value">${{ number_format($afiliacion->valor_adicion, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($afiliacion->tiene_prorroga && ($afiliacion->meses_prorroga || $afiliacion->dias_prorroga))
            <div class="info-row">
                <span class="label">Plazo prórroga:</span>
                <span class="value">
                    {{ $afiliacion->meses_prorroga ? $afiliacion->meses_prorroga . ' mes(es)' : '' }}
                    {{ $afiliacion->dias_prorroga ? $afiliacion->dias_prorroga . ' día(s)' : '' }}
                </span>
            </div>
            @endif
            <div class="info-row">
                <span class="label">Fecha de rechazo:</span>
                <span class="value">{{ $afiliacion->fecha_validacion?->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        @if($afiliacion->motivo_rechazo)
        <div class="motivo-section">
            <h3 style="margin-top: 0; color: #dc2626;">Motivo del Rechazo</h3>
            <p style="margin: 0; color: #7f1d1d;">{{ $afiliacion->motivo_rechazo }}</p>
        </div>
        @endif

        <p>Si considera que el rechazo no es correcto o desea corregir la información, por favor ingrese al sistema y registre nuevamente la novedad con las correcciones pertinentes.</p>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/admin/afiliacions" class="button">
                Ir al Sistema
            </a>
        </div>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Sistema de Gestión de Afiliaciones ARL</p>
    </div>
</body>
</html>
