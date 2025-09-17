<?php
require_once ('include/framework.php');
pagina_permiso(185);

$accion ="";
$tipo_entidad="1";
$Edit=0;


if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } 
if (isset($_REQUEST['t'])) { $tipo_entidad = sanear_int($_REQUEST['t']); } 

$cid= isset($_POST['cid']) ? intval($_POST['cid']) : 0;


if ($accion == "aprobar" && $cid > 0) {

      $stud_arr[0]["pcode"] = 0;
      $stud_arr[0]["pmsg"] ="ERROR";

    $result=sql_update("UPDATE averia_detalle SET desc_aprob=1 WHERE id=".$cid);

    if ($result == true) {

         $resultestado=sql_select("SELECT ave.id_estado,ave.id FROM averia ave INNER JOIN averia_detalle ave_detalle ON ave.id=ave_detalle.id_maestro WHERE ave_detalle.id=".$cid);
         $estado=0;
         $idave=0;

        if ($resultestado!=false){
            if ($resultestado -> num_rows > 0) { 
            $row = $resultestado -> fetch_assoc(); 

            $estado=$row["id_estado"];
            $idave=$row["id"];
            }
        } 

$sqlhistAveria = "INSERT INTO averia_historial_estado
(id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
VALUES (
    $idave, 
    ".$_SESSION['usuario_id'].", 
    ".$estado.", 
    'Descuento Aprobado id: ".$cid."', 
    NOW(), 
    'Se aprobó descuento de averia'
);";

        $resultHist = sql_insert($sqlhistAveria);

      $stud_arr[0]["pcode"] = 1;
      $stud_arr[0]["pmsg"] ="Descuento aprobado correctamente";
      $stud_arr[0]["pcid"] = $cid;

      require_once ('correo_averia_descuento_aviso.php');
    }



    salida_json($stud_arr);
    exit;

}


if ($accion == "anular" && $cid > 0) {

      $stud_arr[0]["pcode"] = 0;
      $stud_arr[0]["pmsg"] ="ERROR";

    $result=sql_update("UPDATE averia_detalle SET desc_aprob=2 WHERE id=".$cid);

    if ($result == true) {

         $resultestado=sql_select("SELECT ave.id_estado,ave.id FROM averia ave INNER JOIN averia_detalle ave_detalle ON ave.id=ave_detalle.id_maestro WHERE ave_detalle.id=".$cid);
         $estado=0;

        if ($resultestado!=false){
            if ($resultestado -> num_rows > 0) { 
            $row = $resultestado -> fetch_assoc(); 

            $estado=$row["id_estado"];
            $idave=$row["id"];
            }
        } 

$sqlhistAveria = "INSERT INTO averia_historial_estado
(id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
VALUES (
    $idave, 
    ".$_SESSION['usuario_id'].", 
    ".$estado.", 
    'Descuento denegado id: ".$cid."', 
    NOW(), 
    'Se denegó descuento de averia'
);";

        $resultHist = sql_insert($sqlhistAveria);


      $stud_arr[0]["pcode"] = 1;
      $stud_arr[0]["pmsg"] ="Descuento Denegado correctamente";
      $stud_arr[0]["pcid"] = $cid;

      require_once ('correo_averia_descuento_aviso.php');
    }

    salida_json($stud_arr);
    exit;

}




if($accion==2)
{?>

    <div class="card">
        <div class="card-header bg-dark text-white">
            Gestión de Descuentos de Averías
        </div>
        <div class="card-body">
            <form id="form_accion_averia" method="post" onsubmit="return false;">
                <input type="hidden" name="cid" value="<?php echo isset($_REQUEST['idDescuento']) ? intval($_REQUEST['idDescuento']) : 0; ?>">
                
                <div class="mb-3">
                    <label><strong>Acciones disponibles:</strong></label>
                </div>

                <div class="d-flex justify-content-start">
                    <button type="button" class="btn btn-success" style="margin-right:20px;" onclick="accionAveria('aprobar')">
                        <i class="fa fa-check"></i> Aprobar
                    </button>
                    <button type="button" class="btn btn-danger" style="margin-right:20px;" onclick="accionAveria('anular')">
                        <i class="fa fa-times"></i> Denegar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cerrar_modal()">
                        <i class="fa fa-ban"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
    function accionAveria(accion) {
        var cid = $("input[name='cid']").val();



        // aquí puedes hacer un AJAX o redirigir, según tu flujo
        if(accion=="aprobar"){
            if(confirm("¿Seguro que desea aprobar el descuento de la avería?")){
                cargando(true);
                $.post("averia_ver_descuentos.php", { a: "aprobar", cid: cid }, function(json){

            if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);
				mytoast('success',json[0].pmsg,3000) ;
				
			}
		} else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
                
                }, "json").done(function() {
                    // Esto se ejecuta siempre, haya o no haya sido exitoso el request
                    cerrar_modal();
                    limpiar_tabla('tabla');
                    procesar_tabla('tabla','forma_ver');
                });
            }
        } else if(accion=="anular"){
            if(confirm("¿Seguro que desea anular la avería?")){
                cargando(true);
                $.post("averia_ver_descuentos.php", { a: "anular", cid: cid }, function(json){

                    if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        cargando(false);
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        cargando(false);
                        mytoast('success',json[0].pmsg,3000) ;
                        
                    }
                } else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}

                }, "json").done(function() {

                    cerrar_modal();
                    limpiar_tabla('tabla');
                    procesar_tabla('tabla','forma_ver');
                });;
            }
        } 
    }


    function cerrar_modal() {
        $('#ModalWindow').modal('hide');
    }
    </script>

    <?php
    exit;
}

