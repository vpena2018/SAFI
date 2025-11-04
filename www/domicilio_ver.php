<?php
require_once ('include/framework.php');
pagina_permiso(123);

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
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.=" and orden_domicilio.numero = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and orden_domicilio.id_estado = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and orden_domicilio.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['id_motorista'])) { $tmpval=sanear_int($_REQUEST['id_motorista']); if (!es_nulo($tmpval)){$filtros.=" and orden_domicilio.id_motorista=".GetSQLValue($tmpval,'int') ;}  }
    if (isset($_REQUEST['tipoest'])) { $tmpval=sanear_int($_REQUEST['tipoest']); 
     
        
        if (($tmpval==0)){
            $filtros.=" AND (orden_domicilio.id_estado=1 or orden_domicilio.id_estado=2)";
         } else {
            $filtros.=" AND orden_domicilio.id_estado=$tmpval"; 
         }
    }

    if (!tiene_permiso(129)) { //ver todos usuarios
        $filtros.=" AND ( orden_domicilio.id_usuario=".$_SESSION['usuario_id']." or orden_domicilio.id_motorista=".$_SESSION['usuario_id'].")"; 
    }

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

    $result = sql_select("SELECT orden_domicilio.* 
	,producto.codigo_alterno,producto.nombre,producto.placa
	,orden_domicilio_estado.nombre AS elestado
	,l1.usuario AS motorista
    ,l2.usuario AS solicitante
	

	FROM orden_domicilio
	LEFT OUTER JOIN producto ON (orden_domicilio.id_producto=producto.id)
	LEFT OUTER JOIN orden_domicilio_estado ON (orden_domicilio.id_estado=orden_domicilio_estado.id)
	LEFT OUTER JOIN usuario l1 ON (orden_domicilio.id_motorista=l1.id)
    LEFT OUTER JOIN usuario l2 ON (orden_domicilio.id_usuario=l2.id)

	
    where  1=1
    $filtros
    order by orden_domicilio.fecha desc, orden_domicilio.id desc
     "
    );//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla_domicilio"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th></th>
                    <th>Orden #</th>
                    
                    <th>Vehiculo</th>
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Asignado</th>
                    <th>Solicitante</th>     
                </tr>
            </thead>
            <tbody id="tablabody">
                
           ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $acclavar="Atender";
                $acclavar_btn="btn-warning";

                if ($row["id_estado"]==1 and (es_nulo($row["id_motorista"]) )) {
                    $acclavar="Asignar" ;
                    $acclavar_btn="btn-info";
                }

                if ($row["id_estado"]==2 ) {
                    $acclavar="Completar" ;
                    $acclavar_btn="btn-success";
                }

                if ($row["id_estado"]==3 ) {
                    $acclavar="Ver" ;
                    $acclavar_btn="btn-secondary";
                }
                $datos.='<tr>
                <td><a  href="#" onclick="domicilio_abrir(\''.$row["id"].'\'); return false;" class="btn btn-sm '.$acclavar_btn.' ">'.$acclavar.'</a></td>
                <td align="center">'.($row["numero"]).'</td>
            
                <td>'.$row["codigo_alterno"].' '.$row["nombre"].'</td>
               <td>'.$row["placa"].'</td>
               <td>'.$row["elestado"].'</td>
               <td>'.$row["motorista"].'</td>
               <td>'.$row["solicitante"].'</td>
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
             echo campo("nombre","Vehiculo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>

   
         <div class="col-sm">
            <?php 
           echo campo("tienda","Tienda",'select',valores_combobox_db('tienda',$_SESSION['tienda_id'],'nombre','','','Todas'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>

         <div class="col-sm">
            <?php 
           echo campo("tipoest","Tipo",'select',valores_combobox_db('orden_domicilio_estado','','nombre','','','Todos Pendientes'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>
         </div>

         <div class="col-sm">
           <?php 
           echo campo("id_motorista","Motorista",'select',valores_combobox_db('usuario','','nombre',' where activo=1 and (grupo_id=3 or perfil_adicional=3)','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
            <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','domicilio_ver.php?a=1','Entregas a Domicilio'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
    
   
 </fieldset>
</form>
</div>


 


<div id="tablaver" class="table-responsive ">
    
   
</div>
 <div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <!-- <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla_domicilio'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a> -->
    </div>

<?php 



?>

<script>

$('#pagina-botones').html('<a href="#" onclick="domicilio_abrir(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');


     $("#numero" ).focus();

    
     $("#btn-filtro" ).click();

    function domicilio_abrir(codigo){
        
    
        modalwindow(
            'Entrega a Domicilio',
            'domicilio_mant.php?a=v&cid='+codigo
            );

    }

    

</script>

</div>


 