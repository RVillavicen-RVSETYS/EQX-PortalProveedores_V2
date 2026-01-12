# MODELO PLANTILLA BASE - RECOMENDACIÓN

**Fecha:** 2025-01-XX  
**Propósito:** Identificar el modelo mejor estructurado para usar como plantilla base

---

## 🏆 RECOMENDACIÓN: `Alertas_Mdl.php`

**Ubicación:** `app/Models/Proveedores/Excepciones/Alertas_Mdl.php`

### ¿Por qué este modelo?

#### ✅ **Ventajas:**

1. **Cumplimiento total del estándar:**
   - ✅ No lee `$_SESSION` dentro del modelo
   - ✅ No hace `echo`, `json_encode` ni `exit`
   - ✅ Usa whitelisting completo de campos y filtros
   - ✅ Retorna formato estándar (`success`, `message`, `filasAfectadas`)

2. **Estructura completa:**
   - ✅ Tiene métodos SELECT (obtenerListaAlertas, cargaDatos)
   - ✅ Tiene método INSERT (registraNotificaProveedor)
   - ✅ Tiene método UPDATE (actualizarNotificaProveedor)
   - ✅ Comentarios claros por secciones (`/* CONSULTAS DE SELECT */`, etc.)

3. **Implementación de características avanzadas:**
   - ✅ Maneja valores RAW (NOW() para fechaReg) correctamente
   - ✅ Validación de valores permitidos con mensajes de error personalizados
   - ✅ Manejo de NULL values
   - ✅ Construcción SQL limpia con `implode()`

4. **Código limpio y mantenible:**
   - ✅ Comentarios descriptivos
   - ✅ Nombres de variables claros
   - ✅ Manejo de errores consistente
   - ✅ Logging apropiado

---

## 📊 COMPARACIÓN DE MODELOS

### 1. `Alertas_Mdl.php` ⭐ **RECOMENDADO**

| Característica | Estado |
|---------------|--------|
| Cumple estándar 100% | ✅ Sí |
| Tiene SELECT, INSERT, UPDATE | ✅ Sí |
| Maneja valores RAW (NOW()) | ✅ Sí |
| Validación completa | ✅ Sí |
| Sin violaciones | ✅ Sí |
| Código limpio | ✅ Sí |

**Puntuación:** 10/10

---

### 2. `IgnoraDescuento_Mdl.php`

| Característica | Estado |
|---------------|--------|
| Cumple estándar 100% | ⚠️ Casi (usa $_SESSION en log línea 358) |
| Tiene SELECT, INSERT, UPDATE | ✅ Sí |
| Maneja valores RAW (NOW()) | ✅ Sí |
| Validación completa | ✅ Sí |
| Propiedades útiles ($tabla, $logFile) | ✅ Sí |
| Sin violaciones | ❌ Usa $_SESSION en log |

**Puntuación:** 8/10

**Problema menor:** Usa `$_SESSION['EQXident']` directamente en el log dentro del método `actualizarIgnoraDescuento()`. Debería recibir `idUser` como parte de `$campos` o `$filtros`.

---

### 3. `BloqueoProveedores_Mdl.php`

| Característica | Estado |
|---------------|--------|
| Cumple estándar 100% | ✅ Sí (después de refactorización) |
| Tiene SELECT, INSERT, UPDATE | ✅ Sí |
| Maneja valores RAW (NOW()) | ✅ Sí |
| Validación completa | ✅ Sí |
| Métodos especiales (cierre anual) | ✅ Sí |
| Sin violaciones | ✅ Sí |

**Puntuación:** 9/10

**Nota:** Tiene métodos adicionales para `conf_cierreAnio` que son casos especiales justificados.

---

### 4. `NotasCredito_Mdl.php`

| Característica | Estado |
|---------------|--------|
| Cumple estándar 100% | ✅ Sí |
| Tiene SELECT, INSERT, UPDATE | ✅ Sí |
| Validación completa | ✅ Sí |
| Maneja múltiples filtros | ✅ Sí |
| Usa BD_ConnectHES para SELECT | ✅ Sí (caso especial justificado) |

