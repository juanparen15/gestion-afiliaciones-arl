---
title: Procesos de Selección
description: Registro de procesos de selección por modalidad, con consecutivo automático y enlace al PAA.
sidebar:
  order: 2
---

Registra los procesos de selección por modalidad. Cada modalidad tiene su
**prefijo y numeración propia**, y el consecutivo se asigna **automáticamente**.

## Modalidades y código

| Modalidad | Código |
|-----------|--------|
| Mínima cuantía | `SMC ### DE AÑO` |
| Menor cuantía | `SAMC ### DE AÑO` |
| Subasta inversa | `SASI ### DE AÑO` |
| Concurso de méritos | `CMA ### DE AÑO` |
| Licitación pública | `LIC ### DE AÑO` |
| Licitación de obra | `LIC OBRA ### DE AÑO` |

## Registrar un proceso

1. Menú → **Contratación → Procesos de Selección → Crear**.
2. Elija la **Modalidad** y la **Vigencia** (año). El formulario muestra el
   código que se generará.
3. Deje el **Consecutivo** vacío para que se asigne automáticamente (el
   siguiente número disponible para esa modalidad y vigencia), o escríbalo si
   necesita uno específico.
4. Complete objeto, dependencia y **quién elaboró**.
5. **Consecutivo PAA:** elija primero la **vigencia del PAA** y luego el
   **registro** (busca por N° Reg). Esto enlaza el proceso con el
   [Plan de Adquisiciones](/docs/usuario/plan-adquisiciones/).

## Tabla y filtros

- Columna **Código** (ej. `SMC 014 DE 2026`).
- Filtros arriba: modalidad, dependencia, elaboró.
- Búsqueda por objeto y consecutivo PAA.

## Importación

Ver [Importadores](/docs/contratacion/importadores/) para cargar el histórico
desde el Excel de consecutivos.
