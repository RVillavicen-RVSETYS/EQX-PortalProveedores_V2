# Proceso de carga de factura nacional (nueva factura) — flujo y validaciones

Documento de referencia para revisar qué hace el portal paso a paso al cargar una factura de ingreso (CFDI 4.0), con énfasis en **montos** y en el caso típico: factura ~**70 500** vs sistema que espera ~**63 450**.

---

## 1. Punto de entrada (administrador)

| Paso | Archivo / método | Qué hace |
|------|------------------|----------|
| 1.1 | `app/Controllers/Administrador/CargarFacturasController.php` → `registraNuevaFactura()` | Recibe `POST`/`FILES`, toma `noProveedor` y delega al controlador global. |
| 1.2 | `app/Globals/Controllers/CargaFacturasGlobalController.php` → `registraNuevaFactura()` | **Validaciones mínimas de formulario:** número de proveedor, orden de compra obligatoria, XML y PDF de factura obligatorios. Normaliza lista de HES (saltos de línea → lista). Arma el arreglo `$valores` (OC, proveedor, HES, excepciones admin, notas de crédito, archivos). |
| 1.3 | Mismo archivo | Instancia `SubirFacturaController` y llama `cargarFacturas($valores)`. |

*(Flujo equivalente para proveedor si existe otra ruta que también llame a `cargarFacturas`; la lógica central es la misma.)*

---

## 2. Orquestación principal: `SubirFacturaController::cargarFacturas`

Archivo: `app/Globals/Controllers/SubirFacturaController.php`

| # | Acción | Modelo / controlador | Notas |
|---|--------|----------------------|--------|
| 2.1 | Valida **orden de compra** para el proveedor | `OrdenCompra_Mdl::verificaOrdenCompra` | Debe existir y corresponder al proveedor. |
| 2.2 | Verifica si la OC **debe anticipo** | `Anticipos_Mdl::verificaAnticipoDeOrdenCompra` | Si hay anticipos pendientes, exige **notas de crédito** en el mismo envío (`reqNotaCredito = 1`). |
| 2.3 | Valida **HES** contra la OC | `HojaEntrada_Mdl::verificaHojaEntrada` | Las hojas de entrada deben ser válidas para esa orden. |
| 2.4 | Si aplica, valida PDF/XML de **notas de crédito** | `DocumentosController::verificadorDeDocumentoARecibir` | Por cada plantilla de NC. |
| 2.5 | Valida **PDF** de la factura | `DocumentosController::verificadorDeDocumentoARecibir(..., 'pdf')` | Tipo, tamaño, etc. |
| 2.6 | Valida **XML** de la factura | `DocumentosController::verificadorDeDocumentoARecibir(..., 'xml')` | |
| 2.7 | **Reglas de negocio + fiscal + registro** | `FacturasNacionalesController::verificaNuevaFacturaIngresos` | Aquí ocurren casi todas las validaciones de negocio y montos. |
| 2.8 | Si todo OK | `FacturasNacionalesController::registraNuevaFacturaIngresos` | Persistencia (`RegistroCFDIsv40_Mdl`, tablas `compras`, `detcompras`, `cfdi_facturas`, etc.). |
| 2.9 | Si hubo NC junto con la factura | `procesarNotasCreditoConFactura` | Valida y registra NC; si falla, hace **rollback** de la factura. |

---

## 3. Núcleo de validación: `FacturasNacionalesController::verificaNuevaFacturaIngresos`

Archivo: `app/Globals/Controllers/FacturasNacionalesController.php`

Orden aproximado:

1. **Montos desde recepciones (HES)**  
   - `HojaEntrada_Mdl::dataMontosHES($HES, $OC)`  
   - Consulta la vista **`vw_ext_PortalProveedores_MontosHES`** y **suma la columna `subtotal`** de todas las filas de las HES indicadas.  
   - Ese total queda en `data['Subtotal']` (nombre clave en el arreglo devuelto).  
   - Valida consistencia: misma OC, misma sociedad, misma moneda, mismo `CPago` entre todas las HES.

2. **Tolerancia de diferencia (empresa + moneda + bloqueos)**  
   - `RecepcionCFDIs_Mdl::diferenciaMontoXMoneda($noProveedor, $idEmpresa, $idMoneda)`  
   - Si el proveedor está en `conf_provBloqDiferencias` → reglas **restrictivas** (montos superior/inferior en 0).  
   - Si no, lee `conf_diferenciaMontos` (`tipoRegla`, `montoSup`/`montoInf`, `porcentajeSup`/`porcentajeInf`).

