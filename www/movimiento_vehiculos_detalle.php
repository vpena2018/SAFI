<?php
require_once ('include/framework.php');

$numero = '';
$tipo_traslado = '';
$tipo_movimiento = '';

if (isset($_GET['numero'])) { $numero = sanear_int($_GET['numero']); }
if (isset($_GET['tipo_traslado'])) { $tipo_traslado = strtoupper(trim(sanear_string($_GET['tipo_traslado']))); }
if (isset($_GET['tipo_movimiento'])) { $tipo_movimiento = strtoupper(trim(sanear_string($_GET['tipo_movimiento']))); }

$tipos_validos = array('RENTA', 'TRASLADO', 'DOMICILIO', 'CITA');
$movimientos_validos = array('SALIDA', 'ENTRADA');
$parametros_validos = (!es_nulo($numero) && !es_nulo($tipo_traslado) && !es_nulo($tipo_movimiento) && in_array($tipo_traslado, $tipos_validos) && in_array($tipo_movimiento, $movimientos_validos));

function obtener_documento_traslado($numero) {
	$sql = "SELECT orden_traslado.*
	,producto.codigo_alterno,producto.nombre,producto.placa
	,orden_traslado_estado.nombre AS elestado
	,l1.nombre AS motorista1
	,l1.grupo_id
	,orden_traslado.observaciones
	,l2.usuario AS solicitante1
	,l3.nombre AS usuariocompleta
	,p1.nombre AS elproveedor
	,t1.nombre AS tiendasalida
	,t2.nombre AS tiendadestino
	,t3.nombre as id_tipo_traslado_lbl
	,t4.nombre as id_tipo_traslado_lbl2
	,t0.nombre AS tiendanombre
	FROM orden_traslado
	LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
	LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
	LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
	LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
	LEFT OUTER JOIN usuario l3 ON (orden_traslado.id_usuario_autoriza=l3.id)
	LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
	LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
	LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)
	LEFT OUTER JOIN tienda t0 ON (t1.tienda_id=t0.id)
	LEFT OUTER JOIN orden_traslado_tipo t3 ON (orden_traslado.id_tipo_traslado=t3.id)
	LEFT OUTER JOIN orden_traslado_tipo t4 ON (orden_traslado.id_tipo_traslado2=t4.id)
	WHERE orden_traslado.numero = ".GetSQLValue($numero,'int')."
	LIMIT 1";

	$result = sql_select($sql);
	if ($result!=false && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return false;
}

