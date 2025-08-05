<?php
require_once ('include/framework.php');

if (app_enviar_email==true) 
{
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $elcodigo 
   
    $correo_servicio_result = sql_select("SELECT servicio.* 
        ,entidad.nombre AS cliente_nombre
        ,producto.nombre AS producto_nombre
        ,producto.codigo_alterno AS producto_alterno
        ,entidad.email
        ,(SELECT A.cliente_email FROM clientes_vehiculos A WHERE A.cliente_id=servicio.cliente_id AND A.id_producto=servicio.id_producto) AS cliente_email
        FROM servicio
        LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
        LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
        where servicio.id=$elcodigo limit 1");

            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 


                    $subject="Orden de Servicio # ".$correo_row["numero"]." Completada";

                    $cuerpo_html="Estimado Cliente, <br><br>
                    Se le notifica que la orden de servicio # ".$correo_row["numero"]." fue completada.<br>
                    <br> <br> 
                    La orden ha sido adjuntada a este correo.
                    <br>
                    <br>
                    Gracias

                    ";
                    
                    $cuerpo_sinhtml=strip_tags($cuerpo_html);
                    
                    if (es_nulo($correo_row['cliente_email'])){
                       $email_enviar=trim($correo_row['email']);
                    }else{
                       $email_enviar=trim($correo_row['cliente_email']); 
                    }

                    if ($email_enviar<>'') {
                        //**** adjunto */
                        $guardar_archivo=app_dir.'reportes/'.'OrdenServicio_'.$correo_row["numero"] .'.pdf';
                        require_once ('servicio_pdf.php');
                        //**** */
                        enviar_correo($email_enviar,$subject,$cuerpo_html,$cuerpo_sinhtml,array(),$guardar_archivo);
                      //  ob_end_flush();
                       // ob_end_clean();
                    }



                }
            }




    }


?>