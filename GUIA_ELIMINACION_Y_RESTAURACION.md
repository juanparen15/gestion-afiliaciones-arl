# Guía de Eliminación y Restauración de Afiliaciones

## 🎯 Introducción

El sistema usa **dos tipos de eliminación** para darte más control y seguridad sobre tus datos:

1. **Eliminación Lógica (Soft Delete)** - Por defecto, segura y reversible
2. **Eliminación Permanente (Force Delete)** - Solo para administradores, irreversible

---

## 📋 Eliminación Lógica (Recomendada)

### ¿Qué Hace?

Cuando haces clic en **"Eliminar"**:
- ✅ El registro se marca como eliminado (`deleted_at` = fecha actual)
- ✅ Desaparece de la lista normal
- ✅ Sigue en la base de datos
- ✅ Se puede restaurar cuando quieras

### ¿Por Qué No Se Borra de la BD?

**Ventajas:**
1. **Seguridad:** Si eliminas por error, puedes recuperarlo
2. **Auditoría:** Historial completo de todos los cambios
3. **Integridad:** No rompe referencias a otros datos
4. **Re-importación:** Puedes volver a importar y se restaura automáticamente

### Cómo Usar

#### Eliminar Un Registro:
1. Ve a la tabla de Afiliaciones
2. Busca el registro que quieres eliminar
3. Haz clic en los **tres puntos (⋮)** del registro
4. Selecciona **"Eliminar"**
5. Confirma en el modal:
   ```
   ¿Estás seguro de que deseas eliminar esta afiliación?
   El registro se marcará como eliminado pero podrás
   restaurarlo después si es necesario.
   ```
6. El registro desaparecerá de la lista

#### Confirmación:
```
✅ Afiliación Eliminada

El registro ha sido eliminado. Puedes restaurarlo
usando el filtro "Registros Eliminados".
```

---

## 🔄 Ver y Restaurar Registros Eliminados

### Ver Registros Eliminados

1. Ve a la tabla de Afiliaciones
2. Busca el filtro **"Registros Eliminados"** (arriba de la tabla)
3. Selecciona una opción:
   - **Sin eliminar** (por defecto) - Solo registros activos
   - **Solo eliminados** - Solo registros eliminados
   - **Con eliminados** - Todos los registros (activos + eliminados)

### Restaurar Un Registro

**Opción 1: Restaurar Individual**
1. Usa el filtro "Registros Eliminados" → Selecciona "Solo eliminados"
2. Encuentra el registro que quieres restaurar
3. Haz clic en los **tres puntos (⋮)**
4. Selecciona **"Restaurar"** (botón verde)
5. Confirmación:
   ```
   ✅ Afiliación Restaurada

   El registro ha sido restaurado exitosamente.
   ```
6. El registro vuelve a la lista normal

**Opción 2: Restaurar Masivamente**
1. Usa el filtro "Registros Eliminados" → "Solo eliminados"
2. Selecciona los registros que quieres restaurar (checkbox)
3. Haz clic en "Acciones masivas" arriba
4. Selecciona **"Restaurar"**
5. Confirmación:
   ```
   ✅ Registros Restaurados

   Los registros seleccionados han sido restaurados exitosamente.
   ```

**Opción 3: Re-Importar desde Excel**
1. Simplemente vuelve a importar el Excel con ese registro
2. El sistema lo restaura y actualiza automáticamente
3. Ver: `MANEJO_DUPLICADOS_Y_ELIMINACION.md`

---

## ⚠️ Eliminación Permanente (Solo Administradores)

### ¿Qué Hace?

Cuando haces clic en **"Eliminar Permanentemente"**:
- ❌ El registro se borra completamente de la base de datos
- ❌ NO se puede recuperar
- ❌ Se pierde todo el historial
- ❌ Se libera el número de documento

### ⚠️ ADVERTENCIA

```
⚠️ Esta acción NO se puede deshacer.
El registro se eliminará permanentemente de la
base de datos y no podrá ser recuperado.
```

### ¿Quién Puede Hacerlo?

Solo usuarios con rol:
- `super_admin`
- `SSST`

