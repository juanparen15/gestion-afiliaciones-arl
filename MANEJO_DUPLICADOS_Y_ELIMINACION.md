# Manejo de Duplicados y Eliminación de Registros

## 🎯 Problemas Solucionados

### Problema 1: Registros "Eliminados" siguen en la Base de Datos
**Causa:** El sistema usa **SoftDeletes** (eliminación lógica)
- Cuando eliminas un registro, NO se borra físicamente
- Solo se marca como eliminado (`deleted_at` con fecha)
- El registro sigue en la base de datos

### Problema 2: Error de Duplicados al Re-Importar
**Error anterior:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062
Duplicate entry '1007568729' for key 'afiliaciones_numero_documento_unique'
```

**Causa:**
- Usuario elimina registro en el panel
- Registro se marca como eliminado (soft delete)
- Usuario intenta re-importar el mismo documento
- Sistema intenta crear nuevo registro
- Base de datos rechaza por número de documento duplicado

---

## ✅ Solución Implementada

### 1. **Sistema Inteligente de Actualización/Creación**

El sistema ahora al importar:

```
┌─────────────────────────────────────┐
│ 1. Lee fila del Excel               │
│ 2. Extrae número de documento       │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ ¿Existe registro con ese documento? │
│ (incluso si está eliminado)         │
└──────────────┬──────────────────────┘
               │
       ┌───────┴───────┐
       │               │
      SÍ              NO
       │               │
       ▼               ▼
┌──────────────┐  ┌──────────────┐
│ ¿Eliminado?  │  │ Crear nuevo  │
└──────┬───────┘  │   registro   │
       │          └──────────────┘
  ┌────┴────┐
  │         │
 SÍ        NO
  │         │
  ▼         ▼
┌───────┐ ┌─────────────┐
│Restaur│ │ Actualizar  │
│   +   │ │  registro   │
│Actual.│ │  existente  │
└───────┘ └─────────────┘
```

---

## 📊 Comportamiento Detallado

### Escenario 1: Registro Nuevo
**Excel:** CC 1234567890 (no existe en BD)

**Resultado:**
```
✅ Crear nuevo registro
   registrosCreados++
```

### Escenario 2: Registro Existente Activo
**Excel:** CC 1007568729 (existe y está activo)

**Resultado:**
```
✅ Actualizar registro existente con nuevos datos
   registrosActualizados++

Datos actualizados:
- Nombre, dirección, teléfono
- Valores del contrato
- Fechas
- Todo excepto created_by
```

### Escenario 3: Registro Eliminado (Soft Deleted)
**Excel:** CC 1007568729 (existe pero está eliminado)

**Resultado:**
```
✅ Restaurar registro eliminado
✅ Actualizar con nuevos datos del Excel
   registrosActualizados++

Proceso:
1. restore() → Quita marca de eliminado
2. update() → Actualiza todos los campos
3. Registro queda activo y actualizado
```

---

## 🔍 Código Implementado

### Detección y Manejo de Duplicados

```php
// Buscar si existe (incluyendo eliminados)
$afiliacionExistente = Afiliacion::withTrashed()
    ->where('numero_documento', $numeroDocumento)
    ->first();

if ($afiliacionExistente) {
    // Si está eliminado, restaurarlo
    if ($afiliacionExistente->trashed()) {
        $afiliacionExistente->restore();
    }

    // Actualizar con nuevos datos
    $afiliacionExistente->update($datos);
    $this->registrosActualizados++;

    return null; // No crear duplicado
}

// Si no existe, crear nuevo
$this->registrosCreados++;
return new Afiliacion($datos);
```

---

## 📈 Notificaciones Mejoradas

### Antes:
```
✅ Importación exitosa
Todos los registros se importaron correctamente.
```

### Ahora:
```
✅ Importación exitosa

