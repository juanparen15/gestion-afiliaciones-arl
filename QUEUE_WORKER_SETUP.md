# Configuración del Worker de Colas (Queue Worker)

## Problema Identificado

Los correos de "Olvidé mi contraseña" no se enviaban inmediatamente porque la aplicación usa `QUEUE_CONNECTION=database`, lo que significa que los correos se encolan en la base de datos y esperan a ser procesados por un worker.

## Solución Actual (Desarrollo)

Se cambió la configuración a `QUEUE_CONNECTION=sync` en el archivo `.env`:

```env
QUEUE_CONNECTION=sync
```

Esto hace que los correos se envíen **inmediatamente** sin pasar por la cola.

---

## Opción Alternativa: Usar Colas en Producción

Si deseas usar colas en producción para mejorar el rendimiento (recomendado para servidores con alto tráfico), sigue estos pasos:

### 1. Configurar la cola en `.env`

```env
QUEUE_CONNECTION=database
```

### 2. Ejecutar el worker de colas

**Opción A: Comando Manual (Solo para pruebas)**

```bash
php artisan queue:work --tries=3 --timeout=90
```

**Opción B: Supervisor (Recomendado para producción)**

Crea el archivo `/etc/supervisor/conf.d/gestion-arl-worker.conf`:

```ini
[program:gestion-arl-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/completa/al/proyecto/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/ruta/completa/al/proyecto/storage/logs/worker.log
stopwaitsecs=3600
```

Luego ejecuta:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gestion-arl-worker:*
```

**Opción C: Systemd (Alternativa a Supervisor)**

Crea el archivo `/etc/systemd/system/gestion-arl-worker.service`:

```ini
[Unit]
Description=Gestion ARL Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/ruta/completa/al/proyecto
ExecStart=/usr/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Luego ejecuta:

```bash
sudo systemctl daemon-reload
sudo systemctl enable gestion-arl-worker
sudo systemctl start gestion-arl-worker
sudo systemctl status gestion-arl-worker
```

### 3. Monitorear la cola

Ver trabajos en cola:

```bash
php artisan queue:monitor
```

Ver trabajos fallidos:

```bash
php artisan queue:failed
```

Reintentar trabajos fallidos:

```bash
php artisan queue:retry all
```

Limpiar trabajos fallidos:

```bash
php artisan queue:flush
```

---

## Recomendación

- **Desarrollo local**: Usar `QUEUE_CONNECTION=sync` (configuración actual)
- **Producción**: Usar `QUEUE_CONNECTION=database` con Supervisor o Systemd

---

## Verificar que los correos se están enviando

1. **Con colas deshabilitadas (`sync`)**:
   - Los correos se envían inmediatamente
   - Verifica el log en `storage/logs/laravel.log`

2. **Con colas habilitadas (`database`)**:
   - Los correos se encolan
   - Necesitas el worker corriendo
   - Ejecuta: `php artisan queue:work --once` para procesar un trabajo

---

## Procesamiento de correos pendientes

Si tienes correos pendientes en la cola, procésalos con:

```bash
# Procesar todos los trabajos en cola
php artisan queue:work --stop-when-empty

# O procesar uno por uno
php artisan queue:work --once
```

---

**Última actualización**: 5 de diciembre de 2025
**Estado actual**: Correos enviándose inmediatamente (`QUEUE_CONNECTION=sync`)
