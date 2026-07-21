# Despliegue — Notas de infraestructura

Configuración del servidor para funcionalidades que dependen de servicios externos.

## 1. Cola en segundo plano (aprobación masiva de certificados ARL)

La acción **"Aprobar Masivo (Certificados ARL)"** procesa lotes grandes (>20 PDFs)
en segundo plano mediante un Job en cola. Sin esto, todo funciona igual pero en
línea (síncrono), lo que puede dar timeout en lotes muy grandes.

**Pasos:**

1. En `.env`:
   ```env
   QUEUE_CONNECTION=database
   ```
2. Crear la tabla de jobs (si no existe):
   ```bash
   php artisan migrate
   ```
3. Configurar el worker permanente con Supervisor:
   - Copiar [`supervisor-arl-worker.conf`](./supervisor-arl-worker.conf) a
     `/etc/supervisor/conf.d/arl-worker.conf`
   - Ajustar rutas, usuario y `numprocs`.
   - Activar:
     ```bash
     sudo supervisorctl reread
     sudo supervisorctl update
     sudo supervisorctl start arl-worker:*
     ```
4. **Tras cada despliegue**, reiniciar el worker para que tome el código nuevo:
   ```bash
   php artisan queue:restart
   ```

## 2. Lectura de PDFs (pdftotext / poppler)

El extractor de cédula de los certificados ARL usa `pdftotext` (poppler-utils),
que lee PDFs protegidos de forma rápida y gratuita. Si no está instalado, el
sistema cae automáticamente a Gemini (más lento, requiere API key).

```bash
sudo apt install poppler-utils
```

Opcional, si el binario está en una ruta no estándar, definir en `.env`:
```env
PDFTOTEXT_BIN=/usr/bin/pdftotext
```

## 2b. Generación de Actas de Necesidad (LibreOffice)

El módulo **Actas de Necesidad** genera el PDF a partir de la plantilla oficial
`.docx` (`resources/document-templates/acta_necesidad.docx`) y la convierte a PDF
con **LibreOffice headless**. Es obligatorio instalarlo en el servidor:

```bash
sudo apt install libreoffice-writer   # o libreoffice-nogui / libreoffice-core
```

Definir la ruta del binario en `.env` (si no está en el PATH):
```env
# Linux
LIBREOFFICE_BIN=/usr/bin/soffice
# Windows (Laragon/local)
LIBREOFFICE_BIN="C:/Program Files/LibreOffice/program/soffice.exe"
```

Migración de datos existentes (una sola vez), desde el Excel de respuestas:
```bash
php artisan actas:importar-excel "/ruta/ACTA DE NECESIDAD 2026 V.3 (Respuestas).xlsx"
```

La firma del alcalde y su texto se configuran desde la app (botón
"Configuración de firma" en Actas de Necesidad). La firma por defecto vive en
`public/images/actas/firma-alcalde.png`.

**Protección del PDF:** el acta se exporta cifrada con permisos de SOLO IMPRESIÓN
(no modificar, no copiar/extraer la firma), usando el FilterData de LibreOffice.
Requiere LibreOffice 7.4+ (versiones anteriores generan el PDF sin cifrado y se
registra una advertencia). Se puede desactivar con `ACTAS_PROTEGER_PDF=false`.

**Verificación por QR:** cada acta aprobada lleva un QR que apunta a
`/actas/verificar/{codigo}` (página pública de autenticidad). Requiere que
`APP_URL` esté bien configurado en producción para que el QR apunte al dominio real.

**Recordatorios a aprobadores:** el comando `actas:recordar-pendientes` corre cada
lunes 07:45 (ver `routes/console.php`); requiere el scheduler activo en el cron.

**Correo:** el envío es síncrono; se recomienda `QUEUE_CONNECTION=database` + worker
para que el envío de correos de actas no bloquee la petición si el SMTP está lento.

**Permisos (Filament Shield):** tras el despliegue, generar la política y permisos
del recurso y otorgarlos a los roles operativos:
```bash
php artisan shield:generate --resource=ActaNecesidadResource --panel=admin
```
Luego, en la app (Roles/Shield), habilitar `view_any`, `view`, `create`, `update`
de "acta::necesidad" para los roles **Dependencia**, **SSST** y **Administrador**
(super_admin tiene acceso total). Quién puede **aprobar/rechazar** se controla
aparte, con el toggle "Puede aprobar/rechazar actas de necesidad" en cada usuario.

## 3. Variables de entorno de Gemini (IA)

Usadas por el chat de IA y por el respaldo de lectura de certificados escaneados.

```env
GEMINI_API_KEY=tu_api_key
GEMINI_MODEL=gemini-2.5-flash
```

> Nota: el modelo `gemini-2.0-flash` fue retirado de la API. Usar `gemini-2.5-flash`.

## 4. Límites de subida (php.ini) para registro masivo

Para subir muchos certificados a la vez, ajustar en el `php.ini` de producción:

```ini
max_file_uploads = 500
upload_max_filesize = 100M
post_max_size = 200M
memory_limit = 1024M
max_execution_time = 300
```

Reiniciar PHP-FPM tras el cambio.
