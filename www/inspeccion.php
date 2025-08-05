<?php
require_once ('include/framework.php');
pagina_permiso(20);

$tipo_registro="1";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else {$accion="";}
if (isset($_REQUEST['t'])) { $tipo_registro = sanear_int($_REQUEST['t']); } 

if ($accion=="1") { // mostrar vehiculos
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
    if (isset($_REQUEST['codigo'])) { $tmpval=sanear_string($_REQUEST['codigo']); if (!es_nulo($tmpval)){$filtros.=" and codigo_alterno like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['placa'])) { $tmpval=sanear_string($_REQUEST['placa']); if (!es_nulo($tmpval)){$filtros.=" and placa like ".GetSQLValue($tmpval,'like') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ if ($tmpval=='camion') { $filtros.=" and nombre like '%camion %'";   } else { $filtros.=" and nombre like ".GetSQLValue($tmpval,'like');}} }
    if (isset($_REQUEST['vin'])) { $tmpval=sanear_string(trim($_REQUEST['vin'])); if (!es_nulo($tmpval)){ $filtros.=" and chasis like ".GetSQLValue($tmpval,'like');} }
     if (isset($_REQUEST['tipo_vehiculo'])) { $tmpval=sanear_string(trim($_REQUEST['tipo_vehiculo'])); if (!es_nulo($tmpval)){ $filtros.=" and tipo_vehiculo = ".GetSQLValue($tmpval,'text');} }
    
    if (isset($_REQUEST['tipo_insp'])) { $tipo_insp=sanear_string($_REQUEST['tipo_insp']);  }
    if (isset($_REQUEST['tipo_doc'])) { $tipo_doc=sanear_string($_REQUEST['tipo_doc']);  }
    if (isset($_REQUEST['empresa'])) { $empresa=sanear_string($_REQUEST['empresa']);  }
    if (isset($_REQUEST['tipo_veh'])) { $tipo_veh=sanear_string($_REQUEST['tipo_veh']);  }

    if (isset($_REQUEST['codigo_veh'])) { $codigo_veh=sanear_string($_REQUEST['codigo_veh']);  }
    if (isset($_REQUEST['codigo_insp'])) { $codigo_insp=sanear_string($_REQUEST['codigo_insp']);  }



    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

    $habilitado="producto.habilitado=1";
    $tipo_producto=app_tipo_vehiculo;

    $datos="";
    $sql="";
    $orderby="";
    

    if (($tipo_insp=="Taller" or $tipo_insp=="Especial") and $tipo_doc=="entrada") {
            $sql="SELECT '' as id_inspeccion, id, codigo_alterno, nombre, codigo_grupo,  tipo, 
            marca, anio, modelo, cilindrada, serie, motor, placa, tipo_vehiculo, chasis 
            FROM producto
            where $habilitado and $tipo_producto";
    }
    
    if ($tipo_insp=="Taller" and $tipo_doc=="salida") {
      $sql="SELECT inspeccion.id as id_inspeccion, producto.id, producto.codigo_alterno, producto.nombre, producto.codigo_grupo,  producto.tipo, 
      producto.marca, producto.anio, producto.modelo, producto.cilindrada, producto.serie, producto.motor, producto.placa, producto.tipo_vehiculo, producto.chasis 
      FROM inspeccion
      LEFT OUTER JOIN producto ON (producto.id=inspeccion.id_producto)
      where $habilitado and $tipo_producto
      and inspeccion.tipo_doc=1 and inspeccion.tipo_inspeccion=2
      and inspeccion.id_estado=2
      ";
      $orderby="order by inspeccion.hora desc, inspeccion.id desc";
      //
     // BUG and inspeccion.id_tienda=
    }
    
    
    if ($tipo_insp=="Renta" and $tipo_doc=="entrada") {
        $sql="SELECT inspeccion.id as id_inspeccion, producto.id, producto.codigo_alterno, producto.nombre, producto.codigo_grupo,  producto.tipo, 
        producto.marca, producto.anio, producto.modelo, producto.cilindrada, producto.serie, producto.motor, producto.placa, producto.tipo_vehiculo, producto.chasis 
        FROM inspeccion
        LEFT OUTER JOIN producto ON (producto.id=inspeccion.id_producto)
        where $habilitado and $tipo_producto
        and inspeccion.tipo_doc=2 and inspeccion.tipo_inspeccion=1
        and inspeccion.id_estado=2
        ";  
        $orderby="order by inspeccion.hora desc, inspeccion.id desc";
          // BUG  : and inspeccion.id_tienda=
          
    }
    
    if ($tipo_insp=="Renta" and $tipo_doc=="salida") {
        $sql="SELECT '' as id_inspeccion, producto.id, producto.codigo_alterno, producto.nombre, producto.codigo_grupo,  producto.tipo, 
        producto.marca, producto.anio, producto.modelo, producto.cilindrada, producto.serie, producto.motor, producto.placa, producto.tipo_vehiculo, producto.chasis 
        FROM producto
        where $habilitado and $tipo_producto";     
    }
 
    $result = sql_select($sql." 
        $filtros
        $orderby
        limit $offset,".app_reg_por_pag
    );

    if ($result!=false){
        if ($result -> num_rows > 0) { 
            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {
                $datos.='<tr>
                <td><a  href="#" onclick="insp_vehiculo(\''.$row["id"].'\',\''.$row["id_inspeccion"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["codigo_alterno"].'</a></td>
                <td>'.$row["nombre"].'</td>
                <td>'.$row["placa"].'</td>
                <td>'.$row["anio"].'</td>
               <td><small>'.$row["chasis"].'</small></td>
                </tr>';               
               
            }

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


    <p class="card-text">Seleccione una opción:</p>

    
    <div id="pnl-1" class="panel-inspeccion">
        <div class="row "> 
        <div class="col-md mb-5  text-center "> 
             <a href="#" onclick="$('#tipo_insp').val('Taller'); navegar_panel(2); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-tools"></i><br>Inspección Taller</a>
        </div>
        <div class="col-md mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_insp').val('Renta'); navegar_panel(12); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-car-side"></i><br>Inspección Renta</a>   
        </div>
      
        <?php if (tiene_permiso(171)){ ?>
            <div class="col-md mb-5  text-center "> 
                 <a href="#" onclick="$('#tipo_insp').val('Especial'); $('#tipo_doc').val('entrada'); $('#estado').val('1'); navegar_panel(14); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-tools"></i><br>Inspección Especial</a>
            </div>
        <?php } ?>    
        
      </div> 
    </div>

    <!-- TALLER -->
    <div id="pnl-2" class="panel-inspeccion oculto">
        <div class="row "> 
        <div class="col-md mb-5  text-center "> 
             <a href="#" onclick="$('#tipo_doc').val('entrada'); $('#estado').val('1'); navegar_panel(14) ; return false;" class="btn btn-outline-info btn-lg "   ><i class="fa fa-tools"></i><br>Entrada<br> Ingreso al Taller</a>
        </div>
        <div class="col-md mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_doc').val('salida'); $('#estado').val('2'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-tools"></i><br>Salida<br>Salida del Taller</a>   
        </div>
        </div> 
        <p class=" mt-16"><a id="btnregresar_panel" href="#" onclick="navegar_panel(1); return false;" class="btn btn-outline-secondary  "   ><i class="fa fa-angle-left"></i> Regresar</a></p>

    </div>

 
    
    <!-- RENTA -->
    <div id="pnl-12" class="panel-inspeccion oculto">
        <div class="row "> 
        <div class="col-md mb-5  text-center "> 
             <a href="#" onclick="$('#tipo_doc').val('salida');  $('#estado').val('2'); $('#empresa').val('0'); navegar_panel(14); return false;" class="btn btn-outline-info btn-lg "   ><i class="fa fa-car-side"></i><br>Salida<br>Inicio de Alquiler</a>
        </div>
        <div class="col-md mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_doc').val('entrada'); $('#estado').val('1'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-car-side"></i><br>Entrada<br>Devolver Alquiler</a>   
        </div>
        </div> 
        <p class=" mt-16"><a id="btnregresar_panel" href="#" onclick="navegar_panel(1); return false;" class="btn btn-outline-secondary  "   ><i class="fa fa-angle-left"></i> Regresar</a></p>

    </div>

    <div id="pnl-13" class="panel-inspeccion oculto">
        <div class="row "> 
        <div class="col-md mb-5  text-center "> 
             <a href="#" onclick="$('#empresa').val('1'); navegar_panel(14); return false;" class="btn btn-outline-info btn-lg "   ><i class="fa fa-car-side"></i><br> HERTZ</a>
        </div>
        <div class="col-md mb-5  text-center"> 
            <a href="#" onclick="$('#empresa').val('2'); navegar_panel(14); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-car-side"></i><br> DOLLAR</a>   
        </div>
        </div> 
        <p class=" mt-16"><a id="btnregresar_panel" href="#" onclick="navegar_panel(1); return false;" class="btn btn-outline-secondary  "   ><i class="fa fa-angle-left"></i> Regresar</a></p>

    </div>

    <div id="pnl-14" class="panel-inspeccion oculto">
        
        <h5 class="card-title mb-3">Tipo de Vehiculo</h5>
        <div class="row "> 
        <div class="col-lg mb-5  text-center "> 
             <a href="#" onclick="$('#tipo_veh').val('turismo'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "   ><i class="fa fa-car"></i><br> Turismo</a>
        </div>
        <div class="col-lg mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_veh').val('camioneta'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-car-side"></i><br> Camioneta</a>   
        </div>
        <div class="col-lg mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_veh').val('pickup'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-truck-pickup"></i><br> Pickup</a>   
        </div>
        <div class="col-lg mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_veh').val('microbus'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-shuttle-van"></i><br> &nbsp; Bus &nbsp; </a>   
        </div>
        <div class="col-lg mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_veh').val('camion'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-truck"></i><br> Camion</a>   
        </div>
        <div class="col-lg mb-5  text-center"> 
            <a href="#" onclick="$('#tipo_veh').val('cuatrimoto'); navegar_panel(15); return false;" class="btn btn-outline-info btn-lg "  ><i class="fa fa-biking"></i><br> Cuatrimoto</a>   
        </div>
        </div> 
        <p class=" mt-16"><a id="btnregresar_panel" href="#" onclick="navegar_panel(1); return false;" class="btn btn-outline-secondary  "   ><i class="fa fa-angle-left"></i> Regresar</a></p>

    </div>



    <div id="pnl-15" class="panel-inspeccion-full oculto">
    <h5 class="card-title mb-3">Seleccione el Vehiculo</h5>
                        <div class="">


                            <div class="botones_accion d-print-none " >
                            <form id="forma" name="forma" >
                            <fieldset id="fs_forma">
                                <div class="row">  
                            
                                    <div class="col-sm">
                                        <?php 
                                    echo campo("codigo","Codigo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
                                        ?>
                                    </div>
                                
                                    <div class="col-sm">
                                        <?php 
                                        echo campo("nombre","Descripción",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
                                        ?>  
                                    </div>
                                    <div class="col-sm">
                                        <?php 
                                        echo campo("tipo_vehiculo","Tipo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
                                        <a id="btn-filtro" href="#" onclick="limpiar_tabla('tabla'); procesar_tabla('tabla'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
                                        
                                    </div>
                                </div>

                                <input id="tipo_insp" name="tipo_insp" type="hidden" value="" >
                                <input id="tipo_doc" name="tipo_doc" type="hidden" value="" >
                                <input id="empresa" name="empresa" type="hidden" value="" >
                                <input id="tipo_veh" name="tipo_veh" type="hidden" value="" >
                                <input id="codigo_veh" name="codigo_veh" type="hidden" value="" >
                                <input id="codigo_insp" name="codigo_insp" type="hidden" value="" >
                                <input id="estado" name="estado" type="hidden" value="" >
                            
                            </fieldset>
                            </form>
                            </div>


                            

                            

                            <div class="table-responsive ">
                                <table id="tabla" data-url="inspeccion.php?a=1" data-page="0" class="table table-striped table-hover table-sm"  style="width:100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Descripción</th>
                                            <th>Placa</th>
                                            <th>Año</th>
                                            <th>VIN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablabody">
                                        
                                    </tbody>
                                
                                </table>
                                <div class="botones_accion d-print-none " >
                                <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
                                <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a>
                                </div>
                            </div>

                    </div>
        
        

        <p class=" mt-16"><a id="btnregresar_panel" href="#" onclick="navegar_panel(1); return false;" class="btn btn-outline-secondary  "   ><i class="fa fa-angle-left"></i> Regresar</a></p>

    </div>


    <p>&nbsp;</p>
    <p>&nbsp;</p>
 

  

</div>





<script>


    function navegar_panel(numpanel){

        if (numpanel==15) {
            var tiptxt='';
            switch ($('#tipo_veh').val()) {
                case 'pickup':
                    tiptxt='PICK UP';
                    break;
                case 'microbus':
                    tiptxt='BUS';
                    break;
            
                default:
                    tiptxt= $('#tipo_veh').val();
                    break;
            }
            $('#tipo_vehiculo').val(tiptxt);
            limpiar_tabla('tabla');
           
        }

        $(".panel-inspeccion").hide();
        $(".panel-inspeccion-full").hide();
        $("#pnl-"+numpanel).show();

        if (numpanel==15) {
            $('#codigo').focus();
        }

        if (numpanel==1) {
            $("#tipo_insp").val('');
            $("#tipo_doc").val('');
            $("#empresa").val('');
            $("#tipo_veh").val('');
            $("#codigo_veh").val('');
            $("#codigo_insp").val('');   
        }
    }


     function insp_vehiculo(cod_vehiculo,cod_inspeccion){
    
        $("#codigo_veh").val(cod_vehiculo);
        $("#codigo_insp").val(cod_inspeccion);
 
        insp_crear_nueva();
 
     }

    function insp_crear_nueva(){
        var tipo_insp= $("#tipo_insp").val();
        var tipo_doc= $("#tipo_doc").val();
        var empresa= $("#empresa").val();
        var tipo_veh= $("#tipo_veh").val();
        var codigo_veh= $("#codigo_veh").val();
        var codigo_insp= $("#codigo_insp").val();
        var estado= $("#estado").val();
        var url='';
        var tie='';
        if (tipo_insp=='Renta') { 
            url='inspeccion_mant.php?ti=1';}
        else { 
            url='inspeccion_mant.php?ti=2';}

        if (tipo_insp=='Especial') {
            tie=1;
        }

        url=url+'&td='+estado; //tipo doc=estado
        url=url+'&em='+empresa;
        url=url+'&tv='+tipo_veh;
        url=url+'&cv='+codigo_veh;
        url=url+'&idant='+codigo_insp;
        url=url+'&retorno='+codigo_insp;
        url=url+'&tie='+tie;
    

        get_page('pagina',url,'Hoja de Inspección - '+tipo_insp) ;
    }

 





</script>