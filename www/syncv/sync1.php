<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

date_default_timezone_set('America/Tegucigalpa');

// Deshabilitar errores
//error_reporting(0);
ini_set('max_execution_time', '900');


$salida="";
$errores="";
$nprod=0;
$nentidad=0;
$ncosto=0;

// Verificacion del cliente y parametros
if (!isset($_POST['productos'],$_POST['costos'],$_POST['entidad'],$_POST['fecha'],$_POST['hora'],$_POST['fecha_now'],$_POST['hora_now'])) {  exit;}	 
if (trim($_SERVER['HTTP_USER_AGENT'])<>"Mozilla/5.0 (PHP; U; CPU; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011") {  exit; }

require_once ('../include/config.php');

file_put_contents(app_logs_folder."LOG_Sync".date("Y-m-d").".log",  date("Y-m-d g:i a").", ".$_SERVER['REMOTE_ADDR']. " \r\n", FILE_APPEND ); 
 


$date_ult=date_create($_POST['fecha'].' '.$_POST['hora']); 
$fecha_ult=date_format($date_ult,'Y-m-d');
$hora_ult=date_format($date_ult,'H:i:s');

$date_now=date_create($_POST['fecha_now'].' '.$_POST['hora_now']); 
$fecha_now=date_format($date_now,'Y-m-d');
$hora_now=date_format($date_now,'H:i:s');


$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (!mysqli_connect_errno()) {	  
		$conn->set_charset("utf8");
} else {  exit; }



//#####  productos #########

$productos= unserialize($_POST['productos']);
foreach($productos as $key => $value) 
  {
    ini_set('max_execution_time', '900');
    $sql="";$sql2="";
    
    $idcod=0;
    unset($salidaex,$rowex);
    $salidaex=$conn->query("SELECT id FROM producto where codigo_alterno=".GetSQLValue($value['ItemCode'],'text'). " LIMIT 1" );
    if ($salidaex->num_rows > 0) { $rowex = $salidaex -> fetch_assoc();  $idcod=intval($rowex["id"]);}
    if ($idcod<=0) {
      $sql="insert into producto set sincronizado=NOW()";
    } else {
      $sql="update producto set sincronizado=NOW()";
      $sql2=" where id=".GetSQLValue($idcod,'int');
    }
    // $date_creado=date_create($value['CreateDate'].' '.$value['CreateTime']);
    // if ($date_creado>=$date_ult) {
    //   $sql="insert into producto set sincronizado=NOW()";
    // } else {
    //   $sql="update producto set sincronizado=NOW()";
    //   $sql2=" where codigo_alterno=".GetSQLValue($value['ItemCode'],'text');
    // }
 //$sql="REPLACE into producto set sincronizado=NOW()";
   

    $sql.= " , codigo_alterno =".GetSQLValue($value["ItemCode"],"text");  
    $sql.= " , nombre =".GetSQLValue($value["ItemName"],"text");  
    $sql.= " , codigo_grupo =".GetSQLValue($value["ItemsGroupCode"],"text");  
    $sql.= " , habilitado =".GetSQLValue(YesNo($value["Valid"]),"int");  
    $sql.= " , congelado =".GetSQLValue(YesNo($value["Frozen"]),"int");  
    $sql.= " , item_compra =".GetSQLValue(YesNo($value["PurchaseItem"]),"int");  
    $sql.= " , item_venta =".GetSQLValue(YesNo($value["SalesItem"]),"int");  
    $sql.= " , item_inventario =".GetSQLValue(YesNo($value["InventoryItem"]),"int");  
    $sql.= " , codigo_hertz =".GetSQLValue($value["U_grphertz"],"text"); 

    $tipotmp=null;
    


    //##### Tipo del producto #########

    // unset($rep,$serv,$vent);

    // // Vehiculos
    // if ($value["U_KF_TIPART"]==0 and substr($value["ItemCode"],0,3)=='EA-') {
    //   $tipotmp=0;
    // }

    // // Repuesto
    // $rep = array(101,102,103,104,105,106, 109,110,111,112,113,114,115,116, 118,119,121, 142,144,146, 168,170,172,180);
    // if ($value["InventoryItem"]==1 and in_array($value["ItemsGroupCode"], $rep)) {
    //   $tipotmp=2;
    // }

    // // Servicio
    // $serv = array(111,112,113,115,116,101,104,121,134,139,140,141,142,143,144,145,146, 147,148,158,168,169,176,156,157,170,171, 177,178,180);
    // if ($value["InventoryItem"]==0 and in_array($value["ItemsGroupCode"], $serv)) {
    //   $tipotmp=3;
    // }

    //  // venta
    // $vent = array(141,149,150,151,152,153,154,181,182);
    // if ($value["U_KF_TIPART"]==0 and $value["InventoryItem"]==0 and $value["PurchaseItem"]==0 and $value["SalesItem"]==1 and in_array($value["ItemsGroupCode"], $vent)) {
    //   $tipotmp=3;
    // }

    //##### Tipo del producto #########
    $sincronizar=true;
    $tipo_ukf=intval($value["U_KF_TIPART"]);
    switch ($tipo_ukf) {
      case 1: // Vehículos
        $tipotmp=0;
        break;
      case 2: //Repuestos
        $tipotmp=2;
        break;
      case 3: //Actividades
        $tipotmp=3;
        break;
      case 4: //Cobrables
        $tipotmp=2;
        break;
      
      default: //No sincronizar
        $sincronizar=false;
        break;
    }

    

    $sql.= " , tipo =".GetSQLValue( $tipotmp,"int");
    $sql.= " , tipo_sap =".GetSQLValue($value["U_KF_TIPART"],"int");   // $sql.= " , tipo =".GetSQLValue($tipotmp,"int_cero"); 


    $sql.= " , marca =".GetSQLValue($value["U_MARCA"],"text");  
    $sql.= " , anio =".GetSQLValue($value["U_YEAR"],"text");  
    $sql.= " , modelo =".GetSQLValue($value["U_MODELO"],"text"); 
    $sql.= " , color =".GetSQLValue($value["U_COLOR"],"text");  
    $sql.= " , cilindrada =".GetSQLValue($value["U_CILINDRADA"],"text");  
    $sql.= " , serie =".GetSQLValue($value["U_SERIE"],"text");  
    $sql.= " , motor =".GetSQLValue($value["U_MOTOR"],"text");  
    $sql.= " , placa =".GetSQLValue($value["U_PLACA"],"text");  
    $sql.= " , tipo_vehiculo =".GetSQLValue($value["U_TIPO"],"text");  
    $sql.= " , chasis =".GetSQLValue($value["U_CHASIS"],"text");
    $sql.= " , clase =".GetSQLValue($value["U_CLASE"],"text");
    $sql.= " , horas =".GetSQLValue($value["U_HORAS"],"double"); 
    if ($value["U_TIPOMANT"]=="P") {
      $sql.= " , tipo_mant ='Preventivo'";
    }
    if ($value["U_TIPOMANT"]=="C") {
      $sql.= " , tipo_mant ='Correctivo'";
    }
    
      
    $sql.= " , precio_costo =".GetSQLValue($value["ProdStdCost"],"double");  
    //$sql.= " , precio_venta =".GetSQLValue($value["precio_venta"],"double");  //     "ItemPrices" =>  ".json_encode($item->ItemPrices),
    // "QuantityOnStock" =>  $item->QuantityOnStock,

    $sql.= $sql2;

   // echo $sql."<br>";
    if ($sincronizar==true) {
      if (!$conn->query($sql)) {
        $errores.='\r\n'.$sql.' '. $conn->$mysqli -> error;
      }
    } else {
      if ($idcod>0) {
      //  $conn->query("DELETE FROM producto where id=".GetSQLValue($idcod,'int')." LIMIT 1");
      }
    }
    $nprod++;

  }



 //#####  COSTOS #########

