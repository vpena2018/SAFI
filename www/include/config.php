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
define("db_user", "root");  // Usuario de la Base de datos
define("db_pw", "inglosalocal");  // Clave
define("db_ip", "localhost");  // Ip o host donde se encuentra la base de datos
define("db_name", "inglosa"); //Nombre de base de datos


define("app_email", "flota@inglosa.hn");
define("app_email_name", "Flota");
define("app_email_host", "smtp.office365.com");
define("app_email_user", "flota@inglosa.hn");
define("app_email_pass", "IgFlot@2018+");
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

//Control de errores
// error_reporting(0); //***** Activar EN PRODUCCION ******


?>