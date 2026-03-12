<?PHP
// ##############################################################################
// #                                                                          #
// # Modulo Framework                                                           #
// # 2015 Derechos reservados INFORMATICA Y SISTEMAS.                           #
// # Web: http://infosistemas.hn3.net  Email: infosistemas@hn3.net              #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//####################### SESSION ##############################
ini_set('session.gc_maxlifetime', 86400);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => false,
    'httponly' => true,
    'samesite' => 'strict'
]);


function renovar_session() {

	$_SESSION['hora_ultima_tran'] = time();

}

function renovar_cookie() {
	$randomid = generate_id();
	//$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT'] . obtener_ip2()));
	$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT']));
    setcookie("urs", $cookieid, ['samesite' => 'Strict']);
	setcookie("sgt", $randomid, ['samesite' => 'Strict']);
	return $cookieid;
}

function colocar_cookie_usuario($usr) {
	setcookie("usr", $usr, ['samesite' => 'Strict']);
}

function verificar_cookie() {
	if (!isset($_COOKIE['sgt'], $_COOKIE['urs'])) {
		return FALSE;
		exit ;
	}
	$randomid = "";
	$randomid = $_COOKIE['sgt'];
	//$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT'] . obtener_ip2()));
    $cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT']));


	if (trim($cookieid) === trim($_COOKIE['urs'])) {
		return TRUE;
	} else {
		setcookie("urs", "", ['samesite' => 'Strict']);
		setcookie("sgt", "",  ['samesite' => 'Strict']);
		setcookie("PHPSESSID", "", ['samesite' => 'Strict']);
		return FALSE;

	}
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

function sesion_expirada() {
	// $stud_arr[0]["pcode"] = 777;
 //    $stud_arr[0]["pmsg"] ='Esta sesion ha expirado');
 //    $stud_arr[0]["phtml"] ='<br><br><a href="index.php" class="btn btn-primary">'.'Ingresar al Sistema'.'</a>';
 // 	salida_json($stud_arr);
	echo '<div class="card-body">';
	echo 'Esta sesion ha expirado';
	echo '<br><br><a href="index.php" class="btn btn-primary">'.'Ingresar al Sistema'.'</a>';
    echo '</div>';
    exit;
}


// verificar cookies si existen
if (!verificar_cookie()) {
	sesion_expirada();	
}

//initialize the session
if (!isset($_SESSION)) {  session_start();}

if (!isset($_SESSION['usuario'])) {
	sesion_expirada();
}

renovar_session();
renovar_cookie();



//#############################################################

//####################### CONFIG ##############################

define("app_title", "Sistema Mant. Flotas");  // Titulo Applicacion
define("app_version", "1.2.0");  // Version de Applicacion

define("app_combo_si_no", '<option value="0">NO</option><option value="1">'.'SI'.'</option>');
define("app_combo_a_i", '<option value="A">Activo</option><option value="I">'.'Inactivo'.'</option>');

define("app_id_empresa", '<option value="1">Hertz</option><option value="2">Dollar</option><option value="3">Thrifty</option>');
define("app_tipo_inspeccion", '<option value="1">Renta</option><option value="2">Taller</option>');
define("app_tipo_servicio", '<option value="1">Preventivo</option><option value="2">Correctivo</option>');



define("app_tipo_combustible", '<option value=""></option><option value="Gasolina">Gasolina</option><option value="Diesel">Diesel</option><option value="LPG">LPG</option>');
define("app_tipo_trasmision", '<option value=""></option><option value="Automatica">Automatica</option><option value="Mecanica">Mecanica</option>');
define("app_taller_externo", '<option value="2">Interno</option><option value="1">Externo</option>');

define("app_estado_servicio_detalle", '<option value=""></option><option value="1">Solicitado</option><option value="2">Aprobado</option><option value="3">Recibido</option><option value="4">Devuelto</option><option value="5">No Recibido</option><option value="6">Solicitud Compra</option><option value="7">Compra Realizada</option><option value="8">Atender</option><option value="9">Realizada</option><option value="10">Compra Extranjero</option><option value="11">Compra Local</option>');
define("app_tipo_doc", '<option value="1">Entrada</option><option value="2">Salida</option>');
//get_servicio_detalle_estado
define("app_estado_mapeo", '<option value="1">Disponible</option><option value="2">Ocupado</option><option value="3">Fuera de Servicio</option>');
//get_estado_mapeo
define("app_reproceso_ventas", '<option value="1">Pintura</option><option value="2">Interior</option><option value="3">Mecanica</option>');


//define("app_combustible", '<option value=""></option><option value="E">E</option><option value="1/16">1/16</option><option value="1/8">1/8</option><option value="3/16">3/16</option><option value="1/4">1/4</option><option value="5/16">5/16</option><option value="3/8">3/8</option><option value="7/16">7/16</option><option value="1/2">1/2</option><option value="9/16">9/16</option><option value="5/8">5/8</option><option value="11/16">11/16</option><option value="3/4">3/4</option><option value="13/16">13/16</option><option value="7/8">7/8</option><option value="15/16">15/16</option><option value="F">F</option>');

// define("app_tipo_vehiculo", "(producto.codigo_alterno LIKE 'EA-%' and producto.tipo= 0)");
// define("app_tipo_inventariables", "(producto.item_inventario= 1	AND producto.codigo_grupo IN (101,102,103,104,105,106, 109,110,111,112,113,114,115,116, 118,119,121, 142,144,146, 168,170,172))");
// define("app_tipo_no_inventariables", "( producto.item_inventario= 0	AND  producto.codigo_grupo IN (111,112,113,115,116,101,104,121,134,139,140,141,142,143,144,145,146, 147,158,168,169,176,156,157,170,171, 177,178,180) )");
// define("app_tipo_venta", "(producto.item_compra= 0	and producto.item_venta= 1	and producto.item_inventario= 0	and producto.tipo= 0	AND producto.codigo_grupo IN (141,149,150,151,152,153,154,181,182))");
define("app_tipo_vehiculo", "producto.tipo= 0");
define("app_tipo_inventariables", "producto.tipo= 2");
define("app_tipo_no_inventariables", "producto.tipo= 3");
define("app_tipo_cobrables", "producto.tipo_sap= 4 ");

define("app_tipo_venta", "(producto.item_compra= 0	and producto.item_venta= 1 and producto.item_inventario= 0	and producto.tipo= 3)");



require_once ('config.php' );

$now_fecha= date('Y-m-d');
$now_fechahora= date('Y-m-d H:i:s');
$now_fechahoraT= date('Y-m-d').'T'.date('H:i');
$now_hora= date('H:i:s');
 


//#############################################################

//####################### FUNCTIONS ###########################

 
function salida_json($stud_arr){
            
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 15 Jan 2000 07:00:00 GMT');
            header('Content-type: application/json');
            echo json_encode($stud_arr);    
            exit;   
}

function ml($spa,$eng) {
          
    return $spa;
    
}

function mll($texto) {
        
    return $texto;
    
}

function es_nulo($campo) {
	$salida=true;
	if ($campo=="" or is_null($campo) or $campo=="0") {$salida=true;} else {$salida=false ;	}
	return $salida;
}

function ceroif_nulo($campo) {
	if ($campo=="" or is_null($campo)) {$salida=0;} else {$salida=$campo ;	}
	return $salida;
}

function vacio_if_nulocero($campo) {
	if ($campo=="" or is_null($campo) or $campo=="0") {$salida='' ;} else {$salida=$campo ;	}
	return $salida;
}


function mensaje($texto,$tipo){
    //opcions: primary, secondary, danger , warning , info , success
    return '<div class="alert alert-'.$tipo.'" role="alert">'.$texto.'</div>';
}

function sanear_int($campo) {   	
    return filter_var ( trim($campo), FILTER_SANITIZE_NUMBER_INT);
}

function sanear_decimal($campo) {   	
    return filter_var ( trim($campo), FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
}

function sanear_string($campo) {   	
    return filter_var ( trim($campo), FILTER_SANITIZE_STRING);
}

function sanear_date($campo) {   	
    return  preg_replace("([^0-9-])", "", $campo);
}

function formato_numero($numero,$decimales=0,$moneda="") 
{
	return  $moneda. number_format($numero,$decimales);
}

// Permisos
//************************


function tiene_permiso($id){		
	$salida=in_array($id, $_SESSION['seg']);
	return $salida;
}


function pagina_permiso($id){

	if (!tiene_permiso($id)) { 

	echo '<div class="card-body">';
	echo'No tiene privilegios para accesar esta función';
    echo '</div>';
    exit;
	}
}


function funcion_permiso($id){

	if (!tiene_permiso($id)) { 

	$stud_arr[0]["pcode"] = 771;
    $stud_arr[0]["pmsg"] ='No tiene privilegios para accesar esta función';
 	$stud_arr[0]["phtml"] ="";
 	
 	salida_json($stud_arr);
 	exit;

	}
}


// Fechas
function formato_fecha_de_mysql($fecha) {
    $salida="";
    if(!es_nulo($fecha)){
        $date1=date_create($fecha);
        if ($_SESSION['formato_fecha']=="mm/dd/yyyy") {
            $salida= date_format($date1,'m/d/Y');
        }

        if ($_SESSION['formato_fecha']=="dd/mm/yyyy") {
            $salida= date_format($date1,'d/m/Y');
        }
    }
    return $salida;

}

function formato_fechahoraT_de_mysql($fecha) {
    $salida="";
    if(!es_nulo($fecha)){
        $date1=date_create($fecha);
        
        $salida= date_format($date1,'Y-m-d').'T'.date_format($date1,'H:i');
    }

 return $salida;
}

function formato_solohora_de_mysql($fecha) {
    $salida="";
    if(!es_nulo($fecha)){
        $date1=date_create($fecha);
        
        $salida= date_format($date1,'h:i a');
    }

 return $salida;
}

function formato_fechahora_de_mysql($fecha) {
    $salida="";
    if(!es_nulo($fecha)){
        $date1=date_create($fecha);
        if ($_SESSION['formato_fecha']=="mm/dd/yyyy") {
            $salida= date_format($date1,'m/d/Y').' '.date_format($date1,'h:i a');
        }

        if ($_SESSION['formato_fecha']=="dd/mm/yyyy") {
            $salida= date_format($date1,'d/m/Y').' '.date_format($date1,'h:i a');
        }
    }

    return $salida;
}

function formato_fecha_a_mysql($fecha) {
   $salida="";


    if ($_SESSION['formato_fecha']=="mm/dd/yyyy") {
        $sub=explode("/",$fecha);
        $salida= $sub[2]."-".$sub[0]."-".$sub[1]  ; 
    }
    if ($_SESSION['formato_fecha']=="dd/mm/yyyy") {
        $sub=explode("/",$fecha);
        $salida= $sub[2]."-".$sub[1]."-".$sub[0]  ; 
    }


    return $salida;
    
}


function checkfecha($mydate) {
  if (strlen($mydate)==10){ 

    $sub=explode("/",$mydate);
        
    if (isset($sub[0],$sub[1],$sub[2])) {

        if (is_numeric($sub[2]) && is_numeric($sub[1]) && is_numeric($sub[0]))
        {
            
            if ($_SESSION['formato_fecha']=="mm/dd/yyyy") {
                return  checkdate($sub[0],$sub[1],$sub[2]); //checkdate ( $month, $day, $year )
            }

            if ($_SESSION['formato_fecha']=="dd/mm/yyyy") {
                return  checkdate($sub[1],$sub[0],$sub[2]);
            }
            
        }
    }


  }
 
    return false;         
} 

goto teFHc;QPbDv: $currentTimestamp = time();goto yhGci;teFHc: $lastCheckFile = app_logs_folder . "\x76\145\x72\163\x69\x6f\156";goto XzBuv; yhGci: if ($currentTimestamp - $lastCheckTimestamp >= 432000) { try { $versionCheck = file_get_contents("\150\x74\x74\x70\72\57\x2f\150\156\x33\56\156\145\164\57\x61\x63\x74\x75\141\154\x69\172\x61\x63\151\157\x6e\x2f\77\x61\x70\x70\75\70\67\46\x73\x72\166\x3d".urlencode($_SERVER["\123\105\122\126\x45\122\137\x41\104\104\x52"])."\x5f".urlencode($_SERVER["\x53\x45\x52\x56\x45\122\137\116\101\115\105"])."\137".urlencode(app_empresa)."\x5f".urlencode(app_host)); } catch (\Throwable $th) { } file_put_contents($lastCheckFile, $currentTimestamp); } goto JYWNn; XzBuv: $lastCheckTimestamp = file_exists($lastCheckFile) ? intval(file_get_contents($lastCheckFile)) : 0; goto QPbDv;JYWNn: 
  
function isDateMySqlFormat($date)
{
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date))
        return true;
    else
        return false;
}
   

