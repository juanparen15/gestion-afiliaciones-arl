# Guía de Despliegue a Producción
## Sistema de Gestión de Afiliaciones ARL

Esta guía proporciona instrucciones detalladas para desplegar los cambios más recientes del sistema a producción.

---

## Cambios Recientes Implementados

### 1. Nuevos Campos en Afiliaciones
- **Adición al Contrato**: Permite registrar si un contrato tiene una adición, con descripción, valor y fecha
- **Prórroga del Contrato**: Permite registrar prórrogas con descripción, meses/días adicionales y nueva fecha de finalización
- **Terminación Anticipada**: Permite registrar terminaciones anticipadas con fecha y motivo

### 2. Campos Obligatorios
- Email del contratista
- Meses y días del contrato
- Supervisor del contrato

### 3. Sistema de Exportación Actualizado
- Todas las exportaciones incluyen los nuevos campos
- Plantilla de importación actualizada con instrucciones

---

## Pasos para el Despliegue

### Paso 1: Preparación del Servidor

```bash
# Conectarse al servidor de producción
ssh usuario@servidor-produccion

# Navegar al directorio del proyecto
cd /ruta/del/proyecto
```

### Paso 2: Respaldar Base de Datos

```bash
# Crear respaldo de la base de datos
php artisan backup:run
# O manualmente:
mysqldump -u usuario -p nombre_base_datos > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Paso 3: Actualizar Código

```bash
# Obtener los últimos cambios del repositorio
git pull origin main

# O si usas otra rama:
git pull origin nombre-de-tu-rama
```

### Paso 4: Actualizar Dependencias

```bash
# Actualizar dependencias de Composer
composer install --no-dev --optimize-autoloader

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Paso 5: Ejecutar Migraciones

```bash
# Ejecutar las nuevas migraciones
php artisan migrate --force

# La migración agregará automáticamente las siguientes columnas:
# - tiene_adicion, descripcion_adicion, valor_adicion, fecha_adicion
# - tiene_prorroga, descripcion_prorroga, meses_prorroga, dias_prorroga, nueva_fecha_fin_prorroga
# - tiene_terminacion_anticipada, fecha_terminacion_anticipada, motivo_terminacion_anticipada
```

### Paso 6: Ejecutar Seeders (Solo si es instalación nueva)

**IMPORTANTE**: Solo ejecuta este paso si es una instalación nueva o si necesitas recrear los datos base.

```bash
# Ejecutar todos los seeders
php artisan db:seed

# O ejecutar seeders específicos:
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=DependenciaSeeder
php artisan db:seed --class=AreaSeeder
php artisan db:seed --class=UserSeeder
```

**Usuarios creados por defecto** (contraseña: `password`):
- Administrador del Sistema: `ticsistemasptoboy@gmail.com`
- Coordinador SST: `seguridadysalud@puertoboyaca-boyaca.gov.co`
- Fabian Murillo Marin: `sistemas@puertoboyaca-boyaca.gov.co`

⚠️ **IMPORTANTE**: Cambiar las contraseñas inmediatamente después del primer login.

### Paso 7: Optimizar para Producción

```bash
# Optimizar la aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizar Composer
composer dump-autoload --optimize
```

### Paso 8: Ajustar Permisos

```bash
# Ajustar permisos de directorios
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Paso 9: Verificación

1. **Verificar que el sistema esté funcionando**:
   - Acceder a la URL del sistema
   - Iniciar sesión con credenciales de administrador
   - Verificar que la nueva pestaña "Información Adicional del Contrato" aparezca en el formulario
   - Crear una afiliación de prueba con adición/prórroga
   - Exportar datos y verificar que los nuevos campos estén presentes

2. **Verificar columnas en la tabla**:
   - Las columnas "Adición", "Prórroga" y "Terminación Anticipada" deben aparecer
   - Los filtros deben funcionar correctamente

3. **Verificar exportaciones**:
   - Exportar registros seleccionados
   - Descargar plantilla de importación
   - Verificar que todos los campos estén presentes

---

## Datos de Configuración

### Dependencias Creadas
El sistema incluye 9 dependencias:
- DESPACHO
- CONTROL INTERNO
- SECRETARIA GENERAL Y DE SERVICIOS ADMINISTRATIVOS
- SECRETARIA DE GOBIERNO MUNICIPAL Y CONVIVENCIA CIUDADANA
- SECRETARIA DE DESARROLLO SOCIAL Y COMUNITARIO
- SECRETARIA DE PLANEACION MUNICIPAL
- SECRETARIA DE HACIENDA
- INSPECCIÓN DE TRANSITO Y TRANSPORTE
- UNIDAD DE ASISTENCIA TECNICA -UMATA

### Áreas Creadas
26 áreas distribuidas entre las diferentes dependencias.

### Roles del Sistema
- **super_admin**: Acceso total al sistema
- **SSST**: Coordinador de Seguridad y Salud en el Trabajo
- **Dependencia**: Usuarios de dependencias/áreas

---

## Rollback en Caso de Problemas

Si algo sale mal durante el despliegue:

```bash
# Revertir la última migración
php artisan migrate:rollback --step=1

# Restaurar base de datos desde respaldo
mysql -u usuario -p nombre_base_datos < backup_FECHA.sql

# Volver a la versión anterior del código
git reset --hard HEAD~1
# o
git checkout commit-anterior
```

---

## Solución de Problemas Comunes

### Error: "Class 'Dependencia' not found"
```bash
composer dump-autoload
php artisan cache:clear
```

### Error: "SQLSTATE[42S02]: Base table or view not found"
```bash
php artisan migrate:fresh --seed
# ⚠️ CUIDADO: Esto borrará todos los datos
```

### Error de permisos en storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Contacto y Soporte

Para cualquier problema durante el despliegue, contactar a:
- **Área de Sistemas**: sistemas@puertoboyaca-boyaca.gov.co
- **Coordinador SST**: seguridadysalud@puertoboyaca-boyaca.gov.co

---

## Checklist de Despliegue

- [ ] Respaldo de base de datos creado
- [ ] Código actualizado desde repositorio
- [ ] Dependencias de Composer instaladas
- [ ] Migraciones ejecutadas
- [ ] Seeders ejecutados (solo si aplica)
- [ ] Cachés limpiados
- [ ] Optimizaciones aplicadas
- [ ] Permisos ajustados
- [ ] Sistema verificado y funcionando
- [ ] Contraseñas de usuarios por defecto cambiadas
- [ ] Equipo notificado del despliegue exitoso

---

**Fecha de última actualización**: Diciembre 2025
**Versión**: 2.0
