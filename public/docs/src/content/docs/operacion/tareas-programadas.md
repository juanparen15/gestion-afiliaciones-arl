---
title: Tareas programadas
description: Notificaciones automáticas, recordatorios y el cron del programador de Laravel.
sidebar:
  order: 2
---

El sistema ejecuta varias tareas automáticas (notificaciones y actualizaciones)
mediante el **programador de Laravel**, definido en `routes/console.php`.

## El cron maestro

Para que las tareas corran, debe existir **una sola** entrada de cron que
ejecute el programador cada minuto (como usuario `www-data`):

```cron
* * * * * cd /var/www/html/gestion-afiliaciones-arl && php artisan schedule:run >> /dev/null 2>&1
```

Verificar que está activo:

```bash
crontab -l -u www-data          # ver el cron
php artisan schedule:list       # ver las tareas y su próxima ejecución
```

## Tareas configuradas

| Tarea | Comando | Frecuencia |
|-------|---------|-----------|
| Afiliaciones ARL próximas a vencer | `afiliaciones:notificar-vencimientos --dias=30` | Lunes 07:00 |
| Contratos SECOP próximos a vencer | `contratos:notificar-vencimientos --dias=30` | Lunes 07:30 |
| Actualizar estados de contratos | `contratos:actualizar-estados` | Diario 06:00 |
| Afiliaciones pendientes (aviso a SSST) | `afiliaciones:notificar-pendientes` | Diario 08:00 |
| Recordatorio de actas pendientes | `actas:recordar-pendientes` | Lunes 07:45 |

Todas corren con `withoutOverlapping()` y `runInBackground()`. Las de
notificación envían el error por correo a `MAIL_ADMIN_ADDRESS` si fallan.

## Ejecutar una tarea manualmente

Útil para probar o forzar un envío:

```bash
php artisan afiliaciones:notificar-vencimientos --dias=30
php artisan actas:recordar-pendientes
php artisan contratos:actualizar-estados
```

## Colas

`QUEUE_CONNECTION=sync`: los correos se envían de forma **síncrona** (no hay
worker de cola). Si en el futuro se cambia a `database`/`redis`, habrá que
levantar un worker:

```bash
php artisan queue:work --tries=3
```

y supervisarlo (systemd o Supervisor).