3. **Lectura del XML**  
   - `CfdisController::leerCfdiXML` → en 4.0 usa la lógica de `cfdisv40` / parser que llena `Comprobante`, `Emisor`, `Receptor`, `TimbreFiscal`, impuestos, etc.

4. **Configuración de recepción por sociedad y tipo de comprobante**  
   - `RecepcionCFDIs_Mdl::configuracionBaseRecepcionCFDI($idEmpresa, $tipoCFDI, $version)`  
   - Aporta entre otros: `usosCFDI`, `formasPagoPUE`, `tiempoVigencia`.

5. **Empresa y proveedor**  
   - `Empresas_Mdl::empresaPorId`  
   - `Proveedores_Mdl::obtenerDatosProveedor`  
   - `Proveedores_Mdl::exepcionesProveedoresFacturas` → banderas de excepción (año fiscal, uso CFDI, PUE, bloqueo de montos, etc.).  
   - `NotasCredito_Mdl::verificaNotaCreditoDeOrdenCompra` → políticas/NC asociadas a la OC (para lógica tipo SilmeAgro).

6. **Arreglo `configParaValidaciones`**  
   - `datosRecepciones` ← resultado de `dataMontosHES` (incluye **`Subtotal`** = suma HES).  
   - `configCFDI`, `diferenciaMontos`, `excepcionesProveedor`, `notasCreditos`.

7. **Reglas internas (datos del XML vs catálogo portal)**  
   - Clase dinámica `ReglasAplicadas{version}` → para 4.0: `ReglasAplicadasv40::validarReglasInternasNacional_Ingresos`  
   - Ver sección 4.

8. **Reglas de negocio (incluye año fiscal, uso CFDI, montos, etc.)**  
   - `ReglasAplicadasv40::validarReglasNegocioNacional_Ingresos`  
   - Ver sección 5 (montos en detalle).

9. **Validación fiscal (SAT)**  
   - `cfdisv40::validarCFDIv1`  
   - Consulta SOAP al servicio del SAT (`ConsultaCFDIService`) con emisor, receptor, **Total** del comprobante y UUID.  
   - Valida estado (Vigente/Cancelado/No encontrado) y lista **EFOS**.

---

## 4. `validarReglasInternasNacional_Ingresos` (CFDI vs proveedor/empresa)

Archivo: `app/Globals/Controllers/Reglas/ReglasAplicadasv40.php`

- UUID no vacío y **no duplicado** en BD (`CFDIs_Mdl::obtenerFacturasPorUUID`).
- Versión del comprobante = **4.0**.
- **RFC emisor** = RFC del proveedor en portal.
- **Nombre emisor** = razón social del proveedor.
- **Régimen fiscal emisor** = el del proveedor en portal.
- **RFC receptor** = RFC de la empresa (sociedad).
- **Nombre receptor** = razón social de la empresa.
- **Domicilio fiscal receptor** = CP configurado para la empresa.
- **Régimen fiscal receptor** = `idRegimen` de la empresa en portal.

*No valida aquí el uso de CFDI ni los montos contra HES; eso va en reglas de negocio.*

---

## 5. `validarReglasNegocioNacional_Ingresos` — lista de chequeos

Mismo archivo `ReglasAplicadasv40.php`.

| Validación | Condición resumida | Excepciones (tablas / flags en `excepcionesProveedor`) |
|------------|-------------------|--------------------------------------------------------|
| Tipo de comprobante | Debe ser **I** (Ingreso) | — |
| **Año fiscal** | Año de `Comprobante.Fecha` y de `TimbreFiscal.FechaTimbrado` = **año calendario actual** | Si `AnioFiscal` está activo para el proveedor, **no** aplica esta restricción. |
| **Tiempo de emisión** | Fecha factura y timbrado no anteriores a `now() - tiempoVigencia` (ej. `6 month` desde `configCFDI`) | Si `FechaEmision` exenta → omite. |
| **Uso CFDI** | `Receptor.UsoCFDI` ∈ lista de `configCFDI['usosCFDI']` | Si `UsoCfdiDistinto`, se **unen** los usos adicionales del proveedor (`UsoCfdi`). |
| Método / forma PPD | Si `MetodoPago = PPD` → `FormaPago` debe ser **99** | — |
| Método / forma PUE | Si `PUE` → `FormaPago` en lista `formasPagoPUE` | — |
| PUE “pagable en el mes” | Con PUE, según `CPago` de las HES, el mes de “fecha pago permitida” vs mes actual | Excepción `PermitirPueSiempre` con fecha de expiración vigente. |
| **Exportación** | `Comprobante.Exportacion` = **01** | — |
| **Moneda** | `Comprobante.Moneda` = `idMoneda` traída de las HES (como código en el XML) | — |
| **Montos** | Ver sección 6 | `IgnoraDescuento`, `BloqDiferenciaMonto`, reglas en `conf_diferenciaMontos`, proveedor en `conf_provBloqDiferencias`. |