$costos= unserialize($_POST['costos']);
foreach($costos as $key => $value) 
  {
    ini_set('max_execution_time', '900');
    $sql="";$sql2="";
    
    $idcod=0;
    unset($salidaex,$rowex);
 
    $salidaex=$conn->query("SELECT id FROM producto_costo where codigo_alterno=".GetSQLValue($value['ItemCode'],'text'). " AND sap_almacen=".GetSQLValue($value['WhsCode'],'text'). " LIMIT 1" );
    if ($salidaex->num_rows > 0) { $rowex = $salidaex -> fetch_assoc();  $idcod=intval($rowex["id"]);}
    if ($idcod<=0) {
      $sql="insert into producto_costo set sincronizado=NOW()";
    } else {
      $sql="update producto_costo set sincronizado=NOW()";
      $sql2=" where id=".GetSQLValue($idcod,'int');
    }

    $sql.= " , codigo_alterno =".GetSQLValue($value["ItemCode"],"text");  
    $sql.= " , sap_almacen =".GetSQLValue($value["WhsCode"],"text");  
    $sql.= " , precio_costo =".GetSQLValue($value["AvgPrice"],"double");  
    if(isset($value["OnHand"])){$sql.= " , OnHand =".GetSQLValue($value["OnHand"],"double"); } 

    $sql.= $sql2;

   // echo $sql."<br>";
    if ($sincronizar==true) {
      if (!$conn->query($sql)) {
        $errores.='\r\n'.$sql.' '. $conn->$mysqli -> error;
      }
    } else {
      if ($idcod>0) {
      //  $conn->query("DELETE FROM producto where id=".GetSQLValue($idcod,'int')." LIMIT 1");
      }
    }
    $ncosto++;

  } 


