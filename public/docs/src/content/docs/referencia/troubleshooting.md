---
title: Solución de Problemas
description: Guía para resolver problemas comunes del sistema
---

## Problemas de Instalación

### Error: "Class not found"

**Síntoma**: Error de clase no encontrada al cargar la página.

**Solución**:
```bash
composer dump-autoload
php artisan optimize:clear
```

---

### Error: "SQLSTATE[HY000] [1045] Access denied"

**Síntoma**: No puede conectar a la base de datos.

**Causas posibles**:
- Credenciales incorrectas en `.env`
- Usuario sin permisos
- Base de datos no existe

**Solución**:
1. Verificar credenciales en `.env`:
```env
DB_HOST=127.0.0.1
DB_DATABASE=gestion_arl
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

2. Verificar que la BD existe:
```bash
mysql -u root -p
SHOW DATABASES;
```

3. Crear si no existe:
```sql
CREATE DATABASE gestion_arl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Error: "The Mix manifest does not exist"

**Síntoma**: Assets no cargan, error de manifest.

**Solución**:
```bash
npm install
npm run dev
# o para producción:
npm run build
```

---

### Error: "Permission denied" en storage/

**Síntoma**: No puede escribir en storage o bootstrap/cache.

**Solución Linux/macOS**:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Solución Windows (Laragon)**:
- Verificar que Laragon se ejecute como administrador

---

### Error: "No application encryption key"

**Síntoma**: Error de clave de aplicación.

**Solución**:
```bash
php artisan key:generate
```

---

## Problemas de Autenticación

### No puedo iniciar sesión

**Verificaciones**:

1. **Usuario existe**:
```bash
php artisan tinker
>>> User::where('email', 'tu@email.com')->first()
```

2. **Contraseña correcta**:
```bash
>>> $user = User::where('email', 'tu@email.com')->first();
>>> Hash::check('password123', $user->password)
```

3. **Tiene rol asignado**:
```bash
>>> $user->roles
```

4. **Si necesitas reset**:
```bash
>>> $user->password = Hash::make('nueva_password');
>>> $user->save();
```

---

### Error 403: Forbidden

**Síntoma**: Usuario autenticado pero sin acceso.

**Causas**:
- Sin rol asignado
- Sin permisos para la acción
- Filtrado por dependencia

**Solución**:
```bash
php artisan tinker

# Verificar rol
>>> $user = User::find(1);
>>> $user->roles

# Asignar rol
>>> $user->assignRole('Dependencia');

# Limpiar caché de permisos
>>> app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

---

### Sesión expira muy rápido

**Solución**: Ajustar en `.env`:
```env
SESSION_LIFETIME=120
```

O en `config/session.php`:
```php
'lifetime' => env('SESSION_LIFETIME', 120),
```

---

## Problemas de Afiliaciones

### No puedo crear afiliaciones

**Verificar**:
1. Tienes rol con permiso `create_afiliacion`
2. Los campos requeridos están completos
3. El número de documento no existe ya

---

### No veo todas las afiliaciones

**Causa**: El filtro por dependencia está activo.

**Si eres Dependencia**: Solo ves tu dependencia/área.

**Si eres SSST/Admin**: Verifica que no hay filtros aplicados.

**Solución técnica**: Revisar `getEloquentQuery()` en el Resource.

---

### El IBC no se calcula automáticamente

**Verificar**: El campo `honorarios_mensual` debe estar lleno.

**El cálculo es**:
```
IBC = honorarios_mensual * 0.4
```

Si no funciona, revisa el `afterStateUpdated` en el formulario.

---

### No puedo subir archivos

**Causas posibles**:
- Archivo muy grande
- Tipo de archivo no permitido
- Sin permisos en storage

**Verificar límites PHP**:
```ini
upload_max_filesize = 20M
post_max_size = 25M
```

**Verificar enlace de storage**:
```bash
php artisan storage:link
```

---

## Problemas de Importación Excel

### "El archivo no es válido"

**Verificar**:
- Formato `.xlsx` o `.xls`
- Archivo no corrupto
- Tamaño menor a 10MB

---

### "La dependencia no existe"

**Causa**: El nombre en el Excel no coincide exactamente.

**Solución**:
1. Verificar nombres exactos en el sistema
2. Usar el código de dependencia en lugar del nombre
3. No distingue mayúsculas/minúsculas

---

### "Formato de fecha inválido"

**Formatos aceptados**:
```
DD/MM/YYYY → 01/01/2024
DD-MM-YYYY → 01-01-2024
YYYY-MM-DD → 2024-01-01
```

**Solución**: Formatear columna como texto en Excel.

---

### Importación tarda mucho

**Causas**:
- Archivo muy grande
- Muchas validaciones

**Soluciones**:
- Dividir en archivos más pequeños
- Importar en horas de baja carga
- Configurar colas para procesar en background

---

## Problemas de Email

### No se envían emails

**Verificar configuración en `.env`**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
```

