<?php
require_once ('include/framework.php');
pagina_permiso(176);

$accion ="";
$tipo ="0";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 
/*if (isset($_REQUEST['estado'])) { $id_estado = sanear_int($_REQUEST['estado']); } else {$id_estado="";}*/
if (isset($_REQUEST['tipo'])) { $tipo = $_REQUEST['tipo']; } 

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
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }         
    if (isset($_REQUEST['pintura'])) { $tmpval=sanear_int($_REQUEST['pintura']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_estado_pintura = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['interior'])) { $tmpval=sanear_int($_REQUEST['interior']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_estado_interior = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['mecanica'])) { $tmpval=sanear_int($_REQUEST['mecanica']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_estado_mecanica = ".GetSQLValue($tmpval,'int') ;}   }      
    if (isset($_REQUEST['estado'])) { $tmpval = sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_estado = ".GetSQLValue($tmpval,'int') ;}   }   

    if ($tipo!="0"){
       $tmpval=sanear_int($_REQUEST['tipo']); if (!es_nulo($tmpval)){$filtros.=" and ventas.tipo_ventas_reparacion = ".GetSQLValue($tmpval,'int') ;}        
    }
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and fecha BETWEEN '$fdesde' AND '$fhasta' " ;
    }
 
  

    $datos="";
    $result = sql_select("SELECT ventas.id, ventas.fecha, ventas.numero, ventas.fecha_promesa
    ,ventas_estado.nombre AS elestado
    ,estado1.nombre AS elestado1
    ,estado2.nombre AS elestado2
    ,estado3.nombre AS elestado3
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
    ,usuario.nombre AS elusuario    
        FROM ventas
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)    
        LEFT OUTER JOIN ventas_estado ON (ventas.id_estado=ventas_estado.id)    
        LEFT OUTER JOIN ventas_estado estado1 ON (ventas.id_estado_pintura=estado1.id)
        LEFT OUTER JOIN ventas_estado estado2 ON (ventas.id_estado_interior=estado2.id)
        LEFT OUTER JOIN ventas_estado estado3 ON (ventas.id_estado_mecanica=estado3.id)
        LEFT OUTER JOIN usuario ON (ventas.id_usuario=usuario.id)        
    where 1=1  
    $filtros
    order by ventas.fecha desc, ventas.id desc
     ");//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Numero</th>
                    <th>Fecha</th>
                    <th>Fecha de Promesa</th>
                    <th>Vehiculo</th>   
                    <th>Estado</th>                                        
                    <th>Pintura</th>
                    <th>Interior</th>
                    <th>Mecanica</th>
                    <th>Creado</th>         
                </tr>
            </thead>
            <tbody id="tablabody">
                
            ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a  href="#" onclick="abrir_ventas(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td>'.formato_fecha_de_mysql($row["fecha_promesa"]).'</td>
                <td>'.$row["codvehiculo"]. ' ' .$row["vehiculo"].'</td>
                <td>'.$row["elestado"].'</td>
                <td>'.$row["elestado1"].'</td>
                <td>'.$row["elestado2"].'</td>
                <td>'.$row["elestado3"].'</td>
                <td>'.$row["elusuario"].'</td>
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
         echo campo("nombre","Vehiculo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
         /*echo campo("numero","Numero",'number','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');*/
       ?>
    </div>

    <div class="col-sm">
       <?php 
         echo campo("tienda","Tienda",'select',valores_combobox_db('tienda',$_SESSION['tienda_id'],'nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
       ?>
    </div>

    <div class="col-sm">
       <?php 
         echo campo("pintura","Pintura",'select',valores_combobox_db('ventas_estado','','nombre',' where ventas_reparacion=1 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
       ?>       
    </div>
    
       
    </div>


 <div class="row"> 

    <div class="col-sm">
           <?php 
             echo campo("interior","Interior",'select',valores_combobox_db('ventas_estado','','nombre',' where ventas_reparacion=1 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
           ?>  
        </div>
        <div class="col-sm">
            <?php 
               echo campo("mecanica","Mecanica",'select',valores_combobox_db('ventas_estado','','nombre',' where ventas_reparacion=1 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
        <div class="col-sm">
             <?php 
               echo campo("tipo","Estado del Vehiculo",'select',valores_combobox_texto('<option value="0">Todos</option><option value="1">Reparacion</option><option value="2">Venta</option>','1'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
             ?>  
        </div>

        <div class="col-sm">
             <?php 
               echo campo("estado","Estado de Venta",'select',valores_combobox_db('ventas_estado','','nombre',' where ventas_reparacion=2 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
             ?>       
       </div>
        
    </div>

    <div class="row"> 
            <div class="col-sm text-right">
                <div class="dropdown">
                    <a class="btn btn-light dropdown-toggle" href="#" role="button" id="rango_fechas" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Fechas
                    </a>
                    <div class="dropdown-menu" aria-labelledby="rango_fechas">
                        <a class="dropdown-item" href="#" onclick="rf_fechas('hoy'); return false;">Hoy</a>
                        <a class="dropdown-item" href="#" onclick="rf_fechas('semana'); return false;">Esta Semana</a>
                        <a class="dropdown-item" href="#" onclick="rf_fechas('mes'); return false;">Este Mes</a>
                        <a class="dropdown-item" href="#" onclick="rf_fechas('anio'); return false;">Este Año</a>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <?php echo campo("rfdesde","Fecha Desde",'date','',' ',' '); ?>
            </div>
            <div class="col-sm">
                <?php echo campo("rfhasta","Fecha Hasta",'date','',' ',' '); ?>
            </div>
            <script>rf_fechas('hoy');</script> 

            <div class="col-sm">
                 <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','vehiculos_reparacion_ver.php?a=1','Ventas de Vehiculos'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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

 $('#pagina-botones').html('<a href="#" onclick="abrir_ventas(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#nombre" ).focus();

    function abrir_ventas(codigo){
        var nuevo="";
        if (codigo==0){
            nuevo="S"
        }else{
            nuevo="N"
        }        
        modalwindow2(
            'Registro Vehiculo en Reparación',
            'vehiculos_reparacion_mant.php?a=v&r='+nuevo+'&cid='+codigo
            );
           
    }

    

</script>

</div>


 