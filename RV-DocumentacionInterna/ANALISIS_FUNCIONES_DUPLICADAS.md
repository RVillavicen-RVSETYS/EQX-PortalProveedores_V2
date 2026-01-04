# ANÁLISIS DE FUNCIONES DUPLICADAS E INCONSISTENTES EN MODELOS

**Fecha de análisis:** 2025-01-XX
**Total de modelos analizados:** 29

---

## 🔴 PROBLEMAS CRÍTICOS ENCONTRADOS

### 1. FUNCIONES DE CAMBIO DE ESTATUS DUPLICADAS

Se encontraron **4 funciones similares** con nombres diferentes que hacen lo mismo:

| Modelo | Función | Parámetros | Tabla que actualiza |
|--------|---------|------------|---------------------|
| `Alertas_Mdl.php` | `cambiaEstatus()` | `($idNotificacion, $nuevoEstatus)` | `conf_notificaProveedor` |
| `DescuentoProveedores_Mdl.php` | `cambiaEstatus()` | `($idDescuento, $nuevoEstatus)` | `conf_provFactDescuento` |
| `BloqueoProveedores_Mdl.php` | `cambiaEstatus()` | `($idProveedor)` | `conf_provFactSiempre` |
| `ExcepcionesProveedores_Mdl.php` | `cambiarEstatus()` | `($tabla, $identificador, $nuevoEstatus, $idProveedor)` | Múltiples tablas (switch) |

**Problema:** 
- Tres usan `cambiaEstatus` y una usa `cambiarEstatus`
- Todas hacen UPDATE de estatus pero con implementaciones diferentes
- Ninguna sigue el patrón estándar `actualizarNombreTabla($campos, $filtros)`

---

### 2. FUNCIONES DE ACTUALIZACIÓN INCONSISTENTES

#### ✅ Funciones que SÍ siguen el estándar:
- `Compras_Mdl.php` → `actualizarDataCompras($campos, $filtros)` ✅
- `NotasCredito_Mdl.php` → `actualizarNotaCredito($campos, $filtros)` ✅
- `Proveedores_Mdl.php` → `actualizarDatosProveedor($campos, $filtros)` ✅

#### ❌ Funciones que NO siguen el estándar (métodos específicos):

**En `Proveedores_Mdl.php`:**
- `actualizaRFC($idProveedor, $nuevoRFC)` ❌
- `actualizaCorreo($idProveedor, $nuevoCorreo)` ❌
- `actualizaPassword($idProveedor, $nuevaPass)` ❌
- `actualizaProveedoresTemp()` ❌
- `actualizaProveedores()` ❌

**En `BloqueoProveedores_Mdl.php`:**
- `actualizarBloqueo($idBloqueo, $idProveedor, $bloque, $estatusFact)` ❌

**Problema:** 
- Estas funciones deberían usar el método genérico `actualizarDatosProveedor()` o `actualizarBloqueoProveedor()`
- Violan el principio de "un solo método de actualización por tabla"

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
4. `actualizarBloqueo` - BloqueoProveedores_Mdl ❌ (no sigue patrón)
5. `actualizaRFC` - Proveedores_Mdl ❌
6. `actualizaCorreo` - Proveedores_Mdl ❌
7. `actualizaPassword` - Proveedores_Mdl ❌
8. `actualizaProveedoresTemp` - Proveedores_Mdl ❌
9. `actualizaProveedores` - Proveedores_Mdl ❌

### Funciones de Cambio de Estatus:
1. `cambiaEstatus` - Alertas_Mdl ❌
2. `cambiaEstatus` - DescuentoProveedores_Mdl ❌
3. `cambiaEstatus` - BloqueoProveedores_Mdl ❌
4. `cambiarEstatus` - ExcepcionesProveedores_Mdl ❌

---

## 🎯 RECOMENDACIONES

### Prioridad ALTA:
1. **Unificar funciones de cambio de estatus:**
   - Crear métodos genéricos `actualizarNombreTabla($campos, $filtros)` para cada tabla
   - Eliminar los métodos específicos `cambiaEstatus()` y `cambiarEstatus()`
   - Usar el método genérico con `['estatus' => $nuevoEstatus]` como campo

2. **Refactorizar `Proveedores_Mdl.php`:**
   - Eliminar `actualizaRFC()`, `actualizaCorreo()`, `actualizaPassword()`
   - Usar `actualizarDatosProveedor()` con los campos correspondientes

3. **Estandarizar nomenclatura:**
   - Cambiar todos los métodos a infinitivo (`actualizar`, `cambiar`)
   - Mantener consistencia en todo el proyecto

### Prioridad MEDIA:
4. **Revisar `actualizarBloqueo()` en BloqueoProveedores_Mdl:**
   - Evaluar si puede convertirse en método genérico `actualizarBloqueoProveedor($campos, $filtros)`

5. **Revisar `actualizaProveedoresTemp()` y `actualizaProveedores()`:**
   - Evaluar si son casos especiales que justifican métodos específicos o pueden unificarse

---

## 📝 NOTAS ADICIONALES

- El estándar oficial está documentado en `INSTRUCCIONES_MODELOS_UPDATE.md`
- Solo 3 de 9 funciones de actualización siguen el estándar correctamente
- Todas las funciones de cambio de estatus deberían migrarse al patrón genérico

---

**Próximos pasos sugeridos:**
1. Revisar este análisis con el equipo
2. Decidir qué funciones refactorizar primero
3. Crear un plan de migración gradual
4. Actualizar la documentación después de cada refactorización

