---
title: Panel de Control (Dashboard)
description: Guía completa del dashboard del Sistema de Gestión de Afiliaciones ARL
---

## Vista General

El Dashboard es la primera pantalla que ves al iniciar sesión. Proporciona una vista rápida del estado de las afiliaciones y alertas importantes.

---

## Widgets de Estadísticas

### Total de Afiliaciones

- **Qué muestra**: Cantidad total de afiliaciones registradas
- **Gráfico**: Línea de tendencia de los últimos 7 días
- **Color**: Azul (informativo)
- **Uso**: Visión general del volumen de trabajo

### Pendientes de Validación

- **Qué muestra**: Afiliaciones esperando revisión por SSST
- **Color**: Amarillo (advertencia)
- **Uso**: El equipo SSST debe priorizar estas solicitudes
- **Acción**: Click para ir directamente a las pendientes

### Afiliaciones Validadas

- **Qué muestra**: Afiliaciones aprobadas con PDF ARL cargado
- **Color**: Verde (éxito)
- **Uso**: Indicador de trabajo completado

### Afiliaciones Rechazadas

- **Qué muestra**: Afiliaciones que requieren corrección
- **Color**: Rojo (alerta)
- **Uso**: Las dependencias deben revisar y corregir

### Contratos Vigentes

- **Qué muestra**: Contratos con fecha de fin posterior a hoy
- **Color**: Verde
- **Filtro**: `fecha_fin >= fecha_actual`

### Por Vencer en 30 Días

- **Qué muestra**: Contratos que terminan en los próximos 30 días
- **Color**: Amarillo (advertencia)
- **Uso**: Planificar renovaciones o terminaciones
- **Acción**: Click para ver el listado detallado

---

## Gráficos

### Distribución por Estado

**Tipo**: Gráfico de dona (doughnut)

Muestra la proporción de afiliaciones por estado:
- **Amarillo**: Pendientes
- **Verde**: Validadas
- **Rojo**: Rechazadas

**Interacción**:
- Pasa el cursor sobre cada sección para ver el número exacto
- Click en la leyenda para ocultar/mostrar un estado

### Afiliaciones por Dependencia

**Tipo**: Gráfico de barras horizontales

Compara la cantidad de afiliaciones entre dependencias.

**Uso**:
- Identificar qué dependencias tienen más carga
- Balancear recursos de validación

---

## Contratos por Vencer

Widget tipo tabla que muestra:

| Columna | Descripción |
|---------|-------------|
| Contratista | Nombre del contratista |
| Contrato | Número de contrato |
| Fecha Fin | Cuándo termina |
| Días Restantes | Countdown |

**Ordenamiento**: Por fecha de vencimiento (más próximos primero)

---

## Filtrado por Usuario

El dashboard respeta los permisos de cada rol:

### Rol Dependencia
- Solo ve estadísticas de **su dependencia y área**
- No ve datos de otras dependencias

### Rol SSST
- Ve todas las estadísticas **globales**
- Tiene visión completa del sistema

### Rol Super Admin
- Acceso **total** a todas las estadísticas
- Puede ver datos históricos y eliminados

---

## Acciones desde el Dashboard

### Acceso Rápido

Los widgets son clickeables y te llevan directamente a:

- **Pendientes**: Lista filtrada por estado pendiente
- **Por Vencer**: Lista filtrada por contratos próximos a terminar

### Actualización de Datos

Los datos se actualizan:
- **Al cargar la página**: Datos frescos
- **Manualmente**: Recarga la página (F5)

:::note
Los gráficos no se actualizan en tiempo real. Recarga la página para ver cambios recientes.
:::

---

## Interpretación de Datos

### Indicadores Saludables

- **Pendientes bajo**: El equipo SSST está al día
- **Rechazadas bajo**: Buena calidad en los registros
- **Validadas alto**: Proceso fluido

### Indicadores de Alerta

- **Pendientes alto**: Posible cuello de botella en SSST
- **Rechazadas alto**: Las dependencias necesitan capacitación
- **Por vencer alto**: Planificar renovaciones

### Recomendaciones

| Situación | Acción Recomendada |
|-----------|-------------------|
| Muchas pendientes | SSST debe priorizar validaciones |
| Muchas rechazadas | Revisar motivos y capacitar a dependencias |
| Contratos por vencer | Contactar contratistas para renovación |

---

## Personalización

### Reordenar Widgets

Actualmente los widgets tienen posición fija. En futuras versiones se permitirá reorganizar.

### Período de Datos

Por defecto muestra datos de los últimos 30 días. Los totales incluyen todos los registros históricos.

---

## Solución de Problemas

### Los gráficos no cargan

1. Verifica la conexión a internet
2. Limpia el caché del navegador
3. Recarga la página

### Los números no coinciden

Los datos pueden tener un pequeño retraso. Si persiste:
1. Verifica que las migraciones estén actualizadas
2. Limpia el caché de la aplicación

### No veo todos los datos

Verifica tu rol:
- Los usuarios de Dependencia solo ven su propia información
- Contacta al administrador si necesitas más acceso

---

## Próximos Pasos

- [Gestión de Afiliaciones](/docs/usuario/afiliaciones/)
- [Importar/Exportar Excel](/docs/usuario/excel/)
