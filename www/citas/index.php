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

function obtener_ip2() {
	$salida="";
	$pieces = explode(".", $_SERVER['REMOTE_ADDR']);
	if (is_array($pieces)){
		foreach ($pieces as $key => $value) {
			$salida.= $value.'.';
		}
	} else {
		$salida= $pieces;
	}
	return $salida;
}

function generate_id()// de 40 caracteres de ancho
{
	return sha1(rand(10000, 30000) . time() . rand(10000, 30000));
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

function get_array_tiendas(){
	global $conn;
	$salida="";

	$sql="SELECT tienda.id,tienda.nombre_cita
	FROM cita_taller 
	LEFT OUTER JOIN tienda ON (cita_taller.id_tienda=tienda.id)
	where cita_taller.interno=0 and cita_taller.activo=1
	GROUP BY cita_taller.id_tienda 
	ORDER BY tienda.nombre_cita";

	$result = $conn -> query($sql);

	if ($result -> num_rows > 0) {
		$coma="";
		while ($row = $result -> fetch_assoc()) {			
			$salida.=$coma.'['.$row['id'].",'".$row['nombre_cita']."']";
			$coma=",";			
		} 
	}

 return $salida;
}

function get_array_talleres(){
	global $conn;
	$salida="";

	$sql="SELECT id, id_tienda, id_taller, taller_nombre
	FROM cita_taller
	where cita_taller.interno=0 and activo=1
	ORDER BY id_tienda";

	$result = $conn -> query($sql);

	if ($result -> num_rows > 0) {
		$coma="";
		while ($row = $result -> fetch_assoc()) {			
			$salida.=$coma.'['.$row['id_taller'].",'".$row['taller_nombre']."',".$row['id_tienda'].']';
			$coma=",";			
		} 
	}

 return $salida;
}

function get_array_horario(){
	global $conn;
	$salida="";

	$sql="SELECT id, nombre, hora
	FROM cita_horario
	ORDER BY hora";

	$result = $conn -> query($sql);

	if ($result -> num_rows > 0) {
		$coma="";
		while ($row = $result -> fetch_assoc()) {	
			$salida.=$coma."['".$row['id']."','".$row['nombre']."',".$row['hora'].']';
			$coma=",";			
		} 
	}

 return $salida;
}




//#######################################################

$txt_mensaje="";

if (isset($_REQUEST['a'])){


 $accion = $_REQUEST['a']; 


//Guardar enviar solicitud
if ($accion=="sol") { 

	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ='Se produjo un error, favor vuelva a intentar';

	//Validar
	$verror="";
	$idvehiculo="";
	$num_inv="";
	$placa="";
	$or="";$filtro="";

	if (trim($_REQUEST['comentario'])=="") {$verror="Ingrese el reporte de detalles de la unidad";  }
	if (trim($_REQUEST['correo'])=="") {$verror="Ingrese el correo electronico";  }
	if (trim($_REQUEST['telefono'])=="") {$verror="Ingrese el Numero de telefono";  }
	if (trim($_REQUEST['identidad'])=="") {$verror="Ingrese el numero de identidad del contacto";  }
	if (trim($_REQUEST['nombre'])=="") {$verror="Ingrese el Nombre del Contacto";  }
	if (trim($_REQUEST['kilometraje'])=="") {$verror="Ingrese el kilometraje actual del Vehiculo";  }
	if (trim($_REQUEST['tipo'])=="") {$verror="Ingrese el tipo de Revision";  }
	if (trim($_REQUEST['hora'])=="") {$verror="Seleccione la hora";  }
	if (trim($_REQUEST['fecha'])=="") {$verror="Seleccione la fecha";  }
	if (trim($_REQUEST['taller'])=="") {$verror="Seleccione el taller";  }
	if (trim($_REQUEST['sucursal'])=="") {$verror="Seleccione la sucursal";  }
	
	if (trim($_REQUEST['num_inv'])=="" and trim($_REQUEST['placa'])=="" and trim($_REQUEST['chasis'])=="") {$verror="Ingrese el numero de inventario, Placa o Vin del Vehiculo";  }
	 
	if (trim($_REQUEST['num_inv'])<>""){
		if (strlen(trim($_REQUEST['num_inv']))<4) {
			$verror="Ingrese un numero de inventario valido";
		}
	}


	if ($verror=="") {	
		$telefono=filter_var ( trim($_REQUEST['telefono']), FILTER_SANITIZE_NUMBER_INT);
		if ($telefono<10000000 or $telefono>99999999) {
			$verror="Ingrese un numero de telefono celular valido";
		}
	}

	if ($verror=="") {	

	
    
	if (trim($_REQUEST['num_inv'])<>"") {
		$filtro="codigo_alterno like'%".$conn->real_escape_string(trim($_REQUEST['num_inv']))."'";
		$or=" OR ";
	}

	if (trim($_REQUEST['placa'])<>""  and  trim(strtoupper($_REQUEST['placa']))<>"SIN PLACA")  {
		$filtro=$filtro.$or."placa='".$conn->real_escape_string(trim($_REQUEST['placa']))."'";
		$or=" OR ";
	}

	if (trim($_REQUEST['chasis'])<>"") {
		$filtro=$filtro.$or."chasis like'%".$conn->real_escape_string(trim($_REQUEST['chasis']))."'";

	}

	$query1="SELECT id, codigo_alterno, placa
	FROM producto
	WHERE tipo=0
	AND ($filtro)
	LIMIT 1";

	$vehiculo = $conn->query($query1) ;

	if ($vehiculo!=false){
        if ($vehiculo -> num_rows > 0) { 
			$row = $vehiculo -> fetch_assoc();
			$idvehiculo=$row["id"];			
		} else {$verror="Vehiculo no fue encontrado, asegurese que el numero de inventario y/o placa sean correctos";}
	} else {$verror="Vehiculo no fue encontrado, asegurese que el numero de inventario y/o placa sean correctos";}
	
	} 

	//validar no repetir el mismo vehiculo el mismo dia
	if ($idvehiculo<>"" and $verror=="") {
		$repetido = $conn->query("SELECT id FROM cita WHERE (fecha_cita=".GetSQLValue($_REQUEST["fecha"],"text")." OR fecha=CURDATE()) AND id_producto=".GetSQLValue($idvehiculo,"int")) ;

		if ($repetido!=false){
			if ($repetido -> num_rows > 0) { 
				$verror="Actualmente ya tiene una cita agendada para ese vehiculo";		
			} 
		}		
	}


	if ($verror=="") {
	//Guardar


	$sqlcampos="";
	//if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
	
	 $sqlcampos.= " , id_producto =".GetSQLValue($idvehiculo,"int");  
	
	
	if (isset($_REQUEST["sucursal"])) { $sqlcampos.= " , id_tienda =".GetSQLValue($_REQUEST["sucursal"],"int"); } 
	if (isset($_REQUEST["taller"])) { $sqlcampos.= " , id_taller =".GetSQLValue($_REQUEST["taller"],"int"); } 
	if (isset($_REQUEST["tipo"])) { $sqlcampos.= " , tipo =".GetSQLValue($_REQUEST["tipo"],"int"); } 	
//	if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
	if (isset($_REQUEST["correo"])) { $sqlcampos.= " , cliente_email =".GetSQLValue($_REQUEST["correo"],"text"); } 
	if (isset($_REQUEST["nombre"])) { $sqlcampos.= " , cliente_contacto =".GetSQLValue($_REQUEST["nombre"],"text"); } 
	if (isset($_REQUEST["identidad"])) { $sqlcampos.= " , cliente_contacto_identidad =".GetSQLValue($_REQUEST["identidad"],"text"); } 
	if (isset($_REQUEST["telefono"])) { $sqlcampos.= " , cliente_contacto_telefono =".GetSQLValue($_REQUEST["telefono"],"text"); } 
	if (isset($_REQUEST["fecha"])) { $sqlcampos.= " , fecha_cita =".GetSQLValue($_REQUEST["fecha"],"text"); } 
	if (isset($_REQUEST["hora"])) { $sqlcampos.= " , hora_cita =".GetSQLValue($_REQUEST["hora"],"int"); } 
	if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
	if (isset($_REQUEST["placa"])) { $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa"],"text"); } 	
	//if (isset($_REQUEST["chasis"])) { $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis"],"text"); } 
	if (isset($_REQUEST["comentario"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["comentario"],"text"); } 
	if (isset($_REQUEST["empresa"])) { $sqlcampos.= " , empresa =".GetSQLValue($_REQUEST["empresa"],"text"); } 
	if (isset($_REQUEST["ciudad"])) { $sqlcampos.= " , ciudad =".GetSQLValue($_REQUEST["ciudad"],"text"); } 
	
	//Crear nuevo            
	$sqlcampos.= " , id_usuario =0";
	$sqlcampos.= " , id_estado =1";
	$sqlcampos.= " , plataforma =0";
	
	
	$result_num = $conn->query("SELECT IFNULL((max(numero)+1),1) as salida FROM cita");//where id_tienda=".$_SESSION['tienda_id']  
	if ($result_num->num_rows > 0) {    
	  $row_num = $result_num -> fetch_assoc();    
		$sqlcampos.= " , numero =".GetSQLValue(trim($row_num["salida"]),"int"); 	   
  	}
	
	$sql_nuevo="INSERT INTO cita SET fecha =NOW(), hora =NOW() ".$sqlcampos." ";

	if ($conn->query($sql_nuevo)!=false){
		$stud_arr[0]["pcode"] = 1;
		$stud_arr[0]["pmsg"] ='Su cita fue agendada satisfactoriamente, recibirá un correo de confirmación';

		$cid= $conn->insert_id;

		//Enviar correo
		require_once ('../correo_cita.php');

	}


	
	}  else {
		$stud_arr[0]["pcode"] = 0;
		  $stud_arr[0]["pmsg"] =$verror;
	  }

	

    salida_json($stud_arr);
    exit;

} 


//calendario obtener fechas disponibles
if ($accion=="cal") { 

	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ='Se produjo un error, favor vuelva a intentar';
	$salida = array();
	$sucursal ="";
	$taller ="";
	$filtros ="";

	if (isset($_REQUEST['s'])) { $sucursal = intval($_REQUEST['s']); } 
	if (isset($_REQUEST['t'])) { $taller = intval($_REQUEST['t']); } 
	if (isset($_REQUEST['m'])) { $mes = intval($_REQUEST['m']); } 
	if (isset($_REQUEST['y'])) { $anio = intval($_REQUEST['y']); } 

	if (!es_nulo($sucursal)){$filtros.=" and tienda = ".$sucursal ;}
	if (!es_nulo($taller)){$filtros.=" and taller = ".$taller ;}
	
	// if (!es_nulo($mes) and !es_nulo($anio) ){$filtros.=" and fecha>= '".$anio."-".str_pad($mes, 2, '0', STR_PAD_LEFT)."-01'" ;}
	if (!es_nulo($mes)){$filtros.=" and MONTH(fecha) = ".str_pad($mes, 2, '0', STR_PAD_LEFT) ;}
	if (!es_nulo($anio)){$filtros.=" and YEAR(fecha) = ".$anio ;}

	// require_once ('../include/config.php' );
	// // Conectar a base datos
	// $conn = new mysqli(db_ip, db_user, db_pw, db_name);
	// if (!mysqli_connect_errno()) {	  
	// 		$conn->set_charset("utf8");
	// } else { echo 'Server Error [101]">'; exit; }

	$query="SELECT id, tienda, taller, fecha, dia_semana, cantidad, cantidad_por_hora
		,IFNULL((SELECT COUNT(cita.id) FROM  cita WHERE cita.id_estado<20 AND cita.id_tienda=cita_disponible.tienda AND cita.id_taller=cita_disponible.taller AND cita.fecha_cita=cita_disponible.fecha),0) AS cant_agendadas
		FROM cita_disponible
		WHERE fecha>=CURDATE()
		$filtros
		HAVING cant_agendadas<cantidad";
//ORDER BY cita_disponible.id desc

	$resultado = $conn->query($query) ;
	

	if ($resultado!=false){
        if ($resultado -> num_rows > 0) { 
			while ($row = $resultado -> fetch_assoc()) {
				//$disponible=$row["cantidad"]-$row["cant_agendadas"];
				array_push($salida, $row["fecha"] );
			}
		}
	}

	$stud_arr[0]["pcode"] = 1;
    $stud_arr[0]["pmsg"] ='';
	$stud_arr[0]["parr"]=$salida; 

    salida_json($stud_arr);
    exit;

} 


//calendario Horas disponibles
if ($accion=="cal2") { 

	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ='Se produjo un error, favor vuelva a intentar';
	$salida = array();
	$row_array = array();
	$sucursal ="0";
	$taller ="0";
	$filtros ="";
	$id_plantilla_horario=0;


	if (isset($_REQUEST['s'])) { $sucursal = intval($_REQUEST['s']); } 
	if (isset($_REQUEST['t'])) { $taller = intval($_REQUEST['t']); } 
	if (isset($_REQUEST['f'])) { $fecha = ($_REQUEST['f']); } 

    $id_plantilla_horario=get_dato_sql("tienda","id_plantilla_horario"," WHERE id=$sucursal");

	if (!es_nulo($sucursal)){$filtros.=" and cita.id_tienda = ".$sucursal ;}
	if (!es_nulo($taller)){$filtros.=" and cita.id_taller = ".$taller ;}
	
	if (!es_nulo($fecha)){$filtros.=" and cita.fecha_cita = '".$fecha."'" ;}

	$max_por_hora=get_dato_sql("cita_disponible","cantidad_por_hora"," WHERE tienda=$sucursal AND taller=$taller AND fecha='$fecha' ");
	if (es_nulo($max_por_hora)) {
		$having="";
	} else {
		$having="HAVING citas_agendadas<".$max_por_hora;
	}

	$query="SELECT cita_horario.id, cita_horario.nombre, cita_horario.hora
	,(SELECT COUNT(*) FROM cita 
	WHERE cita.id_estado<>20 and id_usuario=0 
	$filtros
	AND cita.hora_cita=cita_horario.id) AS citas_agendadas
	
		FROM cita_horario where cita_horario.id_plantilla=$id_plantilla_horario
		$having
		
		ORDER BY cita_horario.hora";


	$resultado = $conn->query($query) ;
	

	if ($resultado!=false){
        if ($resultado -> num_rows > 0) { 
			while ($row = $resultado -> fetch_assoc()) {
				//$disponible=$row["cantidad"]-$row["cant_agendadas"];
				$row_array['id'] = ($row['id']);
				$row_array['nombre'] = ($row['nombre']);
				$row_array['hora'] = ($row['hora']);		
	
				array_push($salida,$row_array);
			}
		}
	}

	$stud_arr[0]["pcode"] = 1;
    $stud_arr[0]["pmsg"] ='';
	$stud_arr[0]["parr"]=$salida; 

    salida_json($stud_arr);
    exit;

} 

} 
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

		let d_tienda = [];
		let d_taller = [];
		let d_horas = [];

	d_tienda = [
		<?php echo get_array_tiendas(); ?>
	];

	d_taller = [
		<?php echo get_array_talleres(); ?>
	];





	</script>
  </head>
  <body>


  <form class="form-1" id="formsol" name="formsol">



  
<div id="form-1div" class="card">
  <div class="card-body form-1-card-body">	
  	 <div class="text-center mb-4 form-1-titulo" style="background-color: #ffffff;">
    	<img src="img/logo.png" alt="" class="   mb-3 mt-2"  width="200" >
    	<hr>
  	 </div>

	   <div class="text-center mb-4 form-1-titulo">AGENDAR CITA PARA SERVICIO</div>
  
	   <div id="mensaje-cuerpo" class="form-1-body">
  		</div>

	  <div id="form-cuerpo" class="form-1-body">
		  <p class="subtitulo">Datos del vehículo</p>
		<!-- <p class="subtitulo">Agende su cita<br> 
			1. Colocar inventario de vehículo <br>
			2. Seleccione tienda (SPS ó TGU) <br>
			3. Seleccione dia y hora según disponibilidad  <br>
		</p> -->
		 	


	   <div class="row">

	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="num_inv">Numero de Inventario</label>
					<input type="text" id="num_inv" name="num_inv" class="form-control" placeholder="" autocomplete="off" required >
					
				</div>
		   </div>		    
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="placa">Numero de Placa</label>
					<input type="text" id="placa" name="placa" class="form-control" placeholder="" autocomplete="off" required >
					
				</div>
		   </div>
		   
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="chasis">Numero de VIN</label>
					<input type="text" id="chasis" name="chasis" class="form-control" placeholder="Ingrese los 6 ultimos numeros" autocomplete="off" required >
				</div>
		   </div>

	   </div>

	   <div class="row">
	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="sucursal">Sucursal</label>					
					<select id="sucursal" name="sucursal"  class="form-control" required>
						<option value="">Seleccione...</option>
					</select>
				</div>
		   </div>
		   <div class="col-sm-1 mb-3">
		   </div>   
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="taller">Taller</label>					
					<select id="taller" name="taller" class="form-control" required>
						<option value="">Seleccione...</option>
					</select>
				</div>
		   </div>
	   </div>

	   <div class="row">
		   
	   	   <div class="col-sm mb-3">
			  <label for="fecha">Fecha</label>
			  <input type="hidden" id="fecha" name="fecha" value="" >
					
			  <div class="calendar"></div>
			  
		   </div>
		   <div class="col-sm-1 mb-3">
		   </div>   
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="hora">Hora</label>					
					<select id="hora" name="hora" class="form-control" required>
						<option value="">Seleccione...</option>
					</select>

					
				</div>
		   </div>
	   </div>

	  <p>&nbsp;</p>
	   <p class="subtitulo">Datos de la Cita y Contacto</p>

	   <div class="row">
	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="tipo">Tipo de Revisión</label>					
					<select id="tipo" name="tipo" class="form-control" required>
						<option value="">Seleccione...</option>	
						<option value="1">Preventivo</option>
						<option value="2">Correctivo</option>
					</select>	
				</div>
		   </div>
		   <div class="col-sm-1 mb-3">
		   </div>   
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="kilometraje">Kilometraje del Vehículo</label>
					<input type="number" id="kilometraje" name="kilometraje" class="form-control" placeholder="" autocomplete="off" required >
					
				</div>
		   </div>
	   </div>



		<div class="row">
	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="nombre">Nombre del Contacto</label>
					<input type="text" id="nombre" name="nombre" class="form-control" placeholder="" autocomplete="off" required >
					
				</div>
		   </div>
		   <div class="col-sm-1 mb-3">
		   </div>   
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="identidad">No. Identidad</label>
					<input type="number" id="identidad" name="identidad" class="form-control" placeholder="" autocomplete="off" required >
					
				</div>
		   </div>
	   </div>




	   <div class="row">
	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="telefono">Teléfono Celular</label>
					<input type="number" id="telefono" name="telefono" class="form-control" placeholder="" autocomplete="off" required >
					
				</div>
		   </div>
		   <div class="col-sm-1 mb-3">
		   </div>   
		   <div class="col-sm mb-3">
		   		<div class="form-label-group">
				   <label for="correo">Correo electrónico</label>
					<input type="email" id="correo" name="correo" class="form-control" placeholder="" autocomplete="off" min="10000000" max="99999999" required >
					
				</div>
		   </div>
	   </div>

	   <div class="row">
	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="empresa">Nombre de Empresa</label>
					<input type="text" id="empresa" name="empresa" class="form-control" placeholder="" autocomplete="off"  >
					
				</div>
		   </div>
		   <div class="col-sm-1 mb-3">
		   </div>  
		   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="ciudad">Ciudad de Procedencia</label>
					<input type="text" id="ciudad" name="ciudad" class="form-control" placeholder="" autocomplete="off"  >
					
				</div>
		   </div>
	 
		  
	   </div>


	   <div class="row">
	   	   <div class="col-sm mb-3">
			  	<div class="form-label-group">
				  <label for="comentario">Reporte por detalles de unidad</label>
					<textarea id="comentario" name="comentario" class="form-control" autocomplete="off" rows="4"></textarea>					
				</div>
		   </div>

		  
	   </div>




	  
	   <input type="hidden" id="ffmes" value="<?php echo date("m"); ?>" >
	   <input type="hidden" id="ffanio" value="<?php echo date("Y"); ?>" >
					   
	  <p class="text-center mt-4"> <a href="#" id="form-1-btn" class="btn btn-primary btn-lg form-btn" onclick="enviar_solicitud();  return false;"> Agendar Cita </a></p>


	   </div>

  



<footer class=""> </footer>
 </div> 
</div> 


<p class="mt-5 mb-3 text-muted text-center">&copy; INGLOSA <?php echo date('Y'); ?></p>

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

	cargar_sucursales();


</script>

<script type="module">
			let d_diasno = [];

            import HelloWeek from './js/helloweek/scripts/hello.week.min.js';
            const calendar = new HelloWeek({
                
				selector: '.calendar',				
                langFolder: './langs/',
				lang: 'es',
				format: 'YYYY-MM-DD',
				disablePastDays: true,
                //disabledDaysOfWeek: [0, 6], 
				
				

				
				onLoad: () => {
					
					// $('#ffmes').val(calendar.getMonth());
					// $('#ffanio').val(calendar.getYear());
				},
				
				onSelect: () => {										
					asignar_fecha(calendar);

				},
				
				 onNavigation: () => {
					
	
					$('#ffmes').val(calendar.getMonth());
					$('#ffanio').val(calendar.getYear());
					cargar_calendario(calendar,false);
				},
				
            });

			document.querySelector('#sucursal').addEventListener('change', () => {
				var $talleres = $('#taller');
				var sucursal_actual = $('#sucursal').val();
				
				// Limpiar taller y calendario
				$talleres.empty().append('<option value="">Seleccione...</option>');
				$('#fecha').val('');
				$('#hora').val('');
				
				// Cargar calendario solo si hay sucursal seleccionada
				if (sucursal_actual !== "") {
					cargar_calendario(calendar, true);
				}
				
				// Cargar talleres correspondientes
				for (i in d_taller) {
					if (sucursal_actual == d_taller[i][2]) {
						$talleres.append('<option value="' + d_taller[i][0] + '">' + d_taller[i][1] + '</option>');
					}
				}
			});

			document.querySelector('#taller').addEventListener('change', () => {
				cargar_calendario(calendar,true);
            });

	

			function cargar_calendario(calendar,resetear){
				
				d_diasno = [];
				$('#fecha').val('');
				$('#hora').val('');
				var s_actual= $('#sucursal').val();
				var t_actual= $('#taller').val();
				var m_actual= parseInt($('#ffmes').val());
				var y_actual= parseInt($('#ffanio').val());
				// var m_actual= calendar.getMonth();
				// var y_actual= calendar.getYear();
				cargando(true);
				$.post( "index.php?a=cal"+"&t="+t_actual+"&s="+s_actual+"&m="+m_actual+"&y="+y_actual, function(json) {
							
					if (json.length > 0) {
						if (json[0].pcode == 0) {
							cargando(false);
							mytoast('error',json[0].pmsg,6000) ;
						}
						if (json[0].pcode == 1) {
							

								cargando(false);
								debugger;
								var fecha_actual;
								var totdias=daysInMonth(m_actual,y_actual);
								for (let i = 1; i <= totdias; i++) {
									fecha_actual=y_actual+"-"+padWithZero(m_actual,  2)+"-"+padWithZero(i,  2);
									
									if (Array.isArray(json[0].parr)) {
										

										if (json[0].parr.indexOf(fecha_actual)<0) {
											d_diasno.push(fecha_actual);
										}
									
	
								} else { d_diasno.push(fecha_actual);}
																	
								}

								

						}

						var fechadefault=y_actual+"-"+padWithZero(m_actual,  2)+"-02";
						//	var fechadefault=padWithZero(m_actual,  2)+"-02"+"-"+y_actual;
						if (resetear==true) {
							calendar.reset({
								defaultDate: fechadefault,				
							//disabledDaysOfWeek: [6], 
							disableDates: d_diasno,
						});
						} else {							
							calendar.reset({
								defaultDate: fechadefault,
													//disabledDaysOfWeek: [6], 
													disableDates: d_diasno,
												});
												
							// calendar.goToDate(fechadefault);
						}

					} else {
					cargando(false);
					mytoast('error','Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar',6000) ;
				}
					
				})
				.done(function() {
					// cargando(false);
				})
				.fail(function(xhr, status, error) {
					cargando(false);					
					mytoast('error','Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar',6000) ;
					})
				.always(function() {					
				});


				
			
				
			};



			


			function asignar_fecha(calendar){
				var lafecha=calendar.getDays();
				$('#fecha').val(lafecha[0]);

				
				//horas
				var $lahora = $('#hora').empty();
				//$lahora.append('<option value = "">Seleccione...</option>');
				var f_actual= $('#fecha').val();				
				var s_actual= parseInt($('#sucursal').val());
				var t_actual= parseInt($('#taller').val());


				if (s_actual>0 && t_actual>0) {
					
				
				cargando(true);
				$.post( "index.php?a=cal2"+"&t="+t_actual+"&s="+s_actual+"&f="+f_actual+"", function(json) {
							
					if (json.length > 0) {
						if (json[0].pcode == 0) {
							cargando(false);
							mytoast('error',json[0].pmsg,6000) ;
						}
						if (json[0].pcode == 1) {
							

								cargando(false);


								if (Array.isArray(json[0].parr)) {

									$.each(json[0].parr, function(i, doc) {								
										$lahora.append('<option value = "' + doc.id + '">' + doc.nombre + '</option>');
									});

			

								} else {   }


						}

						

					} else {
					cargando(false);
					mytoast('error','Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar',6000) ;
				}
					
				})
				.done(function() {
					// cargando(false);
				})
				.fail(function(xhr, status, error) {
					cargando(false);					
					mytoast('error','Se produjo un error. favor verifique que tenga acceso a Internet y vuelva a intentar',6000) ;
					})
				.always(function() {					
				});

			}


			}
			
        </script>
	

</body>
</html>
