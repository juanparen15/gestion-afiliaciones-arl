# Sistema de Notificaciones por Correo Electrónico

## Descripción General

El sistema de gestión de afiliaciones ARL cuenta con un sistema automático de notificaciones por correo electrónico que se activa cuando se registra una nueva afiliación. Este sistema está diseñado para notificar a los usuarios con rol **SSST (Seguridad y Salud en el Trabajo)** para que puedan revisar y aprobar/rechazar las afiliaciones.

---

## Flujo de Trabajo

### 1. Creación de Nueva Afiliación

Cuando un usuario con rol **Dependencia** crea una nueva afiliación:

1. Se registra la afiliación con estado `pendiente`
2. Se dispara automáticamente el evento `AfiliacionCreada`
3. El listener `EnviarNotificacionNuevaAfiliacion` captura el evento
4. Se buscan todos los usuarios con rol **SSST**
5. Se envía un correo electrónico a cada usuario SSST

### 2. Contenido del Correo Electrónico

El correo enviado incluye:

- **Información del Contratista:**
  - Nombre completo
  - Tipo y número de documento
  - Email
  - Teléfono

- **Información del Contrato:**
  - Número de contrato
  - Objeto contractual
  - Valor del contrato
  - Duración (meses y días)
  - Fechas de inicio y fin

- **Información ARL:**
  - Nombre de la ARL
  - Nivel de riesgo
  - IBC (Ingreso Base de Cotización)

- **Dependencia y Usuario:**
  - Dependencia que creó la afiliación
  - Usuario que la creó
  - Fecha de creación

- **Enlace directo:**
  - Botón "Revisar Afiliación" que lleva directamente al detalle de la afiliación en el sistema

---

## Aprobación y Rechazo de Afiliaciones

### Validación (Aprobación)

Cuando un usuario SSST valida una afiliación:

1. Abre la afiliación desde el correo o desde el panel
2. Hace clic en el botón **"Validar"**
3. Se abre un modal que solicita:
   - **PDF del Afiliado en ARL** (obligatorio)
     - Archivo PDF generado en el sistema de la ARL
     - Tamaño máximo: 10MB
   - **Observaciones** (opcional)
     - Campo de texto para notas adicionales

4. Al confirmar:
   - El estado cambia a `validado`
   - Se guarda el PDF en el sistema
   - Se registra quién validó y cuándo
   - Se muestra una notificación de éxito

### Rechazo

Cuando un usuario SSST rechaza una afiliación:

1. Hace clic en el botón **"Rechazar"**
2. Se abre un modal que solicita:
   - **Motivo del Rechazo** (obligatorio)
     - Campo de texto para describir por qué se rechaza

3. Al confirmar:
   - El estado cambia a `rechazado`
   - Se guarda el motivo del rechazo
   - Se registra quién rechazó y cuándo
   - Se muestra una notificación de advertencia

---

## Configuración del Correo Electrónico

### Desarrollo

En desarrollo, el sistema está configurado para simular el envío de correos sin enviarlos realmente. Los correos se guardan en el archivo de logs:

```
storage/logs/laravel.log
```

### Producción

Para activar el envío real de correos, edite el archivo `.env`:

#### Gmail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-correo@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicación
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="tu-correo@gmail.com"
MAIL_FROM_NAME="Sistema de Gestión ARL"
```

**Nota:** Para Gmail, debes generar una "Contraseña de Aplicación" en tu cuenta de Google.

#### Outlook/Office365

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=tu-correo@entidad.gov.co
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@entidad.gov.co"
MAIL_FROM_NAME="Sistema de Gestión ARL"
```

#### Servidor SMTP Personalizado

```env
MAIL_MAILER=smtp
MAIL_HOST=tu-servidor-smtp.com
MAIL_PORT=587
MAIL_USERNAME=usuario@dominio.com
MAIL_PASSWORD=contraseña-segura
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@entidad.gov.co"
MAIL_FROM_NAME="Sistema de Gestión ARL"
```

---

## Estructura Técnica

### Archivos Principales

