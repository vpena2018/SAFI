<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
 
require_once ('include/framework.php');  
pagina_permiso(8);

$stud_arr[0]["pcode"] = 0;
$stud_arr[0]["pmsg"] ="Error";
$stud_arr[0]["pdata"] ="";

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {salida_json($stud_arr);    exit;}
if (isset($_REQUEST['idrep'])) { $reporte = intval($_REQUEST['idrep']); } else   {salida_json($stud_arr);exit;}
if (isset($_REQUEST['nombrerep'])) { $nombre_reporte = ($_REQUEST['nombrerep']); } else   {$nombre_reporte ='';}


if (isset($_REQUEST['id_producto'])) { $id_producto = intval($_REQUEST['id_producto']); } else   {$id_producto ='';}
if (isset($_REQUEST['fdesde'])) { $fdesde = sanear_date($_REQUEST['fdesde']); } else   {$fdesde ='';}
if (isset($_REQUEST['fhasta'])) { $fhasta = sanear_date($_REQUEST['fhasta']); } else   {$fhasta ='';}
if (isset($_REQUEST['id_tienda'])) { $id_tienda = intval($_REQUEST['id_tienda']); } else   {$id_tienda ='';}
if (isset($_REQUEST['id_tecnico'])) { $id_tecnico = intval($_REQUEST['id_tecnico']); } else   {$id_tecnico ='';}
if (isset($_REQUEST['id_lavador'])) { $id_lavador = intval($_REQUEST['id_lavador']); } else   {$id_lavador ='';}
if (isset($_REQUEST['id_motorista'])) { $id_motorista = intval($_REQUEST['id_motorista']); } else   {$id_motorista ='';}

if (isset($_REQUEST['cliente_id'])) { $cliente_id = intval($_REQUEST['cliente_id']); } else   {$cliente_id ='';}
//if (isset($_REQUEST['id_tecnico'])) { $id_tecnico = intval($_REQUEST['id_tecnico']); } else   {$id_tecnico ='';}
if (isset($_REQUEST['placa'])) { $placa = $_REQUEST['placa']; } else   {$placa ='';}

if (isset($_REQUEST['id_actividad'])) { $id_actividad = intval($_REQUEST['id_actividad']); } else   {$id_actividad ='';}
if (isset($_REQUEST['averia_coaseguro'])) { $averia_coaseguro = intval($_REQUEST['averia_coaseguro']); } else   {$averia_coaseguro ='';}
if (isset($_REQUEST['averia_deducible'])) { $averia_deducible = intval($_REQUEST['averia_deducible']); } else   {$averia_deducible ='';}

if (isset($_REQUEST['id_tipo_averia'])) { $id_tipo_averia = intval($_REQUEST['id_tipo_averia']); } else   {$id_tipo_averia ='';}
if (isset($_REQUEST['id_tipo_causa'])) { $id_tipo_causa = intval($_REQUEST['id_tipo_causa']); } else   {$id_tipo_causa ='';}
if (isset($_REQUEST['id_tipo_revision'])) { $id_tipo_revision = intval($_REQUEST['id_tipo_revision']); } else   {$id_tipo_revision ='';}

if (isset($_REQUEST['actividad_repuesto'])) { $actividad_repuesto = intval($_REQUEST['actividad_repuesto']); } else   {$actividad_repuesto ='';}

if (isset($_REQUEST['id_estado_cita'])) { $id_estado_cita = intval($_REQUEST['id_estado_cita']); } else   {$id_estado_cita ='';}
if (isset($_REQUEST['id_estado_traslado'])) { $id_estado_traslado = intval($_REQUEST['id_estado_traslado']); } else   {$id_estado_traslado='';}
if (isset($_REQUEST['costo'])) { $costo = intval($_REQUEST['costo']); } else   {$costo='';}
if (isset($_REQUEST['id_estado_os'])) { $id_estado_os = intval($_REQUEST['id_estado_os']); } else   {$id_estado_os='';}
if (isset($_REQUEST['fechac'])) { $fechac = intval($_REQUEST['fechac']); } else   {$fechac='';}

$errores="";
$reporte_datos="";
$sql='';
$where='';
$con_datatable=false;
 $stud_arr[0]["pcode"] = 1;
 $datatable_adicional='';

