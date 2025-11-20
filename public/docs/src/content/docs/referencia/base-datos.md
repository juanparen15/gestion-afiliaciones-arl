---
title: Base de Datos
description: Referencia completa del esquema de base de datos
---

## Diagrama Entidad-Relación

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │  dependencias   │
├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │
│ name            │◄──┐   │ nombre          │
│ email           │   │   │ codigo          │
│ dependencia_id  │───┼──►│ responsable     │
│ area_id         │   │   │ activo          │
│ password        │   │   └────────┬────────┘
└────────┬────────┘   │            │
         │            │            │
         │            │   ┌────────▼────────┐
         │            │   │     areas       │
         │            │   ├─────────────────┤
         │            │   │ id              │
         │            │   │ dependencia_id  │
         │            └───│ nombre          │
         │                │ codigo          │
         │                │ activo          │
         │                └────────┬────────┘
         │                         │
         │                         │
         ▼                         ▼
┌─────────────────────────────────────────────┐
│              afiliaciones                   │
├─────────────────────────────────────────────┤
│ id                                          │
│ nombre_contratista                          │
│ numero_documento (unique)                   │
│ numero_contrato                             │
│ dependencia_id ─────────────────────────────┤
│ area_id ────────────────────────────────────┤
│ created_by ─────────────────────────────────┤
│ validated_by ───────────────────────────────┤
│ estado                                      │
│ pdf_arl                                     │
│ deleted_at (soft delete)                    │
└─────────────────────────────────────────────┘
```

---

## Tabla: afiliaciones

### Estructura

```sql
CREATE TABLE afiliaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Datos del contratista
    nombre_contratista VARCHAR(255) NOT NULL,
    tipo_documento ENUM('CC', 'CE', 'PP', 'TI', 'NIT') NOT NULL,
    numero_documento VARCHAR(50) NOT NULL UNIQUE,
    email_contratista VARCHAR(255),
    telefono_contratista VARCHAR(50),
    fecha_nacimiento DATE,
    barrio VARCHAR(255),
    direccion_residencia VARCHAR(255),

    -- Seguridad social
    eps VARCHAR(255),
    afp VARCHAR(255),

    -- Contrato
    numero_contrato VARCHAR(100) NOT NULL,
    objeto_contractual TEXT,
    valor_contrato DECIMAL(15, 2),
    honorarios_mensual DECIMAL(15, 2),
    ibc DECIMAL(15, 2),
    meses_contrato INT,
    dias_contrato INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    contrato_pdf_o_word VARCHAR(255),

    -- ARL
    nombre_arl VARCHAR(255),
    tipo_riesgo ENUM('I', 'II', 'III', 'IV', 'V'),
    numero_afiliacion_arl VARCHAR(100),
    fecha_afiliacion_arl DATE,
    fecha_terminacion_afiliacion DATE,
    pdf_arl VARCHAR(255),

    -- Relaciones
    dependencia_id BIGINT UNSIGNED,
    area_id BIGINT UNSIGNED,
    created_by BIGINT UNSIGNED,
    validated_by BIGINT UNSIGNED,

    -- Estado
    estado ENUM('pendiente', 'validado', 'rechazado') DEFAULT 'pendiente',
    observaciones TEXT,
    motivo_rechazo TEXT,
    fecha_validacion TIMESTAMP NULL,

    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    -- Índices
    INDEX idx_numero_documento (numero_documento),
    INDEX idx_numero_contrato (numero_contrato),
    INDEX idx_estado (estado),
    INDEX idx_dependencia (dependencia_id),
    INDEX idx_fecha_fin (fecha_fin),

    -- Foreign keys
    FOREIGN KEY (dependencia_id) REFERENCES dependencias(id),
    FOREIGN KEY (area_id) REFERENCES areas(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (validated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Índices

| Índice | Columnas | Propósito |
|--------|----------|-----------|
| PRIMARY | id | Clave primaria |
| UNIQUE | numero_documento | Evitar duplicados |
| idx_estado | estado | Filtrar por estado |
| idx_dependencia | dependencia_id | Filtrar por dependencia |
| idx_fecha_fin | fecha_fin | Ordenar/filtrar por vencimiento |

---

## Tabla: users

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    correo_institucional VARCHAR(255),
    cargo VARCHAR(255),
    dependencia_id BIGINT UNSIGNED,
    area_id BIGINT UNSIGNED,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (dependencia_id) REFERENCES dependencias(id),
    FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tabla: dependencias

```sql
CREATE TABLE dependencias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) UNIQUE,
    descripcion TEXT,
    responsable VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tabla: areas

```sql
CREATE TABLE areas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dependencia_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) UNIQUE,
    descripcion TEXT,
    responsable VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_dependencia_activo (dependencia_id, activo),

    FOREIGN KEY (dependencia_id) REFERENCES dependencias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tablas de Spatie Permission

### roles

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY roles_name_guard (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### permissions

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY permissions_name_guard (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### model_has_roles

```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, model_id, model_type),
    INDEX model_has_roles_model_id_model_type (model_id, model_type),

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tabla: activity_log

```sql
CREATE TABLE activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    log_name VARCHAR(255),
    description TEXT NOT NULL,
    subject_type VARCHAR(255),
    event VARCHAR(255),
    subject_id BIGINT UNSIGNED,
    causer_type VARCHAR(255),
    causer_id BIGINT UNSIGNED,
    properties JSON,
    batch_uuid CHAR(36),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX activity_log_log_name (log_name),
    INDEX subject (subject_type, subject_id),
    INDEX causer (causer_type, causer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Consultas SQL Comunes

### Estadísticas del Dashboard

```sql
-- Total por estado
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'validado' THEN 1 ELSE 0 END) as validadas,
    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazadas
FROM afiliaciones
WHERE deleted_at IS NULL;

-- Contratos por vencer
SELECT *
FROM afiliaciones
WHERE fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
  AND deleted_at IS NULL
ORDER BY fecha_fin;

-- Por dependencia
SELECT
    d.nombre,
    COUNT(a.id) as total
FROM dependencias d
LEFT JOIN afiliaciones a ON a.dependencia_id = d.id AND a.deleted_at IS NULL
GROUP BY d.id, d.nombre
ORDER BY total DESC;
```

### Búsquedas

```sql
-- Buscar por documento
SELECT * FROM afiliaciones
WHERE numero_documento LIKE '%123456%'
  AND deleted_at IS NULL;

-- Buscar por contratista
SELECT * FROM afiliaciones
WHERE nombre_contratista LIKE '%Juan%'
  AND deleted_at IS NULL;
```

### Reportes

```sql
-- Afiliaciones del mes
SELECT *
FROM afiliaciones
WHERE MONTH(created_at) = MONTH(CURDATE())
  AND YEAR(created_at) = YEAR(CURDATE())
  AND deleted_at IS NULL;

-- Tiempo promedio de validación
SELECT
    AVG(TIMESTAMPDIFF(HOUR, created_at, fecha_validacion)) as horas_promedio
FROM afiliaciones
WHERE estado = 'validado'
  AND fecha_validacion IS NOT NULL;
```

---

## Respaldo y Restauración

### Respaldo Completo

```bash
mysqldump -u usuario -p gestion_arl > backup_$(date +%Y%m%d).sql
```

### Respaldo Solo Datos

```bash
mysqldump -u usuario -p --no-create-info gestion_arl > datos_$(date +%Y%m%d).sql
```

### Restaurar

```bash
mysql -u usuario -p gestion_arl < backup.sql
```

---

## Próximos Pasos

- [Comandos Artisan](/docs/referencia/comandos/)
- [Solución de Problemas](/docs/referencia/troubleshooting/)
