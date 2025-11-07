# Guía de Importación Mejorada - Sistema ARL

## 📋 Descripción General

El sistema de importación ha sido mejorado para validar **todos los datos** antes de importar, identificar exactamente **qué datos faltan**, y generar **reportes detallados** de errores que pueden descargarse en Excel para facilitar las correcciones.

---

## ✅ Campos Obligatorios

El sistema ahora valida los siguientes campos como **OBLIGATORIOS**:

### 📄 Información del Contrato
1. **No. Contrato** (`no_contrato`) - Número único del contrato
2. **Objeto del Contrato** (`objeto_contrato`) - Descripción del contrato
3. **Secretaría** (`secretaria`) - Dependencia o secretaría
4. **Valor del Contrato** (`valor_del_contrato`) - Valor total (solo números)

### 👤 Información del Contratista
5. **CC Contratista** (`cc_contratista`) - Número de cédula
6. **Contratista** (`contratista`) - Nombre completo

### 📅 Fechas
7. **Fecha Ingreso** (`fecha_ingreso_a_partir_de_acta_inicio`) - Fecha de inicio del contrato
8. **Fecha Retiro** (`fecha_retiro`) - Fecha de finalización del contrato

### 💰 Información Financiera
9. **Honorarios Mensual** (`honorarios_mensual`) - Honorarios mensuales (solo números)

> ⚠️ **IMPORTANTE:** El **IBC (Ingreso Base de Cotización)** NO se debe incluir en el Excel. El sistema lo calcula automáticamente como el 40% de los honorarios mensuales.

---

## 📊 Campos Opcionales (con validación si se llenan)

### Campos con Valor por Defecto Automático:
- **Meses** (`meses`) - Número entero. **Si se deja vacío = 0 automático**
- **Días** (`dias`) - Número entero. **Si se deja vacío = 0 automático**
- **Nivel de Riesgo** (`nivel_de_riesgo`) - Debe ser 1-5 o I-V. **Si se deja vacío = I (Nivel 1)**

### Campos Opcionales sin valor por defecto:
- **No. Celular** (`no_celular`)
- **Correo Electrónico** (`direccion_de_correo_electronica`) - Debe tener formato de email válido
- **Fecha de Nacimiento** (`fecha_de_nacimiento`)
- **Barrio** (`barrio`)
- **Dirección Residencia** (`direccion_residencia`)
- **EPS** (`eps`)
- **AFP** (`afp`)
- **Fecha de Afiliación** (`fecha_de_afiliacion`)
- **Fecha Terminación Afiliación** (`fecha_termiancion_afiliacion`)
- **Área** (`area`) - Depende de la secretaría seleccionada

---

## 🔄 Proceso de Importación

### Paso 1: Preparar el Excel
1. Descargue la **Plantilla de Excel** desde el botón "Descargar Plantilla"
2. Llene todos los campos obligatorios
3. Verifique que los datos numéricos no tengan texto
4. Verifique que las fechas estén en formato correcto

### Paso 2: Importar el Archivo
1. Haga clic en **"Importar Excel"**
2. Seleccione su archivo (.xlsx, .xls o .csv)
3. Haga clic en **"Importar"**

### Paso 3: Revisar Resultados

#### ✅ Si todo es correcto:
- Verá una notificación verde: **"Importación exitosa"**
- Todos los registros se habrán importado correctamente

#### ⚠️ Si hay errores:
- Verá una notificación amarilla con:
  - **Cantidad total de errores** encontrados
  - **Número de filas** con problemas
  - **Resumen de errores más comunes**
  - Botón **"Descargar Reporte de Errores"**

---

## 📥 Reporte de Errores (Excel)

El reporte descargable incluye:

| Columna | Descripción |
|---------|-------------|
| **Fila Excel** | Número de fila en su archivo original |
| **Campo con Error** | Nombre del campo que tiene el problema |
| **Descripción del Error** | Explicación clara del problema |
| **Valor Actual** | El valor que tiene actualmente (o "vacío") |
| **Acción Requerida** | Qué debe hacer para corregirlo |

### Ejemplo de Reporte:

| Fila Excel | Campo con Error | Descripción del Error | Valor Actual | Acción Requerida |
|------------|-----------------|----------------------|--------------|------------------|
| 5 | no_contrato | El número de contrato es obligatorio | (vacío) | Ingresar el número de contrato |
| 5 | honorarios_mensual | Los honorarios mensuales son obligatorios | (vacío) | Ingresar los honorarios mensuales (solo números) |
| 7 | direccion_de_correo_electronica | El correo electrónico no tiene un formato válido | juan@correo | Ingresar un correo electrónico válido |
| 12 | valor_del_contrato | El valor del contrato debe ser un número | $1.500.000 | Ingresar el valor del contrato (solo números) |

---

## 🛠️ Solución de Problemas Comunes

### Problema: "El valor del contrato debe ser un número"
**Solución:** Elimine símbolos como $, puntos o comas. Use solo números: `1500000` en lugar de `$1.500.000`

### Problema: "El correo electrónico no tiene un formato válido"
**Solución:** Verifique que el correo tenga formato completo: `usuario@dominio.com`

### Problema: "La secretaría/dependencia es obligatoria"
**Solución:** Verifique que la columna `secretaria` no esté vacía y contenga un nombre válido

### Problema: "La fecha de inicio es obligatoria"
**Solución:** Asegúrese de que la columna `fecha_ingreso_a_partir_de_acta_inicio` tenga una fecha válida

### Problema: "Los honorarios deben ser mayor a 0"
**Solución:** Ingrese un valor numérico positivo en la columna `honorarios_mensual`

---

## 📝 Recomendaciones

1. **Descargue siempre la plantilla actualizada** antes de llenar datos
2. **Use formato de número** en celdas numéricas (no texto)
3. **Use formato de fecha** en celdas de fechas
4. **No use símbolos** en valores monetarios (sin $, sin puntos, sin comas)
5. **Descargue y revise el reporte de errores** para corregir rápidamente
6. **Corrija el archivo original** usando el reporte como guía
7. **Vuelva a importar** después de corregir

---

## 💡 Ventajas del Nuevo Sistema

✅ **Validación completa** antes de importar
✅ **Identificación precisa** de qué datos faltan
✅ **Reporte descargable** en Excel
✅ **Mensajes claros** de qué hacer para corregir
✅ **Ahorro de tiempo** al saber exactamente qué corregir
✅ **Menos errores** en el sistema
✅ **Trazabilidad** de problemas por fila

---

## 📞 Soporte

Si tiene dudas sobre:
- Qué dato ingresar en un campo específico
- Cómo corregir un error que no entiende
- Problemas técnicos con el archivo

Contacte al área de SSST o al administrador del sistema.

---

**Última actualización:** Noviembre 2025
**Versión del Sistema:** 2.0
