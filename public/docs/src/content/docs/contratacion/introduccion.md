---
title: Ciclo de Contratación
description: Visión general del módulo de Contratación y cómo se enlazan sus etapas.
sidebar:
  order: 1
---

El módulo de **Contratación** gestiona el ciclo completo, tomando como base el
**Plan de Adquisiciones (PAA)** ya existente y encadenando cada etapa:

```
Plan de Adquisiciones (PAA)
   └─ Proceso de Selección
        └─ Contrato / Convenio / Comodato
             └─ Aprobación de Pólizas
```

Todas las etapas comparten los mismos enlaces: **dependencia**, **elaborador**
(quién elaboró/aprobó) y el **consecutivo del PAA** (formato `AÑO-N° Reg`).

## Componentes

| Módulo | Para qué sirve |
|--------|----------------|
| **Equipo de Contratación** | Catálogo de personas que elaboran/aprueban. |
| **Procesos de Selección** | Registro por modalidad (mínima/menor cuantía, subasta, concurso, licitación), enlazado al PAA. |
| **Contratos y Convenios** | Registro unificado de contratos, convenios y comodatos (campo *Tipo*), enlazado a proceso y PAA. |
| **Aprobación de Pólizas** | Aprobaciones de pólizas por contrato (estado: inicio, adición, prórroga, final…). |
| **Dashboard** | Indicadores: valor contratado por dependencia, pólizas por estado, procesos por modalidad. |

## El enlace con el Plan de Adquisiciones

El **consecutivo del PAA** (`2026-221` = vigencia 2026, N° Reg 221) conecta cada
proceso/contrato con su registro del Plan. Para que el vínculo funcione, el Plan
debe tener bien definida su **vigencia** (campo propio, ver
[Plan de Adquisiciones](/docs/usuario/plan-adquisiciones/)).

- El PAA de una vigencia se registra desde **diciembre del año anterior**
  (ej. la vigencia 2026 se empieza a cargar en diciembre de 2025).
- Si un proceso/contrato referencia un PAA que aún no está en el sistema, el
  código se guarda como **texto** y se puede re-vincular después.

## Roles

Los datos de contratación son visibles para el equipo con acceso al panel. Las
dependencias y áreas se resuelven automáticamente por nombre al importar.
