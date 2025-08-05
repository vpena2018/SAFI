<?php
require_once ('include/framework.php');
pagina_permiso(26);






    $datos="";

    $result = sql_select("SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
    ,servicio.fecha_hora_ingreso
    ,servicio.fecha_hora_asigna
    ,servicio.fecha_hora_promesa
    ,servicio.kilometraje
        ,producto.codigo_alterno,producto.nombre,producto.placa,producto.chasis AS vin
        ,servicio_estado.nombre AS elestado
        ,servicio_tipo_mant.nombre AS eltipo
     ,entidad.nombre AS cliente_nombre
      ,producto.nombre AS producto_nombre
      ,servicio_estado.nombre AS elestado
      ,servicio_tipo_mant.nombre AS eltipo
      ,servicio_tipo_revision.nombre AS eltiporevision
      ,tec1.nombre AS eltecnico1
      ,taller.nombre AS taller_nombre
    
      FROM servicio
      LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
      LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
      LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
      LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
      LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
      LEFT OUTER JOIN usuario tec1 ON (servicio.id_tecnico1=tec1.id)
      LEFT OUTER JOIN entidad taller ON (servicio.id_taller=taller.id)	
        where 1=1
    
    and servicio.id_tienda=".$_SESSION['tienda_id']."
    and servicio.id_estado <20
    
    ORDER BY servicio.id_tipo_mant, servicio.id desc
     "
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td ><a  href="#" onclick="servicio_abrir(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td class="text-nowrap">'.$row["codigo_alterno"].'</td>
                <td>'.formato_fecha_de_mysql($row["fecha_hora_ingreso"]).'</td>
                <td>'.formato_fecha_de_mysql($row["fecha_hora_asigna"]).'</td>
                <td>'.$row["eltipo"].'</td>
                <td>'.$row["taller_nombre"].'</td>
                <td>'.$row["eltecnico1"].'</td>
                <td>'.formato_fecha_de_mysql($row["fecha_hora_promesa"]).'</td>
                <td class="">'.$row["nombre"].'</td>
                <td class="text-nowrap">'.$row["kilometraje"].'</td>
                <td>'.$row["eltiporevision"].'</td>
               <td>'.$row["elestado"].'</td>

                </tr>';               
               
            }


        }

    } 






?>


<div class="card-body">



<div class="table-responsive ">
    <table id="tabla_seguimiento"  class="table table-striped table-hover table-sm"  style="width:100%">
        <thead class="thead-dark">
            <tr>
                <th>No.</th>
                <th># INV</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Asignado</th>
                <th>Tipo</th>
                <th>Taller</th>
                <th>Tecnico</th>
                <th>Fecha Promesa</th>
                <th>Vehiculo</th>
                <th>KM</th>
                <th>Tipo Revisión</th>

                <th>Estado</th>
                     
            </tr>
        </thead>
        <tbody id="tablabody_seguimiento" style="font-size: 12px;">
            <?php 
                echo $datos;
            ?>
        </tbody>
       
    </table>

</div>


</div>

<script>


    function servicio_abrir(codigo){
        
        get_page('pagina','servicio_mant.php?a=v&cid='+codigo,'Orden de Servicio') ;
    }



    var table=$('#tabla_seguimiento').dataTable(     	{
	//		"bAutoWidth": true,
			"bFilter": true,
			"bPaginate": false,
		//	"bSort": false,
        	//"bInfo": false,
        	"bStateSave": false,

        	"responsive": false,   

            "order": [[5, 'asc']],
        "rowGroup": {
            dataSrc: [ 5 ]
        },
        "columnDefs": [ {
            targets: [ 5 ],
            visible: false
        } ],

  			"dom": '<"clear"> frtiplB',

  			"processing": false,
            "serverSide": false,

    		buttons: ['excelHtml5', 'csvHtml5', 'print' ],
 
       		"bScrollCollapse": true,
	
			"bJQueryUI": false,
			
	         "language": { "url": "plugins/datatables/spanish.lang" }			

    });
    

</script>