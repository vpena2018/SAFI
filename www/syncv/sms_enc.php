<?php
    date_default_timezone_set('America/Tegucigalpa');
	require_once ('../include/config.php');
	
    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {	  
 	   $conn->set_charset("utf8");
    } else { salida_json($stud_arr); exit; }

	//obtener codigo de vehiculo y celular de cliente  
	$currentDate = date('Y-m-d');  
	$fecha = date("Y-m-d",strtotime($currentDate."- 1 days"));  
  
	$resultsms = $conn->query("SELECT cita.cliente_contacto_telefono
	FROM cita
	LEFT OUTER JOIN producto ON (cita.id_producto=producto.id)
	LEFT OUTER JOIN cita_estado ON (cita.id_estado=cita_estado.id)
	LEFT OUTER JOIN cita_horario ON (cita.hora_cita=cita_horario.id)
	LEFT OUTER JOIN inspeccion ON (cita.id_inspeccion=inspeccion.id)
	LEFT OUTER JOIN servicio ON (servicio.id_inspeccion=inspeccion.id)
	where date(servicio.fecha_hora_final)='$fecha' AND cita.cliente_contacto_telefono IS NOT NULL AND servicio.id_estado=22");  	  
		if ($resultsms->num_rows > 0) {          
			while ($rowsms = $resultsms -> fetch_assoc()){                    
				$sms_mensaje="HERTZ Estimado cliente. Agradeceremos ingresar en el siguiente enlace y llenar la encuesta de satisfacción del servicio en nuestro taller: http://hertzhn.com/E ";          
				$sms_numero=trim($rowsms["cliente_contacto_telefono"]);
				$sms_numero=str_replace('-','', $sms_numero);
				$sms_numero=str_replace(' ','', $sms_numero);
				$smsenviado=0;              								
				if (strlen($sms_numero)==8) {  
					  $url_api="https://ec.tigobusiness.hn/api/http/send_to_contact?";
                      $api_key ="2VZELLcqn2vB0ol1KVt6hdtWVtbPg9US";
					  
					  ini_set('max_execution_time', '900');
					  $header=array(
						  "User-Agent: Mozilla/5.0 (PHP; U; CPU; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011",
						  "Accept-language: en",
					  );

					  $url_api.="msisdn="."504".$sms_numero;
					  $url_api.="&message=".urlencode($sms_mensaje) ;
					  $url_api.="&api_key=".$api_key;                                
									   
					  $ch = curl_init();
					  curl_setopt($ch, CURLOPT_URL, $url_api );
					  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
					  
					  curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
					  
					  $json_respuesta = curl_exec( $ch );  
								  
				} 
			}
		}	
?>

