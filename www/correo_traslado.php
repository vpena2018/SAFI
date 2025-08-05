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


    $correo_servicio_result = sql_select("SELECT orden_traslado.* 
        ,producto.codigo_alterno,producto.nombre,producto.placa
        ,orden_traslado_estado.nombre AS elestado
        ,l1.nombre AS motorista1
        ,l2.usuario AS solicitante1
        ,p1.nombre AS elproveedor
        ,p1.email as emailproveedor
        ,t1.nombre AS tiendasalida
        ,t2.nombre AS tiendadestino

        FROM orden_traslado
        LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
        LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
        LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
        LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
        LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
        LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
        LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)

        where orden_traslado.id=$cid limit 1");

            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 
                }
            }
  
     
    if ($mov_atender==2) { //salida
        $subject='Salida de vehículo'; 
        $cuerpo_html="
        Notificación de salida de vehículo
        <br><br>
        Se ha creado y atendido un traslado de vehículo
        <br><br>
        <b>Orden de traslado No.</b> ".$correo_row["numero"]."
        <br><br>
        <b>Fecha de salida:</b> ".formato_fecha_de_mysql($correo_row["traslado_inicio"])."
        <br><br>
        <b>Hora de salida:</b> ".formato_solohora_de_mysql($correo_row["traslado_inicio"])."
        <br><br>
        <b>Conductor:</b> ".$correo_row["motorista1"]."
        <br><br>
        <b>Vehículo:</b> ".$correo_row["codigo_alterno"]."  ".$correo_row["nombre"]."  Placa: ".$correo_row["placa"]."
        <br><br>
        <b>Tienda salida:</b> ".$correo_row["tiendasalida"]."<br><br>";

         if ($correo_row['tipo_destino']==2) {
            $cuerpo_html.="<b>Proveedor destino:</b> ".$correo_row["elproveedor"];
            //Proveedor
            if(!es_nulo($correo_row["emailproveedor"])){
                $email_enviar=trim($correo_row["emailproveedor"]);
            }
        } else {
            $cuerpo_html.="<b>Tienda destino:</b> ".$correo_row["tiendadestino"];
        }

        $cuerpo_html.="
        <br><br>
        <b>Observaciones del creador:</b> ".$correo_row["observaciones"]."
        <br><br>
        <b>Combustible salida:</b> ".$correo_row["combustible_salida"]."
        <br><br>
        <b>Kilometraje salida:</b> ".$correo_row["kilometraje_salida"]."
        "; 

         //solicitante
         $email_enviar_solicitante=trim(get_dato_sql('usuario','email',' where id='.$correo_row["id_solicitante"]));

   
        
    } else { // entrada
        $subject='Entrada de vehículo';        
        $cuerpo_html="
        Notificación de entrada de vehículo
        <br><br>
        Se ha realizado un traslado de vehículo
        <br><br>
        <b>Orden de traslado No.</b> ".$correo_row["numero"]."
        <br><br>
        <b>Fecha de entrada:</b> ".formato_fecha_de_mysql($correo_row["traslado_final"])."
        <br><br>
        <b>Hora de entrada:</b> ".formato_solohora_de_mysql($correo_row["traslado_final"])."
        <br><br>
        <b>Conductor:</b> ".$correo_row["motorista1"]."
        <br><br>
        <b>Vehículo:</b> ".$correo_row["codigo_alterno"]."  ".$correo_row["nombre"]."  Placa: ".$correo_row["placa"]."
        <br><br>
        <b>Tienda salida:</b> ".$correo_row["tiendasalida"]."<br><br>";

        if ($correo_row['tipo_destino']==2) {
            $cuerpo_html.="<b>Proveedor destino:</b> ".$correo_row["elproveedor"];

            //Proveedor
            if(!es_nulo($correo_row["emailproveedor"])){
                $email_enviar=trim($correo_row["emailproveedor"]);
            }
            

           ///averias
           //malo: $email_enviar=trim(get_dato_sql('tienda_agencia LEFT OUTER JOIN tienda ON (tienda_agencia.tienda_id=tienda.id)','tienda_agencia.correo_orden_averia_nueva',' where tienda_agencia.id='.$correo_row["id_tienda_destino"]));

        } else {
            $cuerpo_html.="<b>Tienda destino:</b> ".$correo_row["tiendadestino"];
            
            //tienda destino 
            $email_enviar=trim(get_dato_sql('tienda_agencia LEFT OUTER JOIN tienda ON (tienda_agencia.tienda_id=tienda.id)','tienda_agencia.correo_orden_servicio_nueva',' where tienda_agencia.id='.$correo_row["id_tienda_destino"]));
            $email_enviar_rdp=trim(get_dato_sql('tienda_agencia LEFT OUTER JOIN tienda ON (tienda_agencia.tienda_id=tienda.id)','tienda_agencia.correo_completada_rdp',' where tienda_agencia.id='.$correo_row["id_tienda_destino"]));
        }

        $cuerpo_html.="

        <br><br>
        <b>Observaciones del motorista:</b> ".$correo_row["observaciones2"]."
        <br><br>
        <b>Combustible entrada:</b> ".$correo_row["combustible_entrada"]."
        <br><br>
        <b>Kilometraje entrada:</b> ".$correo_row["kilometraje_entrada"]."
        "; 

        
    }        


    
    
     $cuerpo_sinhtml=strip_tags($cuerpo_html);

    


     if ($email_enviar<>'') {
       enviar_correo($email_enviar,$subject,$cuerpo_html,$cuerpo_sinhtml);
     }
      if ($email_enviar_solicitante<>'') {
       enviar_correo($email_enviar_solicitante,$subject,$cuerpo_html,$cuerpo_sinhtml);
     }

     if ($email_enviar_rdp<>'') {
        enviar_correo($email_enviar_rdp,$subject,$cuerpo_html,$cuerpo_sinhtml);
      }

    }


?> 