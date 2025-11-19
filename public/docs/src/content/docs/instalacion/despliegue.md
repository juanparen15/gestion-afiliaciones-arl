---
title: Despliegue en Producción
description: Guía para desplegar el Sistema de Gestión de Afiliaciones ARL en un servidor de producción
---

## Preparación del Servidor

### Requisitos del Servidor

- Ubuntu 22.04 LTS o superior
- 4 GB RAM mínimo
- 50 GB SSD
- PHP 8.2+ con extensiones
- MySQL 8.0+
- Nginx o Apache
- Composer 2.x
- Node.js 18+
- SSL/TLS certificado

---

## Configuración del Entorno de Producción

### Variables de Entorno (.env)

```env
APP_NAME="Gestión ARL"
APP_ENV=production
APP_KEY=base64:TU_CLAVE_GENERADA
APP_DEBUG=false
APP_URL=https://arl.tudominio.gov.co

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_arl_prod
DB_USERNAME=arl_user
DB_PASSWORD=CONTRASEÑA_SEGURA

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.tudominio.gov.co
MAIL_PORT=587
MAIL_USERNAME=sistema@tudominio.gov.co
MAIL_PASSWORD=TU_CONTRASEÑA
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="sistema@tudominio.gov.co"
MAIL_FROM_NAME="${APP_NAME}"

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

:::danger[Importante]
- Nunca uses `APP_DEBUG=true` en producción
- Usa contraseñas fuertes
- El archivo `.env` no debe estar en el repositorio
:::

---

## Despliegue Paso a Paso

### 1. Subir Código al Servidor

```bash
# Opción A: Git
cd /var/www
git clone https://github.com/tu-org/gestion-afiliaciones-arl.git
cd gestion-afiliaciones-arl

# Opción B: SFTP/SCP
scp -r ./gestion-afiliaciones-arl user@servidor:/var/www/
```

### 2. Instalar Dependencias

```bash
# Dependencias PHP (sin dev)
composer install --optimize-autoloader --no-dev

# Dependencias JavaScript
npm ci
npm run build
```

### 3. Configurar Permisos

```bash
# Propietario
sudo chown -R www-data:www-data /var/www/gestion-afiliaciones-arl

# Permisos de directorios
sudo find /var/www/gestion-afiliaciones-arl -type d -exec chmod 755 {} \;

# Permisos de archivos
sudo find /var/www/gestion-afiliaciones-arl -type f -exec chmod 644 {} \;

# Permisos especiales para storage y bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Configurar Entorno

```bash
# Copiar y editar .env
cp .env.example .env
nano .env  # Editar con valores de producción

# Generar clave
php artisan key:generate
```

### 5. Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders (solo primera vez)
php artisan db:seed --force

# Generar permisos de Shield
php artisan shield:generate --all
```

### 6. Optimizar para Producción

```bash
# Crear caché de configuración
php artisan config:cache

# Crear caché de rutas
php artisan route:cache

# Crear caché de vistas
php artisan view:cache

# Crear caché de eventos
php artisan event:cache

# Optimizar autoloader
composer dump-autoload --optimize
```

### 7. Crear Enlace de Storage

```bash
php artisan storage:link
```

---

## Configuración de Nginx

Crea `/etc/nginx/sites-available/arl`:

```nginx
server {
    listen 80;
    server_name arl.tudominio.gov.co;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name arl.tudominio.gov.co;
    root /var/www/gestion-afiliaciones-arl/public;

    # SSL
    ssl_certificate /etc/ssl/certs/arl.crt;
    ssl_certificate_key /etc/ssl/private/arl.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;

    # Seguridad
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php;
    charset utf-8;

    # Logs
    access_log /var/log/nginx/arl-access.log;
    error_log /var/log/nginx/arl-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Caché de assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

Habilitar sitio:

```bash
sudo ln -s /etc/nginx/sites-available/arl /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Configurar Supervisor para Colas

Crea `/etc/supervisor/conf.d/arl-worker.conf`:

```ini
[program:arl-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gestion-afiliaciones-arl/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/gestion-afiliaciones-arl/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start arl-worker:*
```

---

## Configurar Cron para Tareas Programadas

Edita el crontab:

```bash
sudo crontab -u www-data -e
```

Agrega:

```
* * * * * cd /var/www/gestion-afiliaciones-arl && php artisan schedule:run >> /dev/null 2>&1
```

---

## SSL con Let's Encrypt

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d arl.tudominio.gov.co

# Auto-renovación
sudo certbot renew --dry-run
```

---

## Monitoreo y Logging

### Logs de Laravel

```bash
# Ver logs en tiempo real
tail -f /var/www/gestion-afiliaciones-arl/storage/logs/laravel.log

# O usar pail
php artisan pail
```

### Configurar Log Rotation

Crea `/etc/logrotate.d/arl`:

```
/var/www/gestion-afiliaciones-arl/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

---

## Respaldo de Base de Datos

### Script de Respaldo

Crea `/usr/local/bin/backup-arl.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/var/backups/arl
mkdir -p $BACKUP_DIR

# Respaldar BD
mysqldump -u arl_user -p'CONTRASEÑA' gestion_arl_prod | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Respaldar archivos
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/gestion-afiliaciones-arl/storage/app

# Eliminar respaldos de más de 30 días
find $BACKUP_DIR -type f -mtime +30 -delete
```

```bash
chmod +x /usr/local/bin/backup-arl.sh

# Programar respaldo diario
echo "0 2 * * * root /usr/local/bin/backup-arl.sh" | sudo tee /etc/cron.d/arl-backup
```

---

## Actualización del Sistema

### Proceso de Actualización

```bash
cd /var/www/gestion-afiliaciones-arl

# Activar modo mantenimiento
php artisan down

# Obtener cambios
git pull origin master

# Instalar dependencias
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Ejecutar migraciones
php artisan migrate --force

# Limpiar y recrear cachés
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar workers
sudo supervisorctl restart arl-worker:*

# Desactivar modo mantenimiento
php artisan up
```

---

## Checklist de Seguridad

- [ ] `APP_DEBUG=false`
- [ ] Contraseñas fuertes en `.env`
- [ ] SSL/TLS configurado
- [ ] Firewall configurado (solo puertos 80, 443, 22)
- [ ] Permisos de archivos correctos
- [ ] `.env` no accesible desde web
- [ ] Respaldos automáticos configurados
- [ ] Monitoreo de errores (Sentry)
- [ ] Actualizaciones de seguridad del SO
- [ ] Cambiar usuarios de prueba por defecto

---

## Soporte

Si encuentras problemas durante el despliegue, consulta la sección de [Solución de Problemas](/referencia/troubleshooting/).
