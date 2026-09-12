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

define("app_host", "http://localhost/");
define("app_dir", "c:/DEV-git/www/reportes/");


$ip_scan="http://localhost:8082/"; 
  
// base de datos local
define("db_user", "CHANGE_ME");  // Usuario de la Base de datos
define("db_pw", "CHANGE_ME");  // Clave
//define("db_pw", "CHANGE_ME");  // Clave

define("db_ip", "10.10.1.31");  // Ip o host donde se encuentra la base de datos
define("db_name", "inglosa"); //Nombre de base de datos


define("app_email", "xxx@xxx.con");
define("app_email_name", "Flota");
define("app_email_host", "smtp.xxx.con");
define("app_email_user", "CHANGE_ME");
define("app_email_pass", "CHANGE_ME");
define("app_email_port", "587");
define("app_enviar_email", false);

// Logs
define("app_logs_folder", "c:/DEV-git/SAFI/www/logs/");
define("app_log_logins", true);
define("app_log_email_errors", true);

//Varios
define("app_reg_por_pag", 500);  // Cantidad de registros a mostrar en tablas LIMIT


// Format de la fecha y hora
date_default_timezone_set('America/Tegucigalpa');
define("db_gmt_offset", -6 ); // offset de Hora local segun la Hora GMT 



define("app_Seed", "zr50V29Xy03H7vq18fW4l");  // Semilla para operaciondes en criptografia
define("app_Seed_ancho", 8);  // Cantidad de caracteres de la segunda semilla aleatoria


// #########################################
// #### IA para lectura de comprobantes ###
// #########################################
// Usada para leer banco/fecha/referencia/monto de los comprobantes de pago
// (ver include/ia_comprobantes.php). Mientras quede vacia, la lectura automatica
// se desactiva sola y el usuario llena los datos a mano (el guardado y la
// validacion de duplicados si funcionan).
//
// La clave en si vive en su propio archivo (include/openai_key.php, copialo de
// include/openai_key.example.php); si no existe, queda vacia sin romper nada.
if (file_exists(__DIR__ . '/openai_key.php')) {
    require_once(__DIR__ . '/openai_key.php');
} else {
    define("app_openai_api_key", "");
}
define("app_openai_model", "gpt-4.1-mini");  // Modelo usado para leer el comprobante

//Control de errores
// error_reporting(0); //***** Activar EN PRODUCCION ******


?>