<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud BPIM Rechazada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .motivo { background-color: #fff1f2; padding: 15px; margin: 15px 0; border-left: 4px solid #dc2626; border-radius: 4px; color: #7f1d1d; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Solicitud BPIM Rechazada</h1>
    </div>
    <div class="content">
        <p>Estimado(a) {{ $solicitud->nombre_solicitante }},</p>
        <p>Lamentamos informarle que la Solicitud BPIM <strong>{{ $solicitud->codigo }}</strong>
           del proyecto <strong>{{ $solicitud->nombre_proyecto }}</strong> fue <strong>rechazada</strong>.</p>

        <div class="motivo">
            <strong>Motivo del rechazo:</strong><br>
            {{ $solicitud->motivo_rechazo }}
        </div>

        <p>Agradecemos su comprensión y lo invitamos a corregir la información y volver a registrar la solicitud.</p>
        <p style="margin-top: 24px;">Atentamente,<br>Alcaldía de Puerto Boyacá, Boyacá</p>
    </div>
    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá</p>
    </div>
</body>
</html>