```
app/
├── Events/
│   └── AfiliacionCreada.php              # Evento disparado al crear afiliación
├── Listeners/
│   └── EnviarNotificacionNuevaAfiliacion.php  # Listener que envía el correo
├── Mail/
│   └── NuevaAfiliacionMail.php           # Clase Mailable del correo
├── Observers/
│   └── AfiliacionObserver.php            # Observer que escucha cambios en afiliaciones
└── Providers/
    └── AppServiceProvider.php            # Registro del observer y listener

resources/views/emails/
└── nueva-afiliacion.blade.php            # Plantilla HTML del correo

database/migrations/
└── 2025_11_06_144928_add_pdf_arl_to_afiliaciones_table.php  # Migración del campo PDF
```

### Base de Datos

**Campo agregado a tabla `afiliaciones`:**

```php
$table->string('pdf_arl')->nullable();
```

Este campo almacena la ruta del PDF del afiliado en el sistema de ARL.

---

## Vista del PDF en el Sistema

### En el Listado

En la tabla de afiliaciones, hay una columna **"PDF ARL"** que muestra:

- ✓ Icono verde con check: PDF cargado
- ○ Icono gris: Sin PDF

### En la Vista de Detalles

En la pestaña **"Información ARL"** de una afiliación validada:

- Se muestra el campo **"PDF del Afiliado en Sistema ARL"**
- Se puede descargar el PDF
- Se puede visualizar en el navegador
- Solo visible para afiliaciones con estado `validado`

---

## Permisos y Roles

### Usuario Dependencia
- Crear afiliaciones
- Editar afiliaciones de su dependencia
- Ver afiliaciones de su dependencia

### Usuario SSST
- Ver TODAS las afiliaciones (sin importar la dependencia)
- Validar afiliaciones (con carga de PDF)
- Rechazar afiliaciones (con justificación obligatoria)
- Recibir notificaciones por correo de nuevas afiliaciones

### Super Admin
- Acceso completo a todas las funcionalidades
- Puede validar/rechazar aunque no es SSST

---

## Pruebas del Sistema

### Probar Envío de Correos en Desarrollo

1. Crea una nueva afiliación
2. Revisa el archivo `storage/logs/laravel.log`
3. Busca el contenido del correo simulado

### Probar Envío Real

1. Configura las variables SMTP en `.env`
2. Ejecuta: `php artisan config:clear`
3. Crea una nueva afiliación
4. Verifica que llegue el correo a los usuarios SSST

### Probar Validación con PDF

1. Inicia sesión como usuario SSST
2. Ve a Afiliaciones > Filtrar por "Pendiente"
3. Haz clic en "Validar" en una afiliación
4. Sube un PDF de prueba
5. Confirma la validación
6. Verifica que el estado cambió a "Validado"
7. Abre la afiliación y ve a la pestaña "Información ARL"
8. Verifica que el PDF se puede descargar

---

## Solución de Problemas

### Los correos no se envían

1. Verifica la configuración SMTP en `.env`
2. Verifica que `MAIL_MAILER=smtp` (no `log`)
3. Ejecuta: `php artisan config:clear`
4. Revisa los logs: `storage/logs/laravel.log`

### No hay usuarios SSST para notificar

1. Ve a Usuarios en el panel admin
2. Asigna el rol "SSST" al menos a un usuario
3. Verifica que el usuario tenga un email válido

### El PDF no se guarda

1. Verifica que la carpeta `storage/app` tenga permisos de escritura
2. Ejecuta: `php artisan storage:link`
3. Verifica que el archivo no exceda 10MB

### El PDF no se muestra en la vista

1. Verifica que la afiliación esté en estado "validado"
2. El campo solo es visible para afiliaciones validadas

---

## Mantenimiento

### Limpiar archivos temporales de PDFs

Los PDFs se guardan en:
```
storage/app/afiliaciones/pdfs-arl/
```

Puedes crear un comando programado para limpiar PDFs antiguos de afiliaciones eliminadas.

### Monitorear envío de correos

Revisa regularmente:
```
storage/logs/laravel.log
```

Para verificar que los correos se estén enviando correctamente.

---

## Contacto y Soporte

Para reportar problemas o solicitar mejoras, contacta al administrador del sistema.

---

**Última actualización:** 06/11/2025
**Versión del sistema:** 1.0.0
