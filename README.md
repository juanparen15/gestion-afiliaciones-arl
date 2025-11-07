# Sistema de Gestión de Afiliaciones ARL Independientes

Sistema web completo desarrollado en **Laravel 12** con **FilamentPHP 3** para la gestión y control de afiliaciones a la ARL de contratistas por prestación de servicios profesionales y de apoyo a la gestión en entidades públicas.

## Características Principales

✅ **Gestión Completa de Afiliaciones ARL**  
✅ **Sistema de Roles**: Administrador, Dependencia, SSST  
✅ **Flujo de Validación** con estados y trazabilidad  
✅ **Carga de Archivos** (PDF/Imágenes)  
✅ **Importación/Exportación Excel**  
✅ **Dashboard con Estadísticas y Gráficas**  
✅ **Sistema de Auditoría Completo**  
✅ **Notificaciones por Correo**  
✅ **Seguridad Robusta**  

---

## Instalación Rápida

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Configurar base de datos en .env
DB_DATABASE=gestion_arl
DB_USERNAME=root
DB_PASSWORD=

# 4. Ejecutar migraciones y seeders
php artisan migrate --seed
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan shield:generate --all

# 5. Crear enlace simbólico
php artisan storage:link

# 6. Iniciar servidor
php artisan serve
```

**Acceder al panel**: http://localhost:8000/admin

---

## Usuarios Predeterminados

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@arl.gov.co | password123 |
| SSST | sst@arl.gov.co | password123 |
| Dependencia | dependencia@arl.gov.co | password123 |

⚠️ **Cambiar estas contraseñas en producción**

---

## Tecnologías

- **Laravel 12** (PHP 8.2+)
- **FilamentPHP 3** (Panel Admin)
- **MySQL 8.0+**
- **Spatie Laravel Permission** (Roles)
- **Filament Shield** (UI de permisos)
- **Maatwebsite/Laravel-Excel** (Importación/Exportación)
- **Spatie Activitylog** (Auditoría)
- **Filament Apex Charts** (Gráficas)

---

## Estructura de Base de Datos

### Tablas Principales

- **users**: Usuarios con roles y dependencias
- **dependencias**: Dependencias de la entidad
- **afiliaciones**: Registro completo de afiliaciones ARL
- **archivos_afiliaciones**: Documentos soporte
- **activity_log**: Auditoría de acciones
- **roles** / **permissions**: Sistema de permisos

---

## Uso del Sistema

### Roles y Permisos

**Administrador**
- Acceso completo al sistema
- Gestión de usuarios y dependencias
- Ver auditoría completa

**Dependencia**
- Crear y editar afiliaciones propias
- Ver afiliaciones de su dependencia
- Cargar documentos soporte

**SSST (Seguridad y Salud)**
- Ver todas las afiliaciones
- Validar o rechazar afiliaciones
- Agregar observaciones
- Generar reportes

### Flujo de Trabajo

1. **Dependencia** registra nueva afiliación (Estado: Pendiente)
2. **SSST** revisa la información
3. **SSST** valida o rechaza:
   - ✅ **Validado**: Registra fecha y validador
   - ❌ **Rechazado**: Requiere motivo
4. **Dependencia** recibe notificación por correo

---

## Módulos del Sistema

### 1. Gestión de Dependencias
- CRUD completo de dependencias
- Asignación de responsables
- Activar/desactivar

### 2. Gestión de Afiliaciones
- Información del contratista
- Datos del contrato
- Información ARL
- Carga de archivos
- Estados de validación

### 3. Dashboard
- Estadísticas por dependencia
- Gráficas de estados
- Contratos próximos a vencer
- Afiliaciones pendientes

### 4. Auditoría
- Registro de todas las acciones
- Trazabilidad completa
- Filtros avanzados

---

## Importación/Exportación Excel

### Exportar
1. Ir a módulo "Afiliaciones"
2. Clic en botón "Exportar"
3. Seleccionar campos
4. Descargar archivo Excel

### Importar
1. Preparar archivo Excel con formato oficial
2. Clic en "Importar"
3. Seleccionar archivo
4. Sistema valida y procesa

---

## Seguridad

- Autenticación robusta con Laravel
- Sistema de roles y permisos granular
- Validación de datos en backend y frontend
- Protección CSRF y XSS
- Carga segura de archivos
- Contraseñas encriptadas (Bcrypt)
- HTTPS recomendado en producción

---

## Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan optimize

# Actualizar permisos Shield
php artisan shield:generate --all

# Ver logs
php artisan tail
```

