<?php
// ##############################################################################
// # Infosistemas 2023                                                          #
// # Modulo API para sistema rentworks                                          #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################


//****  activar Modulo
define("rw_modulo_activo", true);
define("rw_modulo_debug", true);

// require_once ('framework.php');
ini_set('max_execution_time', '300');


function rw_api($rw_post,$suburl){

    $comando_curl="curl -s --location --request POST 'https://rwwebe.barscloud.com:8715/htzhonduras/web/RWMobileHandler/fleet/".$suburl."' \
    --header 'X-Api-Key: 123e3a22302c2137' \
    --header 'Authorization: Basic VDIzNTg2OjhsZ2x0SVMrQVQyYQ==' \
    --header 'Content-Type: application/json' \
    --data-raw '{";

    $coma="";   
    foreach ($rw_post as $key => $value) {
        $comando_curl.= $coma.'"'.$key.'":"'.$value.'"'; $coma=", ";
    }

    $comando_curl.="}' 2>&1";

    if ( rw_modulo_activo ==false or rw_modulo_debug==true) {
         file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_curl.log", "".date("Y-m-d g:i a")."\r\n".$comando_curl."\r\n \r\n", FILE_APPEND );
    }

    $rw_json_respuesta="";
    if ( rw_modulo_activo ==true) {
        $rw_json_respuesta = shell_exec($comando_curl);
    }
        
     // echo "<pre>$rw_json_respuesta</pre>";
  //  $rw_json_respuesta = '{"Result":"Successful","RepairOrderID":"32678","Message":"Fleet record odometer not updated.  Odometer reading sent, 117431, is less than current fleet record odometer."}';

    $rw_respuesta ="";

    try {
        $rw_respuesta = json_decode($rw_json_respuesta, true);
    } catch (\Throwable $th) {
        $rw_respuesta = "";
        file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_error.log", ",".date("Y-m-d g:i a").", ".$th->getMessage().", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT'].""." \r\n", FILE_APPEND );       
    }

    if (rw_modulo_debug==true ) {
        try {
               file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_debug.log", ",".date("Y-m-d g:i a").", ".$rw_json_respuesta.", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT'].""." \r\n", FILE_APPEND );       
        } catch (\Throwable $th) {
           
        }
        
    }

    $rw_salida['pcode']=0;
    $rw_salida['pid']="";
    $rw_salida['pmsg']="Error";

    if (is_array($rw_respuesta)) {
        try {

          if ($suburl=="repairorder") { 

                if (isset($rw_respuesta['Result'])) {
                    if ($rw_respuesta['Result']=="Successful") {
                        $rw_salida['pcode']=1;
                    } else {
                        file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_error.log", ",".date("Y-m-d g:i a").", ".$rw_respuesta['Message'].", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT'].""." \r\n", FILE_APPEND );
                    }
                }

                if (isset($rw_respuesta['Message'])) {            
                        $rw_salida['pmsg']=$rw_respuesta['Message'];
                }

                if (isset($rw_respuesta['RepairOrderID'])) {            
                    $rw_salida['pid']=$rw_respuesta['RepairOrderID'];
                }
          }  

          if ($suburl=="NonRev") { //Traslados
              //{"TicketNumber":"78963","TicketStatus":"Open","ReturnMsg":""}
              if (isset($rw_respuesta['TicketNumber'])) {                
                    $rw_salida['pcode']=1;
                    $rw_salida['pid']=$rw_respuesta['TicketNumber'];
                    $rw_salida['pmsg']=$rw_respuesta['ReturnMsg'];
                } else {
                    file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_error.log", ",".date("Y-m-d g:i a").", ".$rw_respuesta['ReturnMsg'].", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT'].""." \r\n", FILE_APPEND );
                }
                

          }

          
            
        
    
        } catch (\Throwable $th) {
            file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_error.log", ",".date("Y-m-d g:i a").", ".$th->getMessage().", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT'].""." \r\n", FILE_APPEND );       
        }
    

    } else {
        file_put_contents(app_logs_folder.date("Y-m-d")."_api_rentworks_error.log", ",".date("Y-m-d g:i a").", ".$rw_respuesta.", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT'].""." \r\n", FILE_APPEND );       
    }

    return $rw_salida;

}

/** Crear orden repairorder*
 * tipo_doc:  1=servicio, 2=lavado , 3=combustible
 * $tipo_taller: propio, externo, pintura
 */ 
function rw_crear_orden($tipo_doc,$id_doc,$notas,$odocampo=""){
    
    $rw_salida['pcode']=0;
    $rw_salida['pid']="";
    $rw_salida['pmsg']="Error";


    //tipo_doc
    switch ($tipo_doc) {
        case 1:
            $doctabla="servicio";            
             break;
        case 2:
            $doctabla="orden_lavado";           
            break;
        case 3:
            $doctabla="orden_combustible";           
            break;
    }

    
    $vehiculo="";
    $odometro="";
    $sqladd2="";
    $id_tipo_mant="";
    if ($tipo_doc==1) {
        $sqladd2=",servicio.id_tipo_mant";
    }

    $result=sql_select("SELECT tienda.rentworks_almacen 
    ,producto.codigo_alterno
    ,producto.km
    $sqladd2
    FROM ".$doctabla." 
    LEFT OUTER JOIN tienda ON (".$doctabla.".id_tienda=tienda.id)
    LEFT OUTER JOIN producto ON (".$doctabla.".id_producto=producto.id)
    WHERE ".$doctabla.".id=".$id_doc);

    if ($result!=false){
        if ($result -> num_rows > 0) { 
           $row = $result -> fetch_assoc() ; 
           $locacion_tmp=$row['rentworks_almacen'];
           $vehiculo=$row['codigo_alterno'];
           $odometro=$row['km'];
           if ($tipo_doc==1) {
             $id_tipo_mant=$row['id_tipo_mant'];
           }
        }
    }

    if ($odocampo<>"") {
        $odometro=$odocampo;
    }

    //repairtype y fleetstatus
    $repairtype="Mantenimiento Taller Interno";    
    $fleetstatus="";

    switch ($tipo_doc) {
        case 1: if ($id_tipo_mant==1) {$repairtype="Mantenimiento Taller Interno"; $fleetstatus="Taller de mantenimiento"; }
                if ($id_tipo_mant==2) {$repairtype="Mantenimiento Taller Externo";$fleetstatus="TALLERES EXTERNOS"; }
                if ($id_tipo_mant==7) {$repairtype="Taller de Pintura";$fleetstatus="Taller de Pintura"; }
              break;//servicio
        case 2: $repairtype="Lavado y Aspirado"; $fleetstatus="Necesita Limpieza"; break;//lavado
        case 3: $repairtype="Repostar Combustible"; $fleetstatus="Refuel"; break;//combustible                               
    }

    //locacion    
    if ($locacion_tmp<>'') {
        $locacion=$locacion_tmp;
    } else {
        $locacion="OPSPS";
    }

    //vehiculo
    if ($vehiculo<>'') {
        //convertir codigo a formato de renworks, quitar un digito despues del EA-
        $vehiculo=substr_replace($vehiculo,'',strpos($vehiculo, '-')+1,1);
    }
    
    $rw_post = array();
    $rw_post['RequestType'] ='CreateRepairOrder';
    $rw_post['Product'] ='VEHICLES';
    $rw_post['UnitNumber'] ="$vehiculo";//EA-2004
    // $rw_post['VIN'] ='xxx';
    // $rw_post['LicenseNumber'] ='xxx';
    $rw_post['CurrentOdometer'] ="$odometro";
    $rw_post['CurrentLocation'] =""; //"$locacion";//ASPS
    $rw_post['DateTimeOpened'] = date('Y-m-d')."T".date('H:i');
    $rw_post['DateTimeDue'] = date('Y-m-d')."T".date('H:i'); //date('Y-m-d')."T".date('H:i', strtotime(date('Y-m-d H:i:s'). ' +10 minutes'));// date('Y-m-d')."T".date('H:i'); // ojo incrementar fecha hora
    $rw_post['FleetStatus'] ="$fleetstatus";  //For Sale
    $rw_post['RepairType'] ="$repairtype";//Problems Repair
    $rw_post['RepairSummary'] ="";
    $rw_post['RepairShopName'] ="";
    // $rw_post['PartsCost'] ="125.88";
    // $rw_post['LaborCost'] ='1267.45';
    $rw_post['Notes'] ="$notas";


    $rw_salida=rw_api($rw_post,"repairorder");
    if ($rw_salida['pcode']==1) {

       if ($rw_salida['pid']<>"") {
           sql_insert("INSERT INTO rw_numero_orden SET id_doc=$id_doc, id_rw=".$rw_salida['pid'].", tipo_doc=$tipo_doc");
       }

    } else {
        // ojo quitar
       // if ( rw_modulo_activo ==false) {sql_insert("INSERT INTO rw_numero_orden SET id_doc=$id_doc, id_rw=1234, tipo_doc=$tipo_doc");}           
    }

    return $rw_salida;

}



/** Cerrar orden repairorder*
 * tipo_doc:  1=servicio, 2=lavado , 3=combustible
 * 
 */ 
function rw_cerrar_orden($tipo_doc,$id_doc,$notas,$odocampo=""){
    
    $rw_salida['pcode']=0;
    $rw_salida['pid']="";
    $rw_salida['pmsg']="Error";

    $campo_add="";

    //tipo_doc
    switch ($tipo_doc) {
        case 1:
            $doctabla="servicio";
            // $campo_add=",".$doctabla.".disponibilidad";
             break;
        case 2:
            $doctabla="orden_lavado";
            break;
        case 3:
            $doctabla="orden_combustible";
            break;
    }

    //fleetstatus
    $fleetstatus="Available";

    $vehiculo="";
    $odometro="";

    $result=sql_select("SELECT tienda.rentworks_almacen 
    ,producto.codigo_alterno
    ,producto.km
    $campo_add
    FROM ".$doctabla." 
    LEFT OUTER JOIN tienda ON (".$doctabla.".id_tienda=tienda.id)
    LEFT OUTER JOIN producto ON (".$doctabla.".id_producto=producto.id)
    WHERE ".$doctabla.".id=".$id_doc);

    if ($result!=false){
        if ($result -> num_rows > 0) { 
           $row = $result -> fetch_assoc() ; 
           $locacion_tmp=$row['rentworks_almacen'];
           $vehiculo=$row['codigo_alterno'];
            $odometro=$row['km'];
            // if ($campo_add<>"") {
            //     $disponibilidad=intval($row['disponibilidad']);
            //     if ($disponibilidad==1) { $fleetstatus="Available"; }
            //     if ($disponibilidad==2) { $fleetstatus="Long Term Rental"; }
            // }
        }
    }

    if ($odocampo<>"") {
        $odometro=$odocampo;
    }

    //locacion    
    if ($locacion_tmp<>'') {
        $locacion=$locacion_tmp;
    } else {
        $locacion="OPSPS";
    }

    //order ID
    $order_id="";
    $result2=sql_select("SELECT id_rw 
    from rw_numero_orden
    WHERE tipo_doc=$tipo_doc AND id_doc=".$id_doc."
    ORDER BY id desc 
    Limit 1");

    if ($result2!=false){
        if ($result2 -> num_rows > 0) { 
           $row2 = $result2 -> fetch_assoc() ; 
           $order_id=$row2['id_rw'];
        }
    }
    

    $rw_post = array();
    $rw_post['RequestType'] ='CloseRepairOrder';
    $rw_post['RepairOrderID'] ="$order_id";
    $rw_post['CurrentOdometer'] ="$odometro";
    $rw_post['CurrentLocation'] =""; //"$locacion";
    $rw_post['DateTimeClosed'] = date('Y-m-d')."T".date('H:i');
    $rw_post['FleetStatus'] ="$fleetstatus"; 
    $rw_post['Notes'] ="$notas";
        

    if ($order_id<>"") {
        $rw_salida=rw_api($rw_post,"repairorder");
        if ($rw_salida['pcode']==1) {

        } else {

        }
    }
    

    return $rw_salida;

}



/** Crear traslado NonRev*
 *  $tipo_doc=
 *  
 */ 
function rw_crear_traslado($id_doc,$notas,$odocampo=""){
    
    $rw_salida['pcode']=0;
    $rw_salida['pid']="";
    $rw_salida['pmsg']="Error";


  
    
    $vehiculo="";
    $odometro="";
    $id_tipo_mant="";
    $tipo_estado="";
    $id_tipo_traslado="";


    $result=sql_select("SELECT orden_traslado.* 
    ,producto.codigo_alterno,producto.nombre,producto.placa
    ,orden_traslado_estado.nombre AS elestado
    ,l1.nombre AS motorista1
    ,l2.usuario AS solicitante1
    ,p1.nombre AS elproveedor
    ,t1.nombre AS tiendasalida
    ,t2.nombre AS tiendadestino
    ,t1.rentworks_almacen as rentworks_tiendasalida
    ,t2.rentworks_almacen as rentworks_tiendaentrada
    ,orden_traslado_tipo.nombre as id_tipo_traslado_lbl

    FROM orden_traslado
    LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
    LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
    LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
    LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
    LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
    LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
    LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)
    LEFT OUTER JOIN orden_traslado_tipo ON (orden_traslado.id_tipo_traslado=orden_traslado_tipo.id)
    WHERE orden_traslado.id=".$id_doc);

    if ($result!=false){
        if ($result -> num_rows > 0) { 
           $row = $result -> fetch_assoc() ; 
           $locacion_tmp=$row['rentworks_tiendasalida'];
           $locacion_destino=$row['rentworks_tiendaentrada'];
           $company="";
           if ($row['tipo_destino']==2) {//proveedor
                $locacion_destino=$row['rentworks_tiendasalida']; 
                $company=$row['elproveedor'];
            }
           $vehiculo=$row['codigo_alterno'];

          
           
           $odometro=$row['kilometraje_salida'];
           $combustible=$row['combustible_salida'];
           $driver=$row['motorista1'];
            $notas=$row['observaciones'];
            $tipo_estado=$row['id_tipo_traslado_lbl'];
            $id_tipo_traslado=$row['id_tipo_traslado'];
            
        }
    }

    if ($odocampo<>"") {
        $odometro=$odocampo;
    }



    //locacion    
    if ($locacion_tmp<>'') {
        $locacion=$locacion_tmp;
    } else {
        $locacion="OPSPS";
    }

    //vehiculo
    if ($vehiculo<>'') {
        //convertir codigo a formato de renworks, quitar un digito despues del EA-
        $vehiculo=substr_replace($vehiculo,'',strpos($vehiculo, '-')+1,1);
    }

    //Combustible 
    $fuellevel=rw_combustible_convertir($combustible);
    

    
    
    $rw_post = array();
    $rw_post['Action'] ='Open';
    $rw_post['Product'] ='VEHICLES';
    $rw_post['UnitNumber'] ="$vehiculo";//EA-2004
    $rw_post['VehicleStatus'] ="$tipo_estado";//'Non-Rev';

    $rw_post['OdometerOut'] ="$odometro";
    $rw_post['FuelLevelOut'] ="$fuellevel";

    $rw_post['Company'] ="$company";

    $rw_post['LocationOut'] ="$locacion";
    $rw_post['LocationDue'] ="$locacion_destino";
    $rw_post['DateTimeOut'] = date('Y-m-d')."T".date('H:i');
    $rw_post['DateTimeDue'] = date('Y-m-d')."T".date('H:i');
 
      
    $rw_post['Driver'] ="$driver";
    $rw_post['DueDetails'] ="$notas";


    $rw_salida=rw_api($rw_post,"NonRev");
    if ($rw_salida['pcode']==1) {

       if ($rw_salida['pid']<>"") {
           sql_insert("INSERT INTO rw_numero_traslado SET id_doc=$id_doc, id_rw=".$rw_salida['pid']);
       }

    } else {
       
    }

    return $rw_salida;

}

/** cerrar orden traslado NonRev*
 * 
 * 
 */ 
function rw_cerrar_traslado($id_doc,$notas,$odocampo=""){
    
    $rw_salida['pcode']=0;
    $rw_salida['pid']="";
    $rw_salida['pmsg']="Error";

    $campo_add="";

    $vehiculo="";
    $odometro="";
    $tipo_estado="";
    $id_tipo_traslado="";

    $result=sql_select("SELECT orden_traslado.* 
    ,producto.codigo_alterno,producto.nombre,producto.placa
    ,orden_traslado_estado.nombre AS elestado
    ,l1.nombre AS motorista1
    ,l2.usuario AS solicitante1
    ,p1.nombre AS elproveedor
    ,t1.nombre AS tiendasalida
    ,t2.nombre AS tiendadestino
    ,t1.rentworks_almacen as rentworks_tiendasalida
    ,t2.rentworks_almacen as rentworks_tiendaentrada
    ,orden_traslado_tipo.nombre as id_tipo_traslado_lbl

    FROM orden_traslado
    LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
    LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
    LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
    LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
    LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
    LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
    LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)
    LEFT OUTER JOIN orden_traslado_tipo ON (orden_traslado.id_tipo_traslado=orden_traslado_tipo.id)
    WHERE orden_traslado.id=".$id_doc);

    if ($result!=false){
        if ($result -> num_rows > 0) { 
           $row = $result -> fetch_assoc() ; 
           $locacion_tmp=$row['rentworks_tiendasalida'];
           $locacion_destino=$row['rentworks_tiendaentrada'];
           $company="";
           if ($row['tipo_destino']==2) {//proveedor
                $locacion_destino=$row['rentworks_tiendasalida']; 
                $company=$row['elproveedor'];
            }
           $vehiculo=$row['codigo_alterno'];

          
           
           $odometro=$row['kilometraje_entrada'];
           $combustible=$row['combustible_entrada'];
           $driver=$row['motorista1'];
            $notas=$row['observaciones2'];
            $tipo_estado=$row['id_tipo_traslado_lbl'];
            $id_tipo_traslado=$row['id_tipo_traslado'];
        }
    }

    if ($odocampo<>"") {
        $odometro=$odocampo;
    }



    //locacion    
    if ($locacion_tmp<>'') {
        $locacion=$locacion_tmp;
    } else {
        $locacion="OPSPS";
    }

    //vehiculo
    if ($vehiculo<>'') {
        //convertir codigo a formato de renworks, quitar un digito despues del EA-
        $vehiculo=substr_replace($vehiculo,'',strpos($vehiculo, '-')+1,1);
    }

    //Combustible 
    $fuellevel=rw_combustible_convertir($combustible);
    
    
    //order ID
    $order_id="";
    $result2=sql_select("SELECT id_rw 
    from rw_numero_traslado
    WHERE id_doc=".$id_doc."
    ORDER BY id desc 
    Limit 1");

    if ($result2!=false){
        if ($result2 -> num_rows > 0) { 
           $row2 = $result2 -> fetch_assoc() ; 
           $order_id=$row2['id_rw'];
        }
    }

    //cuando el conductor le dé clic en completar no deberá cambiar el status dejando igual al seleccionado
    //Excepto auto reemplazo y refuel si deberá cambiar a “available”
    if ($id_tipo_traslado==5 or $id_tipo_traslado==6) {
         $tipo_estado='Available';
    }
    
    
    $rw_post = array();
    $rw_post['Action'] ='Close';
    $rw_post['TicketNumber'] ="$order_id";
    $rw_post['VehicleStatus'] ="$tipo_estado";//'Available';
    $rw_post['LocationIn'] ="$locacion_destino";
    $rw_post['DateTimeIn'] = date('Y-m-d')."T".date('H:i');

    $rw_post['OdometerIn'] ="$odometro";
    $rw_post['FuelLevelIn'] ="$fuellevel";


    $rw_salida=rw_api($rw_post,"NonRev");
    if ($rw_salida['pcode']==1) {

    } else {
       
    }

    return $rw_salida;

}


function rw_combustible_convertir($combustible){
    $salida=0;

    switch ($combustible) {
        case 'E': $salida=0; break;
        case '1/16': $salida=1; break;
        case '1/8': $salida=1; break;
        case '3/16': $salida=2; break;
        case '1/4': $salida=2; break;
        case '5/16': $salida=3; break;
        case '3/8': $salida=3; break;
        case '7/16': $salida=4; break;
        case '1/2': $salida=4; break;
        case '9/16': $salida=5; break;
        case '5/8': $salida=5; break;
        case '11/16': $salida=6; break;
        case '3/4': $salida=6; break;
        case '13/16': $salida=7; break;
        case '7/8': $salida=7; break;
        case '15/16': $salida=8; break;
        case 'F': $salida=8; break;

    }

    return $salida;
}
   
?>