# Plan de revisión y mejoras – Módulo Excepciones Proveedores

Ruta: `/Administrador/ExcepcionesProveedores`

Ir marcando con `[x]` los puntos corregidos conforme se revisen.

---

## 1. Validación de entrada en acciones POST

- [ ] **1.1** Validar y castear en `cambiarEstatus()`: `ident` (int), `tabla` (valores permitidos 1-6), `idProveedor` (int).
- [ ] **1.2** Validar en `eliminar()`: `ident` (int).
- [ ] **1.3** Validar en `eliminarCfdiPermitido()`: `ident` (int).
- [ ] **1.4** Validar en `agregarProveedorIG()`: `idProveedor` (int), `motivo` (string no vacío).
- [ ] **1.5** Validar en `agregarProveedorEAF()`, `agregarProveedorEFE()`: `idProveedor` (int).
- [ ] **1.6** Validar en `agregarProveedorUC()`: `idProveedor` (int), `idUsoCfdi` (int/string permitido).
- [ ] **1.7** Validar en `agregarProveedorBD()`: `idProveedor` (int), `motivo` (string).
- [ ] **1.8** Validar en `agregarProveedorBUC()`: `idProveedor`, `idUsoCfdiPermitido` (array no vacío).
- [ ] **1.9** Validar en `agregarProveedorPUE()`: `idProveedor` (int), `fechaExpiracion` (fecha válida), `motivo` (string).

---

## 2. Código muerto / limpieza

- [ ] **2.1** Eliminar variable no usada `$tabla = "conf_provCfdisPermitidos"` en `cfdisPorProveedor()` (controlador).

---

## 3. Respuestas JSON

- [ ] **3.1** Enviar `header('Content-Type: application/json; charset=utf-8')` en todos los métodos del controlador que responden con `echo json_encode(...)`.

---

## 4. Mensajes de error en el frontend

- [ ] **4.1** En `index_js.php`, en los callbacks `error:` de las peticiones AJAX que cargan tabs, cambiar el mensaje genérico “Error en el inicio de sesión” por uno más adecuado (ej.: “Error al cargar la sección. Intente de nuevo.”) y, si aplica, diferenciar error de sesión.

---

## 5. Registro de CFDIs por proveedor

- [ ] **5.1** En `agregarProveedorBUC()` del controlador: normalizar `idUsoCfdiPermitido` a array y validar que no esté vacío antes de llamar al modelo; si está vacío, responder con mensaje claro (“Seleccione al menos un uso de CFDI”).

---

## 6. Reducción de duplicación en el controlador

- [ ] **6.1** Extraer método privado (ej. `obtenerDatosAreaYMenu()`) que centralice la obtención de `idArea`, `menuData`, `areaData`, `areaLink` y el manejo de errores, y usarlo en `index`, `listaIgnoraDesc`, `listaExentos`, `listaFechaEmision`, `listaCfdiDistinto`, `listaBloqueoDiferencias`, `cfdisPorProveedor` y `listaPermitirPueSiempre`.

---

## 7. Nueva excepción: “Permitir PUE siempre”

- [x] **7.1** Tabla `conf_provPermitirPueSiempre` creada en BD (id, idProveedor, fechaExpiracion, motivo, estatus, idUserReg, fechaReg). *Script: RV-DocumentacionInterna/EstruturaBD/conf_provPermitirPueSiempre.sql*
- [x] **7.2** Modelo `PermitirPueSiempre_Mdl` con: obtenerLista, getProveedores (sin permiso activo), tienePermisoActivo, registra, actualizarEstatus.
- [x] **7.3** Controlador: `listaPermitirPueSiempre`, `agregarProveedorPUE`, y caso `6` en `cambiarEstatus`.
- [x] **7.4** Vista `permitirPueSiempre.php`: formulario (proveedor, fecha expiración, motivo) y tabla (No. proveedor, Fecha expiración del permiso, Estatus con botón palomita para desactivar).
- [x] **7.5** No permitir agregar otro permiso para el mismo proveedor si ya tiene uno activo (validación en modelo y/o controlador).
- [x] **7.6** Tab “Permitir PUE siempre” en la vista principal y en `index_js.php` (cargar tab, enviar formulario, recargar al cambiar estatus).

---

## Leyenda

- `[ ]` Pendiente de revisar/corregir  
- `[x]` Revisado / Corregido  

---

*Última actualización: según avance del plan.*
