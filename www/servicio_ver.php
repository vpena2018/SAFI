<?php
require_once ('include/framework.php');
pagina_permiso(25);

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
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.=" and servicio.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tipo'])) { $tmpval=sanear_int($_REQUEST['tipo']); if (!es_nulo($tmpval)){$filtros.=" and servicio.id_tipo_mant = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and servicio.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and servicio.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and producto.placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and producto.chasis like ".GetSQLValue($tmpval,'like');} }
    
    if (isset($_REQUEST['id_tecnico'])) { $tmpval=sanear_int($_REQUEST['id_tecnico']); if (!es_nulo($tmpval)){$filtros.=' AND (servicio.id_tecnico1='.GetSQLValue($tmpval,'int').' OR servicio.id_tecnico2='.GetSQLValue($tmpval,'int').' OR servicio.id_tecnico3='.GetSQLValue($tmpval,'int').' OR servicio.id_tecnico4='.GetSQLValue($tmpval,'int').')' ;}   }
    if (isset($_REQUEST['id_tipo_revision'])) { $tmpval=sanear_int($_REQUEST['id_tipo_revision']); if (!es_nulo($tmpval)){$filtros.=" and servicio.id_tipo_revision = ".GetSQLValue($tmpval,'int') ;}   }
    
   
    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and fecha BETWEEN '$fdesde' AND '$fhasta' " ;
    }
    
    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }


    $datos="";

    $result = sql_select("SELECT servicio.id, servicio.fecha,  servicio.numero, servicio.numero_alterno, fecha_hora_final
	,producto.codigo_alterno,producto.nombre,producto.placa
    ,servicio.id_estado
	,servicio_estado.nombre AS elestado
	,servicio_tipo_mant.nombre AS eltipo
    ,servicio.nota_operaciones
    ,servicio.observaciones_realizado
    ,servicio.observaciones_adpc
    ,t1.nombre AS tecnico
    ,t2.nombre AS tecnico2
    ,t3.nombre AS tecnico3
    ,t4.nombre AS tecnico4
    ,t5.usuario AS auditado
    ,entidad.codigo_alterno AS cliente
    ,entidad.nombre AS nombrecliente
    ,case 
        when servicio.estado_paro_por_repuesto='A' then 'Activo'
        when servicio.estado_paro_por_repuesto='I' then 'Inactivo' 
    end
    AS estadoparoporrepuesto
    ,(select A.mod_citas from inspeccion A where A.id=servicio.id_inspeccion limit 1) as mod_citas    
	FROM servicio
	LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
	LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
	LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
    LEFT OUTER JOIN usuario t1 ON (servicio.id_tecnico1=t1.id)
    LEFT OUTER JOIN usuario t2 ON (servicio.id_tecnico2=t2.id)
    LEFT OUTER JOIN usuario t3 ON (servicio.id_tecnico3=t3.id)
    LEFT OUTER JOIN usuario t4 ON (servicio.id_tecnico4=t4.id)
    LEFT OUTER JOIN usuario t5 ON (servicio.id_usuario_auditado=t5.id)
    LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)   	
    where 1=1
    $filtros
    order by servicio.fecha desc, servicio.id desc
"
    );

   
  

    if ($result!=false){
        if ($result -> num_rows > 0) {
            
            $datos='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Numero</th>
                    <th>Fecha Creacion</th>
                    <th>Fecha Completada</th> 
                    <th>Tipo</th>
                    <th>Vehiculo</th>
                    <th>Cliente</th>                    
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Cita</th>
                    <th>Tecnico</th>
                    <th>Nota Operaciones</th>
                    <th>Observaciones Tecnico</th>    
                    <th>Revision ADPC</th>    
                    <th>Comentario ADPC</th>                                          
                </tr>
            </thead>
            <tbody>';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                if ($perfilTecnico!=2){
                    $adpc=trim($row["observaciones_adpc"]);
                }else{
                   $adpc=""; 
                }
                $color="";
                if ($row["estadoparoporrepuesto"]=='Activo' and $row["id_estado"]==7){
                   $color="yellow";
                } 
                $datos.='<tr>
                <td bgcolor='.$color.'><a  href="#" onclick="servicio_abrir(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td bgcolor='.$color.'>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td bgcolor='.$color.'>'.formato_fecha_de_mysql($row["fecha_hora_final"]).'</td>
                <td bgcolor='.$color.'>'.$row["eltipo"].'</td>
                <td bgcolor='.$color.'>'.$row["codigo_alterno"].' '.$row["nombre"].'</td>
                <td bgcolor='.$color.'>'.trim($row["cliente"]).' '.trim($row["nombrecliente"]).'</td>
                <td bgcolor='.$color.'>'.$row["placa"].'</td>
                <td bgcolor='.$color.'>'.$row["elestado"].'</td>
                <td bgcolor='.$color.'>'.$row["mod_citas"].'</td>
                <td bgcolor='.$color.'>'.implode("<br>", array_filter([$row['tecnico'],$row['tecnico2'],$row['tecnico3'],$row['tecnico4'] ])) .'</td>
                <td bgcolor='.$color.'>'.$row["nota_operaciones"].'</td>
                <td bgcolor='.$color.'>'.trim($row["observaciones_realizado"]).'</td>
                <td bgcolor='.$color.'>'.$row["auditado"].'</td>                
                <td bgcolor='.$color.'>'.$adpc.'</td>                
                </tr>';               
               
            }

            $datos.='</tbody></table>  ';

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
           echo campo("tipo","Tipo",'select',valores_combobox_db('servicio_tipo_mant','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
         <div class="col-sm">
            <?php 
           echo campo("estado","Estado",'select',valores_combobox_db('servicio_estado','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
           echo campo("id_tecnico","Mecanico",'select',valores_combobox_db('usuario','','nombre',' where activo=1 and grupo_id=2 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
                <?php
                 echo campo("id_tipo_revision","Tipo Revision",'select',valores_combobox_db('servicio_tipo_revision','','nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
                ?>
            </div>

            <div class="col-sm">
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','servicio_ver.php?a=1','Ordenes de Servicio','forma_ver');  return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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


<script>

 //$('#pagina-botones').html('<a href="#" onclick="abrir_producto(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#numero" ).focus();


    function servicio_abrir(codigo){
        
        get_page_switch('pagina','servicio_mant.php?a=v&cid='+codigo,'Orden de Servicio') ;
    }


</script>

</div>


 