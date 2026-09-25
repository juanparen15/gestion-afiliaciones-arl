# Diseño: Landing nuevo + Port completo del sistema BPIM al sistema de trámites

Fecha: 2026-09-08
Estado: Aprobado por el usuario (alcance ampliado)

## Contexto

`gestion-afiliaciones-arl` (tramites.ticsistemas.com.co) es el ecosistema de trámites
(Afiliaciones ARL, Actas de Necesidad, Contratación, Plan de Adquisiciones). Se quieren
dos cambios:

1. **Landing**: reemplazar `resources/views/welcome.blade.php` por la plantilla cunnet
   (`index-digital-light.html`), conservando 100% el diseño y las animaciones, cambiando
   solo textos (sobre el sistema de trámites y sus módulos) e imágenes (8 fotos de la
   Alcaldía de Puerto Boyacá, ya copiadas a `public/landing/img/alcaldia/`).
2. **BPIM**: portar TODO el `sistema-bpim` (app Laravel independiente) como módulos dentro
   del sistema de trámites, visibles SOLO para super_admin (Shield).

## Alcance aprobado

- Port completo del `sistema-bpim`:
  - **Solicitudes BPIM** (`solicitudes_bpim`, ~50 campos) + ítems de presupuesto
    (`presupuesto_items`).
  - **Proyectos / Capítulos / Ítems** (`proyectos`, `proyecto_capitulos`, `capitulo_items`)
    y **Catálogo de Ítems** (`items_catalogo`), con control presupuestal y autocompletado.
  - **Verificación de documentos** (`documentos_verificacion`) con QR.
  - **Formulario público** de solicitud BPIM (desde ya).
  - **Widgets/Dashboard** del BPIM.
  - **Servicios**: DocumentoService (Word `plantilla_bpim.docx` → PDF), DocumentoVerificacionService (QR), NotificacionService (correo).
- **Flujo de aprobación** de Solicitudes BPIM alineado a Actas:
  - Toggle de usuario **`puede_aprobar_bpim`** (espejo de `puede_aprobar_actas`), habilitado en `UserResource` y aplicado vía `Gate::before`.
  - Columna/seguimiento **"Aprobado por"** en la tabla de solicitudes.
- **Permisos**: todos los recursos BPIM (Solicitudes, Proyectos, Catálogo) **solo visibles para super_admin** mediante Filament Shield (policies + Gate::before).

## Decisiones

- **Formulario público**: se incluye desde ya (`/bpim` público), además del registro interno.
- **Fuera de alcance**: nada nuevo; es un port 1:1 adaptado a las convenciones del sistema de trámites.

## Integración / riesgos (fusionar dos apps Laravel)

- **Tablas**: verificar que ninguna colisione con las existentes (dominios distintos; se espera OK). Portar migraciones de BPIM con fechas nuevas.
- **User**: agregar `puede_aprobar_bpim` (+ cualquier campo de usuario que use el BPIM) al `User` del trámites; no duplicar el modelo.
- **AdminPanelProvider**: registrar los Resources y Widgets BPIM; nuevos grupos de navegación ("Gestión de BPIM", "Proyectos BPIM"); todo restringido a super_admin.
- **Shield**: `shield:generate` para los recursos nuevos; policies que solo permitan super_admin (o Gate::before). No romper las policies existentes (recordar: la lógica custom va en Gate::before, no en policies).
- **Servicios BPIM**: portar con sus namespaces; ajustar rutas de storage y config (plantilla, carpetas de documentos).
- **Plantilla Word**: copiar `plantilla_bpim.docx` a `storage/app/plantillas/` del trámites.
- **Rutas**: portar rutas públicas del formulario BPIM y la verificación (`/bpim/verificar/{codigo}`), respetando el middleware de dominio antiguo.
- **QR/verificación**: URL basada en `APP_URL` (como actas), para que funcione tras el túnel/dominio.
- **Correo**: usar el SMTP existente; alinear a `puede_aprobar_bpim` para destinatarios.

## Fases de implementación

- **Fase 0 — Landing** (independiente): integrar cunnet + assets a `public/`, `welcome.blade.php`, contenido de módulos, 8 fotos. Verificable de inmediato.
- **Fase 1 — Base BPIM**: migraciones + modelos + `puede_aprobar_bpim` en User + Shield (super_admin).
- **Fase 2 — Solicitudes BPIM (interno)**: Resource (wizard + presupuesto), flujo aprobar/rechazar, "Aprobado por", servicios documento+QR+correo, verificación pública.
- **Fase 3 — Proyectos/Catálogo**: Resources + relaciones + control presupuestal + autocompletado.
- **Fase 4 — Formulario público** `/bpim` + éxito.
- **Fase 5 — Widgets/Dashboard** BPIM.

Cada fase se prueba (render + flujo) antes de commit/push, como en el resto de la sesión.

## Criterios de éxito

- Landing con diseño/animaciones intactos, contenido de trámites y fotos de la alcaldía.
- Módulos BPIM funcionando end-to-end (registro, aprobación con toggle, documento+QR, correo, formulario público), visibles solo a super_admin.
- Sin romper los módulos existentes (Afiliaciones, Actas, Contratación, PAA).
