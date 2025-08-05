<?php
require_once ('include/framework.php');
pagina_permiso(5);

$accion ="";
$tipo_entidad="1";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 
if (isset($_REQUEST['t'])) { $tipo_entidad = sanear_int($_REQUEST['t']); } 

if ($accion=="1") {
   // sleep(3);
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="No se encontraron Datos";
    $stud_arr[0]["pdata"] ="";
    $stud_arr[0]["pmas"] =0;

      
    $pagina=1;
    $offset=0;
    $haymas=0;
    $filtros="";

    if (isset($_REQUEST['pg'])) { $pagina = sanear_int($_REQUEST['pg']); }
    if (isset($_REQUEST['codigo'])) { $tmpval=sanear_string($_REQUEST['codigo']); if (!es_nulo($tmpval)){$filtros.=" and codigo_alterno like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and nombre like ".GetSQLValue($tmpval,'like');} }
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and chasis like ".GetSQLValue($tmpval,'like');} }
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    $habilitado="habilitado=1";
    if (isset($_REQUEST['inactivos'])) {$habilitado="habilitado=0"; }


    $datos="";
    $tipo_producto=app_tipo_vehiculo;
    if ($tipo_entidad==2) {
        $tipo_producto=" ( ".app_tipo_inventariables." OR ". app_tipo_no_inventariables." OR ". app_tipo_cobrables.")";
    }
    $result = sql_select(" SELECT  id, codigo_alterno, nombre, codigo_grupo,  tipo, 
    marca, anio, modelo, cilindrada, serie, motor, placa, tipo_vehiculo, chasis 
    FROM producto
    where $habilitado
    and $tipo_producto
    $filtros
     "
    );//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Codigo</th>
                    <th>Descripción</th>
                    <th>Placa</th>
                    <th>Año</th>
                     <th>VIN</th>
                </tr>
            </thead>
            <tbody id="tablabody">
                
            ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a  href="#" onclick="abrir_producto(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["codigo_alterno"].'</a></td>
                <td>'.$row["nombre"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.$row["anio"].'</td>
               <td><small>'.$row["chasis"].'</small></td>
                </tr>';               
               
            }

            $datos.='</tbody>
           
            </table>';

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="";
        $stud_arr[0]["pdata"] =$datos;
        $stud_arr[0]["pmas"] =$haymas;

        }

    } 

    salida_json($stud_arr);
    exit;

} 




?>


<div class="card-body">


<div class="botones_accion d-print-none " >
<form id="forma" name="forma" >
 <fieldset id="fs_forma">
    <div class="row">  
   
        <div class="col-sm">
            <?php 
           echo campo("codigo","Codigo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
     
        <div class="col-sm">
              <?php 
             echo campo("nombre","Descripción",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
        <div class="col-sm">
              <?php 
             echo campo("placa","Placa",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
        <div class="col-sm">
              <?php 
             echo campo("vin","VIN",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>

        <div class="col-sm">
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','productos_ver.php?a=1&t=<?php echo $tipo_entidad ;?>','Vehiculos'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
     <div class="row">
        <div class="col-sm">
              <?php 
             echo campo("inactivos","Mostrar Inactivos",'checkbox','1',' ','');
            ?>  
        </div>
     </div>
 </fieldset>
</form>
</div>


 


<div id="tablaver" class="table-responsive ">
    
 
</div>
<div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <!-- <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a> -->
    </div>

<?php 



?>

<script>

 //$('#pagina-botones').html('<a href="#" onclick="abrir_producto(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#nombre" ).focus();

    function abrir_producto(codigo){

        modalwindow(
            'Producto',
            'productos.php?a=v&cid='+codigo<?php echo "+'&t=".$tipo_entidad."'" ;?>
            );
           
    }

    

</script>

</div>


 