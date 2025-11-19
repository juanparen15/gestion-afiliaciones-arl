---
title: Características
description: Características completas del Sistema de Gestión de Afiliaciones ARL
---

## Gestión de Afiliaciones

### Registro Completo de Información

El sistema permite registrar toda la información necesaria de los contratistas:

#### Datos Personales
- Nombre completo del contratista
- Tipo y número de documento (CC, CE, PP, TI)
- Fecha de nacimiento
- Teléfono y email de contacto
- Dirección y barrio de residencia

#### Información de Seguridad Social
- EPS (Entidad Promotora de Salud)
- AFP (Administradora de Fondos de Pensiones)

#### Datos del Contrato
- Número de contrato
- Objeto contractual
- Valor total del contrato
- Honorarios mensuales
- IBC (Ingreso Base de Cotización) - calculado automáticamente al 40%
- Fechas de inicio y fin
- Dependencia y área asignada
- Supervisor del contrato

#### Información ARL
- Nombre de la ARL
- Nivel de riesgo (I a V)
- Número de afiliación
- Fechas de afiliación y terminación
- Certificado PDF de la ARL

### Estados de Afiliación

Cada afiliación puede tener uno de los siguientes estados:

| Estado | Descripción | Acción Siguiente |
|--------|-------------|------------------|
| **Pendiente** | Recién creada, esperando validación | SSST debe revisar |
| **Validado** | Aprobada por SSST con PDF cargado | Proceso completado |
| **Rechazado** | Rechazada con motivo especificado | Dependencia debe corregir |

---

## Importación y Exportación Excel

### Importación Masiva

Permite cargar múltiples afiliaciones desde un archivo Excel:

- **Plantilla descargable**: Formato predefinido con todas las columnas necesarias
- **Validación automática**: Verifica datos obligatorios, formatos y duplicados
- **Reporte de errores**: Genera archivo Excel con los errores encontrados por fila
- **Actualización inteligente**: Si el documento ya existe, actualiza en lugar de duplicar

### Columnas del Excel de Importación

```
no_contrato, objeto_contrato, cc_contratista, contratista,
valor_del_contrato, meses, dias, honorarios_mensual, ibc,
fecha_ingreso_a_partir_de_acta_inicio, fecha_retiro,
secretaria, area, fecha_de_nacimiento, nivel_de_riesgo,
no_celular, barrio, direccion_residencia, eps, afp,
direccion_de_correo_electronica, fecha_de_afiliacion,
fecha_terminacion_afiliacion
```

### Exportación

- **Exportar todo**: Descarga todas las afiliaciones en formato Excel
- **Exportar seleccionados**: Solo las filas marcadas en la tabla
- **Formato profesional**: Con estilos, encabezados y columnas ajustadas

---

## Sistema de Roles y Permisos

### Rol: Dependencia

**Permisos:**
- Crear nuevas afiliaciones
- Editar sus propias afiliaciones (estado pendiente)
- Ver afiliaciones de su dependencia/área
- Cargar documentos del contrato

**Restricciones:**
- No puede validar ni rechazar
- No puede ver afiliaciones de otras dependencias
- No puede eliminar permanentemente

### Rol: SSST (Seguridad y Salud en el Trabajo)

**Permisos:**
- Ver todas las afiliaciones del sistema
- Validar afiliaciones (cargar PDF ARL)
- Rechazar afiliaciones (con motivo)
- Agregar observaciones
- Importar y exportar Excel
- Acceso a reportes y estadísticas

**Funciones especiales:**
- Recibe notificaciones por email de nuevas afiliaciones
- Puede restaurar registros eliminados

### Rol: Super Admin

**Permisos:**
- Acceso total al sistema
- Gestión de usuarios y roles
- Configuración de dependencias y áreas
- Eliminar permanentemente registros
- Acceso a auditoría completa

---

## Dashboard y Estadísticas

### Widgets de Estadísticas

El panel de control muestra:

1. **Total de Afiliaciones**: Contador general con gráfico de tendencia
2. **Pendientes de Validación**: Afiliaciones esperando revisión (alerta)
3. **Afiliaciones Validadas**: Total de aprobadas
4. **Afiliaciones Rechazadas**: Total de rechazadas
5. **Contratos Vigentes**: Contratos activos actualmente
6. **Por Vencer (30 días)**: Contratos próximos a terminar (alerta)

### Gráficos

- **Distribución por Estado**: Gráfico tipo dona (Pendiente/Validado/Rechazado)
- **Por Dependencia**: Cantidad de afiliaciones por secretaría
- **Próximos a Vencer**: Lista de contratos que vencen pronto

---

## Notificaciones Automáticas

### Notificación por Email

Cuando se crea una nueva afiliación:

1. El sistema detecta el evento de creación
2. Identifica a todos los usuarios con rol SSST
3. Envía un email con los datos de la afiliación
4. El SSST puede acceder directamente desde el email

**Contenido del email:**
- Nombre del contratista
- Número de contrato
- Dependencia
- Enlace directo al sistema

---

## Auditoría y Trazabilidad

### Registro de Actividades

El sistema registra automáticamente:

- **Quién**: Usuario que realizó la acción
- **Qué**: Tipo de acción (crear, editar, eliminar, validar)
- **Cuándo**: Fecha y hora exacta
- **Dónde**: Modelo y registro afectado
- **Cambios**: Valores anteriores y nuevos

### Campos de Trazabilidad

Cada afiliación incluye:
- `created_by`: Usuario que la creó
- `validated_by`: Usuario que la validó/rechazó
- `fecha_validacion`: Cuándo se validó
- `created_at` / `updated_at`: Timestamps automáticos
- `deleted_at`: Soft delete (eliminación lógica)

---

## Búsqueda y Filtrado

### Búsqueda Global

Busca en todos los campos principales:
- Nombre del contratista
- Número de documento
- Número de contrato
- Email

### Filtros Disponibles

| Filtro | Opciones |
|--------|----------|
| Estado | Pendiente, Validado, Rechazado |
| Dependencia | Lista de dependencias |
| Área | Áreas según dependencia |
| Nivel de Riesgo | I, II, III, IV, V |
| Contratos Vigentes | Sí/No |
| Por Vencer | Próximos 30 días |
| Registros Eliminados | Mostrar/Ocultar |

---

## Gestión de Documentos

### Tipos de Documentos

1. **Contrato PDF/Word**: Documento del contrato (máximo 10MB)
2. **Certificado PDF ARL**: Cargado por SSST al validar

### Almacenamiento

- Archivos guardados en `storage/app/`
- Nombres únicos para evitar colisiones
- Registro de metadatos (tamaño, tipo, usuario)

---

## Soft Deletes

### Eliminación Lógica

- Los registros no se eliminan permanentemente
- Se marcan con fecha de eliminación
- Pueden ser restaurados por SSST o Admin

### Eliminación Permanente

- Solo disponible para Super Admin y SSST
- Requiere confirmación adicional
- No se puede deshacer

---

## Seguridad

### Autenticación

- Login con email y contraseña
- Contraseñas encriptadas con Bcrypt
- Sesiones seguras

### Autorización

- Middleware de autenticación en todas las rutas
- Verificación de permisos por acción
- Filtrado automático por dependencia

### Protección

- CSRF tokens en formularios
- Validación de entrada en frontend y backend
- Sanitización de datos
