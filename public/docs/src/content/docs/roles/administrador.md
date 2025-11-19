---
title: Guía para Administrador
description: Guía completa para administradores del Sistema de Gestión de Afiliaciones ARL
---

## Tu Rol en el Sistema

Como **Super Admin**, tienes control total del sistema:
- Gestión de usuarios y roles
- Configuración de dependencias y áreas
- Acceso completo a todas las afiliaciones
- Administración del sistema
- Auditoría y monitoreo

---

## Acceso Completo

### Todas las Acciones

| Módulo | Acciones |
|--------|----------|
| Afiliaciones | Crear, ver, editar, eliminar, validar, rechazar, restaurar |
| Usuarios | Crear, ver, editar, eliminar, asignar roles |
| Dependencias | Crear, ver, editar, eliminar |
| Áreas | Crear, ver, editar, eliminar |
| Roles | Ver y asignar permisos (Shield) |
| Auditoría | Ver todos los logs de actividad |

---

## Gestión de Usuarios

### Crear Usuario

1. Ve a **Usuarios** en el menú
2. Click en **Crear**
3. Completa la información:

```
Nombre: María López
Email: maria.lopez@dominio.gov.co
Correo institucional: mlopez@institucion.gov.co
Contraseña: (generar segura)
Cargo: Coordinador de Área
Dependencia: Sistemas e Informática
Área: Área de Sistemas
Rol: Dependencia
```

4. Click en **Crear**

### Asignar Roles

Los roles disponibles son:
- **super_admin**: Acceso total
- **SSST**: Valida afiliaciones
- **Dependencia**: Crea afiliaciones

### Cambiar Rol de Usuario

1. Ve a **Usuarios**
2. Edita el usuario
3. Cambia el campo **Roles**
4. Guardar

### Desactivar Usuario

Para usuarios que ya no necesitan acceso:
1. Edita el usuario
2. Marca como inactivo (si hay campo) o elimina
3. El usuario no podrá iniciar sesión

---

## Gestión de Dependencias

### Crear Dependencia

1. Ve a **Dependencias**
2. Click en **Crear**
3. Completa:

```
Nombre: Secretaría de Hacienda
Código: HAC
Descripción: Gestión financiera...
Responsable: Juan Pérez
Email: hacienda@institucion.gov.co
Teléfono: 6012345678
Activo: Sí
```

4. Click en **Crear**

### Desactivar Dependencia

Si una dependencia se elimina o reorganiza:
1. Edita la dependencia
2. Desmarca **Activo**
3. Guardar

:::note
Las afiliaciones existentes se mantienen, pero no se pueden crear nuevas para esta dependencia.
:::

---

## Gestión de Áreas

### Crear Área

1. Ve a **Áreas**
2. Click en **Crear**
3. Completa:

```
Dependencia: Secretaría de Hacienda
Nombre: Área de Presupuesto
Código: HAC-PRE
Descripción: Gestión del presupuesto...
Responsable: Pedro García
Email: presupuesto@institucion.gov.co
Activo: Sí
```

4. Click en **Crear**

### Organización Jerárquica

```
Dependencia: Secretaría de Hacienda
├── Área: Presupuesto
├── Área: Contabilidad
├── Área: Tesorería
└── Área: Impuestos
```

---

## Gestión de Permisos (Shield)

### Acceder a Shield

1. Ve a **Shield** en el menú (si está visible)
2. O usa el comando: `php artisan shield:generate --all`

### Estructura de Permisos

Cada recurso tiene permisos:
- `view_any_afiliacion`: Ver lista
- `view_afiliacion`: Ver detalle
- `create_afiliacion`: Crear
- `update_afiliacion`: Editar
- `delete_afiliacion`: Eliminar
- `force_delete_afiliacion`: Eliminar permanente
- `restore_afiliacion`: Restaurar

### Crear Rol Personalizado

Si necesitas un rol intermedio:

1. Accede a la gestión de roles
2. Crea nuevo rol
3. Asigna permisos específicos
4. Guarda

Ejemplo: Rol "Supervisor" que solo puede ver pero no editar.

---

## Auditoría del Sistema

### Ver Actividad

El sistema registra automáticamente:
- Quién realizó la acción
- Qué acción (crear, editar, eliminar)
- Cuándo (timestamp)
- Qué cambió (valores antes/después)

### Acceder a Logs

```bash
# Ver en la base de datos
php artisan tinker
>>> Activity::latest()->take(20)->get()
```

