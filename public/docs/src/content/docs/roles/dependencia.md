---
title: Guía para Rol Dependencia
description: Guía completa para usuarios con rol Dependencia en el Sistema de Gestión de Afiliaciones ARL
---

## Tu Rol en el Sistema

Como usuario con rol **Dependencia**, eres responsable de:
- Crear las afiliaciones de los contratistas de tu secretaría/área
- Proporcionar información completa y correcta
- Corregir errores cuando una afiliación sea rechazada
- Dar seguimiento al estado de tus solicitudes

---

## Lo que Puedes Hacer

### Acciones Permitidas

| Acción | Descripción |
|--------|-------------|
| Ver afiliaciones | Solo de tu dependencia/área |
| Crear afiliaciones | Nuevos registros |
| Editar | Tus afiliaciones en estado pendiente |
| Cargar documentos | Contrato PDF/Word |
| Exportar | Tus afiliaciones a Excel |
| Buscar y filtrar | Dentro de tus registros |

### Acciones NO Permitidas

| Acción | Quién puede |
|--------|------------|
| Validar afiliaciones | Solo SSST |
| Rechazar afiliaciones | Solo SSST |
| Cargar PDF ARL | Solo SSST |
| Eliminar permanentemente | Solo Admin |
| Ver otras dependencias | Solo SSST/Admin |
| Gestionar usuarios | Solo Admin |

---

## Tu Flujo de Trabajo

```
1. Recibir información del contratista
           │
           ▼
2. Crear afiliación en el sistema
           │
           ▼
3. Cargar documento del contrato
           │
           ▼
4. Esperar validación de SSST
           │
      ┌────┴────┐
      ▼         ▼
   VALIDADO   RECHAZADO
      │         │
      ▼         ▼
   Listo    Corregir y
            esperar
```

---

## Crear una Nueva Afiliación

### Paso 1: Recopilar Información

Antes de crear la afiliación, ten a mano:

**Del Contratista:**
- Copia de documento de identidad
- Información de contacto (teléfono, email)
- Dirección de residencia
- Certificados de EPS y AFP

**Del Contrato:**
- Número de contrato
- Objeto contractual
- Valor y honorarios
- Fechas de inicio y fin
- Documento del contrato firmado

### Paso 2: Acceder al Formulario

1. Inicia sesión en el sistema
2. Ve a **Afiliaciones** en el menú
3. Click en **Crear**

### Paso 3: Completar el Formulario

#### Tab 1: Datos del Contratista

```
Nombre completo: Juan Carlos Pérez García
Tipo documento: CC
Número documento: 1234567890
Fecha nacimiento: 15/03/1990
Teléfono: 3001234567
Email: juan.perez@email.com
Dirección: Carrera 10 #20-30
Barrio: Centro
EPS: Sanitas
AFP: Porvenir
```

#### Tab 2: Información del Contrato

```
Número contrato: CON-2024-001
Dependencia: Sistemas e Informática
Área: Área de Sistemas
Objeto: Prestación de servicios profesionales...
Valor contrato: 30,000,000
Honorarios mensuales: 5,000,000
IBC: 2,000,000 (se calcula automático)
Meses: 6
Fecha inicio: 01/01/2024
Fecha fin: 30/06/2024
Contrato PDF: [Subir archivo]
```

#### Tab 3: Información ARL

```
Nombre ARL: Positiva
Nivel de riesgo: II
```

### Paso 4: Guardar

1. Revisa toda la información
2. Click en **Crear**
3. Si hay errores, corrígelos
4. La afiliación se crea en estado **Pendiente**

---

## Dar Seguimiento

### Ver Estado de tus Afiliaciones

1. Ve a **Afiliaciones**
2. Busca por nombre o contrato
3. La columna **Estado** muestra:
   - 🟡 Pendiente: Esperando validación
   - 🟢 Validado: Aprobada, puedes descargar PDF ARL
   - 🔴 Rechazado: Debes corregir