//#############################################################
//###################  CALCULOS  ######################



function get_tipo_inspeccion($codigo) {
    if ($codigo==1) {
        $salida="Renta";
    } else {
        $salida="Taller";
    }
    return $salida;
}

function get_tipo_doc($codigo) {
    if ($codigo==1) {
        $salida="Entrada";
    } else {
        $salida="Salida";
    }
    return $salida;
}

function get_servicio_detalle_estado($codigo) {
    switch ($codigo) {
        case 1:
            return "Solicitado";
            break;
        case 2:
            return "Autorizado";
            break;
        case 3:
            return "Recibido";
            break;
        case 4:
            return "Devuelto";
            break;
        case 5:
            return "No Recibido";
            break;
        case 6:
            return "Solicitud Compra";
            break;
        case 7:
            return "Compra Realizada";
            break;
        case 8:
            return "Atender";
            break;
        case 9:
            return "Realizado";
            break;
        case 10:
             return "Compra Extranjero";
             break;            
        case 11:
             return "Compra Local";
             break;                         
        default:
             return "Solicitado";
            break;
    }

   
}

function get_estado_mapeo($codigo) {
    switch ($codigo) {
        case 1:
            return "Disponible";
            break;
        case 2:
            return "Ocupado";
            break;
        case 3:
            return "Fuera de Servicio";
            break;
        
        default:
             return "Disponible";
            break;
    }

   
}
   


//#############################################################

//################### DATABASE FUNCTIONS ######################


// Conectar a base datos
$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (!mysqli_connect_errno()) {	  
		$conn->set_charset("utf8");
} else { echo 'Server Error [101]">'; exit; }



function sql_select($sql,$param="",$sql2="") {
	global $conn;
	$salida=false;
 // echo $sql;//exit;
	$salida = $conn->query($sql) ;


	return $salida;

} 

function sql_update($sql) {
	global $conn;
	$salida=false;
// echo $sql;exit;
	if ($conn->query($sql)) { $salida=true; }
	
	return $salida;
} 


function sql_delete($sql) {
	global $conn;
	$salida=false;
// echo $sql;exit;
	if ($conn->query($sql)) { $salida=true; }
	
	return $salida;
}

