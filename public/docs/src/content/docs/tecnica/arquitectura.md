---
title: Arquitectura del Sistema
description: Documentación técnica de la arquitectura del Sistema de Gestión de Afiliaciones ARL
---

## Stack Tecnológico

### Backend

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | 8.2+ | Lenguaje de programación |
| Laravel | 12.x | Framework web |
| FilamentPHP | 3.x | Panel de administración |
| MySQL | 8.0+ | Base de datos |
| Composer | 2.x | Gestión de dependencias |

### Frontend

| Tecnología | Propósito |
|------------|-----------|
| Blade | Templates |
| Alpine.js | Interactividad (via Filament) |
| Tailwind CSS | Estilos (via Filament) |
| Vite | Compilación de assets |

### Paquetes Principales

```json
{
  "filament/filament": "Panel admin completo",
  "spatie/laravel-permission": "Roles y permisos",
  "spatie/laravel-activitylog": "Auditoría",
  "maatwebsite/excel": "Import/Export Excel",
  "bezhansalleh/filament-shield": "UI para permisos",
  "leandrocfe/filament-apex-charts": "Gráficos",
  "sentry/sentry-laravel": "Monitoreo de errores"
}
```

---

## Estructura de Directorios

```
gestion-afiliaciones-arl/
├── app/
│   ├── Events/                 # Eventos del sistema
│   ├── Exports/                # Clases de exportación Excel
│   ├── Filament/
│   │   ├── Pages/              # Páginas custom de Filament
│   │   ├── Resources/          # Recursos CRUD
│   │   │   ├── AfiliacionResource/
│   │   │   │   └── Pages/      # Páginas del recurso
│   │   │   ├── UserResource/
│   │   │   ├── DependenciaResource/
│   │   │   └── AreaResource/
│   │   └── Widgets/            # Widgets del dashboard
│   ├── Http/
│   │   └── Controllers/        # Controladores web
│   ├── Imports/                # Clases de importación Excel
│   ├── Listeners/              # Listeners de eventos
│   ├── Mail/                   # Clases de email
│   ├── Models/                 # Modelos Eloquent
│   ├── Observers/              # Observers de modelos
│   ├── Policies/               # Policies de autorización
│   └── Providers/              # Service providers
│       └── Filament/
│           └── AdminPanelProvider.php
├── bootstrap/
├── config/                     # Archivos de configuración
├── database/
│   ├── factories/              # Model factories
│   ├── migrations/             # Migraciones de BD
│   └── seeders/                # Seeders de datos
├── public/                     # Archivos públicos
├── resources/
│   ├── css/
│   ├── js/
│   └── views/                  # Vistas Blade
│       └── emails/             # Templates de email
├── routes/
│   ├── web.php
│   └── console.php
├── storage/
│   ├── app/                    # Archivos subidos
│   ├── framework/
│   └── logs/
└── tests/
```

---

## Patrones de Diseño

### MVC (Model-View-Controller)

Laravel sigue el patrón MVC:
- **Models**: `app/Models/` - Lógica de negocio y datos
- **Views**: `resources/views/` - Presentación (Blade)
- **Controllers**: Filament Resources manejan esta capa

### Repository Pattern (Implícito)

Eloquent ORM actúa como repository:
```php
// Consultas encapsuladas en el modelo
Afiliacion::pendiente()->get();
Afiliacion::vigente()->get();
```

### Observer Pattern

Para eventos del ciclo de vida:
```php
// AfiliacionObserver
public function created(Afiliacion $afiliacion)
{
    if ($afiliacion->estado === 'pendiente') {
        event(new AfiliacionCreada($afiliacion));
    }
}
```

### Event-Driven Architecture

Eventos y listeners desacoplados:
```
AfiliacionCreada (Event)
    └── EnviarNotificacionNuevaAfiliacion (Listener)
        └── NuevaAfiliacionMail (Mailable)
```

---

## Flujo de Request

```
1. Request HTTP
       │
       ▼
2. Middleware (auth, csrf, etc.)
       │
       ▼
3. Routing (Filament maneja /admin/*)
       │
       ▼
4. Filament Resource
       │
       ├── Form Schema (crear/editar)
       ├── Table Schema (listar)
       └── Actions (validar, rechazar, etc.)
       │
       ▼
5. Policy Check (autorización)
       │
       ▼
6. Model (Eloquent)
       │
       ├── Observers
       ├── Events
       └── Activity Log
       │
       ▼
7. Database
       │
       ▼
8. Response
```

---

## Capas de la Aplicación

### Capa de Presentación

**Filament Resources**
```php
// app/Filament/Resources/AfiliacionResource.php
class AfiliacionResource extends Resource
{
    public static function form(Form $form): Form
    {
        // Define el formulario de creación/edición
    }

    public static function table(Table $table): Table
    {
        // Define la tabla de listado
    }
}
```

### Capa de Negocio

**Models con lógica**
```php
// app/Models/Afiliacion.php
class Afiliacion extends Model
{
    // Scopes para consultas
    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    // Relaciones
    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class);
    }

    // Mutators/Accessors
    protected function ibc(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ?? $this->honorarios_mensual * 0.4,
        );
    }
}
```

### Capa de Datos

**Eloquent ORM**
- Migraciones para estructura
- Seeders para datos iniciales
- Factories para testing

