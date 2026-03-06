<?php
require_once ('include/framework.php');

if (app_enviar_email==true) 
{
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $elcodigo 
   


    $correo_servicio_result = sql_select("SELECT inspeccion.id, inspeccion.numero 
            ,inspeccion.cliente_email
            ,entidad.nombre AS cliente_nombre
            ,entidad.email
    
            FROM inspeccion
            LEFT OUTER JOIN entidad ON (inspeccion.cliente_id=entidad.id)


        where inspeccion.id=$elcodigo limit 1");

            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 


                    $subject="HOJA DE INSPECCION # ".$correo_row["numero"]."";

                    $cuerpo_html="Estimado Cliente, <br><br>
                    Se le notifica que la hoja de inspeccion # ".$correo_row["numero"]." fue completada.<br>
                    <br> <br> 
                    La hoja ha sido adjuntada a este correo.
                    <br>
                    <br>
                    Gracias

                    ";
                    
                    $cuerpo_sinhtml=strip_tags($cuerpo_html);
                    $email_enviar='';
                    $email_enviar_adicional=array();
                    $email_enviar=trim($correo_row['email']);
                    if (!es_nulo(trim($correo_row['cliente_email']))) {
                        if ($email_enviar<>$correo_row['cliente_email']) {
                              if (es_nulo($email_enviar)) {  $email_enviar=trim($correo_row['cliente_email']); }
                              else { array_push($email_enviar_adicional,  trim($correo_row['cliente_email'])) ;}
                         }
                    }
                    

                    if ($email_enviar<>'') {
                        //**** adjunto */
                        $guardar_archivo=app_dir.'reportes/'.'Inspeccion_'.$correo_row["numero"] .'.pdf';
                        require_once ('inspeccion_pdf.php');
                        //**** */
                        enviar_correo($email_enviar,$subject,$cuerpo_html,$cuerpo_sinhtml,$email_enviar_adicional,$guardar_archivo);
                      //  ob_end_flush();
                       // ob_end_clean();
                    }



                }
            }




    }


?>