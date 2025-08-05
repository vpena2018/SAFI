<?php
require_once ('include/framework.php');

if (app_enviar_email==true) {
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $correotabladetalle
    // $cid 
 


    $correo_servicio_result = sql_select("SELECT averia.* 
    ,entidad.nombre AS cliente_nombre
    ,producto.nombre AS producto_nombre
    ,producto.codigo_alterno AS producto_alterno

    ,averia_estado.nombre AS elestado


    FROM averia
    LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
    LEFT OUTER JOIN producto ON (averia.id_producto =producto.id)
    LEFT OUTER JOIN averia_estado ON (averia.id_estado=averia_estado.id)


    where averia.id=$cid limit 1");

            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 
                }
            }


    //**************** DETALLE ****************
       
    
       

    $subject="Solicitud de Autorizacion";

    $cuerpo_html="Notificacion de Autorizacion de Actividades / Repuestos<br>
    <br>  
    Se ha creado una Solicitud de Autorizacion, se envia el detalle de la Orden de Averia.<br>
    <br>
    <b>Orden de Averia:</b>  No. ".$correo_row["numero"]."<br>
    <b>Fecha:</b> ".formato_fecha_de_mysql($correo_row["fecha"])."<br>

    <b>Vehiculo:</b> ".$correo_row["producto_alterno"]." ".$correo_row["producto_nombre"] ."<br>
    <b>Descripcion:</b> "." , KM ".formato_numero($correo_row["kilometraje"],0)."  , ".$correo_row["observaciones"]."<br>
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