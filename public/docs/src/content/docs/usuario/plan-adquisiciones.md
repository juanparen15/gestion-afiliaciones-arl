---
title: Plan de Adquisiciones
description: Registro y consulta del Plan Anual de Adquisiciones (PAA) con clasificación UNSPSC.
---

El módulo de **Plan de Adquisiciones** administra los registros del Plan Anual
de Adquisiciones (PAA), con su clasificación de productos y servicios según el
catálogo **UNSPSC**.

## La tabla

Columnas principales:

| Columna | Descripción |
|---------|-------------|
| **N° Reg.** | Número de registro del ítem del plan. |
| **Vigencia** | Año de la vigencia (badge). |
| **Descripción** | Descripción del contrato/adquisición. |
| **Valor Estimado** | Valor estimado del ítem. |
| **Dependencia / Área** | Origen del registro. |
| **Estado Vigencia** | Estado (Vigente, Cerrada, etc.). |
| **Contratos** | Número de contratos asociados. |

### Filtros

Los filtros aparecen **sobre la tabla** para acceso rápido:

- **Vigencia (Año):** viene preseleccionada en el **año actual**. Puede
  cambiarla o limpiarla.
- **Área:** solo se muestra a usuarios que ven varias áreas. Si el usuario está
  limitado a un área, no aparece (ya solo ve la suya).
- **Estado Vigencia.**

## Registrar un ítem del plan

1. Menú lateral → **Plan de Adquisiciones** → **Crear**.
2. Complete los datos generales: dependencia, área, duración del contrato,
   valores, y las clasificaciones (tipo de adquisición, modalidad, fuente,
   mes de inicio, etc.).
3. En **Códigos de productos y servicios** agregue una o varias
   clasificaciones UNSPSC.

### Clasificación UNSPSC

La clasificación es jerárquica: **Segmento → Familia → Clase → Producto**.

- Cada nivel se filtra según el anterior.
- En cada selector se muestra **"código - descripción"**, por lo que puede
  **buscar por el código UNSPSC o por el texto**.
- El **Producto** es opcional: puede dejar la clasificación hasta la **Clase**.

Puede agregar **varias** clasificaciones a un mismo ítem con el botón de añadir.

## Relación con las Actas de Necesidad

Al registrar un [Acta de Necesidad](/docs/usuario/actas-necesidad/), el campo
**Código(s) del PAA** consulta este módulo: primero se elige la vigencia y luego
los registros del plan de esa vigencia (buscables por N° de Registro).
