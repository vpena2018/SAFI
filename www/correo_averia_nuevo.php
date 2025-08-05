<?php
require_once ('include/framework.php');

if (app_enviar_email==true) {
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $sql_detalle
    // $cid 
    // $accion 

    
    if (!isset($tipo_movimiento_texto)) {
        $tipo_movimiento_texto='Nueva Orden de Averia';
    }

    $email_enviar2='';
    if (isset($placas)){
        $email_enviar2='analistalegal@inglosa.hn';
    }

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

    

    $subject="$tipo_movimiento_texto";

    $cuerpo_html="Notificacion de $tipo_movimiento_texto<br>
    <br>  
    Se ha creado una $tipo_movimiento_texto<br>
    <br>
    <b>Orden de Averia:</b>  No. ".$correo_row["numero"]."<br>
    <b>Fecha:</b> ".formato_fecha_de_mysql($correo_row["fecha"])."<br>
    <b>Vehiculo:</b> ".$correo_row["producto_alterno"]." ".$correo_row["producto_nombre"] ."<br>
    <b>Descripcion:</b> "." , KM ".formato_numero($correo_row["kilometraje"],0)."  , ".$correo_row["observaciones"]."<br>
    <br>
    <br>
     ";
    
    $cuerpo_sinhtml=strip_tags($cuerpo_html);
    $email_enviar=$_SESSION['correo_orden_averia_nueva'];          
    $email_enviar1='';
    if($correo_row["id_tipo"]==1 || $correo_row["id_tipo"]==4 || $correo_row["id_tipo"]==5 || $correo_row["id_tipo"]==6 ){
        $email_enviar1=trim(get_dato_sql('tienda','correo_contabilidad',' where id='.$correo_row["id_tienda"]));       
    }     
    if ($_SESSION['tienda_id']<>$correo_row["id_tienda"]) {
        $email_enviar=trim(get_dato_sql('tienda','correo_orden_averia_nueva',' where id='.$correo_row["id_tienda"]));        
    }

    if ($email_enviar2=='') {
        if ($email_enviar<>'') {
            enviar_correo($email_enviar,$subject,$cuerpo_html,$cuerpo_sinhtml);
        }
        if ($email_enviar1<>'') {
            enviar_correo($email_enviar1,$subject,$cuerpo_html,$cuerpo_sinhtml);
        }  
    }else{
        //Se le envia alerta abogada si la averia es reposicion de placas
        enviar_correo($email_enviar2,$subject,$cuerpo_html,$cuerpo_sinhtml);
    }
}

?> 