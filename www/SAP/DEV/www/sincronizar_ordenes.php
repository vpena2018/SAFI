<?php
// ##############################################################################
// # Infosistemas 2022                                                          #
// # Modulo Sincronizar  ORDENES                                                #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################

// #########################################
// ######### Configuracion Basica ##########
// #########################################

//$url_actualiza="https://flota.inglosa.hn";
$url_actualiza="http://localhost";

// $usuario ="manager";
// $clave ="@dmiN123*";
// $companyDB ="SBO_HERTZ_PRUEBAS";

$usuario ="manager";
$clave ="@dmiN123*";
$companyDB ="SBO_HERTZ_PROD";

//Series San Pedro Sula  **usar mismo codigo de almacen SAP
$series["01"] = [
    "orden_compra" => 15,
    "salida_mercancia" => 22,
    "facturas_averias" => 198,
    "facturas_proveedores" => 143
  ];
  
  //Series Tegucigalpa   **usar mismo codigo de almacen SAP
  $series["02"] = [
    "orden_compra" => 107,
    "salida_mercancia" => 147,
    "facturas_averias" => 208,
    "facturas_proveedores" => 153
  ];

$config = [
    "https" => true,
    "host" => "10.10.2.10",
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

function es_nulo($campo) {
	$salida=true;
	if ($campo=="" or is_null($campo) or $campo=="0") {$salida=true;} else {$salida=false ;	}
	return $salida;
}

function obtener_serie($serie,$almacen,$tipo){
    $salida=NULL;
    try {
      if (array_key_exists($almacen,$serie)){
        $tmp=$serie[$almacen][$tipo];
        $salida=$tmp;
      }
    } catch (\Throwable $th) {
      //throw $th;
    }
    return $salida;
  }

// ###### OBTENER DATOS DE SISTEMA FLOTA PARA SINCRONIZAR ######
 
file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Actualizando...  \r\n", FILE_APPEND ); 
 
try {
    $registros_actualizar=0;
    $datos=file_get_contents($url_actualiza."/syncv/sync_ordenes.php?tk=H04s1o9zlOz7");


    $stud_arr=json_decode($datos);

    $tot_oc =intval($stud_arr->tot_oc);
    $tot_ocob =intval($stud_arr->tot_ocob);
    $tot_inv =intval($stud_arr->tot_inv);
    $tot_inv_av =intval($stud_arr->tot_inv_av);
    $tot_comb =intval($stud_arr->tot_comb);

    $registros_actualizar=$tot_oc+$tot_ocob+$tot_inv+$tot_inv_av+$tot_comb;


    file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Registros Actualizar: $registros_actualizar \r\n", FILE_APPEND ); 

} catch (\Throwable $th) {
    file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error: $th \r\n", FILE_APPEND ); 
    exit;
}
     

if ($registros_actualizar<=0){ exit; }



// ###### CREAR SESION SAP ######

include "SAPb1/SAPb1.php";

use SAPb1\SAPClient;
use SAPb1\Filters\Equal;
use SAPb1\Filters\LessThan;
use SAPb1\Filters\MoreThan;
use SAPb1\Filters\MoreThanEqual;

$sap = SAPClient::createSession($config, $usuario, $clave, $companyDB);


// BusinessPartners  --> clientes y proveedores
// Drafts  ---> facturas en borador y ordenes de compra...
// facturas=13, orden compra=22,entrada mercancia=59,salida mercancia=60    ObjType
// Items   --> inventario
// Projects    ->vehiculos



// ####### ORDEN DE COMPRA ########
$id_actualizar_oc="";
$coma="";
if ($stud_arr->pcode==1 and $registros_actualizar>0) {
    if (is_array($stud_arr->oc)){

    foreach($stud_arr->oc as $item_oc) {   
      
        unset($orden_compra,$result_oc_crear,$detalle);
        $detalle=array();


        foreach($item_oc->detalle as $item_oc_det) { 
            array_push($detalle, [
                "ItemCode"=> $item_oc_det[0],//producto_codigoalterno,
                "Quantity"=> $item_oc_det[1],//cantidad, 
                "Price"=> $item_oc_det[2],//precio_costo,            
                "WarehouseCode"=> $item_oc->sap_almacen, 
                "ProjectCode"=> $item_oc->codigo_producto,
                "Currency"=> "L",
                "Rate"=> 1                 
            ]);
        }
       
        // "xxx"=> $item_oc_det[3],//precio_venta,
 
        
        try {

            $compra_Comments="";
            $compra_Reference2="";
            $compra_JournalMemo="";

            if (!es_nulo($item_oc->numero_servicio)) {
                $compra_Comments="Sistema Flota Orden Servicio #".$item_oc->numero_servicio;
                $compra_Reference2="OS".$item_oc->numero_servicio;
                $compra_JournalMemo="Orden de Cobro / ".$item_oc->codigo_producto.", OS".$item_oc->numero_servicio;
            }

            if (!es_nulo($item_oc->numero_averia)) {
                $compra_Comments="Sistema Flota Orden Averia #".$item_oc->numero_averia;
                $compra_Reference2="AV".$item_oc->numero_averia;
                $compra_JournalMemo="Orden de Cobro / ".$item_oc->codigo_producto.", AV".$item_oc->numero_averia;
               
            }

            ini_set('max_execution_time', '900');
            $orden_compra = $sap->getService("Drafts");
            $result_oc_crear = $orden_compra->create([

                "CardCode"=> $item_oc->codigo_entidad,
                "ObjType"=> 22,    
                "DocObjectCode"=> 22,
                "DocDate"=> $item_oc->fecha ,
                "DocDueDate"=> $item_oc->fecha ,
                "DocCurrency"=> "L",
                "DocRate"=> 1, 
                "Comments"=> $compra_Comments ,
                 "Reference2"=> $compra_Reference2 ,
                 "JournalMemo"=> $compra_JournalMemo ,
                 "Series"=> obtener_serie($series,$item_oc->sap_almacen,"orden_compra"),
                "DocumentLines"=> $detalle
            ]); //. " " .$item_oc->observaciones

              
            

           
                
        } catch (\Throwable $th) {
            file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error OC: $th \r\n", FILE_APPEND ); 
        }

        $id_actualizar_oc.=$coma. $item_oc->id ;
        $coma=",";


    }

  
    //marcar como sincronizado
    try {
        $sincro_oc=file_get_contents($url_actualiza."/syncv/sync_ordenes.php?tk=H04s1o9zlOz7&tip=OC&lid=".urlencode($id_actualizar_oc) );
    } catch (\Throwable $th) {
        file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error actualizar OC: $th \r\n", FILE_APPEND ); 
    }
    
} 

}










// ####### ORDEN DE COBRO ########
$id_actualizar_cob="";
$coma="";
if ($stud_arr->pcode==1 and $registros_actualizar>0) {

    if (is_array($stud_arr->cob)){
    foreach($stud_arr->cob as $item_cob) {   
      
        unset($orden_compra,$result_oc_crear,$detalle);
        $detalle=array();


        foreach($item_cob->detalle as $item_cob_det) { 
            
            $preciocobrar= $item_cob_det[3];
            //validar si cobrable al costo
            if ($item_cob->tipo_cobro==1 or $item_cob->tipo_cobro==2 or $item_cob->tipo_cobro==3) {
                $preciocobrar= $item_cob_det[2];
            } 

            array_push($detalle, [
                "ItemCode"=> $item_cob_det[0],//producto_codigoalterno,
                "ItemDescription"=> $item_cob_det[5], //descipcion
                "Quantity"=> $item_cob_det[1],//cantidad, 
                "Price"=> $preciocobrar,//precio_venta,            
                "WarehouseCode"=> $item_cob->sap_almacen, 
                "ProjectCode"=> $item_cob->codigo_producto,
                "TaxCode"=> $item_cob->codigo_isv,  // ISV  , EXE
                "Currency"=> "L",
                "Rate"=> 1                 
            ]);
          
            
        }  
        
        try {

            $cobro_Comments="";
            $cobro_Reference2="";
            $cobro_JournalMemo="";
            $cobro_contrato="";

            if (!es_nulo($item_cob->numero_servicio)) {
                $cobro_Comments="Sistema Flota Orden Servicio #".$item_cob->numero_servicio;
                $cobro_Reference2="OS".$item_cob->numero_servicio;
                $cobro_JournalMemo="Orden de Cobro / ".$item_cob->codigo_producto.", OS".$item_cob->numero_servicio;
                
                if ($item_cob->sap_almacen=='01') { $cobro_contrato="OPSPS_OC_".$item_cob->numero_servicio;  }
                if ($item_cob->sap_almacen=='02') { $cobro_contrato="OPT_OC_".$item_cob->numero_servicio;  }

            }

            if (!es_nulo($item_cob->numero_averia)) {
                $cobro_Comments="Sistema Flota Orden Averia #".$item_cob->numero_averia;
                $cobro_Reference2="AV".$item_cob->numero_averia;
                $cobro_JournalMemo="Orden de Cobro / ".$item_cob->codigo_producto.", AV".$item_cob->numero_averia;
               
                if ($item_cob->sap_almacen=='01') { $cobro_contrato="OPSPS_AVE_".$item_cob->numero_averia;  }
                if ($item_cob->sap_almacen=='02') { $cobro_contrato="OPT_AVE_".$item_cob->numero_averia;  }

               // if ($item_cob->id_tipo==1 or $item_cob->id_tipo==5) {

                    if (!es_nulo($item_cob->codigo_gastos_admon)) {
                         
                    //gastos administrativos
                    array_push($detalle, [
                        "ItemCode"=> $item_cob->codigo_gastos_admon, //'DYG-00006',//producto_codigoalterno,
                        "Quantity"=> 1,//cantidad, 
                        "Price"=> $item_cob->total_gastos_admon,//$total_gasto_admon,            
                        "WarehouseCode"=> $item_cob->sap_almacen, 
                        "ProjectCode"=> $item_cob->codigo_producto,
                        "TaxCode"=> $item_cob->codigo_isv,  // ISV  , EXE
                        "Currency"=> "L",
                        "Rate"=> 1                 
                    ]);
                     }
                // }
            }

            ini_set('max_execution_time', '900');
            $orden_compra = $sap->getService("Drafts");
            $result_oc_crear = $orden_compra->create([

                "CardCode"=> $item_cob->codigo_entidad,
                "ObjType"=> 13,    
                "DocObjectCode"=> 13,
                "DocDate"=> $item_cob->fecha ,
                "DocDueDate"=> $item_cob->fecha ,
                "DocCurrency"=> "L",
                "DocRate"=> 1, 
                "Comments"=>  $cobro_Comments ,
                "Reference2"=> $cobro_Reference2 ,
                "JournalMemo"=> $cobro_JournalMemo ,
                "U_CONTRATO"=> $cobro_contrato ,
                "Series"=> obtener_serie($series,$item_cob->sap_almacen,"facturas_averias"),
                "DocumentLines"=> $detalle
            ]); //. " " .$item_cob->observaciones

                

           
                
        } catch (\Throwable $th) {

            file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error COB: $th \r\n", FILE_APPEND ); 
        }
        $id_actualizar_cob.=$coma. $item_cob->id ;
        $coma=",";


    }

    //marcar como sincronizado
    try {
        $sincro_oc=file_get_contents($url_actualiza."/syncv/sync_ordenes.php?tk=H04s1o9zlOz7&tip=COB&lid=".urlencode($id_actualizar_cob) );
    } catch (\Throwable $th) {
        file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error actualizar COB: $th \r\n", FILE_APPEND ); 
    }
    
}

}





// ####### ENTRADAS Y SALIDAS INVENTARIO ########
$id_actualizar_inv="";
$coma="";
$solicitud_conteo="";
if ($stud_arr->pcode==1 and $registros_actualizar>0) {

    if (is_array($stud_arr->inv)){
    foreach($stud_arr->inv as $item_inv) {   
      
        unset($orden_compra,$result_oc_crear,$detalle);
        $detalle=array();

        foreach($item_inv->detalle as $item_inv_det) { 
            array_push($detalle, [
                "ItemCode"=> $item_inv_det[0],//producto_codigoalterno,
                "Quantity"=> $item_inv_det[1],//cantidad, 
                "Price"=> $item_inv_det[3],//precio_venta,            
                "WarehouseCode"=> $item_inv->sap_almacen,
                "ProjectCode"=> $item_inv->codigo_producto, 
                "Currency"=> "L",
                "Rate"=> 1                 
            ]);
            // Id del detalle
            $id_actualizar_inv.=$coma.$item_inv_det[5] ;
            $coma=",";
            $solicitud_conteo=$item_inv_det[6];
           
        }  
        //        "Price"=> $item_inv_det[2],//precio_costo,
 
        
        try {
            ini_set('max_execution_time', '900');
            $orden_compra = $sap->getService("Drafts");
            $result_oc_crear = $orden_compra->create([

              
                "ObjType"=> $item_inv->SAP_tipo,    
                "DocObjectCode"=> $item_inv->SAP_tipo,
                "DocDate"=> $item_inv->fecha ,
                "DocDueDate"=> $item_inv->fecha ,
                "DocCurrency"=> "L",
                "DocRate"=> 1, 
                "Comments"=> "Sistema Flota Orden Servicio #".$item_inv->numero_servicio ,
                "Reference2"=> "OS".$item_inv->numero_servicio ."-".$solicitud_conteo,
                "JournalMemo"=> "Salida de Mercancias / ".$item_inv->codigo_producto.", OS".$item_inv->numero_servicio."-".$solicitud_conteo ,
                "Series"=> obtener_serie($series,$item_inv->sap_almacen,"salida_mercancia"),
                "DocumentLines"=> $detalle
            ]); //. " " .$item_inv->observaciones

               //  "CardCode"=> $item_inv->codigo_entidad, 

           
                
        } catch (\Throwable $th) {

            file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error INV: $th \r\n", FILE_APPEND ); 
        }
        


    }

    //marcar como sincronizado
    try {
        $sincro_oc=file_get_contents($url_actualiza."/syncv/sync_ordenes.php?tk=H04s1o9zlOz7&tip=INV&lid=".urlencode($id_actualizar_inv) );
    } catch (\Throwable $th) {
        file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error actualizar INV: $th \r\n", FILE_APPEND ); 
    }
    
}

}



// ####### ENTRADAS Y SALIDAS INVENTARIO   *** AVERIAS ****    ########
$id_actualizar_inv_av="";
$coma="";
$solicitud_conteo="";
if ($stud_arr->pcode==1 and $registros_actualizar>0) {

    if (is_array($stud_arr->inv_av)){
    foreach($stud_arr->inv_av as $item_inv) {   
      
        unset($orden_compra,$result_oc_crear,$detalle);
        $detalle=array();

        foreach($item_inv->detalle as $item_inv_det) { 
            array_push($detalle, [
                "ItemCode"=> $item_inv_det[0],//producto_codigoalterno,
                "Quantity"=> $item_inv_det[1],//cantidad, 
                "Price"=> $item_inv_det[3],//precio_venta,            
                "WarehouseCode"=> $item_inv->sap_almacen,
                "ProjectCode"=> $item_inv->codigo_producto, 
                "Currency"=> "L",
                "Rate"=> 1                 
            ]);
            // Id del detalle
            $id_actualizar_inv_av.=$coma.$item_inv_det[5] ;
            $coma=",";
            $solicitud_conteo=$item_inv_det[6];
           
        }  
        //        "Price"=> $item_inv_det[2],//precio_costo,
 
        
        try {
            ini_set('max_execution_time', '900');
            $orden_compra = $sap->getService("Drafts");
            $result_oc_crear = $orden_compra->create([

              
                "ObjType"=> $item_inv->SAP_tipo,    
                "DocObjectCode"=> $item_inv->SAP_tipo,
                "DocDate"=> $item_inv->fecha ,
                "DocDueDate"=> $item_inv->fecha ,
                "DocCurrency"=> "L",
                "DocRate"=> 1, 
                "Comments"=> "Sistema Flota Orden Averia #".$item_inv->numero_servicio ,
                "Reference2"=> "AV".$item_inv->numero_servicio."-".$solicitud_conteo ,
                "JournalMemo"=> "Salida de Mercancias / ".$item_inv->codigo_producto.", AV".$item_inv->numero_servicio."-".$solicitud_conteo ,
                "Series"=> obtener_serie($series,$item_inv->sap_almacen,"salida_mercancia"),
                "DocumentLines"=> $detalle
            ]); //. " " .$item_inv->observaciones

               //  "CardCode"=> $item_inv->codigo_entidad, 

           
                
        } catch (\Throwable $th) {

            file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error INV_AV: $th \r\n", FILE_APPEND ); 
        }
        


    }

    //marcar como sincronizado
    try {
        $sincro_oc=file_get_contents($url_actualiza."/syncv/sync_ordenes.php?tk=H04s1o9zlOz7&tip=INV_AV&lid=".urlencode($id_actualizar_inv_av) );
    } catch (\Throwable $th) {
        file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error actualizar INV_AV: $th \r\n", FILE_APPEND ); 
    }
    
}

}



// ####### ORDEN DE COMBUSTIBLE ########
$id_actualizar_comb="";
$coma="";
if ($stud_arr->pcode==1 and $registros_actualizar>0) {
    if (is_array($stud_arr->comb)){

    foreach($stud_arr->comb as $item_comb) {   
      
        unset($orden_comb,$result_comb_crear,$detalle);
        $detalle=array();

       
        $combustible="FUEL";
        if ($item_comb->tipo_combustible=="Diesel") {
            $combustible="DIESEL";
        }

            array_push($detalle, [
                "ItemCode"=> $combustible,//producto_codigoalterno,
                "Quantity"=> $item_comb->litros,//cantidad, 
                "Price"=> $item_comb->precio_litro,//precio_costo,            
                "WarehouseCode"=> $item_comb->sap_almacen, 
                "ProjectCode"=> $item_comb->codigo_producto,
                "Currency"=> "L",
                "Rate"=> 1                 
            ]);
       
       
        // "xxx"=> $item_comb_det[3],//precio_venta,
 
        
        try {
            ini_set('max_execution_time', '900');
            $orden_comb = $sap->getService("Drafts");
            $result_comb_crear = $orden_comb->create([

                "CardCode"=> $item_comb->codigo_entidad,
                "ObjType"=> 18,    
                "DocObjectCode"=> 18,
                "DocDate"=> $item_comb->fecha ,
                "DocDueDate"=> $item_comb->fecha ,
                "DocCurrency"=> "L",
                "DocRate"=> 1,
                "NumAtCard"=> $item_comb->factura_proveedor,
                "U_numfacp"=> $item_comb->factura_proveedor,
                
                "Comments"=> "Sistema Flota Orden Combustible #".$item_comb->numero_servicio." , Factura #".$item_comb->factura_proveedor ." , Vehiculo #".$item_comb->codigo_alterno,
                "Reference2"=> "OC".$item_comb->numero_servicio ,
                "JournalMemo"=> "Orden de Combustible / ".$item_comb->codigo_producto.", OC".$item_comb->numero_servicio ,
                "Series"=> obtener_serie($series,$item_comb->sap_almacen,"facturas_proveedores"),
                "DocumentLines"=> $detalle
            ]); //. " " .$item_comb->observaciones

                
           
                
        } catch (\Throwable $th) {
            file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error OC: $th \r\n", FILE_APPEND ); 
        }

        $id_actualizar_comb.=$coma. $item_comb->id ;
        $coma=",";


    }

    //marcar como sincronizado
    try {
        $sincro_comb=file_get_contents($url_actualiza."/syncv/sync_ordenes.php?tk=H04s1o9zlOz7&tip=COMB&lid=".urlencode($id_actualizar_comb) );
    } catch (\Throwable $th) {
        file_put_contents("logs/LOG_SyncOrdenes".date("Y-m-d").".log",  date("Y-m-d g:i a").",Error actualizar COMB: $th \r\n", FILE_APPEND ); 
    }
    
} 

}

// borrar
// $entidad = $sap->getService('Drafts');
// $entidad->headers(['Prefer' => 'odata.maxpagesize=0']);

// $result_entidad = $entidad->queryBuilder()
//     ->select('*')
//     ->orderBy("DocEntry", "desc")
//     ->limit(5) //TODO OJO quitar 
//     ->findAll() 
//     ;


//     var_dump($result_entidad);
//borrar fin


// ###### CERRAR SESION SAP ######
try {
    $cerrar_session = $sap->getService('Logout');
    //$cerrar_session_crear = $cerrar_session->create([]);
} catch (\Throwable $th) {
    //throw $th;
}

 
   
?>