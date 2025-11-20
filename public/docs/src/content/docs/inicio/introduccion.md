---
title: Introducción
description: Introducción al Sistema de Gestión de Afiliaciones ARL
---

## ¿Qué es el Sistema de Gestión de Afiliaciones ARL?

El Sistema de Gestión de Afiliaciones ARL es una aplicación web desarrollada para facilitar y automatizar el proceso de registro, seguimiento y validación de afiliaciones a la Administradora de Riesgos Laborales (ARL) de los contratistas vinculados a entidades gubernamentales.

## Problema que Resuelve

Antes de este sistema, el proceso de gestión de afiliaciones ARL presentaba los siguientes desafíos:

- **Proceso manual**: Las afiliaciones se gestionaban mediante hojas de cálculo dispersas
- **Falta de trazabilidad**: No había registro de quién creaba o validaba cada afiliación
- **Demoras en validación**: El equipo de SSST no tenía visibilidad centralizada de las solicitudes pendientes
- **Información desactualizada**: Dificultad para identificar contratos por vencer
- **Sin control de acceso**: Cualquier persona podía modificar la información

## Solución Implementada

El sistema proporciona:

### Para las Dependencias
- Interfaz intuitiva para registrar nuevas afiliaciones
- Formularios con validaciones automáticas
- Carga de documentos contractuales
- Seguimiento del estado de sus solicitudes

### Para el Equipo SSST
- Vista centralizada de todas las afiliaciones pendientes
- Proceso estandarizado de validación/rechazo
- Carga del certificado PDF de la ARL
- Notificaciones automáticas por email

### Para los Administradores
- Gestión completa de usuarios y roles
- Configuración de dependencias y áreas
- Auditoría de todas las acciones
- Reportes y estadísticas

## Flujo General del Sistema

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ Dependencia │ --> │   Pendiente │ --> │    SSST     │
│ crea        │     │   de        │     │   revisa    │
│ afiliación  │     │   validación│     │             │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                               │
                                    ┌──────────┴──────────┐
                                    │                     │
                              ┌─────▼─────┐        ┌──────▼──────┐
                              │ Validado  │        │  Rechazado  │
                              │ + PDF ARL │        │  + Motivo   │
                              └───────────┘        └─────────────┘
```

## Beneficios Clave

| Beneficio | Descripción |
|-----------|-------------|
| **Centralización** | Toda la información en un solo lugar |
| **Automatización** | Notificaciones y cálculos automáticos |
| **Trazabilidad** | Registro completo de todas las acciones |
| **Seguridad** | Control de acceso basado en roles |
| **Eficiencia** | Reducción de tiempos de gestión |
| **Reportes** | Estadísticas y alertas en tiempo real |

## Requisitos para Usar el Sistema

Para comenzar a usar el sistema necesitas:

1. **Credenciales de acceso**: Email y contraseña proporcionados por el administrador
2. **Navegador web moderno**: Chrome, Firefox, Edge o Safari actualizado
3. **Conexión a internet**: Para acceder al sistema

## Próximos Pasos

- [Ver las características completas](/docs/inicio/caracteristicas/)
- [Guía de primeros pasos](/docs/usuario/primeros-pasos/)
- [Instalación del sistema](/docs/instalacion/requisitos/) (para desarrolladores)