---

## 6. Validación de montos (detalle para el caso 70 500 vs 63 450)

### 6.1 Monto “esperado” (lado recepciones / ERP)

- Viene de **`dataMontosHES['data']['Subtotal']`**.  
- Es la **suma de `subtotal`** de la vista `vw_ext_PortalProveedores_MontosHES` para las HES capturadas.  
- Ese valor es el que el mensaje de error describe como lo que “debería ser” (ej. **63 450**).

### 6.2 Monto “del XML” que compara el código

En `validarReglasNegocioNacional_Ingresos`:

```php
$subtotalXML = $dataXML['Comprobante']['Total'] ?? 0;
```

**Importante:** la variable se llama `$subtotalXML`, pero se asigna con **`Comprobante.Total`** (total del CFDI, normalmente **subtotal + impuestos − descuentos**, etc.), **no** con `Comprobante.SubTotal`.

Luego:

1. Si el proveedor **no** tiene `IgnoraDescuento`:  
   `$subtotalXML -= Comprobante.Descuento` (descuento global del comprobante).
2. Si existiera la rama de “descontar promociones” (notas de crédito de política comercial en la OC), se restarían montos o porcentajes sobre ese valor.

### 6.3 Coherencia conceptual (por qué puede no “empatar”)

Si en el ERP/recepciones el campo `subtotal` de la vista es **base sin IVA** (o un importe distinto al **Total** del XML), pero el sistema compara contra el **`Total` del CFDI**, aparecen diferencias del orden **IVA u otros impuestos** (o mezcla subtotal vs total).  
**70 500 vs 63 450** sugiere revisar en paralelo:

- Qué devuelve exactamente `subtotal` en `vw_ext_PortalProveedores_MontosHES` (¿neto?, ¿con algún ajuste?).  
- En el XML: `SubTotal`, `Total`, `Descuento`, impuestos trasladados/retenidos.

### 6.4 Tolerancia y reglas numéricas

- `subtotalConfig = datosRecepciones['Subtotal']` (suma HES).  
- Si **`BloqDiferenciaMonto`** para el proveedor: exige **igualdad exacta** `$subtotalXML == $subtotalConfig`.  
- Si no: calcula `minimo` y `maximo` según `tipoRegla` (1 = solo montos fijos, 2 = solo porcentajes, 3 = intersección de ambos rangos) usando `diferenciaMontos`.  
- Falla si:  
  `($subtotalXML <= $minimo || $subtotalXML >= $maximo) && $subtotalXML != 0`  
  (es decir, el valor debe quedar **estrictamente entre** `minimo` y `maximo`; en el mensaje usuario suele ver el rango y el “debería ser” ≈ `subtotalConfig`).

### 6.5 Descuentos por políticas / “asociar para que empaten”

El comentario en código menciona **SilmeAgro** e **INNOVAK**: en algunos casos se restan al monto del XML los importes de notas de crédito de política (`FormaCobro == 'NC'`, `idPoliticaComercial > 0`).

**Origen del flag:** columna **`proveedores.descontarPromocionesAplicables`** (1 = activo). Se lee en `Proveedores_Mdl::exepcionesProveedoresFacturas` y llega a reglas como **`$configParaValidaciones['excepcionesProveedor']['DescontarPromocionesAplicables']`** (boolean).  
**Nota:** En una versión anterior del código se consultaba por error `$configParaValidaciones['descontarPromocionesAplicables']` en la raíz (clave inexistente → warning PHP y la rama no corría). Debe usarse solo la ruta bajo `excepcionesProveedor`.

Para que se aplique el 10 % de “Descuento por pronto pago” contra el total de la factura, el proveedor debe tener **`descontarPromocionesAplicables = 1`** en BD y existir la política en `notasCreditos` con `idPoliticaComercial > 0` y `FormaCobro == 'NC'`.

**Administración:** en **Administrador → Excepciones a proveedores → pestaña “Ajuste políticas comerciales”** se puede activar o quitar el flag por proveedor (actualiza `proveedores.descontarPromocionesAplicables` y deja el motivo en el log `LOG_SYSTEM/excepciones/{año}/politicasComerciales.log`).

