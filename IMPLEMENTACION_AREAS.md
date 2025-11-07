# Implementación del Sistema de Áreas

## ✅ Completado

### 1. Modelo y Migraciones de Áreas
- ✅ Creado modelo `Area` con `LogsActivity`
- ✅ Creada migración `create_areas_table` con campos:
  - dependencia_id (FK a dependencias)
  - nombre, codigo (único), descripcion
  - responsable, email, telefono
  - activo (boolean)
- ✅ Creada migración `add_area_id_to_users_table`
- ✅ Creada migración `add_area_id_to_afiliaciones_table`
- ✅ Migraciones ejecutadas exitosamente

### 2. Relaciones de Modelos
- ✅ **Area**: Relaciones con Dependencia, Usuarios y Afiliaciones
- ✅ **User**: Agregado campo `area_id` y relación `area()`
- ✅ **Afiliacion**: Agregado campo `area_id` y relación `area()`
- ✅ **Dependencia**: Agregada relación `areas()`

### 3. AreaResource
- ✅ Creado `AreaResource` completo con:
  - Formulario con secciones organizadas
  - Tabla con columnas filtables
  - Contadores de usuarios y afiliaciones por área
  - Filtros por dependencia y estado
  - Navegación en grupo "Administración"

### 4. Policy
- ✅ Creado `AreaPolicy`

---

## 📋 Pendiente de Implementación

### 1. Modificar UserResource para Áreas

**Archivo:** `app/Filament/Resources/UserResource.php`

**Agregar en el formulario después del campo `dependencia_id`:**

```php
Forms\Components\Select::make('area_id')
    ->label('Área')
    ->relationship('area', 'nombre', function ($query, $get) {
        $dependenciaId = $get('dependencia_id');
        if ($dependenciaId) {
            return $query->where('dependencia_id', $dependenciaId)->where('activo', true);
        }
        return $query->where('activo', true);
    })
    ->searchable()
    ->preload()
    ->native(false)
    ->helperText('Seleccione primero una dependencia')
    ->disabled(fn($get) => !$get('dependencia_id')),
```

**Agregar en la tabla:**

```php
Tables\Columns\TextColumn::make('area.nombre')
    ->label('Área')
    ->searchable()
    ->sortable()
    ->badge()
    ->color('success')
    ->toggleable(),
```

**Agregar en filtros:**

```php
Tables\Filters\SelectFilter::make('area_id')
    ->label('Área')
    ->relationship('area', 'nombre')
    ->searchable()
    ->preload()
    ->native(false),
```

---

### 2. Modificar AfiliacionResource para Áreas

**Archivo:** `app/Filament/Resources/AfiliacionResource.php`

**Agregar en la sección "Información del Contrato" después del campo `dependencia_id`:**

```php
Forms\Components\Select::make('area_id')
    ->label('Área')
    ->relationship('area', 'nombre', function ($query, $get) {
        $dependenciaId = $get('dependencia_id');
        if ($dependenciaId) {
            return $query->where('dependencia_id', $dependenciaId)->where('activo', true);
        }
        return $query->where('activo', true);
    })
    ->searchable()
    ->preload()
    ->native(false)
    ->helperText('Seleccione primero una dependencia')
    ->disabled(fn($get) => !$get('dependencia_id')),
```

**Agregar en la tabla después de `dependencia.nombre`:**

```php
Tables\Columns\TextColumn::make('area.nombre')
    ->label('Área')
    ->searchable()
    ->sortable()
    ->badge()
    ->color('success')
    ->toggleable(),
```

**Agregar en filtros:**

```php
Tables\Filters\SelectFilter::make('area_id')
    ->label('Área')
    ->relationship('area', 'nombre')
    ->searchable()
    ->preload()
    ->native(false),
```

