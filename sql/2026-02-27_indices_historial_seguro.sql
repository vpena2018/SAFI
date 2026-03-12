-- Indices recomendados para tablas de historial (idempotente)
-- Fecha: 2026-02-27
-- Motor esperado: MySQL/MariaDB

SET @db := DATABASE();

-- ==============================
-- servicio_historial_estado
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='servicio_historial_estado' AND index_name='idx_serv_hist_servicio_id'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE servicio_historial_estado ADD INDEX idx_serv_hist_servicio_id (id_servicio, id)',
    'SELECT "idx_serv_hist_servicio_id already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='servicio_historial_estado' AND index_name='idx_serv_hist_servicio_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE servicio_historial_estado ADD INDEX idx_serv_hist_servicio_fecha (id_servicio, fecha)',
    'SELECT "idx_serv_hist_servicio_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='servicio_historial_estado' AND index_name='idx_serv_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE servicio_historial_estado ADD INDEX idx_serv_hist_estado (id_estado)',
    'SELECT "idx_serv_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='servicio_historial_estado' AND index_name='idx_serv_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE servicio_historial_estado ADD INDEX idx_serv_hist_usuario (id_usuario)',
    'SELECT "idx_serv_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================
-- averia_historial_estado
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='averia_historial_estado' AND index_name='idx_averia_hist_maestro_id'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE averia_historial_estado ADD INDEX idx_averia_hist_maestro_id (id_maestro, id)',
    'SELECT "idx_averia_hist_maestro_id already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='averia_historial_estado' AND index_name='idx_averia_hist_maestro_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE averia_historial_estado ADD INDEX idx_averia_hist_maestro_fecha (id_maestro, fecha)',
    'SELECT "idx_averia_hist_maestro_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='averia_historial_estado' AND index_name='idx_averia_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE averia_historial_estado ADD INDEX idx_averia_hist_estado (id_estado)',
    'SELECT "idx_averia_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='averia_historial_estado' AND index_name='idx_averia_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE averia_historial_estado ADD INDEX idx_averia_hist_usuario (id_usuario)',
    'SELECT "idx_averia_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================
-- inspeccion_historial_estado
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='inspeccion_historial_estado' AND index_name='idx_ins_hist_maestro_id'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE inspeccion_historial_estado ADD INDEX idx_ins_hist_maestro_id (id_maestro, id)',
    'SELECT "idx_ins_hist_maestro_id already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='inspeccion_historial_estado' AND index_name='idx_ins_hist_maestro_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE inspeccion_historial_estado ADD INDEX idx_ins_hist_maestro_fecha (id_maestro, fecha)',
    'SELECT "idx_ins_hist_maestro_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='inspeccion_historial_estado' AND index_name='idx_ins_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE inspeccion_historial_estado ADD INDEX idx_ins_hist_estado (id_estado)',
    'SELECT "idx_ins_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='inspeccion_historial_estado' AND index_name='idx_ins_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE inspeccion_historial_estado ADD INDEX idx_ins_hist_usuario (id_usuario)',
    'SELECT "idx_ins_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='inspeccion_historial_estado' AND index_name='idx_ins_hist_nombre_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE inspeccion_historial_estado ADD INDEX idx_ins_hist_nombre_fecha (nombre, fecha)',
    'SELECT "idx_ins_hist_nombre_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================
-- cotizacion_historial_estado
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='cotizacion_historial_estado' AND index_name='idx_cot_hist_maestro_id'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE cotizacion_historial_estado ADD INDEX idx_cot_hist_maestro_id (id_maestro, id)',
    'SELECT "idx_cot_hist_maestro_id already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='cotizacion_historial_estado' AND index_name='idx_cot_hist_maestro_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE cotizacion_historial_estado ADD INDEX idx_cot_hist_maestro_fecha (id_maestro, fecha)',
    'SELECT "idx_cot_hist_maestro_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='cotizacion_historial_estado' AND index_name='idx_cot_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE cotizacion_historial_estado ADD INDEX idx_cot_hist_estado (id_estado)',
    'SELECT "idx_cot_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='cotizacion_historial_estado' AND index_name='idx_cot_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE cotizacion_historial_estado ADD INDEX idx_cot_hist_usuario (id_usuario)',
    'SELECT "idx_cot_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================
