<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afiliaciones Pendientes de Validación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f3f4f6;
        }
        .wrapper {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1d4ed8;
            color: white;
            padding: 28px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 6px;
            font-size: 22px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.88;
        }
        .summary-box {
            background-color: #eff6ff;
            border-left: 5px solid #1d4ed8;
            margin: 24px 30px 0;
            padding: 16px 20px;
            border-radius: 4px;
        }
        .summary-box .count {
            font-size: 32px;
            font-weight: bold;
            color: #1d4ed8;
            line-height: 1;
        }
        .summary-box .label {
            font-size: 14px;
            color: #374151;
            margin-top: 4px;
        }
        .content {
            padding: 20px 30px 30px;
        }
        .intro {
            color: #374151;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        thead tr {
            background-color: #1d4ed8;
            color: white;
        }
        thead th {
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
        }
        tbody tr:nth-child(even) {
            background-color: #f8faff;
        }
        tbody tr:hover {
            background-color: #eff6ff;
        }
        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }
        .badge-pendiente {
            display: inline-block;
            padding: 2px 10px;
            background-color: #fef3c7;
            color: #92400e;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0 10px;
        }
        .button {
            display: inline-block;
            padding: 13px 36px;
            background-color: #1d4ed8;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
        }
        .footer {
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>⏳ Afiliaciones Pendientes de Validación</h1>
            <p>Sistema de Gestión ARL — Alcaldía Municipal de Puerto Boyacá</p>
        </div>

        <div class="summary-box">
            <div class="count">{{ $afiliaciones->count() }}</div>
            <div class="label">afiliación(es) esperando aprobación o rechazo</div>
        </div>

        <div class="content">
            <p class="intro">
                Estimado usuario SSST,<br><br>
                Le informamos que las siguientes afiliaciones se encuentran en estado <strong>Pendiente de Validación</strong>
                y requieren su revisión. Este recordatorio se enviará diariamente hasta que todas sean procesadas.
            </p>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Contratista</th>
                        <th>No. Contrato</th>
                        <th>Dependencia</th>
                        <th>Fecha Registro</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($afiliaciones as $i => $afiliacion)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $afiliacion->nombre_contratista }}</strong></td>
                        <td>{{ $afiliacion->numero_contrato }}</td>
                        <td>{{ $afiliacion->dependencia?->nombre ?? '—' }}</td>
                        <td>{{ $afiliacion->created_at?->format('d/m/Y') }}</td>
                        <td><span class="badge-pendiente">Pendiente</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="btn-wrapper">
                <a href="{{ config('app.url') }}/admin/afiliacions?tableFilters[estado][value]=pendiente" class="button">
                    Revisar Afiliaciones Pendientes
                </a>
            </div>

            <p style="font-size: 13px; color: #6b7280; text-align: center;">
                Haga clic en el botón para ir directamente al listado filtrado por estado Pendiente.
            </p>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no responder.</p>
            <p>&copy; {{ date('Y') }} Sistema de Gestión de Afiliaciones ARL</p>
        </div>
    </div>
</body>
</html>
