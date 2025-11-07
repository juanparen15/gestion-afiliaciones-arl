# 📥 Guía Rápida de Importación de Afiliaciones

## 🎯 Pasos para Importar Afiliaciones

### 1️⃣ Preparar el Archivo Excel

**Opción A: Crear desde cero**

1. Abrir Excel o Google Sheets
2. En la primera fila, copiar **exactamente** estos nombres de columnas:

```
No. CONTRATO | OBJETO CONTRATO | CC CONTRATISTA | CONTRATISTA | VALOR DEL CONTRATO | MESES | DIAS | Honorarios mensual | IBC | Fecha ingreso A partir de Acta inicio | Fecha retiro | Secretaría | Fecha de Nacimiento | Nivel de riesgo | No. Celular | Barrio | Dirección Residencia | EPS | AFP | Dirección de correo Electronica | FECHA DE AFILIACION | FECHA TERMIANCION AFILIACION
```

3. A partir de la fila 2, ingresar los datos

**Opción B: Usar plantilla**

1. Descargar la plantilla desde: `storage/app/public/plantilla_importacion_afiliaciones.csv`
2. Abrir con Excel
3. Reemplazar el registro de ejemplo con tus datos

### 2️⃣ Ingresar al Sistema

1. Acceder a: http://localhost:8000/admin
2. Iniciar sesión con tus credenciales
3. Hacer clic en **"Afiliaciones"** en el menú lateral

### 3️⃣ Importar el Archivo

1. Buscar el botón **"Importar Excel"** (verde, arriba a la derecha)
2. Hacer clic en el botón
3. En el modal que aparece, hacer clic en **"Choose file"**
4. Seleccionar tu archivo Excel (.xlsx, .xls o .csv)
5. Hacer clic en **"Importar"**
6. Esperar el mensaje de confirmación

### 4️⃣ Verificar la Importación

✅ **Importación Exitosa**
```
Notificación verde: "Importación exitosa"
Mensaje: "Todos los registros se importaron correctamente"
```

⚠️ **Importación con Errores**
```
Notificación amarilla: "Importación completada con errores"
Mensaje: Muestra los primeros 3 errores encontrados
```

❌ **Error en la Importación**
```
Notificación roja: "Error en la importación"
Mensaje: Detalles del error
```

---

## 📋 Formato de Datos

### Fechas ✅
```
Formatos aceptados:
28/01/2025
28-01-2025
28-ene-2025
2025-01-28
```

### Valores Monetarios ✅
```
Formatos aceptados:
$18.600.000,00
18600000
18.600.000
18,600,000
```

### Nivel de Riesgo ✅
```
Formatos aceptados:
1, 2, 3, 4, 5 → Se convierten a I, II, III, IV, V
I, II, III, IV, V → Se mantienen
```

### Secretaría/Dependencia ✅
```
Escribir el nombre o código de la dependencia
Ejemplos:
- General
- Sistemas e Informática
- SIS
- Talento Humano
```

---

## ⚡ Consejos Rápidos

1. **Primera vez**: Importar solo 1-2 registros para probar
2. **Dependencias**: Crear primero las dependencias en el módulo correspondiente
3. **Formato**: Mantener el formato de la primera fila exacto
4. **Fechas**: Si hay error, usar formato dd/mm/yyyy
5. **Valores**: Eliminar símbolos $ si hay problemas

---

## 🔍 Solución de Problemas Comunes

### "Faltan columnas requeridas"
➡️ **Solución**: Copiar y pegar los nombres de columnas desde esta guía

### "Dependencia no encontrada"
➡️ **Solución**: 
   1. Ir a "Dependencias"
   2. Crear la dependencia
   3. Volver a importar

### "Formato de fecha inválido"
➡️ **Solución**: Cambiar a formato dd/mm/yyyy

### "No se pudo leer el archivo"
➡️ **Solución**: 
   - Guardar como .xlsx (Excel Workbook)
   - Verificar que el archivo no esté corrupto

---

## 📊 Ejemplo de Registro

```
No. CONTRATO: 19
OBJETO CONTRATO: PRESTACIÓN DE SERVICIOS PROFESIONALES...
CC CONTRATISTA: 91275160
CONTRATISTA: JUAN MAURICIO ROMERO QUIÑONES
VALOR DEL CONTRATO: 18600000
MESES: 138
DIAS: 0
Honorarios mensual: 4650000
IBC: 1860000
Fecha ingreso: 28/01/2025
Fecha retiro: 14/06/2025
Secretaría: General
Fecha de Nacimiento: 12/10/1970
Nivel de riesgo: 4
No. Celular: 3244196814
Barrio: Villatex
Dirección Residencia: Calle 20b No. 3-04
EPS: SURA
AFP: PROTECCION
Correo: juanmarroqui70@gmail.com
FECHA DE AFILIACION: 28/01/2025
FECHA TERMINACION: 27/07/2025
```

---

## 📤 Exportar para Re-importar

Si quieres exportar datos existentes para editarlos:

1. Ir a "Afiliaciones"
2. Seleccionar registros (checkboxes)
3. Clic en menú de acciones masivas
4. Seleccionar "Exportar"
5. Editar el archivo descargado
6. Re-importar

---

## 🎯 Validaciones Automáticas

El sistema valida automáticamente:
- ✅ Campos obligatorios (Contrato, CC, Nombre)
- ✅ Formato de fechas
- ✅ Formato de emails
- ✅ Valores numéricos
- ✅ Existencia de dependencias

---

## 📞 Soporte

Si tienes problemas con la importación:
1. Revisar esta guía
2. Verificar el formato del archivo
3. Intentar con un solo registro primero
4. Contactar al administrador del sistema

---

**¡Listo para importar!** 🚀
