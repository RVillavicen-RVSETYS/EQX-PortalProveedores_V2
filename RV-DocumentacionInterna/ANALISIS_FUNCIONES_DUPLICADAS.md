# ANÁLISIS DE FUNCIONES DUPLICADAS E INCONSISTENTES EN MODELOS

**Fecha de análisis:** 2025-01-XX  
**Última actualización:** 2025-01-XX  
**Total de modelos analizados:** 29

---

## ✅ PROGRESO DE REFACTORIZACIÓN

### ✅ COMPLETADO:

1. **Funciones de cambio de estatus refactorizadas:**
   - ✅ `Alertas_Mdl.php` → `actualizarNotificaProveedor($campos, $filtros)`
   - ✅ `DescuentoProveedores_Mdl.php` → `actualizarDescuentoProveedor($campos, $filtros)`
   - ✅ `BloqueoProveedores_Mdl.php` → `actualizarBloqueoProveedor($campos, $filtros)`
   - ✅ `ExcepcionesProveedores_Mdl.php` → Dividido en 5 modelos con métodos genéricos:
     - `IgnoraDescuento_Mdl.php` → `actualizarIgnoraDescuento($campos, $filtros)`
     - `ExentoAnoFisc_Mdl.php` → `actualizarExentoAnoFisc($campos, $filtros)`
     - `ExentoFechaEmision_Mdl.php` → `actualizarExentoFechaEmision($campos, $filtros)`
     - `UsoCfdiDistinto_Mdl.php` → `actualizarUsoCfdiDistinto($campos, $filtros)`
     - `BloqDiferencias_Mdl.php` → `actualizarBloqDiferencias($campos, $filtros)`

2. **Funciones de actualización refactorizadas en Proveedores_Mdl:**
   - ✅ `actualizaRFC()` → Eliminada, ahora usa `actualizarDatosProveedor()`
   - ✅ `actualizaCorreo()` → Eliminada, ahora usa `actualizarDatosProveedor()`
   - ✅ `actualizaPassword()` → Eliminada, ahora usa `actualizarDatosProveedor()`

3. **Modelos reorganizados:**
   - ✅ Todos los modelos de excepciones movidos a `app/Models/Proveedores/Excepciones/`
   - ✅ Modelos antiguos eliminados de `app/Models/Configuraciones/`

---

## 🔴 PROBLEMAS CRÍTICOS ENCONTRADOS (HISTÓRICO)

### ~~1. FUNCIONES DE CAMBIO DE ESTATUS DUPLICADAS~~ ✅ RESUELTO

~~Se encontraron **4 funciones similares** con nombres diferentes que hacen lo mismo:~~

**Estado:** ✅ **TODAS REFACTORIZADAS** - Ahora todas usan el patrón genérico `actualizarNombreTabla($campos, $filtros)`

---

### 2. FUNCIONES DE ACTUALIZACIÓN INCONSISTENTES

#### ✅ Funciones que SÍ siguen el estándar:
- `Compras_Mdl.php` → `actualizarDataCompras($campos, $filtros)` ✅
- `NotasCredito_Mdl.php` → `actualizarNotaCredito($campos, $filtros)` ✅
- `Proveedores_Mdl.php` → `actualizarDatosProveedor($campos, $filtros)` ✅

#### ⚠️ Funciones que requieren evaluación especial:

**En `Proveedores_Mdl.php`:**
- ✅ ~~`actualizaRFC($idProveedor, $nuevoRFC)`~~ → **ELIMINADA** (ahora usa `actualizarDatosProveedor()`)
- ✅ ~~`actualizaCorreo($idProveedor, $nuevoCorreo)`~~ → **ELIMINADA** (ahora usa `actualizarDatosProveedor()`)
- ✅ ~~`actualizaPassword($idProveedor, $nuevaPass)`~~ → **ELIMINADA** (ahora usa `actualizarDatosProveedor()`)
- ⚠️ `actualizaProveedoresTemp()` → **CASO ESPECIAL** (sincronización masiva desde fuente externa)
- ⚠️ `actualizaProveedores()` → **CASO ESPECIAL** (sincronización masiva desde tabla temporal)

**En `BloqueoProveedores_Mdl.php`:**
- ✅ ~~`actualizarBloqueo($idBloqueo, $idProveedor, $bloque, $estatusFact)`~~ → **REFACTORIZADA** a `actualizarBloqueoProveedor($campos, $filtros)`

**Nota sobre funciones especiales:**
- `actualizaProveedoresTemp()` y `actualizaProveedores()` son procesos batch de sincronización masiva
- Hacen INSERT...ON DUPLICATE KEY UPDATE masivos desde fuentes externas
- **NO son actualizaciones individuales**, por lo que probablemente NO deben refactorizarse al patrón genérico
- Se recomienda mantenerlas como funciones especiales, pero considerar renombrarlas a infinitivo: `actualizarProveedoresTemp()` y `actualizarProveedores()`

---

### 3. INCONSISTENCIAS EN NOMENCLATURA