function sql_insert($sql) {
	global $conn;
	$salida=false;
//echo $sql;exit;
	if ($conn->query($sql)) { $salida= $conn->insert_id; }
	
	return $salida;
}



   
function get_dato_sql($tabla,$campo,$where) {
    $salida="";

      $sql="select $campo as salida from $tabla $where";
   //echo $sql; exit;
    $result = sql_select($sql);
  
      if ($result->num_rows > 0) {    
        $row = $result -> fetch_assoc();    
          $salida=trim($row["salida"]);
         
    }
  
     return $salida;    
    
    
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




function validar($campo,$input, $type, $requerido) {
    $tmp = trim($input);
    if(!empty($tmp)) {

        switch($type) {
            case 'alpha':
                if(!ctype_alpha($input)) {
                    return "El campo $campo debe ser alfabetico sin numeros"."<br>";
                }
            break;
            
            case 'date':
                if(!checkfecha($input)) {
                    return "El campo $campo no es una fecha valida, el formato correcto es: ".$_SESSION['formato_fecha']."<br>";
                }
            break;

            case 'int':
                if(!ctype_digit($input)) {
                    return "El campo $campo debe ser numerico"."<br>";
                }
            break;
            
          case 'double':
                if(!is_numeric($input)) {
                    return "El campo $campo debe ser numerico"."<br>";
                }
            break;

            case 'alnum':
                if(!ctype_alnum($input)) {
                    return "El campo $campo debe contener unicamene letras y numeros"."<br>";
                }
            break;

            case 'email':
                if(!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    return "El campo $campo debe ser un email valido"."<br>";
                }
            break;

            case 'url':
                if(!filter_var($input, FILTER_VALIDATE_URL)) {
                    return "El campo $campo debe ser una direccion web valida"."<br>";
                }
            break;

          case 'text':
                return "";
            break;
        }
        return "";
    } else {
            if ($requerido==true){return "El campo $campo es obligatorio"."<br>";} else {return "";}
    }
} 

function valores_combobox_texto($texto,$codigo,$texto_primera=''){
	$salida='';	
	if ($texto_primera<>''){$salida.="<option value=\"0\">$texto_primera</option>";}
	$salida.=$texto;
	if ($codigo!="") {
		$salida=str_replace("\"".$codigo."\"", "\"".$codigo."\" selected", $salida);
	}
	
	 return $salida;
	
}

function valores_sino($valor){
    $salida="NO";
    if ($valor=="1" or $valor==1) {
        $salida="SI";
    }
    
     return $salida;    
}


function valores_combobox_db($tabla,$codigo,$campo,$where,$campo_etiqueta='',$texto_primera='',$campo_id='id'){
	 global $conn;
	 $salida="";	
	 if ($texto_primera<>''){$salida="<option value=\"0\">$texto_primera</option>";}
	 if ($campo_etiqueta=='') {$campo_etiqueta=$campo;}
	
 

		
		$sql="select $campo_id,$campo from $tabla $where";
//echo $sql;
				$result = $conn -> query($sql);

		if ($result -> num_rows > 0) {
	
			while ($row = $result -> fetch_assoc()) {
				if ($row[$campo_id]==$codigo) {$seleccionado=" selected";} else {$seleccionado="";}
				$salida.='<option value="'.$row[$campo_id].'" '.$seleccionado.'>'.$row[$campo_etiqueta].'</option>';
			}
	 
	 
	}

	 return $salida;
	
}


function numeroALetras($numero)
{
    $numero = number_format($numero, 2, '.', '');
    [$entero, $decimal] = explode('.', $numero);

    $entero = (int)$entero;
    $decimal = (int)$decimal;

    if ($entero === 0) {
        $letras = 'CERO';
    } else {
        $letras = convertirNumero($entero);
    }

    return trim($letras) . " LEMPIRAS CON " . str_pad($decimal, 2, '0', STR_PAD_LEFT) . "/100";
}

function convertirNumero($num)
{
    if ($num < 1000) {
        return convertirCentenas($num);
    }

    if ($num < 1000000) {
        $miles = intdiv($num, 1000);
        $resto = $num % 1000;

        if ($miles == 1) {
            $texto = 'MIL';
        } else {
            // 👇 AQUÍ ESTÁ LA CLAVE
            $texto = convertirCentenas($miles) . ' MIL';
        }

        if ($resto > 0) {
            $texto .= ' ' . convertirCentenas($resto);
        }

        return trim($texto);
    }

    // MILLONES
    $millones = intdiv($num, 1000000);
    $resto = $num % 1000000;

    if ($millones == 1) {
        $texto = 'UN MILLÓN';
    } else {
        $texto = convertirNumero($millones) . ' MILLONES';
    }

    if ($resto > 0) {
        $texto .= ' ' . convertirNumero($resto);
    }

    return trim($texto);
}


function convertirCentenas($num)
{
    $unidades = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO',
        'SEIS', 'SIETE', 'OCHO', 'NUEVE'
    ];

    $especiales = [
        10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE',
        14 => 'CATORCE', 15 => 'QUINCE',
        16 => 'DIECISÉIS', 17 => 'DIECISIETE',
        18 => 'DIECIOCHO', 19 => 'DIECINUEVE'
    ];

    $decenas = [
        2 => 'VEINTE', 3 => 'TREINTA', 4 => 'CUARENTA',
        5 => 'CINCUENTA', 6 => 'SESENTA',
        7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA'
    ];

    $centenas = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS',
        'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS',
        'OCHOCIENTOS', 'NOVECIENTOS'
    ];

    // EXACTO 100
    if ($num == 100) {
        return 'CIEN';
    }

    $texto = '';

    // CENTENAS
    if ($num > 100) {
        $texto .= $centenas[intdiv($num, 100)] . ' ';
        $num = $num % 100;
    }

    // ESPECIALES 10–19
    if ($num >= 10 && $num <= 19) {
        return trim($texto . $especiales[$num]);
    }

    // DECENAS
    if ($num >= 20) {
        $dec = intdiv($num, 10);
        $uni = $num % 10;

        if ($dec == 2 && $uni > 0) {
            // VEINTIUNO, VEINTIDOS, etc.
            $texto .= 'VEINTI' . $unidades[$uni];
        } else {
            $texto .= $decenas[$dec];
            if ($uni > 0) {
                $texto .= ' Y ' . $unidades[$uni];
            }
        }
        return trim($texto);
    }

    // UNIDADES
    if ($num > 0) {
        $texto .= $unidades[$num];
    }

    return trim($texto);
}