**Modificar método `mutateFormDataBeforeCreate` en `CreateAfiliacion.php`:**

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['created_by'] = Auth::id();
    $data['estado'] = $data['estado'] ?? 'pendiente';

    // Asignar área del usuario si no se especificó
    if (!isset($data['area_id']) && Auth::user()?->area_id) {
        $data['area_id'] = Auth::user()->area_id;
    }

    return $data;
}
```

**Actualizar filtros según área del usuario (línea ~586):**

```php
// Aplicar filtro de dependencia si no es super_admin
if (!Auth::user()?->hasRole('super_admin')) {
    // Si el usuario tiene área, filtrar por área
    if (Auth::user()?->area_id) {
        $query->where('area_id', Auth::user()->area_id);
    } else {
        // Si solo tiene dependencia, filtrar por dependencia
        $query->where('dependencia_id', Auth::user()->dependencia_id);
    }
}
```

---

### 3. Actualizar AfiliacionesImport

**Archivo:** `app/Imports/AfiliacionesImport.php`

**Modificar el método `model()` para incluir área:**

Después de buscar la dependencia (línea ~28), agregar:

```php
// Buscar o asignar área
$area = null;
if ($dependencia && isset($row['area'])) {
    $area = \App\Models\Area::where('dependencia_id', $dependencia->id)
        ->where(function($q) use ($row) {
            $q->where('nombre', 'like', '%' . trim($row['area']) . '%')
              ->orWhere('codigo', trim($row['area']));
        })
        ->first();
}
```

**Agregar en el return del modelo (línea ~63):**

```php
'area_id' => $area?->id,
```

---

### 4. Crear Export con Plantilla de Excel

**Crear archivo:** `app/Exports/AfiliacionesTemplateExport.php`

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AfiliacionesTemplateExport implements WithHeadings, WithStyles, WithTitle
{
    public function headings(): array
    {
        return [
            [
                'SISTEMA DE GESTIÓN DE AFILIACIONES ARL',
            ],
            [
                'No. CONTRATO',
                'OBJETO CONTRATO',
                'CC CONTRATISTA',
                'CONTRATISTA',
                'VALOR DEL CONTRATO',
                'MESES',
                'DIAS',
                'Honorarios mensual',
                'IBC',
                'Fecha ingreso A partir de Acta inicio',
                'Fecha retiro',
                'Secretaría',
                'Área',
                'Fecha de Nacimiento',
                'Nivel de riesgo',
                'No. Celular',
                'Barrio',
                'Dirección Residencia',
                'EPS',
                'AFP',
                'Dirección de correo Electronica',
                'FECHA DE AFILIACION',
                'FECHA TERMIANCION AFILIACION',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFA500'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Plantilla Afiliaciones';
    }
}
```

**Crear archivo:** `app/Exports/AfiliacionesExport.php`

```php
<?php

namespace App\Exports;

use App\Models\Afiliacion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AfiliacionesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query ?? Afiliacion::query()->with(['dependencia', 'area']);
    }

    public function headings(): array
    {
        return [
            [
                'SISTEMA DE GESTIÓN DE AFILIACIONES ARL',
            ],
            [
                'No. CONTRATO',
                'OBJETO CONTRATO',
                'CC CONTRATISTA',
                'CONTRATISTA',
                'VALOR DEL CONTRATO',
                'MESES',
                'DIAS',
                'Honorarios mensual',
                'IBC',
                'Fecha ingreso A partir de Acta inicio',
                'Fecha retiro',
                'Secretaría',
                'Área',
                'Fecha de Nacimiento',
                'Nivel de riesgo',
                'No. Celular',
                'Barrio',
                'Dirección Residencia',
                'EPS',
                'AFP',
                'Dirección de correo Electronica',
                'FECHA DE AFILIACION',
                'FECHA TERMIANCION AFILIACION',
                'ARL',
                'Estado',
            ],
        ];
    }

    public function map($afiliacion): array
    {
        return [
            $afiliacion->numero_contrato,
            $afiliacion->objeto_contractual,
            $afiliacion->numero_documento,
            $afiliacion->nombre_contratista,
            $afiliacion->valor_contrato,
            $afiliacion->meses_contrato,
            $afiliacion->dias_contrato,
            $afiliacion->honorarios_mensual,
            $afiliacion->ibc,
            $afiliacion->fecha_inicio?->format('d/m/Y'),
            $afiliacion->fecha_fin?->format('d/m/Y'),
            $afiliacion->dependencia?->nombre,
            $afiliacion->area?->nombre,
            $afiliacion->fecha_nacimiento?->format('d/m/Y'),
            $afiliacion->tipo_riesgo,
            $afiliacion->telefono_contratista,
            $afiliacion->barrio,
            $afiliacion->direccion_residencia,
            $afiliacion->eps,
            $afiliacion->afp,
            $afiliacion->email_contratista,
            $afiliacion->fecha_afiliacion_arl?->format('d/m/Y'),
            $afiliacion->fecha_terminacion_afiliacion?->format('d/m/Y'),
            $afiliacion->nombre_arl,
            match($afiliacion->estado) {
                'pendiente' => 'Pendiente',
                'validado' => 'Validado',
                'rechazado' => 'Rechazado',
                default => $afiliacion->estado,
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFA500'],
                ],
            ],
        ];
    }
}
```

