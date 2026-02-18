-- Tabla: conf_provPermitirPueSiempre
-- Excepción "Permitir PUE siempre": permite aceptar facturas PUE aunque los días de crédito
-- ya no caigan en el mes corriente. Un permiso temporal por proveedor.
-- Ejecutar en la base de datos del portal.

CREATE TABLE IF NOT EXISTS conf_provPermitirPueSiempre (
  id INT NOT NULL AUTO_INCREMENT COMMENT 'PK',
  idProveedor INT NOT NULL COMMENT 'FK a proveedores.id',
  fechaExpiracion DATE NOT NULL COMMENT 'Fecha límite hasta la que aplica el permiso',
  motivo VARCHAR(500) NOT NULL COMMENT 'Motivo por el que se otorga el permiso',
  estatus TINYINT NOT NULL DEFAULT 1 COMMENT 'Estatus del permiso. Valores: 0=Cancelado/Inactivo, 1=Activo',
  idUserReg INT NULL COMMENT 'Usuario que registró el permiso (auditoría)',
  fechaReg DATETIME NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de registro (auditoría)',
  idUserCancela INT NULL COMMENT 'Usuario que canceló el permiso, si aplica (auditoría)',
  fechaCancela DATETIME NULL COMMENT 'Fecha y hora en que se canceló el permiso, si aplica (auditoría)',
  motivoCancela VARCHAR(255) NULL COMMENT 'Motivo por el que se deshabilitó/canceló el permiso',
  PRIMARY KEY (id),
  INDEX idx_prov_estatus (idProveedor, estatus),
  INDEX idx_fecha_expiracion (fechaExpiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
