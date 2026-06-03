<?php
// error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once ('../include/config.php' );
//require_once ('framework.php');
	
	$conn = new mysqli(db_ip, db_user, db_pw, db_name);// Conectar a base datos
	if (!mysqli_connect_errno()) {	  
			$conn->set_charset("utf8");
	} else { echo 'Server Error [101]">'; exit; }


	//funciones de BD//
	function sql_select($sql,$param="",$sql2="") {
	global $conn;
	$salida=false;
 // echo $sql;//exit;
	$salida = $conn->query($sql) ;


	return $salida;

} 

function sql_insert($sql) {
	global $conn;
	$salida=false;
//echo $sql;exit;
	if ($conn->query($sql)) { $salida= $conn->insert_id; }
	
	return $salida;
}

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="";}

$accion = $_REQUEST['a'] ?? '';
$codigo = trim($_REQUEST['codigo'] ?? '');


// Leer Datos    ############################  
if ($accion=="L") {

		$result = sql_select("SELECT orden_traslado.* 
		,producto.codigo_alterno,producto.nombre,producto.placa
		,orden_traslado_estado.nombre AS elestado
		,l1.nombre AS motorista1
		,l2.usuario AS solicitante1
		,l3.nombre AS usuariocompleta
		,p1.nombre AS elproveedor
		,t1.nombre AS tiendasalida
		,t2.nombre AS tiendadestino
		,t3.nombre as id_tipo_traslado_lbl
		,t4.nombre as id_tipo_traslado_lbl2
		FROM orden_traslado
		LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
		LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
		LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
		LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
		LEFT OUTER JOIN usuario l3 ON (orden_traslado.id_usuario_autoriza=l3.id)
		LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
		LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
		LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)
		LEFT OUTER JOIN orden_traslado_tipo t3 ON (orden_traslado.id_tipo_traslado=t3.id)
		LEFT OUTER JOIN orden_traslado_tipo t4 ON (orden_traslado.id_tipo_traslado2=t4.id)
		WHERE producto.codigo_alterno LIKE '%$codigo'
		AND id_estado=3
		limit 1");

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();

        echo json_encode([
            'ok' => true,
            'data' => $row
        ]);

    } else {

        echo json_encode([
            'ok' => false,
            'error' => 'No se encontró ningún vehículo'
        ]);
    }

    exit;

	

} // fin leer datos








$txt_mensaje="";
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,, maximum-scale=1.0, user-scalable=0 shrink-to-fit=no">
    <meta name="description" content="Inglosa Gestion de cita para servicio">
    <meta name="author" content="">
    <meta name="robots" content="none" />
    <title>INGLOSA - Programar cita para servicio</title>
 
       
    <link rel="icon" href="img/favicon.ico">
    
	<link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="js/sweetalert2/sweetalert2.min.css" rel="stylesheet">
	<link href="css/index.css" rel="stylesheet">
	<link href="js/helloweek/styles/hello.week.min.css" rel="stylesheet" />
	<link href="js/helloweek/styles/hello.week.theme.min.css" rel="stylesheet" />
	<link href="js/helloweek/public/styles/main.css" rel="stylesheet" />
	<script>



	</script>
  </head>
  <body>


  <form class="form-1" id="formsol" name="formsol">

