---
title: Modelos de Datos
description: Documentación técnica de los modelos Eloquent del sistema
---

## Diagrama de Relaciones

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    User     │     │ Dependencia │     │    Area     │
├─────────────┤     ├─────────────┤     ├─────────────┤
│ id          │     │ id          │     │ id          │
│ name        │     │ nombre      │     │ nombre      │
│ email       │◄────┤ codigo      │◄────┤ codigo      │
│ dependencia │     │ responsable │     │ dependencia │
│ area_id     │     │ activo      │     │ activo      │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │
       │    ┌──────────────┼───────────────────┘
       │    │              │
       ▼    ▼              ▼
┌─────────────────────────────────────┐
│           Afiliacion                │
├─────────────────────────────────────┤
│ id                                  │
│ nombre_contratista                  │
│ numero_documento                    │
│ numero_contrato                     │
│ dependencia_id (FK)                 │
│ area_id (FK)                        │
│ created_by (FK → User)              │
│ validated_by (FK → User)            │
│ estado                              │
│ pdf_arl                             │
└─────────────────────────────────────┘
```

---

## Modelo: Afiliacion

### Ubicación
`app/Models/Afiliacion.php`

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | PK autoincrement |
| `nombre_contratista` | string | Nombre completo |
| `tipo_documento` | enum | CC, CE, PP, TI, NIT |
| `numero_documento` | string | Único |
| `email_contratista` | string | Email de contacto |
| `telefono_contratista` | string | Teléfono |
| `fecha_nacimiento` | date | Fecha de nacimiento |
| `barrio` | string | Barrio de residencia |
| `direccion_residencia` | string | Dirección completa |
| `eps` | string | Nombre de la EPS |
| `afp` | string | Nombre del AFP |
| `numero_contrato` | string | Identificador del contrato |
| `objeto_contractual` | text | Descripción del contrato |
| `valor_contrato` | decimal(15,2) | Valor total |
| `honorarios_mensual` | decimal(15,2) | Pago mensual |
| `ibc` | decimal(15,2) | Ingreso Base Cotización |
| `meses_contrato` | integer | Duración en meses |
| `dias_contrato` | integer | Días adicionales |
| `fecha_inicio` | date | Inicio del contrato |
| `fecha_fin` | date | Fin del contrato |
| `nombre_arl` | string | Nombre de la ARL |
| `tipo_riesgo` | enum | I, II, III, IV, V |
| `numero_afiliacion_arl` | string | Código de afiliación |
| `fecha_afiliacion_arl` | date | Fecha de afiliación |
| `fecha_terminacion_afiliacion` | date | Fin de cobertura |
| `pdf_arl` | string | Ruta del certificado |
| `contrato_pdf_o_word` | string | Ruta del contrato |
| `dependencia_id` | bigint | FK a dependencias |
| `area_id` | bigint | FK a areas |
| `created_by` | bigint | FK a users (creador) |
| `validated_by` | bigint | FK a users (validador) |
| `estado` | enum | pendiente, validado, rechazado |
| `observaciones` | text | Notas adicionales |
| `motivo_rechazo` | text | Motivo si rechazado |
| `fecha_validacion` | timestamp | Cuándo se validó |
| `created_at` | timestamp | Creación |
| `updated_at` | timestamp | Última actualización |
| `deleted_at` | timestamp | Soft delete |

### Traits

```php
use HasFactory;
use SoftDeletes;
use LogsActivity;
```

### Fillable

```php
protected $fillable = [
    'nombre_contratista',
    'tipo_documento',
    'numero_documento',
    'email_contratista',
    'telefono_contratista',
    'fecha_nacimiento',
    'barrio',
    'direccion_residencia',
    'eps',
    'afp',
    'numero_contrato',
    'objeto_contractual',
    'valor_contrato',
    'honorarios_mensual',
    'ibc',
    'meses_contrato',
    'dias_contrato',
    'fecha_inicio',
    'fecha_fin',
    'nombre_arl',
    'tipo_riesgo',
    'numero_afiliacion_arl',
    'fecha_afiliacion_arl',
    'fecha_terminacion_afiliacion',
    'pdf_arl',
    'contrato_pdf_o_word',
    'dependencia_id',
    'area_id',
    'created_by',
    'validated_by',
    'estado',
    'observaciones',
    'motivo_rechazo',
    'fecha_validacion',
];
```

### Casts

```php
protected $casts = [
    'fecha_nacimiento' => 'date',
    'fecha_inicio' => 'date',
    'fecha_fin' => 'date',
    'fecha_afiliacion_arl' => 'date',
    'fecha_terminacion_afiliacion' => 'date',
    'fecha_validacion' => 'datetime',
    'valor_contrato' => 'decimal:2',
    'honorarios_mensual' => 'decimal:2',
    'ibc' => 'decimal:2',
];
```

### Relaciones

```php
// Pertenece a una dependencia
public function dependencia()
{
    return $this->belongsTo(Dependencia::class);
}

