<?php
// error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once ('../include/config.php' );
	
	$conn = new mysqli(db_ip, db_user, db_pw, db_name);// Conectar a base datos
	if (!mysqli_connect_errno()) {	  
			$conn->set_charset("utf8");
	} else { echo 'Server Error [101]">'; exit; }

// FUNCIONES
//#######################################################
function salida_json($stud_arr){
    
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 15 Jan 2000 07:00:00 GMT');
            header('Content-type: application/json');
            echo json_encode($stud_arr);    
            exit;   
}

function GetSQLValue($theValue, $theType)
 {
 	global $conn;

 	$theValue =$conn->real_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break; 
    case "like":
      $theValue = ($theValue != "") ? "'%" . $theValue . "%'" : "NULL";
      break;    
       
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "int_cero":
      $theValue = ($theValue != "") ? intval($theValue) : "0";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "0";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL"; //formato_fecha_a_mysql($theValue)
      break;
    case "datetime":
    $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL"; 
    break;
  }
  return $theValue;
}


function es_nulo($campo) {
	$salida=true;
	if ($campo=="" or is_null($campo) or $campo=="0") {$salida=true;} else {$salida=false ;	}
	return $salida;
}

function get_dato_sql($tabla,$campo,$where) {
	global $conn;
    $salida="";

      $sql="select $campo as salida from $tabla $where";

	$result = $conn -> query($sql);
  
      if ($result->num_rows > 0) {    
        $row = $result -> fetch_assoc();    
          $salida=trim($row["salida"]);
         
    }
  
     return $salida;    
    
    
  }





//#######################################################

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
                                onclick="buscar_vehiculo(); return false;">

                            BUSCAR

                        </button>

                    </div>

                </div>


                <div id="resultadoBusqueda"
                     class="mt-4"
                     style="">

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