-- ventas_historial_estado
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='ventas_historial_estado' AND index_name='idx_ventas_hist_maestro_id'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE ventas_historial_estado ADD INDEX idx_ventas_hist_maestro_id (id_maestro, id)',
    'SELECT "idx_ventas_hist_maestro_id already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='ventas_historial_estado' AND index_name='idx_ventas_hist_maestro_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE ventas_historial_estado ADD INDEX idx_ventas_hist_maestro_fecha (id_maestro, fecha)',
    'SELECT "idx_ventas_hist_maestro_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='ventas_historial_estado' AND index_name='idx_ventas_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE ventas_historial_estado ADD INDEX idx_ventas_hist_estado (id_estado)',
    'SELECT "idx_ventas_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='ventas_historial_estado' AND index_name='idx_ventas_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE ventas_historial_estado ADD INDEX idx_ventas_hist_usuario (id_usuario)',
    'SELECT "idx_ventas_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='ventas_historial_estado' AND index_name='idx_ventas_hist_nombre_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE ventas_historial_estado ADD INDEX idx_ventas_hist_nombre_fecha (nombre, fecha)',
    'SELECT "idx_ventas_hist_nombre_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================
-- orden_traslado_historial_estado
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='orden_traslado_historial_estado' AND index_name='idx_tras_hist_maestro_id'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE orden_traslado_historial_estado ADD INDEX idx_tras_hist_maestro_id (id_maestro, id)',
    'SELECT "idx_tras_hist_maestro_id already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='orden_traslado_historial_estado' AND index_name='idx_tras_hist_maestro_fecha'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE orden_traslado_historial_estado ADD INDEX idx_tras_hist_maestro_fecha (id_maestro, fecha)',
    'SELECT "idx_tras_hist_maestro_fecha already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='orden_traslado_historial_estado' AND index_name='idx_tras_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE orden_traslado_historial_estado ADD INDEX idx_tras_hist_estado (id_estado)',
    'SELECT "idx_tras_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='orden_traslado_historial_estado' AND index_name='idx_tras_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE orden_traslado_historial_estado ADD INDEX idx_tras_hist_usuario (id_usuario)',
    'SELECT "idx_tras_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================
-- mapeo_historial
-- ==============================
SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='mapeo_historial' AND index_name='idx_mapeo_hist_producto_hora'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE mapeo_historial ADD INDEX idx_mapeo_hist_producto_hora (id_producto, hora)',
    'SELECT "idx_mapeo_hist_producto_hora already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='mapeo_historial' AND index_name='idx_mapeo_hist_hora'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE mapeo_historial ADD INDEX idx_mapeo_hist_hora (hora)',
    'SELECT "idx_mapeo_hist_hora already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='mapeo_historial' AND index_name='idx_mapeo_hist_mapeo'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE mapeo_historial ADD INDEX idx_mapeo_hist_mapeo (id_mapeo)',
    'SELECT "idx_mapeo_hist_mapeo already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='mapeo_historial' AND index_name='idx_mapeo_hist_tipo'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE mapeo_historial ADD INDEX idx_mapeo_hist_tipo (id_tipo)',
    'SELECT "idx_mapeo_hist_tipo already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='mapeo_historial' AND index_name='idx_mapeo_hist_estado'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE mapeo_historial ADD INDEX idx_mapeo_hist_estado (id_estado)',
    'SELECT "idx_mapeo_hist_estado already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=@db AND table_name='mapeo_historial' AND index_name='idx_mapeo_hist_usuario'
);
SET @sql := IF(@exists=0,
    'ALTER TABLE mapeo_historial ADD INDEX idx_mapeo_hist_usuario (id_usuario)',
    'SELECT "idx_mapeo_hist_usuario already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
