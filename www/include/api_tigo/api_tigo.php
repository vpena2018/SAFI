<?php
//FECHA                       DEVELOPER                   DESCRIPCION
//09-11-2026                  Moises Gamez                INC-20261109 - Creacion de API TIGO para SMSS reformulada como Object


require_once('include/framework.php');
require_once('include/helpers.php');

date_default_timezone_set('America/Tegucigalpa');

ini_set('max_execution_time', '900');
class Api_tigo
{
    // #########################################
    // ######### Configuracion Basica ##########
    // #########################################

    private $url_api = "https://ec.tigobusiness.hn/api/http/send_to_contact?";
    private $api_key = "2VZELLcqn2vB0ol1KVt6hdtWVtbPg9US";

    // parametros de ejemplo como deberia ir el mensaje
    // $sms_numero="88888888";
    // $sms_mensaje="Prueba";

    private function send_smss($sms_numero, $sms_mensaje): void
    {
        if (isset($sms_numero, $sms_mensaje)) {

            $url_api = $this->url_api;
            $url_api .= "msisdn=" . "504" . $sms_numero;
            $url_api .= "&message=" . urlencode($sms_mensaje);
            $url_api .= "&api_key=" . $this->api_key;

            $header = array(
                "User-Agent: Mozilla/5.0 (PHP; U; CPU; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011",
                "Accept-language: en",
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            $json_respuesta = curl_exec($ch);

            $smsenviado = 0;

            try {
                $respuesta = json_decode($json_respuesta, true);
            } catch (\Throwable $th) {
                $respuesta = "";
                bitacora_sistema('api_tigo.php', 'Error al enviar mensaje: ' . $th->getMessage());
            }

            if (is_array($respuesta)) {
                try {
                    $smsenviado = $respuesta['sms_sent'];
                } catch (\Throwable $th) {
                    bitacora_sistema('api_tigo.php', 'Error al enviar mensaje: ' . $th->getMessage());
                }

            }

        }

    }

    function enviarSmss($id_usuario, $mensaje)
    {
        try {
            if ($id_usuario) {
                $info_motorista = getUsuario($id_usuario);
                if ($info_motorista) {
                    $usuario_motorista = $info_motorista['usuario'];
                    $telefono_motorista = $info_motorista['telefono'];
                    if (validateNumberBySize($telefono_motorista, 8)) { //funcion del framework.php                                                                     
                        $this->send_smss($telefono_motorista, $mensaje);
                        bitacora_sistema('domicilio_mant.php', 'Mensaje enviado por smss a: ' . $telefono_motorista);
                    }
                } else {
                    bitacora_sistema('domicilio_mant.php', 'No se encontro informacion del motorista');
                }
            } else {
                bitacora_sistema('domicilio_mant.php', 'No se encontro informacion del motorista');
            }
        } catch (Exception $e) {
            bitacora_sistema('domicilio_mant.php', 'Error intentando enviar mensaje de whatsapp: ' . $e->getMessage());
        }
    }


}
?>