function obtener_documento_renta_salida($numero) {
	$sql = "SELECT
	'RENTA' AS tipo_traslado
	,'SALIDA' AS tipo_movimiento
	,inspeccion.id
	,inspeccion.numero AS numero_inspeccion
	,inspeccion.hora AS fecha_inspeccion
	,t1.nombre AS tienda_nombre
	,inspeccion.placa
	,producto.codigo_alterno
	,producto.nombre AS producto_nombre
	,inspeccion.combustible_entrada AS combustible
	,inspeccion.cliente_contacto
	,inspeccion.kilometraje_entrada AS kilometraje
	,entidad.nombre AS cliente_nombre
	,entidad.codigo_alterno AS cliente_codigo
	FROM inspeccion
	LEFT JOIN entidad ON inspeccion.cliente_id = entidad.id
	LEFT JOIN producto ON inspeccion.id_producto = producto.id
	LEFT OUTER JOIN tienda t1 ON (inspeccion.id_tienda=t1.id)
	WHERE inspeccion.numero = ".GetSQLValue($numero,'int')."
	LIMIT 1";

	$result = sql_select($sql);
	if ($result!=false && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return false;
}

function obtener_documento_renta_entrada($numero) {
	$sql = "SELECT
	'RENTA' AS tipo_traslado
	,'ENTRADA' AS tipo_movimiento
	,inspeccion.id
	,inspeccion.numero AS numero_inspeccion
	,inspeccion.hora AS fecha_inspeccion
	,t1.nombre AS tienda_nombre
	,inspeccion.placa
	,producto.codigo_alterno
	,producto.nombre AS producto_nombre
	,inspeccion.combustible_entrada AS combustible
	,inspeccion.cliente_contacto
	,inspeccion.kilometraje_entrada AS kilometraje
	,entidad.nombre AS cliente_nombre
	,entidad.codigo_alterno AS cliente_codigo
	FROM inspeccion
	LEFT JOIN entidad ON inspeccion.cliente_id = entidad.id
	LEFT JOIN producto ON inspeccion.id_producto = producto.id
	LEFT OUTER JOIN tienda t1 ON (inspeccion.id_tienda=t1.id)
	WHERE inspeccion.numero = ".GetSQLValue($numero,'int')."
	LIMIT 1";

	$result = sql_select($sql);
	if ($result!=false && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return false;
}

function obtener_documento_domicilio_salida($numero) {
	$sql = "SELECT
	orden_domicilio.*,
	'DOMICILIO' AS tipo_traslado,
	'SALIDA' AS tipo_movimiento,
	COALESCE((
		SELECT km
		FROM inspeccion i
		WHERE i.id_producto = orden_domicilio.id_producto
		AND i.id_estado = 1
		AND i.tipo_doc = 2
		AND i.tipo_inspeccion = 1
		LIMIT 1
	), 0) AS kilometraje_salida,
	producto.codigo_alterno,
	producto.nombre,
	producto.placa,
	orden_domicilio_estado.nombre AS elestado,
	COALESCE((
		SELECT i.combustible_entrada
		FROM inspeccion i
		WHERE i.id_producto = orden_domicilio.id_producto
		AND i.id_estado = 1
		AND i.tipo_doc = 2
		AND i.tipo_inspeccion = 1
		LIMIT 1
	), 0) AS combustible_salida,
	l1.nombre AS motorista1,
	entidad.nombre AS cliente,
	l2.usuario AS solicitante1,
	t0.nombre AS tiendanombre
	FROM orden_domicilio
	LEFT OUTER JOIN producto ON orden_domicilio.id_producto = producto.id
	LEFT OUTER JOIN orden_domicilio_estado ON orden_domicilio.id_estado = orden_domicilio_estado.id
	LEFT OUTER JOIN usuario l1 ON orden_domicilio.id_motorista = l1.id
	LEFT OUTER JOIN entidad ON orden_domicilio.cliente_id = entidad.id
	LEFT OUTER JOIN usuario l2 ON orden_domicilio.id_usuario = l2.id
	LEFT OUTER JOIN tienda_agencia t1 ON orden_domicilio.id_tienda = t1.id
	LEFT OUTER JOIN tienda t0 ON t1.tienda_id = t0.id
	WHERE orden_domicilio.numero = ".GetSQLValue($numero,'int')."
	LIMIT 1";

	$result = sql_select($sql);
	if ($result!=false && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return false;
}

function obtener_documento_domicilio_entrada($numero) {
	$sql = "SELECT
	orden_domicilio.*,
	'DOMICILIO' AS tipo_traslado,
	'ENTRADA' AS tipo_movimiento,
	COALESCE((
		SELECT km
		FROM inspeccion i
		WHERE i.id_producto = orden_domicilio.id_producto
		AND i.id_estado = 1
		AND i.tipo_doc = 2
		AND i.tipo_inspeccion = 1
		LIMIT 1
	), 0) AS kilometraje_salida,
	producto.codigo_alterno,
	producto.nombre,
	producto.placa,
	orden_domicilio_estado.nombre AS elestado,
	COALESCE((
		SELECT i.combustible_entrada
		FROM inspeccion i
		WHERE i.id_producto = orden_domicilio.id_producto
		AND i.id_estado = 1
		AND i.tipo_doc = 2
		AND i.tipo_inspeccion = 1
		LIMIT 1
	), '1/4') AS combustible_salida,
	l1.nombre AS motorista1,
	entidad.nombre AS cliente,
	l2.usuario AS solicitante1,
	t0.nombre AS tiendanombre
	FROM orden_domicilio
	LEFT OUTER JOIN producto ON orden_domicilio.id_producto = producto.id
	LEFT OUTER JOIN orden_domicilio_estado ON orden_domicilio.id_estado = orden_domicilio_estado.id
	LEFT OUTER JOIN usuario l1 ON orden_domicilio.id_motorista = l1.id
	LEFT OUTER JOIN entidad ON orden_domicilio.cliente_id = entidad.id
	LEFT OUTER JOIN usuario l2 ON orden_domicilio.id_usuario = l2.id
	LEFT OUTER JOIN tienda_agencia t1 ON orden_domicilio.id_tienda = t1.id
	LEFT OUTER JOIN tienda t0 ON t1.tienda_id = t0.id
	WHERE orden_domicilio.numero = ".GetSQLValue($numero,'int')."
	LIMIT 1";

	$result = sql_select($sql);
	if ($result!=false && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return false;
}

function obtener_bitacora_seguridad($numero, $tipo_traslado, $tipo_movimiento) {
	$sql = "SELECT *
	FROM traslado_bitacora
	WHERE numero_traslado = ".GetSQLValue($numero,'int')."
	AND tipo_traslado = ".GetSQLValue($tipo_traslado,'text')."
	AND tipo_movimiento = ".GetSQLValue($tipo_movimiento,'text')."
	ORDER BY id_bitacora DESC
	LIMIT 1";

	$result = sql_select($sql);
	if ($result!=false && $result->num_rows > 0) {
		return $result->fetch_assoc();
	}

	return false;
}

function obtener_texto_movimiento($tipo_movimiento) {
	if ($tipo_movimiento === 'ENTRADA') {
		return array('combustible' => 'Combustible Entrada', 'kilometraje' => 'Kilometraje Entrada');
	}
	return array('combustible' => 'Combustible Salida', 'kilometraje' => 'Kilometraje Salida');
}

function construir_src_firma($firma_raw) {
	$firma = trim((string)$firma_raw);
	if ($firma === '') { return ''; }
	if (stripos($firma, 'data:image') === 0) { return $firma; }
	return 'data:image/png;base64,'.$firma;
}

$mensaje_error = '';
$documento = false;
$bitacora = false;

if ($parametros_validos) {
	switch($tipo_traslado){

		case 'TRASLADO':
			// GUI Traslado
			$documento = obtener_documento_traslado($numero);
			if ($documento===false) {
				$mensaje_error = 'No se encontro el documento de traslado.';
			} else {
				$bitacora = obtener_bitacora_seguridad($numero, $tipo_traslado, $tipo_movimiento);
			}
			break;

		case 'RENTA':
			// GUI RENTA
			if ($tipo_movimiento==='SALIDA') {
				$documento = obtener_documento_renta_salida($numero);
			} else {
				$documento = obtener_documento_renta_entrada($numero);
			}

			if ($documento===false) {
				$mensaje_error = 'No se encontro el documento de renta.';
			} else {
				$bitacora = obtener_bitacora_seguridad($numero, $tipo_traslado, $tipo_movimiento);
			}
			break;

		case 'DOMICILIO':
			// GUI DOMICILIO
			if ($tipo_movimiento==='SALIDA') {
				$documento = obtener_documento_domicilio_salida($numero);
			} else {
				$documento = obtener_documento_domicilio_entrada($numero);
			}

			if ($documento===false) {
				$mensaje_error = 'No se encontro el documento de domicilio.';
			} else {
				$bitacora = obtener_bitacora_seguridad($numero, $tipo_traslado, $tipo_movimiento);
			}
			break;

		case 'CITA':
			// Pendiente: implementar GUI de CITA
			$mensaje_error = 'GUI de CITA pendiente de implementacion.';
			break;
	}
} else {
	$mensaje_error = 'Parametros invalidos.';
}
?>

<div class="card">
	<div class="card-body">
		<?php if (!es_nulo($mensaje_error)) { ?>
			<div class="alert alert-warning"><?php echo $mensaje_error; ?></div>
		<?php } ?>

		<?php if ($documento!==false && $tipo_traslado==='TRASLADO') {
			$txt_mov = obtener_texto_movimiento($tipo_movimiento);
			$fecha_doc = '';
			if (isset($documento['fecha']) && !es_nulo($documento['fecha'])) { $fecha_doc = formato_fecha_de_mysql($documento['fecha']); }

			$inicio_traslado = '';
			if (isset($documento['traslado_inicio']) && !es_nulo($documento['traslado_inicio'])) { $inicio_traslado = formato_fechahora_de_mysql($documento['traslado_inicio']); }

			$final_traslado = '';
			if (isset($documento['traslado_final']) && !es_nulo($documento['traslado_final'])) { $final_traslado = formato_fechahora_de_mysql($documento['traslado_final']); }
		?>

		<div class="card mb-3">
			<div class="card-header font-weight-bold">DATOS DEL DOCUMENTO</div>
			<div class="card-body">

				<div class="row mb-2">
					<div class="col-md-3"><?php echo campo("numero","Numero",'labelb',$documento['numero'],' ',' '); ?></div>
					<div class="col-md-3"><?php echo campo("fecha","Fecha",'labelb',$fecha_doc,' ',' '); ?></div>
					<div class="col-md-3"><?php echo campo("id_tienda","Tienda",'labelb',$documento['tiendanombre'],' ',' '); ?></div>
					<div class="col-md-3"><?php echo campo("estado","Estado",'labelb',$documento['elestado'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("id_tipo_traslado_lbl","Razon del Traslado",'labelb',$documento['id_tipo_traslado_lbl'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("id_tipo_traslado_lbl2","Razon del Traslado",'labelb',$documento['id_tipo_traslado_lbl2'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12"><?php echo campo("vehiculo","Vehiculo",'labelb',trim($documento['codigo_alterno'].' '.$documento['nombre'].' '.$documento['placa']),' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("tiendasalida","Salida de",'labelb',$documento['tiendasalida'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("tiendadestino","Destino a",'labelb',$documento['tiendadestino'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("elproveedor","Proveedor",'labelb',$documento['elproveedor'],' ',' '); ?></div>
					<div class="col-md-6">
						<div style="background-color: #f0f0d7; padding: 8px; border-radius: 4px;">
							<?php echo campo("movimiento","Tipo Movimiento",'labelb',$tipo_movimiento,' ',' '); ?>
						</div>
					</div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("solicitante1","Solicitado por",'labelb',$documento['solicitante1'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("motorista1","Atendido por",'labelb',$documento['motorista1'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("usuariocompleta","Autorizado/Completado por",'labelb',$documento['usuariocompleta'],' ',' '); ?></div>
					<div class="col-md-6">
						<div style="background-color: #f0f0d7; padding: 8px; border-radius: 4px;">
							<?php echo campo("tipo_traslado","Tipo Traslado",'labelb',$tipo_traslado,' ',' '); ?>
						</div>
					</div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("observaciones","Comentarios",'labelb',$documento['observaciones'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("observaciones2","Comentarios Entrada",'labelb',$documento['observaciones2'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("traslado_inicio","Inicio del Traslado",'labelb',$inicio_traslado,' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("traslado_final","Finalizacion del Traslado",'labelb',$final_traslado,' ',' '); ?></div>
				</div>
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header font-weight-bold">BIT&Aacute;CORA DE SEGURIDAD</div>
			<div class="card-body">
				<?php if ($bitacora===false) { ?>
					<div class="alert alert-light border mb-0">No hay registro de bitacora para este movimiento.</div>
				<?php } else {
					$fecha_bitacora = '';
					if (isset($bitacora['fecha']) && !es_nulo($bitacora['fecha'])) { $fecha_bitacora = formato_fechahora_de_mysql($bitacora['fecha']); }
					$firma_src = construir_src_firma($bitacora['firma']);
				?>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("fecha_registro","Fecha Registro",'labelb',$fecha_bitacora,' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("codigo_alterno","Vehiculo",'labelb',$bitacora['codigo_alterno'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("combustible","{$txt_mov['combustible']}",'labelb',$bitacora['combustible'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("kilometraje","{$txt_mov['kilometraje']}",'labelb',$bitacora['kilometraje'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("dispositivo","Dispositivo",'labelb',$bitacora['dispositivo'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("ip_cliente","IP Cliente",'labelb',$bitacora['ip_cliente'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12"><?php echo campo("user_agent","User Agent",'labelb',$bitacora['user_agent'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12">
						<label class="outside-label">Firma</label>
						<div class="border rounded p-2 bg-white" style="min-height: 130px;">
							<?php if ($firma_src==='') { ?>
								<div class="text-muted">Sin firma registrada.</div>
							<?php } else { ?>
								<img src="<?php echo $firma_src; ?>" alt="Firma" style="max-width: 100%; height: auto; max-height: 220px;" />
							<?php } ?>
						</div>
					</div>
				</div>

				<?php } ?>
			</div>
		</div>

		<?php } ?>

		<?php if ($documento!==false && $tipo_traslado==='RENTA') {
			$txt_mov = obtener_texto_movimiento($tipo_movimiento);
			$fecha_renta = '';
			if (isset($documento['fecha_inspeccion']) && !es_nulo($documento['fecha_inspeccion'])) { $fecha_renta = formato_fechahora_de_mysql($documento['fecha_inspeccion']); }
		?>

		<div class="card mb-3">
			<div class="card-header font-weight-bold">DATOS DEL DOCUMENTO</div>
			<div class="card-body">

				<div class="row mb-2">
					<div class="col-md-6">
						<div style="background-color: #f0f0d7; padding: 8px; border-radius: 4px;">
							<?php echo campo("tipo_movimiento_renta_lbl","Movimiento",'labelb',$tipo_movimiento,' ',' '); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div style="background-color: #f0f0d7; padding: 8px; border-radius: 4px;">
							<?php echo campo("tipo_traslado_renta_mostrar_lbl","Tipo traslado",'labelb',$tipo_traslado,' ',' '); ?>
						</div>
					</div>
				</div>

				<div class="row mb-2">
					<div class="col-md-4"><?php echo campo("numero_renta_lbl","Numero",'labelb',$documento['numero_inspeccion'],' ',' '); ?></div>
					<div class="col-md-4"><?php echo campo("fecha_renta_lbl","Fecha",'labelb',$fecha_renta,' ',' '); ?></div>
					<div class="col-md-4"><?php echo campo("tienda_renta_lbl","Tienda",'labelb',$documento['tienda_nombre'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-4"><?php echo campo("vehiculo_renta_lbl","Vehiculo",'labelb',trim($documento['codigo_alterno'].' '.$documento['producto_nombre'].' '.$documento['placa']),' ',' '); ?></div>
					<div class="col-md-4"><?php echo campo("cliente_renta_lbl","Cliente",'labelb',trim($documento['cliente_codigo'].' '.$documento['cliente_nombre']),' ',' '); ?></div>
					<div class="col-md-4"><?php echo campo("contacto_renta_lbl","Nombre del contacto",'labelb',$documento['cliente_contacto'],' ',' '); ?></div>
				</div>

			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header font-weight-bold">BIT&Aacute;CORA DE SEGURIDAD</div>
			<div class="card-body">
				<?php if ($bitacora===false) { ?>
					<div class="alert alert-light border mb-0">No hay registro de bitacora para este movimiento.</div>
				<?php } else {
					$fecha_bitacora = '';
					if (isset($bitacora['fecha']) && !es_nulo($bitacora['fecha'])) { $fecha_bitacora = formato_fechahora_de_mysql($bitacora['fecha']); }
					$firma_src = construir_src_firma($bitacora['firma']);
				?>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("fecha_registro_renta","Fecha Registro",'labelb',$fecha_bitacora,' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("codigo_alterno_renta","Vehiculo",'labelb',$bitacora['codigo_alterno'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("combustible_renta","{$txt_mov['combustible']}",'labelb',$bitacora['combustible'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("kilometraje_renta","{$txt_mov['kilometraje']}",'labelb',$bitacora['kilometraje'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("dispositivo_renta","Dispositivo",'labelb',$bitacora['dispositivo'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("ip_cliente_renta","IP Cliente",'labelb',$bitacora['ip_cliente'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12"><?php echo campo("user_agent_renta","User Agent",'labelb',$bitacora['user_agent'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12">
						<label class="outside-label">Firma</label>
						<div class="border rounded p-2 bg-white" style="min-height: 130px;">
							<?php if ($firma_src==='') { ?>
								<div class="text-muted">Sin firma registrada.</div>
							<?php } else { ?>
								<img src="<?php echo $firma_src; ?>" alt="Firma" style="max-width: 100%; height: auto; max-height: 220px;" />
							<?php } ?>
						</div>
					</div>
				</div>

				<?php } ?>
			</div>
		</div>

		<?php } ?>

		<?php if ($documento!==false && $tipo_traslado==='DOMICILIO') {
			$txt_mov = obtener_texto_movimiento($tipo_movimiento);
			$fecha_domicilio = '';
			if (isset($documento['fecha']) && !es_nulo($documento['fecha'])) { $fecha_domicilio = formato_fecha_de_mysql($documento['fecha']); }

			$inicio_domicilio = '';
			if (isset($documento['domicilio_inicio']) && !es_nulo($documento['domicilio_inicio'])) { $inicio_domicilio = formato_fechahora_de_mysql($documento['domicilio_inicio']); }

			$final_domicilio = '';
			if (isset($documento['domicilio_final']) && !es_nulo($documento['domicilio_final'])) { $final_domicilio = formato_fechahora_de_mysql($documento['domicilio_final']); }
		?>

		<div class="card mb-3">
			<div class="card-header font-weight-bold">DATOS DEL DOCUMENTO</div>
			<div class="card-body">

				<div class="row mb-2">
					<div class="col-md-6">
						<div style="background-color: #f0f0d7; padding: 8px; border-radius: 4px;">
							<?php echo campo("tipo_movimiento_domicilio_lbl","Movimiento",'labelb',$tipo_movimiento,' ',' '); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div style="background-color: #f0f0d7; padding: 8px; border-radius: 4px;">
							<?php echo campo("tipo_traslado_domicilio_lbl","Tipo traslado",'labelb',$tipo_traslado,' ',' '); ?>
						</div>
					</div>
				</div>

				<div class="row mb-2">
					<div class="col-md-3"><?php echo campo("numero_domicilio_lbl","Numero",'labelb',$documento['numero'],' ',' '); ?></div>
					<div class="col-md-3"><?php echo campo("fecha_domicilio_lbl","Fecha",'labelb',$fecha_domicilio,' ',' '); ?></div>
					<div class="col-md-3"><?php echo campo("tienda_domicilio_lbl","Tienda",'labelb',$documento['tiendanombre'],' ',' '); ?></div>
					<div class="col-md-3"><?php echo campo("estado_domicilio_lbl","Estado",'labelb',$documento['elestado'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("vehiculo_domicilio_lbl","Vehiculo",'labelb',trim($documento['codigo_alterno'].' '.$documento['nombre'].' '.$documento['placa']),' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("cliente_domicilio_lbl","Cliente",'labelb',$documento['cliente'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("solicitante_domicilio_lbl","Solicitado por",'labelb',$documento['solicitante1'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("motorista_domicilio_lbl","Atendido por",'labelb',$documento['motorista1'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("obs_domicilio_salida_lbl","Comentarios",'labelb',$documento['observaciones'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("obs_domicilio_entrada_lbl","Comentarios Entrada",'labelb',$documento['observaciones2'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("inicio_domicilio_lbl","Inicio del Domicilio",'labelb',$inicio_domicilio,' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("final_domicilio_lbl","Finalizacion del Domicilio",'labelb',$final_domicilio,' ',' '); ?></div>
				</div>

			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header font-weight-bold">BIT&Aacute;CORA DE SEGURIDAD</div>
			<div class="card-body">
				<?php if ($bitacora===false) { ?>
					<div class="alert alert-light border mb-0">No hay registro de bitacora para este movimiento.</div>
				<?php } else {
					$fecha_bitacora = '';
					if (isset($bitacora['fecha']) && !es_nulo($bitacora['fecha'])) { $fecha_bitacora = formato_fechahora_de_mysql($bitacora['fecha']); }
					$firma_src = construir_src_firma($bitacora['firma']);
				?>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("fecha_registro_domicilio","Fecha Registro",'labelb',$fecha_bitacora,' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("codigo_alterno_domicilio","Vehiculo",'labelb',$bitacora['codigo_alterno'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("combustible_domicilio","{$txt_mov['combustible']}",'labelb',$bitacora['combustible'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("kilometraje_domicilio","{$txt_mov['kilometraje']}",'labelb',$bitacora['kilometraje'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-6"><?php echo campo("dispositivo_domicilio","Dispositivo",'labelb',$bitacora['dispositivo'],' ',' '); ?></div>
					<div class="col-md-6"><?php echo campo("ip_cliente_domicilio","IP Cliente",'labelb',$bitacora['ip_cliente'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12"><?php echo campo("user_agent_domicilio","User Agent",'labelb',$bitacora['user_agent'],' ',' '); ?></div>
				</div>

				<div class="row mb-2">
					<div class="col-md-12">
						<label class="outside-label">Firma</label>
						<div class="border rounded p-2 bg-white" style="min-height: 130px;">
							<?php if ($firma_src==='') { ?>
								<div class="text-muted">Sin firma registrada.</div>
							<?php } else { ?>
								<img src="<?php echo $firma_src; ?>" alt="Firma" style="max-width: 100%; height: auto; max-height: 220px;" />
							<?php } ?>
						</div>
					</div>
				</div>

				<?php } ?>
			</div>
		</div>

		<?php } ?>

		<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top">
			<div class="row">
				<div class="col-sm">
					<a href="#" onclick="$('#ModalWindow').modal('hide'); return false;" class="btn btn-light btn-block mb-2 xfrm"><?php echo 'Cerrar'; ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
