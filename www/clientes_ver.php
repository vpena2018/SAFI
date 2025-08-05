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
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and nombre like ".GetSQLValue($tmpval,'like');} }
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    $habilitado="habilitado=1";
    if (isset($_REQUEST['inactivos'])) {$habilitado="habilitado=0"; }


    $datos="";
    $result = sql_select("SELECT id,codigo_alterno, nombre, telefono,ciudad,pais,rtn
    FROM entidad 
    where $habilitado
    and tipo=$tipo_entidad
    $filtros
     limit $offset,".app_reg_por_pag
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a  href="#" onclick="abrir_cliente(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["codigo_alterno"].'</a></td>
                <td>'.$row["nombre"].'</td>
                <td>'.$row["rtn"].'</td>
                <td>'.$row["ciudad"].'</td>
                 <td>'.$row["pais"].'</td>
                </tr>';               
               
            }

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
             echo campo("nombre","Nombre",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>

        <div class="col-sm">
            <a id="btn-filtro" href="#" onclick="limpiar_tabla('tabla'); procesar_tabla('tabla'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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


 


<div class="table-responsive ">
    <table id="tabla" data-url="clientes_ver.php?a=1&t=<?php echo $tipo_entidad ;?>" data-page="0" class="table table-striped table-hover table-sm"  style="width:100%">
        <thead class="thead-dark">
            <tr>
                <th><?php echo 'Codigo'; ?></th>
                <th><?php echo 'Nombre'; ?></th>
                <th><?php echo 'RTN'; ?></th>
                <th><?php echo 'Ciudad'; ?></th>
                <th><?php echo 'Pais'; ?></th>
            </tr>
        </thead>
        <tbody id="tablabody">
            
        </tbody>
       
    </table>
    <div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a>
    </div>
</div>


<?php 



?>

<script>

 $('#pagina-botones').html('<a href="#" onclick="abrir_cliente(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#nombre" ).focus();

    function abrir_cliente(codigo){

        modalwindow(
            'Cliente',
            'clientes.php?a=v&cid='+codigo<?php echo "+'&t=".$tipo_entidad."'" ;?>
            );
           
    }

    

</script>

</div>


 