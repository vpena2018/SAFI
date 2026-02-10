<?php
require_once ('include/framework.php');
pagina_permiso(166);

$accion ="";
$pruebasContrato = "N";

if (tiene_permiso(188)){
    $pruebasContrato="S";
}

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 
if (isset($_REQUEST['estado'])) { $id_estado = sanear_int($_REQUEST['estado']); } else {$id_estado="";}

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
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (producto.nombre like ".GetSQLValue($tmpval,'like')." or producto.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }         
    if (isset($_REQUEST['impuestos'])) { $tmpval=sanear_int($_REQUEST['impuestos']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_impuesto = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_tienda = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['factura'])) { $tmpval=sanear_int($_REQUEST['factura']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_factura = ".GetSQLValue($tmpval,'int') ;}   }

    if (es_nulo($id_estado)){
        $carShopPerfil=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
        if ($carShopPerfil=='18'){
            $filtros.=" and ventas.id_estado >=1  and ventas.id_estado < 20 ";   
        }else{
            $filtros.=" and ventas.id_estado < 20 ";   
        }    
    }else{   
       if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if (!es_nulo($tmpval)){$filtros.=" and ventas.id_estado = ".GetSQLValue($tmpval,'int') ;}   }      
    }   


    if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }

/*     if (isset($_REQUEST['rfdesde'])) { $fdesde = sanear_date($_REQUEST['rfdesde']); } else   {$fdesde ='';}
    if (isset($_REQUEST['rfhasta'])) { $fhasta = sanear_date($_REQUEST['rfhasta']); } else   {$fhasta ='';}
    if (!es_nulo($fdesde) and !es_nulo($fhasta)) {
        $filtros.=" and fecha BETWEEN '$fdesde' AND '$fhasta' " ;
    }
 */
  

    $datos="";
    $result = sql_select("SELECT ventas.id, ventas.fecha, ventas.numero, ventas.precio_minimo as pminimo, ventas.precio_maximo as pmaximo 
    ,ventas_estado.nombre AS elestado
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
    ,usuario.nombre AS elusuario
    ,ventas_impuestos.nombre as elimpuesto 
	 ,(SELECT COUNT(*) FROM ventas_fotos WHERE id_venta=ventas.id) fotos  
        FROM ventas
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)        
        LEFT OUTER JOIN ventas_estado ON (ventas.id_estado=ventas_estado.id)
        LEFT OUTER JOIN ventas_impuestos ON (ventas.id_impuesto=ventas_impuestos.id)
        LEFT OUTER JOIN usuario ON (ventas.id_usuario=usuario.id)
        
    where 1=1
  
    $filtros
    order by ventas.fecha desc, ventas.id desc
     ");//limit $offset,".app_reg_por_pag

    if ($result!=false){
        if ($result -> num_rows > 0) { 

            $datos.='<table id="tabla"  class="table table-striped table-hover table-sm"  style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Numero</th>
                    <th>Fecha</th>
                    <th>Vehiculo</th>                    
                    <th>Precio Minimo</th>
                    <th>Precio Maximo</th>
                    <th>Estado</th>
                    <th>Impuesto</th>
                    <th>Creado</th> 
                    <th>Fotos</th>        
                </tr>
            </thead>
            <tbody id="tablabody">
                
            ';

            if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
            while ($row = $result -> fetch_assoc()) {

                $fotos = $row["fotos"];

                // Definir color según el valor
                $btnClass = ($fotos > 0) ? 'btn-success text-white' : 'btn-secondary';

                $datos.='<tr>
                <td><a  href="#" onclick="abrir_ventas(\''.$row["id"].'\'); return false;" class="btn btn-sm btn-secondary">'.$row["numero"].'</a></td>
                <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                <td>'.$row["codvehiculo"]. ' ' .$row["vehiculo"].'</td>
                <td>'.$row["pminimo"].'</td>
                <td>'.$row["pmaximo"].'</td>
                <td>'.$row["elestado"].'</td>
                <td>'.$row["elimpuesto"].'</td>
                <td>'.$row["elusuario"].'</td>
                <td><a  href="#" onclick="abrir_ventana_foto(\''.$row["id"].'\'); return false;" class="btn btn-sm ' . $btnClass . '">' .$fotos.'</a></td>
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
         echo campo("nombre","Vehiculo",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
         /*echo campo("numero","Numero",'number','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');*/
       ?>
    </div>

    <div class="col-sm">
       <?php 
         echo campo("estado","Estado",'select',valores_combobox_db('ventas_estado','','nombre',' where ventas_reparacion=2 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
             echo campo("impuestos","Impuestos",'select',valores_combobox_db('ventas_impuestos','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
             /* echo campo("proveedor","Proveedor",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');*/
           ?>  
        </div>
        <div class="col-sm">
            <?php 
               echo campo("factura","Factura",'select',valores_combobox_db('ventas_factura','','nombre','','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>
        <div class="col-sm">
              <?php 
             echo campo("placa","Placa",'text','',' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
            ?>  
        </div>

    </div>

    <div class="row"> 
   <!--          <div class="col-sm text-right">
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
                <?php //echo campo("rfdesde","Fecha Desde",'date','',' ',' '); ?>
            </div>
            <div class="col-sm">
                <?php //echo campo("rfhasta","Fecha Hasta",'date','',' ',' '); ?>
            </div>
            <script>rf_fechas('hoy');</script> -->

            <div class="col-sm">
                 <a id="btn-filtro" href="#" onclick="procesar_tabla_datatable('tablaver','tabla','ventas_vehiculos_ver.php?a=1','Ventas de Vehiculos'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
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

 $('#pagina-botones').html('<a href="#" onclick="abrir_ventas(\'0\'); return false;" class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

     $("#nombre" ).focus();

    function abrir_ventas(codigo){
        var nuevo="";
        var contrato="<?php echo $pruebasContrato; ?>";

        if (codigo==0){
            nuevo="S"
        }else{
            nuevo="N"
        }
        
        if (contrato=="S"){
            modalwindow2(
                'Registro Venta de Vehiculo Pruebas Contrato',
                'ventas_mant_contrato.php?a=v&r='+nuevo+'&cid='+codigo
                );

        }else{
            modalwindow2(
                'Registro Venta de Vehiculo',
                'ventas_mant.php?a=v&r='+nuevo+'&cid='+codigo
                );
            
        }    
    }

function abrir_ventana_foto(codigo) {
    abrir_ventas(codigo);

    setTimeout(function () {
        ventas_cambiartab('nav_Fotos_venta');
        $('#insp_tabFotos').tab('show');
    }, 300); // dale un poco más de tiempo
}

    

</script>

</div>


 