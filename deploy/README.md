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