Los usuarios normales **NO verán** esta opción.

### Cuándo Usar

**✅ Usa Eliminación Permanente cuando:**
- El registro fue creado por error y nunca debió existir
- Necesitas liberar un número de documento para usarlo en otro registro
- Tienes autorización explícita para borrar el dato
- Estás haciendo limpieza de datos de prueba

**❌ NO uses Eliminación Permanente si:**
- Solo quieres "ocultar" el registro (usa eliminación lógica)
- No estás 100% seguro
- El registro tiene datos importantes
- Podrías necesitar el historial después

### Cómo Usar

#### Eliminar Permanentemente Un Registro:

1. **Primero debes eliminarlo lógicamente**
   - Elimina el registro normalmente

2. **Luego acceder a registros eliminados**
   - Usa filtro "Registros Eliminados" → "Solo eliminados"

3. **Eliminar permanentemente**
   - Haz clic en los **tres puntos (⋮)**
   - Selecciona **"Eliminar Permanentemente"** (botón rojo)
   - Lee la advertencia cuidadosamente
   - Confirma:
     ```
     ⚠️ ADVERTENCIA: Esta acción NO se puede deshacer.
     El registro se eliminará permanentemente de la
     base de datos y no podrá ser recuperado.
     ```
   - Haz clic en "Sí, eliminar permanentemente"

4. **Confirmación final:**
   ```
   ✅ Registro Eliminado Permanentemente

   El registro ha sido eliminado de forma permanente
   y no puede ser recuperado.
   ```

#### Eliminar Permanentemente Múltiples Registros:

1. Filtra "Solo eliminados"
2. Selecciona los registros (checkbox)
3. "Acciones masivas" → **"Eliminar Permanentemente"**
4. Confirma la advertencia
5. Los registros se borran para siempre

---

## 📊 Flujo de Trabajo Recomendado

### Escenario 1: Eliminar Temporalmente

```
Usuario quiere "ocultar" un registro
         ↓
Hacer clic en "Eliminar"
         ↓
Registro se marca como eliminado
         ↓
Desaparece de la lista
         ↓
Sigue en la BD (recuperable)
```

**Resultado:** ✅ Seguro, reversible

### Escenario 2: Restaurar Registro Eliminado

```
Usuario eliminó por error
         ↓
Ir a filtro "Registros Eliminados"
         ↓
Seleccionar "Solo eliminados"
         ↓
Buscar registro
         ↓
Hacer clic en "Restaurar"
         ↓
Registro vuelve a la lista normal
```

**Resultado:** ✅ Recuperado exitosamente

### Escenario 3: Eliminar Para Siempre (Admin)

```
Administrador verifica que debe eliminar
         ↓
Primero: Eliminación lógica
         ↓
Segundo: Filtrar "Solo eliminados"
         ↓
Tercero: "Eliminar Permanentemente"
         ↓
Confirmar advertencia
         ↓
Registro borrado para siempre
```

**Resultado:** ❌ Eliminado permanentemente, irreversible

---

## 🔍 Búsqueda en Registros Eliminados

### Cómo Buscar Un Registro Eliminado

1. Activa filtro "Registros Eliminados" → "Solo eliminados"
2. Usa la barra de búsqueda normalmente
3. Busca por:
   - Número de contrato
   - Nombre del contratista
   - Número de documento
   - Etc.

### Ver Cuándo Fue Eliminado

Los registros eliminados muestran:
- 🗑️ Ícono de eliminado
- Fecha de eliminación (si está visible la columna)

---

## 📝 Estados de Un Registro

### 1. Activo (Normal)
- ✅ Visible en la lista por defecto
- ✅ Se puede editar
- ✅ Se puede validar/rechazar
- ✅ Se puede eliminar

### 2. Eliminado (Soft Deleted)
- ⚠️ NO visible en lista normal
- ⚠️ Visible solo con filtro "Solo eliminados"
- ✅ Se puede restaurar
- ⚠️ Se puede eliminar permanentemente (admin)

### 3. Eliminado Permanentemente
- ❌ Ya no existe
- ❌ No se puede recuperar
- ❌ No aparece en ningún lado

