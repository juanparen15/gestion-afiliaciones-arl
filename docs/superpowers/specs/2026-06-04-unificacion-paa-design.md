# Unificación del módulo PAA en gestion-afiliaciones-arl

**Fecha:** 2026-06-04
**Estado:** Diseño aprobado por el usuario
**Repositorio destino:** `C:\laragon\www\gestion-afiliaciones-arl` (rama `feature/unificacion-paa`)

## Resumen

Migrar el módulo "Plan Anual de Adquisiciones" (PAA) —actualmente en el proyecto
legacy Laravel 7 `paa2023V1` y parcialmente reescrito en `paa-v4` (Filament v4)—
al proyecto `gestion-afiliaciones-arl` (Laravel 12, **Filament v3**), unificando
ambos sistemas en una sola base de datos (`gestion_arl`) para poder vincular cada
línea del Plan de Adquisición con sus contratos.

Se implementa **todo el módulo PAA excepto el módulo de contratos del PAA legacy**
(el usuario gestiona contratos en el proyecto destino y ya existe `ContratoResource`).
El objetivo de la unificación es enlazar `Planadquisicione` → `Contrato`.

## Contexto y decisiones tomadas

| Decisión | Resolución |
|----------|------------|
| Estrategia de datos | Migrar PAA a la BD `gestion_arl` (una sola base) |
| Fuente de datos | Dump de **producción** `C:\Users\User\Downloads\paa.sql` (NO la BD local `paa`) |
| Tablas compartidas (areas/dependencias/users) | Reusar las de `gestion_arl` + **remapear** por nombre/email |
| Usuarios PAA sin match | **Crearlos** en `gestion_arl` durante el import (misma organización; rol básico + password aleatorio) |
| Vínculo Plan↔Contrato | **1:N** — un plan tiene varios contratos; `planadquisicione_id` en `contratos` |
| Mecanismo de importación | **Staging + comando artisan** idempotente con reporte de no-coincidencias |
| Versión Filament | **v3** (la del proyecto destino); se porta el trabajo escrito en v4 |
| Datos de referencia | Ambos sistemas son de la **misma entidad** (Alcaldía de Puerto Boyacá). Para desarrollo/pruebas se cargan en local los dos dumps de producción: `paa.sql` → `paa_legacy`, `gestion_arl.sql` → `gestion_arl` |

### Estado verificado del destino (`gestion_arl`)
- Filament v3.2, Laravel 12, plugins: Shield, Overlook, ErrorPages, SentryFeedback, WhatsappWidget.
- Resources existentes: `Afiliacion`, `Area`, `Dependencia`, `User`, `Contrato`.
- Datos en uso: 295 contratos, 32 áreas, 14 dependencias, 5 usuarios, 4 afiliaciones.
- Esquema `areas`: `id, dependencia_id, nombre, codigo(unique), descripcion, responsable, email, telefono, activo`.
- Esquema `dependencias`: `id, nombre(unique), codigo, descripcion, responsable, email, telefono, activo`.
- Esquema `users`: estándar Laravel + `area_id`.

### Estado verificado del dump de producción (`paa.sql`, 8.6 MB)
Tablas presentes: areas, clases, dependencias, detalleplanadquisiciones, empresas,
estadovigencias, familias, fuentes, intervalos, meses, modalidades, planadquisiciones,
planadquisicione_producto, productos, requipoais, requiproyectos, segmentos,
tipoadquisiciones, tipoprioridades, tipoprocesos, tipozonas, vigenfuturas, users,
+ tablas spatie/permission y de sistema.

Esquemas clave de producción:
- `areas`: `id, nomarea, slug, dependencia_id, timestamps`.
- `users`: `id, name, areas_id, email, email_verified_at, password, apellido, telefono, documento, remember_token, timestamps, avatar`.
- `planadquisiciones`: `id, id_vigencia, descripcioncont(500), valorestimadocont(str), valorestimadovig(str), duracont(str), codbpim, [13 FK lookup], user_id, slug(1000), timestamps`.

Volúmenes aprox. (dump producción): ~586 planes, ~49.022 productos, ~3.818 clases,
~420 familias, ~56 segmentos, ~22 áreas, ~10 dependencias, ~27 usuarios.

## Arquitectura

- **Una sola base de datos** `gestion_arl` con las tablas PAA añadidas → FK real
  `contratos.planadquisicione_id`.
- Todo el código PAA en **sintaxis Filament v3** (`Filament\Forms\Form`,
  `Filament\Tables\Table`, `Forms\Components\*`, `Tables\Columns\*`,
  `protected static ?string $navigationIcon`).