**Puntuación:** 9/10

**Nota:** Este modelo es más complejo porque maneja múltiples conexiones (BD_Connect y BD_ConnectHES). Es excelente pero puede ser confuso para principiantes.

---

### 5. `Compras_Mdl.php`

| Característica | Estado |
|---------------|--------|
| Cumple estándar 100% | ❌ Usa $_SESSION directamente (línea 612) |
| Construcción SQL | ⚠️ Inconsistente (usa ltrim() y comas iniciales) |
| Validación completa | ✅ Sí |

**Puntuación:** 6/10

**Problemas:**
- Usa `$_SESSION['EQXident']` directamente en el método `actualizarDataCompras()`
- Construcción SQL con comas iniciales en `sqlQuery` (`, estatus = :estatus`) requiere `ltrim()`
- No sigue el patrón estándar completamente

---

## 📝 ESTRUCTURA RECOMENDADA (basada en Alertas_Mdl.php)

```php
<?php

namespace App\Models\TuNamespace;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class TuModelo_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase TuModelo_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    /* CONSULTAS DE SELECT */
    public function obtenerLista()
    {
        // Implementación SELECT...
    }

    /* CONSULTAS DE INSERT */
    public function registraNombreTabla($campos)
    {
        $camposValidos = [
            'campo1' => ['tipoDato' => 'STRING', 'sqlQuery' => 'campo1 = :campo1'],
            // ...
        ];

        // Validación, construcción SQL, bind parameters, etc.
    }

    /* CONSULTAS DE UPDATE */
    public function actualizarNombreTabla($campos, $filtros)
    {
        $camposValidos = [
            'campo1' => ['tipoDato' => 'STRING', 'sqlQuery' => 'campo1 = :campo1'],
            // ...
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id'],
            // ...
        ];

        // Validación, construcción SQL, bind parameters, etc.
    }
}
```

---

## 🎯 RECOMENDACIÓN FINAL

**Usar `Alertas_Mdl.php` como plantilla base porque:**

1. ✅ **Es el más completo y limpio** - Tiene todos los elementos necesarios
2. ✅ **Cumple 100% el estándar** - No tiene violaciones
3. ✅ **Fácil de entender** - Código bien comentado y estructurado
4. ✅ **Aplicable a la mayoría de casos** - Cubre SELECT, INSERT, UPDATE
5. ✅ **Maneja casos especiales** - Valores RAW (NOW()), validaciones, NULL

**Alternativa (si necesitas propiedades de tabla):**
- Si necesitas propiedades como `$tabla` y `$logFile`, puedes usar `IgnoraDescuento_Mdl.php` como base, pero **debes corregir** el uso de `$_SESSION` en el log.

---

## 📋 CHECKLIST PARA NUEVOS MODELOS

Basado en `Alertas_Mdl.php`, asegúrate de incluir:

- [ ] Namespace correcto
- [ ] Includes con `INCLUDE_CHECK`
- [ ] Constructor con debug opcional
- [ ] Sección `/* CONSULTAS DE SELECT */`
- [ ] Sección `/* CONSULTAS DE INSERT */` con `registraNombreTabla($campos)`
- [ ] Sección `/* CONSULTAS DE UPDATE */` con `actualizarNombreTabla($campos, $filtros)`
- [ ] Whitelisting completo de campos y filtros
- [ ] Validación de tipos de datos
- [ ] Validación de valores permitidos (si aplica)
- [ ] Manejo de valores RAW (NOW(), etc.)
- [ ] Manejo de NULL values
- [ ] Construcción SQL con `implode()`
- [ ] Bind parameters con tipos correctos
- [ ] Retorno estándar (`success`, `message`, `filasAfectadas`)
- [ ] Logging de errores
- [ ] Sin `$_SESSION`, `echo`, `json_encode`, `exit` en el modelo

---

**Conclusión:** `Alertas_Mdl.php` es el modelo de referencia perfecto para crear nuevos modelos siguiendo el estándar del proyecto.
