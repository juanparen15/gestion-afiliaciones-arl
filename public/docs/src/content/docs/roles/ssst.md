---
title: Guía para Rol SSST
description: Guía completa para usuarios del equipo de Seguridad y Salud en el Trabajo
---

## Tu Rol en el Sistema

Como usuario con rol **SSST** (Seguridad y Salud en el Trabajo), eres responsable de:
- Revisar y validar las afiliaciones creadas por las dependencias
- Cargar los certificados PDF de la ARL
- Rechazar afiliaciones con información incorrecta
- Monitorear el estado general del sistema
- Generar reportes y estadísticas

---

## Lo que Puedes Hacer

### Acciones Permitidas

| Acción | Descripción |
|--------|-------------|
| Ver todas las afiliaciones | De todas las dependencias |
| Crear afiliaciones | Cuando sea necesario |
| Editar cualquier afiliación | Sin restricción de estado |
| **Validar** | Aprobar y cargar PDF ARL |
| **Rechazar** | Con motivo de rechazo |
| Restaurar eliminados | Soft deletes |
| Eliminar permanentemente | Force delete |
| Importar Excel | Carga masiva |
| Exportar | Todos los datos |

### Acciones NO Permitidas

| Acción | Quién puede |
|--------|------------|
| Gestionar usuarios | Solo Admin |
| Configurar dependencias | Solo Admin |
| Gestionar roles | Solo Admin |

---

## Tu Flujo de Trabajo Diario

```
1. Revisar emails de nuevas afiliaciones
              │
              ▼
2. Acceder al sistema y ver pendientes
              │
              ▼
3. Para cada afiliación pendiente:
              │
        ┌─────┴─────┐
        ▼           ▼
   Revisar       ¿Datos
   datos         correctos?
        │           │
        │      ┌────┴────┐
        │      No        Sí
        │      │         │
        │      ▼         ▼
        │   Rechazar   Obtener PDF
        │   con motivo  de ARL
        │                │
        │                ▼
        │            Validar con
        │            PDF cargado
        │
        └────────────────┘
```

---

## Recibir Notificaciones

### Email de Nueva Afiliación

Cuando una dependencia crea una afiliación, recibes:
- **Asunto**: "Nueva Afiliación Pendiente de Revisión - ARL"
- **Contenido**: Datos básicos del contratista y contrato
- **Enlace**: Acceso directo al sistema

### Revisar Pendientes

1. Accede al sistema
2. El dashboard muestra el widget **Pendientes de Validación**
3. Click para ir a la lista filtrada

---

## Proceso de Validación

### Paso 1: Revisar la Afiliación

1. Abre la afiliación (icono de ojo)
2. Revisa todos los tabs:
   - Datos del contratista
   - Información del contrato
   - Información ARL
   - Observaciones

### Paso 2: Verificar Datos

Lista de verificación:

- [ ] Documento de identidad válido (10 dígitos para CC)
- [ ] Email en formato correcto
- [ ] Información de EPS y AFP presente
- [ ] Número de contrato coherente
- [ ] Valor del contrato razonable
- [ ] Fechas lógicas (fin > inicio)
- [ ] Documento del contrato adjunto
- [ ] Nivel de riesgo apropiado

### Paso 3: Obtener Certificado ARL

1. Accede al sistema de la ARL (Positiva, Sura, etc.)
2. Busca la afiliación por número de documento
3. Descarga el certificado en PDF
4. Verifica que los datos coincidan

### Paso 4: Validar en el Sistema

1. En la fila de la afiliación, click en ✓ (validar)
2. Se abre el modal de validación
3. Click en **Seleccionar archivo**
4. Sube el certificado PDF de la ARL
5. Agrega observaciones si es necesario
6. Click en **Validar**

### Resultado

- Estado cambia a **VALIDADO**
- PDF disponible para la dependencia
- Se registra quién validó y cuándo

---

## Proceso de Rechazo

### Cuándo Rechazar

- Datos incompletos o incorrectos
- Documento del contrato faltante
- Nivel de riesgo inadecuado
- Inconsistencias en la información

### Cómo Rechazar

1. Click en ✗ (rechazar) en la fila
2. Se abre el modal de rechazo
3. Escribe el motivo **claro y específico**
4. Click en **Rechazar**

### Ejemplo de Buenos Motivos

