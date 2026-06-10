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

	function fraccion($valor){
    $salida=$valor;
    $pieces = explode("/", $valor);
	if (is_array($pieces)){
        if (count($pieces)>1) {         
                $salida="<sup>".$pieces[0]."</sup>&frasl;<sub>".$pieces[1]."</sub>"  ;           
        }		
	}  
     

    return $salida;
}

function campo_combustible($nombre,$valor,$adicional=""){
    $salida='';
    $lineas  = array('E','1/16','1/8','3/16','1/4','5/16','3/8','7/16','1/2','9/16','5/8','11/16','3/4','13/16','7/8','15/16','F');
    
    $salida.=' <div class="btn-group btn-group-toggle mb-2 flex-wrap" data-toggle="buttons" >';
       
    $i=0;
    foreach ($lineas as  $value) {
        $i++;
        if ($value==$valor) {$selected=" checked"; $active=" active";} else {$selected=""; $active="";}
        $salida.=' <label class=" btn btn-outline-secondary btn-sm '.$active.'">
        <input type="radio" class="" name="'.$nombre.'"   value="'.$value.'"  '.$selected.' '.$adicional.' > 
        '.fraccion($value).'</label>'; //id="'.$nombre.$i.'"
        // $salida.='<input class="form-check-input" type="radio" name="'.$nombre.'" id="'.$nombre.$i.'" value="'.$value.'" '.$selected.' required>
        // <label class="form-check-label btn btn-outline-primary btn-sm" for="'.$nombre.$i.'" '.$active.'>'.fraccion($value).'</label>';
        

    }
    $salida.='</div>';
    return $salida;
}

	function campo($nombre,$etiqueta,$tipo,$valor,$class="",$adicional="",$valor2="",$valor_etiqueta="",$numero="") {
	$salida="";
	$salida_end="";
	 // autocomplete="off"  class='form-control-plaintext' readonly

	if ($etiqueta!="") { 
 		$salida = '<div class="form-label-group">';
 		$salida_end='<label for="'.$nombre.'">'.$etiqueta.'</label></div>';
    }

	switch ($tipo) {

		case "label":    
			$salida.= '<input  type="text"  id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" placeholder="" class="form-control-plaintext '.$class.'" '.$adicional.' readonly >';
		    break;
        
		case "labelb":

			$salida = '<div class="form-label-group">';
			$salida.= '<span class="label-label" style="color:#8e613e;">'.$etiqueta.'</span>';

			if ($valor2<>'') {
				$salida.= ' <a href="#" onclick="'.$valor2.' return false;" class="label-icon"><i class="fa fa-edit"></i></a>';
			}

            $salida.= '<br><span id="'.$nombre.'_valor" class="label-texto" style="color:#000;">'.$valor.'</span>';
			$salida_end='</div>';
			break;

		case "text":   
			$salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" placeholder="" class="form-control '.$class.'"  '.$adicional.' autocomplete="off">';
	    	break;

	    case "password":   
			$salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" placeholder="" class="form-control '.$class.'"  '.$adicional.'>';
	    	break;

	    case "number":   
			$salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" placeholder="" class="form-control '.$class.'"  '.$adicional.'  autocomplete="off">';
	    	break;

	    case "email":   
			$salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" placeholder="" class="form-control '.$class.'"  '.$adicional.'  autocomplete="off">';
	    	break;

	    case "password":   
			$salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" placeholder="" class="form-control '.$class.'"  '.$adicional.'>';
	    	break;
	
		case "hidden":   
			$salida = '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="'.$tipo.'"  '.$adicional.' />';
	    	$salida_end="";
	    	break; 

	    case "textarea":
	    	$salida.= '<textarea id="'.$nombre.'" name="'.$nombre.'" spellcheck="false" class="form-control '.$class.'" '.$adicional.'>'.$valor.'</textarea>';
	      	break; 

	    case "select":  
	    	$salida.= '<select id="'.$nombre.'" name="'.$nombre.'" class="form-control '.$class.'" '.$adicional.'>'.$valor.'</select>';
	    	break; 
         
            case "checkboxCustom":  

            $checked = $valor ? "checked" : "";
            $value_attr = "1"; // Valor que se enviará si el checkbox está marcado
            $hidden_value = "0"; // Valor que se enviará si el checkbox no está marcado


            $salida = '<div class="form-group"><div class="custom-control custom-checkbox">';
            $salida .= '<input type="hidden" name="' . $nombre . '" value="' . $hidden_value . '">';
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $value_attr . '" type="checkbox" class="custom-control-input ' . $class . '" ' . $checked . ' ' . $adicional . ' />';
            $salida_end = ' <label class="custom-control-label" for="' . $nombre . '">' . $etiqueta . '</label></div></div>';
		    break;

	    case "checkbox":  
      		$salida ='<div class="form-group"><div class="custom-control custom-checkbox ">';
			$salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="checkbox" class="custom-control-input '.$class.'" '.$adicional.' />';
		    $salida_end=' <label class="custom-control-label" for="'.$nombre.'">'.$etiqueta.'</label></div></div>';
		    break;

		case "checkbox2": 
		  	$salida ='<div class="form-group"> <div class="custom-control custom-switch">';
			$salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="checkbox" class="custom-control-input '.$class.'" '.$adicional.' />';
		    $salida_end='<label class="custom-control-label" for="'.$nombre.'">'.$etiqueta.'</label></div></div>';
		    break;

		case "radio":  
   			$salida ='<div class="form-group"><div class="custom-control custom-radio">';
			$salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="'.$tipo.'" class="custom-control-input '.$class.'" '.$adicional.' />';
		    $salida_end='<label class="custom-control-label" for="'.$nombre.'">'.$etiqueta.'</label></div></div>';
		    break;

	// 	case "date":  
    //   // $salida.= '<script>$(function() {$( "#'.$nombre.'" ).datepicker({showOn: "both",buttonImage: "images/calendar.gif",buttonImageOnly: true,changeMonth: true,changeYear: true,dateFormat: "'.$_SESSION['formato_fecha_jquery'].'"}); });</script>';
    //   // $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="text" '.$adicional.' />';
    //      $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" data-date-format="'.$_SESSION['formato_fecha'].'" type="text" class="form-control '.$class.'" '.$adicional.' />';         
    //      $salida .=  "<script> 	$('#$nombre').datepicker();	</script> ";
	// 	 break;

    //      case "hora":  
    //         // $salida.= '<script>$(function() {$( "#'.$nombre.'" ).datepicker({showOn: "both",buttonImage: "images/calendar.gif",buttonImageOnly: true,changeMonth: true,changeYear: true,dateFormat: "'.$_SESSION['formato_fecha_jquery'].'"}); });</script>';
    //         // $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="text" '.$adicional.' />';
    //            $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" data-date-format="'.$_SESSION['formato_fecha'].'" type="text" class="form-control '.$class.'" '.$adicional.' />';         
    //            $salida .=  "<script> 	$('#$nombre').datepicker();	</script> ";
                                  
         
          
    //  	 break;

    case "date":   
        $salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" " class="form-control '.$class.'"  '.$adicional.' autocomplete="off">';
        break;

        case "time":   
			$salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  class="form-control '.$class.'"  '.$adicional.' autocomplete="off">';
	    	break;

        case "datetime-local":   
        $salida.= '<input type="'.$tipo.'" id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" " class="form-control '.$class.'"  '.$adicional.' autocomplete="off">';
        break;

		case "boton":  //falta  
			$salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="'.$tipo.'" class="form-control '.$class.'" '.$adicional.' />';
		    break; 


		case "select2":
	
		  /*$lg=',language: "es"' ; 
		  $salida = '<div class="form-group"><label class="outside-label">'.$etiqueta.'</label>';
	      $salida.= '<select id="'.$nombre.'" name="'.$nombre.'" class="form-control select2 '.$class.'" style="width: 100%;" '.$adicional.'>'.$valor.'</select>';
	      $salida.= '<script>$(document).ready(function() {$("#'.$nombre.'").select2( {theme: "classic" '.$lg.'   }); });    </script>';
		  $salida_end='</div>';
	      break;*/
                      //evento change para ventas_mant.php
            if ($nombre == 'id_estado') {
            $evento_estado = ' onchange="toggleClientePorEstado(this)" ';
            } else {
                $evento_estado = '';
            }

            $lg = ',language: "es"';

            $salida = '<div class="form-group">';
            if ($etiqueta != '') {
                $salida .= '<label class="outside-label">'.$etiqueta.'</label>';
            }

            $salida .= '
                <select 
                    id="'.$nombre.'" 
                    name="'.$nombre.'" 
                    class="form-control select2 '.$class.'" 
                    style="width:100%;" 
                    '.$adicional.'
                    '.$evento_estado.'
                >
                    '.$valor.'
                </select>';

            $salida .= '
                <script>
                $(document).ready(function() {
                    $("#'.$nombre.'").select2({
                        theme: "classic" '.$lg.'
                    });
                });
                </script>';

            $salida_end = '</div>';
            break;  

		case "select2ajax":
            $nombre_id=$nombre;
            if (substr($nombre_id, -2)=="[]") {
                $nombre_id =substr($nombre_id, 0, -2).$numero;
            }
		  $lg=',language: "es"' ; 
		  $salida = '<div class="form-group"><label class="outside-label">'.$etiqueta.'</label>';
	      $salida.= '<select id="'.$nombre_id.'" name="'.$nombre.'" class="form-control select2 '.$class.'" style="width: 100%;" '.$adicional.'>';
	      if(trim($valor_etiqueta)<>""){
              $salida .= '<option value="'.$valor.'" selected="selected">'.$valor_etiqueta.'</option>';
          }
          $salida.= '</select>';

          $salida.= '<script>$(document).ready(function() {$("#'.$nombre_id.'").select2( {theme: "classic" '.$lg;
		  $salida.= ", ajax: {
		    url: '".$valor2."',
		    
		    dataType: 'json',
		    delay: 250,
		    data: function (params) {
		      return {
		        q: params.term, 
		        page: params.page
		      };
		    },
		    processResults: function (data) {	          
		            return {
		                
		                results: data.results
		            };
		        },
		        
		    cache: false
		  },
		 
		  minimumInputLength: 3, });
		   }); </script>"; 
		  $salida_end='</div>';

		break;  

	}			

 

	$salida .= $salida_end;      

	return $salida;

}


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

