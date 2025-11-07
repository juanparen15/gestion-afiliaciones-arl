# Valores por Defecto en la Importación de Excel

## 📋 Resumen

El sistema aplica **valores por defecto automáticos** a ciertos campos cuando se dejan vacíos en el Excel. Esto facilita el trabajo a las secretarías y evita errores.

---

## ✅ Campos con Valores por Defecto

### 1. **Meses del Contrato** (`meses`)

**Si se deja vacío:** El sistema pone automáticamente **0**

**Ejemplos:**
```
Excel: [vacío] → Sistema: 0
Excel: 6       → Sistema: 6
Excel: 12      → Sistema: 12
```

**¿Por qué?**
- Algunos contratos se miden solo en días
- No es obligatorio especificar meses
- Tener 0 es mejor que tener NULL en la base de datos

---

### 2. **Días del Contrato** (`dias`)

**Si se deja vacío:** El sistema pone automáticamente **0**

**Ejemplos:**
```
Excel: [vacío] → Sistema: 0
Excel: 15      → Sistema: 15
Excel: 30      → Sistema: 30
```

**¿Por qué?**
- Algunos contratos se miden solo en meses
- No es obligatorio especificar días
- Tener 0 es mejor que tener NULL en la base de datos

---

### 3. **Nivel de Riesgo** (`nivel_de_riesgo`)

**Si se deja vacío:** El sistema pone automáticamente **I** (Nivel 1 - Riesgo Mínimo)

**Ejemplos:**
```
Excel: [vacío] → Sistema: I (Nivel 1)
Excel: 1       → Sistema: I
Excel: II      → Sistema: II
Excel: 3       → Sistema: III
Excel: V       → Sistema: V
```

**¿Por qué?**
- La mayoría de contratos administrativos son nivel I
- Es el nivel de riesgo más bajo
- Es más seguro asumir nivel I que dejar sin clasificar

---

### 4. **IBC (Ingreso Base de Cotización)** - AUTOMÁTICO

**No se incluye en el Excel.** El sistema SIEMPRE calcula:

```
IBC = Honorarios Mensuales × 40%
```

**Ejemplos:**
```
Honorarios: $5,000,000 → IBC: $2,000,000
Honorarios: $3,500,000 → IBC: $1,400,000
Honorarios: $10,000,000 → IBC: $4,000,000
```

**¿Por qué?**
- Evita errores de cálculo manual
- Es una fórmula fija del 40%
- Garantiza consistencia en todos los registros

---

### 5. **Nombre ARL** - AUTOMÁTICO

**Valor fijo:** `ARL SURA`

**¿Por qué?**
- La alcaldía tiene contrato con ARL SURA
- No es necesario que las secretarías lo ingresen
- Evita errores de escritura

---

### 6. **Tipo de Documento** - AUTOMÁTICO

**Valor fijo:** `CC` (Cédula de Ciudadanía)

**¿Por qué?**
- La mayoría de contratistas tienen cédula de ciudadanía
- Si es otro tipo de documento (CE, PP, TI), se puede editar después
- Simplifica la plantilla de Excel

---

### 7. **Estado** - AUTOMÁTICO

**Valor fijo:** `pendiente`

**¿Por qué?**
- Todas las afiliaciones importadas inician pendientes de validación
- El SSST las valida después
- Es parte del flujo de trabajo del sistema

---

## 📊 Tabla Resumen

| Campo | Si está vacío | Valor por Defecto | ¿Editable después? |
|-------|---------------|-------------------|-------------------|
| Meses | ✅ Sí | 0 | ✅ Sí |
| Días | ✅ Sí | 0 | ✅ Sí |
| Nivel de Riesgo | ✅ Sí | I (Nivel 1) | ✅ Sí |
| IBC | ❌ No aplica | Calculado (40%) | ✅ Sí (manual) |
| Nombre ARL | ❌ No aplica | ARL SURA | ✅ Sí |
| Tipo Documento | ❌ No aplica | CC | ✅ Sí |
| Estado | ❌ No aplica | Pendiente | ✅ Sí (solo SSST) |

---

## 💡 Recomendaciones

### ✅ Campos que SÍ debes llenar siempre:

