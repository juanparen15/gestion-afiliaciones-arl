<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de actas pendientes</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 640px; margin: 0 auto; padding: 20px; }
        .header { background-color: #d97706; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        th { background: #fff7ed; color: #9a3412; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header"><h1>Actas de necesidad pendientes</h1></div>
    <div class="content">
        <p>Estimado usuario,</p>
        <p>Existen <strong>{{ $actas->count() }}</strong> acta(s) de necesidad pendiente(s) de revisión:</p>
        <table>
            <thead><tr><th>Solicitante</th><th>Dependencia</th><th>Fecha</th></tr></thead>
            <tbody>
                @foreach($actas as $a)
                    <tr>
                        <td>{{ $a->nombre_solicitante }}</td>
                        <td>{{ $a->dependencia_texto }}</td>
                        <td>{{ optional($a->fecha_solicitud)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="margin-top:16px;">Por favor, ingrese al sistema para gestionarlas.</p>
    </div>
    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá</p>
    </div>
</body>
</html>
