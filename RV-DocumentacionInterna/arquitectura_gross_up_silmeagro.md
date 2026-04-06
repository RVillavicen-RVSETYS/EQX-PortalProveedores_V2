# Arquitectura Financiera y Comercial: Cargas Múltiples y Gross-Up (SilmeAgro)

## 1. El Paradigma de Silmeagro (Gross-Up)
En compras estándar, un producto de precio de lista se le "resta" un descuento para calcular el monto a pagar ($180 - 10% = $162). 
Sin embargo, **Silmeagro registra el precio final Neto pagado (162) y el sistema debe inflarlo "Hacia Arriba" (Gross-Up)** para recuperar matemáticamente cuál era el valor Bruto o Real original del inventario (180).

*   **Fórmula del Sistema:** `Precio Capturado (Neto) / Factor Inverso (1 - (Porcentaje/100))`
*   *Ejemplo Práctico:* `162.00 / (1 - 0.10) = 180.00`

---

## 2. Motor de Políticas Comerciales (Exclusiones Condicionadas)
El motor de reglas de compras genera diferentes incentivos comerciales, pero no todos deben inflar de regreso el Inventario:

1.  **Tipo `DESCINC` (Descuento Incluido):** La Factura ya trae este porcentaje bajado. El valor neto anotado sirve para control histórico, pero **NO sufre Gross-Up** en las Bases de Datos ya que alteraría absurdamente la tarifa sobre la que se rige la factura externa.
2.  **Tipo `NC` (Nota de Crédito Automática):** Al ser un descuento de "Pronto Pago", la factura viene con el cobro total e inflado original. En la BD este tipo de política **SÍ fuerza el Gross-Up** para coincidir con la Factura.
3.  **Excepciones de Producto:** El motor salta inteligentemente productos, muebles o equipos marcados como inviables en los catálogos del sistema, por lo tanto esos específicos se mantienen intactos o "Exemptos".

---

## 3. Topología de Base de Datos y Comportamiento Matemático
### Tabla `detcompras` (Productos Cotizados)
Los campos `costoReal` y `subTotalReal` son fotográficos e inmutables desde que la compra se marca Guardada.
*   **`costoUnitario`** -> Neto Cotizado
*   **`costoReal`** -> Bruto Gross-Up 

### Tabla Cabecera `recepciones` (HES Pura)
Se le implementó un cálculo interno dinámico capaz de procesar **Recepciones Parciales Multi-Viaje**. Cada recepción (hoja independiente):
*   **`montoHes`** -> Lee y multiplica las cantidades parciales ingresadas vs su respectivo `costoUnitario` (Ideal para Finanzas y Pagos Netos).
*   **`adeudo`** -> La deuda formal del sistema que baja con cada compensación bancaria.
*   **`montoHesReal`** -> Lee y multiplica las cantidades parciales recibidas con el `costoReal` (Ideal para cuadrar valor de inventario físico vs. Factura externa íntegra y mostrar en portales de Proveedores).

---

## 4. Vista para el Portal de Proveedores Externo
El sistema expone la capa `vw_ext_PortalProveedores_MontosHES` con la peculiaridad de cruzar cantidades usando multiplicaciones en vivo (On the fly) `dr.cantidad * dc.costoReal` y homologando los `idProveedor` (sumando +100000) de los ERPs secundarios de pago sin descuidar la fidelidad del cálculo gross.
