<?php
require_once ('include/framework.php');

 if (app_enviar_email==true) 
{
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $cid 
    // $mov_atender :  2=atender   3=completar

    $email_enviar="";
    $email_enviar_solicitante="";


    $correo_servicio_result = sql_select("SELECT ventas.* 
        ,producto.codigo_alterno,producto.nombre,producto.placa
        ,ventas_estado.nombre AS elestado        
        ,l1.nombre AS vendedor        
        ,tienda.nombre AS latienda
        ,tienda.correo_ventas_carshop AS correos
        FROM ventas
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)
        LEFT OUTER JOIN ventas_estado ON (ventas.id_estado=ventas_estado.id)
        LEFT OUTER JOIN usuario l1 ON (ventas.id_usuario=l1.id)
        LEFT OUTER JOIN tienda  ON (ventas.id_tienda=tienda.id)       
        where ventas.id=$cid limit 1");

        if ($correo_servicio_result!=false){
           if ($correo_servicio_result -> num_rows > 0) { 
                  $correo_row = $correo_servicio_result -> fetch_assoc(); 
           }
        } 
        $email_enviar='admon.sps@inglosa.hn';
        $subject='Venta de Vehiculo'; 
        $cuerpo_html="
        Notificación de reparacion de vehículo completada  
        <br><br>            
        <b>Tienda:</b> ".$correo_row["latienda"]."<br><br>";
        $cuerpo_html.= "<b>Fecha Completada:</b> ".formato_fecha_de_mysql($correo_row["fecha_reparacion_completada"]);                            
        $cuerpo_html.="        
        <br><br>
        <b>Usuario:</b> ".$correo_row["vendedor"]."
        <br><br>
        <b>Vehículo:</b> ".$correo_row["codigo_alterno"]."  ".$correo_row["nombre"]."  Placa: ".$correo_row["placa"]."                
        <br><br>
        <b>Observaciones:</b> ".$correo_row["observaciones"]."
        ";             
   
             
     $cuerpo_sinhtml=strip_tags($cuerpo_html);    

     if ($email_enviar<>'') {
       enviar_correo($email_enviar,$subject,$cuerpo_html,$cuerpo_sinhtml);
     }    

    }

?> 