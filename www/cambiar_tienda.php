<?php
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else	{exit ;}
if (isset($_REQUEST['tid'])) { $tid = intval($_REQUEST['tid']); } else	{exit ;}
require_once ('include/framework.php');


pagina_permiso(28);

if ($accion=="v") {
    ?>
    
    <form id="forma_tienda" name="forma_tienda" class="needs-validation" novalidate>
    <fieldset id="fs_forma"> 
    <small>Donde serán registradas las transacciones</small>
    <input id="a" name="a"  type="hidden" value="g" >
    <br><br>
    <?php echo campo("tid","Tienda",'select2',valores_combobox_db('tienda',$tid,'nombre','','','...'),' ',' required ',''); ?>
    

    <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <a href="#" onclick="procesar_cambiar_tienda('cambiar_tienda.php','forma_tienda',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Seleccionar</a>
           

          </div>
          </div>
        </div>

    </fieldset>
     </form>
    <?php            
}


if ($accion=="g") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";
    $stud_arr[0]["ptid"] =$_SESSION['tienda_id'];
    $stud_arr[0]["ptienda"] =$_SESSION['tienda_nombre'];

    $nombretienda=get_dato_sql('tienda','nombre',' where id='.$tid);
    if (!es_nulo($nombretienda)) {
        $_SESSION['tienda_id']=$tid;
        $_SESSION['tienda_nombre']=$nombretienda;
        $_SESSION['correo_bodega'] = trim(get_dato_sql('tienda','correo_bodega',' where id='.$tid));
        $_SESSION['correo_compras'] = trim(get_dato_sql('tienda','correo_compras',' where id='.$tid));

        $_SESSION['correo_orden_servicio_nueva'] = trim(get_dato_sql('tienda','correo_orden_servicio_nueva',' where id='.$tid));
		$_SESSION['correo_orden_averia_nueva'] =  trim(get_dato_sql('tienda','correo_orden_averia_nueva',' where id='.$tid));
		$_SESSION['correo_cita'] = trim(get_dato_sql('tienda','correo_cita',' where id='.$tid));
        $_SESSION['sap_almacen'] =  trim(get_dato_sql('tienda','sap_almacen',' where id='.$tid));	 
        

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="La tienda: $nombretienda fue asignada";
        $stud_arr[0]["ptid"] =$tid;
        $stud_arr[0]["ptienda"] =$nombretienda;
    }
    
    salida_json($stud_arr);
    exit;

}


?>
