---
title: Comandos Artisan
description: Referencia de comandos útiles para administrar el sistema
---

## Comandos de Instalación

### Configuración Inicial

```bash
# Clonar repositorio
git clone https://github.com/tu-org/gestion-afiliaciones-arl.git
cd gestion-afiliaciones-arl

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate
php artisan db:seed

# Generar permisos
php artisan shield:generate --all

# Crear enlace de storage
php artisan storage:link

# Compilar assets
npm run build
```

---

## Comandos de Desarrollo

### Servidor Local

```bash
# Iniciar servidor Laravel
php artisan serve

# Iniciar con puerto específico
php artisan serve --port=8080

# Compilar assets en desarrollo (watch)
npm run dev
```

### Base de Datos

```bash
# Ver estado de migraciones
php artisan migrate:status

# Ejecutar migraciones pendientes
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Resetear y volver a migrar
php artisan migrate:fresh

# Migrar y seedear
php artisan migrate:fresh --seed

# Ejecutar seeder específico
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Generar Código

```bash
# Crear modelo con migración
php artisan make:model NuevoModelo -m

# Crear controlador
php artisan make:controller NuevoController

# Crear recurso Filament
php artisan make:filament-resource NuevoRecurso

# Crear evento
php artisan make:event NuevoEvento

# Crear listener
php artisan make:listener NuevoListener

# Crear mail
php artisan make:mail NuevoEmail

# Crear policy
php artisan make:policy NuevaPolicy --model=Modelo

# Crear observer
php artisan make:observer NuevoObserver --model=Modelo
```

---

## Comandos de Caché

### Limpiar Cachés

```bash
# Limpiar todo
php artisan optimize:clear

# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Limpiar caché de vistas
php artisan view:clear

# Limpiar caché de aplicación
php artisan cache:clear

# Limpiar caché de eventos
php artisan event:clear
```

### Crear Cachés (Producción)

```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Cachear eventos
php artisan event:cache

# Optimizar todo
php artisan optimize
```

---

## Comandos de Permisos

### Filament Shield

```bash
# Generar todos los permisos
php artisan shield:generate --all

# Generar para recurso específico
php artisan shield:generate --resource=AfiliacionResource

# Instalar Shield
php artisan shield:install

# Crear super admin
php artisan shield:super-admin
```

### Spatie Permission

```bash
# Limpiar caché de permisos
php artisan permission:cache-reset

# Mostrar permisos
php artisan permission:show
```

---

## Comandos de Cola

### Trabajadores

```bash
# Iniciar worker
php artisan queue:work

# Iniciar con cola específica
php artisan queue:work --queue=emails

# Procesar un solo job
php artisan queue:work --once

# Con timeout
php artisan queue:work --timeout=60

# Con reintentos
php artisan queue:work --tries=3

# En producción (recomendado)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Gestión de Colas

```bash
# Ver jobs fallidos
php artisan queue:failed

# Reintentar job fallido
php artisan queue:retry 5

# Reintentar todos los fallidos
php artisan queue:retry all

# Limpiar jobs fallidos
php artisan queue:flush

# Monitorear cola
php artisan queue:monitor
```

---

## Comandos de Logs

### Ver Logs

```bash
# Ver logs en tiempo real
php artisan pail

# Filtrar por nivel
php artisan pail --filter="level:error"

# Filtrar por mensaje
php artisan pail --filter="message:afiliacion"
```

---

## Comandos de Mantenimiento

### Modo Mantenimiento

```bash
# Activar modo mantenimiento
php artisan down

# Con mensaje personalizado
php artisan down --message="Actualizando el sistema"

# Permitir IP específica
php artisan down --allow=192.168.1.1

# Desactivar modo mantenimiento
php artisan up
```

### Storage

```bash
# Crear enlace simbólico
php artisan storage:link

# Limpiar archivos temporales
php artisan storage:clear
```

---

## Comandos de Testing

```bash
# Ejecutar todos los tests
php artisan test

# Con cobertura
php artisan test --coverage

# Test específico
php artisan test --filter=AfiliacionTest

# Ejecutar tests en paralelo
php artisan test --parallel
```

---

## Comandos de Debug

### Información del Sistema

```bash
# Ver información de la aplicación
php artisan about

# Ver rutas registradas
php artisan route:list

# Ver rutas de Filament
php artisan route:list --path=admin

# Ver eventos y listeners
php artisan event:list
```

### Tinker (Consola Interactiva)

```bash
# Iniciar tinker
php artisan tinker

# Ejemplos en tinker:
>>> Afiliacion::count()
>>> User::find(1)->roles
>>> Afiliacion::pendiente()->get()
>>> User::first()->assignRole('SSST')
```

---

## Comandos Personalizados

### Crear Comando

```bash
php artisan make:command LimpiarAfiliacionesAntiguas
```

### Ejemplo de Comando

```php
// app/Console/Commands/LimpiarAfiliacionesAntiguas.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Afiliacion;

class LimpiarAfiliacionesAntiguas extends Command
{
    protected $signature = 'afiliaciones:limpiar {--dias=365}';
    protected $description = 'Elimina permanentemente afiliaciones eliminadas hace más de X días';

    public function handle()
    {
        $dias = $this->option('dias');

        $count = Afiliacion::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($dias))
            ->forceDelete();

        $this->info("Se eliminaron {$count} afiliaciones antiguas.");
    }
}
```

### Programar Comando

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('afiliaciones:limpiar')
        ->monthly();
}
```

---

## Scripts Útiles

### Script de Actualización

```bash
#!/bin/bash
# update.sh

php artisan down

git pull origin master

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo supervisorctl restart arl-worker:*

php artisan up

echo "Actualización completada"
```

### Script de Respaldo

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/var/backups/arl

mkdir -p $BACKUP_DIR

# Respaldar BD
mysqldump -u arl_user -p'password' gestion_arl | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Respaldar storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/gestion-afiliaciones-arl/storage/app

echo "Respaldo completado: $DATE"
```

---

## Comandos de Email

### Probar Email

```bash
php artisan tinker

>>> use Illuminate\Support\Facades\Mail;
>>> Mail::raw('Test', function($m) { $m->to('test@test.com')->subject('Test'); });
```

### Ver Configuración de Mail

```bash
php artisan tinker

>>> config('mail')
```

---

## Comandos Frecuentes

| Tarea | Comando |
|-------|---------|
| Limpiar todo | `php artisan optimize:clear` |
| Ver rutas | `php artisan route:list` |
| Regenerar permisos | `php artisan shield:generate --all` |
| Crear usuario admin | `php artisan shield:super-admin` |
| Ver logs | `php artisan pail` |
| Modo mantenimiento | `php artisan down` / `php artisan up` |
| Ejecutar migraciones | `php artisan migrate` |
| Iniciar servidor | `php artisan serve` |

---

## Próximos Pasos

- [Solución de Problemas](/docs/referencia/troubleshooting/)
