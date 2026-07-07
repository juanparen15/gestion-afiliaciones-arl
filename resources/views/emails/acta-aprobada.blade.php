<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Necesidad Aprobada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #16a34a; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .info-section { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #16a34a; border-radius: 4px; }
        .info-row { margin: 8px 0; }
        .label { font-weight: bold; color: #6b7280; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Acta de Necesidad Aprobada</h1>
    </div>
    <div class="content">
        <p>Estimado usuario,</p>
        <p>A continuación adjuntamos el <strong>Acta de Necesidad No 0{{ $acta->consecutivo }}</strong> aprobada.</p>

        <div class="info-section">
            <div class="info-row"><span class="label">No. Acta:</span> 0{{ $acta->consecutivo }}</div>
            <div class="info-row"><span class="label">Dependencia:</span> {{ $acta->dependencia_texto }} @if($acta->area_texto) - {{ $acta->area_texto }} @endif</div>
            <div class="info-row"><span class="label">Solicitante:</span> {{ $acta->nombre_solicitante }}</div>
            <div class="info-row"><span class="label">Objeto:</span> {{ $acta->objeto_contrato }}</div>
        </div>

        <p>El acta se encuentra adjunta a este correo en formato PDF.</p>
        <p style="margin-top: 24px;">Atentamente,<br>Alcaldía de Puerto Boyacá, Boyacá</p>
    </div>
    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá</p>
    </div>
</body>
</html>
