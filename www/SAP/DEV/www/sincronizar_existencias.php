<?php
// ##############################################################################
// # Infosistemas 2023                                                          #
// # Modulo Sincronizar   SOLO EXISTENCIAS                                                      #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################

// #########################################
// ######### Configuracion Basica ##########
// #########################################

$url_actualiza="https://flota.inglosa.hn";
//$url_actualiza="http://localhost/inglosa";

// $usuario ="manager";
// $clave ="@dmiN123*";
// $companyDB ="SBO_HERTZ_PRUEBAS";

$usuario ="manager";
$clave ="@dmiN123*";
$companyDB ="SBO_HERTZ_PROD";

$config = [
    "https" => true,
    "host" => "10.10.2.7",
    "port" => 50000,
    "sslOptions" => [
        //"cafile" => "SAPb1/certificate.crt",
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
    "version" => 1
];

date_default_timezone_set('America/Tegucigalpa');
 error_reporting(0); //***** Activar EN PRODUCCION ******

ini_set('max_execution_time', '900');

chdir(__DIR__);
// ################# FIN DE CONFIGURACION ######################



// ###### OBTENER DATOS DE ULTIMA SINCRONIZACION ######
 
file_put_contents("logs/LOG_Sync".date("Y-m-d").".log",  date("Y-m-d g:i a").",Actualizando...  \r\n", FILE_APPEND ); 
 
try {

    $ultima_actualizacion=file_get_contents($url_actualiza."/syncv/sync_ult.php");
    $date1=date_create($ultima_actualizacion); 
    $fecha_ult=date_format($date1,'Y-m-d');
    $hora_ult=date_format($date1,'H:i:s');
    $fecha_now= date('Y-m-d');
    $hora_now= date('H:i:s'); 



} catch (\Throwable $th) {
    file_put_contents("logs/LOG_Sync".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error: $th \r\n", FILE_APPEND ); 
    exit;
}
     

// ###### CREAR SESION SAP ######

include "SAPb1/SAPb1.php";

use SAPb1\SAPClient;
use SAPb1\Filters\Equal;
use SAPb1\Filters\LessThan;
use SAPb1\Filters\MoreThan;
use SAPb1\Filters\MoreThanEqual;

$sap = SAPClient::createSession($config, $usuario, $clave, $companyDB);


// BusinessPartners  --> clientes y proveedores
// Drafts  ---> facturas en borador y ordenes de compra... facturas=13, orden compra=22,entrada mercancia=59,salida mercancia=60    ObjType
// Items   --> inventario
// Projects    ->vehiculos


$arr_entidad=array();
$arr_productos=array();
$arr_costos=array();


// ####### OBTENER REGISTROS: Productos y Vehiculos ########


$productos = $sap->getService('Items');
$productos->headers(['Prefer' => 'odata.maxpagesize=0']);

// $result_productos = $productos->queryBuilder()
//     ->select('ItemCode,ItemName,ItemsGroupCode,PurchaseItem,SalesItem,InventoryItem,Valid,Frozen,QuantityOnStock,U_grphertz,U_KF_TIPART,U_MARCA,U_YEAR,U_MODELO,U_COLOR,U_CILINDRADA,U_SERIE,U_MOTOR,U_PLACA,U_TIPO,U_CHASIS,ProdStdCost,CreateDate,CreateTime')
//     ->where(new MoreThanEqual("UpdateDate", $fecha_ult),new MoreThanEqual("UpdateTime", $hora_ult))
//     ->limit(9000) //TODO OJO quitar 
//     ->findAll() 
//     ;
$result_productos = $productos->queryBuilder()
    ->select('ItemCode,ItemName,ItemsGroupCode,PurchaseItem,SalesItem,InventoryItem,Valid,Frozen,QuantityOnStock,U_grphertz,U_KF_TIPART,U_MARCA,U_YEAR,U_MODELO,U_COLOR,U_CILINDRADA,U_SERIE,U_MOTOR,U_PLACA,U_TIPO,U_CHASIS,ProdStdCost,AvgStdPrice,CreateDate,CreateTime,U_HORAS,U_TIPOMANT,U_MANO_OBRA')
    ->where(new MoreThanEqual("UpdateDate", $fecha_ult),new MoreThanEqual("UpdateTime", $hora_ult))
    ->orWhere(new MoreThanEqual("CreateDate", $fecha_ult),new MoreThanEqual("CreateTime", $hora_ult))
    ->limit(19000) //TODO OJO quitar 
    ->findAll() 
    ;

    //**********Costos x almacen**************
    $productos_costo = $sap->getService('sml.svc/OITW');
    $productos_costo->headers(['Prefer' => 'odata.maxpagesize=0']);
    $result_productos_costo = $productos_costo->queryBuilder()
        ->select('ItemCode,WhsCode,AvgPrice,OnHand')
     //***oct2023   ->where(new MoreThan("AvgPrice", 0))
      //  ->limit(20) //TODO OJO quitar 
        ->findAll() 
        ;
    $datos_costos=json_decode(json_encode($result_productos_costo),true);
    unset($result_productos_costo);
    //****************************************
    
$arr_productos=array();
$arr_costos=array();
foreach($result_productos->value as $item) {
  
    //**********Costos x almacen**************
    reset($datos_costos);
    unset($itemcosto);
    foreach($datos_costos["value"] as $itemcosto)
    {
        if ($itemcosto['ItemCode'] == $item->ItemCode)
        {
            array_push($arr_costos,array(
                "ItemCode" =>  $itemcosto['ItemCode'],
                "WhsCode" =>  $itemcosto['WhsCode'],
                "AvgPrice" =>  $itemcosto['AvgPrice'],
                "OnHand" =>  $itemcosto['OnHand']
            ));
           // break;
        }
    }

    //****************************************
    


}
unset($result_productos,$datos_costos);



    ini_set('max_execution_time', '900');

// ###### CERRAR SESION SAP ######
try {
    $cerrar_session = $sap->getService('Logout');
    //$cerrar_session_crear = $cerrar_session->create([]);
} catch (\Throwable $th) {
    //throw $th;
}



//######  ENVIAR DATOS AL SISTEMA FLOTA ######
$nprod=0;
$nentidad=0;
try {
  $nprod=count($arr_productos);
} catch (\Throwable $th) {}
try {
    $nentidad=count($arr_entidad);
  } catch (\Throwable $th) {}
  
  
$post = array();
$post['fecha'] =$fecha_ult;
$post['hora'] =$hora_ult;
$post['fecha_now'] =$fecha_now;
$post['hora_now'] =$hora_now;
$post['productos'] = serialize($arr_productos) ;
$post['entidad'] = serialize($arr_entidad);
$post['costos'] = serialize($arr_costos) ;
$post['solo_existencias'] =1;

$header=array(
    "User-Agent: Mozilla/5.0 (PHP; U; CPU; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011",
    "Accept-language: en");

$ch = curl_init();
$fullurl = $url_actualiza."/syncv/sync1.php";
curl_setopt($ch, CURLOPT_URL, $fullurl );
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch, CURLOPT_POST, 1 );
curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
curl_setopt($ch, CURLOPT_POSTFIELDS, $post );

$respuesta = curl_exec( $ch );


file_put_contents("logs/LOG_Sync".date("Y-m-d").".log",  date("Y-m-d g:i a").",Enviado= Prod: $nprod  Entidad: $nentidad  /  Procesado=  $respuesta \r\n", FILE_APPEND ); 
 

//echo $respuesta;


 
   
?>