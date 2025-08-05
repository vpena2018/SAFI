<?php
require_once ('include/framework.php');
pagina_permiso(6);

$accion ="";

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 

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
    if (isset($_REQUEST['codigo'])) { $tmpval=sanear_string($_REQUEST['codigo']); if (!es_nulo($tmpval)){$filtros.=" and orden_compra.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and entidad.nombre like ".GetSQLValue($tmpval,'like');} }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and orden_compra.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and orden_compra.fecha BETWEEN '$fdesde' AND '$fhasta' " ;
    }


    $datos="";
    $result = sql_select("SELECT orden_compra.id, orden_compra.numero,orden_compra.SAP_sinc, orden_compra.fecha
    ,orden_compra.id_servicio
    ,servicio.numero AS numero_servicio
    ,averia.numero AS numero_averia
    ,entidad.nombre
    ,tienda.nombre AS nombre_tienda
    
    FROM orden_compra 
    LEFT OUTER JOIN servicio ON (orden_compra.id_servicio=servicio.id)
    LEFT OUTER JOIN averia ON (orden_compra.id_averia=averia.id)
    LEFT OUTER JOIN entidad ON (orden_compra.id_entidad=entidad.id)
    LEFT OUTER JOIN tienda ON (orden_compra.id_tienda=tienda.id)
    
                 
    where 1=1
    $filtros
    order by orden_compra.id desc
     "
    );//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.=' <table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>No. Orden</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>No. Servicio</th>
                    <th>No. Avería</th>
                    <th>Sincronizado</th>
                    <th>Tienda</th>
                </tr>
            </thead>
            <tbody id="tablabody">
                
            ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a href="#" onclick="modalwindow2(\'Orden de Compra\',\'compra_mant.php?a=v&cid='.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td>'.$row["nombre"].'</td>
                <td align="center">'.$row["numero_servicio"].'</td>
                <td align="center">'.$row["numero_averia"].'</td>
                <td>'.formato_fechahora_de_mysql($row["SAP_sinc"]).'</td>
                 <td>'.$row["nombre_tienda"].'</td>
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
           echo campo("codigo","No. Orden",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
     
        <div class="col-sm">
              <?php 
             echo campo("nombre","Proveedor",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
        <div class="col-sm">
              <?php 
             echo campo("tienda","Tienda",'select',valores_combobox_db('tienda',$_SESSION['tienda_id'],'nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','compra_ver.php?a=1','Ordenes de Compra'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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


   //  $("#nombre" ).focus();

  

    

</script>

</div>


 