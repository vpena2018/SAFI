<?php

date_default_timezone_set('America/Tegucigalpa');
$hoy=date('Y-m-d');

// Deshabilitar errores
error_reporting(0);
ini_set('max_execution_time', '900');

$stud_arr["pcode"] = 0;
$stud_arr["tot_oc"] =0;
$stud_arr["tot_ocob"] =0;
$stud_arr["tot_inv"] =0;
$stud_arr["tot_inv_av"] =0;
$stud_arr["tot_comb"] =0;
$stud_arr["oc"] ='';
$stud_arr["cob"] ='';
$stud_arr["inv"] ='';
$stud_arr["inv_av"] ='';
$stud_arr["comb"] ='';


// Verificacion del cliente y parametros
if (!isset($_REQUEST['tk'])) {   salida_json($stud_arr); exit; }	 
if ($_REQUEST['tk']<>'H04s1o9zlOz7') {salida_json($stud_arr); exit;  }

require_once ('../include/config.php');

file_put_contents(app_logs_folder."LOG_Sync_ordenes".date("Y-m-d").".log",  date("Y-m-d g:i a").", ".$_SERVER['REMOTE_ADDR']. " \r\n", FILE_APPEND ); 
 


$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (!mysqli_connect_errno()) {	  
		$conn->set_charset("utf8");
} else { salida_json($stud_arr); exit; }


