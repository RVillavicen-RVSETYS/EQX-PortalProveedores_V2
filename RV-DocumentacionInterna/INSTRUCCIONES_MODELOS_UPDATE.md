# INSTRUCCIONES PARA GENERAR MODELOS (ESTÁNDAR OFICIAL DEL PROYECTO)

## Contexto general

Estás trabajando en un proyecto PHP con MySQL.
Los Modelos siguen un estándar estricto y NO deben improvisar estructuras nuevas.

El objetivo es que cada tabla tenga UN solo método de actualización genérico, reutilizable, controlado y seguro.

---

## ❗ Reglas ABSOLUTAS del Modelo

### El modelo NUNCA debe:

- Leer `$_POST`, `$_GET`, `$_REQUEST`
- Imprimir `echo`, `json_encode`, `exit`
- Manejar sesiones visuales
- Decidir flujos de negocio
- Construir mensajes para UI

### El modelo SOLO:

- Valida datos técnicos
- Ejecuta SQL
- Regresa arrays estructurados (`success`, `message`, `filasAfectadas`)

---

## 🧠 Patrón obligatorio para UPDATE

Para cualquier tabla **NO se crean métodos específicos** como:

- `actualizarEstatus()`
- `actualizarSerie()`
- `actualizarRutaPDF()`
- `actualizarXML()`

En su lugar se usa **UN SOLO MÉTODO**:

```php
actualizarNombreTabla(array $campos, array $filtros)
```

### Ejemplo real:

```php
actualizarNotaCredito($campos, $filtros)
```

👉 **TODOS los cambios a esa tabla deben pasar por este método.**

---

## 🧩 Estructura obligatoria del método UPDATE

### 1️⃣ Whitelist de campos actualizables

El modelo define un arreglo `$camposValidos` donde solo se incluyen los campos que SÍ se pueden actualizar.

#### Ejemplo:

```php
$camposValidos = [
  'estatus' => [
    'tipoDato' => 'STRING',
    'sqlQuery' => 'estatus = :estatus',
    'permitidos' => ['0','1','2'],
    'mensajeError' => 'Estatus inválido. Permitidos: 0,1,2'
  ],
  'serie' => [
    'tipoDato' => 'STRING',
    'sqlQuery' => 'serie = :serie'
  ],
  'urlPDF' => [
    'tipoDato' => 'STRING',
    'sqlQuery' => 'urlPDF = :urlPDF'
  ]
];
```

📌 **Si un campo NO está en `$camposValidos`, NO se puede actualizar.**

### 2️⃣ Whitelist de filtros

El modelo define `$filtrosValidos` con los únicos filtros permitidos para el WHERE.

#### Ejemplo:

```php
$filtrosValidos = [
  'id' => ['tipoDato'=>'INT', 'sqlQuery'=>'id = :id'],
  'uuid' => ['tipoDato'=>'STRING', 'sqlQuery'=>'uuid = :uuid'],
  'idCompra' => ['tipoDato'=>'INT', 'sqlQuery'=>'idCompra = :idCompra']
];
```

📌 **Si llega un filtro no definido → ERROR.**

### 3️⃣ Validación de valores permitidos (ESTÁNDAR OFICIAL)

Cuando un campo tiene valores restringidos, **NO se usan funciones flecha ni validadores custom**.

Se usa **SIEMPRE** el estándar:

```php
'permitidos' => ['0','1','2']
```

Y se valida así:

```php
if (isset($camposValidos[$campo]['permitidos'])) {
  if (!in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
    throw new Exception(...);
  }
}
```

👉 **NO inventar validadores nuevos si ya existe este patrón.**

### 4️⃣ Campos que NO se actualizan

Campos que **NO deben ir** en `$camposValidos`:

- Primary Key (`id`)
- Campos de creación (`idUserReg`, `fechaReg`)
- Campos técnicos que solo se cargan por INSERT (ej. `selloCFDI`, `selloSAT`)
- Campos que el negocio define como inmutables

📌 **Si no debe actualizarse, simplemente no se agrega al whitelist.**

### 5️⃣ Manejo de NULL

Si un valor viene como `null`, se debe bindear con:

```php
PDO::PARAM_NULL
```

No se convierten nulls a strings vacíos.

### 6️⃣ Construcción del SQL (OBLIGATORIO)

El SET se arma con arrays (`implode`)

**NO usar** `ltrim`, concatenaciones manuales ni comas mágicas

#### Ejemplo:

```php
$sql = "UPDATE tabla SET " . implode(', ', $setParts) .
       " WHERE " . implode(' AND ', $whereParts);
```

### 7️⃣ Bind de parámetros

El tipo de dato se define así:

```php
'tipoDato' => 'INT' | 'STRING'
```

Y se bindea así:

```php
$stmt->bindValue(
  $param,
  $value,
  $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR
);
```

📌 **Si el parámetro no existe en los metadatos → ERROR.**

### 8️⃣ Retorno del modelo

El modelo **SIEMPRE** retorna un array:

```php
[
  'success' => true|false,
  'message' => 'Texto técnico',
  'filasAfectadas' => int
]
```

📌 **`rowCount()` puede devolver 0 aunque el UPDATE haya sido válido.**

---

## 🧪 Ejemplos de uso esperados

```php
// Cambiar estatus
actualizarNotaCredito(
  ['estatus'=>'2'],
  ['id'=>4]
);

// Actualizar rutas
actualizarNotaCredito(
  ['urlPDF'=>$pdf, 'urlXML'=>$xml],
  ['idCompra'=>17, 'idProveedor'=>'100074']
);
```

---

## 🎯 Objetivo final

- Un solo punto de mantenimiento
- Ningún UPDATE improvisado
- Seguridad por diseño
- Código consistente en todo el proyecto

---

**Última actualización:** 2025-01-XX
**Versión del estándar:** 1.0

