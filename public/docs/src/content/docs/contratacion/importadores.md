---
title: Importadores y vinculación
description: Comandos para importar procesos, contratos y pólizas desde Excel y vincularlos al PAA.
sidebar:
  order: 5
---

Los tres módulos se pueden cargar desde los Excel de consecutivos. Cada comando
admite `--dry-run` (solo reporta) y `--fresh` (borra antes de importar).

## Requisito: vigencia del PAA

Para que el enlace al Plan de Adquisiciones funcione, los planes deben tener su
**vigencia** correcta. El PAA de una vigencia se registra desde **diciembre del
año anterior**. Reclasificación típica de la vigencia 2026:

```bash
php artisan tinker --execute="echo DB::update(\"UPDATE planadquisiciones SET vigencia=2026 WHERE created_at >= '2025-12-01'\");"
```

## Importar

```bash
# Procesos de selección (hojas por modalidad)
php artisan procesos:importar-excel  "/ruta/Consecutivos Procesos Seleccion 2026.xlsx" --dry-run
php artisan procesos:importar-excel  "/ruta/Consecutivos Procesos Seleccion 2026.xlsx"

# Contratos, convenios y comodatos (3 hojas)
php artisan contratos:importar-excel "/ruta/Consecutivos Contratos y demas 2026.xlsx"

# Aprobación de pólizas
php artisan polizas:importar-excel   "/ruta/Aprobacion Polizas 2026.xlsx"
```

## Re-vincular el PAA

Si se corrige la vigencia de los planes después de importar, re-enlaza los
procesos a su registro del PAA:

```bash
php artisan procesos:revincular-paa --dry-run
php artisan procesos:revincular-paa
```

## Notas

- La dependencia se resuelve por nombre (con alias y sin acentos); si no
  coincide, se guarda como texto.
- El "quién elaboró/aprobó" se crea automáticamente en el
  [Equipo de Contratación](/docs/contratacion/introduccion/) si no existe.
