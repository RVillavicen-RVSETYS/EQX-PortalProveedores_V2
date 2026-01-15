### Resumen General
El proceso está diseñado para validar un Complemento de Pago (en formato XML y PDF) contra las facturas que el portal tenga del proveedor especificado, tanto a nivel de datos internos como fiscalmente ante el SAT, antes de registrarlo permanentemente. Es un flujo de múltiples pasos, scripts de backend para validación preliminar y final, una clase para leer el XML, y servicios externos.

---

### Proceso Detallado Paso a Paso

**Paso 1 En caso de cargarse como Administrador: Vamos a CargarFacturas, en la tarjeta de "Carga Complementos de Pago" Seleccionamos el Proveedor **

1.  **Carga de Archivos:** El usuario utiliza un formulario (`formComplemento`) para seleccionar el archivo `.xml` y el archivo `.pdf` de su complemento de pago.
2.  **Inicio del Proceso:** Al presionar un botón ( "Cargar Complemento de Pago"), se ejecuta la función de JavaScript. Esta función recopila los IDs de las facturas seleccionadas y los archivos, y los envía mediante una petición `AJAX` (usando `FormData`) al controlador "app\Controllers\Administrador\CargarFacturasController.php" para ejecutar registraNuevoComplementoPago().

Al igual que registraNuevaNotaCredito() debera enviar la información al controlador Global para que hay se procese y llevar la misma estructura.

Que necesitamos antes de guardar
 * Validación Preliminar de documentos (Garantizar que los subieron y que los datos no vienen vacios)
 * Lectura inicial del XML para identificar la versión del CFDI (3.3 o 4.0) y llama a un método específico para cada una
 * Lectura del XML y extrae los datos clave del XML: RFC del emisor/receptor, folio, fecha, total y, lo más importante, los UUID de los documentos relacionados (`cfdi:DoctoRelacionado`).
 * Análisis del Contenido

 Nosotros aplicamos 2 tipos de reglas las internas y las de negocio.
  Internas:
    *   Verifica que el `TipoDeComprobante` en el XML sea "P" (Pago).
    *   Verificamos que no este duplicado ese CFDI buscando su UUID que no este registrado y activo en la tabla 'cfdi_complementoPago' para verificar suuuid y estatus
    *   Verifica la cantidad de Pago que vienen en el complemento y con que UUID estan relacionados para garantizar que todos los UUIDs los tenemos y que son de facturas que ya tenemos en el sistema.

Reglas de Negocio:
    * Se cruza la información del complemento (RFCs, moneda) con los datos de las facturas relacionadas almacenadas en la base de datos para garantizar que coincidan.

    * Aqui dame mas opciones no se que mas validar

Pasamos a la validación Fiscal tal como los otros CFDIs

3.  **Transacción de Base de Datos:**
    *   Si todas las validaciones (internas y fiscales) son exitosas, el script inicia una transacción en la base de datos MySQL (`mysqli_begin_transaction`).
    *   **Inserción de Datos:**
        *   Inserta un registro principal en la tabla `cfdi_complementoPago` con los datos generales del complemento.
        *   Inserta un registro por cada pago del complemento en la tabla `cfdi_complementoPagoDet`, relacionando el complemento con sus facturas.
    *   **Confirmación:** Si todas las inserciones son correctas, se confirma la transacción con `mysqli_commit()`. Si algo falla, se revierte con `mysqli_rollback()`.

4.  **Manejo Final de Archivos:**
    No olvides almacenar los archivos en su ruta correspondiente

5.  **Respuesta Final:** El script devuelve un mensaje de éxito o error a la interfaz de usuario, completando el ciclo.