```
✅ BUENO:
"El número de cédula 12345678901 tiene 11 dígitos.
Las cédulas colombianas tienen 10 dígitos.
Por favor verificar con el documento de identidad."

❌ MALO:
"Cédula incorrecta"
```

```
✅ BUENO:
"El nivel de riesgo III corresponde a actividades con
exposición a riesgos físicos. Para prestación de servicios
profesionales administrativos, el nivel debe ser I o II.
Por favor corregir según la actividad del contratista."

❌ MALO:
"Riesgo mal"
```

---

## Importación Masiva

### Descargar Plantilla

1. Click en **Descargar Plantilla**
2. Comparte con las dependencias
3. Que completen y te devuelvan

### Importar Archivo

1. Click en **Importar Excel**
2. Selecciona el archivo .xlsx
3. Click en **Importar**
4. Revisa el resultado

### Manejar Errores

Si hay errores:
1. Click en **Descargar errores**
2. Revisa el archivo de errores
3. Corrige el Excel original
4. Importa nuevamente las filas corregidas

---

## Estadísticas y Reportes

### Dashboard

Tu dashboard muestra estadísticas globales:
- Total de afiliaciones del sistema
- Pendientes de todas las dependencias
- Distribución por estado
- Por dependencia

### Identificar Problemas

| Indicador | Posible Problema | Acción |
|-----------|-----------------|--------|
| Muchas pendientes | Cuello de botella | Priorizar validaciones |
| Muchas rechazadas | Falta capacitación | Crear guías para dependencias |
| Alta tasa de rechazo en una dependencia | Necesitan ayuda | Contactar y capacitar |

### Exportar Datos

- **Todo**: Para reportes gerenciales
- **Filtrado**: Por dependencia, estado, fechas
- **Seleccionados**: Casos específicos

---

## Gestión de Eliminados

### Ver Registros Eliminados

1. Aplica filtro **Registros eliminados**
2. Los registros soft-deleted aparecen

### Restaurar

1. Click en el icono de restaurar
2. Confirma la acción
3. El registro vuelve al estado anterior

### Eliminar Permanentemente

1. Filtra por eliminados
2. Click en **Eliminar permanentemente**
3. Confirma (acción irreversible)
4. El registro se borra de la base de datos

:::danger[Precaución]
La eliminación permanente **no se puede deshacer**.
:::

---

## Editar Información ARL

Solo tú puedes modificar:
- Nombre de la ARL
- Número de afiliación
- Fechas de afiliación
- PDF del certificado

Para cambiar el PDF después de validar:
1. Edita la afiliación
2. En el tab "Información ARL"
3. Sube un nuevo PDF
4. Guardar

---

## Buenas Prácticas

### Diariamente

1. **Revisar emails** de nuevas afiliaciones
2. **Validar o rechazar** dentro de 48 horas
3. **Monitorear** el dashboard

### Semanalmente

1. **Revisar estadísticas** por dependencia
2. **Identificar patrones** de rechazo
3. **Comunicar** problemas comunes

### Mensualmente

1. **Exportar reporte** general
2. **Analizar tendencias**
3. **Proponer mejoras** al proceso

---

## Comunicación con Dependencias

### Cuando Rechazas

- Sé claro y específico en el motivo
- Indica qué campo está mal
- Explica cómo corregirlo

### Si hay Preguntas

- Responde oportunamente
- Proporciona ejemplos
- Considera crear guías si la duda es común

### Capacitación

Si una dependencia tiene muchos rechazos:
1. Analiza los motivos comunes
2. Programa una capacitación
3. Proporciona materiales de apoyo

---

## Preguntas Frecuentes

### ¿Puedo validar sin el PDF de la ARL?

No. El PDF es requisito obligatorio para validar.

### ¿Puedo cambiar un estado después de validar?

No directamente. Contacta al administrador si es necesario.

### ¿Cómo veo quién creó cada afiliación?

En la tabla, columna "Creado por". También puedes ver en la vista detallada.

### ¿Puedo crear afiliaciones directamente?

Sí, tienes permiso de creación. Útil para casos especiales.

---

## Soporte

Para problemas técnicos:
1. Revisa [Solución de Problemas](/docs/referencia/troubleshooting/)
2. Contacta al administrador del sistema

---

## Próximos Pasos

- [Validación y Rechazo detallado](/docs/usuario/validacion/)
- [Importar/Exportar Excel](/docs/usuario/excel/)
