<?php
require_once ('include/framework.php');
pagina_permiso(132);

function get_tipo_servicio($codigo) {
    switch ($codigo) {
        case 1:
            return "Preventivo";
            break;
        case 2:
            return "Correctivo";
            break;        
        
        default:
             return "Preventivo";
            break;
    }

   
}

function get_tipo_sino($codigo) {
    switch ($codigo) {
        case 0:
            return "Si";
            break;
        case 1:
            return "No";
            break;                
        default:
             return "No Definido";
            break;
    }   
}

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
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.=" and cita.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tipo'])) { $tmpval=sanear_int($_REQUEST['tipo']); if (!es_nulo($tmpval)){$filtros.=" and cita.tipo = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and cita.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and cita.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['taller'])) { $tmpval=sanear_int($_REQUEST['taller']); if (!es_nulo($tmpval)){$filtros.=" and cita.id_taller = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and producto.placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }
    //if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and producto.chasis like ".GetSQLValue($tmpval,'like');} }
    
    if (isset($_REQUEST['id_estado'])) { $tmpval=sanear_int(trim($_REQUEST['id_estado'])); if (!es_nulo($tmpval)){ $filtros.=" and cita.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    
    if (isset($_REQUEST['externo'])) { $tmpval=sanear_int(trim($_REQUEST['externo'])); if (!es_nulo($tmpval)){ $filtros.=" and cita_taller.externo = ".GetSQLValue($tmpval,'int') ;}   }
    
    
    //if (isset($_REQUEST['id_usuario'])) { $tmpval=sanear_int($_REQUEST['id_usuario']); if (!es_nulo($tmpval)){$filtros.=' AND (cita.id_usuario='.GetSQLValue($tmpval,'int').')' ;}   }


    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and fecha_cita BETWEEN '$fdesde' AND '$fhasta' " ;
    }


    $datos="";

    $result = sql_select("SELECT cita.id, cita.fecha, cita.fecha_cita,cita.hora_cita,cita.cliente_contacto, cita.numero, cita.numero_alterno
	,cita.tipo,cita.plataforma
    ,producto.codigo_alterno,producto.nombre,producto.placa
	,cita_estado.nombre AS elestado
    ,cita_horario.nombre as lahora
    ,inspeccion.numero AS numinspecion
    ,servicio.numero AS numservicio
    ,cita.cliente_contacto_telefono AS clientetel
    ,entidad.codigo_alterno AS cliente
    ,entidad.nombre AS nombrecliente	
    ,cita_taller.taller_nombre AS eltaller
    FROM cita
    LEFT OUTER JOIN producto ON (cita.id_producto=producto.id)
    LEFT OUTER JOIN cita_estado ON (cita.id_estado=cita_estado.id)
    LEFT OUTER JOIN cita_horario ON (cita.hora_cita=cita_horario.id)
    LEFT OUTER JOIN inspeccion ON (cita.id_inspeccion=inspeccion.id)
    LEFT OUTER JOIN servicio ON (servicio.id_inspeccion=inspeccion.id)
    LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)  
    INNER JOIN cita_taller ON (cita.id_taller=cita_taller.id_taller)
    where 1=1
    $filtros
    order by cita.fecha_cita ,cita.hora_cita 
     "
    );//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Numero</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Vehiculo</th>
                    <th>Cliente</th>
                    <th>Taller</th>                    
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Contacto</th>
                    <th>#Inspeccion</th>  
                    <th>#Servicio</th>   
                    <th>#Telefono</th>   
                    <th>Plataforma</th>   
                </tr>
            </thead>
            <tbody id="tablabody">
                
           ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a  href="#" onclick="modalwindow(\'Cita\',\'cita_mant.php?mm=1&a=v&cid='.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha_cita"]).'</td>
                <td>'.$row["lahora"].'</td>
                <td>'.get_tipo_servicio($row["tipo"]).'</td>
                <td>'.$row["codigo_alterno"].' '.$row["nombre"].'</td>
                <td>'.$row["cliente"].' '.$row["nombrecliente"].'</td>
                <td>'.$row["eltaller"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.$row["elestado"].'</td>
                <td>'.$row["cliente_contacto"].'</td>
                <td>'.$row["numinspecion"].'</td>
                <td>'.$row["numservicio"].'</td>
                <td>'.$row["clientetel"].'</td>
                <td>'.get_tipo_sino($row["plataforma"]).'</td>
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
<form id="forma" name="forma" >
 <fieldset id="fs_forma">
    <div class="row">  
   
        <div class="col-sm">
            <?php 
           echo campo("numero","Numero",'number','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>

         <div class="col-sm">
            <?php 
              echo campo("tipo","Tipo",'select',valores_combobox_texto(app_tipo_servicio,'','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>
        
         <div class="col-sm">
            <?php 
           echo campo("tienda","Tienda",'select',valores_combobox_db('tienda',$_SESSION['tienda_id'],'nombre','','','Todas'),' ',' '); // onchange="cargar_talleres()"
            ?>
         </div>
         <div class="col-sm">
            <?php 
           echo campo("taller","Taller",'select',valores_combobox_db('cita_taller','','taller_nombre','','','Todas','id_taller'),' ',' ');
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
           echo campo("id_estado","Estado",'select',valores_combobox_db('cita_estado','','nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>

      

    </div>


   

    <div class="row"> 
            <div class="col-sm">
              <?php 
               echo campo("externo","Tipo de Taller",'select',valores_combobox_texto(app_taller_externo,'','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
               ?>
            </div>

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
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','cita_ver.php?a=1','Citas Programadas'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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



    

</script>

</div>