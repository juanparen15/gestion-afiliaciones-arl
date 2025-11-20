---
title: Políticas y Permisos
description: Documentación técnica del sistema de autorización
---

## Sistema de Permisos

El sistema usa **Spatie Laravel Permission** para gestionar roles y permisos, integrado con **Filament Shield** para la interfaz de administración.

---

## Estructura

```
Roles (roles)
├── super_admin
├── SSST
└── Dependencia

Permisos (permissions)
├── view_any_afiliacion
├── view_afiliacion
├── create_afiliacion
├── update_afiliacion
├── delete_afiliacion
├── force_delete_afiliacion
├── restore_afiliacion
└── ... (para cada recurso)
```

---

## Roles Definidos

### super_admin

**Descripción**: Acceso total al sistema

**Permisos**: Todos

**Uso**: Administradores técnicos del sistema

### SSST

**Descripción**: Seguridad y Salud en el Trabajo

**Permisos**:
- Ver todas las afiliaciones
- Crear, editar, eliminar afiliaciones
- Validar y rechazar
- Importar/exportar Excel
- Restaurar eliminados
- Eliminar permanentemente

**Uso**: Equipo que valida afiliaciones

### Dependencia

**Descripción**: Usuarios de las secretarías

**Permisos**:
- Ver afiliaciones de su dependencia
- Crear afiliaciones
- Editar sus propias afiliaciones (pendientes)
- Cargar documentos

**Restricciones**:
- No puede validar ni rechazar
- No puede ver otras dependencias
- No puede eliminar permanentemente

---

## Policies

### AfiliacionPolicy

**Ubicación**: `app/Policies/AfiliacionPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Afiliacion;
use Illuminate\Auth\Access\HandlesAuthorization;

class AfiliacionPolicy
{
    use HandlesAuthorization;

    /**
     * Ver listado
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_afiliacion');
    }

    /**
     * Ver detalle
     */
    public function view(User $user, Afiliacion $afiliacion): bool
    {
        return $user->can('view_afiliacion');
    }

    /**
     * Crear
     */
    public function create(User $user): bool
    {
        return $user->can('create_afiliacion');
    }

    /**
     * Editar
     */
    public function update(User $user, Afiliacion $afiliacion): bool
    {
        return $user->can('update_afiliacion');
    }

    /**
     * Eliminar (soft delete)
     */
    public function delete(User $user, Afiliacion $afiliacion): bool
    {
        return $user->can('delete_afiliacion');
    }

    /**
     * Restaurar
     */
    public function restore(User $user, Afiliacion $afiliacion): bool
    {
        return $user->can('restore_afiliacion');
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(User $user, Afiliacion $afiliacion): bool
    {
        return $user->can('force_delete_afiliacion');
    }

    /**
     * Eliminar múltiples
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_afiliacion');
    }

    /**
     * Restaurar múltiples
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_afiliacion');
    }

    /**
     * Eliminar permanentemente múltiples
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_afiliacion');
    }
}
```

### Registro de Policy

**Ubicación**: `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    Afiliacion::class => AfiliacionPolicy::class,
];
```

---

## Filament Shield

### Instalación

```bash
composer require bezhansalleh/filament-shield
php artisan shield:install
```

### Generar Permisos

```bash
# Generar permisos para todos los recursos
php artisan shield:generate --all

# Generar para un recurso específico
php artisan shield:generate --resource=AfiliacionResource
```

### Integración con Resource

```php
// En el Resource
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class AfiliacionResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
        ];
    }
}
```

---

## Control de Acceso en el Resource

### Filtrar por Dependencia

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()
        ->withoutGlobalScopes([SoftDeletingScope::class]);

    $user = auth()->user();

    // Admin y SSST ven todo
    if ($user->hasRole(['super_admin', 'SSST'])) {
        return $query;
    }

    // Dependencia solo ve su dependencia
    return $query->where('dependencia_id', $user->dependencia_id);
}
```

### Ocultar Acciones por Rol

```php
// En las actions
Action::make('validar')
    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'SSST']))

// En los campos del formulario
Select::make('estado')
    ->disabled(fn () => !auth()->user()->hasRole(['super_admin', 'SSST']))

// En columnas de la tabla
TextColumn::make('created_by')
    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'SSST']))
