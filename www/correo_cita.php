<?php

if (app_enviar_email==true) {
    require_once ('include/correo.php');

    // variables vienen de otro archivo
    // $cid 
    

    $correo_servicio_result = $conn->query("SELECT  cita.id , cita.id_usuario , cita.id_tienda , cita.id_estado , cita.id_taller , cita.fecha , cita.hora , cita.tipo , cita.numero , cita.numero_alterno , cita.id_producto , cita.cliente_id , cita.cliente_email , cita.cliente_contacto , cita.cliente_contacto_identidad , cita.cliente_contacto_telefono , cita.fecha_cita , cita.hora_cita , cita.kilometraje , cita.placa , cita.chasis , cita.observaciones
    ,cita.empresa,cita.ciudad
    ,producto.codigo_alterno AS elcodvehiculo, producto.nombre AS elvehiculo

   ,cita_taller.taller_nombre
   ,cita_horario.nombre AS lahora
   ,if(cita.tipo=1,'Preventivo','Correctivo') AS eltipo

   ,cita_taller.taller_nombre_abrevia
   ,cita_taller.enlace
  FROM cita 
  LEFT OUTER JOIN producto ON (cita.id_producto=producto.id)

  LEFT OUTER JOIN cita_taller  ON (cita.id_taller=cita_taller.id_taller)
  LEFT OUTER JOIN cita_horario ON (cita.hora_cita=cita_horario.id)
    where cita.id=$cid limit 1") ;


            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 
                }
            }

            $taller_html="";
            $taller_sms="";
    if ($correo_row["enlace"]<>"") {
        $taller_html="<b>Ubicación:</b > ".'<a href="'.$correo_row["enlace"].'">'.$correo_row["enlace"].'</a> <br>' ;
        $taller_sms=", Ubicación: ".$correo_row["enlace"];
    }

    $subject="Cita de Servicio";

    //." ".$correo_row["elvehiculo"]
    $date1=date_create($correo_row["fecha_cita"]);
    $lafecha=date_format($date1,'d/m/Y');
    $cuerpo_html="Se ha creado una $subject <br>
    <br>
    <b>Vehiculo:</b> ".$correo_row["elcodvehiculo"]."<br>
    <b>Placa:</b> ".$correo_row["placa"]."<br>    
    <b>Kilometraje del Vehículo:</b> ".$correo_row["kilometraje"]."<br>
    <b>Tipo de Revisión:</b> ".$correo_row["eltipo"]."<br>
    <b>Taller:</b> ".$correo_row["taller_nombre"]."<br>
    ".$taller_html."
    <br>
    <b>Fecha:</b> ".$lafecha."<br>
    <b>Hora:</b> ".$correo_row["lahora"]."<br>
    <br>  
    <b>Nombre del Contacto:</b> ".$correo_row["cliente_contacto"]."<br>
    <b>No. Identidad:</b> ".$correo_row["cliente_contacto_identidad"]."<br>
    <b>Teléfono Celular:</b> ".$correo_row["cliente_contacto_telefono"]."<br>
    <b>Correo electrónico:</b> ".$correo_row["cliente_email"]."<br>
    <b>Nombre de Empresa:</b> ".$correo_row["empresa"]."<br>
    <b>Ciudad de Procedencia:</b> ".$correo_row["ciudad"]."<br>
    <br>
    <b>Reporte por detalles de unidad:</b> ".$correo_row["observaciones"]."<br>   
    <br>
    <br>
     ";


    
     $cuerpo_sinhtml=strip_tags($cuerpo_html);


            $email_enviar="" ;
            $sql="SELECT correo_cita FROM tienda where id=".$correo_row["id_tienda"];
            $correo_tienda_result = $conn->query($sql) ;
            if ($correo_tienda_result!=false){
                if ($correo_tienda_result -> num_rows > 0) { 
                    $tienda_row = $correo_tienda_result -> fetch_assoc();
                    $email_enviar=trim($tienda_row["correo_cita"]);
                 }
            }
    

     if ($email_enviar<>'') {
        enviar_correo($email_enviar,$subject,utf8_decode($cuerpo_html),utf8_decode($cuerpo_sinhtml));
     }

     $email_enviar_cliente=trim(filter_var($correo_row["cliente_email"], FILTER_SANITIZE_EMAIL));
     if ($email_enviar_cliente<>'') {
        enviar_correo($email_enviar_cliente,$subject,utf8_decode($cuerpo_html),utf8_decode($cuerpo_sinhtml));
     }


     //enviar SMS
     
     if ($correo_row["cliente_contacto_telefono"]<>'') {
        $sms_mensaje="Hertz: Su cita para: ".$lafecha." ".$correo_row["lahora"]. " ".$correo_row["elcodvehiculo"].", ".$correo_row["taller_nombre_abrevia"].$taller_sms;
      //  enviar_sms($correo_row["cliente_contacto_telefono"],$cuerpo_sms);
        $sms_numero=$correo_row["cliente_contacto_telefono"];
        $smsenviado=0;

        require_once ('include/sms_api.php');
     }
     

     
     

  
    }


?> 