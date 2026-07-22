# Dashboard PAA con filtros globales - Diseño

**Fecha:** 2026-06-10
**Proyecto:** gestion-afiliaciones-arl (Filament v3.2, Laravel 12)
**Módulo:** Plan Anual de Adquisiciones (PAA)

## Objetivo

Mejorar la analítica del módulo PAA con un **dashboard dedicado** que tenga una
**barra de filtros global** (vigencia, área, dependencia, tipo de adquisición) y
varios widgets que respeten el filtro y el scope por rol del usuario.

## Decisiones tomadas (brainstorming)

- **Ubicación:** página dedicada `Dashboard Plan de Adquisiciones`, separada del
  dashboard de Afiliaciones/Contratos.
- **Filtro:** barra de filtros global en la cabecera que afecta a TODOS los
  widgets PAA a la vez (no filtro por widget).
- **Scope por rol:** mismo comportamiento que el `PlanadquisicioneResource`
  (super_admin/SSST ven todo; `area_id` → su área; `dependencia_id` → su
  dependencia + áreas de la dependencia).

## Componentes

### 1. Página `app/Filament/Pages/PaaDashboard.php`

- Extiende `Filament\Pages\Dashboard` (BaseDashboard).
- Slug `paa-dashboard`, navegación con etiqueta "Dashboard Plan de Adquisiciones",
  ícono `heroicon-o-chart-bar`, grupo de navegación coherente con el módulo PAA.
- Usa `Filament\Pages\Dashboard\Concerns\HasFiltersForm` para definir
  `filtersForm(Form $form)` con:
  - `Select vigencia` (requerido, opciones = años distintos de `created_at`,
    default = año más reciente con datos).
  - `Select area_id` (opcional, `relationship`/options por nombre).
  - `Select dependencia_id` (opcional).
  - `Select tipoadquisicione_id` (opcional).
- Sobreescribe `getWidgets()` devolviendo SOLO los widgets PAA.

### 2. Separación de widgets en el dashboard actual

`app/Filament/Pages/Dashboard.php` sobreescribe `getWidgets()` para devolver
solo los widgets de Afiliaciones/Contratos. Así los widgets PAA dejan de
aparecer en el dashboard principal (hoy todos se auto-muestran juntos) y solo
viven en el PaaDashboard.

### 3. Scope reutilizable en el modelo (mejora DRY)

En `app/Models/Planadquisicione.php`:

- `scopeVisibleTo(Builder $q, ?User $user): Builder` - réplica exacta de la
  lógica actual de `PlanadquisicioneResource::getEloquentQuery()`:
  - sin usuario → `whereRaw('1=0')`.
  - super_admin o SSST → sin filtro.
  - con `area_id` → `where('area_id', $user->area_id)`.
  - con `dependencia_id` → `dependencia_id` directo OR `whereHas('area')` de esa
    dependencia.
  - else → `whereRaw('1=0')`.
- `scopeApplyDashboardFilters(Builder $q, array $filters): Builder` - aplica:
  - `vigencia` → `whereYear('created_at', ...)` (driver-agnóstico vía helper).
  - `area_id`, `dependencia_id`, `tipoadquisicione_id` si están presentes.

`PlanadquisicioneResource::getEloquentQuery()` pasa a delegar en `visibleTo`,
eliminando la duplicación de lógica.

### 4. Helper de valor monetario

Los valores legacy son strings con separador de miles (`"55.000.000"`). Se
agrega un método estático/accessor para parsear a float:
`Planadquisicione::parseValor($valor): float` (`str_replace(['.', ','], ['', '.'])`).
Las sumas se hacen en PHP tras `pluck` (volumen acotado por vigencia, ~2.500
filas), igual que el `PaaStatsOverview` actual.

### 5. Widgets

Todos: leen los filtros vía `Filament\Widgets\Concerns\InteractsWithPageFilters`
(`$this->filters`), aplican `visibleTo(Auth::user())` y
`applyDashboardFilters($this->filters)`.

| Widget | Tipo | Contenido |
|---|---|---|
| `PaaStatsOverview` (mejora) | Stats | N° de planes, **Valor estimado total $**, productos UNSPSC, áreas - respetando filtros + rol |
| `PlanesValorPorDependenciaChart` (nuevo) | Barras horizontales | Suma de valor $ por dependencia. `canView()` solo para quien ve todo (super_admin/SSST) |
| `PlanesPorAreaChart` (refactor) | Barras horizontales | Conteo de planes por oficina productora (área) |
| `PlanesPorTipoAdquisicionChart` (nuevo) | Dona | Distribución por tipo de adquisición |
| `PlanesPorMesChart` (refactor → timeline) | Línea | Planes por mes de inicio dentro de la vigencia |
| `PlanesVinculadosContratoChart` (nuevo) | Dona + stat | Planes vinculados a contratos vs sin vincular; stat con % de avance |

### 6. Permisos (Shield)

La página `PaaDashboard` se registra bajo Shield (permiso de tipo página) para
que solo aparezca a roles autorizados, consistente con el resto del módulo.
super_admin debe re-sincronizar permisos tras desplegar.

## Testing

- `PaaDashboardScopeTest`: `scopeVisibleTo` filtra correcto por cada rol
  (super_admin ve todo; usuario de área ve solo su área; usuario de dependencia
  ve su dependencia + áreas; sin scope no ve nada).
- `PaaWidgetsTest` (refactor): los widgets responden a `$filters`
  (vigencia/área) y el conteo/valor cambia con el filtro.
- `PaaDashboardTest`: la página renderiza para un usuario con permiso y el
  filtro de vigencia toma por default el año más reciente con datos.
- `PlanesVinculadosContratoTest`: el conteo de vinculados/sin vincular es
  correcto según `contratos()`.

## Fuera de alcance (YAGNI)

- Exportación de los gráficos (ya existe export SECOP II en la lista).
- Drill-down interactivo click-en-gráfico.
- Comparativa multi-vigencia en un mismo gráfico (se filtra una vigencia a la vez).
