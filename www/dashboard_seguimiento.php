<?php
require_once ('include/framework.php');
pagina_permiso(0);

$datos="";
$accion ="";

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 

if ($accion=="1") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="No se encontraron Datos";
    $stud_arr[0]["pdata"] ="";
    $stud_arr[0]["pmas"] =0;
    

    $result = sql_select("SELECT servicio.id
    ,  servicio.numero
    ,servicio.fecha_hora_ingreso
      ,servicio.fecha_hora_asigna
      ,servicio.fecha_hora_promesa
      ,(SELECT cita.numero FROM cita WHERE cita.id_inspeccion =servicio.id_inspeccion ORDER BY cita.id desc LIMIT 1) AS num_cita 
      ,entidad.nombre AS cliente_nombre
      ,servicio_tipo_revision.nombre AS eltiporevision
      ,servicio_tipo_mant.nombre AS eltipo
      ,producto.nombre AS producto_nombre
      ,servicio_estado.nombre AS elestado
      ,concat(ifnull(tec1.nombre,''),' / ',ifnull(tec2.nombre,''),' / ',ifnull(tec3.nombre,'')) AS eltecnico
      ,(SELECT (sum((if((servicio_detalle.estado=9),1,0) ) )/COUNT(servicio_detalle.estado))*100 FROM servicio_detalle WHERE servicio_detalle.producto_tipo=3 AND  servicio_detalle.id_servicio =servicio.id) AS porcentaje
 
    -- , servicio.fecha, servicio.numero_alterno,servicio.kilometraje,producto.placa,producto.chasis AS vin
    ,producto.codigo_alterno

        FROM servicio
        LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
        LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
        LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
        LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
        LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
        LEFT OUTER JOIN usuario tec1 ON (servicio.id_tecnico1=tec1.id)
        LEFT OUTER JOIN usuario tec2 ON (servicio.id_tecnico2=tec2.id)
        LEFT OUTER JOIN usuario tec3 ON (servicio.id_tecnico3=tec3.id)  
          where 1=1
    
    and servicio.id_tienda=".$_SESSION['tienda_id']."
    AND (servicio.id_estado<>22 AND servicio.id_estado<>20)
    
    ORDER BY servicio.id desc
     "
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }

            $datos='<table id="tabla_seguimiento"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>No.</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha Asignado</th>
                    <th>Fecha Prometido</th>
                    <th>Cita #</th>
                    <th>Cliente</th>
                    <th>Tipo de Revisión</th>
                    <th>Tipo de Orden de servicio</th>
                    <th>Vehiculo</th>
                    <th>Estado</th>
                    <th>Tecnico Asignado</th>
                    <th>Porcentaje de Avance</th>

                                                                    

                </tr>
            </thead>
            <tbody id="tablabody_seguimiento" style="font-size: 12px;">';


            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td ><a  href="#" onclick="servicio_abrir_btnseg(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td>'.formato_fechahora_de_mysql($row["fecha_hora_ingreso"]).'</td>
                <td>'.formato_fechahora_de_mysql($row["fecha_hora_asigna"]).'</td>
                <td>'.formato_fechahora_de_mysql($row["fecha_hora_promesa"]).'</td>
                <td>'.$row["num_cita"].'</td>
                <td>'.$row["cliente_nombre"].'</td>
                 <td>'.$row["eltiporevision"].'</td>
                <td>'.$row["eltipo"].'</td>                
                <td class="text-nowrap">'.$row["codigo_alterno"].'</td>                
                
                <td>'.$row["elestado"].'</td>
                <td>'.$row["eltecnico"].'</td>
               
               <td>'.formato_numero($row["porcentaje"],0).'%</td>

                </tr>';   
                








               
            }
            $datos.='</tbody></table>  ';

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="";
        $stud_arr[0]["pdata"] =$datos;
        $stud_arr[0]["pmas"] =0;


        }

    } 
    salida_json($stud_arr);
    exit;


}



?>


<div class="card-body">

<form id="forma_ver" name="forma_ver" >
 <fieldset id="fs_forma">
    <div class="row">
        <div class="col-sm">
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','dashboard_seguimiento.php?a=1','Dashboard Seguimiento','forma_ver');  return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  Actualizar</a>
        </div>            
    </div>
</fieldset>
</form>


<div id="tablaver" class="table-responsive ">
        
    
</div>

    <div class="botones_accion d-print-none " >
        <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
        <!-- <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a> -->
    </div>


</div>

<script>


    function servicio_abrir_btnseg(codigo){
        
        get_page_switch('pagina','servicio_mant.php?a=v&btnseg=1&cid='+codigo,'Orden de Servicio') ;
    }



    // var table=$('#tabla_seguimiento').dataTable(     	{
	// //		"bAutoWidth": true,
	// 		"bFilter": true,
	// 		"bPaginate": false,
	// 	//	"bSort": false,
    //     	//"bInfo": false,
    //     	"bStateSave": false,

    //     	"responsive": false,   

    //      //   "order": [[5, 'asc']],
    //     // "rowGroup": {
    //     //     dataSrc: [ 5 ]
    //     // },
    //     // "columnDefs": [ {
    //     //     targets: [ 5 ],
    //     //     visible: false
    //     // } ],

  	// 		"dom": '<"clear"> frtiplB',

  	// 		"processing": false,
    //         "serverSide": false,

    // 		buttons: ['excelHtml5', 'csvHtml5', 'print' ],
 
    //    		"bScrollCollapse": true,
	
	// 		"bJQueryUI": false,
			
	//          "language": { "url": "plugins/datatables/spanish.lang" }			

    // });
    

</script>