---

## 7. Validación fiscal (`cfdisv40::validarCFDIv1`)

- Arma la expresión impresa con RFC emisor, RFC receptor, **Total** (6 decimales) y UUID.  
- Llama al web service del SAT.  
- Interpreta **Estado**, **CodigoEstatus**, **ValidacionEFOS**, etc.

No compara contra HES; solo estado del CFDI ante el SAT.

---

## 8. Registro en BD (tras pasar todo)

`RegistroCFDIsv40_Mdl::registrarCFDI_Ingresos40` (resumen):

- Inserta `compras` usando **SubTotal** y **Total** del XML del comprobante, moneda y sociedad desde `dataMontosHES`, etc.  
- Inserta `detcompras` por cada línea de `resultQuery` de las HES (montos por recepción).  
- Inserta `cfdi_facturas` con `monto`/`subtotal` del XML, `usoCfdi`, sellos, resultado de validación fiscal, etc.

---

## 9. Checklist rápido para depurar un rechazo por monto

1. Revisar en BD o log el resultado de **`dataMontosHES`**: valor `Subtotal` y filas de `resultQuery`.  
2. Abrir el XML: **`Total`**, **`SubTotal`**, **`Descuento`**, impuestos.  
3. Confirmar si la comparación debe ser **Total vs suma HES** o si negocio esperaba **SubTotal vs suma HES** (hoy el código usa **Total** en la variable `$subtotalXML`).  
4. Revisar `conf_diferenciaMontos` para esa **empresa** y **moneda**, y si el proveedor está en **`conf_provBloqDiferencias`**.  
5. Revisar excepciones: **`IgnoraDescuento`**, **`BloqDiferenciaMonto`**, **`DescontarPromocionesAplicables`** y si la rama de promociones **realmente se ejecuta** (clave `descontarPromocionesAplicables` vs `excepcionesProveedor`).  
6. Activar temporalmente `$debug = 1` en `SubirFacturaController`, `FacturasNacionalesController` y `ReglasAplicadasv40` para ver `var_dump` de `dataMontosHES`, `diferenciaMontoXMoneda` y mensajes de `debug` de reglas.

---

## 10. Archivos clave (mapa rápido)

| Tema | Ruta |
|------|------|
| Entrada admin | `app/Controllers/Administrador/CargarFacturasController.php` |
| Guardas y armado de payload | `app/Globals/Controllers/CargaFacturasGlobalController.php` |
| Pipeline OC / HES / archivos | `app/Globals/Controllers/SubirFacturaController.php` |
| Orquestación validación + registro | `app/Globals/Controllers/FacturasNacionalesController.php` |
| Reglas internas y negocio (montos) | `app/Globals/Controllers/Reglas/ReglasAplicadasv40.php` |
| Suma montos HES | `app/models/datosCompra/HojaEntrada_Mdl.php` → `dataMontosHES` |
| Tolerancias | `app/models/configuraciones/RecepcionCFDIs_Mdl.php` → `diferenciaMontoXMoneda` |
| Excepciones proveedor | `app/models/proveedores/Proveedores_Mdl.php` → `exepcionesProveedoresFacturas` |
| SAT | `app/Globals/Controllers/Cfdis/cfdisv40.php` → `validarCFDIv1` |
| INSERT factura | `app/Globals/Controllers/RegistroCFDIs/RegistroCFDIsv40_Mdl.php` |

---

---

## 11. Proveedor nacional — Inicio (`ProveedorNacional/Inicio`)

- **Carga factura (OC + HES):** el formulario envía por AJAX a `Inicio/registraNuevaFactura`, que llama a `CargaFacturasGlobalController::registraNuevaFactura(..., $noProveedor, false)` — mismo pipeline que administrador, sin `excepcionesAdmin`.
- **Validaciones previas:** `Inicio/verificaOrdenCompraFactura`, `Inicio/validaHojaEntrada`, `Inicio/cargaFormNotaCredito` delegan en los mismos controladores globales que en admin.
- **Carga por anticipo:** la vista valida el código con `Inicio/validaCodigoAnticipo` (alias de `validaAnticipo`). El **envío de PDF/XML por anticipo no está conectado** a un registro en servidor (tampoco en la vista admin equivalente); el formulario del proveedor evita el POST completo y muestra un aviso.

---

*Generado para revisión del flujo de carga y del comportamiento de montos. Ajustar este documento si cambian vistas o reglas en BD.*