---

### 5. Agregar Botones de Exportación en AfiliacionResource

**En el método `table()`, agregar en `->headerActions([])` antes del botón de importar:**

```php
Tables\Actions\Action::make('descargar_plantilla')
    ->label('Descargar Plantilla')
    ->icon('heroicon-o-arrow-down-tray')
    ->color('info')
    ->action(function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AfiliacionesTemplateExport(),
            'plantilla_afiliaciones_arl.xlsx'
        );
    }),

Tables\Actions\Action::make('exportar_todo')
    ->label('Exportar Todo')
    ->icon('heroicon-o-document-arrow-down')
    ->color('success')
    ->visible(fn() => Auth::user()->hasRole('SSST'))
    ->action(function () {
        $query = \App\Models\Afiliacion::query()->with(['dependencia', 'area']);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AfiliacionesExport($query),
            'afiliaciones_arl_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }),
```

---

### 6. Actualizar Seeder con Áreas de Ejemplo

**Archivo:** `database/seeders/RolesAndPermissionsSeeder.php`

**Después de crear las dependencias, agregar:**

```php
// Crear áreas para Secretaría General
$sistemasDep = Dependencia::where('codigo', 'SIS')->first();
\App\Models\Area::create([
    'dependencia_id' => $sistemasDep->id,
    'nombre' => 'Área de Sistemas',
    'codigo' => 'SIS-SIS',
    'descripcion' => 'Gestión de sistemas de información',
    'activo' => true,
]);

\App\Models\Area::create([
    'dependencia_id' => $sistemasDep->id,
    'nombre' => 'Área de Contratación',
    'codigo' => 'SIS-CON',
    'descripcion' => 'Gestión de contratos',
    'activo' => true,
]);

\App\Models\Area::create([
    'dependencia_id' => $sistemasDep->id,
    'nombre' => 'Área de Archivo',
    'codigo' => 'SIS-ARC',
    'descripcion' => 'Gestión documental y archivo',
    'activo' => true,
]);

\App\Models\Area::create([
    'dependencia_id' => $sistemasDep->id,
    'nombre' => 'Área de Almacén',
    'codigo' => 'SIS-ALM',
    'descripcion' => 'Gestión de almacén e inventarios',
    'activo' => true,
]);

// Agregar áreas para otras dependencias según necesidad
```

---

## 🚀 Pasos para Completar

1. **Ejecutar seeder actualizado:**
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

2. **Modificar UserResource** (agregar campo área)

3. **Modificar AfiliacionResource** (agregar campo área y filtros)

4. **Crear las clases Export** (AfiliacionesTemplateExport y AfiliacionesExport)

5. **Agregar botones de exportación** en AfiliacionResource

6. **Actualizar AfiliacionesImport** para incluir columna de área

7. **Probar el flujo completo:**
   - Crear áreas en el panel admin
   - Asignar áreas a usuarios
   - Crear afiliaciones con áreas
   - Exportar plantilla vacía
   - Importar con área incluida
   - Exportar todo (solo SSST)

---

## 📊 Estructura Final

```
Dependencias (Ej: Secretaría General)
    └── Áreas (Ej: Sistemas, Contratación, Archivo, Almacén)
        └── Usuarios (Asignados a un área)
            └── Afiliaciones (Creadas por usuario, asignadas a su área)
```

## 🔐 Permisos

- **Super Admin**: Ve y gestiona todo
- **SSST**: Ve todas las afiliaciones, puede exportar todo
- **Dependencia**: Ve afiliaciones de su dependencia (todas las áreas)
- **Usuario con Área**: Ve solo afiliaciones de su área específica

---

**Última actualización:** 06/11/2025