---

## Configuración Adicional

### Correo Electrónico

Editar `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=correo@entidad.gov.co
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@entidad.gov.co"
```

### Base de Datos

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_arl
DB_USERNAME=root
DB_PASSWORD=
```

---

## Solución de Problemas

### Error de permisos
```bash
chmod -R 775 storage bootstrap/cache
```

### Regenerar caché
```bash
php artisan optimize:clear
php artisan optimize
```

### Resetear base de datos
```bash
php artisan migrate:fresh --seed
```

---

## Documentación Técnica

### Estructura de Archivos

```
app/
├── Filament/
│   └── Resources/
│       ├── DependenciaResource.php
│       └── AfiliacionResource.php
├── Models/
│   ├── User.php
│   ├── Dependencia.php
│   ├── Afiliacion.php
│   └── ArchivoAfiliacion.php
└── Policies/

database/
├── migrations/
└── seeders/
    └── RolesAndPermissionsSeeder.php
```

### Modelos y Relaciones

- **User** → belongsTo → **Dependencia**
- **Afiliacion** → belongsTo → **Dependencia**
- **Afiliacion** → belongsTo → **User** (creador/validador)
- **Afiliacion** → hasMany → **ArchivoAfiliacion**

---

## Despliegue en Producción

1. Configurar servidor (Apache/Nginx)
2. Instalar SSL/HTTPS
3. Configurar variables de entorno
4. Optimizar aplicación:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
5. Configurar cron para tareas programadas
6. Configurar backup automático de BD

---

## Soporte

Para reportar problemas o sugerencias, contactar al equipo de desarrollo.

---

## Licencia

Sistema propietario de uso interno exclusivo de la entidad pública.

---

**Desarrollado con Laravel 12 + FilamentPHP 3**  
**Versión**: 1.0.0  
**Fecha**: Octubre 2025

**¡Sistema Listo para Producción!**

---

## Importación de Excel - Guía Detallada

### Formato del Archivo Excel

El archivo Excel debe contener las siguientes columnas **en la primera fila** (exactamente como se muestran):

| Columna | Nombre del Header | Tipo de Dato | Ejemplo |
|---------|-------------------|--------------|---------|
| A | No. CONTRATO | Texto | 19 |
| B | OBJETO CONTRATO | Texto | PRESTACIÓN DE SERVICIOS... |
| C | CC CONTRATISTA | Número | 12345678 |
| D | CONTRATISTA | Texto | JUAN PABLO RENDON |
| E | VALOR DEL CONTRATO | Moneda | $18.600.000,00 |
| F | MESES | Número | 138 |
| G | DIAS | Número | 0 |
| H | Honorarios mensual | Moneda | $4.650.000,00 |
| I | IBC | Moneda | $1.860.000,00 |
| J | Fecha ingreso A partir de Acta inicio | Fecha | 28-ene-2025 |
| K | Fecha retiro | Fecha | 14-jun-2025 |
| L | Secretaría | Texto | General |
| M | Fecha de Nacimiento | Fecha | 12-oct-1970 |
| N | Nivel de riesgo | Número | 4 |
| O | No. Celular | Texto | 3131234567 |
| P | Barrio | Texto | Villatex |
| Q | Dirección Residencia | Texto | Calle 20b No. 3-04 |
| R | EPS | Texto | SURA |
| S | AFP | Texto | PROTECCION |
| T | Dirección de correo Electronica | Email | jprendon11@gmail.com |
| U | FECHA DE AFILIACION | Fecha | 28/01/2025 |
| V | FECHA TERMIANCION AFILIACION | Fecha | 27/07/2025 |

### Instrucciones de Uso

#### 1. Preparar el Archivo Excel

1. Abrir Excel o Google Sheets
2. En la **primera fila**, escribir exactamente los nombres de las columnas como se muestran arriba
3. A partir de la **segunda fila**, comenzar a ingresar los datos

#### 2. Formatos de Datos

**Fechas**: Se aceptan los siguientes formatos:
- `28/01/2025`
- `28-01-2025`
- `28-ene-2025`
- `2025-01-28`

**Valores Monetarios**: Se aceptan:
- `$18.600.000,00`
- `18600000`
- `18.600.000`

**Nivel de Riesgo**: 
- Número del 1 al 5
- Se convertirá automáticamente a romano (I, II, III, IV, V)

**Secretaría/Dependencia**:
- Escribir el nombre de la dependencia
- El sistema buscará coincidencias automáticamente

#### 3. Importar desde Filament

1. Iniciar sesión en el panel de administración
2. Ir a **Afiliaciones**
3. Hacer clic en el botón **"Importar Excel"** (verde, arriba a la derecha)
4. Seleccionar el archivo Excel (.xlsx, .xls o .csv)
5. Hacer clic en **"Importar"**
6. Esperar a que se procese el archivo
7. El sistema mostrará una notificación con el resultado

#### 4. Validaciones Automáticas

El sistema validará:
- ✅ Campos obligatorios (No. Contrato, CC, Nombre)
- ✅ Formato de fechas
- ✅ Formato de valores monetarios
- ✅ Existencia de la dependencia
- ✅ Formato de email

#### 5. Manejo de Errores

Si hay errores durante la importación:
- Se mostrará una notificación con los primeros 3 errores
- Los registros correctos se importarán
- Los registros con errores se omitirán
- Se indicará el número de fila con error

### Exportación de Datos

Para **exportar** las afiliaciones existentes:

1. Ir a **Afiliaciones**
2. Seleccionar los registros a exportar (checkboxes)
3. Hacer clic en **"Exportar"** en el menú de acciones masivas
4. El archivo se descargará con el formato compatible para importación

---

## Funcionalidades Adicionales

### Botón de Importación

El botón **"Importar Excel"** aparece en la parte superior derecha de la tabla de afiliaciones con:
- 🟢 Color verde
- 📤 Ícono de carga
- Modal de selección de archivo
- Validación de tipos de archivo
- Límite de 10MB por archivo

### Botón de Exportación

El botón **"Exportar"** aparece al seleccionar registros con:
- Formato Excel compatible con importación
- Todas las columnas necesarias
- Nombres de archivo con fecha y hora
- Formato `.xlsx`

---

## Solución de Problemas en Importación

### Error: "No se pudo leer el archivo"
**Solución**: Verificar que el archivo sea .xlsx, .xls o .csv válido

### Error: "Faltan columnas requeridas"
**Solución**: Verificar que los nombres de las columnas sean exactos (copiar y pegar)

### Error: "Fecha inválida"
**Solución**: Usar formato dd/mm/yyyy o dd-mm-yyyy

### Error: "Dependencia no encontrada"
**Solución**: 
1. Crear la dependencia primero en el módulo "Dependencias"
2. O usar un nombre existente en la columna "Secretaría"

### Advertencia: "Algunos registros no se importaron"
**Solución**: 
1. Ver la notificación con los errores específicos
2. Corregir las filas indicadas
3. Volver a importar solo esas filas

---

## Buenas Prácticas

1. **Antes de Importar**:
   - Crear todas las dependencias necesarias
   - Verificar el formato del archivo
   - Hacer una prueba con 1-2 registros primero

2. **Durante la Importación**:
   - No cerrar el navegador mientras se procesa
   - Esperar a que aparezca la notificación de resultado
   - Verificar el número de registros importados

3. **Después de Importar**:
   - Revisar los registros importados
   - Verificar que los datos sean correctos
   - Validar las afiliaciones según el flujo SSST

---