// actualizar estado sincronizado
if (isset($_GET['lid'],$_GET['tip'])) {
    $tabla="";
    switch ($_GET['tip']) {
        case 'OC':
            $tabla="orden_compra";
            break;
        case 'COB':
            $tabla="orden_cobro";
            break;
        case 'INV':
            $tabla="servicio_detalle";
            break;
        case 'INV_AV':
            $tabla="averia_detalle";
            break;
        case 'COMB':
            $tabla="orden_combustible";
            break;

    }

    if ($tabla<>"") {
        $ids=filter_var( $_GET['lid'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND );
        if ($conn->query("UPDATE $tabla SET SAP_sinc=NOW() WHERE id IN ($ids)")!= false) {
            $stud_arr["pcode"] = 1;
        } 
    }
  
    salida_json($stud_arr);
    exit;
}




// Ordenes de compra
$result_OC = $conn->query("SELECT orden_compra.id,orden_compra.numero, orden_compra.fecha,  orden_compra.observaciones
,tienda.sap_almacen
,servicio.numero AS numero_servicio
,averia.numero AS numero_averia
,entidad.codigo_alterno AS codigo_entidad
,producto.codigo_alterno AS codigo_producto
FROM orden_compra
LEFT OUTER JOIN tienda ON (orden_compra.id_tienda=tienda.id)
LEFT OUTER JOIN servicio ON (orden_compra.id_servicio=servicio.id)
LEFT OUTER JOIN averia ON (orden_compra.id_averia=averia.id)
LEFT OUTER JOIN entidad ON (orden_compra.id_entidad=entidad.id)
LEFT OUTER JOIN producto ON (producto.id=ifnull(servicio.id_producto,averia.id_producto))

WHERE orden_compra.SAP_sinc IS NULL AND orden_compra.id_estado>=1

 "); 
  
  
if ($result_OC!=false){
    if ($result_OC -> num_rows > 0) { 
        $stud_arr["pcode"] = 1;
        $stud_arr["tot_oc"] =$result_OC -> num_rows;
        $ordenes=array();
         
        $i=0;    
        while ($row_OC = $result_OC -> fetch_assoc()) {

            unset($ordenes_detalle,$result_OC_detalle);
            $ordenes_detalle=array();
            $result_OC_detalle = $conn->query("SELECT 
               producto_codigoalterno             
             , cantidad
             , precio_costo
             , precio_venta
             , producto_nota
            FROM orden_compra_detalle
            WHERE id_orden=".$row_OC["id"]);
            if ($result_OC_detalle -> num_rows > 0) { 
                // $d=0;
                // while ($row_OC_det = $result_OC_detalle -> fetch_assoc()) {

                //     $d++;
                // }
                $ordenes_detalle = $result_OC_detalle -> fetch_all();
                //$ordenes_detalle=$row_OC_det;
            }

            $ordenes[$i]['id']=$row_OC["id"];
            $ordenes[$i]['numero']=$row_OC["numero"];
            $ordenes[$i]['codigo_entidad']=$row_OC["codigo_entidad"];
            $ordenes[$i]['fecha']=$hoy; // $row_OC["fecha"];
            $ordenes[$i]['observaciones']=$row_OC["observaciones"];
            $ordenes[$i]['sap_almacen']=$row_OC["sap_almacen"];
            $ordenes[$i]['numero_servicio']=$row_OC["numero_servicio"];
            $ordenes[$i]['numero_averia']=$row_OC["numero_averia"];
            $ordenes[$i]['codigo_producto']=$row_OC["codigo_producto"];
            $ordenes[$i]['detalle']=$ordenes_detalle;

            $i++;
           
        }   
        $stud_arr["oc"]=$ordenes;
       
       
    }
}




// ORDENES DE COBRO
unset($result_OC,$row_OC,$ordenes);
$result_OC = $conn->query("SELECT orden_cobro.id,orden_cobro.numero, orden_cobro.fecha,  orden_cobro.observaciones
,orden_cobro.codigo_gastos_admon
,tienda.sap_almacen
,servicio.numero AS numero_servicio
,averia.numero AS numero_averia
,entidad.codigo_alterno AS codigo_entidad
,producto.codigo_alterno AS codigo_producto
,averia.id_tipo
,orden_cobro.tipo_cobro
FROM orden_cobro
LEFT OUTER JOIN tienda ON (orden_cobro.id_tienda=tienda.id)
LEFT OUTER JOIN servicio ON (orden_cobro.id_servicio=servicio.id)
LEFT OUTER JOIN averia ON (orden_cobro.id_averia=averia.id)
LEFT OUTER JOIN entidad ON (orden_cobro.id_entidad=entidad.id)
LEFT OUTER JOIN producto ON (producto.id=ifnull(servicio.id_producto,averia.id_producto))

WHERE orden_cobro.SAP_sinc IS NULL AND orden_cobro.id_estado>=1


 "); 
  
  
if ($result_OC!=false){
    if ($result_OC -> num_rows > 0) { 
        $stud_arr["pcode"] = 1;
        $stud_arr["tot_ocob"] =$result_OC -> num_rows;
        $ordenes=array();

        //*******productos  gasto admninistrativos********
        
        $gasto_admon=0;
        $exento_ganancia=array();
    
          $sql="SELECT isv, porcentaje_ganancia, porcentaje_gastos_admon,productos_exentos_ganancia,productos_exentos_isv,cobro_recargo_porcentaje,  cobro_precio_atm_x_hora
          FROM configuracion WHERE id=1";
    
        $result_config = $conn->query($sql);
      
          if ($result_config->num_rows > 0) {    
            $row_config = $result_config -> fetch_assoc();    
                           
             try { if($row_config["porcentaje_gastos_admon"]>0){$gasto_admon=$row_config["porcentaje_gastos_admon"]/100;}     } catch (\Throwable $th) {    } 
             try { if(trim($row_config["productos_exentos_ganancia"])<>''){ $exento_ganancia=array_map('trim', explode(',', $row_config["productos_exentos_ganancia"]));}     } catch (\Throwable $th) {    } 
            
        }       
        //********************************************** */
         
        $i=0;  
         
        while ($row_OC = $result_OC -> fetch_assoc()) {
            
            unset($ordenes_detalle,$result_OC_detalle);
            $gastos_admon=0; 
            $subtotal=0;
            $subtotal_alcosto=0;
            $ordenes_detalle=array();
            $result_OC_detalle = $conn->query("SELECT 
               producto_codigoalterno             
             , cantidad
             , precio_costo
             , precio_venta
             , producto_nota
             , producto_nombre
            FROM orden_cobro_detalle
            WHERE id_orden=".$row_OC["id"]);
            if ($result_OC_detalle -> num_rows > 0) { 
                $ordenes_detalle = $result_OC_detalle -> fetch_all();
               
                foreach ($ordenes_detalle  as $value) {
                    $subtotal+=($value[1]*$value[3]);
                    $subtotal_alcosto+=($value[1]*$value[2]);
                }
                //  while ($row_OC_det = $result_OC_detalle_tmp -> fetch_assoc()) {
                //     $subtotal+=($row_OC_det['cantidad']*$row_OC_det['precio_venta']);
                
                //  }
                
                //$ordenes_detalle=$row_OC_det;
            }

            //  CALCULOS
            //-------------
            //Orden de servicio
            
            $codigo_isv='ISV';

            if(!es_nulo($row_OC["tipo_cobro"])) { //ORDEN DE Servicio
                switch ($row_OC["tipo_cobro"]) {
                    case 1: // <option value="1">Cobrable al costo sin recargo del 15% en actividades y repuestos sin gastos administrativos y sin 15% ISV</option>
                        $codigo_isv='EXE';
                        $gastos_admon=0;
                        break;
                    case 2:// <option value="2">Cobrable al costo sin recargo en actividades y repuestos con gastos administrativos y sin 15% ISV</option>
                        $codigo_isv='EXE';
                        $gastos_admon=$subtotal_alcosto*($gasto_admon);
                        break;
                    case 3:// <option value="3">Cobrable al costo sin recargo en actividades y repuestos con gastos administrativos y con 15% ISV</option>
                        $codigo_isv='ISV';
                        $gastos_admon=$subtotal_alcosto*($gasto_admon);
                        break;

                    case 4:// <option value="4">Cobrable con recargo, con gastos administrativos y con 15% de ISV</option>
                        $codigo_isv='ISV';
                        $gastos_admon=$subtotal*($gasto_admon);
                        break;
                    case 5:// <option value="5">Cobrable con recargo, con gastos administrativos y sin 15% de ISV</option>
                        $codigo_isv='EXE';
                        $gastos_admon=$subtotal*($gasto_admon);
                        break;
                    case 6:// <option value="6">Cobrable con recargo, sin gastos administrativos y con 15% de ISV</option>
                        $codigo_isv='ISV';
                        $gastos_admon=0;
                        break;
                    case 7:// <option value="7">Cobrable con recargo, sin gastos administrativos y sin 15% de ISV</option>
                        $codigo_isv='EXE';
                        $gastos_admon=0;
                        break;

                }

            }


            if (intval($row_OC["numero_averia"])>0) { //AVERIA   
     
                $tipo_averia=$row_OC["id_tipo"];
                
                //totales
               
                $gastos_admon=$subtotal*($gasto_admon);        
                // $isv= ($subtotal_gravable+$gastos_admon)*($_SESSION['p_isv']);                
                // $total= $subtotal+$isv+$gastos_admon;

                
                //solo averias cobrables pagan ISV y GA
                if ($tipo_averia==2 or $tipo_averia==3) { 
                    $codigo_isv='EXE';
                    $gastos_admon=0; 

                }

                //4 Cobrable sin ISV sin Gastos Administrativos
                if ($tipo_averia==4) { 
                    $codigo_isv='EXE';
                    $gastos_admon=0; 
 
                }
                //5 Cobrable sin ISV con Gastos Administrativos
                if ($tipo_averia==5) { 
                    $codigo_isv='EXE';

                }
                //6 Cobrable sin Gastos Administrativos con ISV
                if ($tipo_averia==6) { 
                    $gastos_admon=0; 

                }
            }

            //-------------

            $ordenes[$i]['id']=$row_OC["id"];
            $ordenes[$i]['numero']=$row_OC["numero"];
            $ordenes[$i]['codigo_entidad']=$row_OC["codigo_entidad"];
            $ordenes[$i]['fecha']=$hoy; // $row_OC["fecha"];
            $ordenes[$i]['observaciones']=$row_OC["observaciones"];
            $ordenes[$i]['sap_almacen']=$row_OC["sap_almacen"];
            $ordenes[$i]['numero_servicio']=$row_OC["numero_servicio"];
            $ordenes[$i]['numero_averia']=$row_OC["numero_averia"];
            $ordenes[$i]['codigo_producto']=$row_OC["codigo_producto"];
            
            $ordenes[$i]['gasto_admon']=$gasto_admon; //porcentaje
            $ordenes[$i]['total_gastos_admon']=$gastos_admon;
            $ordenes[$i]['codigo_isv']=$codigo_isv;

            $ordenes[$i]['detalle']=$ordenes_detalle;
     
            $ordenes[$i]['id_tipo']=$row_OC["id_tipo"];
            $ordenes[$i]['codigo_gastos_admon']=$row_OC["codigo_gastos_admon"];
            $ordenes[$i]['tipo_cobro']=$row_OC["tipo_cobro"];

            $i++;
           
        }   
        $stud_arr["cob"]=$ordenes;
       
       
    }
}







// ENTRADAS Y SALIDAS INVENTARIO
unset($result_OC,$row_OC,$ordenes);
$result_OC = $conn->query("SELECT servicio.id,servicio.numero, servicio.fecha,  servicio.observaciones
,tienda.sap_almacen
,servicio.numero AS numero_servicio
,entidad.codigo_alterno AS codigo_entidad
,servicio_detalle.SAP_tipo
,producto.codigo_alterno AS codigo_producto

FROM servicio
LEFT OUTER JOIN servicio_detalle ON (servicio_detalle.id_servicio=servicio.id)
LEFT OUTER JOIN tienda ON (servicio.id_tienda=tienda.id)
LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
LEFT OUTER JOIN producto ON (producto.id=servicio.id_producto)

WHERE servicio_detalle.SAP_sinc IS NULL AND servicio_detalle.SAP_tipo IS NOT NULL 


GROUP BY servicio.id , servicio_detalle.SAP_tipo


 "); 
  
  
if ($result_OC!=false){
    if ($result_OC -> num_rows > 0) { 
        $stud_arr["pcode"] = 1;
        $stud_arr["tot_inv"] =$result_OC -> num_rows;
        $ordenes=array();
             
        $i=0;    
        while ($row_OC = $result_OC -> fetch_assoc()) {

            unset($ordenes_detalle,$result_OC_detalle);
            $ordenes_detalle=array();
            $result_OC_detalle = $conn->query("SELECT 
            producto_codigoalterno             
          , cantidad
          , precio_costo
          , precio_venta
          , producto_nota
          ,id
          ,solicitud_conteo
       
         FROM servicio_detalle
         WHERE id_servicio=".$row_OC["id"]."
         AND SAP_sinc is null 
         AND SAP_tipo is not null
         ");
            if ($result_OC_detalle -> num_rows > 0) { 
                // $d=0;
                // while ($row_OC_det = $result_OC_detalle -> fetch_assoc()) {

                //     $d++;
                // }
                $ordenes_detalle = $result_OC_detalle -> fetch_all();
                //$ordenes_detalle=$row_OC_det;
            }
          
            $ordenes[$i]['id']=$row_OC["id"];
            $ordenes[$i]['numero']=$row_OC["numero"];
            $ordenes[$i]['codigo_entidad']=$row_OC["codigo_entidad"];
            $ordenes[$i]['fecha']=$hoy; // $row_OC["fecha"];
            $ordenes[$i]['observaciones']=$row_OC["observaciones"];
            $ordenes[$i]['sap_almacen']=$row_OC["sap_almacen"];
            $ordenes[$i]['numero_servicio']=$row_OC["numero_servicio"];
            $ordenes[$i]['SAP_tipo']=$row_OC["SAP_tipo"];
            $ordenes[$i]['codigo_producto']=$row_OC["codigo_producto"];
            $ordenes[$i]['detalle']=$ordenes_detalle;
       

            $i++;
           
        }   
        $stud_arr["inv"]=$ordenes;
       
       
    }
}







// ENTRADAS Y SALIDAS INVENTARIO ****averias***
unset($result_OC,$row_OC,$ordenes);
$result_OC = $conn->query("SELECT averia.id,averia.numero, averia.fecha,  averia.observaciones
,tienda.sap_almacen
,averia.numero AS numero_servicio
,entidad.codigo_alterno AS codigo_entidad
,averia_detalle.SAP_tipo
,producto.codigo_alterno AS codigo_producto

FROM averia
LEFT OUTER JOIN averia_detalle ON (averia_detalle.id_maestro=averia.id)
LEFT OUTER JOIN tienda ON (averia.id_tienda=tienda.id)
LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
LEFT OUTER JOIN producto ON (producto.id=averia.id_producto)

WHERE averia_detalle.SAP_sinc IS NULL AND averia_detalle.SAP_tipo IS NOT NULL 


GROUP BY averia.id , averia_detalle.SAP_tipo


 "); 
  
  
if ($result_OC!=false){
    if ($result_OC -> num_rows > 0) { 
        $stud_arr["pcode"] = 1;
        $stud_arr["tot_inv_av"] =$result_OC -> num_rows;
        $ordenes=array();
             
        $i=0;    
        while ($row_OC = $result_OC -> fetch_assoc()) {

            unset($ordenes_detalle,$result_OC_detalle);
            $ordenes_detalle=array();
            $result_OC_detalle = $conn->query("SELECT 
            producto_codigoalterno             
          , cantidad
          , precio_costo
          , precio_venta
          , producto_nota
          ,id
          ,solicitud_conteo
       
         FROM averia_detalle
         WHERE id_maestro=".$row_OC["id"]."
         AND SAP_sinc is null 
         AND SAP_tipo is not null
         ");
            if ($result_OC_detalle -> num_rows > 0) { 
                // $d=0;
                // while ($row_OC_det = $result_OC_detalle -> fetch_assoc()) {

                //     $d++;
                // }
                $ordenes_detalle = $result_OC_detalle -> fetch_all();
                //$ordenes_detalle=$row_OC_det;
            }
          
            $ordenes[$i]['id']=$row_OC["id"];
            $ordenes[$i]['numero']=$row_OC["numero"];
            $ordenes[$i]['codigo_entidad']=$row_OC["codigo_entidad"];
            $ordenes[$i]['fecha']=$hoy; // $row_OC["fecha"];
            $ordenes[$i]['observaciones']=$row_OC["observaciones"];
            $ordenes[$i]['sap_almacen']=$row_OC["sap_almacen"];
            $ordenes[$i]['numero_servicio']=$row_OC["numero_servicio"];
            $ordenes[$i]['SAP_tipo']=$row_OC["SAP_tipo"];
            $ordenes[$i]['codigo_producto']=$row_OC["codigo_producto"];
            $ordenes[$i]['detalle']=$ordenes_detalle;
       

            $i++;
           
        }   
        $stud_arr["inv_av"]=$ordenes;
       
       
    }
}



// Ordenes de combustible
$result_COMB = $conn->query("SELECT orden_combustible.id,orden_combustible.numero, orden_combustible.fecha,  orden_combustible.observaciones
,orden_combustible.tipo_combustible
,orden_combustible.lempiras
,orden_combustible.factura_proveedor
,producto.codigo_alterno
,tienda.sap_almacen
,orden_combustible.litros_reales
,orden_combustible.precio_litro
,entidad.codigo_alterno AS codigo_entidad
FROM orden_combustible
LEFT OUTER JOIN tienda ON (orden_combustible.id_tienda=tienda.id)
LEFT OUTER JOIN entidad ON (orden_combustible.id_entidad=entidad.id)
LEFT OUTER JOIN producto ON (orden_combustible.id_producto=producto.id)

WHERE orden_combustible.SAP_sinc IS NULL AND orden_combustible.id_estado=3

 "); 
  
  
if ($result_COMB!=false){
    if ($result_COMB -> num_rows > 0) { 
        $stud_arr["pcode"] = 1;
        $stud_arr["tot_comb"] =$result_COMB -> num_rows;
        $ordenes=array();
         
        $i=0;    
        while ($row_COMB = $result_COMB -> fetch_assoc()) {

 
            $ordenes[$i]['id']=$row_COMB["id"];
            $ordenes[$i]['numero']=$row_COMB["numero"];
            $ordenes[$i]['codigo_entidad']=$row_COMB["codigo_entidad"];
            $ordenes[$i]['fecha']=$hoy; // $row_COMB["fecha"];
            $ordenes[$i]['observaciones']=$row_COMB["observaciones"];
            $ordenes[$i]['sap_almacen']=$row_COMB["sap_almacen"];
            $ordenes[$i]['numero_servicio']=$row_COMB["numero"];

            $ordenes[$i]['tipo_combustible']=$row_COMB["tipo_combustible"];
            $ordenes[$i]['lempiras']=$row_COMB["lempiras"];
            $ordenes[$i]['factura_proveedor']=$row_COMB["factura_proveedor"];
            $ordenes[$i]['codigo_alterno']=$row_COMB["codigo_alterno"];
            $ordenes[$i]['codigo_producto']=$row_COMB["codigo_alterno"];
            $ordenes[$i]['litros']=$row_COMB["litros_reales"];
            $ordenes[$i]['precio_litro']=$row_COMB["precio_litro"];
            $ordenes[$i]['detalle']='';

            $i++;
           
        }   
        $stud_arr["comb"]=$ordenes;
       
       
    }
}





  
    
   // file_put_contents(app_logs_folder."LOG_Sync_ordenes".date("Y-m-d").".log",  date("Y-m-d g:i a").", ".$salida. " \r\n", FILE_APPEND ); 
    salida_json($stud_arr);




//####################################################################

function salida_json($stud_arr){
    
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 15 Jan 2000 07:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($stud_arr);    
    exit;   
}

    ?>