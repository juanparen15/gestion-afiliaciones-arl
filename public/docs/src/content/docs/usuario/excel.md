---
title: Importar/Exportar Excel
description: Guía para importar y exportar afiliaciones mediante archivos Excel
---

## Exportación de Datos

### Exportar Todas las Afiliaciones

1. Ve a **Afiliaciones**
2. Click en el botón **Exportar Todo** (en el encabezado de la tabla)
3. Se descarga un archivo Excel con todas las afiliaciones

**Contenido del archivo:**
- Todas las columnas de la tabla
- Formateado con estilos profesionales
- Encabezados en color naranja
- Columnas ajustadas automáticamente

### Exportar Seleccionados

1. Marca las casillas de las afiliaciones deseadas
2. Click en **Acciones masivas**
3. Selecciona **Exportar seleccionados**
4. Se descarga Excel solo con esas filas

### Descargar Plantilla

Para importar datos, primero descarga la plantilla:

1. Click en **Descargar Plantilla**
2. Se descarga un Excel vacío con los encabezados correctos
3. Usa esta plantilla para preparar tus datos

---

## Formato de la Plantilla

### Columnas Requeridas

| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| no_contrato | Número de contrato | CON-2024-001 |
| objeto_contrato | Descripción del contrato | Prestación de servicios... |
| cc_contratista | Número de documento | 1234567890 |
| contratista | Nombre completo | Juan Pérez García |
| valor_del_contrato | Valor total | 50000000 |
| meses | Meses de duración | 6 |
| dias | Días adicionales | 15 |
| honorarios_mensual | Pago mensual | 5000000 |
| fecha_ingreso_a_partir_de_acta_inicio | Fecha inicio | 01/01/2024 |
| fecha_retiro | Fecha fin | 30/06/2024 |
| secretaria | Nombre dependencia | Sistemas e Informática |
| fecha_de_nacimiento | Fecha nacimiento | 15/03/1990 |
| nivel_de_riesgo | Nivel (1-5 o I-V) | 3 o III |

### Columnas Opcionales

| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| ibc | Ingreso Base Cotización | 2000000 |
| area | Nombre del área | Área de Sistemas |
| no_celular | Teléfono | 3001234567 |
| barrio | Barrio | Centro |
| direccion_residencia | Dirección | Calle 10 #20-30 |
| eps | Nombre EPS | Sanitas |
| afp | Nombre AFP | Porvenir |
| direccion_de_correo_electronica | Email | juan@email.com |
| fecha_de_afiliacion | Fecha afiliación ARL | 01/01/2024 |
| fecha_terminacion_afiliacion | Fin afiliación | 30/06/2024 |

---

## Preparar Datos para Importación

### Formato de Fechas

El sistema acepta varios formatos:

```
DD/MM/YYYY → 01/01/2024
DD-MM-YYYY → 01-01-2024
YYYY-MM-DD → 2024-01-01
```

:::tip[Recomendación]
Usa formato DD/MM/YYYY para evitar confusiones.
:::

### Formato de Valores Monetarios

Puedes usar varios formatos:

```
5000000     → Se acepta
5.000.000   → Se acepta (se limpian puntos)
$5.000.000  → Se acepta (se limpia el $)
```

### Formato de Nivel de Riesgo

Puedes usar números o romanos:

```
1 → Se convierte a I
2 → Se convierte a II
3 → Se convierte a III
4 → Se convierte a IV
5 → Se convierte a V
```

### Nombre de Dependencia

Debe coincidir con una dependencia existente:
- Usa el nombre exacto
- O el código de la dependencia
- No distingue mayúsculas/minúsculas

---

## Importar Archivo Excel

### Proceso de Importación

1. Ve a **Afiliaciones**
2. Click en **Importar Excel**
3. Se abre un modal
4. Click en **Seleccionar archivo** o arrastra el Excel
5. Click en **Importar**

### Durante la Importación

