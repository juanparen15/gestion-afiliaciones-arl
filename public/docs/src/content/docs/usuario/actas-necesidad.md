---
title: Actas de Necesidad
description: Registro, aprobación, generación del PDF oficial y verificación de las Actas de Necesidad.
---

El módulo de **Actas de Necesidad** gestiona el ciclo completo del documento
oficial que respalda la necesidad de contratación: registro, aprobación,
generación del PDF con el formato de la Alcaldía, envío al solicitante y
verificación de autenticidad.

## Ciclo de vida de un acta

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Registrada, en espera de revisión. Se le asigna el N° de acta al registrar. |
| **Aprobado** | Revisada y aprobada. Se genera el PDF oficial y se envía por correo. |
| **Rechazado** | Devuelta con un motivo. |
| **Anulado** | Aprobada pero anulada posteriormente (queda registro en auditoría). |

## Registrar un acta

1. Menú lateral → **Actas de Necesidad** → botón **Crear**.
2. Se abre un asistente (wizard) por pasos:
   - **Paso 1 — Solicitud:** dependencia, área, nombre del solicitante,
     correo, objeto del contrato y nombre de la persona a contratar.
   - **Paso 2 — Condiciones:** tipo de contrato, **duración**, modalidad de
     selección, tipo de solicitud, número de contrato/convenio, presupuesto,
     código BPIN-BPIM y **Código(s) del Plan Anual de Adquisiciones (PAA)**.
   - **Observaciones** (según aplique).
3. **Guardar**. El acta queda en estado **Pendiente** con su N° asignado.

### Duración compuesta

La duración admite una parte principal y una **parte adicional opcional**:

- Si la unidad principal es **Años**, la adicional puede ser Meses o Días.
- Si es **Meses**, la adicional solo puede ser Días.
- Si es **Días**, no hay parte adicional.

Ejemplos: `4 MESES Y 15 DIAS`, `1 AÑO Y 6 MESES`, `20 DIAS`.

### Código(s) del PAA

El campo del PAA funciona en dos pasos:

1. Seleccione la **Vigencia** (año) del Plan de Adquisiciones.
2. Elija **uno o varios registros** de esa vigencia. Puede buscar por
   **N° de Registro** o por la descripción. Un acta puede llevar varios
   códigos PAA.

## Acciones sobre un acta

Las acciones disponibles dependen del estado y del permiso del usuario
(ver [permisos](#permisos)).

| Acción | Cuándo | Qué hace |
|--------|--------|----------|
| **Vista previa PDF** | Pendiente | Genera el PDF con un sello **BORRADOR** para revisarlo antes de decidir. No cambia el estado ni envía correo. |
| **Aprobar** | Pendiente | Asigna N° definitivo, genera el PDF oficial (sin sello) y lo envía por correo al solicitante. |
| **Rechazar** | Pendiente | Devuelve el acta con un motivo obligatorio. |
| **Editar** | Cualquiera (según permiso) | Corrige los datos del acta. Tras editar, use **Regenerar PDF**. |
| **Regenerar PDF** | Aprobado / Anulado | Vuelve a generar el PDF con la plantilla y firma actuales. No reenvía el correo. |
| **Descargar PDF** | Aprobado | Abre el PDF oficial. |
| **Reenviar correo** | Aprobado | Reenvía el PDF al correo del solicitante. |
| **Anular** | Aprobado | Marca el acta como anulada con un motivo (queda en auditoría). |

:::tip[Flujo recomendado para revisar]
En un acta pendiente: **Vista previa PDF** → revisa el documento con el sello
BORRADOR → **Aprobar** (el PDF final sale sin sello) o **Rechazar**.
:::

## El documento PDF

El PDF se genera a partir de la plantilla oficial en Word y se convierte a PDF,
conservando el formato de la Alcaldía. Incluye:

- Escudo y título institucional.
- Todos los datos del acta.
- La **firma del alcalde** sobre "Vo. Bo. Alcalde Municipal".
- Un **código QR** de verificación en la esquina superior derecha.

La firma y el texto bajo la firma se configuran en **Configuración de firma**
(botón en la cabecera de la tabla), donde se sube la imagen de la firma.

## Verificación de autenticidad

Cada acta aprobada tiene un **código de verificación** único. El QR del PDF
enlaza a una página pública:

```
https://tramites.ticsistemas.com.co/actas/verificar/{codigo}
```

Cualquier persona puede escanear el QR o abrir el enlace para confirmar que el
documento es auténtico y ver sus datos.

## Correo al solicitante

Al aprobar, el sistema envía automáticamente el PDF al correo del solicitante.

- Si el envío falla, el acta lo registra y aparece la acción **Reenviar correo**.
- El correo incluye el logo institucional y un botón para validar la autenticidad.

## Permisos

- Los usuarios con el toggle **"Puede aprobar actas"** activo pueden usar todas
  las acciones (Vista previa, Aprobar, Rechazar, Regenerar PDF, Reenviar correo,
  Anular y Editar), además del super administrador.
- El resto de usuarios solo puede registrar y ver, según su rol.

## Importación masiva

Las actas históricas se cargan desde el Excel de respuestas con el comando
`actas:importar-excel` (ver [Comandos](/docs/referencia/comandos/)).
