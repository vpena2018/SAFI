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

			$salida.= '<br><span class="label-texto" style="color:#000;">'.$valor.'</span>';
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
	<link href="css/app2.css" rel="stylesheet">
	<link href="css/index.css" rel="stylesheet">
	
	<link href="js/helloweek/styles/hello.week.min.css" rel="stylesheet" />
	<link href="js/helloweek/styles/hello.week.theme.min.css" rel="stylesheet" />

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
                     class="mt-4" style="display:block;">

                    <div class="row mb-3">

                        <div class="col-md-3">
							<?php $numero_traslado='58421' ; echo campo("numero_trasladolbl","Numero",'labelb',$numero_traslado,'',' ');?>
                        </div>
						

						<div class="col-md-3">
							<?php
								$fecha = '03/06/2026';
								echo campo("fecha_lbl", "Fecha", "labelb", $fecha, '', ' ');
							?>
						</div>

						<div class="col-md-3">
							<?php
								$tienda = 'Tienda Central';
								echo campo("tienda_lbl", "Tienda", "labelb", $tienda, '', ' ');
							?>
						</div>

						<div class="col-md-3">
							<?php
								$estado = 'Pendiente';
								echo campo("estado_lbl", "Estado", "labelb", $estado, '', ' ');
							?>
						</div>

                    </div>


					<div class="row mb-3">

						<div class="col-12">
							<?php
								$vehiculo = 'EA-03946 MITSUBISHI MONTERO SPORT 4X4 2024 CAMIONETA COLOR BLANCO DIAMANTE DIESEL HDW3633';
								echo campo("vehiculo_lbl", "Vehiculo", "labelb", $vehiculo, '', ' ');
							?>
						</div>

					</div>


					<div class="row">

						<div class="col-md-6">
							<?php
								$salida = 'Hertz Tegucigalpa';
								echo campo("salida_lbl", "Salida", "labelb", $salida, '', ' ');
							?>

							<?php
                                $solicitado_por = 'etabora';
                                echo campo("solicitado_por_lbl", "Solicitado por", "labelb", $solicitado_por, '', ' ');
                            ?>
						</div>

						<div class="col-md-6">
							<?php
								$proveedor = 'TALLER OPT';
								echo campo("proveedor_lbl", "Proveedor", "labelb", $proveedor, '', ' ');
							?>


						</div>

					</div>

					<div class="row mb-2"> 
								
								<div class="col-md-4">   
									<span class="outside-label">Combustible Salida</span>
									<?php 	$combustible_salida = '3/8';	
											$disable_combsalida = 'disabled';				
										echo campo_combustible('combustible_salida',$combustible_salida,$disable_combsalida);       
									?>              
								</div>
								

								<div class="col-md-4 <?php echo $mostrar_entrada; ?>">  
								<span class="outside-label">Combustible Entrada</span>
									<?php 	$combustible_entrada = '3/8';	
											$disable_combentrada = 'disabled';				
										echo campo_combustible('combustible_entrada',$combustible_entrada,$disable_combentrada);
									?>              
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
