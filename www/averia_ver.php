<?php
require_once ('include/framework.php');
pagina_permiso(34);

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
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.=" and averia.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tipo'])) { $tmpval=sanear_int($_REQUEST['tipo']); if (!es_nulo($tmpval)){$filtros.=" and averia.id_tipo = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and averia.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and averia.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['contacto'])) { $tmpval=sanear_string($_REQUEST['contacto']); if (!es_nulo($tmpval)){$filtros.=" and averia.contacto like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['cliente'])) { $tmpval=sanear_string($_REQUEST['cliente']); if (!es_nulo($tmpval)){$filtros.=" and entidad.nombre like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and producto.placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and producto.chasis like ".GetSQLValue($tmpval,'like');} }
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and fecha BETWEEN '$fdesde' AND '$fhasta' " ;
    }


    $datos="";

    $result = sql_select("SELECT averia.id, averia.fecha,  averia.numero, averia.numero_alterno
	,producto.codigo_alterno,producto.nombre,producto.placa,averia.observaciones_adpc
	,averia_estado.nombre AS elestado
    ,averia_tipo.nombre AS eltipo
    ,entidad.nombre as elcliente
    ,usuario.usuario as auditado
	FROM averia
	LEFT OUTER JOIN producto ON (averia.id_producto=producto.id)
    LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
	LEFT OUTER JOIN averia_tipo ON (averia.id_tipo=averia_tipo.id)
	LEFT OUTER JOIN averia_estado ON (averia.id_estado=averia_estado.id)
    LEFT OUTER JOIN usuario ON (averia.id_usuario_auditado=usuario.id)
	
    where 1=1
    $filtros
    order by averia.fecha desc, averia.id desc
     "
    );//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Numero</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Vehiculo</th>
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Revision ADPC</th>    
                    <th>Comentario ADPC</th>                            
                </tr>
            </thead>
            <tbody id="tablabody">
                
            ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                if (tiene_permiso(163)){
                    $adpc=trim($row["observaciones_adpc"]);
                 }else{
                    $adpc=""; 
                }
                $datos.='<tr>
                <td><a  href="#" onclick="averia_abrir(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td>'.$row["eltipo"].'</td>
                <td>'.$row["elcliente"].'</td>
                <td>'.$row["codigo_alterno"].' '.$row["nombre"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.$row["elestado"].'</td>
                <td>'.$row["auditado"].'</td>
                <td>'.$adpc.'</td>
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
           echo campo("tipo","Tipo",'select',valores_combobox_db('averia_tipo','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
         <div class="col-sm">
            <?php 
           echo campo("estado","Estado",'select',valores_combobox_db('averia_estado','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
             echo campo("cliente","Cliente",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
        <div class="col-sm">
              <?php 
             echo campo("contacto","Contacto",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
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
        <!-- <div class="col-sm">
              <?php 
             echo campo("vin","VIN",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div> -->

       

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
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','averia_ver.php?a=1','Ordenes de Averia','forma_ver'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
 
   
 </fieldset>
</form>
</div>


 


<div id="tablaver"  class="table-responsive ">
    
   
</div>
 <div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <!-- <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a> -->
    </div>

<?php 



?>

<script>

 //$('#pagina-botones').html('<a href="#" onclick="abrir_producto(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#numero" ).focus();


    function averia_abrir(codigo){
        
        get_page_switch('pagina','averia_mant.php?a=v&cid='+codigo,'Orden de Avería') ;
    }

    

</script>

</div>


 