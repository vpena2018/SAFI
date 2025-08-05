<?php
    date_default_timezone_set('America/Tegucigalpa');
	require_once ('../include/config.php');
	
    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {	  
 	   $conn->set_charset("utf8");
    } else { salida_json($stud_arr); exit; }
	
    /*$ftp_server="ftp://flota.inglosa.hn";  //servidor ftp
    $ftp_user_name="ftpdev";       //user ftp
    $ftp_user_pass="L0h5Fd3sJ5r4Fz1";    //pass ftp    
    $conn_id = ftp_connect($ftp_server); //creando la conexión     */
 
    $ftp_server="ftp.hertzhn.com:21";  //servidor ftp
    $ftp_user_name="website@hertzhn.com";       //user ftp
    $ftp_user_pass="3q7SjpfjrA6C";    //pass ftp    
    $conn_id = ftp_connect($ftp_server); //creando la conexión    
 
    // login 
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    
    if((!$conn_id) || (!$login_result)){
       echo "Error en Conexion al Servidor FTP";
       die;
    }
    // Base de Datos  
	$resultsms = $conn->query("SELECT archivo
	FROM inspeccion_foto
	where date(fecha)<='2022-07-30'"); 
		if ($resultsms->num_rows > 0) {          
			while ($rowsms = $resultsms -> fetch_assoc()){                    
                $archivof=trim($rowsms["archivo"]);
                $local_file = $archivof;  //fichero local
                $server_file = $archivof;  //fichero remoto
    
				echo $archivof.'<br/>';				  	
                
                // descargando el fichero y guardandole en local
             /*   if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
                    echo "Se descargo el archivo! $local_file\n";
                }
                else {
                    echo "No se logro descargar \n";
                }*/
			}
            // cierra la conexion
            ftp_close($conn_id);	
		}	
?>