// ############## ENTIDAD ###############
    
    $entidad= unserialize($_POST['entidad']);
foreach($entidad as $key => $value) 
    {
      ini_set('max_execution_time', '900');
      $sql="";$sql2="";
      $idcod=0;
      unset($salidaex,$rowex);
      $salidaex=$conn->query("SELECT id FROM entidad where codigo_alterno=".GetSQLValue($value['CardCode'],'text'). " LIMIT 1" );
      if ($salidaex->num_rows > 0) { $rowex = $salidaex -> fetch_assoc();  $idcod=intval($rowex["id"]);}
      if ($idcod<=0) {
        $sql="insert into entidad set sincronizado=NOW()";
      } else {
        $sql="update entidad set sincronizado=NOW()";
        $sql2=" where id=".GetSQLValue($idcod,'int');
      }

      
      // $date_creado=date_create($value['CreateDate'].' '.$value['CreateTime']);
      // if ($date_creado>=$date_ult) {
      //   $sql="insert into entidad set sincronizado=NOW()";
      // } else {
      //   $sql="update entidad set sincronizado=NOW()";
      //   $sql2=" where codigo_alterno=".GetSQLValue($value['CardCode'],'text');
      // }
     // $sql="REPLACE into entidad set sincronizado=NOW()";

      $sql.= " , tipo =".GetSQLValue(ClienteProv($value["CardType"]),"int");  //cCustomer sSupplier
      $sql.= " , codigo_alterno =".GetSQLValue($value["CardCode"],"text");  
      $sql.= " , codigo_grupo =".GetSQLValue($value["GroupCode"],"text");  
      $sql.= " , nombre =".GetSQLValue($value["CardName"],"text");  
      $sql.= " , direccion =".GetSQLValue($value["Address"],"text"); //Address 
      $sql.= " , ciudad =".GetSQLValue($value["City"],"text");  
     // $sql.= " , departamento =".GetSQLValue($value["departamento"],"text");  
      $sql.= " , pais =".GetSQLValue($value["Country"],"text");  
      $sql.= " , telefono =".GetSQLValue($value["Phone1"],"text");  
      $sql.= " , telefono2 =".GetSQLValue($value["Phone2"],"text");  
      //$sql.= " , telefono3 =".GetSQLValue($value["telefono3"],"text");  
      $sql.= " , contacto =".GetSQLValue($value["ContactPerson"],"text");  
      $sql.= " , notas =".GetSQLValue($value["Notes"],"text");  
    //  $sql.= " , identidad =".GetSQLValue($value["identidad"],"text");  
      $sql.= " , rtn =".GetSQLValue($value["U_RTN"],"text");  
      $sql.= " , habilitado =".GetSQLValue(YesNo($value["Valid"]),"int"); 
      $sql.= " , tipo_precio =".GetSQLValue(($value["PriceListNum"]),"int"); 
      $sql.= " , email =".GetSQLValue($value["EmailAddress"],"text");  
      // $sql.= " , email2 =".GetSQLValue($value["email2"],"text");  
      // $sql.= " , email3 =".GetSQLValue($value["email3"],"text");  
      $Fecha = date("Y-m-d H:i:s",strtotime($value["CreateDate"]));
      $sql.= " , fecha_alta = '".$Fecha."'";
     
      //$sql.= " , fecha_alta =".GetSQLValue($value["CreateDate"],"text");  

 
      // "U_clase" =>  $item->U_clase,
      // "U_cliehertz" =>  $item->U_cliehertz,
 

      $sql.= $sql2;

     // echo $sql."<br><br>";

      if (!$conn->query($sql)) {
       $errores.='\r\n'.$sql.' '. $conn->$mysqli -> error;
      }

      $nentidad++;

    }

    if (!isset($_POST['solo_existencias'])) {    
      $conn->query("UPDATE actualizacion SET fecha_hora=".GetSQLValue($fecha_now.' '.$hora_now,"datetime")."  WHERE id=1") ;
    }
    $salida="Productos: $nprod Costos: $ncosto Entidad: $nentidad";
    file_put_contents(app_logs_folder."LOG_Sync".date("Y-m-d").".log",  date("Y-m-d g:i a").", ".$salida. " \r\n", FILE_APPEND ); 
 
    if ($errores<>"") {
      file_put_contents(app_logs_folder."LOG_Sync_errores".date("Y-m-d").".log",  date("Y-m-d g:i a").": ".$errores. " \r\n", FILE_APPEND ); 
    }
echo $salida;



function ClienteProv($theValue)
 {
  if ($theValue=='cSupplier') {
    $salida=2;
  } else {
    $salida=1;
  }
  return $salida;
 }

function YesNo($theValue)
 {
  if ($theValue=='tYES') {
    $salida=1;
  } else {
    $salida=0;
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
    ?>