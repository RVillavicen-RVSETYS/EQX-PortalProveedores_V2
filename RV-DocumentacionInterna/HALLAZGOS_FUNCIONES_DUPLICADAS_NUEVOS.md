# HALLAZGOS ADICIONALES - FUNCIONES DUPLICADAS E INCONSISTENTES

**Fecha de análisis:** 2025-01-XX  
**Modelos analizados:** 34 modelos totales

---

## ✅ RESUMEN EJECUTIVO

Después de una revisión exhaustiva de todos los modelos en `app/Models/`, se encontraron los siguientes hallazgos adicionales:

- **Funciones de actualización/inserción que NO siguen el estándar:** 3
- **Funciones que son casos especiales (justificadas):** 3
- **Modelos solo con SELECT (sin problemas):** 28

---

## ✅ PROBLEMAS RESUELTOS

### 1. ✅ `insertarCierreAnual()` → `registraCierreAnual()` - **REFACTORIZADO**

**Ubicación:** `app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php`

**Estado:** ✅ **COMPLETADO**

**Cambios realizados:**
- ✅ Refactorizado a `registraCierreAnual($campos)` siguiendo el patrón estándar
- ✅ Implementado whitelisting de campos con validación
- ✅ Eliminada lectura de `$_SESSION['EQXident']` dentro del modelo
- ✅ Controlador actualizado para pasar `idUserReg` como parte de `$campos`
- ✅ Mantiene la lógica de desactivar cierres anuales anteriores
- ✅ Retorna formato estándar con `success`, `message`, `filasAfectadas`

**Antes:**
```php
public function insertarCierreAnual($fechaInicio, $fechaFin, $msjEsp, $msjIng)
```

**Después:**
```php
public function registraCierreAnual($campos)
```

---

### 2. `insertarPagos()` en `HistorialFacturas_Mdl.php`

**Ubicación:** `app/Models/Facturas/HistorialFacturas_Mdl.php` (línea 221)

**Problema:**
```php
public function insertarPagos($dataPagos)
```
- ⚠️ **CASO ESPECIAL JUSTIFICADO** - Inserción masiva batch desde fuente externa (HES)
- Hace múltiples INSERTs en un loop
- Similar a `actualizaProveedoresTemp()` y `actualizaProveedores()` - proceso de sincronización masiva
- **Recomendación:** Mantener como caso especial, pero considerar renombrar a `registrarPagos()` (infinitivo)

---

### 3. Nomenclatura inconsistente: `insertarCierreAnual` vs `registra*`

**Problema:**
- El modelo `BloqueoProveedores_Mdl.php` ya tiene métodos como `registraBloqueoProveedor($campos)`
- Pero también tiene `insertarCierreAnual()` que debería ser `registraCierreAnual()`

**Inconsistencia:**
- ✅ `registraBloqueoProveedor` (infinitivo)
- ❌ `insertarCierreAnual` (infinitivo pero diferente verbo)

**Recomendación:**
- Unificar a `registraCierreAnual($campos)` para mantener consistencia con el resto del modelo

---

## ⚠️ CASOS ESPECIALES (NO REQUIEREN REFACTORIZACIÓN)

### 1. `actualizaProveedoresTemp()` y `actualizaProveedores()` en `Proveedores_Mdl.php`
- **Razón:** Sincronización masiva desde fuente externa (SILME)
- **Tipo:** Proceso batch con múltiples registros
- **Recomendación:** Mantener como funciones especiales, considerar renombrar a infinitivo

### 2. `insertarPagos()` en `HistorialFacturas_Mdl.php`
- **Razón:** Inserción masiva desde fuente externa (HES)
- **Tipo:** Proceso batch con múltiples registros
- **Recomendación:** Mantener como función especial, considerar renombrar a `registrarPagos()`

---

## ✅ MODELOS QUE SOLO TIENEN SELECT (SIN PROBLEMAS)