- Grupos de navegación nuevos:
  - **"Plan de Adquisiciones"** → `PlanadquisicioneResource`.
  - **"Clasificación UNSPSC"** → Segmento, Familia, Clase, Producto.
  - **"Configuración PAA"** → los 12 catálogos lookup.

## Componentes

### 1. Capa de datos — migraciones nuevas

**Se REUSAN (no se tocan):** `areas`, `dependencias`, `users`, `contratos`.

**Se CREAN** (con guardas `hasTable` para idempotencia):
- UNSPSC: `segmentos`, `familias`, `clases`, `productos`.
- Lookups (12): `estadovigencias`, `meses`, `modalidades`, `intervalos`,
  `vigenfuturas`, `tipozonas`, `tipoprocesos`, `tipoadquisiciones`,
  `requiproyectos`, `fuentes`, `tipoprioridades`, `requipoais`.
- Principal: `planadquisiciones` con `area_id`→`areas` (ARL), `user_id`→`users` (ARL),
  `id_vigencia` (entero, año de vigencia), `slug`, y las 13 FK a lookups/UNSPSC.
- Pivote: `planadquisicione_producto` (`planadquisicione_id`, `producto_id`, `clase_id`).

**Campos de texto preservados tal cual del legacy:** `valorestimadocont`,
`valorestimadovig`, `duracont` se mantienen como `string` (los datos de producción
usan separador de miles, p. ej. `1.000.000`). El nombre de columna con error
tipográfico del legacy (`requiproyectos.detproyeto`) se conserva para no romper datos.

**Se OMITEN:** `detalleplanadquisiciones` (0 filas), `empresas`, y el módulo de
contratos del PAA legacy (no usado; tablas inexistentes en producción).

**Modelos Eloquent:** uno por tabla nueva, con relaciones (`Planadquisicione`
`belongsTo` cada lookup + `belongsToMany` productos/clases; `Segmento hasMany Familia`,
etc.). Convención de nombres del legacy preservada (`Planadquisicione`, `Mese`,
`Modalidade`, `Requipoai`, etc.) para alinear con datos y FKs.

### 2. Importación de datos — Enfoque A (staging + comando)

1. **Staging:** cargar `paa.sql` en una BD temporal `paa_legacy` (manual o documentado
   en el plan).
2. **Conexión:** añadir conexión `paa_legacy` en `config/database.php` (lee de `.env`).
3. **Comando `php artisan paa:import`** (idempotente, re-ejecutable):
   - Copia catálogos UNSPSC y lookups **preservando los IDs** del legacy (para mantener
     intactas las FKs de `planadquisiciones`). Usa `updateOrInsert` por `id`.
   - **Remapeo de `area_id`:** por cada plan, traduce el `area_id` legacy → ID de área
     en `gestion_arl` buscando por **nombre** (`areas.nomarea` legacy ≈ `areas.nombre`
     ARL, normalizado: trim + minúsculas + sin tildes).
   - **Remapeo de `user_id`:** traduce por **email** del usuario legacy → `users.email`
     ARL. Los usuarios PAA **sin match** (≈19 de 31; cuentas de dependencias de la
     misma alcaldía) se **crean** en `gestion_arl` con: name/apellido/email/documento/
     telefono del legacy, password aleatorio (`Str::random` + hash), rol básico
     (p. ej. `panel_user`), y se mapean a su área ya remapeada. Quedan registrados en
     el reporte como "creados".
   - **Remapeo de `area_id`** sin match → queda `null` y se registra en el reporte.
   - **Reporte final:** lista de áreas legacy sin match y usuarios creados/sin match
     (para revisión con el usuario). Se imprime en consola y se guarda en
     `storage/logs/paa-import-YYYYMMDD.log`.
   - Reconstruye el pivote `planadquisicione_producto`.
   - Resumen: filas insertadas/actualizadas por tabla.

### 3. Filament Resources (v3)

**PlanadquisicioneResource** (grupo "Plan de Adquisiciones"):
- Formulario tipo **wizard de 3 pasos**:
  1. *Datos del contrato*: descripcioncont, valorestimadocont, valorestimadovig,
     duracont, codbpim, id_vigencia, area_id (opciones según rol).
  2. *Clasificación*: tipoadquisicione, modalidade, tipozona, estadovigencia,
     vigenfutura, fuente, mese, intervalo, tipoprioridade, requiproyecto, requipoai,
     tipoproceso (todos Select con relationship).
  3. *Clasificación UNSPSC*: cascada segmento→familia→clase (dependientes vía `live()`)
     + multi-select de productos y clases asociados.
- **Tabla**: columnas descripcioncont, valores, área, modalidad, mes, estadovigencia
  (badge por color), vigencia (año), registrado por. Filtros: vigencia (año), área,
  estadovigencia.
