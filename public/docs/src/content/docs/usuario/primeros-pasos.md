---
title: Primeros Pasos
description: Guía rápida para comenzar a usar el Sistema de Gestión de Afiliaciones ARL
---

## Acceder al Sistema

### 1. Ingresar a la URL

Abre tu navegador web y navega a la URL del sistema:
- **Desarrollo**: `http://localhost:8000/admin`
- **Producción**: `https://arl.tudominio.gov.co/admin`

### 2. Iniciar Sesión

1. Ingresa tu **correo electrónico**
2. Ingresa tu **contraseña**
3. Click en **Iniciar Sesión**

:::tip[Olvidaste tu contraseña?]
Usa el enlace "¿Olvidaste tu contraseña?" para restablecerla por email.
:::

---

## Conocer la Interfaz

### Barra Lateral (Sidebar)

La barra lateral contiene los menús principales:

- **Dashboard**: Vista general con estadísticas
- **Afiliaciones**: Gestión de afiliaciones ARL
- **Usuarios**: Administración de usuarios (solo admin)
- **Dependencias**: Gestión de dependencias (solo admin)
- **Áreas**: Gestión de áreas (solo admin)

### Barra Superior

- **Búsqueda global**: Busca en todo el sistema
- **Notificaciones**: Alertas del sistema
- **Perfil**: Tu información y cerrar sesión

### Área Principal

Muestra el contenido de la sección seleccionada:
- Tablas con datos
- Formularios de creación/edición
- Gráficos y estadísticas

---

## Navegar por el Dashboard

El dashboard te muestra un resumen del estado del sistema:

### Tarjetas de Estadísticas

| Tarjeta | Descripción | Color |
|---------|-------------|-------|
| Total Afiliaciones | Cantidad total registrada | Azul |
| Pendientes | Esperando validación | Amarillo |
| Validadas | Aprobadas por SSST | Verde |
| Rechazadas | Rechazadas por SSST | Rojo |
| Vigentes | Contratos activos | Verde |
| Por Vencer | Próximos 30 días | Amarillo |

### Gráficos

- **Estado de Afiliaciones**: Distribución visual
- **Por Dependencia**: Comparativa entre áreas

---

## Acciones Básicas

### Ver Lista de Afiliaciones

1. Click en **Afiliaciones** en el menú lateral
2. Se muestra la tabla con todas las afiliaciones
3. Usa los filtros y búsqueda para encontrar registros

### Buscar una Afiliación

**Búsqueda rápida:**
1. Escribe en el campo de búsqueda (arriba de la tabla)
2. Puedes buscar por: nombre, documento, contrato, email

**Filtros avanzados:**
1. Click en el icono de filtro
2. Selecciona los criterios (estado, dependencia, etc.)
3. Click en **Aplicar filtros**

### Ordenar la Tabla

- Click en el encabezado de cualquier columna
- Click de nuevo para invertir el orden
- Las columnas con ordenamiento muestran una flecha

---

## Crear tu Primera Afiliación

### Paso 1: Abrir el Formulario

1. Ve a **Afiliaciones**
2. Click en el botón **Crear** (esquina superior derecha)

### Paso 2: Completar Datos del Contratista

- **Nombre completo**: Nombre y apellidos
- **Tipo de documento**: Selecciona CC, CE, PP o TI
- **Número de documento**: Sin puntos ni espacios
- **Fecha de nacimiento**: Selecciona del calendario
- **Teléfono**: Número de contacto
- **Email**: Correo electrónico válido
- **Dirección**: Dirección de residencia
- **Barrio**: Barrio o localidad

### Paso 3: Información de Seguridad Social

- **EPS**: Nombre de la EPS
- **AFP**: Nombre del fondo de pensiones

### Paso 4: Información del Contrato

- **Número de contrato**: Identificador del contrato
- **Dependencia**: Selecciona tu secretaría
- **Área**: Selecciona el área (opcional)
- **Objeto contractual**: Descripción del contrato
- **Valor del contrato**: Monto total
- **Honorarios mensuales**: El IBC se calcula automáticamente
- **Fechas**: Inicio y fin del contrato
- **Documento**: Sube el contrato en PDF o Word

### Paso 5: Información ARL

- **Nombre ARL**: Nombre de la administradora
- **Nivel de riesgo**: Selecciona de I a V

### Paso 6: Guardar

1. Revisa toda la información
2. Click en **Crear** o **Guardar**
3. El sistema te redirige a la lista de afiliaciones

---

## Editar una Afiliación

1. Encuentra la afiliación en la tabla
2. Click en el icono de **lápiz** (editar) en la fila
3. Modifica los campos necesarios
4. Click en **Guardar**

:::note[Solo puedes editar]
Afiliaciones en estado **Pendiente** que hayas creado.
:::

---

## Ver Detalles

1. Click en el icono de **ojo** (ver) en la fila
2. Se abre una vista de solo lectura
3. Puedes ver todos los campos y el historial

---

## Filtrar por Estado

Para ver solo las afiliaciones de un estado específico:

1. Click en el icono de filtro
2. Selecciona **Estado**
3. Elige: Pendiente, Validado o Rechazado
4. Click en **Aplicar**

Para quitar el filtro:
- Click en la "X" junto al filtro aplicado

---

## Exportar Datos

### Exportar Todo

1. Click en el botón **Exportar todo** (encabezado de tabla)
2. Se descarga un archivo Excel con todas las afiliaciones

### Exportar Seleccionados

1. Marca las casillas de las afiliaciones deseadas
2. Click en **Acciones masivas** > **Exportar seleccionados**
3. Se descarga un Excel solo con esas filas

---

## Personalizar tu Vista

### Mostrar/Ocultar Columnas

1. Click en el icono de columnas (tabla)
2. Marca/desmarca las columnas a mostrar
3. La preferencia se guarda automáticamente

### Cantidad de Filas por Página

1. En la parte inferior de la tabla
2. Selecciona: 10, 25, 50, 100
3. Navega entre páginas con las flechas

---

## Cerrar Sesión

1. Click en tu nombre (esquina superior derecha)
2. Selecciona **Cerrar sesión**
3. Serás redirigido a la página de login

:::caution[Seguridad]
Siempre cierra sesión cuando termines de usar el sistema, especialmente en computadores compartidos.
:::

---

## Atajos de Teclado

| Atajo | Acción |
|-------|--------|
| `Ctrl + K` | Búsqueda global |
| `Esc` | Cerrar modal/cancelar |
| `Enter` | Confirmar acción |

---

## Próximos Pasos

Según tu rol, consulta la guía específica:
- [Guía para rol Dependencia](/roles/dependencia/)
- [Guía para rol SSST](/roles/ssst/)
- [Guía para Administrador](/roles/administrador/)

O continúa con la [Guía del Dashboard](/usuario/dashboard/).