### Analizar Actividad

Buscar actividad de un usuario:
```php
Activity::causedBy($user)->get();
```

Buscar actividad en un modelo:
```php
Activity::forSubject($afiliacion)->get();
```

---

## Mantenimiento del Sistema

### Limpiar Cachés

```bash
php artisan optimize:clear
```

### Actualizar Permisos

Después de crear nuevos recursos:
```bash
php artisan shield:generate --all
```

### Respaldo de Base de Datos

```bash
mysqldump -u usuario -p gestion_arl > backup_$(date +%Y%m%d).sql
```

### Ver Logs de Errores

```bash
tail -f storage/logs/laravel.log
```

---

## Configuración del Sistema

### Archivos de Configuración

| Archivo | Propósito |
|---------|-----------|
| `.env` | Variables de entorno |
| `config/app.php` | Configuración general |
| `config/filament.php` | Panel admin |
| `config/permission.php` | Roles y permisos |
| `config/activitylog.php` | Auditoría |

### Cambiar Configuración

1. Edita el archivo correspondiente
2. Limpia caché: `php artisan config:clear`
3. En producción: `php artisan config:cache`

---

## Monitoreo

### Dashboard

Como admin ves todas las estadísticas globales:
- Total de afiliaciones
- Por estado
- Por dependencia
- Contratos por vencer

### Indicadores de Salud

| Indicador | Saludable | Requiere Atención |
|-----------|-----------|-------------------|
| Pendientes | < 20 | > 50 |
| Tasa rechazo | < 10% | > 30% |
| Errores en logs | 0 | > 10/día |

### Alertas

Configura alertas en Sentry para:
- Errores 500
- Excepciones no manejadas
- Performance issues

---

## Resolución de Problemas

### Usuario No Puede Acceder

1. Verifica que el usuario existe
2. Verifica que tiene rol asignado
3. Verifica que el rol tiene permisos
4. Limpia caché de permisos

### Afiliación Atascada

Si una afiliación tiene problemas de estado:
1. Edita directamente en la base de datos
2. O usa tinker para modificar

```bash
php artisan tinker
>>> $a = Afiliacion::find(123)
>>> $a->estado = 'pendiente'
>>> $a->save()
```

### Permisos No Funcionan

```bash
php artisan permission:cache-reset
php artisan shield:generate --all
```

### Error en Importación

1. Revisa el log de errores
2. Verifica formato del Excel
3. Verifica que las dependencias existan
4. Limpia y reintenta

---

## Tareas Periódicas

### Diario

- [ ] Revisar logs de errores
- [ ] Verificar colas de trabajo

### Semanal

- [ ] Revisar estadísticas del dashboard
- [ ] Verificar respaldos automáticos
- [ ] Revisar usuarios inactivos

### Mensual

- [ ] Analizar tendencias
- [ ] Revisar y limpiar logs antiguos
- [ ] Verificar actualizaciones de seguridad
- [ ] Generar reportes gerenciales

### Anual

- [ ] Revisar estructura de roles
- [ ] Auditar accesos
- [ ] Actualizar dependencias y áreas

---

## Comandos Útiles

```bash
# Limpiar todo
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Ver cola
php artisan queue:monitor

# Regenerar permisos
php artisan shield:generate --all

# Ver migraciones
php artisan migrate:status

# Respaldar .env
cp .env .env.backup

# Logs en tiempo real
php artisan pail
```

---

## Seguridad

### Checklist de Seguridad

- [ ] Contraseñas seguras para todos los admin
- [ ] 2FA habilitado (si disponible)
- [ ] SSL/TLS en producción
- [ ] Respaldos encriptados
- [ ] Acceso SSH restringido
- [ ] Firewall configurado
- [ ] Actualizaciones al día

### Usuarios de Prueba

**En producción, elimina o cambia:**
- admin@arl.gov.co
- sst@arl.gov.co
- dependencia@arl.gov.co

---

## Soporte Avanzado

Si necesitas ayuda técnica:
1. Revisa los logs detalladamente
2. Consulta la documentación de Laravel
3. Consulta la documentación de Filament
4. Contacta al equipo de desarrollo

---

## Próximos Pasos

- [Arquitectura del Sistema](/tecnica/arquitectura/)
- [Modelos de Datos](/tecnica/modelos/)
- [Solución de Problemas](/referencia/troubleshooting/)
