---
title: Configuración
description: Configuración avanzada del Sistema de Gestión de Afiliaciones ARL
---

## Configuración del Panel Filament

El panel de administración se configura en `app/Providers/Filament/AdminPanelProvider.php`.

### Personalizar Branding

```php
->brandName('Gestión ARL')
->brandLogo(asset('images/logo.svg'))
->brandLogoHeight('2rem')
->favicon(asset('images/favicon.ico'))
```

### Cambiar Colores

```php
->colors([
    'primary' => Color::Blue,
    'danger' => Color::Rose,
    'warning' => Color::Orange,
    'success' => Color::Emerald,
])
```

### Configurar Página de Login

```php
->login()
->registration(false) // Deshabilitar auto-registro
->passwordReset()
```

---

## Configuración de Correo

### Mailtrap (Desarrollo)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
```

### SMTP Institucional

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.tudominio.gov.co
MAIL_PORT=587
MAIL_USERNAME=sistema@tudominio.gov.co
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="sistema@tudominio.gov.co"
MAIL_FROM_NAME="Sistema de Gestión ARL"
```

### Amazon SES

```env
MAIL_MAILER=ses
```

Configura las credenciales de AWS en `.env`:
```env
AWS_ACCESS_KEY_ID=tu_access_key
AWS_SECRET_ACCESS_KEY=tu_secret_key
AWS_DEFAULT_REGION=us-east-1
```

### Probar Envío de Email

```bash
php artisan tinker

# En tinker
Mail::raw('Prueba de email', function($message) {
    $message->to('tu@email.com')->subject('Test');
});
```

---

## Configuración de Colas

Para mejor rendimiento en notificaciones, configura colas:

### Driver de Base de Datos

```env
QUEUE_CONNECTION=database
```

Crear tabla de trabajos:
```bash
php artisan queue:table
php artisan migrate
```

### Ejecutar Worker

```bash
# Desarrollo
php artisan queue:work

# Producción (con Supervisor)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Configurar Supervisor (Linux)

Crea `/etc/supervisor/conf.d/arl-worker.conf`:

```ini
[program:arl-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gestion-afiliaciones-arl/artisan queue:work --sleep=3 --tries=3 --max-time=3600
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

## Configuración de Dependencias y Áreas

### Agregar Nueva Dependencia

1. Accede como administrador
2. Ve a **Dependencias** en el menú
3. Click en **Nueva Dependencia**
4. Completa los campos:
   - Nombre (ej: "Secretaría de Hacienda")
   - Código (ej: "HAC")
   - Responsable
   - Email y teléfono de contacto
5. Guardar

### Agregar Nueva Área

1. Ve a **Áreas** en el menú
2. Click en **Nueva Área**
3. Selecciona la **Dependencia padre**
4. Completa los campos:
   - Nombre (ej: "Área de Presupuesto")
   - Código (ej: "HAC-PRE")
5. Guardar

---

## Configuración de Usuarios

### Crear Usuario Nuevo

1. Ve a **Usuarios** en el menú
2. Click en **Nuevo Usuario**
3. Completa la información:
   - Nombre completo
   - Email (para login)
   - Correo institucional
   - Cargo
   - Dependencia
   - Área
   - Contraseña
4. Asigna el **Rol** correspondiente
5. Guardar

### Roles Disponibles

| Rol | Código | Descripción |
|-----|--------|-------------|
| Super Admin | super_admin | Acceso total |
| SSST | SSST | Valida afiliaciones |
| Dependencia | Dependencia | Crea afiliaciones |

---

## Configuración de Activity Log

El sistema registra automáticamente todas las acciones. Configura en `config/activitylog.php`:

```php
return [
    'enabled' => true,
    'delete_records_older_than_days' => 365, // Limpiar logs viejos
    'default_log_name' => 'default',
    'default_auth_driver' => null,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
];
```

### Ver Logs de Actividad

```bash
php artisan tinker

# Ver últimas 10 actividades
Activity::latest()->take(10)->get();
```

---

## Configuración de Sentry (Monitoreo)

### Obtener DSN

1. Crea cuenta en [sentry.io](https://sentry.io)
2. Crea nuevo proyecto Laravel
3. Copia el DSN

### Configurar en .env

```env
SENTRY_LARAVEL_DSN=https://xxxx@xxxx.ingest.sentry.io/xxxx
```

### Probar Integración

```bash
php artisan sentry:test
```

---

## Configuración de Almacenamiento

### Local (Por defecto)

```env
FILESYSTEM_DISK=local
```

Los archivos se guardan en `storage/app/`.

### Amazon S3

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=tu_access_key
AWS_SECRET_ACCESS_KEY=tu_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=tu-bucket
```

### Configurar Límite de Subida

En `php.ini`:

```ini
upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 300
```

---

## Variables de Configuración Personalizadas

Puedes crear un archivo de configuración personalizado en `config/arl.php`:

```php
<?php

return [
    'ibc_percentage' => 40, // Porcentaje para cálculo de IBC
    'contract_warning_days' => 30, // Días antes de vencimiento para alertas
    'max_file_size' => 10240, // KB
    'allowed_file_types' => ['pdf', 'doc', 'docx'],
    'risk_levels' => ['I', 'II', 'III', 'IV', 'V'],
];
```

Acceder a la configuración:

```php
$percentage = config('arl.ibc_percentage');
$warningDays = config('arl.contract_warning_days');
```

---

## Configuración de Zona Horaria

En `.env`:

```env
APP_TIMEZONE=America/Bogota
```

En `config/app.php`:

```php
'timezone' => env('APP_TIMEZONE', 'America/Bogota'),
```

---

## Siguiente Paso

Continúa con la [Guía de Despliegue](/instalacion/despliegue/) para publicar en producción.
