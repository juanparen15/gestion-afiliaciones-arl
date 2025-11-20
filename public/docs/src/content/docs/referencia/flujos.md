---
title: Flujos de Trabajo
description: Documentación de los flujos de trabajo del sistema
---

## Flujo Principal: Crear y Validar Afiliación

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO DE AFILIACIÓN                         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────┐
│ Dependencia │
│   inicia    │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│ Crear afiliación│
│ en el sistema   │
└──────┬──────────┘
       │
       ▼
┌─────────────────┐     ┌─────────────────┐
│ Estado:         │     │ Notificación    │
│ PENDIENTE       │────►│ email a SSST    │
└──────┬──────────┘     └─────────────────┘
       │
       ▼
┌─────────────────┐
│ SSST revisa     │
│ la afiliación   │
└──────┬──────────┘
       │
       ├───────────────────┐
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│ ¿Datos      │     │ ¿Datos      │
│ correctos?  │     │ incorrectos?│
│    SÍ       │     │    NO       │
└──────┬──────┘     └──────┬──────┘
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│ Obtener PDF │     │ Indicar     │
│ de la ARL   │     │ motivo      │
└──────┬──────┘     └──────┬──────┘
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│ Validar +   │     │ Rechazar    │
│ cargar PDF  │     │             │
└──────┬──────┘     └──────┬──────┘
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│ Estado:     │     │ Estado:     │
│ VALIDADO    │     │ RECHAZADO   │
└──────┬──────┘     └──────┬──────┘
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│ PDF ARL     │     │ Dependencia │
│ disponible  │     │ corrige y   │
│ para        │     │ reenvía     │
│ dependencia │     │             │
└─────────────┘     └──────┬──────┘
                           │
                           │ (vuelve a PENDIENTE)
                           └────────────────────┘
```

---

## Flujo de Importación Excel

```
┌─────────────┐
│ Obtener     │
│ plantilla   │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│ Completar datos │
│ en Excel        │
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Subir archivo   │
│ al sistema      │
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Sistema procesa │
│ cada fila       │
└──────┬──────────┘
       │
       ├─────────────────────┐
       │                     │
       ▼                     ▼
┌─────────────┐       ┌─────────────┐
│ Fila válida │       │ Fila con    │
│             │       │ errores     │
└──────┬──────┘       └──────┬──────┘
       │                     │
       ▼                     ▼
┌─────────────┐       ┌─────────────┐
│ ¿Existe     │       │ Agregar a   │
│ documento?  │       │ archivo de  │
│             │       │ errores     │
└──────┬──────┘       └─────────────┘
       │
  ┌────┴────┐
  │         │
  ▼         ▼
┌─────┐   ┌─────┐
│ Sí  │   │ No  │
└──┬──┘   └──┬──┘
   │         │
   ▼         ▼
┌─────────┐ ┌─────────┐
│Actualiza│ │ Crea    │
│registro │ │ nuevo   │
└─────────┘ └─────────┘
```

---

## Flujo de Notificaciones

```
┌───────────────────┐
│ Afiliación creada │
│ (Observer)        │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Disparar evento   │
│ AfiliacionCreada  │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Listener ejecuta  │
│ EnviarNotificacion│
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Obtener usuarios  │
│ con rol SSST      │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Para cada SSST:   │
│ enviar email      │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ SSST recibe email │
│ con enlace        │
└───────────────────┘
```

---

## Flujo de Autorización

```
┌───────────────────┐
│ Usuario hace      │
│ request           │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Middleware        │
│ Authenticate      │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ ¿Usuario          │
│ autenticado?      │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    │           │
    ▼           ▼
┌──────┐    ┌──────┐
│  Sí  │    │  No  │
└──┬───┘    └──┬───┘
   │           │
   │           ▼
   │     ┌──────────┐
   │     │ Redirect │
   │     │ a login  │
   │     └──────────┘
   │
   ▼
┌───────────────────┐
│ Policy check      │
│ (AfiliacionPolicy)│
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ ¿Tiene permiso    │
│ para la acción?   │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    │           │
    ▼           ▼
┌──────┐    ┌──────┐
│  Sí  │    │  No  │
└──┬───┘    └──┬───┘
   │           │
   ▼           ▼
┌──────────┐ ┌──────────┐
│ Ejecutar │ │ Error    │
│ acción   │ │ 403      │
└──────────┘ └──────────┘
```

---

## Flujo de Soft Delete

```
┌───────────────────┐
│ Usuario elimina   │
│ afiliación        │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Verificar         │
│ permiso delete    │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Marcar deleted_at │
│ con timestamp     │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Registro oculto   │
│ de consultas      │
│ normales          │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ ¿Restaurar?       │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    │           │
    ▼           ▼
┌──────┐    ┌──────┐
│  Sí  │    │  No  │
└──┬───┘    └──┬───┘
   │           │
   ▼           ▼
┌──────────┐ ┌────────────┐
│ deleted_ │ │ ¿Eliminar  │
│ at=null  │ │ permanente?│
└──────────┘ └──────┬─────┘
                    │
              ┌─────┴─────┐
              │           │
              ▼           ▼
          ┌──────┐    ┌──────┐
          │  Sí  │    │  No  │
          └──┬───┘    └──┬───┘
             │           │
             ▼           ▼
        ┌──────────┐ ┌──────────┐
        │ DELETE   │ │ Mantener │
        │ de BD    │ │ eliminado│
        └──────────┘ └──────────┘
```

---

## Estados de la Afiliación

### Diagrama de Estados

```
         ┌──────────────────┐
         │    PENDIENTE     │◄────────────┐
         └────────┬─────────┘             │
                  │                       │
         ┌────────┴─────────┐             │
         │                  │             │
         ▼                  ▼             │
┌─────────────────┐ ┌─────────────────┐   │
│    VALIDADO     │ │   RECHAZADO     │───┘
└─────────────────┘ └─────────────────┘
                    (al corregir vuelve
                     a pendiente)
```

### Transiciones Permitidas

| Estado Actual | Acción | Estado Final | Quién |
|---------------|--------|--------------|-------|
| - | Crear | Pendiente | Dependencia |
| Pendiente | Validar | Validado | SSST |
| Pendiente | Rechazar | Rechazado | SSST |
| Rechazado | Editar | Pendiente | Dependencia |

---

## Ciclo de Vida del Request

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Nginx/    │
│   Apache    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  PHP-FPM    │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│         Laravel Application         │
├─────────────────────────────────────┤
│ 1. HTTP Kernel                      │
│ 2. Middleware Stack                 │
│ 3. Route Dispatch                   │
│ 4. Filament Resource                │
│ 5. Policy Authorization             │
│ 6. Eloquent Model                   │
│ 7. Database Query                   │
│ 8. Response Generation              │
└─────────────────────────────────────┘
       │
       ▼
┌─────────────┐
│   MySQL     │
└─────────────┘
```

---

## Próximos Pasos

- [Base de Datos](/docs/referencia/base-datos/)
- [Comandos Artisan](/docs/referencia/comandos/)