### Filtrar por Estado

1. Click en el icono de filtro
2. Selecciona **Estado**
3. Elige el estado que te interesa
4. Click en **Aplicar**

---

## Cuando te Rechazan una Afiliación

### Ver el Motivo

1. Abre la afiliación rechazada
2. Ve a la pestaña **Estado y Observaciones**
3. Lee el **Motivo de rechazo**

### Corregir y Reenviar

1. Click en **Editar**
2. Corrige los errores indicados
3. Click en **Guardar**
4. El estado vuelve a **Pendiente**
5. SSST recibe notificación para revisar

### Errores Comunes

| Error | Solución |
|-------|----------|
| "Número de documento inválido" | Verificar cédula sin puntos ni espacios |
| "Falta documento del contrato" | Subir PDF/Word del contrato firmado |
| "Nivel de riesgo incorrecto" | Consultar con SSST el nivel apropiado |
| "Fechas inconsistentes" | Verificar que fin sea posterior a inicio |

---

## Obtener el Certificado ARL

Una vez que SSST valida tu afiliación:

1. Ve a la afiliación validada
2. Abre la pestaña **Información ARL**
3. Click en el enlace del **PDF ARL**
4. Descarga y guarda el certificado

También puedes descargarlo desde la tabla:
- Columna **PDF ARL** → Click en el enlace

---

## Exportar tus Datos

### Exportar Todo

1. Click en **Exportar Todo**
2. Se descarga Excel con todas tus afiliaciones
3. Útil para reportes internos

### Exportar Seleccionados

1. Marca las casillas de las afiliaciones
2. **Acciones masivas** → **Exportar seleccionados**
3. Se descarga Excel solo con esas filas

---

## Consejos para Evitar Rechazos

### Datos del Contratista

- ✅ Verifica la cédula con el documento físico
- ✅ Usa email personal del contratista
- ✅ Dirección completa con barrio
- ✅ Teléfono celular válido

### Información del Contrato

- ✅ Número de contrato exacto
- ✅ Valor sin redondear
- ✅ Fechas según el contrato firmado
- ✅ **Siempre** adjuntar el documento del contrato

### Información ARL

- ✅ Consultar el nivel de riesgo apropiado
- ✅ Nivel I: Actividades administrativas
- ✅ Nivel II: Trabajo de campo moderado
- ✅ Nivel III-V: Actividades de mayor riesgo

---

## Dashboard para Dependencia

Tu dashboard muestra:
- Total de **tus** afiliaciones
- **Tus** pendientes de validación
- **Tus** validadas y rechazadas
- Contratos de **tu área** por vencer

:::note
Solo ves estadísticas de tu dependencia/área, no de todo el sistema.
:::

---

## Preguntas Frecuentes

### ¿Puedo editar una afiliación validada?

No. Una vez validada no se puede modificar. Contacta a SSST si hay un error grave.

### ¿Puedo ver afiliaciones de otras dependencias?

No. Solo tienes acceso a las afiliaciones de tu dependencia/área.

### ¿Cuánto tarda la validación?

Depende de la carga de trabajo de SSST. Normalmente 1-3 días hábiles.

### ¿Puedo eliminar una afiliación?

Puedes hacer soft delete. La afiliación se marca como eliminada pero puede restaurarse.

### ¿Qué hago si me equivoqué en el número de contrato?

Si está pendiente, puedes editarlo. Si ya fue validada, contacta al administrador.

---

## Soporte

Si tienes problemas:
1. Consulta esta documentación
2. Revisa la sección de [Solución de Problemas](/docs/referencia/troubleshooting/)
3. Contacta al equipo de SSST
4. Contacta al administrador del sistema

---

## Próximos Pasos

- [Gestión de Afiliaciones](/docs/usuario/afiliaciones/)
- [Importar/Exportar Excel](/docs/usuario/excel/)
