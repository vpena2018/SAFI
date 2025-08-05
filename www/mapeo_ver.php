<?php
require_once ('include/framework.php');
pagina_permiso(51);

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
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and mapeo.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and mapeo.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['zona'])) { $tmpval=sanear_string($_REQUEST['zona']); if (!es_nulo($tmpval)){$filtros.=" and mapeo.zona like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['ubicacion'])) { $tmpval=sanear_string($_REQUEST['ubicacion']); if (!es_nulo($tmpval)){$filtros.=" and mapeo.ubicacion like ".GetSQLValue($tmpval,'like') ;}   }

    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and producto.placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and producto.chasis like ".GetSQLValue($tmpval,'like');} }
        
    
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }
    $datos="";

    $result = sql_select("SELECT mapeo.id, mapeo.id_tienda, mapeo.zona, mapeo.ubicacion, mapeo.id_estado, mapeo.id_producto, mapeo.id_usuario, mapeo.hora
    ,tienda.nombre AS latienda
    ,producto.nombre AS vehiculo
    ,producto.placa 
    ,producto.codigo_alterno
    ,producto.clase
    FROM mapeo
    LEFT OUTER JOIN tienda ON (tienda.id=mapeo.id_tienda)
    LEFT OUTER JOIN producto ON (producto.id=mapeo.id_producto)    
    where 1=1
    $filtros
    ORDER BY mapeo.id_tienda,mapeo.zona,mapeo.ubicacion
     limit $offset,".app_reg_por_pag
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $boton='';
                if ($row["id_estado"]==1 ) { //disponible
                   $boton='<a id="btn-filtro" href="#" onclick="mapeo_abrir(\''.$row["id"].'\',\''.$row["id_producto"].'\',\'Entrada\'); return false;" class="btn btn-sm btn-success"> Entrada</a>';  
                }
                if ($row["id_estado"]==2) { //ocupado
                    $boton='<a id="btn-filtro" href="#" onclick="mapeo_abrir(\''.$row["id"].'\',\''.$row["id_producto"].'\',\'Salida\'); return false;" class="btn btn-sm btn-warning"> Salida</a>';  
                 }
                 if ($row["id_estado"]==3 ) { //fuera servicio
                    $boton='<a id="btn-filtro" href="#" onclick="mapeo_abrir(\''.$row["id"].'\',\''.$row["id_producto"].'\',\'Entrada\'); return false;" class="btn btn-sm btn-secondary"> Entrada</a>';  
                 }
                $datos.='<tr>
                <td>'.$boton.'</td>               
                <td>'.$row["zona"].'</td>
                <td>'.$row["ubicacion"].'</td>
                <td>'.get_estado_mapeo($row["id_estado"]).'</td>
                <td>'.$row["codigo_alterno"].' '.$row["vehiculo"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.formato_fechahora_de_mysql($row["hora"]).'</td>
                <td>'.$row["latienda"].'</td>
                <td>'.$row["clase"].'</td>
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
             echo campo("zona","Zona",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>

        <div class="col-sm">
              <?php 
             echo campo("ubicacion","Ubicacion",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>

       
         <div class="col-sm">
            <?php 
           echo campo("estado","Estado",'select',valores_combobox_texto(app_estado_mapeo,'','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
            
         </div>
         <div class="col-sm">
            <?php 
           echo campo("tienda","Tienda",'select',valores_combobox_db('tienda',$_SESSION['tienda_id'],'nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
    </div>   
    <div class="row"> 

        <div class="col-sm">
              <?php 
             echo campo("nombre","Vehiculo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
            <a id="btn-filtro" href="#" onclick="limpiar_tabla('tabla_mapeo'); procesar_tabla('tabla_mapeo'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
 
   
 </fieldset>
</form>
</div>


 


<div class="table-responsive ">
    <table id="tabla_mapeo" data-url="mapeo_ver.php?a=1" data-page="0" class="table table-striped table-hover table-sm"  style="width:100%">
        <thead class="thead-dark">
            <tr>
                <th></th>
                <th>Zona</th>
                <th>Ubicación</th>   
                <th>Estado</th>             
                <th>Vehiculo</th>
                <th>Placa</th>
                <th>Fecha</th>
                <th>Tienda</th>
                <th>Clase</th>                    
            </tr>
        </thead>
        <tbody id="tablabody">
            
        </tbody>
       
    </table>
    <div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla_mapeo'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a>
    </div>
</div>


<?php 



?>

<script>

 //$('#pagina-botones').html('<a href="#" onclick="abrir_producto(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#numero" ).focus();


    function mapeo_abrir(codigo,vehiculo,movimiento){
        
        modalwindow(
            'Mapeo',
            'mapeo_asignar.php?a=v&cid='+codigo+'&pid='+vehiculo+'&mov='+movimiento
            );
    }

    

</script>

</div>


 