<div id="form-1div" class="card">

    <div class="card-body form-1-card-body">

        <div class="text-center mb-4 form-1-titulo"
             style="background-color: #ffffff;">

            <img src="img/logo.png"
                 alt=""
                 class="mb-3 mt-2"
                 width="200">

            <hr>

        </div>

        <div class="text-center mb-4 form-1-titulo">
            TRASLADOS VEHICULOS
        </div>

        <div id="mensaje-cuerpo"
             class="form-1-body">
        </div>

        <div id="form-cuerpo"
             class="form-1-body">

            <p class="subtitulo">
                Busqueda
            </p>


            <div class="card p-3">

                <div class="row align-items-end">

                    <div class="col-md-6">

                        <label for="num_inv"
                               class="form-label">

                            CODIGO VEHICULO

                        </label>

                        <input type="text"
                               id="num_inv"
                               name="num_inv"
                               class="form-control"
                               autocomplete="off"
                               required>

                    </div>

                    <div class="col-md-3">

                        <button type="button"
                                id="btnBuscar"
                                class="btn btn-primary w-100"
                                style="margin-bottom:8px;"
                                >

                            BUSCAR

                        </button>

                    </div>

                </div>


                <div id="resultadoBusqueda"
                     class="mt-4" style="display: none;">

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <small class="outside-label" style="color:#8e613e;">Numero</small>
                            <div id="lblNumero"></div>
                        </div>

                        <div class="col-md-3">
                            <small class="outside-label" style="color:#8e613e;">Fecha</small>
                            <div id="lblFecha"></div>
                        </div>

                        <div class="col-md-3">
                            <small class="outside-label" style="color:#8e613e;">Tienda</small>
                            <div id="lblTienda"></div>
                        </div>

                        <div class="col-md-3">
                            <small class="outside-label" style="color:#8e613e;">Estado</small>
                            <div id="lblEstado"></div>
                        </div>

                    </div>


                    <div class="row mb-3">

                        <div class="col-12">
                            <small class="outside-label" style="color:#8e613e;">Vehiculo</small>
                            <div id="lblVehiculo"></div>
                        </div>

                    </div>


                    <div class="row">

                        <div class="col-md-6">
                            <small class="outside-label" style="color:#8e613e;">Salida</small>
                            <div id="lblSalida"></div>
                        </div>

                        <div class="col-md-6">
                            <small class="outside-label" style="color:#8e613e;">Destino</small>
                            <div id="lblDestino"></div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</form>

<script src="js/jquery/jquery-3.5.1.min.js"></script>
<script src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/index.js"></script>
<script src="js/sweetalert2/sweetalert2.min.js"></script>
<script type="text/javascript">

	function buscar_vehiculo(){
		document.getElementById("resultadoBusqueda").style.display = "block";

	}

	function popupconfirmar(titulo, mensaje, onSi) {

    if (document.getElementById('popupSimple')) return;

    const overlay = document.createElement('div');
    overlay.id = 'popupSimple';
    overlay.style = `
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.45);
        display:flex;
        align-items:center;
        justify-content:center;
        z-index:9999;
    `;

    overlay.innerHTML = `
        <div style="
            background:#fff;
            border-radius:12px;
            padding:20px;
            width:340px;
            box-shadow:0 6px 18px rgba(0,0,0,.3);
            font-family:Arial, sans-serif;
        ">
            <div style="
                font-weight:bold;
                font-size:16px;
                margin-bottom:10px;
            ">
                ${titulo}
            </div>

            <div style="
                font-size:14px;
                margin-bottom:18px;
                color:#333;
            ">
                ${mensaje}
            </div>

            <div style="text-align:right;">

                <button id="btnSiSimple" style="
                    background:#0d6efd;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    padding:7px 16px;
                    cursor:pointer;
                    font-weight:bold;
                ">Sí</button>

				<button id="btnNoSimple" style="
                    background:#6c757d;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    padding:7px 14px;
                    cursor:pointer;
                    margin-right:8px;
                ">No</button>


            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    document.getElementById('btnNoSimple').onclick = () => overlay.remove();

    document.getElementById('btnSiSimple').onclick = () => {
        overlay.remove();
        if (typeof onSi === 'function') onSi();
    };
}

	$('#btnBuscar').on('click', function (e) {
    e.preventDefault();

    var codigo = $('#num_inv').val().trim();

    if (codigo === '') {
        mytoast('error', 'Ingrese un código', 3000);
        return;
    }

    $.ajax({
        url: 'index.php',
        type: 'GET',
        dataType: 'json',
        data: {
            a: 'L',
            codigo: codigo
        },
        success: function (resp) {

            if (resp.ok) {

                console.log(resp.data);

                // Ejemplo:
                // $('#lblNumero').html(resp.data.id);
                // $('#placa').val(resp.data.placa);

            } else {
                mytoast(
                    'error',
                    resp.error || 'No se encontró información',
                    3000
                );
            }
        },
        error: function () {
            mytoast(
                'error',
                'Error de comunicación con el servidor',
                3000
            );
        }
    });
});

	$(document).ready(function() {

		$.ajaxSetup({
			cache: false
		});

	});

</script>

<script type="module">
			

</script>
	

</body>
</html>
