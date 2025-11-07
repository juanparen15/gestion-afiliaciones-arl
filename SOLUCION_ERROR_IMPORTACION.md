# Solución: Error de Importación de Excel

## 🐛 Problema

Al intentar importar un archivo Excel, aparecía el siguiente error:

```
Error en la importación
Ocurrió un error: File [01K9F776E72YGVPZPQTEKHX1GJ.xlsx] does not exist and can therefore not be imported.
```

---

## ✅ Solución Implementada

### Causa del Error

Filament guarda los archivos subidos en un almacenamiento temporal, pero la ruta del archivo no se estaba resolviendo correctamente para la importación de Excel.

### Correcciones Aplicadas

#### 1. **Configuración del FileUpload**

Se agregaron configuraciones específicas para el componente FileUpload:

```php
Forms\Components\FileUpload::make('archivo')
    ->label('Archivo Excel')
    ->acceptedFileTypes([
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
    ])
    ->required()
    ->maxSize(10240)
    ->disk('local')                    // ✅ Nuevo: Usar disco local
    ->directory('temp-imports')        // ✅ Nuevo: Directorio específico
    ->visibility('private')            // ✅ Nuevo: Privado
    ->helperText('Formatos aceptados: .xlsx, .xls, .csv (Máximo 10MB)'),
```

#### 2. **Obtención de la Ruta Correcta**

Se modificó el código para obtener la ruta completa del archivo:

```php
// Obtener la ruta completa del archivo desde el storage
$filePath = storage_path('app/' . $data['archivo']);

$import = new AfiliacionesImport();
Excel::import($import, $filePath);
```

**Explicación:**
- `storage_path('app/')` → Ruta completa a `C:\laragon\www\gestion-afiliaciones-arl\storage\app\`
- `$data['archivo']` → Ruta relativa: `temp-imports/archivo.xlsx`
- `$filePath` → Ruta completa: `C:\laragon\www\gestion-afiliaciones-arl\storage\app\temp-imports\archivo.xlsx`

#### 3. **Limpieza de Archivos Temporales**

Se agregó código para eliminar el archivo temporal después de importar:

```php
// Limpiar archivo temporal después de importar
if (file_exists($filePath)) {
    unlink($filePath);
}
```

También en caso de error:

```php
catch (\Exception $e) {
    // Limpiar archivo temporal en caso de error
    if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }

    // Mostrar error...
}
```

#### 4. **Creación del Directorio**

Se creó el directorio `storage/app/temp-imports/` con un `.gitignore`:

```
storage/
└── app/
    └── temp-imports/
        └── .gitignore
```

Contenido del `.gitignore`:
```
*
!.gitignore
```

Esto asegura que:
- ✅ El directorio existe en el repositorio
- ✅ Los archivos temporales NO se suben a Git
- ✅ El directorio se mantiene limpio

---

## 🔄 Flujo Completo de Importación

### Paso 1: Usuario Sube el Archivo
```
Usuario → Selecciona archivo.xlsx → FileUpload
```

### Paso 2: Filament Guarda el Archivo
```
FileUpload → Guarda en storage/app/temp-imports/HASH.xlsx
```

### Paso 3: Sistema Importa
```
storage_path('app/temp-imports/HASH.xlsx') → Excel::import()
```

### Paso 4: Limpieza
```
unlink(archivo) → Archivo temporal eliminado
```

---

## 📁 Estructura de Archivos

### Antes (Problema):
```
El archivo se guardaba en un lugar temporal no predecible
```

### Después (Solución):
```
storage/
└── app/
    ├── public/
    ├── temp-imports/         ← Nuevo directorio
    │   ├── .gitignore
    │   └── [archivos temporales aquí]
    └── ...
```

---

## 🧪 Cómo Probar

1. **Ir a Afiliaciones** en el sistema
2. **Hacer clic en "Importar Excel"**
3. **Seleccionar un archivo** .xlsx, .xls o .csv
4. **Hacer clic en "Importar"**
5. ✅ **Debería importar correctamente** sin errores

### Si Hay Errores de Validación:
- Verás una notificación con los errores
- Podrás descargar el reporte de errores
- El archivo temporal se limpiará automáticamente

### Si la Importación es Exitosa:
- Verás notificación verde
- Los registros se importarán
- El archivo temporal se limpiará automáticamente

---

## 🛠️ Archivos Modificados

1. ✅ `app/Filament/Resources/AfiliacionResource.php`
   - Configuración de FileUpload
   - Obtención de ruta correcta
   - Limpieza de archivos temporales

2. ✅ `storage/app/temp-imports/.gitignore` (Nuevo)
   - Directorio para importaciones temporales

---

## 💡 Beneficios

✅ **Rutas consistentes** - Siempre en el mismo directorio
✅ **Limpieza automática** - No acumula archivos temporales
✅ **Compatible con Windows** - Funciona en Laragon
✅ **Privacidad** - Archivos privados, no accesibles vía web
✅ **Mantenible** - Fácil de depurar si hay problemas

---

## 🚨 Solución de Problemas

### Error: "Permission denied"

**Solución:** Verificar permisos del directorio
```bash
# En Linux/Mac
chmod -R 775 storage/app/temp-imports

# En Windows (Laragon)
# Normalmente no hay problemas de permisos
```

### Error: "Directory does not exist"

**Solución:** Crear el directorio manualmente
```bash
mkdir storage/app/temp-imports
```

### Error: "Disk [local] does not exist"

**Solución:** Verificar `config/filesystems.php`:
```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    // ...
],
```

---

## ✅ Estado Actual

🟢 **FUNCIONANDO CORRECTAMENTE**

El sistema de importación ahora:
- Guarda archivos en ubicación predecible
- Importa correctamente desde esa ubicación
- Limpia archivos temporales después de importar
- Muestra errores detallados si hay problemas

---

**Última actualización:** Noviembre 2025
**Versión:** 2.1
**Estado:** Resuelto ✅
