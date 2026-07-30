---
title: Actas de Necesidad (técnica)
description: "Arquitectura del módulo de Actas de Necesidad: generación DOCX→PDF, plantilla, verificación e importación."
---

Referencia técnica del módulo de Actas de Necesidad.

## Componentes

| Elemento | Ruta |
|----------|------|
| Recurso Filament | `app/Filament/Resources/ActaNecesidadResource.php` |
| Modelo | `app/Models/ActaNecesidad.php` |
| Configuración de firma | `app/Models/ConfiguracionActa.php` (tabla `acta_configuraciones`) |
| Generador de documento | `app/Services/ActaNecesidadDocGenerator.php` |
| Plantilla Word | `resources/document-templates/acta_necesidad.docx` |
| Correo | `app/Mail/ActaAprobadaMail.php` + `resources/views/emails/acta-aprobada.blade.php` |
| Página de verificación | ruta `actas.verificar` → `resources/views/actas/verificar.blade.php` |
| Importador | `app/Console/Commands/ImportarActasNecesidad.php` |
| Política | `app/Policies/ActaNecesidadPolicy.php` |

## Generación del PDF

El PDF se produce a partir de la plantilla `.docx` (idéntica al formato oficial)
y se convierte con **LibreOffice headless**. No se manipula el PDF directamente.

Flujo en `ActaNecesidadDocGenerator`:

1. `generarDocx()` — abre la plantilla con `PhpWord\TemplateProcessor` y rellena
   los macros `${...}` (DEPENDENCIA, OBJETO, DURACION, CODIGO_PAA, etc.).
2. `insertarImagenesFlotantes()` — inserta como **imágenes flotantes ancladas**
   (no crecen la tabla ni empujan a otra hoja):
   - **Firma del alcalde** sobre "Vo. Bo. Alcalde Municipal" (posición absoluta
     en la página, fuera de la tabla).
   - **QR** de verificación en la esquina superior derecha.
   - **Sello BORRADOR** (solo vista previa) centrado y semitransparente.
   La firma mal posicionada que trae la plantilla se elimina antes de reinsertar.
3. `convertirAPdf()` — ejecuta `soffice --convert-to pdf` (vía `proc_open`, con
   timeout y perfil de usuario único para permitir concurrencia). Puede exportar
   el PDF cifrado con permisos de **solo impresión** según
   `config('services.actas.proteger_pdf')`.

### Requisitos de servidor

- **LibreOffice** instalado. Ruta configurable en `LIBREOFFICE_BIN`
  (`config/services.php` → `libreoffice.bin`).
- El nombre del PDF es determinista: `actas-necesidad/pdf/acta-0{N}.pdf`
  (las vistas previas usan sufijo `-BORRADOR`). Las URLs de descarga llevan
  `?t=` para evitar caché de navegador/CDN.

:::caution
No usar `PageRange` en el `FilterData` de LibreOffice: produce PDFs vacíos.
:::

## Verificación

`ActaNecesidad::asegurarCodigoVerificacion()` genera un código único. La ruta
pública `actas/verificar/{codigo}` muestra los datos del acta. El QR del PDF
apunta a esa URL (`$record->urlVerificacion()`).

## Importación desde Excel

`php artisan actas:importar-excel {archivo} [--fresh] [--dry-run] [--force]`

- Lee columnas A–Q del Excel de respuestas (Q = consecutivo).
- Mapea dependencia/área por nombre (con alias y normalización sin acentos);
  si no hay coincidencia de FK, guarda el nombre como texto (se ve igual).
- `--dry-run` reporta sin escribir; `--fresh` borra todo antes de importar.

## Permisos

La política y las acciones de la tabla autorizan por el permiso Shield
correspondiente **o** el toggle `puede_aprobar_actas` del usuario.
