<?php
require_once ('include/framework.php');

$numero = '';
$tipo_traslado = '';

if (isset($_GET['numero'])) { $numero = trim(sanear_string($_GET['numero'])); }
if (isset($_GET['tipo_traslado'])) { $tipo_traslado = strtoupper(trim(sanear_string($_GET['tipo_traslado']))); }

$tipos_validos = array('RENTA', 'TRASLADO', 'DOMICILIO', 'CITA');
$parametros_validos = (!es_nulo($numero) && !es_nulo($tipo_traslado) && in_array($tipo_traslado, $tipos_validos));
?>

<div class="card">
	<div class="card-body">
		<h5 class="card-title">Detalle Movimiento de Veh&iacute;culo</h5>

		<?php if (!$parametros_validos) { ?>
			<div class="alert alert-warning mb-0">Parametros invalidos.</div>
		<?php } else { ?>
			<p class="mb-2"><strong>N&uacute;mero:</strong><br><?php echo $numero; ?></p>
			<p class="mb-0"><strong>Tipo Traslado:</strong><br><?php echo $tipo_traslado; ?></p>
		<?php } ?>
	</div>
</div>