if ($accion=="P") {

    $numero_traslado_req = trim($_REQUEST['numero_traslado'] ?? '');
    $dispositivo_req = trim($_REQUEST['dispositivo'] ?? '');
    $firma_req = $_REQUEST['firma'] ?? '';
    $ip_cliente = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($numero_traslado_req === '') {
        echo json_encode([
            'ok' => false,
            'error' => 'No hay número de traslado para procesar'
        ]);
        exit;
    }

    if ($firma_req === '') {
        echo json_encode([
            'ok' => false,
            'error' => 'Debe capturar la firma'
        ]);
        exit;
    }

    $firma_limpia = preg_replace('#^data:image/\w+;base64,#i', '', trim($firma_req));

    if ($firma_limpia === '' || !preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $firma_limpia)) {
        echo json_encode([
            'ok' => false,
            'error' => 'La firma no es valida'
        ]);
        exit;
    }

    $numero_traslado_sql = $conn->real_escape_string($numero_traslado_req);
    $dispositivo_sql = $conn->real_escape_string($dispositivo_req);
    $ip_cliente_sql = $conn->real_escape_string($ip_cliente);
    $user_agent_sql = $conn->real_escape_string($user_agent);
    $firma_sql = $conn->real_escape_string($firma_limpia);

    $sql = "INSERT INTO traslado_bitacora
            (numero_traslado, fecha, dispositivo, ip_cliente, user_agent, firma)
            VALUES
            ('{$numero_traslado_sql}', NOW(), '{$dispositivo_sql}', '{$ip_cliente_sql}', '{$user_agent_sql}', '{$firma_sql}')";

    $insert_id = sql_insert($sql);

    if ($insert_id) {
        echo json_encode([
            'ok' => true,
            'id' => $insert_id
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'error' => 'No se pudo guardar el registro'
        ]);
    }

    exit;
}