---

## ❓ Preguntas Frecuentes

### ¿Por qué los registros no se borran de la base de datos?
**R:** Es una característica de seguridad. Te permite recuperar datos si eliminas por error.

### ¿Cómo sé si un registro está eliminado o no?
**R:** Usa el filtro "Registros Eliminados". Si no lo ves en la lista normal pero sí con el filtro "Solo eliminados", está eliminado.

### ¿Puedo restaurar un registro después de semanas/meses?
**R:** Sí, mientras no haya sido eliminado permanentemente.

### ¿Qué pasa si re-importo un Excel con un registro eliminado?
**R:** El sistema lo restaura y actualiza automáticamente. Ver `MANEJO_DUPLICADOS_Y_ELIMINACION.md`.

### ¿Puedo eliminar permanentemente sin ser administrador?
**R:** No. Solo usuarios con rol `super_admin` o `SSST` pueden hacerlo.

### ¿Se puede cambiar el sistema para borrar permanentemente por defecto?
**R:** Sí, pero NO es recomendable. La eliminación lógica es una buena práctica de seguridad.

### ¿Los registros eliminados ocupan mucho espacio?
**R:** Generalmente no. Son solo registros marcados. Si hay problemas de espacio, un admin puede limpiar periódicamente.

### ¿Se puede ver el historial de eliminaciones?
**R:** Sí, el sistema registra todas las acciones en el log de actividad (Spatie Activity Log).

---

## 🎯 Mejores Prácticas

### ✅ Hacer:

1. **Usar eliminación lógica** por defecto
2. **Verificar antes de eliminar** permanentemente
3. **Usar el filtro** "Registros Eliminados" regularmente
4. **Restaurar** en lugar de re-importar (si es solo un registro)
5. **Documentar** por qué eliminaste algo importante

### ❌ No Hacer:

1. **NO eliminar permanentemente** a menos que estés 100% seguro
2. **NO asumir** que "eliminar" borra físicamente
3. **NO eliminar** registros con datos importantes sin backup
4. **NO ignorar** las advertencias de eliminación permanente

---

## 🛠️ Para Administradores

### Limpieza Periódica de Registros Eliminados

Si hay muchos registros eliminados acumulados:

1. Ir a Afiliaciones
2. Filtro "Registros Eliminados" → "Solo eliminados"
3. Revisar cuáles son muy antiguos o no se necesitan
4. Seleccionar los que se pueden borrar permanentemente
5. "Eliminar Permanentemente" en masa
6. Confirmar

**Recomendación:** Hacerlo cada 3-6 meses

### Revisar Logs de Eliminaciones

Para ver quién eliminó qué:
1. Ir al log de actividad del sistema
2. Filtrar por acción "deleted"
3. Ver usuario, fecha, y registro afectado

---

## 📊 Resumen Visual

```
┌──────────────────────────────────────────────┐
│           USUARIO NORMAL                      │
├──────────────────────────────────────────────┤
│                                               │
│  [Eliminar] → Eliminación Lógica             │
│               ↓                               │
│          Registro oculto                      │
│          Recuperable                          │
│               ↓                               │
│  [Restaurar] → Vuelve a lista                │
│                                               │
└──────────────────────────────────────────────┘

┌──────────────────────────────────────────────┐
│         ADMINISTRADOR (SSST)                  │
├──────────────────────────────────────────────┤
│                                               │
│  [Eliminar] → Eliminación Lógica             │
│               ↓                               │
│          Registro oculto                      │
│               ↓                               │
│  Opción 1: [Restaurar] → Vuelve              │
│                                               │
│  Opción 2: [Eliminar Permanentemente]        │
│               ↓                               │
│          ⚠️ ADVERTENCIA ⚠️                     │
│               ↓                               │
│          Borrado para siempre                 │
│          NO RECUPERABLE                       │
│                                               │
└──────────────────────────────────────────────┘
```

---

**Última actualización:** Noviembre 2025
**Versión:** 2.4
**Estado:** ✅ Implementado y Funcional