---

## Sistema de Autenticación

### Filament Auth

```php
// AdminPanelProvider.php
->login()
->authMiddleware([
    Authenticate::class,
])
```

### Sesiones

Por defecto usa `file` driver:
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## Sistema de Autorización

### Spatie Permission

Roles y permisos almacenados en BD:
```
roles
├── super_admin
├── SSST
└── Dependencia

permissions
├── view_any_afiliacion
├── create_afiliacion
├── update_afiliacion
└── ...
```

### Policies

```php
// app/Policies/AfiliacionPolicy.php
public function update(User $user, Afiliacion $afiliacion): bool
{
    return $user->can('update_afiliacion');
}
```

### Control por Dependencia

```php
// En el Resource
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    if (!auth()->user()->hasRole(['super_admin', 'SSST'])) {
        $query->where('dependencia_id', auth()->user()->dependencia_id);
    }

    return $query;
}
```

---

## Sistema de Eventos

### Eventos Definidos

```php
// app/Events/AfiliacionCreada.php
class AfiliacionCreada
{
    public Afiliacion $afiliacion;

    public function __construct(Afiliacion $afiliacion)
    {
        $this->afiliacion = $afiliacion;
    }
}
```

### Listeners

```php
// app/Listeners/EnviarNotificacionNuevaAfiliacion.php
public function handle(AfiliacionCreada $event): void
{
    $usuariosSSST = User::role('SSST')->get();

    foreach ($usuariosSSST as $usuario) {
        Mail::to($usuario->email)
            ->send(new NuevaAfiliacionMail($event->afiliacion));
    }
}
```

### Registro

```php
// EventServiceProvider
protected $listen = [
    AfiliacionCreada::class => [
        EnviarNotificacionNuevaAfiliacion::class,
    ],
];
```

---

## Sistema de Importación/Exportación

### Importación

```php
// app/Imports/AfiliacionesImport.php
class AfiliacionesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return Afiliacion::updateOrCreate(
            ['numero_documento' => $row['cc_contratista']],
            [
                'nombre_contratista' => $row['contratista'],
                // ... más campos
            ]
        );
    }

    public function rules(): array
    {
        return [
            'no_contrato' => 'required',
            'email' => 'nullable|email',
        ];
    }
}
```

### Exportación

```php
// app/Exports/AfiliacionesExport.php
class AfiliacionesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Afiliacion::query()
            ->with(['dependencia', 'area']);
    }

    public function map($afiliacion): array
    {
        return [
            $afiliacion->numero_contrato,
            $afiliacion->nombre_contratista,
            // ... más campos
        ];
    }
}
```

---

## Sistema de Auditoría

### Activity Log

```php
// En el modelo
use LogsActivity;

protected static $logAttributes = ['*'];
protected static $logOnlyDirty = true;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
}
```

### Consultar Logs

```php
// Ver actividad de un modelo
$afiliacion->activities;

// Ver actividad de un usuario
Activity::causedBy($user)->get();
```

---

## Almacenamiento de Archivos

### Configuración

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### Uso en Filament

```php
Forms\Components\FileUpload::make('pdf_arl')
    ->disk('public')
    ->directory('afiliaciones/pdfs')
    ->acceptedFileTypes(['application/pdf'])
    ->maxSize(10240)
```

---

## Cacheo

### Drivers Disponibles

```env
CACHE_DRIVER=file    # Desarrollo
CACHE_DRIVER=redis   # Producción
```

### Uso

```php
// Cachear consultas costosas
$stats = Cache::remember('afiliaciones_stats', 3600, function () {
    return [
        'total' => Afiliacion::count(),
        'pendientes' => Afiliacion::pendiente()->count(),
    ];
});
```

---

## Colas

### Configuración

```env
QUEUE_CONNECTION=sync      # Desarrollo (síncrono)
QUEUE_CONNECTION=database  # Producción
```

### Uso

```php
// Dispatch a la cola
SendEmailJob::dispatch($afiliacion)->onQueue('emails');

// Ejecutar worker
php artisan queue:work --queue=emails
```

---

## Testing

### Estructura

```
tests/
├── Feature/          # Tests de integración
│   ├── AfiliacionTest.php
│   └── UserTest.php
├── Unit/             # Tests unitarios
│   └── AfiliacionModelTest.php
└── TestCase.php
```

### Ejemplo

```php
public function test_dependencia_can_create_afiliacion()
{
    $user = User::factory()->create();
    $user->assignRole('Dependencia');

    $this->actingAs($user)
         ->post('/admin/afiliaciones', $data)
         ->assertSuccessful();
}
```

---

## Seguridad

### Middleware

```php
// AdminPanelProvider.php
->middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AuthenticateSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    SubstituteBindings::class,
    DisableBladeIconComponents::class,
    DispatchServingFilamentEvent::class,
])
```

### Validación de Entrada

```php
// En el formulario de Filament
TextInput::make('email')
    ->email()
    ->required()
    ->unique(ignoreRecord: true)
```

### Hashing

```php
// Contraseñas automáticamente hasheadas
protected $casts = [
    'password' => 'hashed',
];
```

---

## Próximos Pasos

- [Modelos de Datos](/docs/tecnica/modelos/)
- [Recursos Filament](/docs/tecnica/recursos-filament/)
