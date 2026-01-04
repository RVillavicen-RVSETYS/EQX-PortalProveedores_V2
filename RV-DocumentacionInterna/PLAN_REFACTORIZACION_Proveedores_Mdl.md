# PLAN DE REFACTORIZACIÓN - Proveedores_Mdl.php

**Fecha:** 2025-01-XX
**Objetivo:** Eliminar funciones duplicadas y usar el método genérico `actualizarDatosProveedor()`

---

## 📋 FUNCIONES A REFACTORIZAR

### ✅ Funciones simples (fácil refactorización):

1. **`actualizaRFC($idProveedor, $nuevoRFC)`** → Línea 382
   - **Uso actual:** `ControlProveedoresController.php` línea 312
   - **Reemplazo:** `actualizarDatosProveedor(['rfc' => $nuevoRFC], ['id' => $idProveedor])`
   - **Complejidad:** ⭐ Baja

2. **`actualizaCorreo($idProveedor, $nuevoCorreo)`** → Línea 509
   - **Uso actual:** `ControlProveedoresController.php` línea 343
   - **Reemplazo:** `actualizarDatosProveedor(['correo' => $nuevoCorreo], ['id' => $idProveedor])`
   - **Complejidad:** ⭐ Baja

3. **`actualizaPassword($idProveedor, $nuevaPass)`** → Línea 553
   - **Uso actual:** `ControlProveedoresController.php` línea 408
   - **Nota:** El controlador ya hace `password_hash()` antes de llamar al modelo
   - **Reemplazo:** `actualizarDatosProveedor(['pass' => $passEncript], ['id' => $idProveedor])`
   - **Nota:** El método genérico ya maneja el hash automáticamente (línea 880-881)
   - **Complejidad:** ⭐ Baja (pero hay que quitar el hash del controlador)

### ⚠️ Funciones complejas (evaluar por separado):

4. **`actualizaProveedoresTemp()`** → Línea 597
   - **Uso actual:** `ControlProveedoresController.php` línea 371
   - **Complejidad:** ⭐⭐⭐ Alta
   - **Razón:** Hace INSERT con ON DUPLICATE KEY UPDATE, sincroniza con BD externa (Silme)
   - **Decisión:** MANTENER (es un caso especial de sincronización)

5. **`actualizaProveedores()`** → Línea 699
   - **Uso actual:** `ControlProveedoresController.php` línea 374
   - **Complejidad:** ⭐⭐⭐ Alta
   - **Razón:** Sincroniza desde tabla temporal a tabla principal
   - **Decisión:** MANTENER (es un caso especial de sincronización)

---

## 🔄 CAMBIOS REQUERIDOS

### 1. En `ControlProveedoresController.php`:

#### Método `actualizarRFC()` (línea ~300):
```php
// ANTES:
$resultProveedores = $proveedoresModel->actualizaRFC($idProveedor, $nuevoRFC);

// DESPUÉS:
$campos = [
    'rfc' => $nuevoRFC,
];

$filtros = [
    'id' => $idProveedor,
];

$resultProveedores = $proveedoresModel->actualizarDatosProveedor($campos, $filtros);

// Ajustar respuesta (cambiar 'data' por 'message'):
if ($resultProveedores['success']) {
    $Message = $resultProveedores['message']; // Cambiar de 'data' a 'message'
    // ...
}
```

#### Método `actualizarCorreo()` (línea ~329):
```php
// ANTES:
$resultProveedores = $proveedoresModel->actualizaCorreo($idProveedor, $nuevoCorreo);

// DESPUÉS:
$campos = [
    'correo' => $nuevoCorreo,
];

$filtros = [
    'id' => $idProveedor,
];

$resultProveedores = $proveedoresModel->actualizarDatosProveedor($campos, $filtros);

// Ajustar respuesta:
if ($resultProveedores['success']) {
    $Message = $resultProveedores['message']; // Cambiar de 'data' a 'message'
    // ...
}
```