//variables
$numero_traslado="";

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
		WHERE producto.codigo_alterno LIKE '%$codigo'
		      AND NOT EXISTS (
				    SELECT 1
				    FROM traslado_bitacora b
				    INNER JOIN orden_traslado ot2
				        ON ot2.numero = b.numero_traslado
				    WHERE ot2.id_producto = orden_traslado.id_producto
				)
		AND id_estado=4
		ORDER BY FECHA DESC
		limit 1");

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();

		if (isset($row["numero"])) {$numero_traslado= $row["numero"];} else {$numero_traslado= "";}





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
	<link href="css/app2.css" rel="stylesheet">
	<link href="css/index.css" rel="stylesheet">
	
	<link href="js/helloweek/styles/hello.week.min.css" rel="stylesheet" />
	<link href="js/helloweek/styles/hello.week.theme.min.css" rel="stylesheet" />

  </head>
  <body>


  <form class="form-1" id="formsol" name="formsol">

<div id="form-1div" class="card mx-auto" style="max-width: 900px;">

    <div class="card-body form-1-card-body">

<div class="text-center mb-2 form-1-titulo"
     style="background-color: #ffffff;">

    <img src="img/logo.png"
         alt=""
         class="mb-1 mt-1"
         width="150">

    <hr class="my-1">