// Pertenece a un área
public function area()
{
    return $this->belongsTo(Area::class);
}

// Usuario que creó
public function creador()
{
    return $this->belongsTo(User::class, 'created_by');
}

// Usuario que validó
public function validador()
{
    return $this->belongsTo(User::class, 'validated_by');
}

// Archivos adjuntos
public function archivos()
{
    return $this->hasMany(ArchivoAfiliacion::class);
}
```

### Scopes

```php
// Afiliaciones pendientes
public function scopePendiente($query)
{
    return $query->where('estado', 'pendiente');
}

// Afiliaciones validadas
public function scopeValidado($query)
{
    return $query->where('estado', 'validado');
}

// Afiliaciones rechazadas
public function scopeRechazado($query)
{
    return $query->where('estado', 'rechazado');
}

// Contratos vigentes
public function scopeVigente($query)
{
    return $query->where('fecha_fin', '>=', now());
}

// Por vencer en X días
public function scopePorVencer($query, $dias = 30)
{
    return $query->whereBetween('fecha_fin', [
        now(),
        now()->addDays($dias)
    ]);
}
```

### Uso de Scopes

```php
// Obtener pendientes
$pendientes = Afiliacion::pendiente()->get();

// Obtener vigentes de una dependencia
$vigentes = Afiliacion::vigente()
    ->where('dependencia_id', $dependenciaId)
    ->get();

// Combinar scopes
$urgentes = Afiliacion::pendiente()
    ->porVencer(15)
    ->get();
```

---

## Modelo: User

### Ubicación
`app/Models/User.php`

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | PK |
| `name` | string | Nombre completo |
| `email` | string | Email único (login) |
| `correo_institucional` | string | Email institucional |
| `cargo` | string | Puesto de trabajo |
| `dependencia_id` | bigint | FK a dependencias |
| `area_id` | bigint | FK a areas |
| `password` | string | Contraseña hasheada |
| `email_verified_at` | timestamp | Verificación |
| `remember_token` | string | Token de sesión |
| `created_at` | timestamp | Creación |
| `updated_at` | timestamp | Actualización |

### Traits

```php
use HasFactory;
use Notifiable;
use HasRoles;  // Spatie Permission
use FilamentUser;
```

### Relaciones

```php
// Dependencia del usuario
public function dependencia()
{
    return $this->belongsTo(Dependencia::class);
}

// Área del usuario
public function area()
{
    return $this->belongsTo(Area::class);
}

// Afiliaciones creadas por este usuario
public function afiliacionesCreadas()
{
    return $this->hasMany(Afiliacion::class, 'created_by');
}

// Afiliaciones validadas por este usuario
public function afiliacionesValidadas()
{
    return $this->hasMany(Afiliacion::class, 'validated_by');
}
```

### Métodos de Filament

```php
// Puede acceder al panel
public function canAccessPanel(Panel $panel): bool
{
    return true; // O lógica personalizada
}
```

---

## Modelo: Dependencia

### Ubicación
`app/Models/Dependencia.php`

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | PK |
| `nombre` | string | Nombre de la dependencia |
| `codigo` | string | Código único |
| `descripcion` | text | Descripción |
| `responsable` | string | Nombre del responsable |
| `email` | string | Email de contacto |
| `telefono` | string | Teléfono |
| `activo` | boolean | Si está activa |
| `created_at` | timestamp | Creación |
| `updated_at` | timestamp | Actualización |

### Traits

```php
use HasFactory;
use LogsActivity;
```

### Relaciones

```php
// Usuarios de esta dependencia
public function usuarios()
{
    return $this->hasMany(User::class);
}

