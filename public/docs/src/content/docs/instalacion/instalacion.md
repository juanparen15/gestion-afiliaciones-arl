---
title: Instalación
description: Guía paso a paso para instalar el Sistema de Gestión de Afiliaciones ARL
---

## Obtener el Código Fuente

### Opción 1: Clonar desde Git

```bash
# Clonar repositorio
git clone https://github.com/tu-organizacion/gestion-afiliaciones-arl.git

# Entrar al directorio
cd gestion-afiliaciones-arl
```

### Opción 2: Descargar ZIP

1. Descarga el archivo ZIP desde el repositorio
2. Extrae el contenido en tu directorio de proyectos
3. Renombra la carpeta si es necesario

---

## Instalar Dependencias

### Dependencias de PHP (Composer)

```bash
# Instalar dependencias de producción
composer install --optimize-autoloader --no-dev

# Para desarrollo (incluye herramientas de debug)
composer install
```

### Dependencias de JavaScript (NPM)

```bash
# Instalar dependencias
npm install

# Compilar assets para desarrollo
npm run dev

# Compilar assets para producción
npm run build
```

---

## Configuración del Entorno

### Crear archivo .env

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### Configurar Variables de Entorno

Edita el archivo `.env`:

```env
# Aplicación
APP_NAME="Gestión ARL"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://localhost

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_arl
DB_USERNAME=root
DB_PASSWORD=

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@arl.gov.co"
MAIL_FROM_NAME="${APP_NAME}"

# Zona Horaria
APP_TIMEZONE=America/Bogota

# Configuración de Sesión
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuración de Caché
CACHE_DRIVER=file

# Configuración de Cola
QUEUE_CONNECTION=sync
```

---

## Configurar Base de Datos

### Ejecutar Migraciones

```bash
# Crear todas las tablas
php artisan migrate

# Si necesitas recrear desde cero
php artisan migrate:fresh
```

### Ejecutar Seeders

```bash
# Cargar datos iniciales (roles, permisos, usuarios de prueba)
php artisan db:seed

# O específicamente el seeder de roles
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Generar Permisos de Shield

```bash
# Generar todos los permisos automáticamente
php artisan shield:generate --all
```

---

## Crear Enlace Simbólico de Storage

```bash
# Crear enlace para acceso público a archivos
php artisan storage:link
```

Esto crea un enlace de `public/storage` a `storage/app/public`.

---

## Usuarios de Prueba

Después de ejecutar los seeders, tendrás estos usuarios disponibles:

| Rol | Email | Contraseña |
|-----|-------|------------|
| Super Admin | admin@arl.gov.co | password123 |
| SSST | sst@arl.gov.co | password123 |
| Dependencia | dependencia@arl.gov.co | password123 |

:::caution[Importante]
Cambia las contraseñas por defecto en producción.
:::

---

## Limpiar Cachés

```bash
# Limpiar todos los cachés
php artisan optimize:clear

# O individualmente
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Iniciar el Servidor de Desarrollo

### Opción 1: Servidor de Laravel

```bash
php artisan serve
```

Accede a: `http://localhost:8000`

### Opción 2: Laragon (Windows)

1. Coloca el proyecto en `C:\laragon\www\`
2. Inicia Laragon
3. Accede a: `http://gestion-afiliaciones-arl.test`

### Opción 3: Valet (macOS)

```bash
# Desde el directorio del proyecto
valet link arl
```

Accede a: `http://arl.test`

---

## Verificar Instalación

### 1. Acceder al Sistema

Navega a la URL de tu instalación y verifica:
- [ ] Página de login se muestra correctamente
- [ ] Puedes iniciar sesión con los usuarios de prueba
- [ ] El dashboard carga sin errores

### 2. Verificar Funcionalidades

- [ ] Crear una nueva afiliación
- [ ] Ver la lista de afiliaciones
- [ ] Los filtros funcionan
- [ ] La importación de Excel funciona
- [ ] Las notificaciones por email se envían

### 3. Verificar Permisos

- [ ] Usuario Dependencia solo ve sus afiliaciones
- [ ] Usuario SSST puede validar/rechazar
- [ ] Admin tiene acceso completo

---

## Solución de Problemas Comunes

### Error: "SQLSTATE[HY000] [1045] Access denied"

La contraseña de MySQL es incorrecta. Verifica en `.env`:
```env
DB_PASSWORD=tu_contraseña_correcta
```

### Error: "The Mix manifest does not exist"

Compila los assets:
```bash
npm run dev
```

### Error: "Permission denied" en storage/

```bash
# Linux/macOS
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Error: "Class not found"

Regenera el autoloader:
```bash
composer dump-autoload
```

### Imágenes/CSS no cargan

Verifica el enlace de storage:
```bash
php artisan storage:link
```

---

## Siguiente Paso

Continúa con la [Configuración](/instalacion/configuracion/) para personalizar el sistema.
