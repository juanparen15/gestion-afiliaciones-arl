---
title: Validación y Rechazo
description: Proceso de validación y rechazo de afiliaciones por el equipo SSST
---

## Flujo de Validación

El proceso de validación es responsabilidad del equipo de Seguridad y Salud en el Trabajo (SSST).

```
Dependencia crea afiliación
         │
         ▼
   Estado: PENDIENTE
         │
         ▼
  SSST revisa solicitud
         │
    ┌────┴────┐
    ▼         ▼
VALIDAR   RECHAZAR
    │         │
    ▼         ▼
Cargar    Indicar
PDF ARL   motivo
    │         │
    ▼         ▼
VALIDADO  RECHAZADO
```

---

## Recibir Notificación

Cuando una dependencia crea una nueva afiliación:

1. El sistema detecta la creación
2. Identifica a todos los usuarios con rol SSST
3. Envía email de notificación

### Contenido del Email

- Asunto: "Nueva Afiliación Pendiente de Revisión - ARL"
- Nombre del contratista
- Número de contrato
- Dependencia que creó
- Enlace al sistema

:::note
Puedes acceder directamente a la afiliación desde el email.
:::

---

## Ver Afiliaciones Pendientes

### Método 1: Desde Dashboard

1. Ve al Dashboard
2. Click en el widget "Pendientes de Validación"
3. Se abre la lista filtrada automáticamente

### Método 2: Aplicar Filtro

1. Ve a **Afiliaciones**
2. Click en el icono de filtro
3. Selecciona **Estado** = Pendiente
4. Click en **Aplicar**

---

## Revisar una Afiliación

Antes de validar o rechazar, revisa toda la información:

1. Click en el icono de **ojo** (ver)
2. Verifica los datos del contratista
3. Revisa la información del contrato
4. Confirma los datos de la ARL
5. Descarga y revisa el documento del contrato (si está adjunto)

### Puntos a Verificar

- [ ] Datos personales completos y correctos
- [ ] Número de documento válido
- [ ] Información de seguridad social (EPS, AFP)
- [ ] Número de contrato correcto
- [ ] Valores del contrato coherentes
- [ ] Fechas de inicio y fin lógicas
- [ ] Nivel de riesgo apropiado para el objeto contractual
- [ ] Documento del contrato adjunto

---

## Validar una Afiliación

### Requisitos Previos

Para validar necesitas:
- Tener el **certificado PDF de la ARL** descargado del sistema de la aseguradora
- Verificar que los datos coincidan con el certificado

### Proceso de Validación

1. Encuentra la afiliación pendiente
2. Click en el icono de **check** (validar) en la fila
3. Se abre un modal de validación

### Modal de Validación

| Campo | Descripción | Requerido |
|-------|-------------|-----------|
| PDF ARL | Subir certificado de la ARL | Sí |
| Observaciones | Notas adicionales | No |

### Pasos en el Modal

1. Click en **Seleccionar archivo** o arrastra el PDF
2. El archivo debe ser PDF, máximo 10MB
3. Opcionalmente agrega observaciones
4. Click en **Validar**

### Resultado

- El estado cambia a **VALIDADO**
- Se registra la fecha de validación
- Se guarda quién validó
- El PDF queda disponible para descarga
- La dependencia puede ver el certificado

---

## Rechazar una Afiliación

### Cuándo Rechazar

Rechaza una afiliación cuando:
- Los datos son incorrectos o incompletos
- El documento del contrato no está adjunto
- Hay inconsistencias en la información
- El nivel de riesgo no corresponde
- Falta información de seguridad social

### Proceso de Rechazo

1. Encuentra la afiliación pendiente
2. Click en el icono de **X** (rechazar) en la fila
3. Se abre un modal de rechazo

### Modal de Rechazo

| Campo | Descripción | Requerido |
|-------|-------------|-----------|
| Motivo de Rechazo | Explicación clara del problema | Sí |

### Escribir el Motivo

El motivo debe ser:
- **Claro**: Que la dependencia entienda el problema
- **Específico**: Indicar qué campo o dato está mal
- **Constructivo**: Cómo corregir el error

**Ejemplos de buenos motivos:**

```
"El número de cédula tiene 11 dígitos, debe tener 10.
Por favor verificar el documento de identidad."

"Falta adjuntar el documento del contrato en formato PDF.
Este es un requisito obligatorio."

"El nivel de riesgo III no corresponde para actividades
administrativas. Debe ser nivel I o II."

"Las fechas del contrato no son coherentes: la fecha de
fin (01/01/2024) es anterior a la fecha de inicio (01/06/2024)."
```

### Resultado

- El estado cambia a **RECHAZADO**
- El motivo queda registrado
- La dependencia ve el motivo en la afiliación
- Puede corregir y esperar nueva validación

---

## Después de Rechazar

### Para la Dependencia

1. Ve su afiliación con estado **Rechazado**
2. Lee el motivo en la pestaña "Estado y Observaciones"
3. Edita la afiliación para corregir
4. Guarda los cambios
5. El estado vuelve a **Pendiente**
6. SSST recibe nueva notificación

### Para SSST

1. Espera la corrección de la dependencia
2. Recibirás notificación cuando se corrija
3. Revisa nuevamente
4. Valida o rechaza según corresponda

---

## Ver Historial de Validaciones

### En la Afiliación

1. Abre la afiliación (ver o editar)
2. Ve a la pestaña "Estado y Observaciones"
3. Verás:
   - Estado actual
   - Fecha de validación
   - Usuario que validó/rechazó
   - Observaciones
   - Motivo de rechazo (si aplica)

### Auditoría Completa

Los administradores pueden ver el historial completo de cambios en el log de actividad.

---

## Descargar PDF ARL

Una vez validada, el PDF está disponible:

### Para SSST

1. En la tabla, columna "PDF ARL"
2. Click en el enlace
3. Se descarga el archivo

### Para Dependencia

1. Abre la afiliación validada
2. En la pestaña "Información ARL"
3. Click en el enlace del PDF
4. Se descarga el certificado

---

## Validación Masiva

Actualmente no hay opción de validación masiva porque cada afiliación requiere:
- Revisión individual
- Carga de PDF específico

Si necesitas validar muchas afiliaciones, hazlo una por una verificando cada certificado.

---

## Buenas Prácticas

### Para una Validación Eficiente

1. **Revisa pendientes diariamente**: No acumules solicitudes
2. **Verifica siempre el PDF**: Que coincida con los datos
3. **Sé específico al rechazar**: Facilita la corrección
4. **Documenta observaciones**: Útil para futuras referencias

### Errores Comunes a Evitar

| Error | Consecuencia | Solución |
|-------|-------------|----------|
| Validar sin PDF | No hay respaldo | Siempre adjuntar certificado |
| Rechazar sin motivo claro | Dependencia no sabe qué corregir | Ser específico y constructivo |
| No verificar datos | Información incorrecta en el sistema | Revisar todos los campos |
| Acumular pendientes | Demoras en el proceso | Revisar diariamente |

---

## Métricas de Validación

En el Dashboard puedes ver:

- **Pendientes**: Cuántas faltan por validar
- **Validadas**: Total aprobadas
- **Rechazadas**: Total rechazadas
- **Tasa de rechazo**: Rechazadas / Total

Una tasa de rechazo alta puede indicar necesidad de capacitación a las dependencias.

---

## Próximos Pasos

- [Guía completa rol SSST](/docs/roles/ssst/)
- [Guía de Administrador](/docs/roles/administrador/)