if (tiene_permiso($reporte)) {

switch ($reporte) {
    case 54://Reporte de Vehiculos / Ultima Inspección

        if (isset($_REQUEST['notodas'])) {
           $where.=' HAVING datos_insp IS NOT NULL  ';
        } 
        $sql="SELECT producto.id,producto.codigo_alterno,producto.nombre
        ,(SELECT concat(inspeccion.fecha,'|',inspeccion.hora_entrada,'|',inspeccion.kilometraje_entrada,'|',inspeccion.combustible_entrada) FROM inspeccion WHERE inspeccion.id_producto=producto.id ORDER BY inspeccion.id DESC LIMIT 1) AS datos_insp 
            FROM producto	
            WHERE habilitado=1 AND ".app_tipo_vehiculo."
            $where
            ";

        $result = sql_select($sql);
        if ($result!=false){
             if ($result -> num_rows > 0) {
                
                $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                
                //HEADER
                $reporte_datos.= "<thead><tr>";
                $reporte_datos.= "<th>Codigo</th>";
                $reporte_datos.= "<th>Vehiculo</th>";
                $reporte_datos.= "<th>Ultima Inspección</th>";
                $reporte_datos.= "<th>Hora</th>";
                $reporte_datos.= "<th>Kilometraje</th>";
                $reporte_datos.= "<th>Combustible</th>";
                $reporte_datos.= "</tr></thead>";
                
                //BODY
                $reporte_datos.= "<tbody>";
                while ($row = $result -> fetch_assoc()) {
                    $dd0="";$dd1="";$dd2="";$dd3="";
                    if (!es_nulo($row['datos_insp'])) {
                        $datos_insp=explode('|',$row['datos_insp']);
                        try {$dd0=formato_fecha_de_mysql($datos_insp[0]); } catch (\Throwable $th) {$dd0=""; }
                        try {$dd1=formato_solohora_de_mysql($datos_insp[1]); } catch (\Throwable $th) {$dd1=""; }
                        try {$dd2=formato_numero($datos_insp[2],0); } catch (\Throwable $th) {$dd2=""; }
                        try {$dd3=$datos_insp[3]; } catch (\Throwable $th) {$dd3=""; }
                    }
                   
                    $reporte_datos.= "<tr>";
                    $reporte_datos.= '<td>'.$row['codigo_alterno'].'</td>';
                    $reporte_datos.= '<td>'.$row['nombre'].'</td>';
                    $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$dd0.'</td>';
                    $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$dd1.'</td>';
                    $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$dd2.'</td>';
                    $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$dd3.'</td>';

                    $reporte_datos.= "</tr>";

                }
                $reporte_datos.= "</tbody>";
                
                //FOOTER
                // $reporte_datos.= "<tfoot>";
                // $reporte_datos.= "</tfoot>";
                $reporte_datos.= "<table>";

                $con_datatable=true;
             }
        }
       
        
        
    break;

    case 55:// 	Reporte de Historial de Mantenimiento de un Vehiculo
        if (es_nulo($id_producto)) {
            $errores.=' Debe seleccionar el vehiculo ';
         } 

         if ($errores=='') {
                     
         $sql="SELECT  servicio.fecha,servicio.numero,servicio.kilometraje,servicio.observaciones

       
         ,servicio_tipo_mant.nombre AS eltipo
         ,servicio_tipo_revision.nombre AS eltiporevision
       
         ,taller.nombre AS taller_nombre
         ,taller.codigo_alterno AS taller_codigo
         FROM servicio
     
         LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
         LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
         LEFT OUTER JOIN entidad taller ON (servicio.id_taller=taller.id)
         
                         
          where servicio.id_producto=$id_producto
             $where
             order by servicio.fecha, servicio.id
             ";
 
         $result = sql_select($sql);
         if ($result!=false){
              if ($result -> num_rows > 0) {
                 
                 $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                 
                 //HEADER
                 
                 $reporte_datos.= "<thead><tr>";
                 $reporte_datos.= "<th>Fecha</th>";
                 $reporte_datos.= "<th>No. Orden</th>";
                 $reporte_datos.= "<th>Kilometraje</th>";
                 $reporte_datos.= "<th>Tipo</th>";
                 $reporte_datos.= "<th>Revisión</th>";
                 $reporte_datos.= "<th>Taller</th>";
                 $reporte_datos.= "<th>Observaciones</th>";
                 $reporte_datos.= "</tr></thead>";
                 
                 //BODY
                 $reporte_datos.= "<tbody>";
                 while ($row = $result -> fetch_assoc()) {
                   
                               
                     $reporte_datos.= "<tr>";
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_numero($row['kilometraje'],0).'</td>';
                     $reporte_datos.= '<td>'.$row['eltipo'].'</td>';
                     $reporte_datos.= '<td>'.$row['eltiporevision'].'</td>';
                     $reporte_datos.= '<td>'.$row['taller_nombre'].'</td>';
                     $reporte_datos.= '<td>'.$row['observaciones'].'</td>';
 
                     $reporte_datos.= "</tr>";
 
                 }
                 $reporte_datos.= "</tbody>";
                 
                 //FOOTER
                 // $reporte_datos.= "<tfoot>";
                 // $reporte_datos.= "</tfoot>";
                 $reporte_datos.= "<table>";
 
                 $con_datatable=true;
              }
         }
        
        } // errores
       
      
    break;

    case 56:// 	Reporte de Ordenes de servicio Facturables
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
        
             
         if (!es_nulo($id_tienda)) {
            $where.=' AND orden_cobro.id_tienda='.$id_tienda;
         } 
        
         if ($errores=='') {
                     
         $sql="SELECT orden_cobro.fecha
         ,orden_cobro.numero
         ,orden_cobro.observaciones
         
         ,entidad.nombre AS cliente
         ,entidad.codigo_alterno AS codcliente
         
         ,servicio.numero AS numeroservicio
         
           FROM orden_cobro
           LEFT OUTER JOIN entidad ON (orden_cobro.id_entidad =entidad.id)
           LEFT OUTER JOIN tienda ON (orden_cobro.id_tienda =tienda.id)
           LEFT OUTER JOIN servicio ON (orden_cobro.id_servicio =servicio.id)

           WHERE orden_cobro.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
         ORDER BY orden_cobro.fecha,orden_cobro.id  
             ";
 
         $result = sql_select($sql);
         if ($result!=false){
              if ($result -> num_rows > 0) {
                 
                 $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                 
                 //HEADER
                 
                 $reporte_datos.= "<thead><tr>";
                 $reporte_datos.= "<th>Fecha</th>";
                 $reporte_datos.= "<th>Numero</th>";
                 $reporte_datos.= "<th>Cliente</th>";
                 $reporte_datos.= "<th>No. Servicio</th>";
                 $reporte_datos.= "<th>Observaciones</th>";
               
                 $reporte_datos.= "</tr></thead>";
                 
                 //BODY
                 $reporte_datos.= "<tbody>";
                 while ($row = $result -> fetch_assoc()) {
                    
                    
                                                           
                                                            
                                                            
                                                          
                                                            
                     $reporte_datos.= "<tr>";
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                     $reporte_datos.= '<td>'.$row['codcliente']. ' '.$row['cliente'].'</td>';
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numeroservicio'].'</td>';
                     $reporte_datos.= '<td ">'.$row['observaciones'].'</td>';
                     
                     
                     $reporte_datos.= "</tr>";
 
                 }
                 $reporte_datos.= "</tbody>";
                 
                 //FOOTER
                 // $reporte_datos.= "<tfoot>";
                 // $reporte_datos.= "</tfoot>";
                 $reporte_datos.= "<table>";
 
                 $con_datatable=true;
              }
         }
        
        } // errores
       
        
    break;

    case 57:// 	Reporte Entradas y Salidas de Vehiculos	
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  mapeo_historial.id_producto='.$id_producto;
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND mapeo.id_tienda='.$id_tienda;
         } 
        
         if ($errores=='') {
                     
         $sql="SELECT mapeo_historial.*
         ,mapeo.zona
         ,mapeo.ubicacion
         ,mapeo_tipo.nombre AS movimiento
         ,producto.codigo_alterno
         ,producto.nombre
         ,producto.placa
     
       FROM mapeo_historial
       LEFT OUTER JOIN mapeo_tipo ON (mapeo_historial.id_tipo =mapeo_tipo.id)
       LEFT OUTER JOIN mapeo ON (mapeo_historial.id_mapeo =mapeo.id)
       LEFT OUTER JOIN producto ON (mapeo_historial.id_producto =producto.id)
       
       WHERE DATE_FORMAT(mapeo_historial.hora,'%Y-%m-%d') BETWEEN '$fdesde' AND '$fhasta'
       $where
       ORDER BY mapeo.hora   
             ";
 
         $result = sql_select($sql);
         if ($result!=false){
              if ($result -> num_rows > 0) {
                 
                 $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                 
                 //HEADER
                 
                 $reporte_datos.= "<thead><tr>";
                 $reporte_datos.= "<th>Fecha Hora</th>";
                 $reporte_datos.= "<th>Movimiento</th>";
                 $reporte_datos.= "<th>Zona</th>";
                 $reporte_datos.= "<th>Ubicación</th>";
                 $reporte_datos.= "<th>Vehiculo</th>";
               
                 $reporte_datos.= "</tr></thead>";
                 
                 //BODY
                 $reporte_datos.= "<tbody>";
                 while ($row = $result -> fetch_assoc()) {
                   
                     $reporte_datos.= "<tr>";
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahora_de_mysql($row['hora']).'</td>';
                     $reporte_datos.= '<td>'.$row['movimiento'].'</td>';
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['zona'].'</td>';
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['ubicacion'].'</td>';
                     $reporte_datos.= '<td>'.$row['codigo_alterno']. ' '.$row['nombre'].'</td>';
                     
                     $reporte_datos.= "</tr>";
 
                 }
                 $reporte_datos.= "</tbody>";
                 
                 //FOOTER
                 // $reporte_datos.= "<tfoot>";
                 // $reporte_datos.= "</tfoot>";
                 $reporte_datos.= "<table>";
 
                 $con_datatable=true;
              }
         }
        
        } // errores
       
         
        
    break;

    case 58:// 	Reportes del Desempeño de los Mecánicos	
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
        
        if (!es_nulo($id_tecnico)) {
            $where2.=' AND  usuario.id='.$id_tecnico;
           // $where.=' AND  xxxxxx.id_tecnico1='.$id_tecnico;
         } 
        
         if (!es_nulo($id_tienda)) {
            $where2.=' AND  usuario.tienda_id='.$id_tienda;
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 
        
         if ($errores=='') {

        $sql2="SELECT usuario.id, usuario.nombre 
        FROM
        usuario
        where usuario.activo=1 and usuario.grupo_id=2
        $where2
        ORDER BY usuario.nombre
        ";

        $result2 = sql_select($sql2);
        if ($result2!=false){
            if ($result2 -> num_rows > 0) {

                $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                                    
                //HEADER
                
                $reporte_datos.= "<thead><tr>";
                $reporte_datos.= "<th>Mecanico</th>";
                $reporte_datos.= "<th>Fecha Completada</th>";
                $reporte_datos.= "<th># Orden Servicio</th>";
                $reporte_datos.= "<th>Tiempo</th>";
                $reporte_datos.= "<th>Finalizada</th>";
                $reporte_datos.= "<th>En Proceso</th>";
                $reporte_datos.= "<th>Preventivo</th>";
                $reporte_datos.= "<th>Correctivo</th>";
           
                $reporte_datos.= "</tr></thead>";
                
                //BODY
                $reporte_datos.= "<tbody>";

                $total_preventivo=0;
                $total_correctivo=0;
                $total_finalizada=0;
                $total_enproceso=0;
                $total_cant_ordenes=0;
               
               while ($row2 = $result2 -> fetch_assoc()) {
                            $cod_tecnico=$row2['id'];
                            $nombre_tecnico=$row2['nombre'];


                             //**** */
                
                            $fecha_ant=false;
                            $cant_ordenes=0;
                            $orden_act=0;
                            $salida=0;
                            $salida_orden=0;
                            $orden_act_numero="";
                            $preventivo=0;
                            $correctivo=0;
                            $finalizada=0;
                            $enproceso=0;
                            $preventivo_etq="";
                            $correctivo_etq="";
                            $finalizada_etq="";
                            $enproceso_etq="";
                            $fecha_completada="";

                            $sql="SELECT servicio_historial_estado.id_servicio
                            , servicio_historial_estado.id_estado
                            ,servicio_historial_estado.fecha
                            ,servicio.numero
                            ,servicio.fecha AS fechaservicio
                            ,servicio.id_estado AS estadoservicio
                            ,servicio.id_tipo_revision
                            ,servicio.fecha_hora_final AS fechacompletado
                            FROM servicio_historial_estado
                            LEFT OUTER JOIN servicio ON (servicio_historial_estado.id_servicio=servicio.id)
                            
                            WHERE  servicio_historial_estado.id_estado IS NOT NULL
                            AND DATE(servicio.fecha_hora_final) BETWEEN '$fdesde' AND '$fhasta'
                            AND (servicio.id_tecnico1=$cod_tecnico OR servicio.id_tecnico2=$cod_tecnico OR servicio.id_tecnico3=$cod_tecnico OR servicio.id_tecnico4=$cod_tecnico)
                            ORDER BY servicio_historial_estado.id_servicio,  servicio_historial_estado.fecha
                            ";

                            $result = $conn -> query($sql);

                            if ($result -> num_rows > 0) {
                                while ($row = $result -> fetch_assoc()) {

                                    if ($orden_act<>$row['id_servicio']) {
                                        if ($orden_act>0) {
                                            $reporte_datos.= "<tr>";
                                            $reporte_datos.= '<td ></td>';                                   
                                            $reporte_datos.= '<td align="center">'.Formato_fechahora_de_mysql($fecha_completada).'</td>';
                                            $reporte_datos.= '<td align="center">'.$orden_act_numero.'</td>';                                            
                                            $reporte_datos.= '<td align="center">'.minutos_a_hora($salida_orden).'</td>';
                                            $reporte_datos.= '<td align="center">'.$finalizada_etq.'</td>';
                                            $reporte_datos.= '<td align="center">'.$enproceso_etq.'</td>';
                                            $reporte_datos.= '<td align="center">'.$preventivo_etq.'</td>';
                                            $reporte_datos.= '<td align="center">'.$correctivo_etq.'</td>';                                            
                                            $reporte_datos.= "</tr>";
                                            $salida_orden=0;
                                        } else {
                                            $reporte_datos.= '<tr class="tbl_grp_head">';                                            
                                            $reporte_datos.= '<td >['.$cod_tecnico.'] '.$nombre_tecnico.'</td>';                                   
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= '<td align="center"></td>';
                                            $reporte_datos.= "</tr>";
                                        }
                                        $orden_act=$row['id_servicio'];
                                        $orden_act_numero=$row['numero'];
                                        $fecha_completada=$row['fechacompletado'];
                                        $cant_ordenes++;
                                        $total_cant_ordenes++;
                                        $fecha_ant=false;

                                        //----
                                        $preventivo_etq="";  $correctivo_etq="";  $finalizada_etq="";  $enproceso_etq="";
                                       
                                        if ($row['estadoservicio']>20) {
                                            $finalizada++;
                                            $total_finalizada++;
                                            $finalizada_etq="*";
                                        }

                                        if ($row['estadoservicio']==4) {
                                            $enproceso++;
                                            $total_enproceso++;
                                            $enproceso_etq="*";
                                        }

                                        if ($row['id_tipo_revision']==9) {
                                            $correctivo++;
                                            $total_correctivo++;
                                            $correctivo_etq="*";
                                        } else {
                                            $preventivo++;
                                            $total_preventivo++;
                                            $preventivo_etq="*";
                                        }

                                        //----
                                        
                                    }


                                    $fecha_act=strtotime( $row['fecha'] ) ;

                                    if ($fecha_ant==false) {
                                    
                                    } else {
                                        $minutos= get_minutos_fechas($fecha_act , $fecha_ant);
                                        $salida+=$minutos;
                                        $salida_orden+=$minutos;
                                        $fecha_ant=false;
                                    }

                                    if ($row['id_estado']==4) {
                                        $fecha_ant=strtotime( $row['fecha'] ) ;
                                    }

                                    
                                    
                                }  

                                if ($fecha_ant==true) {
                                    
                                } else {                                                    
                                    //$minutos= get_minutos_fechas($fecha_act, $fecha_ant) ;
                                    //$salida+=$minutos;
                                    //$salida_orden+=$minutos;
                                    //$fecha_ant=false;                                    
                                    $reporte_datos.= "<tr>";
                                    $reporte_datos.= '<td ></td>';                  
                                    $reporte_datos.= '<td align="center">'.Formato_fechahora_de_mysql($fecha_completada).'</td>';                 
                                    $reporte_datos.= '<td align="center" >'.$orden_act_numero.'</td>';
                                    $reporte_datos.= '<td align="center" >'.minutos_a_hora($salida_orden).'</td>';                                   
                                    $reporte_datos.= '<td align="center">'.$finalizada_etq.'</td>';
                                    $reporte_datos.= '<td align="center">'.$enproceso_etq.'</td>';
                                    $reporte_datos.= '<td align="center">'.$preventivo_etq.'</td>';
                                    $reporte_datos.= '<td align="center">'.$correctivo_etq.'</td>';
                                    $reporte_datos.= "</tr>";
                                }

                            }

                    
                            //$reporte_datos.= "<tr class="tbl_grp_foot">";     
                            if ($cant_ordenes>0){
                                $reporte_datos.= "<tr>";     
                                $reporte_datos.= '<td></td>';                                                                     
                                $reporte_datos.= '<td align="right">Total '.$nombre_tecnico.':</td>';                                   
                                $reporte_datos.= '<td align="center" ><b>'.$cant_ordenes.'</b></td>';
                                $reporte_datos.= '<td align="center" >'.minutos_a_hora($salida).'</td>';
                                $reporte_datos.= '<td align="center">'.$finalizada.'</td>';
                                $reporte_datos.= '<td align="center">'.$enproceso.'</td>';
                                $reporte_datos.= '<td align="center">'.$preventivo.'</td>';
                                $reporte_datos.= '<td align="center">'.$correctivo.'</td>';
                                $reporte_datos.= "</tr>";
                            }                            
                            //**** */

                }    
                $reporte_datos.= "</tbody>";
         
      
                //FOOTER
                 
                 $reporte_datos.= "<tfoot>";
                 $reporte_datos.= '<tr class="bg-color-footer">';
                 $reporte_datos.= '<td align="right">Gran Total:</td>';                                   
                 $reporte_datos.= '<td align="center" ></td>';
                 $reporte_datos.= '<td align="center" >'.$total_cant_ordenes.'</td>';
                 $reporte_datos.= '<td align="center" ></td>';
                 $reporte_datos.= '<td align="center">'.$total_finalizada.'</td>';
                 $reporte_datos.= '<td align="center">'.$total_enproceso.'</td>';
                 $reporte_datos.= '<td align="center">'.$total_preventivo.'</td>';
                 $reporte_datos.= '<td align="center">'.$total_correctivo.'</td>';
                 $reporte_datos.= "</tr>";
                 $reporte_datos.= "</tfoot>";

                 $reporte_datos.= "<table>";

                 $con_datatable=true;                           
                 $datatable_adicional='"bSort": false,';
     

                // $datatable_adicional="
                // rowGroup: {
                //     endRender: function ( rows, group ) {
                //         var cnt =  rows.count();

                //         var tot = rows
                //         .data()
                //         .pluck(3)
                //         .reduce( function (a, b) {
                //             return a + b.replace(/[^\d]/g, '')*1;
                //         }, 0)

                //         var totminutos= tot;
         
                //         return  $('<tr/>')
                //     .append( '<td >Total '+group+'</td>' )
                //     .append( '<td align=\"center\">'+cnt.toFixed(0)+'</td>' )                    
                //     .append( '<td align=\"center\">'+totminutos.toFixed(2)+'</td>' )
                //     .append( '<td/>' );
                //     },
                //     dataSrc: 0
                // },
                // ";
            }

        }                    
         
        
     } // errores
        
        
    break;


case 73:// 	Reporte de averias	
        $where2='';
        $having='';
        $campoadd='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        if (!es_nulo($id_producto)) {
            $where.=' AND  averia.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  averia.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  averia.id_tienda='.$id_tienda;
         } 

         if (!es_nulo($id_tipo_averia)) {
            $where.=' AND  averia.id_tipo='.$id_tipo_averia;
         } 

         if (!es_nulo($id_tipo_causa)) {
            $where.=' AND  averia.id_tipo_causa='.$id_tipo_causa;
         } 


         if (!es_nulo($id_actividad)) {
         
            $having='HAVING actividad>0';
            $campoadd=",(SELECT COUNT(*) FROM averia_detalle WHERE averia_detalle.id_maestro=averia.id AND averia_detalle.id_producto='".$id_actividad."') AS actividad";
            
            
         } 

         if (!es_nulo($averia_coaseguro)) {
             if ($averia_coaseguro==1) {$where.=' AND  averia.coseguro>0';}
             if ($averia_coaseguro==2) {$where.=' AND  (averia.coseguro<=0 or averia.coseguro is null )';}
            
         } 

         if (!es_nulo($averia_deducible)) {
            if ($averia_deducible==1) {$where.=' AND  averia.deducible>0';}
            if ($averia_deducible==2) {$where.=' AND  (averia.deducible<=0 or averia.deducible is null )';}
           
        } 

         
        
         if ($errores=='') {
                     
            $sql="SELECT averia.id, averia.fecha,  averia.numero, averia.numero_alterno
              ,(IFNULL(averia.coseguro,0)+IFNULL(averia.deducible,0)) as totalseguro
              ,averia.total
              ,averia.total_costo
              ,averia.id_tipo
              ,averia.contacto
                ,producto.codigo_alterno,producto.nombre,producto.placa
                ,averia_estado.nombre AS elestado
                ,averia_tipo.nombre AS eltipo
                ,entidad.nombre as elcliente
                ,averia_tipo_causa.nombre AS lacausa
                $campoadd
                FROM averia
                LEFT OUTER JOIN producto ON (averia.id_producto=producto.id)
                LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
                LEFT OUTER JOIN averia_tipo ON (averia.id_tipo=averia_tipo.id)
                LEFT OUTER JOIN averia_estado ON (averia.id_estado=averia_estado.id)
                LEFT OUTER JOIN averia_tipo_causa ON (averia.id_tipo_causa=averia_tipo_causa.id)
                
              
          WHERE averia.fecha BETWEEN '$fdesde' AND '$fhasta'
          $where 
          $having 
          order by averia.fecha , averia.id 
          
                ";
    
            $result = sql_select($sql);
            $isv=0;
            $gastos_admon=0;
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Contacto</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Avería #</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Tipo Causa</th>";                  
                    $reporte_datos.= "<th>Cubierto x Seguro/ Cobro</th>";
                    $reporte_datos.= "<th>Costo Total</th>";
                    $reporte_datos.= "<th>Venta Total</th>";

                    $reporte_datos.= "<th>Gastos Admon.</th>";
                    $reporte_datos.= "<th>ISV</th>";
                  
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td>'.$row['contacto'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['eltipo'].'</td>';
                        $reporte_datos.= '<td>'.$row['lacausa'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['totalseguro'],2).'</td>';
                        $reporte_datos.= '<td align="right"> '.formato_numero($row['total_costo'],2).'</td>';
                        $totalventa="";
                        if ($row['id_tipo']<>2 and $row['id_tipo']<>3) {$totalventa=formato_numero($row['total'],2);}
                        $reporte_datos.= '<td align="right"> '.$totalventa.'</td>';

                        $eltotal=$row['total']; if (es_nulo($eltotal)) {$eltotal=0;}
                        $isv=$eltotal*$_SESSION['p_isv'];
                        $gastos_admon=$eltotal*( $_SESSION['p_gasto_admon']);                        
                        if (!es_nulo($row['id_tipo'])) {
                            $tipo_averia=$row['id_tipo'];
                            //solo averias cobrables pagan ISV y GA
                            if ($tipo_averia==2 or $tipo_averia==3) { 
                                $isv=0;
                                $gastos_admon=0; 
                            }
                            //4 Cobrable sin ISV sin Gastos Administrativos
                            if ($tipo_averia==4) { 
                                $isv=0;
                                $gastos_admon=0;   
                            }
                            //5 Cobrable sin ISV con Gastos Administrativos
                            if ($tipo_averia==5) { 
                                $isv=0;                                
                                
                            }
                            //6 Cobrable sin Gastos Administrativos con ISV
                            if ($tipo_averia==6) {                                 
                                $gastos_admon=0; 
                            }
                        }
                        $reporte_datos.= '<td align="right"> '.$gastos_admon.'</td>';
                        $reporte_datos.= '<td align="right"> '.$isv.'</td>';

                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 74:// 	Reporte de orden servicio	
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }         
 
        if (!es_nulo($fechac)){
            if ($fechac=='01'){
                $where ='  date(servicio.fecha) BETWEEN '."'$fdesde'".' AND '."'$fhasta'";            
            }else{
                $where ='  date(servicio.fecha_hora_final) BETWEEN '."'$fdesde'".' AND '."'$fhasta'";            
            } 
        }

        if (!es_nulo($id_producto)) {
            $where.=' AND  servicio.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  servicio.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 

         if (!es_nulo($id_tipo_revision)) {
            $where.=' AND  servicio.id_tipo_revision='.$id_tipo_revision;
         } 

         if (!es_nulo($id_estado_os) and $costo=='02') {
            $where.=' AND  servicio.id_estado='.$id_estado_os;
         }  

         if (!es_nulo($id_tecnico)) {
            $where.=' AND  (servicio.id_tecnico1='.$id_tecnico." OR servicio.id_tecnico2=".$id_tecnico." OR servicio.id_tecnico3=".$id_tecnico." OR servicio.id_tecnico4=".$id_tecnico.")";
         } 
        
         if ($errores=='') {
            if ($costo=='01'){        
                $sql="SELECT servicio.id, servicio.fecha, servicio.fecha_hora_final ,servicio.numero, servicio.numero_alterno
                ,producto.codigo_alterno,producto.nombre,producto.placa
                ,servicio_estado.nombre AS elestado
                ,servicio_tipo_mant.nombre AS eltipo
                ,servicio.kilometraje
                ,servicio_tipo_revision.nombre AS elrevision
                ,entidad.nombre as elcliente
                ,taller.nombre as eltaller
                ,tec1.nombre as tecnombre1
                ,tec2.nombre as tecnombre2
                ,tec3.nombre as tecnombre3 
                ,tec4.nombre as tecnombre4                
                ,(
                    SELECT  
                    sum(ifnull(servicio_detalle.cantidad,0)*ifnull(servicio_detalle.precio_costo,0) ) as costo  
                    FROM servicio_detalle
                    WHERE servicio_detalle.id_servicio=servicio.id
                    AND servicio_detalle.estado <>4
                ) AS total_costo
                
                FROM servicio
                LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
                LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
                LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
                LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
                LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
                LEFT OUTER JOIN entidad taller ON (servicio.id_taller=taller.id)  
                LEFT OUTER JOIN usuario tec1 on (servicio.id_tecnico1=tec1.id) 
                LEFT OUTER JOIN usuario tec2 on (servicio.id_tecnico2=tec2.id)
                LEFT OUTER JOIN usuario tec3 on (servicio.id_tecnico3=tec3.id)   
                LEFT OUTER JOIN usuario tec4 on (servicio.id_tecnico3=tec4.id)                                                             
                WHERE /*date(servicio.fecha_hora_final) BETWEEN '$fdesde' AND '$fhasta'*/
                $where
                order by servicio.fecha , servicio.id 
                ";// servicio.fecha
            }else{
                $sql="SELECT servicio.id, servicio.fecha, servicio.fecha_hora_final, servicio.numero, servicio.numero_alterno
                ,producto.codigo_alterno,producto.nombre,producto.placa
                ,servicio_estado.nombre AS elestado
                ,servicio_tipo_mant.nombre AS eltipo
                ,servicio.kilometraje
                ,servicio_tipo_revision.nombre AS elrevision
                ,entidad.nombre as elcliente
                ,taller.nombre as eltaller
                ,tec1.nombre as tecnombre1
                ,tec2.nombre as tecnombre2
                ,tec3.nombre as tecnombre3 
                ,tec4.nombre as tecnombre4 
                ,0 as total_costo
                FROM servicio
                LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
                LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
                LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
                LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
                LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
                LEFT OUTER JOIN entidad taller ON (servicio.id_taller=taller.id) 
                LEFT OUTER JOIN usuario tec1 on (servicio.id_tecnico1=tec1.id) 
                LEFT OUTER JOIN usuario tec2 on (servicio.id_tecnico2=tec2.id)
                LEFT OUTER JOIN usuario tec3 on (servicio.id_tecnico3=tec3.id)  
                LEFT OUTER JOIN usuario tec4 on (servicio.id_tecnico4=tec4.id)                                              
                WHERE /* date(servicio.fecha) BETWEEN '$fdesde' AND '$fhasta'*/
                $where
                order by servicio.fecha , servicio.id 
                ";// servicio.fecha date(servicio.fecha) BETWEEN '$fdesde' AND '$fhasta'
                 
            }    
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha Creacion</th>";
                    $reporte_datos.= "<th>Fecha Completado</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Servicio #</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Tipo Revision</th>";
                    $reporte_datos.= "<th>Taller</th>";                    
                    $reporte_datos.= "<th>Kilometraje</th>";
                    $reporte_datos.= "<th>Total Costo</th>";
                    $reporte_datos.= "<th>Tecnico 1</th>";
                    $reporte_datos.= "<th>Tecnico 2</th>";
                    $reporte_datos.= "<th>Tecnico 3</th>";  
                    $reporte_datos.= "<th>Tecnico 4</th>";                     
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha_hora_final']).'</td>'; 
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td>'.$row['nombre'].'</td>';  
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elrevision'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['eltaller'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';
                        $reporte_datos.= '<td align="right"> '.formato_numero($row['total_costo'],2).'</td>';
                        $reporte_datos.= '<td>'.$row['tecnombre1'].'</td>'; 
                        $reporte_datos.= '<td>'.$row['tecnombre2'].'</td>'; 
                        $reporte_datos.= '<td>'.$row['tecnombre3'].'</td>'; 
                        $reporte_datos.= '<td>'.$row['tecnombre4'].'</td>'; 
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 78:// 	Reporte de desempeno lavadores
        $where2="";
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
        if (!es_nulo($id_lavador)) {
            $where.=' AND  (orden_lavado.id_lavador='.$id_lavador.' OR orden_lavado.id_lavador2='.$id_lavador.")";
         } 

         if (!es_nulo($id_tienda)) {
            $where.=' AND orden_lavado.id_tienda='.$id_tienda;
         } 


         if ($errores=='') {
                     
         $sql="SELECT orden_lavado.* 
         ,producto.codigo_alterno,producto.nombre,producto.placa
         ,orden_lavado_estado.nombre AS elestado
         ,l1.nombre AS lavador1
         ,l2.nombre  AS lavador2
     
         FROM orden_lavado
         LEFT OUTER JOIN producto ON (orden_lavado.id_producto=producto.id)
         LEFT OUTER JOIN orden_lavado_estado ON (orden_lavado.id_estado=orden_lavado_estado.id)
         LEFT OUTER JOIN usuario l1 ON (orden_lavado.id_lavador=l1.id)
         LEFT OUTER JOIN usuario l2 ON (orden_lavado.id_lavador2=l2.id)
         WHERE 1=1                         
         AND orden_lavado.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
          
         ORDER BY orden_lavado.fecha,orden_lavado.numero
             ";
 
        $result = sql_select($sql);
        if ($result!=false){
            if ($result -> num_rows > 0) {
                
                $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                
                //HEADER
                
                $reporte_datos.= "<thead><tr>";
                $reporte_datos.= "<th>Fecha</th>";
                $reporte_datos.= "<th>Lavador</th>";                
                $reporte_datos.= "<th>Vehiculo</th>";
                $reporte_datos.= "<th># Orden</th>";
                $reporte_datos.= "<th>Lavados</th>";
                $reporte_datos.= "<th>Aspirados</th>";
                $reporte_datos.= "<th>Champuseados</th>"; 
                $reporte_datos.= "<th>Chasis</th>";   
                $reporte_datos.= "<th>Detallado</th>"; 
                $reporte_datos.= "<th>Champuseado Seco</th>"; 
                $reporte_datos.= "<th>Express</th>"; 
                $reporte_datos.= "</tr></thead>";
                
                $lavado=0;
                $aspirado=0;
                $shampoo=0;
                $chasis=0; 
                $detallado=0;  
                $shampuseado_seco=0;

                //BODY
                $reporte_datos.= "<tbody>";
                while ($row = $result -> fetch_assoc()) {
                
                            
                    $reporte_datos.= "<tr>";
                    $reporte_datos.= '<td align="center" >'.formato_fecha_de_mysql($row['fecha']).'</td>';
                    $reporte_datos.= '<td style="">'.$row['lavador1'].'<br>'.$row['lavador2'].'</td>';
                    $reporte_datos.= '<td align="center" >'.$row['codigo_alterno'].'</td>';
                    $reporte_datos.= '<td align="center" >'.$row['numero'].'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_lavado']).'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_aspirado']).'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_shampoo']).'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_chasis']).'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_detallado']).'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_shampuseado_seco']).'</td>';
                    $reporte_datos.= '<td align="center" >'.valores_sino($row['lavado_express']).'</td>';
        

                    $reporte_datos.= "</tr>";
                    
                    $lavado+=$row['lavado_lavado'];
                    $aspirado+=$row['lavado_aspirado'];
                    $shampoo+=$row['lavado_shampoo'];
                    $chasis+=$row['lavado_chasis'];
                    $detallado+=$row['lavado_detallado'];
                    $shampuseado_seco+=$row['lavado_shampuseado_seco'];
                    $lavado_express+=$row['lavado_express'];
                }
                $reporte_datos.= "</tbody>";
                
                //FOOTER
                $reporte_datos.= "<tfoot>";
                $reporte_datos.= '<tr class="tbl_grp_foot">';
                $reporte_datos.= '<td ></td>';
                    $reporte_datos.= '<td >TOTALES</td>';
                    $reporte_datos.= '<td ></td>';
                    $reporte_datos.= '<td ></td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($lavado,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($aspirado,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($shampoo,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($chasis,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($detallado,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($shampuseado_seco,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.formato_numero($lavado_express,0).'</td>';
                    $reporte_datos.= '<td align="center" >'.'</td>';
                    
        

                    $reporte_datos.= "</tr>";
                $reporte_datos.= "</tfoot>";

                $reporte_datos.= "<table>";

                $con_datatable=true;
            }
        }

        } // errores


        break;



    case 79:// 	Reporte de desempeno motoristas
        $where="";
        $total=0;
        $renta=0;
        $taller=0;
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
        if (!es_nulo($id_motorista)) {
            $where.=' AND  (inspeccion.id_usuario='.$id_motorista.")";
         } 

         if (!es_nulo($id_tienda)) {
            $where.=' AND inspeccion.id_tienda='.$id_tienda;
         } 


         if ($errores=='') {
                     
        //  $sql="SELECT usuario.id, usuario.nombre 
        //  ,SUM(1) AS total
        //  ,SUM(if(inspeccion.tipo_inspeccion=2,1,0)) AS taller
        //  ,SUM(if(inspeccion.tipo_inspeccion=1,1,0)) AS renta
        //  FROM inspeccion
        //  LEFT OUTER JOIN usuario ON (inspeccion.id_usuario=usuario.id)
         
                         
        //  WHERE inspeccion.fecha BETWEEN '$fdesde' AND '$fhasta'
        //     $where
        //     GROUP BY inspeccion.id_usuario
        //  ORDER BY usuario.nombre
        //      ";


         $sql="SELECT usuario.id, usuario.nombre 
         , inspeccion.fecha
         , inspeccion.numero
            ,(if(inspeccion.tipo_inspeccion=2,'Taller','Renta')) AS tipo
   
            
            ,inspeccion_estado.nombre AS elestado
            ,l2.nombre as finalizador
            FROM inspeccion
            LEFT OUTER JOIN inspeccion_estado ON (inspeccion.id_estado=inspeccion_estado.id)
            LEFT OUTER JOIN usuario ON (inspeccion.id_usuario=usuario.id)
            LEFT OUTER JOIN usuario l2 ON (inspeccion.id_usuario_completado=l2.id)
            
                         
         WHERE inspeccion.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
     
         ORDER BY usuario.nombre
             ";
 
         $result = sql_select($sql);
         if ($result!=false){
              if ($result -> num_rows > 0) {
                 
                 $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                 
                                    
                 //HEADER
                 
                 $reporte_datos.= "<thead><tr>";
                 $reporte_datos.= "<th>Fecha</th>";
                 $reporte_datos.= "<th>Motorista Creador</th>";
                 $reporte_datos.= "<th>Motorista Finalizador</th>";
                 $reporte_datos.= "<th>Orden #</th>";
                 $reporte_datos.= "<th>Tipo</th>";  
                 $reporte_datos.= "<th>Estado</th>";   
                 $reporte_datos.= "</tr></thead>";
                 
                 //BODY
                 $reporte_datos.= "<tbody>";
                 while ($row = $result -> fetch_assoc()) {
                   
                               
                     $reporte_datos.= "<tr>";
                     $reporte_datos.= '<td style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                     $reporte_datos.= '<td align="center" >'.$row['nombre'].'</td>';
                     $reporte_datos.= '<td align="center" >'.$row['finalizador'].'</td>';
                     $reporte_datos.= '<td align="center" >'.$row['numero'].'</td>';
                     $reporte_datos.= '<td align="center" >'.$row['tipo'].'</td>';
                     $reporte_datos.= '<td align="center" >'.$row['elestado'].'</td>';
           
 
                     $reporte_datos.= "</tr>";
                    
                    //  $total+=$row['total'];
                    // $renta+=$row['renta'];
                    // $taller+=$row['taller'];
                 }
                 $reporte_datos.= "</tbody>";
                 
                 //FOOTER
                //  $reporte_datos.= "<tfoot>";
                //  $reporte_datos.= '<tr class="tbl_grp_foot">';
                //      $reporte_datos.= '<td >TOTALES</td>';
                //      $reporte_datos.= '<td align="center" >'.formato_numero($renta,0).'</td>';
                //      $reporte_datos.= '<td align="center" >'.formato_numero($taller,0).'</td>';
                //      $reporte_datos.= '<td align="center" >'.formato_numero($total,0).'</td>';
                     
           
 
                //      $reporte_datos.= "</tr>";
                //  $reporte_datos.= "</tfoot>";

                 $reporte_datos.= "<table>";
 
                 $con_datatable=true;
              }
         }
        
        } // errores
        
      
    break;





    case 102:// 	Reporte de Actividades / Horas de los Mecánicos

        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
        if (!es_nulo($id_tecnico)) {
            $where.=' AND (servicio.id_tecnico1='.$id_tecnico.' OR servicio.id_tecnico2='.$id_tecnico.' OR servicio.id_tecnico3='.$id_tecnico.')' ;
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 
        
         if ($errores=='') {
                     
            $sql="SELECT 
            t1.nombre AS tecnico
        --    t1.nombre AS tecnico
         --   ,t2.nombre AS tecnico2
         --   ,t3.nombre AS tecnico3
            ,servicio.fecha AS fecha_orden
            ,servicio.numero AS numero_orden
            ,servicio_detalle.producto_nombre AS actividad 
            ,producto.tipo_mant
            ,producto.horas
            ,servicio_detalle.horas_atender
            
            FROM servicio_detalle 
            LEFT OUTER JOIN producto ON (servicio_detalle.id_producto=producto.id)
            LEFT OUTER JOIN servicio ON (servicio_detalle.id_servicio=servicio.id)
            LEFT OUTER JOIN usuario t1 ON (servicio_detalle.id_usuario_atender=t1.id)
        --  LEFT OUTER JOIN usuario t1 ON (servicio.id_tecnico1=t1.id)
		--	LEFT OUTER JOIN usuario t2 ON (servicio.id_tecnico2=t2.id)
		--	LEFT OUTER JOIN usuario t3 ON (servicio.id_tecnico3=t3.id) 
            WHERE DATE(servicio_detalle.fecha_atender_fin) BETWEEN '$fdesde' AND '$fhasta'
            AND servicio_detalle.id_usuario_atender IS NOT null
            and SUBSTRING(codigo_alterno,1,3)='ATM'
            $where
            order by servicio_detalle.id_usuario_atender, servicio.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Técnico</th>";
                    $reporte_datos.= "<th>Fecha Orden</th>";
                    $reporte_datos.= "<th>Orden No.</th>";
                    $reporte_datos.= "<th>Actividad</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Horas Planeadas</th>";
                    $reporte_datos.= "<th>Horas Reales</th>";
                  
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    $horas_planeadas=0;
                    $horas_reales=0;
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['tecnico'].'</td>'; //.'<br>'.$row['tecnico2'].'<br>'.$row['tecnico3']
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha_orden']).'</td>';
                        $reporte_datos.= '<td align="center"> '.$row['numero_orden'].'</td>';
                        $reporte_datos.= '<td>'.$row['actividad'].'</td>';
                        $reporte_datos.= '<td align="center"> '.$row['tipo_mant'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['horas'],2).'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['horas_atender'],2).'</td>';

                        $reporte_datos.= "</tr>";
                        $horas_planeadas+=floatval($row['horas']);
                        $horas_reales +=floatval($row['horas_atender']);    
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    $reporte_datos.= "<tfoot>";
                     $reporte_datos.= '<tr class="tbl_grp_foot">';
                     $reporte_datos.= '<td colspan="5" align="right" >TOTALES</td>';
                     
                     $reporte_datos.= '<td align="center" >'.formato_numero($horas_planeadas,2).'</td>';
                     $reporte_datos.= '<td align="center" >'.formato_numero($horas_reales,2).'</td>';
                     
           
 
                     $reporte_datos.= "</tr>";
                        $reporte_datos.= "</tfoot>";

                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 103:// 	Reporte de orden servicio	DETALLADO
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  servicio.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  servicio.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 

        if (!es_nulo($actividad_repuesto)) {
            $where.=' AND  servicio_detalle.producto_tipo='.$actividad_repuesto;
         } 
              
         if ($errores=='') {
                     
            $sql="SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,servicio_estado.nombre AS elestado
            ,servicio_tipo_mant.nombre AS eltipo
            ,entidad.nombre as elcliente
            
            ,servicio_detalle.producto_nombre
            ,servicio_detalle.precio_costo
            ,servicio_detalle.cantidad
           
            ,servicio.kilometraje
            ,servicio_tipo_revision.nombre AS elrevision
            
            
            FROM servicio_detalle
            LEFT OUTER JOIN servicio ON (servicio.id=servicio_detalle.id_servicio)
            LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
            LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
            LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
            LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
            LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
            WHERE servicio.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
            order by servicio.fecha , servicio.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Servicio #</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Cant.</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Tipo Revision</th>";
                    $reporte_datos.= "<th>Kilometraje</th>";
                    $reporte_datos.= "<th>Costo</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
                        $reporte_datos.= '<td align="center"> '.$row['cantidad'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['producto_nombre'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elrevision'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';
                        $elcosto=$row['precio_costo'];
                        // ,(select orden_compra_detalle.precio_costo FROM orden_compra_detalle WHERE orden_compra_detalle.id_servicio=servicio_detalle.id_servicio AND orden_compra_detalle.id_producto=servicio_detalle.id_producto LIMIT 1 ) AS costocompra
                        // if (!es_nulo($row['costocompra'])) {
                        //      $elcosto=$row['costocompra'];
                        // }
                        $totalcosto=$elcosto*$row['cantidad'];
                        $reporte_datos.= '<td align="right"> '.formato_numero($totalcosto,2).'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;


    // case 103:// 	Reporte de orden servicio	DETALLADO
    //     $where2='';
    //     if (es_nulo($fdesde) or es_nulo($fhasta)) {
    //         $errores.="debe ingresar las fechas";
    //     }
          
        
    //     if (!es_nulo($id_producto)) {
    //         $where.=' AND  servicio.id_producto='.$id_producto;
    //      } 

    //      if (!es_nulo($cliente_id)) {
    //         $where.=' AND  servicio.cliente_id='.$cliente_id;
    //      } 

    //      if (!es_nulo($placa)) {
    //         $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
    //      } 
        
    //      if (!es_nulo($id_tienda)) {
    //         $where.=' AND  servicio.id_tienda='.$id_tienda;
    //      } 

    //     if (!es_nulo($actividad_repuesto)) {
    //         $where.=' AND  servicio_detalle.producto_tipo='.$actividad_repuesto;
    //      } 
              
    //      if ($errores=='') {
                     
    //         $sql="SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
    //         ,producto.codigo_alterno,producto.nombre,producto.placa
    //         ,servicio_estado.nombre AS elestado
    //         ,servicio_tipo_mant.nombre AS eltipo
    //         ,entidad.nombre as elcliente
            
    //         ,servicio_detalle.producto_nombre
    //         ,servicio_detalle.precio_costo
            
            
    //         FROM servicio_detalle
    //         LEFT OUTER JOIN servicio ON (servicio.id=servicio_detalle.id_servicio)
    //         LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
    //         LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
    //         LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
    //         LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
    //         WHERE servicio.fecha BETWEEN '$fdesde' AND '$fhasta'
    //         $where
    //         order by servicio.fecha , servicio.id 
   
    //             ";
 
    //         $result = sql_select($sql);
    //         if ($result!=false){
    //              if ($result -> num_rows > 0) {
                    
    //                 $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
    //                 //HEADER
                    
    //                 $reporte_datos.= "<thead><tr>";
    //                 $reporte_datos.= "<th>Fecha</th>";
    //                 $reporte_datos.= "<th>Cliente</th>";
    //                 $reporte_datos.= "<th>Vehiculo</th>";
    //                 $reporte_datos.= "<th>Servicio #</th>";
    //                 $reporte_datos.= "<th>Tipo</th>";
    //                 $reporte_datos.= "<th>Descripcion</th>";
    //                 $reporte_datos.= "<th>Estado</th>";
    //                 $reporte_datos.= "<th>Costo</th>";
    //                 $reporte_datos.= "</tr></thead>";
                    
    //                 //BODY
    //                 $reporte_datos.= "<tbody>";
    //                 while ($row = $result -> fetch_assoc()) {
                       
                      
    //                     $reporte_datos.= "<tr>";
    //                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
    //                     $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
    //                     $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
    //                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
    //                     $reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
    //                     $reporte_datos.= '<td align=""> '.$row['producto_nombre'].'</td>';
    //                     $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
    //                     $reporte_datos.= '<td align="right"> '.formato_numero($row['precio_costo'],2).'</td>';
    //                     $reporte_datos.= "</tr>";
    
    //                 }
    //                 $reporte_datos.= "</tbody>";
                    
    //                 //FOOTER
    //                 // $reporte_datos.= "<tfoot>";
    //                 // $reporte_datos.= "</tfoot>";
    //                 $reporte_datos.= "<table>";
    
    //                 $con_datatable=true;
    //              }
    //         }
           
    //        } // errores
        
        
    // break;





    case 104:// 	Reporte de orden Averia	DETALLADO
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  averia.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  averia.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  averia.id_tienda='.$id_tienda;
         } 

        if (!es_nulo($actividad_repuesto)) {
            $where.=' AND  averia_detalle.producto_tipo='.$actividad_repuesto;
         } 
              
         if ($errores=='') {
                     
            $sql="SELECT averia.id, averia.fecha,  averia.numero, averia.numero_alterno
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,averia_estado.nombre AS elestado
            ,averia_tipo.nombre AS eltipo
            ,entidad.nombre as elcliente
            
            ,averia_detalle.producto_nombre
            ,averia_detalle.precio_costo
            ,averia_detalle.cantidad
            ,averia_detalle.precio_venta
            
            
            FROM averia_detalle
            LEFT OUTER JOIN averia ON (averia.id=averia_detalle.id_maestro)
            LEFT OUTER JOIN producto ON (averia.id_producto=producto.id)
            LEFT OUTER JOIN averia_tipo ON (averia.id_tipo=averia_tipo.id)
            LEFT OUTER JOIN averia_estado ON (averia.id_estado=averia_estado.id)
            LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
            WHERE averia.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
            order by averia.fecha , averia.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Averia #</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Cant.</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Costo</th>";
                    $reporte_datos.= "<th>Venta</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
                        $reporte_datos.= '<td align="center"> '.$row['cantidad'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['producto_nombre'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $elcosto=$row['precio_costo'];
                        $totalcosto=$elcosto*$row['cantidad'];
                        $reporte_datos.= '<td align="right"> '.formato_numero($totalcosto,2).'</td>';
                        $totalventa=$row['precio_venta']*$row['cantidad'];
                        $reporte_datos.= '<td align="right"> '.formato_numero($totalventa,2).'</td>';
                                                
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 118:// 	Reporte Consumo de Combustible	
  
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }       
        
        // if (!es_nulo($id_producto)) {
        //     $where.=' AND  servicio.id_producto='.$id_producto;
        //  } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  orden_combustible.id_tienda='.$id_tienda;
         } 


        
         if ($errores=='') {
                     
            $sql="SELECT orden_combustible.*
            ,entidad.nombre AS elproveedor
            ,orden_combustible_estado.nombre AS elestado
            ,producto.nombre AS vehiculo
            ,producto.codigo_alterno AS codvehiculo
            ,tienda.nombre AS latienda
            ,usuario.nombre AS elautoriza
                FROM orden_combustible
                LEFT OUTER JOIN producto ON (orden_combustible.id_producto=producto.id)
                LEFT OUTER JOIN entidad ON (orden_combustible.id_entidad=entidad.id)
                LEFT OUTER JOIN orden_combustible_estado ON (orden_combustible.id_estado=orden_combustible_estado.id)
                LEFT OUTER JOIN tienda ON (orden_combustible.id_tienda=tienda.id)
                LEFT OUTER JOIN usuario ON (orden_combustible.id_usuario_autoriza=usuario.id)            
            WHERE orden_combustible.fecha BETWEEN '$fdesde' AND '$fhasta'
      
            $where
            order by orden_combustible.fecha , orden_combustible.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Proveedor</th>";
                    $reporte_datos.= "<th>Orden # </th>";
                    $reporte_datos.= "<th>Vehículo</th>";
                    $reporte_datos.= "<th>Descripción vehículo</th>";
                    $reporte_datos.= "<th>Conductor</th>";
                    $reporte_datos.= "<th>Destino</th>";
                    $reporte_datos.= "<th>Tipo combustible</th>";
                    $reporte_datos.= "<th>Kilometraje</th>";
                    $reporte_datos.= "<th>Litros</th>";
                    $reporte_datos.= "<th>Litros Reales</th>";
                    $reporte_datos.= "<th>Factura</th>";
                    $reporte_datos.= "<th>Observaciones</th>";
                    $reporte_datos.= "<th>Combustible Salida</th>";
                    $reporte_datos.= "<th>Precio x litro</th>";
                    $reporte_datos.= "<th>Costo total</th>";
                    $reporte_datos.= "<th>Contrato renta</th>";
                    $reporte_datos.= "<th>Tienda</th>";
                    $reporte_datos.= "<th>Autoriza</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['elproveedor'].'</td>';
                        $reporte_datos.= '<td>'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['codvehiculo'].'</td>';
                        $reporte_datos.= '<td>'.$row['vehiculo'].'</td>';
                        $reporte_datos.= '<td>'.$row['conductor'].'</td>';
                        $reporte_datos.= '<td>'.$row['destino'].'</td>';
                        $reporte_datos.= '<td>'.$row['tipo_combustible'].'</td>';
                        $reporte_datos.= '<td>'.$row['kilometraje'].'</td>';
                        $reporte_datos.= '<td>'.formato_numero($row['litros'],2).'</td>';
                        $reporte_datos.= '<td>'.formato_numero($row['litros_reales'],2).'</td>'; 
                        $reporte_datos.= '<td>'.$row['factura_proveedor'].'</td>';
                        $reporte_datos.= '<td>'.$row['observaciones'].'</td>';
                        $reporte_datos.= '<td>'.$row['combustible_salida'].'</td>';
                        $reporte_datos.= '<td>'.formato_numero($row['precio_litro'],2).'</td>';
                        $reporte_datos.= '<td>'.formato_numero($row['lempiras'],2).'</td>';
                        $reporte_datos.= '<td>'.$row['contrato_renta'].'</td>';
                        $reporte_datos.= '<td>'.$row['latienda'].'</td>';
                        $reporte_datos.= '<td>'.$row['elautoriza'].'</td>';
                        
                     $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores

        
    break;


    case 119:// 	Reporte Ordenes de Servicio Creadas a Partir de Hojas de Inspección
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        
        // if (!es_nulo($id_producto)) {
        //     $where.=' AND  servicio.id_producto='.$id_producto;
        //  } 

        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 

        //  if (!es_nulo($id_tecnico)) {
        //     $where.=' AND  (servicio.id_tecnico1='.$id_tecnico." OR servicio.id_tecnico2=".$id_tecnico." OR servicio.id_tecnico3=".$id_tecnico.")";
        //  } 
        
         if ($errores=='') {
                     
            $sql="SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,servicio_estado.nombre AS elestado
            ,servicio_tipo_mant.nombre AS eltipo
            ,servicio.kilometraje
         
            ,entidad.nombre as elcliente
            ,inspeccion.numero as lainspeccion
            
            
            FROM servicio
            LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
            LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
            LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
            LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
            LEFT OUTER JOIN inspeccion ON (servicio.id_inspeccion=inspeccion.id)
            
            WHERE servicio.fecha BETWEEN '$fdesde' AND '$fhasta'
            AND id_inspeccion is not null
            $where
            order by servicio.fecha , servicio.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Hoja Inspeccion</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Servicio #</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Kilometraje</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['lainspeccion'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';                        
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
       
        
    break;


    case 120:// 	Reporte Averías Creadas a Partir de una Hoja de Inspección
        $where2='';
        $having='';
        $campoadd='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        if (!es_nulo($id_producto)) {
            $where.=' AND  averia.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  averia.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  averia.id_tienda='.$id_tienda;
         } 

         if (!es_nulo($id_tipo_averia)) {
            $where.=' AND  averia.id_tipo='.$id_tipo_averia;
         } 

         if (!es_nulo($id_actividad)) {
         
            $having='HAVING actividad>0';
            $campoadd=",(SELECT COUNT(*) FROM averia_detalle WHERE averia_detalle.id_maestro=averia.id AND averia_detalle.id_producto='".$id_actividad."') AS actividad";
            
            
         } 

         if (!es_nulo($averia_coaseguro)) {
             if ($averia_coaseguro==1) {$where.=' AND  averia.coseguro>0';}
             if ($averia_coaseguro==2) {$where.=' AND  (averia.coseguro<=0 or averia.coseguro is null )';}
            
         } 

         if (!es_nulo($averia_deducible)) {
            if ($averia_deducible==1) {$where.=' AND  averia.deducible>0';}
            if ($averia_deducible==2) {$where.=' AND  (averia.deducible<=0 or averia.deducible is null )';}
           
        } 

         
        
         if ($errores=='') {
                     
            $sql="SELECT averia.id, averia.fecha,  averia.numero, averia.numero_alterno
              ,(IFNULL(averia.coseguro,0)+IFNULL(averia.deducible,0)) as totalseguro
              ,averia.total
              ,averia.total_costo
              ,averia.id_tipo
                ,producto.codigo_alterno,producto.nombre,producto.placa
                ,averia_estado.nombre AS elestado
                ,averia_tipo.nombre AS eltipo
                ,entidad.nombre as elcliente
                ,inspeccion.numero as lainspeccion
                ,averia.kilometraje
                $campoadd
                FROM averia
                LEFT OUTER JOIN producto ON (averia.id_producto=producto.id)
                LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
                LEFT OUTER JOIN averia_tipo ON (averia.id_tipo=averia_tipo.id)
                LEFT OUTER JOIN averia_estado ON (averia.id_estado=averia_estado.id)
                LEFT OUTER JOIN inspeccion ON (averia.id_inspeccion=inspeccion.id)
                
     
                
          WHERE averia.fecha BETWEEN '$fdesde' AND '$fhasta'
          AND averia.id_inspeccion is not null
          $where 
          $having 
          order by averia.fecha , averia.id 
          
                ";
    
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Hoja Inspeccion</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Averia #</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Kilometraje</th>";
                                      
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['lainspeccion'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td>'.$row['elestado'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';                        
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
    
        
    break;


    case 121:// 	Reporte Kilometraje por Vehículo

        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        
        // if (!es_nulo($id_producto)) {
        //     $where.=' AND  inspeccion.id_producto='.$id_producto;
        //  } 

        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  inspeccion.id_tienda='.$id_tienda;
         } 
        

         $sql="SELECT  inspeccion.fecha
        , inspeccion.numero
        , inspeccion.kilometraje_entrada as kilometraje
         ,(if(inspeccion.tipo_inspeccion=2,'Taller','Renta')) AS tipo
         ,inspeccion_estado.nombre AS elestado
         ,entidad.nombre AS elcliente
         ,producto.codigo_alterno
         ,producto.nombre AS elvehiculo
         
         FROM inspeccion
         LEFT OUTER JOIN inspeccion_estado ON (inspeccion.id_estado=inspeccion_estado.id)
         LEFT OUTER JOIN entidad ON (inspeccion.cliente_id=entidad.id)
         LEFT OUTER JOIN producto ON (inspeccion.id_producto=producto.id)         
         WHERE inspeccion.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
     
         ORDER BY inspeccion.fecha,inspeccion.id
             ";
 
         $result = sql_select($sql);
         if ($result!=false){
              if ($result -> num_rows > 0) {
                 
                 $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';

                 //HEADER
                 $reporte_datos.= "<thead><tr>";
                 $reporte_datos.= "<th>Fecha</th>";
                 $reporte_datos.= "<th>Codigo</th>";
                 $reporte_datos.= "<th>Vehiculo</th>";
                 $reporte_datos.= "<th>Cliente</th>";
                 $reporte_datos.= "<th>Hoja Inspección</th>";
                 $reporte_datos.= "<th>Kilometraje</th>";
                 $reporte_datos.= "</tr></thead>";
                 
                 //BODY
                 $reporte_datos.= "<tbody>";
                 while ($row = $result -> fetch_assoc()) {
                    
                    
                     $reporte_datos.= "<tr>";
                     $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                     $reporte_datos.= '<td>'.$row['codigo_alterno'].'</td>';
                     $reporte_datos.= '<td>'.$row['elvehiculo'].'</td>';
                     $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                     $reporte_datos.= '<td align="center">'.$row['numero'].'</td>';
                     $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';                        

 
                     $reporte_datos.= "</tr>";
 
                 }
                 $reporte_datos.= "</tbody>";
                 
                 //FOOTER
                 // $reporte_datos.= "<tfoot>";
                 // $reporte_datos.= "</tfoot>";
                 $reporte_datos.= "<table>";
 
                 $con_datatable=true;
              }
         }

        
    break;


    case 122:// 	Reporte Entregas a Domicilio

        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  orden_domicilio.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  orden_domicilio.cliente_id='.$cliente_id;
         } 

        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  orden_domicilio.id_tienda='.$id_tienda;
         } 
      
         if (!es_nulo($id_motorista)) {
            $where.=' AND  orden_domicilio.id_motorista='.$id_motorista;
         } 

         if ($errores=='') {
                     
            $sql="SELECT orden_domicilio.* 
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,orden_domicilio_estado.nombre AS elestado
            ,l1.nombre AS motorista
            ,entidad.nombre as elcliente
        
            FROM orden_domicilio
            LEFT OUTER JOIN producto ON (orden_domicilio.id_producto=producto.id)
            LEFT OUTER JOIN orden_domicilio_estado ON (orden_domicilio.id_estado=orden_domicilio_estado.id)
            LEFT OUTER JOIN usuario l1 ON (orden_domicilio.id_motorista=l1.id)
            LEFT OUTER JOIN entidad ON (orden_domicilio.cliente_id=entidad.id)
            WHERE orden_domicilio.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
            order by orden_domicilio.fecha , orden_domicilio.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Orden #</th>";
                    $reporte_datos.= "<th>Cliente</th>";                    
                    $reporte_datos.= "<th>Vehiculo</th>";                    
                    $reporte_datos.= "<th>Motorista</th>";           
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Observaciones</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';                       
                        $reporte_datos.= '<td > '.$row['motorista'].'</td>';
                        $reporte_datos.= '<td > '.$row['elestado'].'</td>';
                        $reporte_datos.= '<td > '.$row['observaciones'].'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;


    case 136:// 	Reporte de CITAS
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  cita.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  cita.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  cita.id_tienda='.$id_tienda;
         } 

         if (!es_nulo($id_estado_cita)) {
            $where.=' AND  cita.id_estado='.$id_estado_cita;
         } 

              
         if ($errores=='') {
                     
            $sql="SELECT cita.id, cita.fecha, cita.fecha_cita,cita.hora_cita,cita.cliente_contacto, cita.numero, cita.numero_alterno
            ,producto.codigo_alterno ,producto.nombre ,producto.placa
            ,cita_estado.nombre AS elestado
            ,entidad.codigo_alterno AS codcliente
            ,entidad.nombre AS elcliente
            FROM cita
            LEFT OUTER JOIN producto ON (cita.id_producto=producto.id)
            LEFT OUTER JOIN cita_estado ON (cita.id_estado=cita_estado.id)
            LEFT OUTER JOIN entidad ON (cita.cliente_id=entidad.id)
        
            WHERE cita.fecha_cita BETWEEN '$fdesde' AND '$fhasta'
            $where
            order by cita.fecha_cita , cita.hora_cita 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Hora</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Cita #</th>";                
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha_cita']).'</td>';
                        $reporte_datos.= '<td>'.$row['hora_cita'].'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
  
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 147:// 	Reporte traslado vehiculo

        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  orden_traslado.id_producto='.$id_producto;
         } 

        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  orden_traslado.id_tienda='.$id_tienda;
         } 

         if (!es_nulo($id_motorista)) {
            $where.=' AND  (orden_traslado.id_motorista='.$id_motorista.")";
         } 

         if (!es_nulo($id_estado_traslado)) {
            $where.=' AND  orden_traslado.id_estado='.$id_estado_traslado;
         } 

      
         if ($errores=='') {
                     
            $sql="SELECT orden_traslado.* 
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,orden_traslado_estado.nombre AS elestado
            ,l1.nombre AS motorista
            ,l2.usuario AS solicitante
            ,t1.nombre AS tienda_salida
            ,t2.nombre AS tienda_destino
            ,p1.nombre AS elproveedor
        
            FROM orden_traslado
            LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
            LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
            LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
            LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
            LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
            LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)
            LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)  

            WHERE orden_traslado.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
            order by orden_traslado.fecha , orden_traslado.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Orden #</th>";

                    $reporte_datos.= "<th>Proveedor Destino</th>"; 
                    $reporte_datos.= "<th>Tienda Destino</th>";
                  
                    $reporte_datos.= "<th>Vehiculo</th>";                    
                    $reporte_datos.= "<th>Motorista</th>"; 
                    $reporte_datos.= "<th>Solicitante</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Combustible Salida</th>";
                    $reporte_datos.= "<th>Combustible Entrada</th>";
                    $reporte_datos.= "<th>Kilometraje Salida</th>";
                    $reporte_datos.= "<th>Kilometraje Entrada</th>";
                    

                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        
                        $reporte_datos.= '<td>'.$row['elproveedor'].'</td>';
                        $reporte_datos.= '<td>'.$row['tienda_destino'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';                       
                        $reporte_datos.= '<td > '.$row['motorista'].'</td>';
                        $reporte_datos.= '<td > '.$row['solicitante'].'</td>';

                        $reporte_datos.= '<td > '.$row['elestado'].'</td>';

                        $reporte_datos.= '<td  align="center" > '.$row['combustible_salida'].'</td>';
                        $reporte_datos.= '<td  align="center" > '.$row['combustible_entrada'].'</td>';
                        $reporte_datos.= '<td  align="center" > '.formato_numero($row['kilometraje_salida']).'</td>';
                        $reporte_datos.= '<td  align="center" > '.formato_numero($row['kilometraje_entrada']).'</td>';


                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 154:// 	Reporte de Ordenes de Servicio Cobrables	
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  servicio.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  servicio.cliente_id='.$cliente_id;
         } 

        //  if (!es_nulo($placa)) {
        //     $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
        //  } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 

         if (!es_nulo($id_tecnico)) {
            $where.=' AND  (servicio.id_tecnico1='.$id_tecnico." OR servicio.id_tecnico2=".$id_tecnico." OR servicio.id_tecnico3=".$id_tecnico.")";
         } 
        
         if ($errores=='') {
                     
            $sql="SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,servicio_estado.nombre AS elestado
            ,servicio_tipo_mant.nombre AS eltipo
            ,servicio.kilometraje
            ,servicio_tipo_revision.nombre AS elrevision
            ,entidad.nombre as elcliente
            ,sum(ifnull(servicio_detalle.cantidad,0)*ifnull(servicio_detalle.precio_costo,0) ) as total_costo
            ,sum(ifnull(servicio_detalle.cantidad,0)*ifnull(servicio_detalle.precio_venta,0) ) as total_venta
        		,orden_cobro.tipo_cobro
            
            FROM servicio_detalle
            LEFT OUTER JOIN orden_cobro ON (servicio_detalle.id_ocobro=orden_cobro.id_servicio)
				LEFT OUTER JOIN servicio ON (servicio.id=servicio_detalle.id_servicio)
            LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
            LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
            LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
            LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
            LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
            
            WHERE servicio_detalle.id_ocobro IS NOT null
            
            and  servicio.fecha BETWEEN '$fdesde' AND '$fhasta'
            $where
				GROUP BY servicio.id,orden_cobro.tipo_cobro
            order by servicio.fecha , servicio.id 
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Servicio #</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Tipo Revision</th>";
                    $reporte_datos.= "<th>Kilometraje</th>";
                    $reporte_datos.= "<th>Total Costo</th>";

                    $reporte_datos.= "<th>Total Venta</th>";
                    $reporte_datos.= "<th>Gastos Admon.</th>";
                    $reporte_datos.= "<th>ISV</th>";
                    $reporte_datos.= "<th>Total Cobrable</th>";

                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elrevision'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';
                        $reporte_datos.= '<td align="right"> '.formato_numero($row['total_costo'],2).'</td>';
                        
                        
                        $subtotal=$row['total_venta'];
                        $isv=$subtotal*$_SESSION['p_isv'];
                        $gastos_admon=$subtotal*( $_SESSION['p_gasto_admon']);
                        

                        switch ($row["tipo_cobro"]) {
                            case 1: // <option value="1">Cobrable al costo sin recargo del 15% en actividades y repuestos sin gastos administrativos y sin 15% ISV</option>
                                $subtotal=$row['total_costo'];
                                $isv=0;
                                $gastos_admon=0;
                                break;
                            case 2:// <option value="2">Cobrable al costo sin recargo en actividades y repuestos con gastos administrativos y sin 15% ISV</option>
                                $subtotal=$row['total_costo'];
                                $isv=0;
                                $gastos_admon=$subtotal*($_SESSION['p_gasto_admon']);
                                break;
                            case 3:// <option value="3">Cobrable al costo sin recargo en actividades y repuestos con gastos administrativos y con 15% ISV</option>
                                $subtotal=$row['total_costo'];
                                $isv=$subtotal*$_SESSION['p_isv'];
                                $gastos_admon=$subtotal*($_SESSION['p_gasto_admon']);
                                break;
        
                            case 4:// <option value="4">Cobrable con recargo, con gastos administrativos y con 15% de ISV</option>
                                $isv=$subtotal*$_SESSION['p_isv'];
                                $gastos_admon=$subtotal*($_SESSION['p_gasto_admon']);
                                break;
                            case 5:// <option value="5">Cobrable con recargo, con gastos administrativos y sin 15% de ISV</option>
                                $isv=0;
                                $gastos_admon=$subtotal*($_SESSION['p_gasto_admon']);
                                break;
                            case 6:// <option value="6">Cobrable con recargo, ifsin gastos administrativos y con 15% de ISV</option>
                                $isv=$subtotal*$_SESSION['p_isv'];
                                $gastos_admon=0;
                                break;
                            case 7:// <option value="7">Cobrable con recargo, sin gastos administrativos y sin 15% de ISV</option>
                                $isv=0;
                                $gastos_admon=0;
                                break;
        
                        }

                        $eltotal=$subtotal+$gastos_admon+$isv ;

                        $reporte_datos.= '<td align="right"> '.formato_numero($subtotal,2).'</td>';
                        $reporte_datos.= '<td align="right"> '.formato_numero($gastos_admon,2).'</td>';
                        $reporte_datos.= '<td align="right"> '.formato_numero($isv,2).'</td>';                        
                        $reporte_datos.= '<td align="right"> '.formato_numero($eltotal,2).'</td>';
                        
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;


    case 155:// 	Reporte de Ordenes de Servicio Cobrables Detallado
        $where2='';
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
          
        
        if (!es_nulo($id_producto)) {
            $where.=' AND  servicio.id_producto='.$id_producto;
         } 

         if (!es_nulo($cliente_id)) {
            $where.=' AND  servicio.cliente_id='.$cliente_id;
         } 

         if (!es_nulo($placa)) {
            $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
         } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 

        if (!es_nulo($actividad_repuesto)) {
            $where.=' AND  servicio_detalle.producto_tipo='.$actividad_repuesto;
         } 
              
         if ($errores=='') {
                     
            $sql="SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,servicio_estado.nombre AS elestado
            ,servicio_tipo_mant.nombre AS eltipo
            ,entidad.nombre as elcliente
            
            ,servicio_detalle.producto_nombre
            ,servicio_detalle.precio_costo
            ,servicio_detalle.precio_venta
            ,servicio_detalle.cantidad
           
            ,servicio.kilometraje
            ,servicio_tipo_revision.nombre AS elrevision
            
            
            FROM servicio_detalle
            LEFT OUTER JOIN servicio ON (servicio.id=servicio_detalle.id_servicio)
            LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
            LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
            LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
            LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
            LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
            WHERE servicio.fecha BETWEEN '$fdesde' AND '$fhasta'
            and servicio_detalle.id_ocobro IS NOT null
            $where
            order by servicio.fecha , servicio.id 
   
                ";
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";
                    $reporte_datos.= "<th>Fecha</th>";
                    $reporte_datos.= "<th>Cliente</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Servicio #</th>";
                    $reporte_datos.= "<th>Tipo</th>";
                    $reporte_datos.= "<th>Cant.</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Estado</th>";
                    $reporte_datos.= "<th>Tipo Revision</th>";
                    $reporte_datos.= "<th>Kilometraje</th>";
                    $reporte_datos.= "<th>Costo</th>";
                    $reporte_datos.= "<th>Venta</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                       
                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
                        $reporte_datos.= '<td align="center"> '.$row['cantidad'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['producto_nombre'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        $reporte_datos.= '<td align=""> '.$row['elrevision'].'</td>';
                        $reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';
                        
                        $elcosto=$row['precio_costo'];
                        // ,(select orden_compra_detalle.precio_costo FROM orden_compra_detalle WHERE orden_compra_detalle.id_servicio=servicio_detalle.id_servicio AND orden_compra_detalle.id_producto=servicio_detalle.id_producto LIMIT 1 ) AS costocompra
                        // if (!es_nulo($row['costocompra'])) {
                        //      $elcosto=$row['costocompra'];
                        // }
                        $totalcosto=$elcosto*$row['cantidad'];
                        $reporte_datos.= '<td align="right"> '.formato_numero($totalcosto,2).'</td>';
                        $reporte_datos.= '<td align="right"> '.formato_numero($row['precio_venta']*$row['cantidad'],2).'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;



    case 156:// 	Reporte de Ordenes de Servicio en Paro por Repuesto
        $where2='';
        // if (es_nulo($fdesde) or es_nulo($fhasta)) {
        //     $errores.="debe ingresar las fechas";
        // }
          
        
        // if (!es_nulo($id_producto)) {
        //     $where.=' AND  servicio.id_producto='.$id_producto;
        //  } 

        //  if (!es_nulo($cliente_id)) {
        //     $where.=' AND  servicio.cliente_id='.$cliente_id;
        //  } 

        //  if (!es_nulo($placa)) {
        //     $where.=' AND  producto.placa like '.GetSQLValue($placa,'like');
        //  } 
        
         if (!es_nulo($id_tienda)) {
            $where.=' AND  servicio.id_tienda='.$id_tienda;
         } 

        //  if (!es_nulo($id_tecnico)) {
        //     $where.=' AND  (servicio.id_tecnico1='.$id_tecnico." OR servicio.id_tecnico2=".$id_tecnico." OR servicio.id_tecnico3=".$id_tecnico.")";
        //  } 
        
         if ($errores=='') {
         
            $sql=" SELECT servicio.numero as numero,producto.codigo_alterno as vehiculo,producto.nombre as vehnombre,
            servicio_detalle.producto_codigoalterno as articulo,servicio_detalle.producto_nombre as artnombre,
            servicio.fecha as fecha,servicio_detalle.SAP_sinc as FechaSolicitudCompra,
			(select oc.hora from orden_compra oc where oc.id=servicio_detalle.id_oc and oc.id_servicio=servicio_detalle.id_servicio) as FechaOrdenCompra,
            datediff((select oc.hora from orden_compra oc where oc.id=servicio_detalle.id_oc and oc.id_servicio=servicio_detalle.id_servicio),servicio_detalle.sap_sinc) as DiasEnProcesosCompra,datediff(now(),servicio.fecha) as DiasEnParo,
            servicio_detalle.estado as estado_det,servicio_estado.nombre,tienda.nombre,
            case 
            when servicio.estado_paro_por_repuesto='A' then 'Activo'
            when servicio.estado_paro_por_repuesto='I' then 'Inactivo' 
            end
            AS estadoparoporrepuesto
            FROM servicio_detalle 
            inner join servicio on servicio_detalle.id_servicio=servicio.id
            inner join producto on servicio.id_producto=producto.id
            /*left join orden_compra_detalle on (servicio_detalle.id_servicio=orden_compra_detalle.id_servicio and servicio_detalle.producto_codigoalterno=orden_compra_detalle.producto_codigoalterno)*/
            inner join servicio_estado on servicio.id_estado=servicio_estado.id
            inner join tienda on servicio.id_tienda=tienda.id	
            where servicio_detalle.estado in (6,7) and servicio.id_estado=7 and servicio_detalle.producto_tipo=2 $where order by servicio.fecha , servicio.id;";
            /*         
            $sql="SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
            ,producto.codigo_alterno,producto.nombre,producto.placa
            ,servicio_estado.nombre AS elestado
            ,servicio_tipo_mant.nombre AS eltipo
            ,servicio.kilometraje
            ,servicio_tipo_revision.nombre AS elrevision
            ,entidad.nombre as elcliente
            
            FROM servicio
            LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
            LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
            LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
            LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
            LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
            
            WHERE servicio.id_estado =7
            $where
            order by servicio.fecha , servicio.id 
   
                ";*/
 
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER
                    
                    $reporte_datos.= "<thead><tr>";                    
                    $reporte_datos.= "<th>Servicio #</th>";                    
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Articulo</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Fecha OS</th>";                   
                    $reporte_datos.= "<th>F. Solicitud Compra</th>";
                    $reporte_datos.= "<th>F. Orden Compra</th>";
                    $reporte_datos.= "<th>Dias en Proceso Compras</th>";
                    $reporte_datos.= "<th>Dias en Paro</th>";
                    $reporte_datos.= "<th>Estado</th>";                                    
                    $reporte_datos.= "<th>Estado paro por repuesto</th>";                                    
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                        $estado=get_servicio_detalle_estado($row['estado_det']);                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td>'.$row['numero'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['vehiculo'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['vehnombre'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['articulo'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['artnombre'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['FechaSolicitudCompra']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['FechaOrdenCompra']).'</td>';
                        $reporte_datos.= '<td>'.formato_numero($row['DiasEnProcesosCompra'],0).'</td>';
                        $reporte_datos.= '<td>'.formato_numero($row['DiasEnParo'],0).'</td>';                        
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$estado.'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['estadoparoporrepuesto'].'</td>';
                        //$reporte_datos.= '<td>'.$row['elcliente'].'</td>';
                        //$reporte_datos.= '<td style="white-space: nowrap;">'.$row['codigo_alterno'].'</td>';
                        //$reporte_datos.= '<td align="center" style="white-space: nowrap;">'.$row['numero'].'</td>';
                        //$reporte_datos.= '<td align=""> '.$row['eltipo'].'</td>';
                        //$reporte_datos.= '<td align=""> '.$row['elestado'].'</td>';
                        //$reporte_datos.= '<td align=""> '.$row['elrevision'].'</td>';
                        //$reporte_datos.= '<td align="center"> '.formato_numero($row['kilometraje'],0).'</td>';
                        //$reporte_datos.= '<td align="right"> '.formato_numero($row['total_costo'],2).'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }
           
           } // errores
        
        
    break;


    case 157://Reporte de Vehículos Para Mantenimiento Segun Kilometraje Recorrido

        // if (isset($_REQUEST['notodas'])) {
        //    $where.=' HAVING datos_insp IS NOT NULL  ';
        // } 
        $sql="SELECT producto.id,producto.codigo_alterno,producto.nombre,producto.km
        ,(SELECT concat(servicio.fecha,'|',servicio.kilometraje,'|',servicio.id_tipo_revision) FROM servicio WHERE servicio.id_producto=producto.id ORDER BY servicio.id DESC LIMIT 1) AS datos_insp 
            FROM producto	
            WHERE habilitado=1 AND ".app_tipo_vehiculo."
            and producto.km is not null
            $where
            ";

        $result = sql_select($sql);
        if ($result!=false){
             if ($result -> num_rows > 0) {
                
                $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                
                //HEADER
                $reporte_datos.= "<thead><tr>";
                $reporte_datos.= "<th>Codigo</th>";
                $reporte_datos.= "<th>Vehiculo</th>";
                $reporte_datos.= "<th>Kilometraje</th>";
                $reporte_datos.= "<th>Tipo de Mantenimiento Pendiente</th>";
                $reporte_datos.= "</tr></thead>";
                
                //BODY
                $reporte_datos.= "<tbody>";
                while ($row = $result -> fetch_assoc()) {
                    $dd0="";$dd1="";$dd2="";$dd3="";
                    if (!es_nulo($row['datos_insp'])) {
                        $datos_insp=explode('|',$row['datos_insp']);
                        try {$dd0=formato_fecha_de_mysql($datos_insp[0]); } catch (\Throwable $th) {$dd0=""; }
                        try {$dd1=formato_numero($datos_insp[1],0); } catch (\Throwable $th) {$dd1=""; }
                        try {$dd2=$datos_insp[2]; } catch (\Throwable $th) {$dd2=""; }
                    }
                   
                    $tipo_k=get_tipo_mant_k($row['km'],$dd1);

                    if ($tipo_k<>"") {                     
  
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td>'.$row['codigo_alterno'].'</td>';
                        $reporte_datos.= '<td>'.$row['nombre'].'</td>';
                        $reporte_datos.= '<td>'.$row['km'].'</td>';
                        $reporte_datos.= '<td>'.$tipo_k.'</td>';
                    
                        $reporte_datos.= "</tr>";
                    }

                }
                $reporte_datos.= "</tbody>";
                
                //FOOTER
                // $reporte_datos.= "<tfoot>";
                // $reporte_datos.= "</tfoot>";
                $reporte_datos.= "<table>";

                $con_datatable=true;
             }
        }                    
       break;
    case 173:
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
  
        $sql="SELECT servicio.fecha,servicio.numero,servicio.kilometraje,producto.codigo_alterno as vehiculo
        ,producto.nombre as vehnombre,producto.modelo
        ,producto.chasis,servicio_tipo_mant.nombre as mant,servicio_tipo_revision.nombre as revision,servicio.observaciones 
        from servicio 
        inner join producto on servicio.id_producto=producto.id
        inner join servicio_tipo_mant on servicio.id_tipo_mant=servicio_tipo_mant.id	
        inner join servicio_tipo_revision  on servicio.id_tipo_revision=servicio_tipo_revision.id
        where servicio.fecha BETWEEN '$fdesde' AND '$fhasta' AND producto.marca='NISSAN' and servicio.id_estado=22 ";

        if ($errores=='') {
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER

                    $reporte_datos.= "<thead><tr>";                    
                    $reporte_datos.= "<th>Fecha</th>";                    
                    $reporte_datos.= "<th>Servicio #</th>";                    
                    $reporte_datos.= "<th>Kilometraje</th>";                    
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Descripcion</th>";
                    $reporte_datos.= "<th>Modelo</th>";
                    $reporte_datos.= "<th>Chasis</th>";
                    $reporte_datos.= "<th>Tipo de Mantenimiento</th>";                   
                    $reporte_datos.= "<th>Tipo de Servicio</th>";
                    $reporte_datos.= "<th>Observaciones</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                        $estado=get_servicio_detalle_estado($row['estado_det']);                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fecha_de_mysql($row['fecha']).'</td>';
                        $reporte_datos.= '<td>'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['kilometraje'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['vehiculo'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['vehnombre'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['modelo'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['chasis'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['mant'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['revision'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['observaciones'].'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }        
        }
       break;       
    case 174:
        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
           
        if (!es_nulo($id_tienda)) {
            $where.=' AND  A.id_tienda='.$id_tienda;
        } 

        if (!es_nulo($id_producto)) {
            $where.=' AND  A.id_producto='.$id_producto;
        } 

        if (!es_nulo($cliente_id)) {
            $where.=' AND  A.cliente_id='.$cliente_id;
        } 
        
        $sql="SELECT A.numero as numero,J.nombre as cliente,
            hi1.hora AS fecha_entrada_hi,A.hora as fecha_entrada_os,
            A.fecha_hora_final as fecha_completada_os,hi2.hora AS fecha_salida_hi,
            datediff(hi2.hora,hi1.hora) AS dias_taller,
            C.codigo_alterno as vehiculo,C.nombre as vehnombre,
            C.placa as placa,E.nombre as tipo_mant,G.nombre as tipo_revision,F.nombre as estado,
            H.nombre as sucursal,A.observaciones as observaciones from servicio A	
            inner join producto C on A.id_producto=C.id
            inner join servicio_estado F on A.id_estado=F.id
            inner join tienda H on A.id_tienda=H.id
            inner join servicio_tipo_mant E on A.id_tipo_mant=E.id
            inner join servicio_tipo_revision G on A.id_tipo_revision=G.id
            inner join entidad J on A.cliente_id=J.id 
            INNER JOIN inspeccion hi1 ON A.id_inspeccion=hi1.id
            INNER JOIN inspeccion hi2 ON A.id_inspeccion=hi2.id_inspeccion_anterior
            where A.fecha BETWEEN '$fdesde' AND '$fhasta' and A.id_estado=22 AND datediff(hi2.hora,hi1.hora)>0 $where";
        
        if ($errores=='') {
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER

                    $reporte_datos.= "<thead><tr>";                    
                    $reporte_datos.= "<th>Numero OS</th>";                    
                    $reporte_datos.= "<th>Cliente</th>";                    
                    $reporte_datos.= "<th>Fecha Entrada HI</th>";                    
                    $reporte_datos.= "<th>Fecha Entrada OS</th>";
                    $reporte_datos.= "<th>Fecha Completada OS</th>";
                    $reporte_datos.= "<th>Fecha Salida HI</th>";
                    $reporte_datos.= "<th>Dias en Taller</th>";
                    $reporte_datos.= "<th>Vehiculo</th>";
                    $reporte_datos.= "<th>Descripcion</th>";                   
                    $reporte_datos.= "<th>Placa</th>";
                    $reporte_datos.= "<th>Tipo de Mant</th>";
                    $reporte_datos.= "<th>Tipo de Revision</th>";
                    $reporte_datos.= "<th>Observaciones</th>";
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {
                        $estado=get_servicio_detalle_estado($row['estado_det']);                      
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td>'.$row['numero'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['cliente'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahoraT_de_mysql($row['fecha_entrada_hi']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahoraT_de_mysql($row['fecha_entrada_os']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahoraT_de_mysql($row['fecha_completada_os']).'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahoraT_de_mysql($row['fecha_salida_hi']).'</td>';
                        $reporte_datos.= '<td>'.$row['dias_taller'].'</td>';                        
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['vehiculo'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['vehnombre'].'</td>';                        
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['placa'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['tipo_mant'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['tipo_revision'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['observaciones'].'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }        
        }
       break; 

       case 179:

        if (es_nulo($fdesde) or es_nulo($fhasta)) {
            $errores.="debe ingresar las fechas";
        }
              
        if (!es_nulo($id_tienda)) {
            $where.=' AND  inspeccion.id_tienda='.$id_tienda;
        } 

        $sql="SELECT tienda.nombre AS sucursal, inspeccion.numero as numero, entidad.nombre as cliente,
        case when inspeccion.tipo_inspeccion=1 then 'Renta' else 'Taller' end as tipo,
        case when inspeccion.tipo_doc=1 then 'Entrada' else 'Salida' end as tipo_mov,
        producto.codigo_alterno as inventario, usuario.nombre AS usuario_creacion,hora AS fecha_creacion,
        usuario1.nombre AS usuario_completado,inspeccion_historial_estado.fecha AS fecha_completado
        FROM  inspeccion 
        INNER JOIN tienda ON inspeccion.id_tienda=tienda.id
        INNER JOIN entidad ON inspeccion.cliente_id=entidad.id
        INNER JOIN producto ON inspeccion.id_producto=producto.id
        INNER JOIN usuario ON inspeccion.id_usuario=usuario.id
        INNER JOIN usuario usuario1 ON inspeccion.id_usuario_completado=usuario1.id 
        INNER JOIN inspeccion_historial_estado ON inspeccion.id=inspeccion_historial_estado.id_maestro
        WHERE inspeccion.fecha BETWEEN '$fdesde' AND '$fhasta'  AND inspeccion.id_estado>1 and inspeccion.tipo_inspeccion_especial is null
        AND inspeccion_historial_estado.nombre='Guardar Completado' $where ORDER BY inspeccion.numero";
        if ($errores=='') {
            $result = sql_select($sql);
            if ($result!=false){
                 if ($result -> num_rows > 0) {
                    
                    $reporte_datos.= '<table id="genreporte'.$reporte.'" class="table table-striped  table-sm">';
                    
                    //HEADER

                    $reporte_datos.= "<thead><tr>";                    
                    $reporte_datos.= "<th>Sucursal</th>";                    
                    $reporte_datos.= "<th>Numero HI</th>";   
                    $reporte_datos.= "<th>Tipo</th>";                    
                    $reporte_datos.= "<th>Cliente</th>";                    
                    $reporte_datos.= "<th>Inventario</th>";
                    $reporte_datos.= "<th>Usuario Creacion</th>";
                    $reporte_datos.= "<th>Fecha Creacion</th>";
                    $reporte_datos.= "<th>Usuario Completado</th>";
                    $reporte_datos.= "<th>Fecha Completado</th>";    
                    $reporte_datos.= "<th>Tipo Mov.</th>";            
                    $reporte_datos.= "</tr></thead>";
                    
                    //BODY
                    $reporte_datos.= "<tbody>";
                    while ($row = $result -> fetch_assoc()) {                          
                        $reporte_datos.= "<tr>";
                        $reporte_datos.= '<td>'.$row['sucursal'].'</td>';
                        $reporte_datos.= '<td>'.$row['numero'].'</td>';
                        $reporte_datos.= '<td>'.$row['tipo'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['cliente'].'</td>';
                        $reporte_datos.= '<td>'.$row['inventario'].'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['usuario_creacion'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahoraT_de_mysql($row['fecha_creacion']).'</td>';
                        $reporte_datos.= '<td style="white-space: nowrap;">'.$row['usuario_completado'].'</td>';
                        $reporte_datos.= '<td align="center" style="white-space: nowrap;">'.formato_fechahoraT_de_mysql($row['fecha_completado']).'</td>';
                        $reporte_datos.= '<td>'.$row['tipo_mov'].'</td>';
                        $reporte_datos.= "</tr>";
    
                    }
                    $reporte_datos.= "</tbody>";
                    
                    //FOOTER
                    // $reporte_datos.= "<tfoot>";
                    // $reporte_datos.= "</tfoot>";
                    $reporte_datos.= "<table>";
    
                    $con_datatable=true;
                 }
            }        
        }
       break; 
  
    
    default:
        $reporte_datos="";
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] ="No se encontro el reporte";
        break;
}







} else { //no tiene premiso
    $reporte_datos="";
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="No tiene permisos suficientes para ver este reporte";
}

if ($con_datatable==true) {
    $reporte_datos.= ' <script > 

 

    var table=$(\'#genreporte'.$reporte.'\').dataTable(     	{
        //		"bAutoWidth": true,
        		"bFilter": false,
        		"bPaginate": true,
        	//	"bSort": false,
            	//"bInfo": false,
            	"bStateSave": false,
    
            	"responsive": false,   
                "pageLength": 100,

                // "fixedHeader": {
                //     "header": true,
                //     "headerOffset": $(\'#topmainbar\').height()
                // },
         
                '.$datatable_adicional.'

          		"dom": \'B<"clear"> frtipl\',
    
          		"processing": false,
                "serverSide": false,
    
        		buttons: [\'excelHtml5\', \'csvHtml5\',  
                {
                    extend: \'print\',
                    text: \'Imprimir\',
                    title: \''.$nombre_reporte.'\',
                    customize: function ( win ) {
                        $(win.document.body)
                            .css( \'font-size\', \'10pt\' )
                            .prepend(
                                \'<img src="'.app_host.'img/logo.png" style="position:absolute; top:0; left:0; " height="25" />\'
                            );

                        $(win.document.body).find( \'table\' ).addClass( \'compact\' ).css( \'font-size\', \'10pt\' );
                        $(win.document.body).find(\'h1\').css(\'font-size\', \'14pt\');
                        $(win.document.body).find(\'h1\').css(\'text-align\', \'center\'); 
                        $(win.document.body).find(\'h1\').css(\'line-height\', \'2.5\');
                    }
                }
            ],
                
           	//	"bScrollCollapse": true,
        
        		"bJQueryUI": false,
                
                 "language": { "url": "plugins/datatables/spanish.lang" }			
    
        });
        </script> ';
}

if ($errores<>'') {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] =$errores;
    $stud_arr[0]["pdata"] ='';
} else {
    $titulo='<h5 class="text-center">'.$nombre_reporte.'</h5>';
    $stud_arr[0]["pdata"] =$titulo.$reporte_datos;
}
salida_json($stud_arr);
exit;

?>