// Áreas de esta dependencia
public function areas()
{
    return $this->hasMany(Area::class);
}

// Afiliaciones de esta dependencia
public function afiliaciones()
{
    return $this->hasMany(Afiliacion::class);
}
```

### Scopes

```php
// Solo dependencias activas
public function scopeActivo($query)
{
    return $query->where('activo', true);
}
```

---

## Modelo: Area

### Ubicación
`app/Models/Area.php`

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | PK |
| `dependencia_id` | bigint | FK a dependencias |
| `nombre` | string | Nombre del área |
| `codigo` | string | Código único |
| `descripcion` | text | Descripción |
| `responsable` | string | Nombre del responsable |
| `email` | string | Email de contacto |
| `telefono` | string | Teléfono |
| `activo` | boolean | Si está activa |
| `created_at` | timestamp | Creación |
| `updated_at` | timestamp | Actualización |

### Relaciones

```php
// Dependencia padre
public function dependencia()
{
    return $this->belongsTo(Dependencia::class);
}

// Usuarios de esta área
public function usuarios()
{
    return $this->hasMany(User::class);
}

// Afiliaciones de esta área
public function afiliaciones()
{
    return $this->hasMany(Afiliacion::class);
}
```

---

## Modelo: ArchivoAfiliacion

### Ubicación
`app/Models/ArchivoAfiliacion.php`

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | PK |
| `afiliacion_id` | bigint | FK a afiliaciones |
| `nombre_original` | string | Nombre original del archivo |
| `nombre_archivo` | string | Nombre generado |
| `ruta` | string | Ruta de almacenamiento |
| `tipo_archivo` | string | Tipo (PDF, DOC, etc.) |
| `mime_type` | string | Tipo MIME |
| `tamano` | integer | Tamaño en bytes |
| `tipo_documento` | string | Tipo de documento |
| `descripcion` | text | Descripción |
| `uploaded_by` | bigint | FK a users |
| `created_at` | timestamp | Creación |
| `updated_at` | timestamp | Actualización |

### Relaciones

```php
// Afiliación a la que pertenece
public function afiliacion()
{
    return $this->belongsTo(Afiliacion::class);
}

// Usuario que subió el archivo
public function uploader()
{
    return $this->belongsTo(User::class, 'uploaded_by');
}
```

---

## Consultas Comunes

### Estadísticas del Dashboard

```php
// Total por estado
$stats = [
    'total' => Afiliacion::count(),
    'pendientes' => Afiliacion::pendiente()->count(),
    'validadas' => Afiliacion::validado()->count(),
    'rechazadas' => Afiliacion::rechazado()->count(),
    'vigentes' => Afiliacion::vigente()->count(),
    'porVencer' => Afiliacion::porVencer(30)->count(),
];
```

### Por Dependencia

```php
// Afiliaciones agrupadas por dependencia
$porDependencia = Afiliacion::select('dependencia_id', DB::raw('count(*) as total'))
    ->groupBy('dependencia_id')
    ->with('dependencia:id,nombre')
    ->get();
```

### Con Relaciones

```php
// Cargar afiliaciones con todas las relaciones
$afiliaciones = Afiliacion::with([
    'dependencia',
    'area',
    'creador',
    'validador'
])->get();
```

### Filtrado Complejo

```php
// Afiliaciones pendientes de una dependencia, por vencer
$urgentes = Afiliacion::query()
    ->pendiente()
    ->where('dependencia_id', $dependenciaId)
    ->porVencer(15)
    ->orderBy('fecha_fin')
    ->get();
```

---

## Próximos Pasos

- [Recursos Filament](/docs/tecnica/recursos-filament/)
- [Eventos y Listeners](/docs/tecnica/eventos/)
