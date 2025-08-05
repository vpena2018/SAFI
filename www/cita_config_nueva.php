<?php
require_once ('include/framework.php');
pagina_permiso(133);

$accion ="";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 

// guardar Datos    ############################ 
if ($accion=="g") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

    if (isset($_REQUEST['tienda'])) { $tienda = intval($_REQUEST['tienda']); } else   {$tienda ='';}
    if (isset($_REQUEST['taller'])) { $taller = intval($_REQUEST['taller']); } else   {$taller ='';}
    if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (isset($_REQUEST['dia1_cant'])) { $dia1_cant = intval($_REQUEST['dia1_cant']); } else   {$dia1_cant ='0';}
    if (isset($_REQUEST['dia2_cant'])) { $dia2_cant = intval($_REQUEST['dia2_cant']); } else   {$dia2_cant ='0';}
    if (isset($_REQUEST['dia3_cant'])) { $dia3_cant = intval($_REQUEST['dia3_cant']); } else   {$dia3_cant ='0';}
    if (isset($_REQUEST['dia4_cant'])) { $dia4_cant = intval($_REQUEST['dia4_cant']); } else   {$dia4_cant ='0';}
    if (isset($_REQUEST['dia5_cant'])) { $dia5_cant = intval($_REQUEST['dia5_cant']); } else   {$dia5_cant ='0';}
    if (isset($_REQUEST['dia6_cant'])) { $dia6_cant = intval($_REQUEST['dia6_cant']); } else   {$dia6_cant ='0';}
    if (isset($_REQUEST['dia7_cant'])) { $dia7_cant = intval($_REQUEST['dia7_cant']); } else   {$dia7_cant ='0';}
    
    if (isset($_REQUEST['hora1_cant'])) { $hora1_cant = intval($_REQUEST['hora1_cant']); } else   {$hora1_cant ='0';}
    if (isset($_REQUEST['hora2_cant'])) { $hora2_cant = intval($_REQUEST['hora2_cant']); } else   {$hora2_cant ='0';}
    if (isset($_REQUEST['hora3_cant'])) { $hora3_cant = intval($_REQUEST['hora3_cant']); } else   {$hora3_cant ='0';}
    if (isset($_REQUEST['hora4_cant'])) { $hora4_cant = intval($_REQUEST['hora4_cant']); } else   {$hora4_cant ='0';}
    if (isset($_REQUEST['hora5_cant'])) { $hora5_cant = intval($_REQUEST['hora5_cant']); } else   {$hora5_cant ='0';}
    if (isset($_REQUEST['hora6_cant'])) { $hora6_cant = intval($_REQUEST['hora6_cant']); } else   {$hora6_cant ='0';}
    if (isset($_REQUEST['hora7_cant'])) { $hora7_cant = intval($_REQUEST['hora7_cant']); } else   {$hora7_cant ='0';}
    //Validar
	$verror="";
    if (es_nulo($fdesde) or es_nulo($fhasta)) {
        $verror.="debe ingresar las fechas";
    }

    try {
        $begin = new DateTime($fdesde);
        $end = new DateTime($fhasta);
        $end->setTime(0,0,1);
    } catch (\Throwable $th) {
        $verror="Ingrese una fecha valida";
    }


    if ($verror=="") {

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        
        //Borrar actuales
        $sql="DELETE FROM cita_disponible WHERE
                tienda=".$tienda."
                AND taller=".$taller."
                AND fecha>=".GetSQLValue($fdesde,"text")."
                AND fecha<=".GetSQLValue($fhasta,"text")."
               ";
        sql_delete($sql);

        foreach ($period as $dt) {
            $diasemana=$dt->format('w');
            $cantidad=0;
            $cantidad_por_hora=0;
            switch ($diasemana) {
                case 1: $cantidad=$dia1_cant  ; $cantidad_por_hora=$hora1_cant ;  break;
                case 2: $cantidad=$dia2_cant  ; $cantidad_por_hora=$hora2_cant ;   break;
                case 3: $cantidad=$dia3_cant  ; $cantidad_por_hora=$hora3_cant ;   break;
                case 4: $cantidad=$dia4_cant  ; $cantidad_por_hora=$hora4_cant ;   break;
                case 5: $cantidad=$dia5_cant  ; $cantidad_por_hora=$hora5_cant ;   break;
                case 6: $cantidad=$dia6_cant  ; $cantidad_por_hora=$hora6_cant ;   break;
                case 0: $cantidad=$dia7_cant  ; $cantidad_por_hora=$hora7_cant ;   break;
            }

            $sql="INSERT INTO cita_disponible SET
                tienda=".$tienda.",
                taller=".$taller.",
                fecha=".GetSQLValue($dt->format('Y-m-d'),"text").",
                dia_semana=".$diasemana.",
                cantidad=".$cantidad.",
                cantidad_por_hora=".$cantidad_por_hora
                ;
            

            if ($cantidad>0) {
                 $result = sql_insert($sql);
            }

        }

        

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ='Generado Satisfactoriamente';



    } else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] =$verror;
        $stud_arr[0]["pcid"] = 0;
    }

        salida_json($stud_arr);
        exit;

} // fin guardar datos