- **Scoping por rol** (`getEloquentQuery`): Admin/Supervisor ven todo; usuario normal
  solo `where user_id = auth id`.
- Acción de exportar Excel (ver §6).
- RelationManager de contratos vinculados (ver §5).

**Resources de catálogos** (CRUD simple, patrón v3 del proyecto):
- UNSPSC: `SegmentoResource`, `FamiliaResource`, `ClaseResource`, `ProductoResource`
  (Producto con ~49k filas → tabla con paginación y búsqueda por `detproducto`/clase).
- 12 lookups: un Resource sencillo por cada uno (campo principal + slug autogenerado).

### 4. (incluido en §1 y §3)

### 5. Vínculo Plan↔Contrato (1:N)
- Migración: añadir `planadquisicione_id` (nullable, FK a `planadquisiciones`,
  `nullOnDelete`) a `contratos`.
- Modelos: `Contrato belongsTo Planadquisicione`; `Planadquisicione hasMany Contratos`.
- `ContratoResource`: añadir Select `planadquisicione_id` (searchable, opcional) para
  asociar el contrato a una línea del plan; mostrar la descripción del plan.
- `PlanadquisicioneResource`: **RelationManager** `ContratosRelationManager` que lista
  los contratos vinculados (número, objeto, estado, fechas) con acción de attach/detach
  o edición del campo.

### 6. Dashboard — widgets ApexCharts (v3)
Portar la lógica del `HomeController` legacy como widgets Filament
(`leandrocfe/filament-apex-charts` v3):
- **StatsOverview**: conteos (planes de la vigencia, productos, áreas, usuarios) y
  suma de `valorestimadocont` (parseando el string con separador de miles a decimal).
- **Planes por área** (gráfico de barras).
- **Planes por mes** de la vigencia seleccionada (serie).
- Filtro de vigencia (año) en el dashboard.

### 7. Excel import/export (`pxlrbt/filament-excel` v2 + `maatwebsite/excel`)
- **Exportar planes**: acción de tabla en `PlanadquisicioneResource` con columnas
  legibles (relaciones resueltas a su nombre).
- **Importar catálogos**: acción de importación para áreas (mapeadas a ARL),
  familias, segmentos, clases, fuentes, modalidades, productos (formato del legacy
  `ImportExcelController`). Útil para recargas posteriores, no para la migración inicial.

### 8. Pruebas
- Acceso al panel (autenticado/no autenticado).
- `PlanadquisicioneResource`: render de lista, página de creación, scoping por rol.
- Comando `paa:import`: con un mini-dataset en una conexión SQLite/staging de prueba,
  verifica copia de catálogos, remapeo correcto de área/usuario por nombre/email, y
  registros sin match → null + reporte.
- Vínculo plan↔contrato: asignar `planadquisicione_id` y leer la relación en ambas
  direcciones.

## Flujo de datos (importación)

```
paa.sql ──(import manual)──► BD paa_legacy
                                   │  (conexión Laravel "paa_legacy")
                                   ▼
                         php artisan paa:import
                                   │
        ┌──────────────────────────┼───────────────────────────┐
        ▼                          ▼                            ▼
  catálogos/lookups          planadquisiciones            pivote producto
  (copia 1:1 por id)     (remap area_id por nombre,    (reconstruido desde
                          user_id por email)            legacy)
                                   │
                                   ▼
                         reporte no-coincidencias
                       (consola + storage/logs)
```

## Manejo de errores
- Importación idempotente (`updateOrInsert` por id) → re-ejecutable sin duplicar.
- Remapeo sin match → `null` + entrada en reporte (no aborta la importación).
- Guardas `hasTable` en migraciones para no chocar con tablas existentes.
- FK `nullOnDelete` en `planadquisicione_producto` y `contratos.planadquisicione_id`.

## Fuera de alcance (YAGNI)
- Módulo de contratos del PAA legacy (acta, contratista, supervisore, obligacione,
  empresa, tipodocumento, estado).
- Migración de `detalleplanadquisiciones` (vacía en producción).
- Reescribir/normalizar los valores monetarios string → decimal en la BD (se parsean
  solo en lectura para los widgets).

## Orden de implementación sugerido
1. Migraciones + modelos PAA (tablas nuevas, sin tocar las compartidas).
2. Conexión `paa_legacy` + comando `paa:import` + reporte.
3. Vínculo plan↔contrato (migración + relaciones + ajuste ContratoResource).
4. `PlanadquisicioneResource` (wizard + tabla + scoping) + RelationManager contratos.
5. Resources de catálogos (UNSPSC + lookups).
6. Dashboard widgets.
7. Excel import/export.
8. Pruebas en cada bloque.