if ($accion=="1") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Recargado";
    $stud_arr[0]["pdata"] ="";
    $stud_arr[0]["pmas"] =0;

    $pagina=1;
    $offset=0;
    $haymas=0;
    $filtros="";

        if (isset($_REQUEST['pg'])) { $pagina = sanear_int($_REQUEST['pg']); }
    if (isset($_REQUEST['numero'])) { $tmpval=sanear_int($_REQUEST['numero']); if (!es_nulo($tmpval)){$filtros.="and ave.id = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['estado'])) { $tmpval=sanear_int($_REQUEST['estado']); if ($tmpval==2){$filtros.=" and (desc_aprob IS null or desc_aprob<>1)" ;} else if ($tmpval==1) {$filtros.=" and desc_aprob=1" ;}else{$filtros="";}   }
    if (isset($_REQUEST['tienda'])) { $tmpval=sanear_int($_REQUEST['tienda']); if (!es_nulo($tmpval)){$filtros.=" and tienda.id = ".GetSQLValue($tmpval,'int') ;}   }
    if (isset($_REQUEST['nombre'])) { $tmpval=sanear_string(trim($_REQUEST['nombre'])); if (!es_nulo($tmpval)){ $filtros.=" and (prod.nombre  like ".GetSQLValue($tmpval,'like')." or prod.codigo_alterno like ".GetSQLValue($tmpval,'like').")";} }

    /*$tmpval=sanear_int($_SESSION['usuario_id']); if (!es_nulo($tmpval)){$filtros.=" AND (averia.id_usuario=$tmpval  OR averia.id_tecnico1=$tmpval)" ;} */


        if ($pagina>=1) { $offset=$pagina*app_reg_por_pag;   }
        $datos="";

        $result = sql_select("SELECT ave.id num_averia,ave_detalle.id ave_detalle_id,prod.nombre vehiculo, cliente.nombre cliente,(ave_detalle.cantidad* ave_detalle.precio_costo) valor,  ave_detalle.fecha, tienda.nombre tienda,ave_detalle.desc_aprob
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

            $style = '';
            if ($row["desc_aprob"] == 1) {
                // Aprobado → verde claro
                $style = ' style="background-color:#d4edda;"';
            } elseif ($row["desc_aprob"] == 2) {
                // Anulado → rojo suave
                $style = ' style="background-color:#f8d7da;"';
}


                $estado = '';
                if ($row["desc_aprob"] == 1) {
                    $estado = '✅ Aprobado';
                } elseif ($row["desc_aprob"] == 2) {
                    $estado = '❌ Denegado';
                } else {
                    $estado = '⚠️ Pendiente';
                }

            $datos .= '<tr'.$style.'>
                       
                    <td><a  href="#" onclick="averia_abrir(\''.$row["num_averia"].'\',\''.$row["ave_detalle_id"].'\',\''.$row["desc_aprob"].'\'); return false;" class="btn btn-sm btn-secondary btntxt">'.$row["num_averia"].'</a></td>
                    <td>'.$row["vehiculo"].'</td>
                    <td>'.$row["cliente"].'</td>
                    <td>L '.number_format($row["valor"],2).'</td>
                    <td>'.formato_fecha_de_mysql($row["fecha"]).'</td>
                    <td>'.$row["tienda"].'</td>  
                    <<td style="text-align:left;">'.$estado.'</td>             
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
              echo campo("estado","Estado",'select',valores_combobox_texto('<option value="1">Aprobados</option> <option value="2">Denegados o pendientes</option><option value="3">Todos</option>','2'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro\');"');
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
                <th>Estado</th>                     
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


    function averia_abrir(codigo,idDescuento,estado){
        if (estado==2 || estado==1) {
            if(estado==1) {
                mymodal('info',"Información","El descuento, ya fue aprobado no se puede modificar.");
            } else {
                mymodal('info',"Información","El descuento,ya fue anulado no se puede modificar.");
            }
            return;
        }else{
        modalwindow('Descuentos','averia_ver_descuentos.php?a=2&cid=' + codigo+'&idDescuento=' + idDescuento);
        }
    }

</script>

</div>