<?php
require_once ('include/framework.php');
pagina_permiso(185);

$accion ="";
$tipo_entidad="1";
$Edit=0;

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 
if (isset($_REQUEST['t'])) { $tipo_entidad = sanear_int($_REQUEST['t']); } 

if (isset($_REQUEST['Edit'])) { $Edit = sanear_int($_REQUEST['Edit']); } 



if($accion==2)
{
    echo 'hola';
    exit;
}

if ($accion=="1") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="No se encontraron Datos";
    $stud_arr[0]["pdata"] ="";
    $stud_arr[0]["pmas"] =0;

    $pagina=1;
    $offset=0;
    $haymas=0;
    $filtros="";

        if (isset($_REQUEST['pg'])) { $pagina = sanear_int($_REQUEST['pg']); }
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.="and ave.id = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if ($tmpval==2){$filtros.=" and (desc_aprob IS null or desc_aprob<>1)" ;} else {$filtros.=" and desc_aprob=1" ;}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and tienda.id = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (prod.nombre  like ".GetSQLValue($tmpval,'like')." or prod.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }

    /*$tmpval=sanear_int($_SESSION['usuario_id']); if (!es_nulo($tmpval)){$filtros.=" AND (averia.id_usuario=$tmpval  OR averia.id_tecnico1=$tmpval)" ;} */


        if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }
        $datos="";

        $result = sql_select("SELECT ave.id num_averia,prod.nombre vehiculo, cliente.nombre cliente,(ave_detalle.cantidad* ave_detalle.precio_costo) valor,  ave.fecha, tienda.nombre tienda,ave_detalle.desc_aprob
        FROM averia ave
        INNER JOIN tienda ON tienda.id=ave.id_tienda
        INNER JOIN entidad cliente ON cliente.id=ave.cliente_id
        INNER JOIN producto prod ON prod.id= ave.id_producto
        INNER JOIN averia_detalle ave_detalle ON ave.id=ave_detalle.id_maestro
        WHERE 1=1  AND ave_detalle.producto_codigoalterno='DESC AVERIA'
        $filtros
        order by ave.fecha desc, ave.id desc
        limit $offset,".app_reg_por_pag);

        if ($result!=false){
            if ($result -> num_rows > 0) { 
                if ($result -> num_rows>=app_reg_por_pag) {$haymas=1;  }
                while ($row = $result -> fetch_assoc()) {

            $style = ($row["desc_aprob"] == 1) 
                ? ' style="background-color:#d4edda;"'   // verde claro
                : '';

            $datos .= '<tr'.$style.'>
                       
                    <td><a  href="#" onclick="averia_abrir(\''.$row["num_averia"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["num_averia"].'</a></td>
                    <td>'.$row["vehiculo"].'</td>
                    <td>'.$row["cliente"].'</td>
                    <td>L '.number_format($row["valor"],2).'</td>
                    <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                    <td>'.$row["tienda"].'</td>  
                    <td style="text-align:left;">'.($row["desc_aprob"] == 1 ? '✅ Aprobado' : '⚠️ Pendiente').'</td>           
                    </tr>';
                }

                $stud_arr[0]["pcode"] = 1;
                $stud_arr[0]["pmsg"] ="Datos encontrados";
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
              echo campo("estado","Estado",'select',valores_combobox_texto('<option value="1">Aprobados</option><option value="2">No Aprobado</option>','2'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
            <a id="btn-filtro" href="#" onclick="limpiar_tabla('tabla'); procesar_tabla('tabla','forma_ver'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i>  <?php echo "Buscar"; ?></a>
            
        </div>

    </div>
   
 </fieldset>
</form>
</div>


<div class="table-responsive ">
    <table id="tabla" data-url="averia_ver_descuentos.php?a=1" data-page="0" class="table table-striped table-hover table-sm"  style="width:100%">
        <thead class="thead-dark">
            <tr>
                <th>Numero Ave.</th>
                <th>Vehiculo</th>
                <th>cliente</th>
                <th>Valor</th>
                <th>Fecha</th>
                <th>Tienda</th>    
                <th>Aprobado</th>                     
            </tr>
        </thead>
        <tbody id="tablabody">
            
        </tbody>
       
    </table>
    <div class="botones_accion d-print-none " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <a id="cargando_mas" href="#" onclick="procesar_tabla('tabla','forma_ver'); return false;" class="btn btn-sm btn-info mr-2 mb-2"><i class="fa fa-angle-double-down"></i>  <?php echo "Cargar ".app_reg_por_pag." Mas"; ?></a>
    </div>
</div>


<script>

    <?php
    if($accion==1 or $accion==""){?>
        procesar_tabla('tabla','forma_ver');
        $("#numero" ).focus();
    <?php } ?>

    //procesar_tabla('tabla','forma_ver');
    // $("#numero" ).focus();


    function averia_abrir(codigo){
        
        //get_page_switch('pagina','averia_mant.php?a=v&cid='+codigo,'Orden de Averia') ;
        //modalwindow('Editar','averia_ver_descuentos.php?a=2&Edit=1');

        modalwindow('Editar','averia_ver_descuentos.php?a=2&cid=' + codigo);
    }

</script>

</div>