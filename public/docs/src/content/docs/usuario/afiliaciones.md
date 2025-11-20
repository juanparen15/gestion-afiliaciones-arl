---
title: Gestión de Afiliaciones
description: Guía completa para gestionar afiliaciones en el sistema
---

## Vista de Lista

### Acceder a Afiliaciones

1. Click en **Afiliaciones** en el menú lateral
2. Se muestra la tabla con todas las afiliaciones visibles para tu rol

### Columnas de la Tabla

| Columna | Descripción |
|---------|-------------|
| No. Contrato | Número único del contrato |
| Contratista | Nombre completo |
| Documento | Tipo y número |
| Dependencia | Secretaría asignada |
| Área | Área dentro de la dependencia |
| Valor | Valor total del contrato |
| Inicio | Fecha de inicio |
| Fin | Fecha de finalización |
| Riesgo | Nivel de riesgo ARL (I-V) |
| Estado | Pendiente/Validado/Rechazado |
| PDF ARL | Enlace al certificado |
| Creado por | Usuario que registró |
| Fecha | Cuándo se creó |

---

## Crear Nueva Afiliación

### Abrir Formulario

Click en el botón **Crear** (esquina superior derecha de la tabla)

### Tab 1: Datos del Contratista

#### Información Personal
- **Nombre completo** (requerido): Nombres y apellidos
- **Tipo de documento** (requerido):
  - CC (Cédula de Ciudadanía)
  - CE (Cédula de Extranjería)
  - PP (Pasaporte)
  - TI (Tarjeta de Identidad)
- **Número de documento** (requerido): Sin puntos ni espacios
- **Fecha de nacimiento**: Seleccionar del calendario
- **Teléfono**: Número de contacto
- **Email**: Correo electrónico válido

#### Dirección
- **Dirección de residencia**: Dirección completa
- **Barrio**: Nombre del barrio o localidad

#### Seguridad Social
- **EPS**: Nombre de la entidad de salud
- **AFP**: Nombre del fondo de pensiones

### Tab 2: Información del Contrato

#### Datos del Contrato
- **Número de contrato** (requerido): Identificador único
- **Dependencia/Secretaría** (requerido): Seleccionar de la lista
- **Área**: Se filtra según la dependencia seleccionada
- **Objeto contractual** (requerido): Descripción del contrato
- **Supervisor**: Nombre del supervisor

#### Valores y Duración
- **Valor total del contrato** (requerido): Monto en pesos
- **Honorarios mensuales** (requerido): Monto mensual
- **IBC**: Se calcula automáticamente (40% de honorarios)
- **Meses de contrato**: Duración en meses
- **Días de contrato**: Días adicionales
- **Fecha de inicio** (requerido): Cuándo inicia
- **Fecha de fin** (requerido): Cuándo termina
- **Contrato PDF/Word**: Subir archivo (máx 10MB)

### Tab 3: Información ARL

- **Nombre de la ARL**: Administradora seleccionada
- **Nivel de riesgo**: I, II, III, IV o V
- **Número de afiliación ARL**: Código de la afiliación
- **Fecha de afiliación**: Cuándo se afilió
- **Fecha de terminación**: Cuándo termina la cobertura
- **PDF ARL**: Solo visible y editable por SSST al validar

### Tab 4: Estado y Observaciones

- **Estado**: Solo editable por SSST/Admin
  - Pendiente (por defecto)
  - Validado
  - Rechazado
- **Observaciones**: Notas adicionales
- **Motivo de rechazo**: Solo si está rechazado

### Guardar

1. Revisa toda la información
2. Click en **Crear**
3. El sistema valida los datos
4. Si hay errores, se muestran en rojo
5. Si todo está bien, se guarda y redirige a la lista

---

## Editar Afiliación

### Acceder a Edición

1. Encuentra la afiliación en la tabla
2. Click en el icono de **lápiz** (editar)

### Restricciones de Edición

| Tu Rol | Puedes Editar |
|--------|--------------|
| Dependencia | Solo tus afiliaciones pendientes |
| SSST | Todas las afiliaciones |
| Admin | Todas las afiliaciones |

### Campos Bloqueados

Algunos campos no se pueden editar después de crear:
- Número de documento (si ya fue validado)

