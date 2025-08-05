<?php
require_once ('include/framework.php');
pagina_permiso(31);






    $datos="";

    $result = sql_select("SELECT  producto.id, producto.codigo_alterno, producto.nombre, producto.codigo_grupo,  producto.tipo, 
    producto.marca, producto.anio, producto.modelo, producto.cilindrada, producto.serie, producto.motor, producto.placa, producto.tipo_vehiculo, producto.chasis 
    ,producto.km,producto.k5,producto.k10,producto.k20,producto.k40,producto.k100
	 FROM producto
    where producto.habilitado=1
    and ".app_tipo_vehiculo."
    and  producto.km IS NOT NULL
    AND (
	 (km>=5000 AND k5 IS NULL)
	 OR (km>=10000 AND k10 IS NULL)
	 OR (km>=20000 AND k20 IS NULL)
	 OR (km>=40000 AND k40 IS NULL)
	 OR (km>=100000 AND k100 IS NULL)
	 )
    
     "
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td>'.$row["codigo_alterno"].'</td>
                <td>'.$row["nombre"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.$row["anio"].'</td>
                <td>'.formato_numero($row["km"],0).'</td>
                <td>'.multik_leer($row["k5"]).'</td>
                <td>'.multik_leer($row["k10"]).'</td>
                <td>'.multik_leer($row["k20"]).'</td>
                <td>'.multik_leer($row["k40"]).'</td>
                <td>'.multik_leer($row["k100"]).'</td>
                </tr>';               
               
            }


        }

    } 


function multik_leer($valor){
    $salida="";
    if (es_nulo($valor)) {
        $salida='<span class="text-secondary"><i class="fa fa-times-circle"></i></span>';
    } else {
        $salida='<span class="text-success"><i class="fa fa-check-circle"></i></span>';
    }

    return $salida;
}



?>


<div class="card-body">



<div class="table-responsive ">
    <table id="tabla_seguimiento"  class="table table-striped table-hover table-sm"  style="width:100%">
        <thead class="thead-dark">
            <tr>
                <th>Codigo</th>
                <th>Descripción</th>
                <th>Placa</th>
                <th>Año</th>
                <th>KM</th>
                 <th>K5</th>
                 <th>K10</th>
                 <th>K20</th>
                 <th>K40</th>
                 <th>K100</th>
                     
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


    function multik_abrir(codigo){
        
      //  get_page('pagina','servicio_mant.php?a=v&cid='+codigo,'Orden de Servicio') ;
    }



    var table=$('#tabla_seguimiento').dataTable(     	{
	//		"bAutoWidth": true,
			"bFilter": true,
			"bPaginate": true,
		//	"bSort": false,
        	//"bInfo": false,
        	"bStateSave": false,

        	"responsive": false,   
            "pageLength": 50,
   
  			"dom": '<"clear"> frtiplB',

  			"processing": false,
            "serverSide": false,

    		buttons: ['excelHtml5', 'csvHtml5', 'print' ],
 
       	//	"bScrollCollapse": true,
	
			"bJQueryUI": false,
			
	         "language": { "url": "plugins/datatables/spanish.lang" }			

    });
    

</script>