<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Afiliación Pendiente</title>
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
            background-color: #f59e0b;
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
            border-left: 4px solid #f59e0b;
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
            background-color: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #d97706;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nueva Afiliación Pendiente de Revisión</h1>
    </div>

    <div class="content">
        <p>Estimado usuario SSST,</p>

        <p>Se ha registrado una nueva afiliación que requiere su revisión y aprobación.</p>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #f59e0b;">Información del Contratista</h3>
            <div class="info-row">
                <span class="label">Nombre:</span>
                <span class="value">{{ $afiliacion->nombre_contratista }}</span>
            </div>
            <div class="info-row">
                <span class="label">Documento:</span>
                <span class="value">{{ $afiliacion->tipo_documento }} {{ $afiliacion->numero_documento }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $afiliacion->email_contratista }}</span>
            </div>
            <div class="info-row">
                <span class="label">Teléfono:</span>
                <span class="value">{{ $afiliacion->telefono_contratista }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #f59e0b;">Información del Contrato</h3>
            <div class="info-row">
                <span class="label">No. Contrato:</span>
                <span class="value">{{ $afiliacion->numero_contrato }}</span>
            </div>
            <div class="info-row">
                <span class="label">Objeto:</span>
                <span class="value">{{ $afiliacion->objeto_contractual }}</span>
            </div>
            <div class="info-row">
                <span class="label">Valor:</span>
                <span class="value">${{ number_format($afiliacion->valor_contrato, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Duración:</span>
                <span class="value">{{ $afiliacion->meses_contrato }} meses ({{ $afiliacion->dias_contrato }} días)</span>
            </div>
            <div class="info-row">
                <span class="label">Vigencia:</span>
                <span class="value">{{ $afiliacion->fecha_inicio?->format('d/m/Y') }} - {{ $afiliacion->fecha_fin?->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #f59e0b;">Información ARL</h3>
            <div class="info-row">
                <span class="label">ARL:</span>
                <span class="value">{{ $afiliacion->nombre_arl }}</span>
            </div>
            <div class="info-row">
                <span class="label">Nivel de Riesgo:</span>
                <span class="value">{{ $afiliacion->tipo_riesgo }}</span>
            </div>
            <div class="info-row">
                <span class="label">IBC:</span>
                <span class="value">${{ number_format($afiliacion->ibc, 2) }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3 style="margin-top: 0; color: #f59e0b;">Dependencia</h3>
            <div class="info-row">
                <span class="label">Dependencia:</span>
                <span class="value">{{ $afiliacion->dependencia?->nombre }}</span>
            </div>
            <div class="info-row">
                <span class="label">Creado por:</span>
                <span class="value">{{ $afiliacion->creator?->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Fecha de creación:</span>
                <span class="value">{{ $afiliacion->created_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/admin/afiliacions/{{ $afiliacion->id }}" class="button">
                Revisar Afiliación
            </a>
        </div>

        <p style="margin-top: 30px; font-style: italic; color: #6b7280;">
            Por favor, ingrese al sistema para revisar la información completa y proceder con la validación o rechazo de esta afiliación.
        </p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Sistema de Gestión de Afiliaciones ARL</p>
    </div>
</body>
</html>