---

## Ver Detalles

1. Click en el icono de **ojo** (ver)
2. Se abre vista de solo lectura
3. Muestra todos los campos organizados por tabs

---

## Estados de Afiliación

### Pendiente

- **Color**: Amarillo
- **Significado**: Recién creada, esperando validación
- **Quién puede cambiar**: SSST al validar o rechazar

### Validado

- **Color**: Verde
- **Significado**: Aprobada, tiene PDF ARL cargado
- **Requisitos**: SSST debe cargar el certificado

### Rechazado

- **Color**: Rojo
- **Significado**: No aprobada, requiere correcciones
- **Información**: Incluye motivo del rechazo

---

## Búsqueda y Filtrado

### Búsqueda Rápida

Escribe en el campo de búsqueda para encontrar por:
- Nombre del contratista
- Número de documento
- Número de contrato
- Email

### Filtros Disponibles

| Filtro | Opciones |
|--------|----------|
| Estado | Pendiente, Validado, Rechazado |
| Dependencia | Lista de dependencias |
| Área | Áreas de la dependencia |
| Nivel de Riesgo | I, II, III, IV, V |
| Contratos Vigentes | Fecha fin >= hoy |
| Por Vencer | Próximos 30 días |
| Eliminados | Mostrar soft deletes |

### Aplicar Filtros

1. Click en el icono de filtro
2. Selecciona los criterios
3. Click en **Aplicar filtros**
4. La tabla se actualiza

### Quitar Filtros

- Click en la "X" junto a cada filtro
- O click en **Limpiar filtros**

---

## Acciones de Fila

En cada fila de la tabla tienes estas acciones:

| Icono | Acción | Descripción |
|-------|--------|-------------|
| Ojo | Ver | Vista de solo lectura |
| Lápiz | Editar | Modificar campos |
| Check | Validar | Solo SSST - Aprobar |
| X | Rechazar | Solo SSST - Rechazar |
| Restaurar | Restaurar | Solo si está eliminado |
| Basura | Eliminar | Soft delete |

---

## Acciones Masivas

### Seleccionar Múltiples

1. Marca las casillas de las filas deseadas
2. O usa "Seleccionar todo" en el encabezado

### Acciones Disponibles

- **Exportar seleccionados**: Descargar Excel
- **Eliminar seleccionados**: Soft delete masivo
- **Restaurar seleccionados**: Si están eliminados

---

## Ordenar Tabla

1. Click en el encabezado de cualquier columna
2. Flecha arriba = Ascendente (A-Z, 0-9)
3. Flecha abajo = Descendente (Z-A, 9-0)
4. Click de nuevo para cambiar dirección

---

## Paginación

En la parte inferior de la tabla:

- **Selector de cantidad**: 10, 25, 50, 100 filas
- **Navegación**: Primera, anterior, siguiente, última
- **Indicador**: "Mostrando X-Y de Z resultados"

---

## Eliminar Afiliación

### Soft Delete (Eliminación Lógica)

1. Click en el icono de basura
2. Confirma la acción
3. El registro se marca como eliminado
4. Puede ser restaurado

### Ver Eliminados

1. Aplica el filtro "Registros eliminados"
2. Los registros aparecen con estilo diferente

### Restaurar

1. Filtra por eliminados
2. Click en el icono de restaurar
3. Confirma la acción

### Eliminar Permanentemente

Solo disponible para SSST y Admin:
1. Filtra por eliminados
2. Click en "Eliminar permanentemente"
3. Confirma la acción
4. **No se puede deshacer**

---

## Validaciones del Formulario

El sistema valida automáticamente:

| Campo | Validación |
|-------|------------|
| Número documento | Único en el sistema |
| Email | Formato válido |
| Fechas | Fin debe ser posterior a inicio |
| Valores | Números positivos |
| Archivos | Máximo 10MB, formatos permitidos |

Si hay errores:
- El campo se marca en rojo
- Se muestra mensaje de error debajo
- No se puede guardar hasta corregir

---

## Próximos Pasos

- [Importar/Exportar Excel](/docs/usuario/excel/)
- [Validación y Rechazo](/docs/usuario/validacion/)
