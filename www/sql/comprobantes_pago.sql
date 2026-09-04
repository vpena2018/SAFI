-- ============================================================================
-- Tabla: ventas_comprobantes_pago
-- ----------------------------------------------------------------------------
-- Guarda los datos leidos (por IA) o digitados a mano de cada comprobante de
-- pago (banco / financiera / cooperativa) que se sube en Ventas de Vehiculos,
-- para poder detectar si el mismo comprobante ya fue registrado antes
-- (mismo banco + fecha + referencia + monto) y evitar que se cargue 2 veces.
--
-- Ejecutar este script UNA VEZ contra la base de datos "inglosa" antes de usar
-- la funcionalidad de lectura de comprobantes con IA.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `ventas_comprobantes_pago` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_venta`          INT NOT NULL COMMENT 'Venta a la que pertenece el comprobante (ventas.id)',
  `archivo`           VARCHAR(255) NOT NULL COMMENT 'Nombre del archivo dentro de uploa_d_ventas/',

  -- Datos extraidos del comprobante (por IA o digitados por el usuario)
  `banco`             VARCHAR(150) DEFAULT NULL COMMENT 'Banco / financiera / cooperativa que emitio el comprobante',
  `fecha_comprobante` DATE DEFAULT NULL COMMENT 'Fecha impresa en el comprobante',
  `referencia`        VARCHAR(100) DEFAULT NULL COMMENT 'Numero de referencia / transaccion / autorizacion',
  `monto`             DECIMAL(12,2) DEFAULT NULL COMMENT 'Monto del comprobante',

  -- Trazabilidad de como se obtuvieron los datos
  `origen_datos`      ENUM('ia','manual') NOT NULL DEFAULT 'manual' COMMENT 'Si los datos los leyo la IA o los escribio el usuario',
  `ia_respuesta_raw`  TEXT DEFAULT NULL COMMENT 'Respuesta cruda de la IA (auditoria / depuracion)',

  `id_usuario`        INT DEFAULT NULL COMMENT 'Usuario que registro el comprobante',
  `fecha_registro`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- Baja logica: si se elimina un comprobante mal cargado no se borra el
  -- registro (queda como historial), simplemente deja de contar para la
  -- validacion de duplicados.
  `activo`            TINYINT(1) NOT NULL DEFAULT 1,

  PRIMARY KEY (`id`),
  KEY `idx_id_venta` (`id_venta`),
  -- Indice de apoyo para la busqueda de duplicados (banco+fecha+referencia+monto)
  KEY `idx_comprobante_duplicado` (`banco`, `fecha_comprobante`, `referencia`, `monto`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Nota: no se agrego un UNIQUE KEY sobre (banco, fecha_comprobante, referencia,
-- monto) porque en MySQL una columna en NULL nunca choca con otro NULL en un
-- UNIQUE index, y estos 4 campos pueden quedar vacios cuando la IA no logra
-- leer el comprobante completo. Por eso la validacion de duplicados se hace
-- en la aplicacion (ver include/ia_comprobantes.php -> buscar_comprobante_pago_duplicado),
-- exigiendo los 4 datos completos antes de guardar.