```

### Ocultar Recursos del Menú

```php
// En el Resource
public static function shouldRegisterNavigation(): bool
{
    return auth()->user()->can('view_any_afiliacion');
}
```

---

## Verificar Permisos en Código

### En Controladores

```php
public function show(Afiliacion $afiliacion)
{
    $this->authorize('view', $afiliacion);

    return view('afiliacion.show', compact('afiliacion'));
}
```

### En Blade

```blade
@can('create_afiliacion')
    <button>Crear Afiliación</button>
@endcan

@role('SSST')
    <button>Validar</button>
@endrole
```

### En Modelos/Services

```php
if (auth()->user()->can('update_afiliacion')) {
    // Permitir edición
}

if (auth()->user()->hasRole('SSST')) {
    // Lógica específica para SSST
}
```

---

## Tablas de Base de Datos

### roles

| Campo | Tipo |
|-------|------|
| id | bigint |
| name | string |
| guard_name | string |
| created_at | timestamp |
| updated_at | timestamp |

### permissions

| Campo | Tipo |
|-------|------|
| id | bigint |
| name | string |
| guard_name | string |
| created_at | timestamp |
| updated_at | timestamp |

### model_has_roles

| Campo | Tipo |
|-------|------|
| role_id | bigint |
| model_type | string |
| model_id | bigint |

### model_has_permissions

| Campo | Tipo |
|-------|------|
| permission_id | bigint |
| model_type | string |
| model_id | bigint |

### role_has_permissions

| Campo | Tipo |
|-------|------|
| permission_id | bigint |
| role_id | bigint |

---

## Seeder de Roles y Permisos

**Ubicación**: `database/seeders/RolesAndPermissionsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles
        $superAdmin = Role::create(['name' => 'super_admin']);
        $ssst = Role::create(['name' => 'SSST']);
        $dependencia = Role::create(['name' => 'Dependencia']);

        // Crear dependencias de ejemplo
        $dependenciaSistemas = Dependencia::create([
            'nombre' => 'Sistemas e Informática',
            'codigo' => 'SIS',
        ]);

        // Crear usuarios de ejemplo
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@arl.gov.co',
            'password' => bcrypt('password123'),
            'dependencia_id' => $dependenciaSistemas->id,
        ]);
        $admin->assignRole('super_admin');

        $ssstUser = User::create([
            'name' => 'Usuario SSST',
            'email' => 'sst@arl.gov.co',
            'password' => bcrypt('password123'),
            'dependencia_id' => $dependenciaSistemas->id,
        ]);
        $ssstUser->assignRole('SSST');

        $depUser = User::create([
            'name' => 'Usuario Dependencia',
            'email' => 'dependencia@arl.gov.co',
            'password' => bcrypt('password123'),
            'dependencia_id' => $dependenciaSistemas->id,
        ]);
        $depUser->assignRole('Dependencia');
    }
}
```

---

## Comandos Útiles

```bash
# Regenerar permisos
php artisan shield:generate --all

# Limpiar caché de permisos
php artisan permission:cache-reset

# Ver roles de un usuario
php artisan tinker
>>> User::find(1)->roles

# Asignar rol
>>> User::find(1)->assignRole('SSST')

# Revocar rol
>>> User::find(1)->removeRole('SSST')

# Verificar permiso
>>> User::find(1)->can('create_afiliacion')
```

---

## Matriz de Permisos

| Permiso | super_admin | SSST | Dependencia |
|---------|:-----------:|:----:|:-----------:|
| view_any_afiliacion | ✓ | ✓ | ✓* |
| view_afiliacion | ✓ | ✓ | ✓* |
| create_afiliacion | ✓ | ✓ | ✓ |
| update_afiliacion | ✓ | ✓ | ✓* |
| delete_afiliacion | ✓ | ✓ | ✓* |
| force_delete_afiliacion | ✓ | ✓ | ✗ |
| restore_afiliacion | ✓ | ✓ | ✗ |
| view_any_user | ✓ | ✗ | ✗ |
| create_user | ✓ | ✗ | ✗ |

*Solo para su dependencia/área

---

## Próximos Pasos

- [Flujos de Trabajo](/docs/referencia/flujos/)
- [Base de Datos](/docs/referencia/base-datos/)
