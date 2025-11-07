# Cambios: IBC Calculado Automáticamente

## 📋 Resumen

Se eliminó el campo **IBC** del Excel de importación para evitar confusiones. El sistema ahora **calcula automáticamente** el IBC como el 40% de los honorarios mensuales.

---

## ✅ Cambios Realizados

### 1. Plantilla de Excel Actualizada

**Antes:**
- La plantilla incluía una columna "IBC" que confundía a los usuarios
- Los usuarios no sabían si debían llenarla o no
- Podían ingresar valores incorrectos

**Ahora:**
- ❌ Columna "IBC" **ELIMINADA** de la plantilla
- ✅ Agregados **asteriscos (*)** a campos obligatorios
- ✅ Agregada **fila de ejemplos** con formato esperado
- ✅ Mejores estilos visuales (colores institucionales)

### 2. Cálculo Automático del IBC

**Código anterior:**
```php
$ibc = $this->limpiarValor($row['ibc'] ?? 0);

// Si no hay IBC en el Excel, calcularlo automáticamente
if (empty($ibc) && !empty($honorarios)) {
    $ibc = $honorarios * 0.40;
}
```

**Código nuevo:**
```php
// IMPORTANTE: El IBC SIEMPRE se calcula automáticamente como 40% de los honorarios
// No se debe tomar del Excel para evitar errores
$ibc = $honorarios * 0.40;
```

### 3. Documentación Actualizada

Se actualizó `GUIA_IMPORTACION_MEJORADA.md` con:

> ⚠️ **IMPORTANTE:** El **IBC (Ingreso Base de Cotización)** NO se debe incluir en el Excel. El sistema lo calcula automáticamente como el 40% de los honorarios mensuales.

---

## 📊 Nueva Estructura del Excel

### Fila 1: Título
```
SISTEMA DE GESTIÓN DE AFILIACIONES ARL - PLANTILLA DE IMPORTACIÓN
```
- Color: Azul institucional (#3366CC)
- Texto blanco, centrado

### Fila 2: Encabezados de Columnas

Campos **obligatorios** marcados con asterisco (*):

| Obligatorio | Campo |
|-------------|-------|
| ✅ * | No. CONTRATO |
| ✅ * | OBJETO CONTRATO |
| ✅ * | CC CONTRATISTA |
| ✅ * | CONTRATISTA |
| ✅ * | VALOR DEL CONTRATO |
| ⬜ | MESES |
| ⬜ | DIAS |
| ✅ * | Honorarios mensual |
| ✅ * | Fecha ingreso A partir de Acta inicio |
| ✅ * | Fecha retiro |
| ✅ * | Secretaría |
| ⬜ | Área |
| ⬜ | Fecha de Nacimiento |
| ⬜ | Nivel de riesgo |
| ⬜ | No. Celular |
| ⬜ | Barrio |
| ⬜ | Dirección Residencia |
| ⬜ | EPS |
| ⬜ | AFP |
| ⬜ | Dirección de correo Electronica |
| ⬜ | FECHA DE AFILIACION |
| ⬜ | FECHA TERMIANCION AFILIACION |

**Total de columnas: 22** (antes eran 23 con IBC)

### Fila 3: Ejemplos y Ayudas

Cada columna tiene un ejemplo del formato esperado:
- `Ej: 001-2025` (para No. Contrato)
- `Solo números, sin $ ni puntos` (para valores monetarios)
- `dd/mm/aaaa` (para fechas)
- `correo@ejemplo.com` (para emails)
- etc.

---

## 💡 Beneficios

### Para los Usuarios (Secretarías):
✅ **Menos columnas que llenar** (22 en lugar de 23)
✅ **Sin confusión** sobre qué poner en IBC
✅ **Sin errores** por calcular mal el IBC
✅ **Ejemplos visuales** de cómo llenar cada campo
✅ **Identificación clara** de campos obligatorios con *

### Para el Sistema:
✅ **Cálculo consistente** del IBC (siempre 40%)
✅ **Menos validaciones** necesarias
✅ **Datos más confiables**
✅ **Menos errores** en importación

---

## 🔄 Proceso de Migración

### Usuarios que ya tienen Excel antiguo:

1. **Descargar nueva plantilla** desde el sistema
2. **Copiar datos** del Excel antiguo al nuevo
3. **Omitir la columna IBC** (el sistema lo calcula)
4. **Importar** normalmente

### No es necesario:
- ❌ Re-importar datos existentes
- ❌ Recalcular IBC de registros anteriores
- ❌ Modificar la base de datos

Los registros anteriores **mantienen su IBC** y funcionan normalmente.

---

## 📝 Notas Técnicas

### Fórmula del IBC:
```
IBC = Honorarios Mensuales × 0.40
```

### Ejemplo:
```
Honorarios: $5,000,000
IBC calculado: $2,000,000 (40%)
```

### Campos afectados en el código:
- `app/Exports/AfiliacionesTemplateExport.php` - Eliminada columna IBC
- `app/Imports/AfiliacionesImport.php` - IBC calculado automáticamente
- `GUIA_IMPORTACION_MEJORADA.md` - Documentación actualizada

---

## 🎯 Conclusión

El sistema ahora es más simple, claro y menos propenso a errores. Los usuarios solo necesitan ingresar los **honorarios mensuales** y el sistema calcula automáticamente el **IBC** correcto.

---

**Fecha de implementación:** Noviembre 2025
**Versión:** 2.1