Los siguientes modelos **NO tienen funciones de actualización/inserción** y por lo tanto no requieren revisión:

1. `Administradores_Mdl.php` - Solo SELECT
2. `Empresas_Mdl.php` - Solo SELECT
3. `NotificaProveedores_Mdl.php` - Solo SELECT
4. `CargarFacturas_Mdl.php` - Vacío (solo constructor)
5. `Nacionales_Mdl.php` - Solo SELECT (incompleto)
6. `Pagos_Mdl.php` - Solo SELECT
7. `RecepcionCFDIs_Mdl.php` - Solo SELECT
8. `CierrePortal_Mdl.php` - Solo SELECT
9. `OrdenCompra_Mdl.php` - Solo SELECT
10. `ComprobantesPago_Mdl.php` - Solo SELECT
11. `Anticipos_Mdl.php` - Solo SELECT
12. `HojaEntrada_Mdl.php` - Solo SELECT
13. `CFDIs_Mdl.php` - Solo SELECT
14. `ComprasAgrupadas_Mdl.php` - Solo SELECT
15. `ProveedoresCompras_Mdl.php` - Solo SELECT
16. `CatalogosCFDIs_Mdl.php` - Solo SELECT
17. `ConfiguracionGral_Mdl.php` - Solo SELECT
18. `Validador_Mdl.php` - Solo SELECT
19. `Menu_Mdl.php` - Solo SELECT
20. `LoginAdmin_Mdl.php` - Solo SELECT
21. `Login_Mdl.php` - Solo SELECT
22. `Idioma_Mdl.php` - Solo SELECT

---

## 📊 ESTADÍSTICAS

| Categoría | Cantidad |
|-----------|----------|
| Modelos totales analizados | 34 |
| Modelos con funciones UPDATE/INSERT | 12 |
| Funciones que siguen el estándar | 12 ✅ |
| Funciones que NO siguen el estándar | 0 ✅ |
| Casos especiales justificados | 3 |
| Modelos solo con SELECT | 22 |

---

## 🎯 PLAN DE ACCIÓN

### ✅ Prioridad ALTA - COMPLETADO:

1. ✅ **Refactorizar `insertarCierreAnual()` en `BloqueoProveedores_Mdl.php`:** - **COMPLETADO**
   - ✅ Cambiado a `registraCierreAnual($campos)`
   - ✅ Implementado whitelisting de campos
   - ✅ Eliminada lectura de `$_SESSION` dentro del modelo
   - ✅ Controlador actualizado (`BloqueoProveedoresController.php`)

### Prioridad BAJA:

2. **Renombrar funciones especiales a infinitivo:**
   - `actualizaProveedoresTemp()` → `actualizarProveedoresTemp()` (caso especial)
   - `actualizaProveedores()` → `actualizarProveedores()` (caso especial)
   - `insertarPagos()` → `registrarPagos()` (caso especial)

---

## 📝 NOTAS ADICIONALES

- La mayoría de los modelos (22 de 34) solo tienen funciones SELECT y no requieren cambios
- Solo se encontró **1 función** que realmente necesita refactorización según el estándar
- Los 3 casos especiales son procesos batch/importación masiva y están justificados como funciones especiales
- El proyecto tiene un buen nivel de consistencia después de las refactorizaciones realizadas

---

**Estado actual:**
1. ✅ `insertarCierreAnual()` refactorizado correctamente
2. ⚠️ Evaluar si renombrar funciones especiales (opcional, prioridad baja)
3. ⚠️ Considerar crear documentación sobre cuándo es apropiado usar funciones especiales vs. métodos genéricos

**Conclusión:**
- ✅ **Todas las funciones de actualización/inserción ahora siguen el estándar del proyecto**
- ✅ **No quedan funciones que requieran refactorización según el estándar**
- ⚠️ Solo quedan 3 casos especiales justificados (sincronización masiva batch)
