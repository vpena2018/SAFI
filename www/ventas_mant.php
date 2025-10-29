<?php
require_once ('include/framework.php');



if (isset($_REQUEST['r'])) { $nuevo = $_REQUEST['r']; } else   {$nuevo ="N";}
if ($nuevo=='N'){
   pagina_permiso(170);
}else{
   pagina_permiso(167);
}

 

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}
$disable_sec1=' ';    
$disable_sec2=' ';    

// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT ventas.*    
    ,ventas_estado.nombre AS elestado
    ,ventas_impuestos.nombre AS elimpuesto
    ,ventas_factura.nombre AS lafactura
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
    ,tienda.nombre AS latienda
        FROM ventas
        LEFT OUTER JOIN tienda ON (ventas.id_tienda=tienda.id)        
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)        
        LEFT OUTER JOIN ventas_estado ON (ventas.id_estado=ventas_estado.id)
        LEFT OUTER JOIN ventas_impuestos ON (ventas.id_impuesto=ventas_impuestos.id)
        LEFT OUTER JOIN ventas_factura ON (ventas.id_factura=ventas_factura.id)
        
    where ventas.id=$cid limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
			$row = $result -> fetch_assoc(); 
		}
	}

} // fin leer datos

// borrar     ############################  
if ($accion=="del") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Error";
    $stud_arr[0]["pcid"] = 0;
    
if (!tiene_permiso(168)) {
        $stud_arr[0]["pmsg"] ="No tiene privilegios para Borrar";
    } else {
        $cid=0;
        if (isset($_REQUEST['id'])) { $cid = intval($_REQUEST["id"]); }
        $result = sql_select("SELECT id_estado
        FROM ventas
        where id=$cid limit 1");

        if ($result!=false){
            if ($result -> num_rows > 0) { 
                $row = $result -> fetch_assoc(); 
                if ($row['id_estado']<=20) {
                    sql_delete("DELETE FROM ventas where id=$cid limit 1");
                    $stud_arr[0]["pcode"] = 1;
                    $stud_arr[0]["pmsg"] ="Anulada";
                } else {
                    $stud_arr[0]["pmsg"] ="No puede Borrar porque, la orden ya ha sido completada";
                }
            }
        }

    }


	salida_json($stud_arr);
 	exit;

}
//Elimina Foto
if ($accion=="dfoto") {
   $cid=0;
   if (isset($_REQUEST['id'])) { $cid = intval($_REQUEST["id"]); }   
   sql_update("UPDATE ventas set foto=null where id=$cid limit 1");   
   $stud_arr[0]["pcode"] = 1;
   $stud_arr[0]["pmsg"] ="Foto eliminada";     
   salida_json($stud_arr);  
   exit;  
}

if($accion=="gfoto")
{
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Error";
    $stud_arr[0]["pcid"] = 0;
    
    
    $cid=0;

    $is_main = isset($_POST['isMain']) ? intval($_POST['isMain']) : 0;

    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }   
    if (isset($_REQUEST['arch'])) 
        { 

            $foto_original = urldecode($_REQUEST['arch']);
            $foto = str_replace(' ', '_', urldecode($_REQUEST["arch"]));




            // Rutas originales
            $ruta1 = 'uploa_d_ventas/' . $foto_original;
            $ruta2 = 'uploa_d_ventas/thumbnail/' . $foto_original;

            // Rutas nuevas
            $nueva1 = 'uploa_d_ventas/' . $foto;
            $nueva2 = 'uploa_d_ventas/thumbnail/' . $foto;

            // Renombrar
            if (file_exists($ruta1)) {
                rename($ruta1, $nueva1);
            }
            if (file_exists($ruta2)) {
                rename($ruta2, $nueva2);
            }
        
        } else{$foto ="";}   


    
     if (!es_nulo($foto) && !es_nulo($cid)){ 
         sql_insert("INSERT INTO ventas_fotos (id_venta,  nombre_archivo,  principal,fecha)
         VALUES ( $cid,  '$foto', $is_main, NOW())"); 

         $stud_arr[0]["pcode"] = 1;
         $stud_arr[0]["pmsg"] ="Foto guardada";     
         $stud_arr[0]["pcid"] = $cid;     
    }else{
         $stud_arr[0]["pmsg"] ="Error al guardar la foto";     
    } 
    
    salida_json($stud_arr);  
    exit;  
}

// borrar ARCHIVO de ventas
if ($accion =="dfotoventas") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB102";

    if (isset($_REQUEST['arch'])) { $arch =GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}
    if (isset($_REQUEST['cod'])) { $cod = "and id=".GetSQLValue(urldecode($_REQUEST["cod"]),"text"); } else	{$cod ="" ;}

    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }  else	{$cid =0 ;}

    if ($cod<>'' or $arch<>'') {

    borrar_foto_directorio($cid,$cod,$arch,"foto_ventas");

    $result = sql_delete("DELETE FROM ventas_fotos 
                            WHERE id_venta=$cid 
                            and nombre_archivo=$arch 
                            LIMIT 1
                            ");


 } else {$result==false;}
    if ($result!=false){

        //TODO borrar archivo $arch

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="Borrado";
    }

  salida_json($stud_arr);
    exit;

}

