<?php
// ##############################################################################
// #                                                                            #
// # Modulo Configuracion                                                       #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################

// #########################################
// ######### Configuracion Basica ##########
// #########################################


// Aplicacion
define("app_empresa", "INGLOSA");

define("app_host", "https://flota.inglosa.hn/");
define("app_dir", "/var/www/html/");

//$ip_scan="http://192.168.252.131:8082/";
$ip_scan="http://localhost:8082/"; 
  

define("db_user", "ing_user");  // Usuario de la Base de datos
define("db_pw", "K0peis03mzG93f");  // Clave
define("db_ip", "localhost");  // Ip o host donde se encuentra la base de datos
define("db_name", "inglosa"); //Nombre de base de datos

// Cuenta de correo 
define("app_email", "copiadora59@inglosa.hn");
define("app_email_name", "Flota");
define("app_email_host", "smtp.office365.com");
define("app_email_user", "copiadora59@inglosa.hn");
define("app_email_pass", "Eacopy2018*");
define("app_email_port", "587");
// Requiere SSL: Sí
// Requiere TLS: Sí
// Autenticación: Sí 


define("app_enviar_email", true);
define('app_logs_folder', 'C:/DEV-git/SAFI/www/logs/');

// Logs
//define("app_logs_folder", "/var/www/html/logs/");

/*if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    define('app_logs_folder', 'C:/DEV-git/SAFI/www/logs/');
} else {
    define('app_logs_folder', '/var/www/html/logs/');
}*/


define("app_log_logins", true);
define("app_log_email_errors", true);

//Varios
define("app_reg_por_pag", 100);  // Cantidad de registros a mostrar en tablas LIMIT


// Format de la fecha y hora
date_default_timezone_set('America/Tegucigalpa');
define("db_gmt_offset", -6 ); // offset de Hora local segun la Hora GMT 



define("app_Seed", "zr50V29Xy03H7vq18fW4l");  // Semilla para operaciondes en criptografia
define("app_Seed_ancho", 8);  // Cantidad de caracteres de la segunda semilla aleatoria


// #########################################
// #### IA para lectura de comprobantes ###
// #########################################
// Usada para leer banco/fecha/referencia/monto de los comprobantes de pago
// (ver include/ia_comprobantes.php). Se obtiene en https://platform.openai.com/api-keys
// Mientras quede vacia, la lectura automatica se desactiva sola y el usuario
// llena los datos a mano (el guardado y la validacion de duplicados si funcionan).
define("app_openai_api_key", "");  // TODO: pegar aqui la API key cuando este disponible
define("app_openai_model", "gpt-4.1-mini");  // Modelo usado para leer el comprobante

//Control de errores
 error_reporting(0); //***** Activar EN PRODUCCION ******


?>