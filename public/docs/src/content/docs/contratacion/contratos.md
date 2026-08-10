---
title: Contratos y Convenios
description: Registro unificado de contratos, convenios y comodatos, enlazado a proceso, PAA y dependencia.
sidebar:
  order: 3
---

Registro **unificado** de **contratos, convenios y comodatos** mediante un campo
**Tipo**, evitando tener tres módulos separados.

## Campos

- **Tipo:** Contrato, Convenio o Comodato.
- **N° / Ítem, Fecha, Contratista, Valor.**
- **Proceso:** referencia del proceso (ej. `CD-CPS 001 DE 2026`).
- **Dependencia** y **Quién elaboró**.
- **Consecutivo PAA:** se elige por vigencia + registro del Plan (busca por
  N° Reg) y enlaza con el [Plan de Adquisiciones](/docs/usuario/plan-adquisiciones/).
- Opcionalmente puede enlazarse al **Contrato SECOP** existente sin duplicar.

## Registrar

1. Menú → **Contratación → Contratos y Convenios → Crear**.
2. Seleccione el **Tipo**, complete los datos y el **PAA** (vigencia → registro).
3. Guarde.

## Tabla y filtros

- Badges por tipo (Contrato/Convenio/Comodato) y valor en pesos.
- Filtros arriba: tipo, dependencia, elaboró.
- Búsqueda por contratista, número, proceso y PAA.

## Relación con "Contratos SECOP"

Este módulo es el **registro interno de consecutivos**; es distinto de
**Contratos SECOP** (seguimiento de vencimientos importado de SECOP). Un
registro puede apuntar a su contrato SECOP para no duplicar información.

## Importación

Ver [Importadores](/docs/contratacion/importadores/).
