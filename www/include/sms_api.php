<?php
// ##############################################################################
// # Infosistemas 2022                                                          #
// # Modulo SMS API                                                         #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################

// #########################################
// ######### Configuracion Basica ##########
// #########################################


$url_api="https://ec.tigobusiness.hn/api/http/send_to_contact?";
$api_key ="2VZELLcqn2vB0ol1KVt6hdtWVtbPg9US";

date_default_timezone_set('America/Tegucigalpa');

ini_set('max_execution_time', '900');

// ################# FIN DE CONFIGURACION ######################

//parametros
// $sms_numero="88888888";
// $sms_mensaje="Prueba";

if (isset($sms_numero,$sms_mensaje)) {



$url_api.="msisdn="."504".$sms_numero;
$url_api.="&message=".urlencode($sms_mensaje) ;
$url_api.="&api_key=".$api_key;

$header=array(
    "User-Agent: Mozilla/5.0 (PHP; U; CPU; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011",
    "Accept-language: en",
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_api );
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);

curl_setopt($ch, CURLOPT_HTTPHEADER, $header );


$json_respuesta = curl_exec( $ch );

$smsenviado=0;

try {
    $respuesta = json_decode($json_respuesta, true);
} catch (\Throwable $th) {
    $respuesta = "";
}

if (is_array($respuesta)) {
        try {        
            $smsenviado=$respuesta['sms_sent'];
        } catch (\Throwable $th) {       
        }
   

    }


}


 
   
?>