?>


<div class="card-body">

<div class="maxancho600 mx-auto">

<div class="row">
<div class="col">
    	<div class="form-group">
		  
	 
	<form id="forma" name="forma">
		<fieldset id="fs_forma">
			<div class="">
            <?php 
           echo campo("tienda","Tienda",'select','',' ',' onchange="cita_cargar_talleres()" ');
           echo campo("taller","Taller",'select','',' ',' ');
           echo campo("rfdesde","Fecha Desde",'date','',' ',' ');
           echo campo("rfhasta","Fecha Hasta",'date','',' ',' ');


            ?>
            <div class="maxancho400 mx-auto">
            <div class="row mb-2 mt-3 bg-info text-white "> 
                <div class="col ">       
                    Dia de Semana          
                </div>
                <div class="col ">       
                   Cantidad Diario             
                </div>
                <div class="col ">       
                   Cantidad x Media Hora             
                </div>
            </div>

            <div class="row mb-2"> 
                <div class="col text-center">       
                    Lunes           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia1_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora1_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>

            <div class="row mb-2"> 
                <div class="col text-center">       
                    Martes           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia2_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora2_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>
            <div class="row mb-2"> 
                <div class="col text-center">       
                    Miercoles           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia3_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora3_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>
            <div class="row mb-2"> 
                <div class="col text-center">       
                    Jueves           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia4_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora4_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>
            <div class="row mb-2"> 
                <div class="col text-center">       
                    Viernes           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia5_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora5_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>
            <div class="row mb-2"> 
                <div class="col text-center">       
                    Sabado           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia6_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora6_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>

            <div class="row mb-2"> 
                <div class="col text-center">       
                    Domingo           
                </div>
                <div class="col">       
                    <?php 
                    echo campo("dia7_cant","Cupo Diario",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
                <div class="col">       
                    <?php 
                    echo campo("hora7_cant","x Media Hora",'number',0 ,' ',' min="0" max="999"','');				
                    ?>              
                </div>
            </div>

            </div>

            </div>

            <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
                <div class="row">
             
                    <div class="col-sm text-center"><a href="#" onclick="procesar('cita_config_nueva.php?a=g','forma',''); return false;" class="btn btn-primary  mb-2 xfrm" ><i class="fa fa-check"></i>  Generar</a></div>
      	
                <!-- <div class="col-sm"><a href="#" onclick="$('#ModalWindow').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  Cerrar</a></div> -->
                </div>
            </div>

        </fieldset>
	</form>

    </div>




</div>


</div>


 


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

        ultmomes=new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).getDate();
        desde= new Date(hoy.getFullYear(),hoy.getMonth(),1);
        hasta= new Date(hoy.getFullYear(),hoy.getMonth(),ultmomes);
        $('#rfdesde').val(fechaISOLocal(desde));
        $('#rfhasta').val(fechaISOLocal(hasta));

</script>

</div>