if ($accion =="ufotoportadaventas") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB102";

    if (isset($_REQUEST['arch'])) { $arch =GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}
    if (isset($_REQUEST['cod'])) { $cod = intval($_REQUEST["cod"]); } else	{$cod ="" ;}

    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }  else	{$cid =0 ;}

    if ($cod<>'' or $arch<>'') {

            $sqlReset="update ventas_fotos set principal=0 where id_venta=".$cid."";
            $sqlMarcar="update ventas_fotos set principal=1 where id=".$cod." and id_venta=".$cid."  limit 1";

            $resultReset = sql_update($sqlReset);

            $resulMarcar = sql_update($sqlMarcar);



    }else {$result==false;}
    if ($resultReset!=false and $resulMarcar!=false){

        //TODO borrar archivo $arch

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="Actualizado";
    }

    salida_json($stud_arr);
    exit;

}



// guardar Datos    ############################  
if ($accion=="g") {
 //sleep(3);
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

    //Validar
	$verror="";
    $cid=intval($_REQUEST["id"]);
    $carShopPerfil="";
    $verror.=validar("Sucursal",$_REQUEST['id_tienda'], "int", true);
    $verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
    $verror.=validar("Kilomatraje",$_REQUEST['kilometraje'], "int", true);
    $verror.=validar("Cilindraje",$_REQUEST['cilindraje'], "int", true);
    $verror.=validar("Precio Minimo",$_REQUEST['precio_minimo'], "int", true);
    $verror.=validar("Precio Maximo",$_REQUEST['precio_maximo'], "int", true);         
    $precio_minimo=intval($_REQUEST['precio_minimo']);     
    $precio_maximo=intval($_REQUEST['precio_maximo']);    
    
    if ($precio_maximo<$precio_minimo){$verror.='Ingrese el precio maximo tiene que ser mayor';}
    if ($precio_minimo>$precio_maximo){$verror.='Ingrese el precio minimo tiene que ser menor';}
    
    if (es_nulo($cid)){
        $id_producto=intval($_REQUEST['id_producto']);
        $vehiculo=get_dato_sql("ventas","count(*)"," where id_producto=".$id_producto);
        if (!es_nulo($vehiculo) && es_nulo($cid)){ $verror.='Vehiculo ya esta registrado'; }   
    }  
    
    $carShopPerfil=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
    $id_estado=intval($_REQUEST['id_estado']);
    $precio_venta=intval($_REQUEST['precio_venta']);
   
    if (!es_nulo($cid) && $id_estado==20){
       if (es_nulo($precio_venta)) { $verror.='Ingrese el precio de venta del vehiculo'; }    
    }    
    if (!es_nulo($cid) && $id_estado<20){
       if (!es_nulo($precio_venta)) { $verror.='Precio de venta solo se ingresa, estado de vendido entregado'; }    
    }  
    if ($carShopPerfil=='18'){
        $verror.=validar("Estado",$_REQUEST['id_estado'], "int", true);
        $id_vendedor=intval(get_dato_sql("ventas","id_vendedor"," where id=".$cid)); 
        if (!es_nulo($id_vendedor) && $id_vendedor!=intval($_REQUEST['id_vendedor']) && $id_estado==11){ 
            $verror.='No es posible realizar el cambio de vendedor';
        }        
        $id_estado_ant=intval(get_dato_sql("ventas","id_estado"," where id=".$cid));          
        if ($id_estado==5){ 
            $verror.='No es posible realizar el cambio de estado';
        }
    }

    $envioCorreo="";
    $fotoRegistro=get_dato_sql("ventas_estado","foto"," where foto=1 and id=".$id_estado);
    $envioCorreo=get_dato_sql("ventas_estado","envio_correo"," where envio_correo=1 and id=".$id_estado);
    if (!es_nulo($fotoRegistro)){
        if (isset($_REQUEST['foto'])) {
           $verror.=validar("Foto de comprobante de pago",$_REQUEST['foto'], "text", true);
        }                        
    }


    if ($verror=="") {
        //Campos
        $sqlcampos="";


        $nuevoregistro=false;
        $cid= intval($_REQUEST["id"]);
        if (es_nulo($cid)) {
            $nuevoregistro=true;
        }     

        if (isset($_REQUEST["id_producto"])) { $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
        if (isset($_REQUEST["id_tienda"])) { $sqlcampos.= " , id_tienda =".GetSQLValue($_REQUEST["id_tienda"],"int"); }    
        if (isset($_REQUEST["id_estado"])) { $sqlcampos.= " , id_estado =".GetSQLValue($_REQUEST["id_estado"],"int"); }    
        
        if (isset($_REQUEST["precio_minimo"])) { $sqlcampos.= " , precio_minimo =".GetSQLValue($_REQUEST["precio_minimo"],"int"); } 
        if (isset($_REQUEST["precio_maximo"])) { $sqlcampos.= " , precio_maximo =".GetSQLValue($_REQUEST["precio_maximo"],"int"); } 
        if (isset($_REQUEST["precio_venta"])) { $sqlcampos.= " , precio_venta =".GetSQLValue($_REQUEST["precio_venta"],"int"); } 
        if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
        if (isset($_REQUEST["cilindraje"])) { $sqlcampos.= " , cilindraje =".GetSQLValue($_REQUEST["cilindraje"],"int"); } 
        if (isset($_REQUEST["trasmision"])) { $sqlcampos.= " , trasmision =".GetSQLValue($_REQUEST["trasmision"],"text"); } 
        if (isset($_REQUEST["id_impuesto"])) { $sqlcampos.= " , id_impuesto =".GetSQLValue($_REQUEST["id_impuesto"],"int"); } 
        if (isset($_REQUEST["id_factura"])) { $sqlcampos.= " , id_factura =".GetSQLValue($_REQUEST["id_factura"],"int"); } 
        if (isset($_REQUEST["id_vendedor"])) { $sqlcampos.= " , id_vendedor =".GetSQLValue($_REQUEST["id_vendedor"],"int"); } 
        if (isset($_REQUEST["id_televentas"])) { $sqlcampos.= " , id_televentas =".GetSQLValue($_REQUEST["id_televentas"],"int"); } 
        if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); }         
        if (isset($_REQUEST["foto"])) { $sqlcampos.= " , foto =".GetSQLValue($_REQUEST["foto"],"text"); } 
        if (isset($_REQUEST["reproceso"])) { $sqlcampos.= " , reproceso =".GetSQLValue($_REQUEST["reproceso"],"text"); } 
        if (isset($_REQUEST["oferta"])) { $sqlcampos.= " , oferta =".GetSQLValue($_REQUEST["oferta"],"int"); } 

        if ($nuevoregistro==false) {
            $reproceso="";  
            $nombreReproceso="";
            if (isset($_REQUEST["reproceso"])){
                $reproceso=$_REQUEST["reproceso"];
                if (!es_nulo($reproceso)){
                    switch ($reproceso){
                       case "1":
                           $nombreReproceso='Pintura';
                           $sqlcampos.= " , id_estado_pintura = 30";
                            break;
                       case "2":
                           $nombreReproceso='Interior';
                           $sqlcampos.= " , id_estado_interior = 30";   
                            break;
                       case "3":
                           $nombreReproceso='Mecanica';
                           $sqlcampos.= " , id_estado_mecanica = 30";
                            break;
                    }
                    $sqlcampos.= " , tipo_ventas_reparacion = 1";
                    $sqlcampos.= " , id_estado = 99";
                    $sqlcampos.= " , reproceso = 'R'";
                    sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                    VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Vehiculo a Reproceso', NOW(), '$nombreReproceso')");
                }
            }
            if (intval($_REQUEST['id_estado'])==20){                
               // $sqlcampos.=" , fecha_vendido=now()";  
             }            
            //si modifica se guarda el registo del cambio
            $id_tienda=intval(get_dato_sql("ventas","id_tienda"," where id=".$cid));
            if ($id_tienda!=intval($_REQUEST['id_tienda'])){   
               $id_tienda_name=get_dato_sql("tienda","nombre"," where id=".$_REQUEST['id_tienda']);
               sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
               VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Tienda', NOW(), '$id_tienda_name')");
            }
            $kilometraje=intval(get_dato_sql("ventas","kilometraje"," where id=".$cid));
            if ($kilometraje!=intval($_REQUEST['kilometraje'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Kilometraje', NOW(), ".$_REQUEST['kilometraje'].")");
            }

             $precio_minimo=intval(get_dato_sql("ventas","precio_minimo"," where id=".$cid));
             if ($precio_minimo!=intval($_REQUEST['precio_minimo'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Precio Minimo', NOW(), ".$_REQUEST['precio_minimo'].")");
             }

             $precio_maximo=intval(get_dato_sql("ventas","precio_maximo"," where id=".$cid));
             if ($precio_maximo!=intval($_REQUEST['precio_maximo'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Precio Maximo', NOW(), ".$_REQUEST['precio_maximo'].")");
             }

             $precio_venta=intval(get_dato_sql("ventas","precio_venta"," where id=".$cid));
             if ($precio_venta!=intval($_REQUEST['precio_venta'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Precio de Venta', NOW(), ".$_REQUEST['precio_venta'].")");
             }

             $id_estado=intval(get_dato_sql("ventas","id_estado"," where id=".$cid));             
             $id_estado_vendido=intval(get_dato_sql("ventas_estado","envio_correo"," where id=".$id_estado));             
             $id_estado_modifico=false;
             if ($id_estado!=intval($_REQUEST['id_estado'])){                  
                 //si cambia el estado a en negociacion  
                 if ($envioCorreo==1){                      
                    $id_estado_modifico=true;
                    $sqlcampos.=" , fecha_vendido=now()";                                                 
                 }
                 if (intval($_REQUEST['id_estado'])==11){                                    
                    $sqlcampos.=" , fecha_negociacion=now()";  
                 }                               
                 if ($id_estado==11){                
                    $sqlcampos.=" , fecha_negociacion=null";  
                 }                                 
                 if ($id_estado_vendido==1){                
                     $sqlcampos.=" , fecha_vendido=null, precio_venta=null";  
                 }                                                                                                                     
                 $fotoRegistro1=get_dato_sql("ventas_estado","foto"," where foto=1 and id=".$id_estado);
                 $id_estado_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",$id_estado,'Modificacion de Estado', NOW(),'$id_estado_name')");               
             }

             $id_impuesto=intval(get_dato_sql("ventas","id_impuesto"," where id=".$cid));
             if ($id_impuesto!=intval($_REQUEST['id_impuesto'])){   
                 $id_impuesto_name=get_dato_sql("ventas_impuestos","nombre"," where id=".$_REQUEST['id_impuesto']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Impuestos', NOW(),'$id_impuesto_name')");
             }

             $id_factura=intval(get_dato_sql("ventas","id_factura"," where id=".$cid));
             if ($id_factura!=intval($_REQUEST['id_factura'])){   
                 $id_factura_name=get_dato_sql("ventas_factura","nombre"," where id=".$_REQUEST['id_factura']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Factura', NOW(),'$id_factura_name')");
             }            

             $id_vendedor=intval(get_dato_sql("ventas","id_vendedor"," where id=".$cid));
             if ($id_vendedor!=intval($_REQUEST['id_vendedor'])){   
                $id_vendedor_name=get_dato_sql("usuario","nombre"," where id=".$_REQUEST['id_vendedor']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Vendedor', NOW(), '$id_vendedor_name')");
             }

             $id_televentas=intval(get_dato_sql("ventas","id_televentas"," where id=".$cid));
             if ($id_televentas!=intval($_REQUEST['id_televentas'])){   
                 $id_televentas_name=get_dato_sql("usuario","nombre"," where id=".$_REQUEST['id_televentas']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Televentas', NOW(),'$id_televentas_name')");
             }

             $observaciones=trim(get_dato_sql("ventas","observaciones"," where id=".$cid));
             if ($observaciones!=trim($_REQUEST['observaciones'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Observaciones', NOW(),'".$_REQUEST['observaciones']."')"); 
             }
            
             

            $sql="update ventas set ".$sqlcampos." where id=".$cid." limit 1";
            $result = sql_update($sql);

        } else {
            //Crear nuevo                       
            $sqlcampos.=" ,id_usuario=".$_SESSION['usuario_id'] ;      
            $sqlcampos.=" ,tipo_ventas_reparacion=2";      
            $sqlcampos.=" ,numero=".GetSQLValue(get_dato_sql('ventas',"IFNULL((max(numero)+1),1)"," "),"int"); 
            $sql="insert into ventas set fecha=NOW(), hora=now(),".$sqlcampos." ";        
            
            $result = sql_insert($sql);
            $cid=$result; //last insert id 

            sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
            VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Nuevo registro de vehiculo', NOW(),'Nuevo')");
        }

        
        if ($result!=false){
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ="Guardado";
            $stud_arr[0]["pcid"] = $cid;       
            
            //correo
            if ($envioCorreo==1 and $id_estado_modifico==true){
                //require_once ('correo_ventas.php');
            }
        }

    } else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] =$verror;
        $stud_arr[0]["pcid"] = 0;
    }

    salida_json($stud_arr);
    exit;

} // fin guardar datos




?>

<div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      <li class="nav-item">
        <a class="nav-link " id="insp_tabhistorial" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_historial');"   role="tab"  >Historial</a>
      </li> 
      <?php if(tiene_permiso(186)) { ?>
        <li class="nav-item">
            <a class="nav-link " id="insp_tabFotos" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_Fotos_venta');"   role="tab"  >Fotos</a>
        </li> 
       <?php } ?>
    </ul>   
 </div>

<div class="maxancho800 mx-auto">

<div class="tab-content" id="nav-tabContent">
<!-- DETALLE  -->
<div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >


<div class="row">
<div class="col">
   	<div class="form-group">	   
	<form id="forma_ventas" name="forma_ventas">
	<fieldset id="fs_forma">		 
<?php 

    if (isset($row["elimpuesto"])) {$elimpuesto=$row["elimpuesto"];} else {$elimpuesto="";}
    if (isset($row["elestado"])) {$elestado=$row["elestado"];}else {$elestado="";}
    if (isset($row["codvehiculo"])) {$producto_etiqueta=$row["codvehiculo"]. ' '.$row["vehiculo"];   }else {$producto_etiqueta="";}
    if (isset($row["lafactura"])) {$lafactura=$row["lafactura"];} else {$lafactura="";}
    if (isset($row["latienda"])) {$latienda=$row["latienda"];} else {$latienda="";}

    if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
    if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= "";}
    if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
    if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
    if (isset($row["id_impuesto"])) {$id_impuesto= $row["id_impuesto"]; } else {$id_impuesto= "";}
    if (isset($row["id_factura"])) {$id_factura= $row["id_factura"]; } else {$id_factura= "";}
    if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "";}
    if (isset($row["fecha"])) {$fecha=$row["fecha"]; } else {$fecha= "";}
    if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
    if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
    if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
    if (isset($row["precio_minimo"])) {$precio_minimo= $row["precio_minimo"]; } else {$precio_minimo= "";}
    if (isset($row["precio_maximo"])) {$precio_maximo= $row["precio_maximo"]; } else {$precio_maximo= "";}
    if (isset($row["precio_venta"])) {$precio_venta= $row["precio_venta"]; } else {$precio_venta= "";}
    if (isset($row["cilindraje"])) {$cilindraje= $row["cilindraje"]; } else {$cilindraje= "";}
    if (isset($row["trasmision"])) {$trasmision= $row["trasmision"]; } else {$trasmision= "";}
    if (isset($row["id_vendedor"])) {$id_vendedor= $row["id_vendedor"]; } else {$id_vendedor= "";}
    if (isset($row["id_televentas"])) {$id_televentas= $row["id_televentas"]; } else {$id_televentas= "";}
    if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
    if (isset($row["foto_televentas"])) {$foto_televentas= $row["foto_televentas"]; } else {$foto_televentas= "";}
    if (isset($row["foto"])) {$foto= $row["foto"]; } else {$foto= "";}
    if (isset($row["fecha_negociacion"])) {$fecha_negociacion=$row["fecha_negociacion"]; } else {$fecha_negociacion= "";}
    if (isset($row["id_inspeccion"])) {$id_inspeccion=$row["id_inspeccion"]; } else {$id_inspeccion= "";}
    if (isset($row["reproceso"])) {$reproceso=$row["reproceso"]; } else {$reproceso= "";}
    $oferta = (isset($row["oferta"]) && $row["oferta"] == 1) ? true : false;
   
    $carShopPerfil="";
    $carShopPerfil=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
    $diff=0;
    if ($id_estado>=1 and $id_estado<20 ){         
        if (!es_nulo($fecha_negociacion)){
            $neg = date_create($fecha_negociacion);
            $hoy = date_create("now"); 
            $diff = date_diff($hoy,$neg);          
        }                 
        if (!tiene_permiso(169)){
            $disable_sec1=' readonly="readonly" ';              
        }     
    }   
    else{
        if ($id_estado==20){
           if (tiene_permiso(167)) {
              $disable_sec1=' ';  
              $disable_sec2=' ';  
           }else{
              $disable_sec1=' disabled="disabled" ';  
              $disable_sec2=' disabled="disabled" ';  
           }
        }
    }

    echo campo("id",("Codigo"),'hidden',$id,' ','');

?>


<div class="row">
    <div class="col-md">
        <?php echo campo("hora",("Fecha / Hora"),'label',formato_fechahora_de_mysql($hora),' ',' ');   ?>
    </div>
    <div class="col-md">
        <?php echo campo("numero","Numero",'label',$numero,' ',' '); ?>        
    </div>    
    <?php if(!es_nulo($diff)) { ?>
        <div class="col-md">
            <?php echo campo("dias","Dias en Negociacion",'label',$diff->days,' ',' '); ?>      
        </div>                
        <div class="col-md">    
            <?php echo campo("fecha_neg","Fecha en Negociacion",'label',formato_fechahora_de_mysql($fecha_negociacion),' ',' '); ?>    
        </div>        
    <?php } ?>        
    <?php echo campo("id_inspeccion","Numero",'hidden',$id_inspeccion,' ',' '); ?>          
</div>

<div class="row">
    <div class="col-md-4">                
         <?php if (tiene_permiso(167) && $id_estado<20 || es_nulo($id_estado)) {            
             echo campo("id_tienda","Sucursall",'select2',valores_combobox_db("tienda",$id_tienda,"nombre"," ",'','...'),' ',' required '.$disable_sec1,''); 
         }else{
             echo campo("id_tienda","sucursal",'hidden',$id_tienda,'','','');
             echo campo("id_tienda_label","Sucursal",'label',$latienda,'','','');
         }  
         ?> 
    </div>    
    <div class="col-md-8">         
         <?php if (tiene_permiso(167) && ($id_estado<20 || es_nulo($id_estado))) {            
               echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,'  class=" "',' onchange="comb_actualizar_veh();"  ','get.php?a=3&t=1',$producto_etiqueta); 
         }else{
               echo campo("id_producto","Vehiculo",'hidden',$id_producto,'','','');
               echo campo("id_producto_label","Vehiculo",'label',$producto_etiqueta,'','','');
          }      
         ?>            
    </div> 
</div>    


<div class="row">
    <div class="col-md">
        <?php echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ',$disable_sec1); ?>        
    </div>
    <div class="col-md">
        <?php echo campo("cilindraje","Cilindraje",'number',$cilindraje,' ',$disable_sec1); ?>        
    </div>    
    <div class="col-md">
        <?php if (tiene_permiso(167) && ($id_estado<20 || es_nulo($id_estado))) { 
              echo campo("trasmision","Trasmision",'select', valores_combobox_texto(app_tipo_trasmision,$trasmision),' ',$disable_sec1); 
        }else{
              echo campo("trasmision","Trasmision",'label',$trasmision,'','','');
        }   
        ?>   
    </div>    
    <div class="col-md">
        <?php echo campo("precio_minimo","Precio Minimo",'number',$precio_minimo,' ',$disable_sec1); ?>        
    </div>
    <div class="col-md">
        <?php echo campo("precio_maximo","Precio Maximo",'number',$precio_maximo,' ',$disable_sec1); ?>          
    </div>    
</div>  

<div class="row">
    <div class="col-md">
         <?php echo campo("id_estado","Estado",'select2',valores_combobox_db("ventas_estado",$id_estado,"nombre"," where ventas_reparacion=2 ",'','...'),' ',' required '.$disable_sec2)  ?> 
         <?php /*echo campo("id_estado_name","Estado",'label',$elestado,'','','');*/ ?>
    </div>
    <div class="col-md">
         <?php if (tiene_permiso(167)){ 
              echo campo("id_impuesto","Impuesto",'select2',valores_combobox_db("ventas_impuestos",$id_impuesto,"nombre","  ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_impuesto","impuesto",'hidden',$id_impuesto,'','','');
              echo campo("id_impuesto_label","Impuesto",'label',$elimpuesto,'','','');
         }
         ?>         
    </div>
    <div class="col-md">
         <?php if (tiene_permiso(167)){ 
               echo campo("id_factura","Factura",'select2',valores_combobox_db("ventas_factura",$id_factura,"nombre"," ",'','...'),' ',' required '.$disable_sec1);  
         }else{
               echo campo("id_factura","Factura",'hidden',$id_factura,'','','');
               echo campo("id_factura_label","Factura",'label',$lafactura,'','','');
         }  
         ?>    
    </div>    
</div>

<div class="row">
    <div class="col-md">
         <?php echo campo("id_vendedor","Vendedor",'select2',valores_combobox_db('usuario',$id_vendedor,'nombre',' where activo=1 and grupo_id=18 ','','...'),' ',' required '.$disable_sec2);  ?> 
    </div>
    <div class="col-md">
         <?php echo campo("id_televentas","Tele Ventas",'select2',valores_combobox_db('usuario',$id_televentas,'nombre',' where activo=1 and grupo_id=18 ','','...'),' ',' required '.$disable_sec2);  ?> 
    </div>
    <div class="col-md">            
         <?php echo campo("precio_venta","Precio de Venta",'number',$precio_venta,' ',$disable_sec2); ?>                 
    </div>   
</div>

<div class="row">
     <div class="col-md">
         <?php echo campo("oferta","Oferta Web",'checkboxCustom',$oferta,' ',$disable_sec2); ?>          
     </div>
</div>

<div class="row">
     <div class="col-md">
         <?php echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',$disable_sec2); ?>         
     </div>
</div>

<div class="row">
     <div class="col-md">
          <?php if (tiene_permiso(177)){ 
                    echo campo("reproceso","Seleccione el Reproceso",'select', valores_combobox_texto(app_reproceso_ventas,$reproceso,'...'),' ',$disable_sec1); 
                 }
          ?>   
    </div>
</div>

<div class="row">
<div class="col-md" id="archivofoto">
<?php  
    
    if ($foto=='') {  echo campo_upload("foto","Adjuntar comprobante de pago",'upload','', '  ','',4,8,'NO',false ); }                   
 ?>

</div>
<div class="col-md">
<div class="" id="insp_fotos_thumbs">
  <?php
  if ($foto<>'') {
     $fext = substr($foto, -3);
            if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {               
                if ($fecha<'2025-10-01'){                   
                    echo '  <a href="#" onclick="mostrar_foto(\''.$foto.'\',\'aws_bucket_s3\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="aws_bucket_s3/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';
                }else{
                    echo '  <a href="#" onclick="mostrar_foto(\''.$foto.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';                   
                }
            } else {
                if ($fecha<'2025-10-01'){                   
                    echo '  <a href="aws_bucket_s3/'.$foto.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto.'</a> ';
                }else{    
                    echo '  <a href="uploa_d/'.$foto.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto.'</a> ';
                }    
            }
  }
  ?>
</div>
</div>
</div>					

 
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
		<div class="col-sm">            
            <a href="#" onclick="procesar('ventas_mant.php?a=g','forma_ventas',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> Guardar</a>                           
        </div>        
        <?php if (tiene_permiso(168)){ ?>
              <div class="col-sm"><a id="ventas_anularbtn"  href="#" onclick="ventas_anular(); return false;" class="btn btn-danger  btn-block mr-2 mb-2 xfrm"><i class="fa fa-trash-alt"></i> Borrar</a></div>		              
              <div class="col-sm"><a id="ventas_anularbtn"  href="#" onclick="ventas_dfoto(); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-trash-alt"></i> Borrar Foto</a></div>	                           
        <?php } ?>  

        <?php if (!es_nulo($id_inspeccion)){ ?>            
            <a href="#" onclick="abrir_hoja(); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-file-medical-alt"></i> Abrir Inspección</a>
        <?php } ?>  

        <div class="col-sm"><a href="#" onclick="$('#ModalWindow2').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
		</div>
	</div>

	</fieldset>
	</form>

<?php  ?>    
 
</div>

</div>

</div>


</div>

<!-- HISTORIAL -->
<div class="tab-pane fade " id="nav_historial" role="tabpanel" ></div>


<!-- fotos ventas -->
 
<div class="tab-pane fade " id="nav_Fotos_venta" role="tabpanel" >
    <div class="" id="insp_fotos_thumbs_ventas">
    </div>
    <div class="row">
    <div class="col-md-10" id="archivofotoventas">
    <?php

        $total_filas=0;
        $principal=false;
        $principalEncontrada=false;
 
        $sql="select id,nombre_archivo,fecha,principal from ventas_fotos where id_venta=".GetSQLValue($id,"int")." order by principal desc";
        $result = sql_select($sql);

        if ($result != false) {
    $total_filas = $result->num_rows;
    if ($total_filas > 0) {

        echo '<div style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            justify-items: center;
            align-items: start;
            justify-content: center;
        ">';

        while ($row = $result->fetch_assoc()) {
            $es_principal = (bool)$row["principal"];
            $fext = strtolower(substr($row["nombre_archivo"], -3));

            if (in_array($fext, ['jpg', 'peg', 'png', 'gif'])) {

                echo '<div style="text-align:center;">';

                // Imagen
                echo '<a href="#" class="foto_br' . $row["id"] . '" 
                        onclick="mostrar_foto(\'' . $row["nombre_archivo"] . '\',\'uploa_d_ventas/\'); return false;"
                        style="display:inline-block; transition: transform 0.2s ease-in-out;">
                        <img class="img img-thumbnail mb-2" 
                             src="uploa_d_ventas/thumbnail/' . $row["nombre_archivo"] . '" 
                             data-cod="' . $row["id"] . '" 
                             style="width:100%; max-width:160px; height:auto; border-radius:6px;">
                      </a>';

                // Controles
                echo '<div style="text-align:center; font-size:13px;">';
                echo '<a href="#" class="mr-2 foto_br' . $row["id"] . '" 
                        onclick="borrar_fotodb(' . $row["id"] . ',\'' . $row["nombre_archivo"] . '\'); return false;"
                        style="color:#dc3545; text-decoration:none;">
                        <i class="fa fa-eraser"></i> Borrar
                      </a>';

                if ($es_principal) {
                    echo '<i class="fa fa-star" title="Foto de portada" style="color:#f0c651;"> Portada</i>';
                } else {
                    echo '<a href="#" onclick="marcar_portada(' . $row["id"] . ',\'' . $row["nombre_archivo"] . '\'); return false;"
                            style="color:#6c757d; text-decoration:none;">
                            <i class="far fa-star"></i> Portada
                          </a>';
                }
                echo '</div>';

                echo '</div>';
            }
        }

        echo '</div>';
    }
}




        $a=$total_filas;
        while ($a < 10) {
            
            echo '<div class="row"><div class="col-12">';
            echo '<div class="ins_varias_foto_div">';
            echo campo_upload_foto_ventas("ins_foto".$a,"Adjuntar Fotos",'upload','', '  ','',3,9,'NO',false,$principal );
            echo "</div></div></div>";
            echo "<hr>"; 
            $a++;
            

        }
    ?>
    </div>
</div>
</div>

<!-- errores -->
<div class="tab-pane fade mt-5 mb-5" id="nav_deshabilitado" role="tabpanel" ><div class="alert alert-warning" role="alert">Debe Guardar el documento para poder continuar con esta sección</div></div>



<script>

function servicio_editarcampo(nombre,etiqueta,valor,adicional=''){
  var estado = $('#id_estado').val();  
  $('#ModalWindow2').modal('hide');
  modalwindow('Editar','ventas_mant.php?a=ec&nom='+encodeURI(nombre)+'&sid='+$('#id').val()+'&eti='+encodeURI(etiqueta)+'&val='+encodeURI(valor)+adicional);
  $('#ModalWindow2').modal('show');
}



function abrir_hoja(){    
    hinspeccion = $('#id_inspeccion').val();
    $('#ModalWindow2').modal('hide');
    get_page('pagina','inspeccion_mant.php?a=v&cid='+hinspeccion,'Hoja de Inspección',false);
}

function insp_guardar_foto(arch,campo){

           $('#'+campo).val(arch);                
           $('#files_'+campo).text('Guardado');
           $('#lk'+campo).html(arch);
           thumb_agregar(arch);    
}


function mostrar_foto(imagen,folder="uploa_d/") {

  Swal.fire({
  imageUrl: folder+imagen,

}); 

}


function thumb_agregar(archivo){
if (archivo!='' && archivo!=undefined) {
  
    var fext= archivo.substr(archivo.length - 3);

    if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
       $("#insp_fotos_thumbs").append('<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a>');
    } else {
       $("#insp_fotos_thumbs").append('<a href="uploa_d/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>');
    }
  }
}

function thumb_agregar_foto_venta(archivo,campo){
    var salida='';
    if (archivo!='' && archivo!=undefined) {
        
   
    var fext= archivo.substr(archivo.length - 3);

   if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
    salida='<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d_ventas/thumbnail/'+archivo+'" ></a> ';
   } else {
    salida='<a href="uploa_d_ventas/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>';
   }

   $("#"+campo).closest('.ins_varias_foto_div').html(salida +'<a id="del_'+campo+'" href="#" onclick="insp_borrar_foto_venta(\''+archivo+'\',\'del_'+campo+'\'); return false;" class="btn  btn-outline-secondary ml-3 "><i class="fa fa-eraser"></i> Borrar</a>');
  }
}



function insp_borrar_foto_venta(arch,campo){

    var cid=$("#id").val();
    var datos= { a: "dfotoventas", cid: cid, pid: $("#pid").val() , arch: encodeURI(arch)} ;


Swal.fire({
	  title: 'Borrar Foto',
	  text:  'Desea Borrar la Foto o Documento adjunto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'ventas_mant.php',datos, function(json) {
                
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        console.log('->'+campo);
                        $("#"+campo).closest('.ins_foto_div').html('Eliminado');
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	abrir_ventas(cid); 
                    setTimeout(function() {
                        ventas_cambiartab('nav_Fotos_venta');
                        $('#insp_tabFotos').tab('show');

                    }, 300);
                    mytoast('success','Borrado',3000) ; 
            })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});
}

function borrar_fotodb(codid,arch){

    var cid=$("#id").val();
    var datos= { a: "dfotoventas", cid: cid, pid: $("#pid").val() , cod: codid, arch: encodeURI(arch)} ;
  

Swal.fire({
	  title: 'Borrar Foto',
	  text:  'Desea Borrar la Foto o Documento adjunto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'ventas_mant.php',datos, function(json) {
                
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        
                        $(".foto_br"+codid).hide();
                        mytoast('success',json[0].pmsg,3000) ;
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	  
                abrir_ventas(cid); 
                    setTimeout(function() {
                        ventas_cambiartab('nav_Fotos_venta');
                        $('#insp_tabFotos').tab('show');
                    }, 300);

                    mytoast('success','Borrado',3000) ; 
            })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});




}

function marcar_portada(codid,arch){
  

    var cid=$("#id").val();
    var datos= { a: "ufotoportadaventas", cid: cid, pid: $("#pid").val() , cod: codid, arch: encodeURI(arch)} ;
  

Swal.fire({
	  title: '⭐Foto de portada',
	  text:  'Desea Marcar la foto como portada?',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'ventas_mant.php',datos, function(json) {
               
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        
                        $(".foto_br"+codid).hide();
                        mytoast('success',json[0].pmsg,3000) ;
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	  
                    abrir_ventas(cid); 
                    setTimeout(function() {
                        ventas_cambiartab('nav_Fotos_venta');
                        $('#insp_tabFotos').tab('show');
                    }, 300);

                    mytoast('success','Actualizado',3000) ; 
            })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});




}

    function insp_guardar_foto_ventas(arch,campo,isMain){
     
    var cid=$("#id").val();
    var datos= { a: "gfoto", arch: encodeURI(arch),cid:cid,isMain:isMain}; 

    debugger;
    

 	 $.post( 'ventas_mant.php',datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
                $('#'+campo).val(arch);                
                $('#files_'+campo).text('Guardado');
                $('#lk'+campo).html(arch);
               // thumb_agregar(arch);
               thumb_agregar_foto_venta(arch,campo);
			
			}
		} else {mytoast('error',json[0].pmsg,3000) ; }
		  
	})
	  .done(function() { 

        abrir_ventas(cid); 
        setTimeout(function() {
            ventas_cambiartab('nav_Fotos_venta');

            $('#insp_tabFotos').tab('show');
        }, 300);

        mytoast('success','Guardado',3000) ;   
    
    })
	  .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
	  .always(function() {	  }); 
    
    }


