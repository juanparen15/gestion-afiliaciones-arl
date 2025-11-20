---
title: Eventos y Listeners
description: Documentación técnica del sistema de eventos
---

## Sistema de Eventos

Laravel usa un patrón Observer para eventos, permitiendo desacoplar acciones del código principal.

---

## Eventos Definidos

### AfiliacionCreada

**Ubicación**: `app/Events/AfiliacionCreada.php`

```php
<?php

namespace App\Events;

use App\Models\Afiliacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AfiliacionCreada
{
    use Dispatchable, SerializesModels;

    public Afiliacion $afiliacion;

    public function __construct(Afiliacion $afiliacion)
    {
        $this->afiliacion = $afiliacion;
    }
}
```

**Se dispara cuando**: Se crea una nueva afiliación en estado pendiente.

---

## Listeners

### EnviarNotificacionNuevaAfiliacion

**Ubicación**: `app/Listeners/EnviarNotificacionNuevaAfiliacion.php`

```php
<?php

namespace App\Listeners;

use App\Events\AfiliacionCreada;
use App\Mail\NuevaAfiliacionMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacionNuevaAfiliacion
{
    public function handle(AfiliacionCreada $event): void
    {
        // Obtener todos los usuarios con rol SSST
        $usuariosSSST = User::role('SSST')->get();

        // Enviar email a cada uno
        foreach ($usuariosSSST as $usuario) {
            Mail::to($usuario->email)
                ->send(new NuevaAfiliacionMail($event->afiliacion));
        }
    }
}
```

**Qué hace**:
1. Obtiene todos los usuarios con rol SSST
2. Envía un email a cada uno
3. El email contiene información de la nueva afiliación

---

## Registro de Eventos

### EventServiceProvider

**Ubicación**: `app/Providers/EventServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Events\AfiliacionCreada;
use App\Listeners\EnviarNotificacionNuevaAfiliacion;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AfiliacionCreada::class => [
            EnviarNotificacionNuevaAfiliacion::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
```

---

## Observer

### AfiliacionObserver

**Ubicación**: `app/Observers/AfiliacionObserver.php`

```php
<?php

namespace App\Observers;

use App\Events\AfiliacionCreada;
use App\Models\Afiliacion;

class AfiliacionObserver
{
    /**
     * Se ejecuta después de crear una afiliación
     */
    public function created(Afiliacion $afiliacion): void
    {
        // Solo disparar evento si está pendiente
        if ($afiliacion->estado === 'pendiente') {
            event(new AfiliacionCreada($afiliacion));
        }
    }

    /**
     * Se ejecuta después de actualizar
     */
    public function updated(Afiliacion $afiliacion): void
    {
        // Aquí podrías agregar lógica para notificar cambios
    }

    /**
     * Se ejecuta después de eliminar
     */
    public function deleted(Afiliacion $afiliacion): void
    {
        // Lógica post-eliminación
    }

    /**
     * Se ejecuta después de restaurar (soft delete)
     */
    public function restored(Afiliacion $afiliacion): void
    {
        // Lógica post-restauración
    }
}
```

### Registro del Observer

**Ubicación**: `app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    Afiliacion::observe(AfiliacionObserver::class);
}
```

---

## Mailable

### NuevaAfiliacionMail

**Ubicación**: `app/Mail/NuevaAfiliacionMail.php`

```php
<?php

namespace App\Mail;

use App\Models\Afiliacion;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NuevaAfiliacionMail extends Mailable
{
    public Afiliacion $afiliacion;

    public function __construct(Afiliacion $afiliacion)
    {
        $this->afiliacion = $afiliacion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Afiliación Pendiente de Revisión - ARL',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva-afiliacion',
        );
    }
}
```

### Template del Email

