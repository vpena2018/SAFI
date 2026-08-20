<?php
require_once ('include/framework.php');

$accion ="";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; }

if ($accion=="1") {
	$stud_arr[0]["pcode"] = 0;
	$stud_arr[0]["pmsg"] ="No se encontraron Datos";
	$stud_arr[0]["pdata"] ="";
	$stud_arr[0]["pmas"] =0;

	$pagina=1;
	$offset=0;
	$haymas=0;
	$filtros="";

	if (isset($_REQUEST['pg'])) { $pagina = sanear_int($_REQUEST['pg']); }
	if ($pagina>=1) { $offset=$pagina*app_reg_por_pag; }

	$filtro_tipo_traslado = '';
	if (isset($_REQUEST['tipo_traslado'])) {
		$tmpval = strtoupper(sanear_string(trim($_REQUEST['tipo_traslado'])));
		$filtro_tipo_traslado = $tmpval;
		if (!es_nulo($tmpval) && $tmpval!="TODOS") {
			if ($tmpval === 'RENTA') {
				$filtros .= " and (
					traslado_bitacora.tipo_traslado = ".GetSQLValue($tmpval,'text')."
					OR (
						traslado_bitacora.tipo_traslado = ".GetSQLValue('DOMICILIO','text')."
						AND UPPER(traslado_bitacora.tipo_movimiento) = ".GetSQLValue('ENTRADA','text')."
					)
				)";
			} else {
				$filtros .= " and traslado_bitacora.tipo_traslado = ".GetSQLValue($tmpval,'text');
			}
		}
	}

	if (isset($_REQUEST['tipo_movimiento'])) {
		$tmpval = strtoupper(sanear_string(trim($_REQUEST['tipo_movimiento'])));
		if (!es_nulo($tmpval) && $tmpval!="TODOS") {
			$filtros .= " and traslado_bitacora.tipo_movimiento = ".GetSQLValue($tmpval,'text');
		}
	}

	if (isset($_REQUEST['ubicacion_dispositivo'])) {
		$tmpval = strtoupper(sanear_string(trim($_REQUEST['ubicacion_dispositivo'])));
		if (!es_nulo($tmpval) && $tmpval!="TODOS") {
			$filtros .= " and traslado_bitacora.ubicacion_dispositivo = ".GetSQLValue($tmpval,'text');
		}
	}

	if (isset($_REQUEST['numero_traslado'])) {
		$tmpval = sanear_int($_REQUEST['numero_traslado']);
		if (!es_nulo($tmpval)) {
			$filtros .= " and traslado_bitacora.numero_traslado = ".GetSQLValue($tmpval,'int');
		}
	}

	if (isset($_REQUEST['codigo_alterno'])) {
		$tmpval = sanear_string(trim($_REQUEST['codigo_alterno']));
		if (!es_nulo($tmpval)) {
			$filtros .= " and traslado_bitacora.codigo_alterno like ".GetSQLValue($tmpval,'like');
		}
	}

	if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else { $fdesde = ''; }
	if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else { $fhasta = ''; }
	if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
		$filtros .= " and DATE(traslado_bitacora.fecha) BETWEEN '$fdesde' AND '$fhasta' ";
	}

	$datos="";

	$result = sql_select("SELECT traslado_bitacora.*, producto.nombre as nombre_producto
	FROM traslado_bitacora
    inner join producto on producto.codigo_alterno=traslado_bitacora.codigo_alterno
	WHERE 1=1
	$filtros
	ORDER BY traslado_bitacora.fecha DESC, traslado_bitacora.id_bitacora DESC limit 100");

	if ($result!=false){
		if ($result -> num_rows > 0) {

			$datos.='<table id="tabla_movimientos_vehiculos" class="table table-striped table-hover table-sm" style="width:100%">
			<thead class="thead-dark">
				<tr>
					<th>Numero Traslado</th>
					<th style="width: 130px;">Fecha</th>
					<th>Vehiculo</th>
					<th>Combustible</th>
					<th style="width: 120px;">Kilometraje</th>
					<th>Mov. Ubicacion</th>
					<th>Tipo Traslado</th>
					<th>Tipo Movimiento</th>
				</tr>
			</thead>
			<tbody id="tablabody">';

			if ($result -> num_rows>=app_reg_por_pag) { $haymas=1; }
			while ($row = $result -> fetch_assoc()) {
				$numero_js = json_encode((string)$row["numero_traslado"]);
				$tipo_js = json_encode((string)$row["tipo_traslado"]);
				$tipo_movimiento_js = json_encode((string)$row["tipo_movimiento"]);
				$onclick = "movimiento_vehiculo_abrir($numero_js,$tipo_js,$tipo_movimiento_js); return false;";
				$tipo_movimiento = strtoupper(trim((string)$row["tipo_movimiento"]));
				$tipo_traslado_real = trim((string)$row["tipo_traslado"]);
				$tipo_traslado_visual = $tipo_traslado_real;
				if ($filtro_tipo_traslado === 'RENTA' && strtoupper($tipo_traslado_real) === 'DOMICILIO' && $tipo_movimiento === 'ENTRADA') {
					$tipo_traslado_visual = 'RENTA';
				}
				$estilo_movimiento = 'background-color:#f8f9fa; color:#495057;';
				if ($tipo_movimiento === 'ENTRADA') {
					$estilo_movimiento = 'background-color:#d4edda; color:#155724; font-weight:700;';
				} elseif ($tipo_movimiento === 'SALIDA') {
					$estilo_movimiento = 'background-color:#f8d7da; color:#721c24; font-weight:700;';
				}

				$datos.='<tr>
				<td><a href="#" class="btn btn-sm btn-info" onclick="'.htmlspecialchars($onclick, ENT_QUOTES).'">'.($row["numero_traslado"]).'</a></td>
				<td align="left" style="white-space: nowrap;">'.date('d/m/Y h:i A', strtotime($row["fecha"])).'</td>
				<td>'.($row["codigo_alterno"] ).' - '.($row["nombre_producto"]).'</td>
				<td>'.($row["combustible"]).'</td>
				<td align="center" style="white-space: nowrap;">'.($row["kilometraje"]).'</td>
				<td>'.($row["ubicacion_dispositivo"]).'</td>
				<td>'.$tipo_traslado_visual.'</td>
				<td style="'.$estilo_movimiento.'">'.($row["tipo_movimiento"]).'</td>
				</tr>';
			}

			$datos.='</tbody></table>';

			$stud_arr[0]["pcode"] = 1;
			$stud_arr[0]["pmsg"] ="";
			$stud_arr[0]["pdata"] =$datos;
			$stud_arr[0]["pmas"] =$haymas;
		}
	}

	salida_json($stud_arr);
	exit;
}
?>

