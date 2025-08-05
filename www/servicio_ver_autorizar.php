<?php
require_once ('include/framework.php');
pagina_permiso(175);

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
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.=" and servicio.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tipo'])) { $tmpval=sanear_int($_REQUEST['tipo']); if (!es_nulo($tmpval)){$filtros.=" and servicio.id_tipo_mant = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if ($tmpval==2){$filtros.=" and servicio.id_estado=21 " ;} else {$filtros.=" and servicio.id_estado=1 " ;}   }
    
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and servicio.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and producto.placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and producto.chasis like ".GetSQLValue($tmpval,'like');} }
    
    // $tmpval=sanear_int($_SESSION['usuario_id']); if (!es_nulo($tmpval)){$filtros.=" AND (servicio.id_usuario=$tmpval  OR servicio.id_tecnico1=$tmpval OR servicio.id_tecnico2=$tmpval OR servicio.id_tecnico3=$tmpval OR servicio.id_tecnico4=$tmpval )" ;}   
   
    
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }


    $datos="";

    $result = sql_select("SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno
	,producto.codigo_alterno,producto.nombre,producto.placa
	,servicio_estado.nombre AS elestado
	,servicio_tipo_mant.nombre AS eltipo
    ,servicio.nota_operaciones
    ,t1.nombre AS tecnico
    ,t2.nombre AS tecnico2
    ,t3.nombre AS tecnico3
    ,t4.nombre AS tecnico4
	FROM servicio
	LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
	LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
	LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
    LEFT OUTER JOIN usuario t1 ON (servicio.id_tecnico1=t1.id)
    LEFT OUTER JOIN usuario t2 ON (servicio.id_tecnico2=t2.id)
    LEFT OUTER JOIN usuario t3 ON (servicio.id_tecnico3=t3.id)
    LEFT OUTER JOIN usuario t4 ON (servicio.id_tecnico4=t4.id)
    where 1=1
    $filtros 
    order by servicio.fecha desc, servicio.id desc
     limit $offset,".app_reg_por_pag
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a  href="#" onclick="servicio_abrir(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td>'.$row["eltipo"].'</td>
                <td>'.$row["codigo_alterno"].' '.$row["nombre"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.$row["elestado"].'</td>     
                <td>'.implode("<br>", array_filter([$row['tecnico'],$row['tecnico2'],$row['tecnico3'],$row['tecnico4'] ])) .'</td>
                <td>'.$row["nota_operaciones"].'</td>
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
<form id="forma_ver" name="forma_ver" >
 <fieldset id="fs_forma">
    <div class="row">  
   
        <div class="col-sm">
            <?php 
           echo campo("numero","Numero",'number','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>

         <div class="col-sm">
            <?php 
           echo campo("tipo","Tipo",'select',valores_combobox_db('servicio_tipo_mant','','nombre','','',''),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
         <div class="col-sm">
            <?php 
           echo campo("estado","Estado",'select',valores_combobox_texto('<option value="1">Pendientes</option><option value="2">Realizadas</option>','1'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
            <a id="btn-filtro" href="#" onclick="limpiar_tabla('tabla'); procesar_tabla('tabla','forma_ver'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
   
 </fieldset>
</form>
</div>


 


<div class="table-responsive ">
    <table id="tabla" data-url="servicio_ver_autorizar.php?a=1" data-page="0" class="table table-striped table-hover table-sm"  style="width:100%">
        <thead class="thead-dark">
            <tr>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Vehiculo</th>
                <th>Placa</th>
                <th>Estado</th>
                <th>Tecnico</th>                
                <th>Nota Operaciones</th>
                     
            </tr>
        </thead>
        <tbody id="tablabody">
            
        </tbody>
       
    </table>
    <div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla','forma_ver'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a>
    </div>
</div>


<?php 



?>

<script>

 //$('#pagina-botones').html('<a href="#" onclick="abrir_producto(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');
    procesar_tabla('tabla','forma_ver');

     $("#numero" ).focus();


    function servicio_abrir(codigo){
        
        get_page_switch('pagina','servicio_mant.php?a=v&cid='+codigo,'Orden de Servicio') ;
    }

    

</script>

</div>


 