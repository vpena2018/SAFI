<?php
require_once ('include/framework.php');
pagina_permiso(22);

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
    $perfilTenico=0;
	$perfilTecnico=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);

    if (isset($_REQUEST['pg'])) { $pagina = sanear_int($_REQUEST['pg']); }
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.=" and inspeccion.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tipo'])) { $tmpval=sanear_int($_REQUEST['tipo']); if (!es_nulo($tmpval)){$filtros.=" and inspeccion.tipo_inspeccion = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and inspeccion.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and inspeccion.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and producto.placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }
    
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and producto.chasis like ".GetSQLValue($tmpval,'like');} }
    if (isset($_REQUEST['cliente_id'])) { $tmpval=sanear_string(trim($_REQUEST['cliente_id'])); if (!es_nulo($tmpval)){ $filtros.=" and entidad.codigo_alterno like ".GetSQLValue($tmpval,'like') ;} }
   
    if (isset($_REQUEST['tipo_doc'])) { $tmpval=sanear_int(trim($_REQUEST['tipo_doc'])); if (!es_nulo($tmpval)){ $filtros.=" and inspeccion.tipo_doc = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['id_estado'])) { $tmpval=sanear_int(trim($_REQUEST['id_estado'])); if (!es_nulo($tmpval)){ $filtros.=" and inspeccion.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
       
    if (isset($_REQUEST['id_usuario'])) { $tmpval=sanear_int($_REQUEST['id_usuario']); if (!es_nulo($tmpval)){$filtros.=' AND (inspeccion.id_usuario='.GetSQLValue($tmpval,'int').')' ;}   }

    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and fecha BETWEEN '$fdesde' AND '$fhasta' " ;
    }


    $datos="";

    $result = sql_select("SELECT inspeccion.id, inspeccion.fecha, inspeccion.tipo_inspeccion, inspeccion.numero, inspeccion.numero_alterno
	,inspeccion.tipo_inspeccion_especial
    ,producto.codigo_alterno,producto.nombre,producto.placa
	,inspeccion_estado.nombre AS elestado
    ,usuario.nombre AS motorista  
    ,aud.usuario AS auditor
    ,observaciones_adpc
    ,tipo_doc   
    ,mod_citas
    FROM inspeccion
	LEFT OUTER JOIN producto ON (inspeccion.id_producto=producto.id)
	LEFT OUTER JOIN inspeccion_estado ON (inspeccion.id_estado=inspeccion_estado.id)
    LEFT OUTER JOIN usuario ON (inspeccion.id_usuario=usuario.id)
    LEFT OUTER JOIN entidad ON (inspeccion.cliente_id=entidad.id)
    LEFT OUTER JOIN usuario aud ON (inspeccion.id_usuario_auditado=aud.id)
    where 1=1
    $filtros
    order by inspeccion.fecha desc,inspeccion.id desc
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
                    <th>Vehiculo</th>
                    <th>Descripcion</th>
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Tipo Mov.</th>
                    <th>Motorista</th>
                    <th>Cita</th>
                    <th>Revision ADPC</th>                    
                    <th>Comentario ADPC</th>     
                </tr>
            </thead>
            <tbody id="tablabody">
                
           ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {                
                $vehiculo=$row["codigo_alterno"];
                if ($perfilTecnico!=3){
                    $adpc=trim($row["observaciones_adpc"]);
                 }else{
                    $adpc=""; 
                 }
                $datos.='<tr>
                <td><a  href="#" onclick="insp_abrir(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>';
                if ($row["tipo_inspeccion_especial"]==1) {
                    $datos.=' <td>Especial</td>';
                } else {
                   $datos.=' <td>'.get_tipo_inspeccion($row["tipo_inspeccion"]).'</td>'; 
                }
               $datos.=' <td>'.$vehiculo.'</td>
               <td>'.$row["nombre"].'</td>
               <td>'.$row["placa"].'</td>
               <td>'.$row["elestado"].'</td>
               <td>'.get_tipo_doc($row["tipo_doc"]).'</td>               
               <td>'.$row["motorista"].'</td>
               <td>'.$row["mod_citas"].'</td>
               <td>'.$row["auditor"].'</td>
               <td>'.$adpc.'</td>
               </tr>';               
               
            }

            $datos.=' </tbody>
           
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
           echo campo("tipo","Tipo",'select',valores_combobox_texto(app_tipo_inspeccion,'','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
         <div class="col-sm">
            <?php 
          // echo campo("estado","Estado",'select',valores_combobox_db('inspeccion_estado','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
             echo campo("tipo_doc","Movimiento",'select',valores_combobox_texto(app_tipo_doc,'','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
              <?php         
                echo campo("cliente_id","Codigo de Cliente",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
              ?>  
        </div>

        <div class="col-sm">
            <?php 
           echo campo("id_estado","Estado",'select',valores_combobox_db('inspeccion_estado','','nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>

        <div class="col-sm">
           <?php 
           echo campo("id_usuario","Motorista",'select',valores_combobox_db('usuario','','nombre',' where activo=1 and (grupo_id=3 or perfil_adicional=3)','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','inspeccion_ver.php?a=1','Hojas de Inspeccion','forma_ver'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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

     $("#numero" ).focus();


    function insp_abrir(codigo){
      
        get_page_switch('pagina','inspeccion_mant.php?a=v&cid='+codigo,'Hoja de Inspección') ;
    }

    

</script>

</div>


 