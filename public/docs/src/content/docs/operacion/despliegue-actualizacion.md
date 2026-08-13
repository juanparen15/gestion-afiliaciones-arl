---
title: Despliegue y actualización
description: Cómo desplegar y actualizar el aplicativo en el servidor de producción.
sidebar:
  order: 1
---

Guía operativa para el servidor de producción de la Alcaldía.

## Datos del entorno

| Elemento | Valor |
|----------|-------|
| Ruta del proyecto | `/var/www/html/gestion-afiliaciones-arl` |
| Servidor web | Nginx/Apache + PHP-FPM 8.3 |
| Dominio | `https://tramites.ticsistemas.com.co` (detrás de Cloudflare) |
| Base de datos | MySQL |
| Usuario del sistema | `www-data` |

## Actualizar a la última versión

Desde la carpeta del proyecto:

```bash
cd /var/www/html/gestion-afiliaciones-arl

# 1. Traer el código
git pull

# 2. Dependencias (solo si cambió composer.json/lock)
composer install --no-dev --optimize-autoloader

# 3. Migraciones (solo si hay nuevas)
php artisan migrate --force

# 4. Limpiar y recompilar cachés
php artisan optimize:clear
php artisan config:cache

# 5. Recargar PHP para que tome el código nuevo (elige el que aplique)
sudo systemctl reload php8.3-fpm
```

:::tip
Si un cambio solo toca vistas/documentación (no PHP), suele bastar con
`git pull && php artisan optimize:clear`.
:::

## Permisos de archivos

Si aparecen errores de escritura (`Permission denied` en `storage/` o
`bootstrap/cache/`):

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
```

## La documentación (/docs)

El sitio de documentación es un proyecto Astro/Starlight cuyo **build ya está
versionado** en `public/docs`. En el servidor **no** se compila: basta con
`git pull`.

Para regenerarla tras editar el contenido (en local):

```bash
cd public/docs
npm install        # solo la primera vez
npm run build      # genera en ../docs-build
cp -r ../docs-build/* .   # publica en public/docs (lo que sirve /docs)
```

## Caché de Cloudflare

Los PDF y assets pueden quedar cacheados en Cloudflare. Las URLs de PDF ya
incluyen `?t=` para evitarlo. Si tras un cambio de imagen/documento se sigue
viendo lo viejo: **Cloudflare → Caching → Purge Everything**.
