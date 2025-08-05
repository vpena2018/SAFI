<?php
require_once ('include/framework.php');

if (app_enviar_email==true) {
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $sql_detalle
    // $cid 
    // $accion 

    
    if (!isset($tipo_movimiento_texto)) {
        $tipo_movimiento_texto='Salida';
    }


    $correo_servicio_result = sql_select("SELECT servicio.* 
        ,entidad.nombre AS cliente_nombre
        ,producto.nombre AS producto_nombre
        ,producto.codigo_alterno AS producto_alterno

        ,servicio_estado.nombre AS elestado
        ,servicio_tipo_mant.nombre AS eltipo
        ,servicio_tipo_revision.nombre AS eltiporevision
        ,tec1.nombre AS eltecnico1
        ,tec2.nombre AS eltecnico2
        ,tec3.nombre AS eltecnico3

        FROM servicio
        LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
        LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
        LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
        LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
        LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
        LEFT OUTER JOIN usuario tec1 ON (servicio.id_tecnico1=tec1.id)
        LEFT OUTER JOIN usuario tec2 ON (servicio.id_tecnico2=tec2.id)
        LEFT OUTER JOIN usuario tec3 ON (servicio.id_tecnico3=tec3.id)

        where servicio.id=$cid limit 1");

            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 
                }
            }


    //**************** DETALLE ****************
    // BUG: solo productos inventariables. OJO en caso de solicitud compra PRODUCTO NO INVENTARIABLE
   $correo_detalle_result = sql_select("SELECT * FROM servicio_detalle WHERE ($sql_detalle) and id_servicio=$cid order by id ");
  
   $correotabladetalle="";
   if ($correo_detalle_result->num_rows > 0) { 
     while ($correo_detalle = $correo_detalle_result -> fetch_assoc()) {
       
        $correotabladetalle.='
        <tr>
            <td align="center">'. $correo_detalle["cantidad"].'</td>
            <td>'. $correo_detalle["producto_codigoalterno"].'</td>
            <td>'. $correo_detalle["producto_nombre"].'</td>
           
        </tr>
        ';//<td>'. $correo_detalle["producto_nota"].'</td>
        // <td align="right">'. formato_numero( $correo_detalle["precio_costo"],2,'').'</td>
       }
    
    
       

    $subject="$tipo_movimiento_texto de Inventario";

    $cuerpo_html="Notificacion de $tipo_movimiento_texto de Repuestos<br>
    <br>  
    Se ha creado una $tipo_movimiento_texto de repuestos, se envia el detalle de la Orden de Servicio.<br>
    <br>
    <b>Orden de servicio:</b>  No. ".$correo_row["numero"]."<br>
    <b>Fecha:</b> ".formato_fecha_de_mysql($correo_row["fecha"])."<br>
    <b>Fecha de Ingreso Programada:</b> ".formato_fecha_de_mysql($correo_row["fecha_hora_ingreso"])."<br>
    <b>Fecha de Salida Programada:</b> ".formato_fecha_de_mysql($correo_row["fecha_hora_promesa"])."<br>
    <b>Mecanico:</b> ".$correo_row["eltecnico1"]." / ".$correo_row["eltecnico2"]." / ".$correo_row["eltecnico3"]."<br>
    <b>Vehiculo:</b> ".$correo_row["producto_alterno"]." ".$correo_row["producto_nombre"] ."<br>
    <b>Descripcion:</b> ".$correo_row["eltipo"]." , KM ".formato_numero($correo_row["kilometraje"],0)." , ".$correo_row["eltiporevision"]." , ".$correo_row["observaciones"]."<br>
    <br>
    <br>
    <table border=\"1\" cellpadding=\"7px\">
        <tr>
            <th>Cantidad</th>
            <th>Codigo</th>
            <th>Descripcion del repuesto</th>
          
        </tr>
        $correotabladetalle
    </table>
    <br>
    <br>
     ";//  <th>Costo</th>
    
     $cuerpo_sinhtml=strip_tags($cuerpo_html);
     $email_enviar=$_SESSION['correo_bodega'];
     if ($_SESSION['tienda_id']<>$correo_row["id_tienda"]) {
        $email_enviar=trim(get_dato_sql('tienda','correo_bodega',' where id='.$correo_row["id_tienda"]));
     }

     if ($accion =="solcomp") {
        $email_enviar=$_SESSION['correo_compras'];
     if ($_SESSION['tienda_id']<>$correo_row["id_tienda"]) {
            $email_enviar=trim(get_dato_sql('tienda','correo_compras',' where id='.$correo_row["id_tienda"]));
         }
     }
     if ($email_enviar<>'') {
        enviar_correo($email_enviar,$subject,$cuerpo_html,$cuerpo_sinhtml);
     }
     

    } // detalle
    }


?> 