<div class="card-body">

<div class="botones_accion d-print-none ">
<form id="forma" name="forma" >
 <fieldset id="fs_forma">
	<div class="row">

		<div class="col-sm">
			<?php
			echo campo(
				"tipo_traslado",
				"Tipo Traslado",
				'select',
				valores_combobox_array(
					array(
						array('valor' => 'RENTA', 'texto' => 'RENTA'),
						array('valor' => 'TRASLADO', 'texto' => 'TRASLADO'),
						array('valor' => 'DOMICILIO', 'texto' => 'DOMICILIO'),
						array('valor' => 'CITA', 'texto' => 'CITA')
					),
					'',
					'Todos'
				),
				' ',
				' onkeypress="buscarfiltro(event,\'btn-filtro\');"'
			);
			?>
		</div>

		<div class="col-sm">
			<?php
			echo campo(
				"tipo_movimiento",
				"Tipo Movimiento",
				'select',
				valores_combobox_array(
					array(
						array('valor' => 'ENTRADA', 'texto' => 'ENTRADA'),
						array('valor' => 'SALIDA', 'texto' => 'SALIDA')
					),
					'',
					'Todos'
				),
				' ',
				' onkeypress="buscarfiltro(event,\'btn-filtro\');"'
			);
			?>
		</div>

		<div class="col-sm">
			<?php
			echo campo(
				"ubicacion_dispositivo",
				"Ubicacion",
				'select',
				valores_combobox_array(
					array(
						array('valor' => 'TGU', 'texto' => 'TGU'),
						array('valor' => 'SPS', 'texto' => 'SPS')
					),
					'',
					'Todos'
				),
				' ',
				' onkeypress="buscarfiltro(event,\'btn-filtro\');"'
			);
			?>
		</div>

		<div class="col-sm">
			<?php
			echo campo("numero_traslado","Numero Traslado",'number','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
			?>
		</div>

		<div class="col-sm">
			<?php
			echo campo("codigo_alterno","Codigo Vehiculo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
			?>
		</div>

	</div>

	<div class="row">
			<div class="col-sm text-right">
				<div class="dropdown">
					<a class="btn btn-light dropdown-toggle" href="#" role="button" id="rango_fechas" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Fechas
					</a>
					<div class="dropdown-menu" aria-labelledby="rango_fechas">
						<a class="dropdown-item" href="#" onclick="rf_fechas('hoy'); return false;">Hoy</a>
						<a class="dropdown-item" href="#" onclick="rf_fechas('semana'); return false;">Esta Semana</a>
						<a class="dropdown-item" href="#" onclick="rf_fechas('mes'); return false;">Este Mes</a>
						<a class="dropdown-item" href="#" onclick="rf_fechas('anio'); return false;">Este A&ntilde;o</a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<?php echo campo("rfdesde","Fecha Desde",'date','',' ',' '); ?>
			</div>
			<div class="col-sm">
				<?php echo campo("rfhasta","Fecha Hasta",'date','',' ',' '); ?>
			</div>
			<script>rf_fechas('hoy');</script>

			<div class="col-sm">
			<a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','movimientos_vehiculos_ver.php?a=1','Movimientos de Vehiculos'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>

		</div>

	</div>


 </fieldset>
</form>
</div>

<div id="tablaver" class="table-responsive ">

</div>
 <div class="botones_accion d-print-none ">
	<div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
	</div>

<script>

$('#pagina-botones').html('');

$("#numero_traslado" ).focus();

$("#btn-filtro" ).click();

function movimiento_vehiculo_abrir(numero, tipo_traslado, tipo_movimiento=''){

	modalwindow(
		'Detalle Movimiento Veh\u00edculo',
		'movimiento_vehiculos_detalle.php?numero=' + numero +
		'&tipo_traslado=' + encodeURIComponent(tipo_traslado) +
		'&tipo_movimiento=' + encodeURIComponent(tipo_movimiento)
	);

}

</script>

</div>