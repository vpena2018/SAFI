CREATE TABLE IF NOT EXISTS orden_traslado_historial_estado (
    id INT NOT NULL AUTO_INCREMENT,
    id_maestro INT NOT NULL,
    id_estado INT NULL,
    id_usuario INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT NULL,
    PRIMARY KEY (id),
    KEY idx_oth_id_maestro (id_maestro),
    KEY idx_oth_id_estado (id_estado),
    KEY idx_oth_id_usuario (id_usuario),
    KEY idx_oth_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