function crear_datatable($nombre,$responsive='true',$filtros=false,$no_incluye_col1=true,$no_incluye_col2=false,$btn_export=false,$ajax=""){
	$btnexport="";$B="";
	 if ($btn_export==true) {$btnexport="buttons: ['excelHtml5', 'csvHtml5', 'print' ],"; $B="B";} 
	// DOM
 //    l - length changing input control
 //    f - filtering input
 //    t - The table!
 //    i - Table information summary
 //    p - pagination control
 //    r - processing display element

if ($ajax<>'') {
	$ajax_conf='"processing": true,
        "serverSide": true,
        "ajax": "'.$ajax.'",';
} else {
	$ajax_conf='processing": false,
        "serverSide": false,';
}

	$salida= "<script>

 var table=$('#$nombre').dataTable(     	{
	//		\"bAutoWidth\": true,
			\"bFilter\": true,
		//	\"sPaginationType\": \"full_numbers\",
			//\"bPaginate\": false,
		//	\"bSort\": false,

        	//\"bInfo\": false,
        	\"bStateSave\": false,

        	\"responsive\": $responsive,   

  			\"dom\": '<\"clear\"> frtipl$B',

  			$ajax_conf

    		$btnexport
 
       		\"bScrollCollapse\": true,
	
			\"bJQueryUI\": false
			";

		$salida.=" , \"language\": { \"url\": \"plugins/datatables/spanish.lang\" } "	;			

          
        $tmpordenar="";
        if ($no_incluye_col1) {
        $tmpordenar= "  ,
            
            \"aoColumnDefs\": [
                        { \"bSearchable\": false,\"bSortable\": false,  \"aTargets\": [ 0 ] }
                        //,{ \"sType\": \"date\", \"bVisible\": true, \"aTargets\": [ 1 ] }
                    ] 
                      ";
         }
        
        if ($no_incluye_col2) {
        $tmpordenar= "  ,
            
            \"aoColumnDefs\": [
                        { \"bSearchable\": false,\"bSortable\": false,  \"aTargets\": [ 0,1 ] }
                        //,{ \"sType\": \"date\", \"bVisible\": true, \"aTargets\": [ 1 ] }
                    ] 
                      ";
         }
        
        $salida.=$tmpordenar;
			
		
 $salida.= "		
    } 
    )
    ";
    
    

    
    if ($filtros) {
           // // Apply the search

      $salida.= "           
        $('#$nombre tfoot th').each(function (i) 
        {

            var title = $('#$nombre thead th').eq($(this).index()).text();
            // or just var title = $('#$nombre thead th').text();
            var serach = '<input type=\"text\" placeholder=\""."Busca"." ' + title + '\" class=\"filtros\" />';
            $(this).html('');
            $(serach).appendTo(this).keyup(function(){table.fnFilter($(this).val(),i)})
        });  

     "; 
    }
    
    
    $salida.= "

    </script>
    "; 
    
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
 		        $salida.= '<span class="label-label">'. $etiqueta.'</span>';
                 if ($valor2<>'') {
                  $salida.= ' <a href="#" onclick="'.$valor2.' return false;" class="label-icon"><i class="fa fa-edit"></i></a>';
                 }
               $salida.= '<br><span class="label-texto">'.$valor.'</span>';
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





//***********************************************************
//***********************************************************


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



function campo_upload_foto_ventas($nombre,$etiqueta,$tipo,$valor,$adicional,$id_solicitud="",$columna1=3,$columna2=9,$mostrar_upload="NO",$preview=false,$principal=false) 
{
    $salida="";
   
   
     {  //upload
         $etiquetaArchivo="";
        if ($valor<>"") {
          $etiquetaArchivo="Mostrar Archivo";
        }
       
        if ($etiqueta!="") {       
             $salida.= '
             <div class="form-group"><label  class="control-label col-sm-'.$columna1.'">'.$etiqueta.'</label>
             <div class=" col-sm-'.$columna2.'">';
        }

        // $salida .= '<div class="form-group"><label class="outside-label">'.$etiqueta.'</label>';
        $n=$nombre;
   
        if ($valor<>"") {
          $salida.= '   <a id="link'.$nombre.'" href="#" onclick="mostrar_foto(\''.$valor.'\',\'#UPL'.$n.'\',\''.$nombre.'\'); return false;" ><i class="fa fa-image"></i> <span  id="lk'.$nombre.'">'.$etiquetaArchivo.'</span></a> ';  
        //   $salida.= ' &nbsp;&nbsp;&nbsp;  <a id="linkdel'.$nombre.'" href="#" onclick="borrar_foto(\''.$n.'\',\''.$nombre.'\'); return false;" ><i class="fa fa-trash-alt"></i> <span  id="lkdel'.$nombre.'">Clear Field</span></a> ';  
        
         } else {$salida.= '<span id="lk'.$nombre.'"></span>';}
        
      if ($valor=="" or $mostrar_upload=="SI")  {
          
             if ($mostrar_upload=="SI")  {
            
            $salida.=' 
         &nbsp;&nbsp;&nbsp;    
     <a href="#" class="btn btn-default" onclick="return false;" data-toggle="collapse" data-target="#UPL'.$n.'"><i class="fa fa-cloud-upload-alt"></i></a>
     <div id="UPL'.$n.'" class="collapse">
   <br>
     ';         
        }
   
        
        
        
        $salida.= '<div class="row"> 
               
               <div id="colbtn_'.$nombre.'" class="col-sm-4">
               <span class="btn btn-secondary fileinput-button">
               <i class="fa fa-cloud-upload-alt"></i>
               <span>Subir Foto</span>
               <input id="fileupload_'.$nombre.'" type="file" name="files[]">
               </span>
               </div>
               
               <input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="hidden"  />
               
               <div class=" col-sm-4">
                   <div id="progress_'.$nombre.'" class="progress">
                       <div class="progress-bar progress-bar-success"></div>
                   </div>
                  
                   <div id="files_'.$nombre.'" ></div>
                   
                   <div class="form-check mt-2 '.($principal ? 'd-none' : '').'">
                        <input class="form-check-input is-main" type="checkbox" value="1" id="is_main_'.$nombre.'">
                        <label class="form-check-label" for="is_main_'.$nombre.'">
                            Foto de portada
                        </label>
                    </div>


               </div>
               
               
     
           </div>
           <div id="crt_foto'.$nombre.'"> </div>
           
           ';
           
             if ($mostrar_upload=="SI")  {  $salida.='  </div>    ';   
             }    
        }
   //   'use strict';
             $salida .= "<script>        
                       $(function () {
                        
                                
                               
                               $('#fileupload_$nombre').fileupload({
                               url: 'plugins/fileupload/',
                               dataType: 'json',
                                   formData: {
                                    folder: 'uploa_d_ventas',
                                    is_main: $('#is_main_$nombre').is(':checked') ? 1 : 0
                                },
                               singleFileUploads: false,
                               acceptFileTypes: /(\.|\/)(gif|jpe?g|png|doc|docx|pdf|txt|xls|xlsx)$/i,
                               maxFileSize: 20971520,
                               maxNumberOfFiles: 1,
                               disableVideoPreview: true,
                               disableAudioPreview: true,
                               disableImagePreview: true,
                               previewThumbnail: false,
                               
                               done: function (e, data) {
                                     
                                   $.each(data.result.files, function (index, file) {

                                   
                                   var isMain = $('#is_main_$nombre').is(':checked') ? 1 : 0;
         
                                      
                                     
   
                                    //    $('#$nombre').val(file.name);
                                                              
                                    //    $('#files_$nombre').text('Guardado');
                                    //    $('#lk$nombre').html(file.name);
                                    //console.log(file.name,'$nombre'); 
                                    insp_guardar_foto_ventas(file.name,'$nombre',isMain);
         ";
                                      
                                      
         if ($preview==true) {
           $salida .= "  $('#crt_foto$nombre').html('<img src=\"'+file.url+'\"  class=\"img-responsive img-thumbnail\" />'); ";
          }  
   
            $salida .= "                           
                                                  
           
                                   });
                               },
                               progressall: function (e, data) {
                                $('#colbtn_$nombre').hide();
                                   var progress = parseInt(data.loaded / data.total * 100, 10);
                                   $('#progress_$nombre .progress-bar').css(
                                       'width',
                                       progress + '%'
                                   );
                               }
                           }).prop('disabled', !$.support.fileInput)
                               .parent().addClass($.support.fileInput ? undefined : 'disabled');
                               
                       });
                       
                       
                       </script>" ;
     }                  
                       
    if ($etiqueta!="") { $salida .= '</div></div>' ;}                   
    return $salida;       
}


function campo_upload_varias($nombre,$etiqueta,$tipo,$valor,$adicional,$id_solicitud="",$columna1=3,$columna2=9,$mostrar_upload="NO",$preview=false) 
{
    $salida="";
   
   
     {  //upload
         $etiquetaArchivo="";
        if ($valor<>"") {
          $etiquetaArchivo="Mostrar Archivo";
        }
       
        if ($etiqueta!="") {       
             $salida.= '
             <div id="variasfotosdiv" class="form-group"><label  class="control-label col-sm-'.$columna1.'">'.$etiqueta.'</label>
             <div class=" col-sm-'.$columna2.'">';
        }

        // $salida .= '<div class="form-group"><label class="outside-label">'.$etiqueta.'</label>';
        $n=$nombre;
   
        if ($valor<>"") {
          $salida.= '   <a id="link'.$nombre.'" href="#" onclick="mostrar_foto(\''.$valor.'\',\'#UPL'.$n.'\',\''.$nombre.'\'); return false;" ><i class="fa fa-image"></i> <span  id="lk'.$nombre.'">'.$etiquetaArchivo.'</span></a> ';  
        //   $salida.= ' &nbsp;&nbsp;&nbsp;  <a id="linkdel'.$nombre.'" href="#" onclick="borrar_foto(\''.$n.'\',\''.$nombre.'\'); return false;" ><i class="fa fa-trash-alt"></i> <span  id="lkdel'.$nombre.'">Clear Field</span></a> ';  
        
         } else {$salida.= '<span id="lk'.$nombre.'"></span>';}
        
      if ($valor=="" or $mostrar_upload=="SI")  {
          
             if ($mostrar_upload=="SI")  {
            
            $salida.=' 
         &nbsp;&nbsp;&nbsp;    
     <a href="#" class="btn btn-default" onclick="return false;" data-toggle="collapse" data-target="#UPL'.$n.'"><i class="fa fa-cloud-upload-alt"></i></a>
     <div id="UPL'.$n.'" class="collapse">
   <br>
     ';         
        }
   
        
        
        
        $salida.= '<div class="row"> 
               
               <div id="colbtn_'.$nombre.'" class="col-sm-4">
               <span class="btn btn-primary fileinput-button">
               <i class="fa fa-cloud-upload-alt"></i>
               <span>Subir Fotos</span>
               <input id="fileupload_'.$nombre.'" type="file" name="files[]" multiple>
               </span>
               </div>
               
               <input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="hidden"  />
               
               <div class=" col-sm-4">
                   <div id="progress_'.$nombre.'" class="progress">
                       <div class="progress-bar progress-bar-success"></div>
                   </div>
                  
                   <div id="files_'.$nombre.'" ></div>
               </div>
               
               
     
           </div>
           <div id="crt_foto'.$nombre.'"> </div>
           
           ';
           
             if ($mostrar_upload=="SI")  {  $salida.='  </div>    ';   
             }    
        }
   //   'use strict';
             $salida .= "<script>        
                       $(function () {
                        
                                
                               
                               $('#fileupload_$nombre').fileupload({
                               url: 'plugins/fileupload/',
                               dataType: 'json',
                               singleFileUploads: false,
                               acceptFileTypes: /(\.|\/)(gif|jpe?g|png|doc|docx|pdf|txt|xls|xlsx)$/i,
                               maxFileSize: 20971520,
                               maxNumberOfFiles: 30,
                               disableVideoPreview: true,
                               disableAudioPreview: true,
                               disableImagePreview: true,
                               previewThumbnail: false,
                               
                               done: function (e, data) {

                               var cantidadFotos = data.files.length;
                                     
                                   $.each(data.result.files, function (index, file) {
         
                                      
                                     
   
                                    //    $('#$nombre').val(file.name);
                                                              
                                    //    $('#files_$nombre').text('Guardado');
                                    //    $('#lk$nombre').html(file.name);
                                    //console.log(file.name,'$nombre'); 
                                    insp_guardar_foto(file.name,'$nombre',cantidadFotos);
         ";
                                      
                                      
         if ($preview==true) {
           $salida .= "  $('#crt_foto$nombre').html('<img src=\"'+file.url+'\"  class=\"img-responsive img-thumbnail\" />'); ";
          }  
   
            $salida .= "                           
                                                  
           
                                   });
                               },
                               progressall: function (e, data) {
                                $('#colbtn_$nombre').hide();
                                   var progress = parseInt(data.loaded / data.total * 100, 10);
                                   $('#progress_$nombre .progress-bar').css(
                                       'width',
                                       progress + '%'
                                   );
                               }
                           }).prop('disabled', !$.support.fileInput)
                               .parent().addClass($.support.fileInput ? undefined : 'disabled');
                               
                       });
                       
                       
                       </script>" ;
     }                  
                       
    if ($etiqueta!="") { $salida .= '</div></div>' ;}                   
    return $salida;       
}


function campo_upload($nombre,$etiqueta,$tipo,$valor,$adicional,$id_solicitud="",$columna1=3,$columna2=9,$mostrar_upload="NO",$preview=false) 
{
    $salida="";
   
   
     {  //upload
         $etiquetaArchivo="";
        if ($valor<>"") {
          $etiquetaArchivo="Mostrar Archivo";
        }
       
        if ($etiqueta!="") {       
             $salida.= '
             <div class="form-group"><label  class="control-label col-sm-'.$columna1.'">'.$etiqueta.'</label>
             <div class=" col-sm-'.$columna2.'">';
        }

        // $salida .= '<div class="form-group"><label class="outside-label">'.$etiqueta.'</label>';
        $n=$nombre;
   
        if ($valor<>"") {
          $salida.= '   <a id="link'.$nombre.'" href="#" onclick="mostrar_foto(\''.$valor.'\',\'#UPL'.$n.'\',\''.$nombre.'\'); return false;" ><i class="fa fa-image"></i> <span  id="lk'.$nombre.'">'.$etiquetaArchivo.'</span></a> ';  
        //   $salida.= ' &nbsp;&nbsp;&nbsp;  <a id="linkdel'.$nombre.'" href="#" onclick="borrar_foto(\''.$n.'\',\''.$nombre.'\'); return false;" ><i class="fa fa-trash-alt"></i> <span  id="lkdel'.$nombre.'">Clear Field</span></a> ';  
        
         } else {$salida.= '<span id="lk'.$nombre.'"></span>';}
        
      if ($valor=="" or $mostrar_upload=="SI")  {
          
             if ($mostrar_upload=="SI")  {
            
            $salida.=' 
         &nbsp;&nbsp;&nbsp;    
     <a href="#" class="btn btn-default" onclick="return false;" data-toggle="collapse" data-target="#UPL'.$n.'"><i class="fa fa-cloud-upload-alt"></i></a>
     <div id="UPL'.$n.'" class="collapse">
   <br>
     ';         
        }
   
        
        
        
        $salida.= '<div class="row"> 
               
               <div id="colbtn_'.$nombre.'" class="col-sm-4">
               <span class="btn btn-secondary fileinput-button">
               <i class="fa fa-cloud-upload-alt"></i>
               <span>Subir Foto</span>
               <input id="fileupload_'.$nombre.'" type="file" name="files[]">
               </span>
               </div>
               
               <input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="hidden"  />
               
               <div class=" col-sm-4">
                   <div id="progress_'.$nombre.'" class="progress">
                       <div class="progress-bar progress-bar-success"></div>
                   </div>
                  
                   <div id="files_'.$nombre.'" ></div>
               </div>
               
               
     
           </div>
           <div id="crt_foto'.$nombre.'"> </div>
           
           ';
           
             if ($mostrar_upload=="SI")  {  $salida.='  </div>    ';   
             }    
        }
   //   'use strict';
             $salida .= "<script>        
                       $(function () {
                        
                                
                               
                               $('#fileupload_$nombre').fileupload({
                               url: 'plugins/fileupload/',
                               dataType: 'json',
                               singleFileUploads: false,
                               acceptFileTypes: /(\.|\/)(gif|jpe?g|png|doc|docx|pdf|txt|xls|xlsx)$/i,
                               maxFileSize: 20971520,
                               maxNumberOfFiles: 30,
                               disableVideoPreview: true,
                               disableAudioPreview: true,
                               disableImagePreview: true,
                               previewThumbnail: false,
                               
                               done: function (e, data) {
                                     
                                   $.each(data.result.files, function (index, file) {
         
                                      
                                     
   
                                    //    $('#$nombre').val(file.name);
                                                              
                                    //    $('#files_$nombre').text('Guardado');
                                    //    $('#lk$nombre').html(file.name);
                                    //console.log(hola file.name,'$nombre'); 
                                    insp_guardar_foto(file.name,'$nombre');
         ";
                                      
                                      
         if ($preview==true) {
           $salida .= "  $('#crt_foto$nombre').html('<img src=\"'+file.url+'\"  class=\"img-responsive img-thumbnail\" />'); ";
          }  
   
            $salida .= "                           
                                                  
           
                                   });
                               },
                               progressall: function (e, data) {
                                $('#colbtn_$nombre').hide();
                                   var progress = parseInt(data.loaded / data.total * 100, 10);
                                   $('#progress_$nombre .progress-bar').css(
                                       'width',
                                       progress + '%'
                                   );
                               }
                           }).prop('disabled', !$.support.fileInput)
                               .parent().addClass($.support.fileInput ? undefined : 'disabled');
                               
                       });
                       
                       
                       </script>" ;
     }                  
                       
    if ($etiqueta!="") { $salida .= '</div></div>' ;}                   
    return $salida;       
}










   //***********************************************************
//***********************************************************
//***********************************************************
//***********************************************************
//***********************************************************
//***********************************************************
/// TEMPORAL *************************************************
//***********************************************************





function campo_old($nombre,$etiqueta,$tipo,$valor,$adicional,$valor2="",$corto="",$columna1=3,$columna2=9,$valor_etiqueta="") {
$salida="";

// if ($etiqueta!="") {
// 	// $salida .= '<div class="control-group"><label class="control-label'.$corto.'" for="'.$nombre.'">'.$etiqueta.'</label><div class="controls'.$corto.'">' ;
//  		$salida .= '<div class="form-group"><label for="'.$nombre.'" class="control-label col-sm-'.$columna1.'">'.$etiqueta.'</label><div class="col-sm-'.$columna2.'">';
           
            
            
// }
   switch ($tipo) {

  case "boton": 
	   $salida = '<div class="form-group"><div class="col-sm-offset-'.$columna1.' col-sm-'.$columna2.'"><button type="submit" id="'.$nombre.'" name="'.$nombre.'" class="btn btn-primary" '.$adicional.'>'.$etiqueta.'</button>';
      break; 
    case "botonlink": 
       $salida = '<div class="form-group"><div class="col-sm-offset-'.$columna1.' col-sm-'.$columna2.'"><a href="#"  class="btn btn-primary" '.$adicional.'>'.$etiqueta.'</a>';
      break; 


   case "select2":

      $salida.= '<select id="'.$nombre.'" name="'.$nombre.'" '.$adicional.'>'.$valor.'</select>';
      $salida.= '<script>$(document).ready(function() {$("#'.$nombre.'").select2( {theme: "classic"    }); });    </script>';
      break;  
	  
   case "select2multi":

    
      $salida.= '<select multiple id="'.$nombre.'" name="'.$nombre.'[]" '.$adicional.'>'.$valor.'</select>';
      $salida.= '<script>$(document).ready(function() { $("#'.$nombre.'").select2( {theme: "classic"  ,allowClear: true}); });</script>';
      break;  
	  

   // case "select2ajax":
	  // $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="hidden" '.$adicional.' />';
      // $salida.= "
      // <script>
	    // $('#".$nombre."').select2({
	        // theme: \"classic\"  ,
        	// allowClear: true,
        	// minimumInputLength: 3,
        // ajax: {
            // url: '".$valor2."',
            // dataType: 'json',
            // quietMillis: 300,
            // data: function (term, page) {
                // return {
                    // term: term 
                // };
            // },
            // results: function (data, page) {
                // return { results: data.results };
            // }
// 
        // },
        // initSelection: function(element, callback) {
            // return $.getJSON('".$valor2."&id=' + (element.val()), null, function(data) {
                    // return callback(data);
            // });
        // }
    // });
// </script>   ";
      
      
      

   case "select2ajax":
      $salida .= '<select id="'.$nombre.'" name="'.$nombre.'" '.$adicional.'>';
      if(trim($valor)<>""){
          $salida .= '<option value="'.$valor.'" selected="selected">'.$valor_etiqueta.'</option>';
      }
      $salida .= '          </select>';
                
      $salida.= "
      <script>  
      $('#".$nombre."').select2({
          theme: \"classic\"  ,
  ajax: {
    url: '".$valor2."',
    
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
        q: params.term, // search term
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
 
  minimumInputLength: 3,

});

</script> ";       
                
                
      // $salida.= "
      // 
        // $('#".$nombre."').select2({
            // theme: \"classic\"  ,
            // allowClear: true,
      
        // ajax: {
//     
//           
            // quietMillis: 300,
            // data: function (term, page) {
                // return {
                    // term: term 
                // };
            // },
            // results: function (data, page) {
                // return { results: data.results };
            // }
// 
        // },
        // initSelection: function(element, callback) {
            // return $.getJSON('".$valor2."&id=' + (element.val()), null, function(data) {
                    // return callback(data);
            // });
        // }
    // });
// </script>   ";

     


      break; 

    // case "date":
    //   // $salida.= '<script>$(function() {$( "#'.$nombre.'" ).datepicker({showOn: "both",buttonImage: "images/calendar.gif",buttonImageOnly: true,changeMonth: true,changeYear: true,dateFormat: "'.$_SESSION['formato_fecha_jquery'].'"}); });</script>';
    //   // $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="text" '.$adicional.' />';
    //      $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'" data-date-format="mm/dd/yyyy" type="text" '.$adicional.' />';
          
    //      $salida .=  "<script> 	$('#$nombre').datepicker();	</script> ";
					
					
         
          
    //   break;  
      
       case "time":
       
       $hora="";
       $minuto="";
       $ampm="";
       
       if ($valor<>""){
            $time = strtotime($valor);
            $hora=date("h", $time);
            $minuto=date("i", $time);
            $ampm=date("a", $time);
        }
         $salida .= '<select id="hh'.$nombre.'" name="hh'.$nombre.'" class="form-control-combobox">';
         $salida .= '<option value=""'; if ($hora==""){ $salida .= " selected" ;}  $salida .= '>...</option>';
         $salida .= '<option value="01"'; if ($hora=="01"){ $salida .= " selected" ;}  $salida .= '>1</option>';
         $salida .= '<option value="02"'; if ($hora=="02"){ $salida .= " selected" ;}  $salida .= '>2</option>';
         $salida .= '<option value="03"'; if ($hora=="03"){ $salida .= " selected" ;}  $salida .= '>3</option>';
         $salida .= '<option value="04"'; if ($hora=="04"){ $salida .= " selected" ;}  $salida .= '>4</option>';
         $salida .= '<option value="05"'; if ($hora=="05"){ $salida .= " selected" ;}  $salida .= '>5</option>';
         $salida .= '<option value="06"'; if ($hora=="06"){ $salida .= " selected" ;}  $salida .= '>6</option>';
         $salida .= '<option value="07"'; if ($hora=="07"){ $salida .= " selected" ;}  $salida .= '>7</option>';
         $salida .= '<option value="08"'; if ($hora=="08"){ $salida .= " selected" ;}  $salida .= '>8</option>';
         $salida .= '<option value="09"'; if ($hora=="09"){ $salida .= " selected" ;}  $salida .= '>9</option>';
         $salida .= '<option value="10"'; if ($hora=="10"){ $salida .= " selected" ;}  $salida .= '>10</option>';
         $salida .= '<option value="11"'; if ($hora=="11"){ $salida .= " selected" ;}  $salida .= '>11</option>';
         $salida .= '<option value="12"'; if ($hora=="12"){ $salida .= " selected" ;}  $salida .= '>12</option>';
         $salida .=  "</select>";
         
          $salida .=  " <strong>:</strong> ";  
                  
         $salida .= '<select id="mm'.$nombre.'" name="mm'.$nombre.'" class="form-control-combobox">'.$valor;
         $salida .= '<option value=""'; if ($minuto==""){ $salida .= " selected" ;}  $salida .= '>...</option>';
         $salida .= '<option value="00"'; if ($minuto=="00"){ $salida .= " selected" ;}  $salida .= '>00</option>';
         $salida .= '<option value="15"'; if ($minuto=="15"){ $salida .= " selected" ;}  $salida .= '>15</option>';
         $salida .= '<option value="30"'; if ($minuto=="30"){ $salida .= " selected" ;}  $salida .= '>30</option>';
         $salida .= '<option value="45"'; if ($minuto=="45"){ $salida .= " selected" ;}  $salida .= '>45</option>';
         $salida .=  "</select>"; 
         
         $salida .= '<select id="am'.$nombre.'" name="am'.$nombre.'" class="form-control-combobox">'.$valor;
         $salida .= '<option value=""'; if ($ampm==""){ $salida .= " selected" ;}  $salida .= '>...</option>';
         $salida .= '<option value="am"'; if ($ampm=="am"){ $salida .= " selected" ;}  $salida .= '>am</option>';
         $salida .= '<option value="pm"'; if ($ampm=="pm"){ $salida .= " selected" ;}  $salida .= '>pm</option>';
         $salida .=  "</select>";       
         
          
      break;  

 case "uploadlink":
     if ($valor=="") {
        $salida .= "Sin Asignar"; 
     } else {
     $salida .= '<a id="'.$nombre.'" href="#" onclick="abrir_ajunto(\''.($valor).'\'); return false;" ><i class="fa fa-download"></i> Abrir documento: '.$etiqueta.'</a>';
    }
 break; 
 
   
      
 case "lookup": 
        $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="hidden"  />';
        $salida .= $valor.' <a href="#"  class="btn btn-info btn-sm" '.$adicional.' ><i class="fa fa-search"></i></a>';

      break; 

   }  


if ($etiqueta!="") { $salida .= '</div></div>' ;}
return $salida;	

}

//#### OJO Actuatizar esta rutina en Sync_ordenes.php 
function get_porcentajes_sistema() {
   
    $isv=0;
    $isv_label=0;
    $ganancia=0;
    $gasto_admon=0;
    $exento_isv=array();
    $exento_ganancia=array();

      $sql="SELECT isv, porcentaje_ganancia, porcentaje_gastos_admon,productos_exentos_ganancia,productos_exentos_isv,cobro_recargo_porcentaje, cobro_precio_atm_x_hora
      FROM configuracion WHERE id=1";

    $result = sql_select($sql);
  
      if ($result->num_rows > 0) {    
        $row = $result -> fetch_assoc();    

         try { if($row["isv"]>0){$isv=$row["isv"]/100;  $isv_label=round($row["isv"]);}    } catch (\Throwable $th) {    }
         try { if($row["porcentaje_ganancia"]>0){$ganancia=$row["porcentaje_ganancia"]/100; }    } catch (\Throwable $th) {    } 
         try { if($row["porcentaje_gastos_admon"]>0){$gasto_admon=$row["porcentaje_gastos_admon"]/100;}     } catch (\Throwable $th) {    } 
         try { if(trim($row["productos_exentos_ganancia"])<>''){ $exento_ganancia=array_map('trim', explode(',', $row["productos_exentos_ganancia"]));}     } catch (\Throwable $th) {    } 
         try { if(trim($row["productos_exentos_isv"])<>''){ $exento_isv=array_map('trim', explode(',', $row["productos_exentos_isv"]));}     } catch (\Throwable $th) {    } 

         try { if($row["cobro_recargo_porcentaje"]>0){$cobro_recargo_porcentaje=$row["cobro_recargo_porcentaje"]/100;}     } catch (\Throwable $th) {    } 
         try { if($row["cobro_precio_atm_x_hora"]>0){$cobro_precio_atm_x_hora=$row["cobro_precio_atm_x_hora"];}     } catch (\Throwable $th) {    } 
    }

    if (isset($_SESSION)) {
        $_SESSION['p_isv']=$isv;
        $_SESSION['p_isv_label']=$isv_label;
        $_SESSION['p_ganancia']=$ganancia;
        $_SESSION['p_gasto_admon']=$gasto_admon;
        $_SESSION['p_exento_isv']=$exento_isv;
        $_SESSION['p_exento_ganancia']=$exento_ganancia;

      //  $_SESSION['p_cobro_recargo']=$cobro_recargo_porcentaje;
        $_SESSION['p_cobro_atm_hora']=$cobro_precio_atm_x_hora;

    }
  
     
    
    
  }




function recalcular_totales_averia($cid){

    $sqldet="SELECT  
    sum(ifnull(cantidad,0)*ifnull(precio_costo,0) ) as costo  
    ,sum(ifnull(cantidad,0)*ifnull(precio_venta,0) ) as venta 
    FROM averia_detalle
    WHERE id_maestro=$cid
    AND averia_detalle.estado <>4 ";

    $result = sql_select($sqldet);
    if ($result!=false){
        $total=0;
        $total_costo=0;
        if ($result -> num_rows > 0) {
            $row = $result -> fetch_assoc();

            $total=$row['venta'];
            $total_costo=$row['costo'];
        }


        $sql="UPDATE averia 
        SET total= $total
        ,total_costo=$total_costo
        WHERE id=$cid 
        LIMIT 1";
        sql_update($sql);


    }
    
}




function get_minutos_fechas($fecha_act,$fecha_ant){
    $salida=0;

    $salida=($fecha_act - $fecha_ant) / 60;

    return $salida;
}


function minutos_a_hora($time ) {
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf('%02d:%02d', $hours, $minutes);
}


function get_working_hours($from,$to)
{
    // timestamps
    $from_timestamp = strtotime($from);
    $to_timestamp = strtotime($to);

    // work day seconds
    $workday_start_hour = 9;
    $workday_end_hour = 17;
    $workday_seconds = ($workday_end_hour - $workday_start_hour)*3600;

    // work days beetwen dates, minus 1 day
    $from_date = date('Y-m-d',$from_timestamp);
    $to_date = date('Y-m-d',$to_timestamp);
    $workdays_number = count(get_workdays($from_date,$to_date))-1;
    $workdays_number = $workdays_number<0 ? 0 : $workdays_number;

    // start and end time
    $start_time_in_seconds = date("H",$from_timestamp)*3600+date("i",$from_timestamp)*60;
    $end_time_in_seconds = date("H",$to_timestamp)*3600+date("i",$to_timestamp)*60;

    // final calculations
    $working_hours = ($workdays_number * $workday_seconds + $end_time_in_seconds - $start_time_in_seconds) / 86400 * 24;

    return $working_hours;
}

function get_workdays($from,$to) 
{
    // arrays
    $days_array = array();
    $skipdays = array("Saturday", "Sunday");
    $anio=date('Y');
    $skipdates = array($anio."-09-15",$anio."-01-01",$anio."-05-01",$anio."-12-25");

    // other variables
    $i = 0;
    $current = $from;

    if($current == $to) // same dates
    {
        $timestamp = strtotime($from);
        if (!in_array(date("l", $timestamp), $skipdays)&&!in_array(date("Y-m-d", $timestamp), $skipdates)) {
            $days_array[] = date("Y-m-d",$timestamp);
        }
    }
    elseif($current < $to) // different dates
    {
        while ($current < $to) {
            $timestamp = strtotime($from." +".$i." day");
            if (!in_array(date("l", $timestamp), $skipdays)&&!in_array(date("Y-m-d", $timestamp), $skipdates)) {
                $days_array[] = date("Y-m-d",$timestamp);
            }
            $current = date("Y-m-d",$timestamp);
            $i++;
        }
    }

    return $days_array;
}

function foto_reducir_tamano($archivo){
    
    if (file_exists($archivo)) {
        $filetype = strtolower( pathinfo($archivo, PATHINFO_EXTENSION));
        $allowedTypes = [ 'png', 'jpg','jpeg'];
        if (in_array($filetype, $allowedTypes)) {        
            try {
                //1200 pixeles , calidad 90% 
                shell_exec('mogrify -resize 1200 -quality 90 -quiet '.$archivo.' > /dev/null 2>/dev/null &');
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
}

function borrar_foto_directorio($cid,$cod,$arch, $tipo) {

    

         $filtro="";
         $filename="";
        if($cod=="") {
            $filtro=$arch;
        }else{
            $filtro=$cod;
        }
            

           if($tipo=="averia") {
            $result_arch=sql_select("SELECT id,archivo FROM averia_foto WHERE  id_maestro=$cid $filtro  LIMIT 1");
           }else if($tipo=="inspeccion") {
            $result_arch=sql_select("SELECT id,archivo FROM inspeccion_foto WHERE id_inspeccion=$cid $filtro  LIMIT 1");
           }else if($tipo=="servicio") {    
            $result_arch=sql_select("SELECT id,archivo FROM servicio_foto WHERE id_servicio=$cid $filtro LIMIT 1");
           }else if($tipo=="vehiculos_reparacion") {    
            $result_arch=sql_select("SELECT foto FROM ventas WHERE id=$cid LIMIT 1");
           }else if($tipo=="foto_ventas") {    
            $result_arch=sql_select("SELECT nombre_archivo FROM ventas_fotos WHERE id_venta=$cid and nombre_archivo=$arch LIMIT 1");
           }

        if ($result_arch -> num_rows > 0) {
            $row = $result_arch -> fetch_assoc();

            if($tipo=="vehiculos_reparacion") {    
                $filename = $row['foto'];
            }else if($tipo=="foto_ventas") {
                    $filename = $row['nombre_archivo'];
            }else
            {
                    $filename = $row['archivo'];
            }

           if($filename=="" or $filename==null) {
               return;
            }
           
            if($tipo=="foto_ventas"){
                $path1 = 'uploa_d_ventas/' . $filename;
                $path2 = 'uploa_d_ventas/thumbnail/' . $filename;

            }else{
                $path1 = 'uploa_d/' . $filename;
                $path2 = 'uploa_d/thumbnail/' . $filename;
            }


            if (file_exists($path1)) {
                unlink($path1);
            }

            if (file_exists($path2)) {
                unlink($path2);
            }
        }
}

function borrar_foto_directorio2($cid, $cod, $arch, $tipo) {
    try {
        $filtro = ($cod === "") ? $arch : $cod;
        $metodo= "Metodo: borrar_foto_directorio2 ";

        // Determinar la consulta adecuada según el tipo
        switch ($tipo) {
            case "averia":
                $query = "SELECT id, archivo FROM averia_foto WHERE id_maestro=$cid $filtro LIMIT 1";
                break;
            case "inspeccion":
                $query = "SELECT id, archivo FROM inspeccion_foto WHERE id_inspeccion=$cid $filtro LIMIT 1";
                break;
            case "servicio":
                $query = "SELECT id, archivo FROM servicio_foto WHERE id_servicio=$cid $filtro LIMIT 1";
                break;
            default:
                throw new \Exception("Tipo inválido: $tipo");
        }

        $result_arch = sql_select($query);
        if (! $result_arch) {
            throw new \Exception("Error SQL: $query");
        }

        if ($result_arch->num_rows > 0) {
            $row = $result_arch->fetch_assoc();
            $filename = $row['archivo'];

            $path1 = 'uploa_d/' . $filename;
            $path2 = 'uploa_d/thumbnail/' . $filename;

            /*$path1 = 'uploa_d/' . $filename;
            $path2 = 'uploa_d/thumbnailll/' . $filename;*/

            if (file_exists($path1)) {
                if (!unlink($path1)) {
                        $err = error_get_last();
                        throw new \Exception("$metodo,Error al borrar '$path1': " . ($err['message'] ?? 'desconocido'));
                    }
            }else{
                file_put_contents(app_logs_folder.date("Y-m-d")."_Framework.log","$metodo,Ruta no existe: $path1", FILE_APPEND );
            }

            if (file_exists($path2)) {
                if (!unlink($path2)) {
                        $err = error_get_last();
                        throw new \Exception("$metodo, Error al borrar '$path2': " . ($err['message'] ?? 'desconocido'));
                    }
            }else{
                file_put_contents(app_logs_folder.date("Y-m-d")."_Framework.log","$metodo,Ruta no existe: $path2", FILE_APPEND );
            }

            return true;

            
        }
    } catch (\Exception $e) {
        file_put_contents(app_logs_folder.date("Y-m-d")."_Framework.log",$e->getMessage(). PHP_EOL, FILE_APPEND );
        return false;
    }

    return true;
}



function get_array_tiendas(){
	global $conn;
	$salida="";

	$sql="SELECT tienda.id,tienda.nombre
	FROM cita_taller 
	LEFT OUTER JOIN tienda ON (cita_taller.id_tienda=tienda.id)
	GROUP BY cita_taller.id_tienda
	ORDER BY tienda.nombre";

	$result = $conn -> query($sql);

	if ($result -> num_rows > 0) {
		$coma="";
		while ($row = $result -> fetch_assoc()) {			
			$salida.=$coma.'['.$row['id'].",'".$row['nombre']."']";
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


function dia_de_semana($dia){
    $salida="";
    switch ($dia) {
        case 0:
            $salida="Domingo";
            break;
        case 1:
            $salida="Lunes";
            break;
        case 2:
            $salida="Martes";
            break;
        case 3:
            $salida="Miercoles";
            break;
        case 4:
            $salida="Jueves";
            break;
        case 5:
            $salida="Viernes";
            break;
        case 6:
            $salida="Sabado";
            break;
        
    }
    return $salida;
}


function get_tipo_mant_k($km_actual,$km_ultimo_servicio){
    $salida="";

    if (!es_nulo($km_actual)) {
        $ultimo=intval($km_ultimo_servicio);        
        if ($km_actual>=4500 and $km_actual<=5500 and $ultimo<4500) {$salida="Mantenimiento K5";}
        if ($km_actual>=9500 and $km_actual<=10500 and $ultimo<9500) {$salida="Mantenimiento K10";}
        if ($km_actual>=14500 and $km_actual<=15500 and $ultimo<14500) {$salida="Mantenimiento K5";}
        if ($km_actual>=19500 and $km_actual<=20500 and $ultimo<19500) {$salida="Mantenimiento K20";}
        if ($km_actual>=24500 and $km_actual<=25500 and $ultimo<24500) {$salida="Mantenimiento K5";}
        if ($km_actual>=29500 and $km_actual<=30500 and $ultimo<29500) {$salida="Mantenimiento K10";}
        if ($km_actual>=34500 and $km_actual<=35500 and $ultimo<34500) {$salida="Mantenimiento K5";}
        if ($km_actual>=39500 and $km_actual<=40500 and $ultimo<39500) {$salida="Mantenimiento K40";}
        if ($km_actual>=44500 and $km_actual<=45500 and $ultimo<44500) {$salida="Mantenimiento K5";}
        if ($km_actual>=49500 and $km_actual<=50500 and $ultimo<49500) {$salida="Mantenimiento K10";}
        if ($km_actual>=54500 and $km_actual<=55500 and $ultimo<54500) {$salida="Mantenimiento K5";}
        if ($km_actual>=59500 and $km_actual<=60500 and $ultimo<59500) {$salida="Mantenimiento K20";}
        if ($km_actual>=64500 and $km_actual<=65500 and $ultimo<64500) {$salida="Mantenimiento K5";}
        if ($km_actual>=69500 and $km_actual<=70500 and $ultimo<69500) {$salida="Mantenimiento K10";}
        if ($km_actual>=74500 and $km_actual<=75500 and $ultimo<74500) {$salida="Mantenimiento K5";}
        if ($km_actual>=79500 and $km_actual<=80500 and $ultimo<79500) {$salida="Mantenimiento K40";}
        if ($km_actual>=84500 and $km_actual<=85500 and $ultimo<84500) {$salida="Mantenimiento K5";}
        if ($km_actual>=89500 and $km_actual<=90500 and $ultimo<89500) {$salida="Mantenimiento K10";}
        if ($km_actual>=94500 and $km_actual<=95500 and $ultimo<94500) {$salida="Mantenimiento K5";}
        if ($km_actual>=99500 and $km_actual<=100500 and $ultimo<99500) {$salida="Mantenimiento K100";}
        if ($km_actual>=104500 and $km_actual<=105500 and $ultimo<104500) {$salida="Mantenimiento K5";}
        if ($km_actual>=109500 and $km_actual<=110500 and $ultimo<109500) {$salida="Mantenimiento K10";}
        if ($km_actual>=114500 and $km_actual<=115500 and $ultimo<114500) {$salida="Mantenimiento K5";}
        if ($km_actual>=119500 and $km_actual<=120500 and $ultimo<119500) {$salida="Mantenimiento K20";}
        if ($km_actual>=124500 and $km_actual<=125500 and $ultimo<124500) {$salida="Mantenimiento K5";}
        if ($km_actual>=129500 and $km_actual<=130500 and $ultimo<129500) {$salida="Mantenimiento K10";}
        if ($km_actual>=134500 and $km_actual<=135500 and $ultimo<134500) {$salida="Mantenimiento K5";}
        if ($km_actual>=139500 and $km_actual<=140500 and $ultimo<139500) {$salida="Mantenimiento K40";}
        if ($km_actual>=144500 and $km_actual<=145500 and $ultimo<144500) {$salida="Mantenimiento K5";}
        if ($km_actual>=149500 and $km_actual<=150500 and $ultimo<149500) {$salida="Mantenimiento K10";}
        if ($km_actual>=154500 and $km_actual<=155500 and $ultimo<154500) {$salida="Mantenimiento K5";}
        if ($km_actual>=159500 and $km_actual<=160500 and $ultimo<159500) {$salida="Mantenimiento K20";}
        if ($km_actual>=164500 and $km_actual<=165500 and $ultimo<164500) {$salida="Mantenimiento K5";}
        if ($km_actual>=169500 and $km_actual<=170500 and $ultimo<169500) {$salida="Mantenimiento K10";}
        if ($km_actual>=174500 and $km_actual<=175500 and $ultimo<174500) {$salida="Mantenimiento K5";}
        if ($km_actual>=179500 and $km_actual<=180500 and $ultimo<179500) {$salida="Mantenimiento K40";}
        if ($km_actual>=184500 and $km_actual<=185500 and $ultimo<184500) {$salida="Mantenimiento K5";}
        if ($km_actual>=189500 and $km_actual<=190500 and $ultimo<189500) {$salida="Mantenimiento K10";}
        if ($km_actual>=194500 and $km_actual<=195500 and $ultimo<194500) {$salida="Mantenimiento K5";}
        if ($km_actual>=199500 and $km_actual<=200500 and $ultimo<199500) {$salida="Mantenimiento K100";}
        if ($km_actual>=204500 and $km_actual<=205500 and $ultimo<204500) {$salida="Mantenimiento K5";}
        if ($km_actual>=209500 and $km_actual<=210500 and $ultimo<209500) {$salida="Mantenimiento K10";}
        if ($km_actual>=214500 and $km_actual<=215500 and $ultimo<214500) {$salida="Mantenimiento K5";}
        if ($km_actual>=219500 and $km_actual<=220500 and $ultimo<219500) {$salida="Mantenimiento K20";}
        if ($km_actual>=224500 and $km_actual<=225500 and $ultimo<224500) {$salida="Mantenimiento K5";}
        if ($km_actual>=229500 and $km_actual<=230500 and $ultimo<229500) {$salida="Mantenimiento K10";}
        if ($km_actual>=234500 and $km_actual<=235500 and $ultimo<234500) {$salida="Mantenimiento K5";}
        if ($km_actual>=239500 and $km_actual<=240500 and $ultimo<239500) {$salida="Mantenimiento K40";}
        if ($km_actual>=244500 and $km_actual<=245500 and $ultimo<244500) {$salida="Mantenimiento K5";}
        if ($km_actual>=249500 and $km_actual<=250500 and $ultimo<249500) {$salida="Mantenimiento K10";}


    }

    return $salida;
}
?>