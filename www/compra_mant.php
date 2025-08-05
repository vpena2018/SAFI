<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');
pagina_permiso(6);


$result = sql_select("SELECT orden_compra.id, 
orden_compra.numero,
orden_compra.SAP_sinc, 
orden_compra.fecha
,orden_compra.id_servicio
,servicio.numero AS numero_servicio
,averia.numero AS numero_averia
,entidad.nombre
,tienda.nombre AS nombre_tienda
,orden_compra.observaciones

FROM orden_compra 
LEFT OUTER JOIN servicio ON (orden_compra.id_servicio=servicio.id)
LEFT OUTER JOIN averia ON (orden_compra.id_averia=averia.id)
LEFT OUTER JOIN entidad ON (orden_compra.id_entidad=entidad.id)
LEFT OUTER JOIN tienda ON (orden_compra.id_tienda=tienda.id)
WHERE  orden_compra.id=$cid          
 limit 1");

if ($result!=false){
    if ($result -> num_rows > 0) { 
        $row = $result -> fetch_assoc(); 

    }
}


$vtipodocto="No. Servicio";
$vnumdocto=$row["numero_servicio"];
if (!es_nulo($row["numero_averia"])) {
       $vtipodocto="No. Avería";
       $vnumdocto=$row["numero_averia"];
}

?>
     <form id="formver_oc" name="forma_cambiarcmp" class="needs-validation" >
    <fieldset > 



  <div class="row">
     <div class="col-md-3">       
            <?php echo campo("numero_lb","No. Orden",'label',$row["numero"],' ',' ');  ?>              
     </div>
     <div class="col-md-3">       
            <?php echo campo("fecha","Fecha",'label',formato_fecha_de_mysql($row["fecha"]),' ',' ');  ?>              
     </div>
     <div class="col-md-3">       
            <?php echo campo("Sincronizado","Sincronizado",'label',formato_fechahora_de_mysql($row["SAP_sinc"]),' ',' ');  ?>              
     </div>
     <div class="col-md-3">       
     <?php echo campo("noServicio",$vtipodocto,'label',$vnumdocto,' ',' ');  ?>              
     </div>
  </div>    

  <div class="row">
     <div class="col-md-6">       
            <?php echo campo("Proveedor","Proveedor",'label',$row["nombre"],' ',' ');  ?>              
     </div>

     <div class="col-md-6">       
            <?php echo campo("Tienda","Tienda",'label',$row["nombre_tienda"],' ',' ');  ?>              
     </div>
  </div>    
  <div class="row">
     <div class="col-md-12">       
            <?php echo campo("observaciones","Observaciones",'label',$row["observaciones"],' ',' ');  ?>              
     </div>

  </div>    

        <div class="row"> 
     
     
  <div class="table-responsive">  
      <table class="table table-striped table-hover" style="width:100%">
        <thead>
          <tr>
           <!-- <th><input type="checkbox"  onchange="servicio_marcar_todos(this,'detalleul_servicio_dlg'); "  ></th> -->
            <th>Codigo</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Costo</th>
            <th>Venta</th>
            <th>Nota</th>
          </tr>
        </thead>
        <tbody >
         
      
      <?php

   
        $servicios_result = sql_select("SELECT * FROM orden_compra_detalle where  id_orden=$cid  order by id ");
        $salidatxt='';
  
        if ($servicios_result->num_rows > 0) { 
          while ($detalle = $servicios_result -> fetch_assoc()) {

           $salidatxt.='<tr>';

           // , producto_tipo, , , , , , cobrable, estado
           $salidatxt.='<td>'.$detalle["producto_codigoalterno"].'</td> ';
           $salidatxt.='<td>'.$detalle["producto_nombre"].'</td> ';
           $salidatxt.='<td>'.$detalle["cantidad"].'</td> ';
           $salidatxt.='<td>'.formato_numero($detalle["precio_costo"],2).'</td> ';
           $salidatxt.='<td>'.formato_numero($detalle["precio_venta"],2).'</td> ';
           $salidatxt.='<td>'.$detalle["producto_nota"].'</td> ';               

           $salidatxt.='</tr>';
          
            }
        } else {
                echo '<tr><td colspan="5" >No se encontraron registros<td> </tr>';
            }

            echo $salidatxt;

      ?>
      <!-- </ul> -->

      </tbody>
 
 </table>

   
    
  </div>
  </div>


    </fieldset> 
    </form>

    <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">

		<div class="col-sm"><a href="#" onclick="$('#ModalWindow2').modal('hide');  return false;" class="btn btn-secondary  mb-2 xfrm" > Cerrar</a></div>
		</div>
	</div>
 
<script>
 

</script>

 