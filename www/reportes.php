<?php
require_once ('include/framework.php');
pagina_permiso(8);


 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {

    $reportes_disponibles="";

	$result = sql_select("SELECT id,nombre, programa
	FROM usuario_nivel
	WHERE nivel_padre_id=8 and activo=1 AND id<>8 AND nivel_categoria_id=3
    order by orden");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
            while ($row = $result -> fetch_assoc()) {
               if (tiene_permiso($row['id'])) { $reportes_disponibles.='<a href="#" onclick="filtros_reporte('.$row['id'].',this); return false;" class="xreportes list-group-item list-group-item-action" style="padding: .15rem 0.55rem">'.$row['nombre'].'</a>';                }
            }			 
		}
	}

} // fin leer datos



?>
<div class="card-body">


<div class="row mb-2 d-print-none">
<div class="col-sm col-md-5">
<h6>Reportes Disponibles:</h6>
    <div class="list-group mb-3">
        <?php echo $reportes_disponibles; ?>
    </div>
</div>
<div class="col-sm col-md-7 maxancho600">
<h6>Selecione los filtros del reporte:</h6><hr>
    	<div class="form-group">
		  
	 
        <form id="forma_rep" name="forma_rep" class="oculto">
        <fieldset id="fs_forma">
           
            
            
            <?php 
                echo campo("idrep","idrep",'hidden','',' ',' ');
		        echo campo("nombrerep","nombrerep",'hidden','',' ',' ');

        	?>

                <div id="repfecha" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php echo campo("fdesde","Fecha Desde",'date','',' ',' '); ?>
                    </div>
                    <div class="col-sm">
                        <?php echo campo("fhasta","Fecha Hasta",'date','',' ',' '); ?>
                    </div>
                </div>

               <div id="reptienda" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("id_tienda","Tienda",'select',valores_combobox_db('tienda',$_SESSION['tienda_id'],'nombre','','','Todas'),' ',' ');           ?>
                    </div>                   
                </div>

                <div id="repcliente" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo("cliente_id","Cliente",'select2ajax','',' ','','get.php?a=2&t=1','');         ?>
                    </div>                   
                </div>


                <div id="repproducto" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("id_producto","Vehiculo",'select2ajax','',' ',' ','get.php?a=3&t=1','');            ?>
                    </div>                   
                </div>

                <div id="repplaca" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("placa","Placa",'text','',' ',' ','','');            ?>
                    </div>                   
                </div>



                <div id="repmecanico" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php 
                         if (tiene_permiso(105)) {
                            echo  campo('id_tecnico', 'Mecanico','select2',valores_combobox_db('usuario','','nombre',' where activo=1 and grupo_id=2 ','','Todos'),' ','  ','');           
                        } else {
                            echo  campo('id_tecnico', '','hidden',$_SESSION['usuario_id'],' ','  ','');          
                        }   
                        ?>
                    </div>                   
                </div>

                <div id="replavadores" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php 
                        if (tiene_permiso(105)) {
                            echo  campo('id_lavador', 'Lavador','select2',valores_combobox_db('usuario','','nombre',' where activo=1 and grupo_id=6 ','','Todos'),' ','  ','');           
                        } else {
                            echo  campo('id_lavador', '','hidden',$_SESSION['usuario_id'],' ','  ','');          
                        }   
                        ?>
                    </div>                   
                </div>

            
                <div id="repmotoristas" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php 
                        if (tiene_permiso(105) or tiene_permiso(122)) {
                            echo  campo('id_motorista', 'Motorista','select2',valores_combobox_db('usuario','','nombre',' where activo=1 and (grupo_id=3 or perfil_adicional=3)','','Todos'),' ','  ','');          
                        } else {
                            echo  campo('id_motorista', '','hidden',$_SESSION['usuario_id'],' ','  ','');          
                        }                      
                        
                        ?>
                    </div>                   
                </div>

             
               
                <div id="repactividades" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo("id_actividad","Actividad",'select2ajax','','class="form-control" style="width: 100%" ','','get.php?a=3&t=3&ff=1','');          ?>
                    </div>                   
                </div>


                <div id="reptipo_averia" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo('id_tipo_averia', 'Tipo Averia','select2',valores_combobox_db('averia_tipo','','nombre',' ','','Todos'),' ','  ','');           ?>
                    </div>                   
                </div>

                <div id="reptipo_causa" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo('id_tipo_causa', 'Tipo Causa de Averia','select2',valores_combobox_db('averia_tipo_causa','','nombre',' ','','Todos'),' ','  ','');           ?>
                    </div>                   
                </div>

                <div id="reptipo_revision" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo('id_tipo_revision', 'Tipo de Revision','select2',valores_combobox_db('servicio_tipo_revision','','nombre',' ','','Todos'),' ','  ','');           ?>
                    </div>                   
                </div>

                <div id="repaveria_coaseguro" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("averia_coaseguro","Coaseguro",'select',valores_combobox_texto('<option value="0">Todas</option><option value="01">Solo con Coaseguro</option><option value="02">Solo sin Coaseguro</option>',''),' ',' ');           ?>
                    </div> 
                    <div class="col-sm">
                        <?php  echo campo("averia_deducible","Deducible",'select',valores_combobox_texto('<option value="0">Todas</option><option value="01">Solo con Deducible</option><option value="02">Solo sin Deducible</option>',''),' ',' ');           ?>
                    </div>                    
                </div>

                <div id="repactividad_repuesto" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("actividad_repuesto","Actividades / Repuestos",'select',valores_combobox_texto('<option value="0">Todas</option><option value="3">Solo Actividades</option><option value="2">Solo Repuestos</option>',''),' ',' ');           ?>
                    </div>                   
                </div>

                <div id="repestado_cita" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo('id_estado_cita', 'Estado','select2',valores_combobox_db('cita_estado','','nombre',' ','','Todos'),' ','  ','');           ?>
                    </div>                   
                </div>
               

                <div id="repnotodas" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("notodas","Unicamente los que tienen inspección",'checkbox','',' ',' checked ');           ?>
                    </div>                   
                </div>

                <div id="repestado_traslado" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo('id_estado_traslado', 'Estado','select2',valores_combobox_db('orden_traslado_estado','','nombre',' ','','Todos'),' ','  ','');           ?>
                    </div>                   
                 </div>

                <div id="repcosto" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("costo","Incluir Costo",'select',valores_combobox_texto('<option value="01">Si</option><option value="02">No</option>',''),' ',' ');           ?>
                    </div>                    
                </div>

                <div id="repestado_os" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo  campo('id_estado_os', 'Estado','select2',valores_combobox_db('servicio_estado','','nombre',' ','','Todos'),' ','  ','');           ?>
                    </div>                   
                </div>

                <div id="repfechac" class="row repfiltro  mb-1">
                    <div class="col-sm">
                        <?php  echo campo("fechac","Seleccione la Fecha del filtrado",'select',valores_combobox_texto('<option value="01">Fecha Creacion</option><option value="02">Fecha Completado</option>',''),' ',' ');           ?>
                    </div>                    
                </div>

                <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
                    <div class="row">
                
                        <div class="col-sm text-center center"><a href="#" onclick="procesar_reportes('reportes_generar.php?a=p','forma_rep',''); return false;" class="btn btn-outline-secondary  mb-2 xfrm" ><i class="fa fa-print"></i> Generar</a></div>
                
                
                    </div>
                </div>

                </fieldset>
                </form>

   
		  
		 </div>


