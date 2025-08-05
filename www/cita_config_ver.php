<?php
require_once ('include/framework.php');
pagina_permiso(133);

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

 


if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and cita_disponible.tienda = ".GetSQLValue($tmpval,'int') ;}   }
if (isset($_REQUEST['taller'])) { $tmpval=sanear_int($_REQUEST['taller']); if (!es_nulo($tmpval)){$filtros.=" and cita_disponible.taller = ".GetSQLValue($tmpval,'int') ;}   }
if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
    $filtros.=" and cita_disponible.fecha BETWEEN '$fdesde' AND '$fhasta' " ;
}

    if (isset($_REQUEST['pg'])) { $pagina = sanear_int($_REQUEST['pg']); }
     if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    // $habilitado="habilitado=1";
    // if (isset($_REQUEST['inactivos'])) {$habilitado="habilitado=0"; }


    $datos="";

    $result = sql_select(" SELECT cita_disponible.id, cita_disponible.fecha, cita_disponible.dia_semana, cita_disponible.cantidad , cita_disponible.cantidad_por_hora
    ,(SELECT COUNT(*) FROM cita WHERE cita.id_estado<>20 AND cita.id_tienda=cita_disponible.tienda  AND cita.id_taller=cita_disponible.taller AND cita.fecha_cita=cita_disponible.fecha ) AS citas_hechas  
    FROM cita_disponible
        
    where 1=1

    $filtros
    ORDER BY cita_disponible.fecha
     "
    );//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                  
                    <th>Fecha</th>
                    <th>Dia</th>
                    <th>Asignadas</th>
                    <th>Agendadas</th>
                    <th>Disponibles</th>
                    <th>x Media Hora</th>
                </tr>
            </thead>
            <tbody id="tablabody">
                
            ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                //  <td><a  href="#" onclick="abrir_cita_config(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["id"].'</a></td>
                
               $agendadas=intval($row["citas_hechas"]);
               $disponibles=intval($row["cantidad"])-$agendadas;
                $datos.='<tr>
              
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td>'.dia_de_semana($row["dia_semana"]).'</td>
                <td>'.$row["cantidad"].'</td>
                <td>'.$agendadas.'</td>
                <td>'.$disponibles.'</td>
                <td>'.$row["cantidad_por_hora"].'</td>
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
           echo campo("tienda","Tienda",'select','',' ',' onchange="cita_cargar_talleres()" ');
            ?>
         </div>
         <div class="col-sm">
            <?php 
           echo campo("taller","Taller",'select','',' ',' ');
            ?>
         </div>

         <div class="col-sm">
                <?php echo campo("rfdesde","Fecha Desde",'date','',' ',' '); ?>
        </div>
        <div class="col-sm">
            <?php echo campo("rfhasta","Fecha Hasta",'date','',' ',' '); ?>
        </div>

        <div class="col-sm">
            <a id="btn-filtro" href="#" onclick="$('#tablaver').html('') ;procesar_tabla_datatable('tablaver','tabla','cita_config_ver.php?a=1',''); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
     <!-- <div class="row">
        <div class="col-sm">
              <?php 
             echo campo("inactivos","Mostrar Inactivos",'checkbox','1',' ','');
            ?>  
        </div>
     </div> -->
 </fieldset>
</form>
</div>


 


<div id="tablaver" class="table-responsive ">
    
 
</div>
<div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <!-- <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a> -->
    </div>
<p class=" mt-16"><a id="btnregresar_panel" href="#" onclick="get_page('pagina','cita_config.php','Configuración Citas') ; return false;" class="btn btn-outline-secondary  "   ><i class="fa fa-angle-left"></i> Regresar</a></p>
<?php 



?>

<script>




    function abrir_cita_config(codigo){

        modalwindow(
            'Dia',
            'cita_config_ver.php?a=v&cid='+codigo
            );
           
    }


 

function cita_cargar_sucursales(){
	var $sucursales = $('#tienda').empty();
	//$sucursales.append('<option value = "">Seleccione...</option>');
	for (i in d_tienda) {
	  $sucursales.append('<option value = "' + d_tienda[i][0] + '">' + d_tienda[i][1] + '</option>');
	}
}

    function cita_cargar_talleres(){
        var $talleres = $('#taller').empty();
			//	$talleres.append('<option value = "">Todos</option>');
				var sucursal_actual= $('#tienda').val();
				for (i in d_taller) {
					if (sucursal_actual==d_taller[i][2]) {
						$talleres.append('<option value = "' + d_taller[i][0] + '">' + d_taller[i][1] + '</option>');
					}			
				}

				
    }

    cita_cargar_sucursales();
    cita_cargar_talleres();
    var hoy = new Date();
        
        //hoy
        var desde = hoy;
        var hasta = hoy; 
        ultmomes=new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).getDate();
        // desde= new Date(hoy.getFullYear(),hoy.getMonth(),1);
        hasta= new Date(hoy.getFullYear(),hoy.getMonth(),ultmomes);
        $('#rfdesde').val(fechaISOLocal(desde));
        $('#rfhasta').val(fechaISOLocal(hasta));

</script>

</div>


 