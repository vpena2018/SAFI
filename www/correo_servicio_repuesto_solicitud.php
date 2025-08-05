<?php
require_once ('include/framework.php');

if (app_enviar_email==true) {
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $correotabladetalle
    // $cid 
 

    
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
       
    
       

    $subject="Solicitud de Autorizacion";

    $cuerpo_html="Notificacion de Autorizacion de Actividades / Repuestos<br>
    <br>  
    Se ha creado una Solicitud de Autorizacion, se envia el detalle de la Orden de Servicio.<br>
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
    
     //correos autorizadores
    $result_correos = sql_select("SELECT email
	FROM usuario
	WHERE activo=1
	AND grupo_id=5
	AND tienda_id=".$correo_row["id_tienda"]);
      
    $correos_enviar= array();  
    if ($result_correos!=false){
        if ($result_correos -> num_rows > 0) {          
            while ($rowcc = $result_correos -> fetch_assoc()) {
                array_push($correos_enviar, $rowcc["email"]);
            }
        }
    }
     

     if (count($correos_enviar)>0) {
        enviar_correo('',$subject,$cuerpo_html,$cuerpo_sinhtml,$correos_enviar);
     }
     

   
    }


?> 