El sistema:
1. Lee cada fila del Excel
2. Valida los datos
3. Busca la dependencia por nombre/código
4. Busca el área dentro de la dependencia
5. Calcula el IBC si no se proporciona
6. Crea o actualiza el registro

### Comportamiento de Actualización

Si el número de documento ya existe:
- **Actualiza** el registro existente
- No crea duplicados
- Si estaba eliminado, lo **restaura**

---

## Resultado de la Importación

### Importación Exitosa

Muestra un mensaje con:
- Registros creados: X
- Registros actualizados: Y
- Total procesados: Z

### Errores de Importación

Si hay errores:
1. Se muestra un mensaje de alerta
2. Se genera un archivo Excel con los errores
3. Click en **Descargar errores** para obtenerlo

### Archivo de Errores

El Excel de errores contiene:

| Fila | Campo | Error | Valor |
|------|-------|-------|-------|
| 5 | email | El formato de email es inválido | juan@@ |
| 8 | secretaria | La dependencia no existe | Secretaría X |

---

## Validaciones de Importación

### Campos Obligatorios

Estos campos son requeridos y no pueden estar vacíos:

- `no_contrato`
- `objeto_contrato`
- `cc_contratista`
- `contratista`
- `valor_del_contrato`
- `honorarios_mensual`
- `fecha_ingreso_a_partir_de_acta_inicio`
- `fecha_retiro`
- `secretaria`

### Validaciones Específicas

| Campo | Validación |
|-------|------------|
| cc_contratista | Solo números |
| email | Formato válido |
| nivel_de_riesgo | 1-5 o I-V |
| secretaria | Debe existir en el sistema |
| fechas | Formato válido |
| valores | Números positivos |

---

## Consejos para Importación Masiva

### Antes de Importar

1. **Descarga la plantilla** más reciente
2. **No modifiques** los encabezados
3. **Verifica** que las dependencias existan
4. **Revisa** el formato de fechas
5. **Elimina** filas vacías al final

### Durante la Importación

1. Importa en **lotes pequeños** primero (10-20 registros)
2. Verifica que funcione correctamente
3. Luego importa el archivo completo

### Si Hay Errores

1. Descarga el archivo de errores
2. Corrige los datos en el Excel original
3. Importa nuevamente solo las filas corregidas

---

## Limitaciones

- **Tamaño máximo**: 10MB por archivo
- **Formato**: Solo archivos .xlsx o .xls
- **Filas**: Máximo 10,000 filas por importación
- **Tiempo**: Importaciones grandes pueden tomar varios minutos

---

## Ejemplo de Archivo Excel

| no_contrato | contratista | cc_contratista | valor_del_contrato | honorarios_mensual | fecha_ingreso | fecha_retiro | secretaria |
|-------------|-------------|----------------|--------------------|--------------------|---------------|--------------|------------|
| CON-001 | Juan Pérez | 1234567890 | 30000000 | 5000000 | 01/01/2024 | 30/06/2024 | Sistemas |
| CON-002 | María López | 0987654321 | 24000000 | 4000000 | 15/01/2024 | 15/07/2024 | Talento Humano |

---

## Solución de Problemas

### "El archivo no es válido"

- Verifica que sea formato .xlsx o .xls
- Asegúrate de que no esté corrupto
- Intenta abrir y guardar de nuevo en Excel

### "La dependencia no existe"

- Verifica el nombre exacto de la dependencia
- Revisa mayúsculas/minúsculas
- Contacta al admin para crear la dependencia

### "Formato de fecha inválido"

- Usa formato DD/MM/YYYY
- Verifica que Excel no haya cambiado el formato
- Formatea la columna como texto si hay problemas

### La importación tarda mucho

- Divide el archivo en partes más pequeñas
- Importa fuera de horas pico
- Contacta al administrador si persiste

---

## Próximos Pasos

- [Validación y Rechazo](/usuario/validacion/)
- [Guía por Rol](/roles/dependencia/)