function comb_actualizar_veh(){
   
    var datos=$('#id_producto').select2('data')[0];
 
$('#forma_ventas input[id=placa] ').val(datos.placa);


}

function ventas_anular(){
    Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar este vehiculo?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	     ventas_procesar('ventas_mant.php?a=del','forma_ventas','del');        
	  }
	})

}

function ventas_dfoto(){
    Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar este foto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	     ventas_procesar('ventas_mant.php?a=dfoto','forma_ventas','dfoto');        
	  }
	})

}

function ventas_procesar(url,forma,adicional){
   
	$("#"+forma+" .xfrm").addClass("disabled");		
	
	cargando(true); 
	
		
	var datos=$("#"+forma).serialize();

	 $.post( url,datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);
				mytoast('success',json[0].pmsg,3000) ;

					$("#"+forma+' #id').val(json[0].pcid);

                    if (adicional=="del") {
                        $('#ModalWindow2').modal('hide');
                        $( "#btn-filtro" ).click();
                    }
                    if (adicional=="dfoto") {     
                       $('#insp_fotos_thumbs').remove();                                                                 
                    }
                    
			
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
}

function ventas_cambiartab(eltab) {
  var codigo= $('#id').val();
  var continuar=true;
  $('.tab-pane').hide();


  if (eltab!='nav_detalle') {
    if (codigo=="0" || codigo=="") {
      continuar=false;
      $('#nav_deshabilitado').show();
      $('#nav_deshabilitado').tab('show');
    } 
  }


  if (eltab=='nav_historial') {
     procesar_ventas_historial('nav_historial');
  }
  
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

//   nav_detalle
// nav_fotos
// nav_doctos 

}

function procesar_ventas_historial(campo){

var cid=$("#id").val();
var pid=$('#id_producto').val();
var url='ventas_historial.php?cid='+cid+'&pid='+pid ;

$(window).scrollTop(0);
$("#"+campo).html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span class="">'+'Cargando'+'</span></div>');			

$("#"+campo).load(url, function(response, status, xhr) {	
   
  if (status == "error") { 

    //$("#"+campo).html("Error"; // xhr.status + " " + xhr.statusText
    $("#"+campo).html('<p>&nbsp;</p>');
    mytoast('error','Error al cargar la pagina...',6000) ;
  }

});
  
}


</script>