Total procesados: 50 registros
• Nuevos creados: 45
• Actualizados: 5
```

**Información clara:**
- Cuántos registros se procesaron en total
- Cuántos eran nuevos
- Cuántos se actualizaron (incluyendo restaurados)

---

## 🗑️ Tipos de Eliminación

### Eliminación Suave (Soft Delete) - ACTUAL
**Qué hace:**
- Marca el registro con `deleted_at = fecha actual`
- El registro sigue en la base de datos
- No aparece en listados normales
- Se puede restaurar

**Ventajas:**
- ✅ Historial completo
- ✅ Auditoría
- ✅ Recuperación posible
- ✅ Integridad referencial

**Desventajas:**
- ⚠️ Ocupa espacio en BD
- ⚠️ Puede causar confusión

### Eliminación Permanente (Force Delete)
**Qué hace:**
- Borra el registro físicamente de la BD
- No se puede recuperar
- Libera el número de documento

**Cuándo usar:**
- Si realmente quieres eliminar para siempre
- Si necesitas liberar el número de documento
- Si el registro fue creado por error

---

## 💡 Recomendaciones

### Para Usuarios (Secretarías):

1. **No te preocupes por duplicados**
   - Si re-importas un documento, el sistema lo actualizará
   - No verás errores de duplicados

2. **Si eliminas por error**
   - Simplemente vuelve a importar el Excel
   - El registro se restaurará y actualizará

3. **Actualizar información**
   - Si cambió el teléfono, dirección, etc.
   - Importa el Excel con los datos nuevos
   - El sistema actualizará automáticamente

### Para Administradores:

1. **Revisar registros antes de eliminar**
   - La eliminación es suave, se puede restaurar

2. **Si necesitas eliminar permanentemente**
   - Contacta al desarrollador
   - Se puede hacer desde la base de datos

3. **Monitorear actualizaciones**
   - Revisa las notificaciones de importación
   - Verifica cuántos se actualizaron vs. creados

---

## 📝 Ejemplos Prácticos

### Ejemplo 1: Primera Importación
**Excel:** 100 registros nuevos

**Resultado:**
```
✅ Importación exitosa

Total procesados: 100 registros
• Nuevos creados: 100
• Actualizados: 0
```

### Ejemplo 2: Re-importación con Cambios
**Excel:** 100 registros (50 existen, 50 son nuevos)

**Resultado:**
```
✅ Importación exitosa

Total procesados: 100 registros
• Nuevos creados: 50
• Actualizados: 50
```

### Ejemplo 3: Restauración de Eliminados
**Situación:**
- Usuario eliminó 10 registros por error
- Vuelve a importar el Excel con esos 10

**Resultado:**
```
✅ Importación exitosa

Total procesados: 10 registros
• Nuevos creados: 0
• Actualizados: 10  ← Incluye los 10 restaurados
```

---

## 🔧 Migración de Datos Existentes

### Si ya tienes registros eliminados:

**Opción 1: Dejarlos como están**
- Los registros eliminados permanecen ocultos
- Si re-importas, se restaurarán automáticamente

**Opción 2: Limpiar base de datos**
```sql
-- Ver registros eliminados
SELECT * FROM afiliaciones WHERE deleted_at IS NOT NULL;

-- Restaurar todos los eliminados (si lo deseas)
UPDATE afiliaciones SET deleted_at = NULL WHERE deleted_at IS NOT NULL;

-- O eliminar permanentemente los eliminados
DELETE FROM afiliaciones WHERE deleted_at IS NOT NULL;
```

---

## ❓ Preguntas Frecuentes

### ¿Qué pasa si importo el mismo Excel dos veces?
**R:** La segunda vez actualizará todos los registros con los mismos datos. No habrá duplicados.

### ¿Se pueden tener dos personas con el mismo número de documento?
**R:** No. El número de documento es único. Si intentas importar un duplicado, se actualizará el existente.

### ¿Qué campos se actualizan al re-importar?
**R:** TODOS los campos del registro se actualizan con los datos del Excel, excepto:
- `id` (no cambia)
- `created_by` (mantiene el usuario que lo creó originalmente)
- `created_at` (mantiene la fecha de creación original)

### ¿Se pierde el historial al actualizar?
**R:** No. El sistema usa Spatie Activity Log que registra todos los cambios en una tabla separada.

### ¿Puedo ver qué se actualizó?
**R:** Sí, el sistema registra todos los cambios en el log de actividad.

---

## ✅ Ventajas del Nuevo Sistema

1. **Sin errores de duplicados** ❌ → ✅
2. **Actualización automática** de datos existentes
3. **Restauración automática** de eliminados
4. **Estadísticas claras** en cada importación
5. **Historial completo** mantenido
6. **Flexibilidad total** para el usuario

---

## 🚨 Importante

### ⚠️ Cambio de Comportamiento

**Antes:**
- Importar documento existente → ERROR
- Usuario confundido

**Ahora:**
- Importar documento existente → ACTUALIZA
- Usuario feliz

**Implicación:**
- Si importas el mismo Excel varias veces, actualizará los registros
- No creará duplicados
- Úsalo a tu favor para actualizar información masivamente

---

**Última actualización:** Noviembre 2025
**Versión:** 2.3
**Estado:** ✅ Implementado y Funcional
