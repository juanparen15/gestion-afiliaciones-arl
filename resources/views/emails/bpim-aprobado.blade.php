<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud BPIM Aprobada</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f6; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f6; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,.08);">

                    <tr>
                        <td style="background-color:#0f2f5f; padding:22px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="56" valign="middle">
                                        <img src="{{ $message->embed(public_path('images/actas/logo-alcaldia.png')) }}" alt="Alcaldía de Puerto Boyacá" width="48" style="display:block; width:48px; height:auto;">
                                    </td>
                                    <td valign="middle" style="padding-left:12px;">
                                        <div style="color:#ffffff; font-size:15px; font-weight:bold; line-height:1.3;">Alcaldía Municipal de Puerto Boyacá</div>
                                        <div style="color:#9fb6d6; font-size:12px; letter-spacing:.4px;">Boyacá - Colombia</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#16a34a; padding:14px 28px; text-align:center;">
                            <span style="color:#ffffff; font-size:15px; font-weight:bold; letter-spacing:.3px;">SOLICITUD BPIM APROBADA</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px; font-size:14px;">Estimado(a) {{ $solicitud->nombre_solicitante }},</p>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.6;">
                                Le informamos que la <strong>Solicitud BPIM {{ $solicitud->codigo }}</strong>
                                ha sido aprobada. Encontrará el documento oficial adjunto a este correo en formato PDF.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                                <tr><td style="background-color:#f8fafc; padding:10px 16px; font-size:11px; font-weight:bold; color:#64748b; letter-spacing:.5px;">DETALLE DE LA SOLICITUD</td></tr>
                                <tr><td style="padding:6px 16px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; width:38%;">Código</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right;">{{ $solicitud->codigo }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; border-top:1px solid #f1f5f9;">Dependencia</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right; border-top:1px solid #f1f5f9;">{{ $solicitud->dependencia }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; border-top:1px solid #f1f5f9; vertical-align:top;">Proyecto</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right; border-top:1px solid #f1f5f9;">{{ $solicitud->nombre_proyecto }}</td>
                                        </tr>
                                    </table>
                                </td></tr>
                            </table>

                            @if($solicitud->verificacion)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $solicitud->verificacion->getUrlVerificacion() }}" target="_blank"
                                           style="display:inline-block; background-color:#16a34a; color:#ffffff; font-size:15px; font-weight:bold; text-decoration:none; padding:14px 34px; border-radius:10px; box-shadow:0 4px 12px rgba(22,163,74,.30); font-family:Arial,sans-serif;">
                                            Validar autenticidad del documento
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top:8px;">
                                        <span style="font-size:11px; color:#94a3b8;">O escanee el código QR impreso en el documento</span>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="margin:18px 0 0; font-size:14px;">Atentamente,<br><strong>Alcaldía de Puerto Boyacá, Boyacá</strong></p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f8fafc; padding:18px 28px; text-align:center; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.6;">
                                Este es un correo automático, por favor no responder.<br>
                                &copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá - Sistema de Gestión.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
