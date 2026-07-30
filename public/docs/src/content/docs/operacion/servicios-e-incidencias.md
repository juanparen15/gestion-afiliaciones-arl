---
title: Servicios, respaldos e incidencias
description: LibreOffice y PDF, correo SMTP, respaldos y solución de incidencias comunes en el servidor.
sidebar:
  order: 3
---

## LibreOffice (generación de PDF de Actas)

Las Actas de Necesidad se generan convirtiendo un `.docx` a PDF con
**LibreOffice headless**. El servidor debe tener LibreOffice instalado.

```bash
# Instalar
sudo apt update && sudo apt install -y libreoffice

# Verificar
soffice --version
which soffice
```

Configuración en `.env`:

```env
LIBREOFFICE_BIN="soffice"          # o la ruta absoluta a soffice
ACTAS_PROTEGER_PDF=true            # PDF con permisos de solo impresión
```

:::caution[Si las actas no generan PDF]
- **"Class TemplateProcessor not found"** → falta `composer install` en el servidor.
- **Conversión vacía o cuelga** → LibreOffice mal instalado o sin permisos de
  escritura en su perfil temporal. Reinstalar y confirmar `soffice --version`.
- No editar el `FilterData` para usar `PageRange`: genera PDFs vacíos.
:::

## Correo (SMTP)

El envío de actas y notificaciones usa SMTP. Configuración en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=sistemas@puertoboyaca-boyaca.gov.co
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
MAIL_ADMIN_ADDRESS=...        # recibe errores de las tareas programadas
```

Probar el envío:

```bash
php artisan tinker --execute="Mail::raw('Prueba', fn(\$m)=>\$m->to('tu@correo.com')->subject('Test'));"
```

Si un acta se aprueba pero el correo falla, queda registrado y aparece la acción
**Reenviar correo** en la tabla.

## Respaldos

**Base de datos** (diario recomendado):

```bash
mysqldump -u USUARIO -p BASEDATOS > /ruta/backups/arl_$(date +%F).sql
```

**Archivos generados** (PDF de actas, firmas, adjuntos):

```bash
tar czf /ruta/backups/arl_storage_$(date +%F).tgz storage/app/public
```

Conservar también el `.env` en un lugar seguro (no está en el repositorio).

## Incidencias comunes

| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| `FileNotFoundException ... views/*.blade.php` | Caché de vistas desincronizada tras un deploy | `php artisan view:clear && php artisan optimize:clear` |
| `Permission denied` en `storage/` | Propietario/permisos | `chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 ...` |
| Cambios de código no se reflejan | OPcache | Recargar `php8.3-fpm` |
| Imagen/PDF viejo tras actualizar | Caché de navegador/Cloudflare | Ctrl+F5 o Purge en Cloudflare |
| Las tareas automáticas no corren | Falta el cron maestro | Ver [Tareas programadas](/docs/operacion/tareas-programadas/) |

## Diagnóstico rápido

```bash
php artisan about               # estado general del framework
php artisan schedule:list       # tareas programadas
tail -f storage/logs/laravel.log
```