</div>

        <div class="text-center mb-1 form-1-titulo" style="font-size:2rem; font-weight:700; letter-spacing:1px;">
            TRASLADO VEHICULOS
        </div>



        <div id="form-cuerpo"
             class="form-1-body">

            <p class="subtitulo" style="margin-top:0; margin-bottom:6px;">
                Busqueda
            </p>


            <div class="card p-3 mb-0">

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
                               minlength="4"
                               autocomplete="off"
                               required>

                    </div>

                    <div class="col-md-3">

                        <button type="button"
                                id="btnBuscar"
                                class="btn btn-primary w-100"
                                style="margin-bottom:0px;"
                                >

                            BUSCAR

                        </button>

                    </div>

                </div>


                <div id="resultadoBusqueda"
                     class="mt-4" style="display:block;">

                    <div class="row mb-0">

                        <div class="col-auto">
							<?php echo campo("numero_trasladolbl","Numero",'labelb','',' ');?>
                        </div>
						

						<div class="col-auto">
							<?php
                                //$fecha = date('d/m/Y');
								echo campo("fecha_lbl", "Fecha", "labelb", '', ' ');
							?>
						</div>

						<div class="col-auto">
							<?php
								$tienda = 'Tienda Central';
								echo campo("tienda_lbl", "Tienda", "labelb", '', '', ' ');
							?>
						</div>

						<div class="col-auto">
							<?php
								$estado = 'Pendiente';
								echo campo("estado_lbl", "Estado", "labelb", '', '', ' ');
							?>
						</div>
                        <div class="col-auto">
							<?php
								echo campo("solicitado_por_lbl", "Solicitado por", "labelb", '', ' ');
							?>
						</div>
                        <div class="col-auto">
							<?php
								echo campo("atendido_por_lbl", "Atendido por", "labelb", '', ' ');
							?>
						</div>

                    </div>


					<div class="row mb-0">

						<div class="col-12">
							<?php
								echo campo("vehiculo_lbl", "Vehiculo", "labelb", '', ' ');
							?>
						</div>
					</div>


					<div class="row mb-0">
						<div class="col-auto">
							<?php
								echo campo("placa_lbl", "Placa", "labelb", '', ' ');
							?>
						</div>

						<div class="col-auto">
							<?php
								echo campo("salida_lbl", "Salida", "labelb", '', ' ');
							?>
						</div>

						<div class="col-auto">
							<?php
								echo campo("proveedor_lbl", "Proveedor/Destino", "labelb", '', ' ');
							?>
						</div>

                        <div class="col-auto">
							<?php
								echo campo("kilometraje_salida_lbl", "Kilometraje salida", "labelb", '', ' ');
							?>
						</div>
                        

					</div>


                    <div class="row mb-2"> 
								
								<div class="col-md-12">   
									<span class="outside-label">Combustible Salida</span>
									<?php 		
											$disable_combsalida = 'disabled';				
										echo campo_combustible('combustible_salida','',$disable_combsalida);       
									?>              
								</div>
								