**Probar envío**:
```bash
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@test.com'); });
```

---

### Error de conexión SMTP

**Verificar**:
- Host y puerto correctos
- Credenciales válidas
- Firewall no bloquea puerto

---

### Emails van a spam

**Soluciones**:
- Configurar SPF y DKIM
- Usar servicio de email transaccional (Mailgun, SES)
- Verificar contenido del email

---

## Problemas de Rendimiento

### El sistema está lento

**Diagnóstico**:
```bash
# Ver logs
php artisan pail

# Verificar queries lentas
# Agregar en config/database.php
'mysql' => [
    'strict' => false,
    'log_queries' => true,
]
```

**Soluciones**:
1. Habilitar caché en producción
2. Usar eager loading en queries
3. Agregar índices a la BD
4. Configurar Redis para caché y sesiones

---

### Widgets del dashboard tardan

**Causa**: Queries no optimizadas.

**Solución**: Cachear estadísticas:
```php
$stats = Cache::remember('dashboard_stats', 3600, function () {
    return [
        'total' => Afiliacion::count(),
        // ...
    ];
});
```

---

## Problemas de Permisos

### Permisos no funcionan después de cambio de rol

**Solución**:
```bash
php artisan permission:cache-reset
```

O en tinker:
```php
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

---

### Shield no genera permisos

**Solución**:
```bash
php artisan shield:generate --all
php artisan optimize:clear
```

---

## Errores Comunes en Producción

### Error 500 sin detalles

**Verificar**:
1. Logs en `storage/logs/laravel.log`
2. Permisos de storage
3. Configuración de caché

**Temporal para debug**:
```env
APP_DEBUG=true
```

:::danger
Nunca dejes `APP_DEBUG=true` en producción.
:::

---

### CSS/JS no cargan

**Verificar**:
1. Assets compilados: `npm run build`
2. Enlace de storage: `php artisan storage:link`
3. URL correcta en `.env`: `APP_URL`

---

### Colas no procesan

**Verificar Supervisor**:
```bash
sudo supervisorctl status
sudo supervisorctl restart arl-worker:*
```

**Ver logs del worker**:
```bash
tail -f storage/logs/worker.log
```

---

## Herramientas de Diagnóstico

### Ver Logs

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Con pail
php artisan pail
```

### Verificar Configuración

```bash
php artisan about
```

### Verificar Rutas

```bash
php artisan route:list --path=admin
```

### Verificar Base de Datos

```bash
php artisan migrate:status
```

### Verificar Permisos

```bash
php artisan permission:show
```

---

## Restablecer el Sistema

### Reset Completo (Desarrollo)

```bash
php artisan migrate:fresh --seed
php artisan shield:generate --all
php artisan optimize:clear
```

### Limpiar y Reconstruir Caché

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Obtener Ayuda

### Información para Reportar Bug

Al reportar un problema, incluir:

1. **Versión del sistema**:
```bash
php artisan about
```

2. **Logs relevantes**:
```bash
tail -100 storage/logs/laravel.log
```

3. **Pasos para reproducir**
4. **Capturas de pantalla** si aplica
5. **Configuración relevante** (sin contraseñas)

### Recursos

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de Filament](https://filamentphp.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)

---

## Checklist de Problemas

Cuando algo no funciona, verificar en orden:

- [ ] Logs de error (`storage/logs/laravel.log`)
- [ ] Caché limpia (`php artisan optimize:clear`)
- [ ] Permisos de archivos correctos
- [ ] Configuración en `.env`
- [ ] Base de datos accesible
- [ ] Migraciones al día
- [ ] Assets compilados
- [ ] Enlace de storage existe