1. **No. Contrato** - Obligatorio
2. **Objeto del Contrato** - Obligatorio
3. **CC Contratista** - Obligatorio
4. **Nombre Contratista** - Obligatorio
5. **Valor del Contrato** - Obligatorio
6. **Honorarios Mensuales** - Obligatorio (el IBC se calcula automático)
7. **Fecha de Inicio** - Obligatorio
8. **Fecha de Retiro** - Obligatorio
9. **Secretaría** - Obligatorio

### ⚠️ Campos opcionales que conviene llenar:

- **Meses y Días:** Si sabes la duración exacta, llénalos
- **Nivel de Riesgo:** Si es diferente a I, especifícalo
- **Correo Electrónico:** Importante para notificaciones
- **Teléfono:** Importante para contacto

### ❌ Campos que NO debes llenar (se calculan solos):

- **IBC** - Se calcula automáticamente
- ~~Nombre ARL~~ - Ya está predefinido
- ~~Tipo Documento~~ - Ya está predefinido
- ~~Estado~~ - Se asigna automáticamente

---

## 📝 Ejemplos Prácticos

### Ejemplo 1: Contrato por Meses
```excel
Meses: 6
Días: [vacío]
```
**Resultado en el sistema:**
```
meses_contrato: 6
dias_contrato: 0
```

### Ejemplo 2: Contrato por Días
```excel
Meses: [vacío]
Días: 90
```
**Resultado en el sistema:**
```
meses_contrato: 0
dias_contrato: 90
```

### Ejemplo 3: Contrato Mixto
```excel
Meses: 3
Días: 15
```
**Resultado en el sistema:**
```
meses_contrato: 3
dias_contrato: 15
```

### Ejemplo 4: Sin especificar duración en meses/días
```excel
Meses: [vacío]
Días: [vacío]
Fecha Inicio: 01/01/2025
Fecha Fin: 31/12/2025
```
**Resultado en el sistema:**
```
meses_contrato: 0
dias_contrato: 0
fecha_inicio: 2025-01-01
fecha_fin: 2025-12-31
```
**Nota:** El sistema calculará la duración basándose en las fechas de inicio y fin.

---

## 🔄 Comportamiento del Sistema

### Al Importar:
1. ✅ Lee el Excel fila por fila
2. ✅ Si encuentra un campo vacío con valor por defecto → Aplica el valor
3. ✅ Si encuentra un campo vacío SIN valor por defecto → Deja NULL
4. ✅ Valida campos obligatorios
5. ✅ Genera reporte de errores si faltan datos

### Después de Importar:
- Los registros se pueden **editar manualmente**
- Se puede cambiar cualquier valor, incluso los que se pusieron automáticamente
- Los campos con valores por defecto son **sugerencias**, no restricciones

---

## ❓ Preguntas Frecuentes

### ¿Puedo poner 0 manualmente en Meses o Días?
**Sí.** Es lo mismo que dejarlo vacío. El sistema lo interpretará como 0.

### ¿Qué pasa si pongo texto en Meses o Días?
El sistema mostrará un **error de validación** y no importará esa fila. Debe ser un número entero.

### ¿Puedo cambiar el IBC después de importar?
**Sí.** Puedes editarlo manualmente desde el sistema, aunque se calcula automático al importar.

### ¿Puedo usar otra ARL diferente a SURA?
**Sí.** Después de importar, puedes editar el registro y cambiar la ARL manualmente.

### Si dejo Nivel de Riesgo vacío y debería ser III, ¿qué hago?
Puedes hacer dos cosas:
1. Llenar el campo en el Excel antes de importar
2. Importar con el valor I por defecto y luego editarlo en el sistema

---

## ✅ Ventajas de los Valores por Defecto

1. **Menos trabajo** - Las secretarías no tienen que llenar todo
2. **Menos errores** - Campos numéricos siempre tienen un valor válido
3. **Importación más rápida** - Menos validaciones que fallan
4. **Datos consistentes** - Todos los registros tienen el mismo formato
5. **Flexibilidad** - Se puede editar después si es necesario

---

**Última actualización:** Noviembre 2025
**Versión:** 2.2
