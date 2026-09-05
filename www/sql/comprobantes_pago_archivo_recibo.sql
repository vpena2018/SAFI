-- ============================================================================
-- Migracion: agrega la columna archivo_recibo a ventas_comprobantes_pago
-- ----------------------------------------------------------------------------
-- Ejecutar UNA VEZ contra la base de datos "inglosa" (la tabla ventas_comprobantes_pago
-- ya debe existir, creada por sql/comprobantes_pago.sql).
--
-- Guarda el nombre del archivo del "recibo" asociado a un comprobante de pago ya
-- registrado. A diferencia de `archivo` (el comprobante en si, obligatorio desde
-- el inicio), este campo queda vacio hasta que alguien lo suba -puede ser en el
-- momento, o mas adelante, desde la fila del comprobante en la tabla-.
-- ============================================================================

ALTER TABLE `ventas_comprobantes_pago`
  ADD COLUMN `archivo_recibo` VARCHAR(255) DEFAULT NULL
      COMMENT 'Nombre del recibo (dentro de uploa_d_ventas/) asociado a este comprobante. Se puede agregar despues.'
      AFTER `archivo`;