#### Uso de "actualizar" vs "actualiza":
- ✅ `actualizarDataCompras` (infinitivo)
- ✅ `actualizarNotaCredito` (infinitivo)
- ✅ `actualizarDatosProveedor` (infinitivo)
- ❌ `actualizaRFC` (tercera persona)
- ❌ `actualizaCorreo` (tercera persona)
- ❌ `actualizaPassword` (tercera persona)
- ❌ `actualizaProveedoresTemp` (tercera persona)
- ❌ `actualizaProveedores` (tercera persona)

#### Uso de "cambiar" vs "cambia":
- ✅ `cambiarEstatus` (infinitivo) - en ExcepcionesProveedores_Mdl
- ❌ `cambiaEstatus` (tercera persona) - en Alertas_Mdl, DescuentoProveedores_Mdl, BloqueoProveedores_Mdl

**Recomendación:** Usar siempre infinitivo (`actualizar`, `cambiar`) para mantener consistencia.

---

## 📊 RESUMEN DE FUNCIONES POR CATEGORÍA

### Funciones de Actualización (UPDATE):
1. `actualizarDataCompras` - Compras_Mdl ✅
2. `actualizarNotaCredito` - NotasCredito_Mdl ✅
3. `actualizarDatosProveedor` - Proveedores_Mdl ✅
4. `actualizarBloqueoProveedor` - BloqueoProveedores_Mdl ✅
5. `actualizarNotificaProveedor` - Alertas_Mdl ✅
6. `actualizarDescuentoProveedor` - DescuentoProveedores_Mdl ✅
7. `actualizarIgnoraDescuento` - IgnoraDescuento_Mdl ✅
8. `actualizarExentoAnoFisc` - ExentoAnoFisc_Mdl ✅
9. `actualizarExentoFechaEmision` - ExentoFechaEmision_Mdl ✅
10. `actualizarUsoCfdiDistinto` - UsoCfdiDistinto_Mdl ✅
11. `actualizarBloqDiferencias` - BloqDiferencias_Mdl ✅
12. ⚠️ `actualizaProveedoresTemp` - Proveedores_Mdl (CASO ESPECIAL - sincronización masiva)
13. ⚠️ `actualizaProveedores` - Proveedores_Mdl (CASO ESPECIAL - sincronización masiva)

### ~~Funciones de Cambio de Estatus:~~ ✅ TODAS REFACTORIZADAS
~~Todas las funciones de cambio de estatus fueron eliminadas y reemplazadas por métodos genéricos `actualizar*()`~~

---

## 🎯 RECOMENDACIONES

### ✅ COMPLETADO:
1. ✅ **Unificar funciones de cambio de estatus** → Todas refactorizadas a métodos genéricos
2. ✅ **Refactorizar `Proveedores_Mdl.php`** → `actualizaRFC()`, `actualizaCorreo()`, `actualizaPassword()` eliminadas
3. ✅ **Refactorizar modelos de excepciones** → Todos reorganizados y estandarizados

### ⚠️ PENDIENTE (Prioridad BAJA):
1. **Estandarizar nomenclatura en funciones especiales:**
   - Considerar renombrar `actualizaProveedoresTemp()` → `actualizarProveedoresTemp()`
   - Considerar renombrar `actualizaProveedores()` → `actualizarProveedores()`
   - **Nota:** Estas funciones son casos especiales de sincronización masiva y probablemente NO deben refactorizarse al patrón genérico

2. **Revisar otros modelos:**
   - Buscar otros modelos que puedan tener funciones duplicadas o inconsistentes
   - Verificar que todos los nuevos modelos sigan el estándar documentado

---

## 📝 NOTAS ADICIONALES

- El estándar oficial está documentado en `INSTRUCCIONES_MODELOS_UPDATE.md`
- ✅ **11 de 13 funciones de actualización** ahora siguen el estándar correctamente
- ✅ **Todas las funciones de cambio de estatus** fueron migradas al patrón genérico
- ⚠️ Las 2 funciones restantes (`actualizaProveedoresTemp` y `actualizaProveedores`) son casos especiales de sincronización masiva

---

## 📊 ESTADÍSTICAS DE REFACTORIZACIÓN

- **Modelos refactorizados:** 8
- **Funciones eliminadas:** 7 (`cambiaEstatus` x3, `cambiarEstatus` x1, `actualizaRFC`, `actualizaCorreo`, `actualizaPassword`)
- **Funciones creadas:** 11 (métodos genéricos `actualizar*()`)
- **Modelos reorganizados:** 4 (movidos a `app/Models/Proveedores/Excepciones/`)
- **Modelos eliminados:** 4 (de `app/Models/Configuraciones/`)

---

**Próximos pasos sugeridos:**
1. ✅ Revisar funciones especiales (`actualizaProveedoresTemp` y `actualizaProveedores`) - **PENDIENTE**
2. ✅ Buscar otros modelos con funciones duplicadas - **PENDIENTE**
3. ✅ Verificar que todos los controladores usen el nuevo patrón - **PENDIENTE**
4. ✅ Considerar renombrar funciones especiales a infinitivo - **PENDIENTE**