</div>


</div>




<!-- Reporte -->
<div class="row mb-2">
<div class="col">
    <div id="reportebox" class="table-responsive">


    </div>
</div>
</div>

</div>
<script>

function filtros_reporte(cod,objeto){
    $(".xreportes").removeClass("list-group-item-primary");

    $(objeto).addClass("list-group-item-primary");
    $("#idrep").val(cod);
    $("#nombrerep").val($(objeto).text());


    $(".repfiltro").hide();

    switch (cod) {
        case 54: //Reporte de Vehiculos / Ultima Inspección
            $("#repnotodas").show();
        break;
    
        case 55:// 	Reporte de Historial de Mantenimiento de un Vehiculo
            $("#repproducto").show();
        break;

        case 56:// 	Reporte de Ordenes de Compra Facturables
            $("#repfecha").show();
            $("#reptienda").show();
        break;

        case 57:// 	Reporte Entradas y Salidas de Vehiculos	
            $("#repfecha").show();
            $("#repproducto").show();
            $("#reptienda").show();
            
        break;

        case 58:// 	Reportes del Desempeño de los Mecánicos	
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repmecanico").show();            
            
        break;

        case 73:// 	Reporte de averias
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repproducto").show();
            $("#repplaca").show();
            $("#repcliente").show();
            $("#repactividades").show();
            $("#repaveria_coaseguro").show();
            $("#reptipo_averia").show();
            $("#reptipo_causa").show();
         
        break;

        case 74:// 	Reporte de ordenes servicio
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repproducto").show();
            //$("#repplaca").show();
            $("#repcliente").show();
            $("#reptipo_revision").show();
            $("#repmecanico").show();
            $("#repcosto").show();
            $("#repestado_os").show();
            $("#repfechac").show();

        break;


        case 154:// 	Reporte de Ordenes de Servicio Cobrables
            $("#repfecha").show();
            $("#reptienda").show();
            // $("#repproducto").show();
            // $("#repplaca").show();
            // $("#repcliente").show();
            // $("#repmecanico").show();
 
        break;


        case 155:// 	Reporte de Ordenes de Servicio Cobrables Detallado
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repproducto").show();
            $("#repplaca").show();
            $("#repcliente").show();
            $("#repactividad_repuesto").show();
        
            
        break;

        case 156:// 	Reporte de Ordenes de Servicio en Paro por Repuesto
         //   $("#repfecha").show();
            $("#reptienda").show();
         //   $("#repproducto").show();
         //   $("#repplaca").show();
         //   $("#repcliente").show();
          //  $("#repmecanico").show();
 
        break;


        case 157:// 	Reporte de Vehículos Para Mantenimiento Segun Kilometraje Recorrido

          //  $("#reptienda").show();
         //   $("#repproducto").show();

 
        break;

        case 78:// 	Reportes del Desempeño de los Lavadores
            $("#repfecha").show();
            $("#reptienda").show();
            $("#replavadores").show();                 
        break;

        case 79:// 	Reportes del Desempeño de los Motoristas
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repmotoristas").show();                              
        break;


        case 102:// 	Reporte de Actividades / Horas de los Mecánicos	
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repmecanico").show();
            
            
        break;

        case 103:// 	Reporte de ordenes servicio DETALLADO
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repproducto").show();
            $("#repplaca").show();
            $("#repcliente").show();
            $("#repactividad_repuesto").show();
        
            
        break;

        case 104:// 	Reporte de ordenes Averia DETALLADO
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repproducto").show();
            $("#repplaca").show();
            $("#repcliente").show();
             $("#repactividad_repuesto").show();
    
        break;



        case 118:// 	Reporte Consumo de Combustible	
            $("#repfecha").show();
            $("#reptienda").show();
            
        break;

        case 119:// 	Reporte Ordenes de Servicio Creadas a Partir de Hojas de Inspección
            $("#repfecha").show();
            $("#reptienda").show();
            
        break;

        case 120:// 	Reporte Averías Creadas a Partir de una Hoja de Inspección
            $("#repfecha").show();
            $("#reptienda").show();
            
        break;

        case 121:// 	Reporte Kilometraje por Vehículo
            $("#repfecha").show();
            $("#reptienda").show();
            
        break;

        case 122:// 	Reporte Entregas a Domicilio
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repmotoristas").show();
            
        break;

         case 136:// 	Reporte de citas
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repproducto").show();          
            $("#repcliente").show();
            $("#repestado_cita").show();
            
        break;

        case 147:// 	Reporte traslado vehiculo
            $("#repfecha").show();
            $("#reptienda").show();
            $("#repmotoristas").show();
            $("#repestado_traslado").show();
        break;

        case 173:// 	Reporte Vehiculo Nissan
            $("#repfecha").show();            
        break;

    //    case 173:// 	Reporte Vehiculo Nissan
    //        $("#repfecha").show();            
    //    break;

        case 174:// 	Reporte de Vehiculos Dias en Talleres
            $("#repfecha").show();  
            $("#reptienda").show();
            $("#repcliente").show(); 
            $("#repproducto").show();  

        break;

        case 179:// 	Reporte de HI Creadas
            $("#repfecha").show();  
            $("#reptienda").show();
        break;

      
    }
	
	
	


    $("#forma_rep").show();

}


function procesar_reportes(url,forma,adicional){
	 

    errores='';
    if ( $("#repfecha").is(":visible")) {
        if ($("#fdesde").val()=='' || $("#fhasta").val()=='') {
            errores='Debe ingresar las fechas';
        }
    }
    
    if (errores=='') {



    $("#"+forma+" .xfrm").addClass("disabled");		
	cargando(true); 
    $("#reportebox").html('');
			
	var datos=$("#"+forma).serialize();

	 $.post( url,datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);

				$("#reportebox").html(json[0].pdata);
			
                $('html, body').animate({ scrollTop: $('#reportebox').offset().top-80 }, 900);
			
			}
		} else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}		  
	  })
	  .done(function() {	   
	  })
	  .fail(function(xhr, status, error) {
	   		cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	  })
	  .always(function() {	   
		$("#"+forma+" .xfrm").removeClass("disabled");	
	  });



        
    } else {
        mytoast('warning',errores,3000) ;  
    }
		
}
</script>