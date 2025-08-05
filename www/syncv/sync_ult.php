<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

date_default_timezone_set('America/Tegucigalpa');

// Deshabilitar errores
error_reporting(0);

require_once ('../include/config.php');

//file_put_contents(app_logs_folder."LOG_Sync".date("Y-m-d").".log",  date("Y-m-d g:i a").", ".$_SERVER['REMOTE_ADDR'].",  ULT". " \r\n", FILE_APPEND ); 
 
$salida="";

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (!mysqli_connect_errno()) {	  
		$conn->set_charset("utf8");
} else {  exit; }

$result = $conn->query("SELECT fecha_hora FROM actualizacion WHERE id=1") ;
if ($result->num_rows > 0) {    
    $row = $result -> fetch_assoc();    
      $salida=$row["fecha_hora"];
     
}

echo $salida;

?>