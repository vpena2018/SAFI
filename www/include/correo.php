<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('phpcorreo/src/Exception.php');
require_once('phpcorreo/src/PHPMailer.php');
require_once('phpcorreo/src/SMTP.php');


function enviar_correo($email_a,$subject,$cuerpo_html,$cuerpo_sinhtml,$correos_enviar= array(),$adjuntar_archivo=""){
	$salida=false;
	
	if (app_enviar_email==true) {
		$mail = new PHPMailer;
		$mail->CharSet = "UTF-8";
	//	$mail->SMTPDebug = 2;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		$mail->IsSMTP(); 
		 
		$mail->Host = app_email_host;  
		$mail->SMTPAuth = true;                             
		$mail->Username = app_email_user;                          
		$mail->Password = app_email_pass;  
		$mail->Port=app_email_port;                        
		$mail->SMTPSecure = 'tls';                           
		
		$mail->From = app_email;
		$mail->FromName = app_email_name;
		
		if (count($correos_enviar)>0) {
			foreach ($correos_enviar as $correo) {
   			    $mail->AddAddress($correo, '');
			 }
		}

		if ($email_a<>'') {
			$loscorreos = explode(";", $email_a);
			foreach ($loscorreos as $elcorreo) {
				$mail->AddAddress(trim($elcorreo), ''); 
				}			 
		}
   		

		$mail->AddReplyTo(app_email, app_email_name);
		//$mail->AddCC('');	        

		$mail->IsHTML(true);                                 
		
		$mail->Subject = $subject;
		$mail->Body    = ''.$cuerpo_html.'';
		$mail->AltBody = $cuerpo_sinhtml;

		if (!es_nulo($adjuntar_archivo)) {
			if (file_exists($adjuntar_archivo)) { 
				try {
					$mail->AddAttachment($adjuntar_archivo);
				} catch (\Throwable $th) {
					if (app_log_email_errors==true) {
						file_put_contents(app_logs_folder.date("Y-m-d")."_correo_error.log", "$subject ,".date("Y-m-d g:i a").", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT']." Mailer Error: ".$mail->ErrorInfo." ".$th." \r\n", FILE_APPEND );
					}
				}
				
			}
		}
		
		$salida=true;

		if(!$mail->Send()) {	
				$salida=false;	  // echo 'Mailer Error: ' . $mail->ErrorInfo;
				
				if (app_log_email_errors==true) {
					file_put_contents(app_logs_folder.date("Y-m-d")."_correo_error.log", "$subject ,".date("Y-m-d g:i a").", ".$_SERVER['REMOTE_ADDR'].", ".$_SERVER['HTTP_USER_AGENT']." Mailer Error: ".$mail->ErrorInfo." \r\n", FILE_APPEND );
				}
				
			}
	}
		
	
return $salida;
	
	
}

?>