**Ubicación**: `resources/views/emails/nueva-afiliacion.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Nueva Afiliación ARL</title>
</head>
<body>
    <h1>Nueva Afiliación Pendiente de Validación</h1>

    <p>Se ha registrado una nueva afiliación que requiere su revisión:</p>

    <table>
        <tr>
            <td><strong>Contratista:</strong></td>
            <td>{{ $afiliacion->nombre_contratista }}</td>
        </tr>
        <tr>
            <td><strong>Documento:</strong></td>
            <td>{{ $afiliacion->tipo_documento }} {{ $afiliacion->numero_documento }}</td>
        </tr>
        <tr>
            <td><strong>Contrato:</strong></td>
            <td>{{ $afiliacion->numero_contrato }}</td>
        </tr>
        <tr>
            <td><strong>Dependencia:</strong></td>
            <td>{{ $afiliacion->dependencia->nombre }}</td>
        </tr>
        <tr>
            <td><strong>Fecha de Registro:</strong></td>
            <td>{{ $afiliacion->created_at->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <p>
        <a href="{{ url('/admin/afiliaciones/' . $afiliacion->id) }}">
            Ver Afiliación en el Sistema
        </a>
    </p>

    <p>Saludos,<br>Sistema de Gestión ARL</p>
</body>
</html>
```

---

## Flujo Completo

```
1. Usuario Dependencia crea afiliación
                │
                ▼
2. AfiliacionResource::create()
                │
                ▼
3. Modelo Afiliacion::create()
                │
                ▼
4. AfiliacionObserver::created()
                │
                ▼
5. event(new AfiliacionCreada($afiliacion))
                │
                ▼
6. EnviarNotificacionNuevaAfiliacion::handle()
                │
                ▼
7. Mail::to()->send(new NuevaAfiliacionMail())
                │
                ▼
8. Email enviado a todos los SSST
```

---

## Extender el Sistema

### Agregar Nuevo Evento

1. **Crear el evento**:
```bash
php artisan make:event AfiliacionValidada
```

2. **Definir el evento**:
```php
// app/Events/AfiliacionValidada.php
class AfiliacionValidada
{
    public Afiliacion $afiliacion;
    public User $validador;

    public function __construct(Afiliacion $afiliacion, User $validador)
    {
        $this->afiliacion = $afiliacion;
        $this->validador = $validador;
    }
}
```

3. **Crear el listener**:
```bash
php artisan make:listener NotificarAfiliacionValidada
```

4. **Implementar el listener**:
```php
// app/Listeners/NotificarAfiliacionValidada.php
public function handle(AfiliacionValidada $event): void
{
    // Notificar al creador de la afiliación
    $creador = $event->afiliacion->creador;

    Mail::to($creador->email)
        ->send(new AfiliacionValidadaMail($event->afiliacion));
}
```

5. **Registrar en EventServiceProvider**:
```php
protected $listen = [
    AfiliacionCreada::class => [
        EnviarNotificacionNuevaAfiliacion::class,
    ],
    AfiliacionValidada::class => [
        NotificarAfiliacionValidada::class,
    ],
];
```

6. **Disparar el evento** (en el Resource):
```php
Action::make('validar')
    ->action(function (Afiliacion $record, array $data) {
        $record->update([...]);

        event(new AfiliacionValidada($record, auth()->user()));
    }),
```

---

## Colas (Queues)

Para mejor rendimiento, puedes enviar emails a una cola:

### Modificar el Listener

```php
use Illuminate\Contracts\Queue\ShouldQueue;

class EnviarNotificacionNuevaAfiliacion implements ShouldQueue
{
    public $queue = 'emails';

    public function handle(AfiliacionCreada $event): void
    {
        // Mismo código, pero se ejecuta en background
    }
}
```

### Configurar Cola

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work --queue=emails
```

---

## Testing de Eventos

```php
use Illuminate\Support\Facades\Event;

public function test_event_disparado_al_crear_afiliacion()
{
    Event::fake();

    $afiliacion = Afiliacion::factory()->create([
        'estado' => 'pendiente'
    ]);

    Event::assertDispatched(AfiliacionCreada::class, function ($event) use ($afiliacion) {
        return $event->afiliacion->id === $afiliacion->id;
    });
}
```

---

## Próximos Pasos

- [Políticas y Permisos](/docs/tecnica/permisos/)
- [Base de Datos](/docs/referencia/base-datos/)