#### Método `actualizarPassword()` (línea ~392):
```php
// ANTES:
$passEncript = password_hash($nuevaPass, PASSWORD_DEFAULT);
$resultProveedores = $proveedoresModel->actualizaPassword($idProveedor, $passEncript);

// DESPUÉS:
// ELIMINAR la línea de password_hash (el modelo lo hace automáticamente)
$campos = [
    'pass' => $nuevaPass, // Pasar la contraseña sin encriptar
];

$filtros = [
    'id' => $idProveedor,
];

$resultProveedores = $proveedoresModel->actualizarDatosProveedor($campos, $filtros);

// Ajustar respuesta:
if ($resultProveedores['success']) {
    $Message = $resultProveedores['message']; // Cambiar de 'data' a 'message'
    // ...
}
```

### 2. En `Proveedores_Mdl.php`:

#### Eliminar funciones (después de verificar que todo funciona):
- `actualizaRFC()` → Línea 382-424
- `actualizaCorreo()` → Línea 509-551
- `actualizaPassword()` → Línea 553-595

#### Mantener funciones (casos especiales):
- `actualizaProveedoresTemp()` → MANTENER
- `actualizaProveedores()` → MANTENER

---

## ⚠️ CONSIDERACIONES IMPORTANTES

### 1. Diferencia en formato de respuesta:
- **Funciones antiguas:** Retornan `['success' => true, 'data' => 'mensaje']`
- **Método genérico:** Retorna `['success' => true, 'message' => 'mensaje', 'filasAfectadas' => int]`

**Solución:** Actualizar los controladores para usar `'message'` en lugar de `'data'`

### 2. Hash de contraseña:
- **Función antigua:** Recibe la contraseña ya hasheada
- **Método genérico:** Recibe la contraseña sin hashear y la hashea automáticamente (línea 880-881)

**Solución:** Eliminar `password_hash()` del controlador

### 3. Validación de filas afectadas:
- **Funciones antiguas:** Validan `$filasAfectadas == 1`
- **Método genérico:** Valida `$filasAfectadas >= 1`

**Solución:** El método genérico es más flexible, no requiere cambios

---

## 📝 ORDEN DE EJECUCIÓN RECOMENDADO

1. ✅ **Paso 1:** Refactorizar `actualizarRFC()`
   - Actualizar controlador
   - Probar funcionalidad
   - Eliminar función del modelo

2. ✅ **Paso 2:** Refactorizar `actualizarCorreo()`
   - Actualizar controlador
   - Probar funcionalidad
   - Eliminar función del modelo

3. ✅ **Paso 3:** Refactorizar `actualizarPassword()`
   - Actualizar controlador (eliminar hash)
   - Probar funcionalidad
   - Eliminar función del modelo

4. ⚠️ **Paso 4:** Evaluar `actualizaProveedoresTemp()` y `actualizaProveedores()`
   - Decidir si mantener o refactorizar (probablemente mantener)

---

## 🧪 CHECKLIST DE PRUEBAS

Para cada función refactorizada, verificar:

- [ ] El campo se actualiza correctamente en la BD
- [ ] El mensaje de éxito se muestra correctamente
- [ ] El mensaje de error se muestra correctamente
- [ ] Los logs se generan correctamente
- [ ] No hay errores en el log de PHP
- [ ] La respuesta JSON tiene el formato correcto

---

## 📊 RESULTADO ESPERADO

**Antes:**
- 5 funciones de actualización en `Proveedores_Mdl.php`
- 3 funciones específicas que violan el estándar

**Después:**
- 3 funciones de actualización en `Proveedores_Mdl.php`
  - `actualizarDatosProveedor()` (genérico) ✅
  - `actualizaProveedoresTemp()` (caso especial) ⚠️
  - `actualizaProveedores()` (caso especial) ⚠️
- 0 funciones específicas que violan el estándar (para campos simples)

---

**Nota:** Este plan debe ejecutarse paso a paso, probando cada cambio antes de continuar.

