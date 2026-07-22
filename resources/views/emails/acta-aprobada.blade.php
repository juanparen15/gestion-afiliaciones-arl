<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Necesidad Aprobada</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f6; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f6; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,.08);">

                    <!-- Encabezado institucional -->
                    <tr>
                        <td style="background-color:#0f2f5f; padding:22px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="56" valign="middle">
                                        <img src="{{ asset('images/actas/logo-alcaldia.png') }}" alt="Alcaldía de Puerto Boyacá" width="48" style="display:block; width:48px; height:auto;">
                                    </td>
                                    <td valign="middle" style="padding-left:12px;">
                                        <div style="color:#ffffff; font-size:15px; font-weight:bold; line-height:1.3;">Alcaldía Municipal de Puerto Boyacá</div>
                                        <div style="color:#9fb6d6; font-size:12px; letter-spacing:.4px;">Boyacá — Colombia</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Barra de estado -->
                    <tr>
                        <td style="background-color:#16a34a; padding:14px 28px; text-align:center;">
                            <span style="color:#ffffff; font-size:15px; font-weight:bold; letter-spacing:.3px;">ACTA DE NECESIDAD APROBADA</span>
                        </td>
                    </tr>

                    <!-- Cuerpo -->
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px; font-size:14px;">Estimado(a) usuario(a),</p>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.6;">
                                Le informamos que el <strong>Acta de Necesidad No. 0{{ $acta->consecutivo }}</strong>
                                ha sido aprobada. Encontrará el documento oficial adjunto a este correo en formato PDF.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                                <tr><td style="background-color:#f8fafc; padding:10px 16px; font-size:11px; font-weight:bold; color:#64748b; letter-spacing:.5px;">DETALLE DEL ACTA</td></tr>
                                <tr><td style="padding:6px 16px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; width:38%;">No. Acta</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right;">0{{ $acta->consecutivo }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; border-top:1px solid #f1f5f9;">Solicitante</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right; border-top:1px solid #f1f5f9;">{{ $acta->nombre_solicitante }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; border-top:1px solid #f1f5f9;">Dependencia</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right; border-top:1px solid #f1f5f9;">{{ $acta->dependencia_texto }}{{ $acta->area_texto ? ' - '.$acta->area_texto : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; border-top:1px solid #f1f5f9; vertical-align:top;">Objeto</td>
                                            <td style="padding:8px 0; font-weight:bold; text-align:right; border-top:1px solid #f1f5f9;">{{ $acta->objeto_contrato }}</td>
                                        </tr>
                                        @if($acta->codigo_verificacion)
                                        <tr>
                                            <td style="padding:8px 0; color:#6b7280; border-top:1px solid #f1f5f9;">Verificación</td>
                                            <td style="padding:8px 0; text-align:right; border-top:1px solid #f1f5f9;">
                                                <a href="{{ $acta->urlVerificacion() }}" style="color:#0f2f5f; font-weight:bold; text-decoration:none;">Validar autenticidad</a>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </td></tr>
                            </table>

                            <p style="margin:22px 0 0; font-size:13px; color:#6b7280; line-height:1.6;">
                                El documento adjunto está protegido y habilitado únicamente para impresión.
                            </p>
                            <p style="margin:18px 0 0; font-size:14px;">Atentamente,<br><strong>Alcaldía de Puerto Boyacá, Boyacá</strong></p>
                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td style="background-color:#f8fafc; padding:18px 28px; text-align:center; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.6;">
                                Este es un correo automático, por favor no responder.<br>
                                &copy; {{ date('Y') }} Alcaldía Municipal de Puerto Boyacá — Sistema de Gestión.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