<!-- 
								<div class="col-md-4 <?php //echo $mostrar_entrada; ?>">  
								<span class="outside-label">Combustible Entrada</span>
									<?php 		
											//$disable_combentrada = 'disabled';				
										//echo campo_combustible('combustible_entrada','',$disable_combentrada);
									?>              
								</div> -->

								
					</div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="outside-label" for="firmaPad">Firma</label>
                            <canvas id="firmaPad"
                                    style="width:100%; height:140px; border:2px solid #c8ced4; border-radius:8px; background:#fff; touch-action:none;"></canvas>
                            <div class="text-right mt-2">
                                <button type="button" id="btnLimpiarFirma" class="btn btn-outline-secondary btn-sm">Limpiar firma</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button"
                                    id="btnProcesar"
                                    class="btn btn-success btn-lg w-100 py-3"
                                    style="font-weight:700; letter-spacing:.5px;">
                                PROCESAR
                            </button>
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

    let firmaCanvas = null;
    let firmaCtx = null;
    let firmaDibujando = false;
    let firmaTieneTrazo = false;

	function buscar_vehiculo(){
		document.getElementById("resultadoBusqueda").style.display = "block";

	}

    function ajustarCanvasFirma() {
        if (!firmaCanvas || !firmaCtx) {
            return;
        }

        const ratio = window.devicePixelRatio || 1;
        const rect = firmaCanvas.getBoundingClientRect();
        firmaCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
        firmaCanvas.height = Math.max(1, Math.floor(rect.height * ratio));
        firmaCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
        firmaCtx.lineWidth = 2;
        firmaCtx.lineCap = 'round';
        firmaCtx.strokeStyle = '#111';
    }

    function obtenerPosicionFirma(evt) {
        const rect = firmaCanvas.getBoundingClientRect();
        if (evt.touches && evt.touches.length > 0) {
            return {
                x: evt.touches[0].clientX - rect.left,
                y: evt.touches[0].clientY - rect.top
            };
        }

        return {
            x: evt.clientX - rect.left,
            y: evt.clientY - rect.top
        };
    }

    function iniciarFirma(evt) {
        evt.preventDefault();
        firmaDibujando = true;
        const pos = obtenerPosicionFirma(evt);
        firmaCtx.beginPath();
        firmaCtx.moveTo(pos.x, pos.y);
    }

    function moverFirma(evt) {
        if (!firmaDibujando) {
            return;
        }
        evt.preventDefault();
        const pos = obtenerPosicionFirma(evt);
        firmaCtx.lineTo(pos.x, pos.y);
        firmaCtx.stroke();
        firmaTieneTrazo = true;
    }

    function terminarFirma(evt) {
        if (!firmaDibujando) {
            return;
        }
        evt.preventDefault();
        firmaDibujando = false;
        firmaCtx.closePath();
    }

    function limpiarFirma() {
        if (!firmaCanvas || !firmaCtx) {
            return;
        }
        firmaCtx.clearRect(0, 0, firmaCanvas.width, firmaCanvas.height);
        firmaTieneTrazo = false;
    }

    function obtenerFirmaBase64() {
        if (!firmaCanvas || !firmaTieneTrazo) {
            return '';
        }
        return firmaCanvas.toDataURL('image/png');
    }

    function inicializarFirmaPad() {
        firmaCanvas = document.getElementById('firmaPad');
        if (!firmaCanvas) {
            return;
        }

        firmaCtx = firmaCanvas.getContext('2d');
        ajustarCanvasFirma();
        window.addEventListener('resize', ajustarCanvasFirma);

        firmaCanvas.addEventListener('mousedown', iniciarFirma);
        firmaCanvas.addEventListener('mousemove', moverFirma);
        firmaCanvas.addEventListener('mouseup', terminarFirma);
        firmaCanvas.addEventListener('mouseleave', terminarFirma);

        firmaCanvas.addEventListener('touchstart', iniciarFirma, { passive: false });
        firmaCanvas.addEventListener('touchmove', moverFirma, { passive: false });
        firmaCanvas.addEventListener('touchend', terminarFirma, { passive: false });
        firmaCanvas.addEventListener('touchcancel', terminarFirma, { passive: false });
    }

    function formatearFechaDdMmYyyy(fechaRaw) {
        if (!fechaRaw) {
            return '';
        }

        const valor = String(fechaRaw).trim();

        if (/^\d{2}\/\d{2}\/\d{4}$/.test(valor)) {
            return valor;
        }

        const soloFecha = valor.split(' ')[0];
        const partes = soloFecha.split('-');

        if (partes.length === 3) {
            return String(partes[2]).padStart(2, '0') + '/' +
                String(partes[1]).padStart(2, '0') + '/' +
                partes[0];
        }

        return '';
    }

    function setCombustibleValor(nombreCampo, valor) {
        const selector = 'input[name="' + nombreCampo + '"]';
        const $grupo = $(selector);

        $grupo.prop('checked', false);
        $grupo.closest('label').removeClass('active');

        if (!valor) {
            return;
        }

        const $opcion = $(selector + '[value="' + valor + '"]');
        $opcion.prop('checked', true);
        $opcion.closest('label').addClass('active');
    }

    function limpiarCamposResultado() {
        $('#numero_trasladolbl_valor').html('');
        $('#fecha_lbl_valor').html('');
        $('#tienda_lbl_valor').html('');
        $('#estado_lbl_valor').html('');
        $('#vehiculo_lbl_valor').html('');
        $('#placa_lbl_valor').html('');
        $('#salida_lbl_valor').html('');
        $('#solicitado_por_lbl_valor').html('');
        $('#atendido_por_lbl_valor').html('');
        $('#kilometraje_salida_lbl_valor').html('');
        $('#proveedor_lbl_valor').html('');
        setCombustibleValor('combustible_salida', '');
        //setCombustibleValor('combustible_entrada', '');
        limpiarFirma();
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

	limpiarFirma();

    var codigo = $('#num_inv').val().trim();

    if (codigo === '') {
        mytoast('error', 'Ingrese un código', 3000);
        return;
    }

/*     if (codigo.length < 4) {
        mytoast('error', 'Ingrese mínimo 4 caracteres', 3000);
        return;
    } */

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

                    $('#numero_trasladolbl_valor').html(resp.data.numero || '');
                    $('#fecha_lbl_valor').html(formatearFechaDdMmYyyy(resp.data.fecha));
                    $('#tienda_lbl_valor').html(resp.data.tiendanombre || '');
                    
                    $('#estado_lbl_valor').html(
                        (resp.data.elestado || '').toLowerCase() === 'autorizar'
                            ? 'Autorizado'
                            : (resp.data.elestado || '')
                    );

                    $('#vehiculo_lbl_valor').html(resp.data.codigo_alterno+' '+resp.data.nombre || '');
					$('#placa_lbl_valor').html(resp.data.placa || '');

					
                    $('#salida_lbl_valor').html(resp.data.tiendasalida || '');
                    $('#solicitado_por_lbl_valor').html(resp.data.solicitante1 || '');
                    $('#atendido_por_lbl_valor').html(resp.data.motorista1 || '');

                    $('#kilometraje_salida_lbl_valor').html(
                        `${Number(resp.data.kilometraje_salida || 0).toLocaleString('es-HN')} km`
                    );

                    let destino=resp.data.tipo_destino;


                    if(destino==1){
                        $('#proveedor_lbl_valor').html(resp.data.tiendadestino || '');
                    }
                        else if(destino==2){
                             $('#proveedor_lbl_valor').html(resp.data.elproveedor || '');
                            }


                    //$('#proveedor_lbl_valor').html(resp.data.elproveedor || '');
                    $('#proveedor_lbl_valor').siblings('.label-label').text(resp.data.tipo_destino == 1 ? 'Destino a' : 'Proveedor');


                        setCombustibleValor('combustible_salida', resp.data.combustible_salida || '');
                        //setCombustibleValor('combustible_entrada', resp.data.combustible_entrada || '');


            } else {
                limpiarCamposResultado();
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

    $('#btnProcesar').on('click', function (e) {
        e.preventDefault();

        var numeroTraslado = ($('#numero_trasladolbl_valor').text() || '').trim();

        if (numeroTraslado === '') {
            mytoast('error', 'Favor buscar el vehiculo', 3000);
            return;
        }

        var firmaBase64 = obtenerFirmaBase64();
        if (firmaBase64 === '') {
            mytoast('error', 'Debe capturar la firma', 3000);
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: 'index.php',
            type: 'POST',
            dataType: 'json',
            data: {
                a: 'P',
                numero_traslado: numeroTraslado,
                dispositivo: navigator.platform || '',
                firma: firmaBase64
            },
            success: function (resp) {
                if (resp.ok) {
                    mytoast('success', 'Procesado correctamente', 3000);
                    limpiarCamposResultado();
                    $('#num_inv').val('').focus();
                } else {
                    mytoast('error', resp.error || 'Error al procesar', 3000);
                }
            },
            error: function () {
                mytoast('error', 'Error de comunicación con el servidor', 3000);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

	$(document).ready(function() {

		$.ajaxSetup({
			cache: false
		});

        inicializarFirmaPad();

        $('#btnLimpiarFirma').on('click', function () {
            limpiarFirma();
        });

	});



</script>

<script type="module">
			

</script>
	

</body>
</html>
