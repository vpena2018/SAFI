<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// FUNCIONES
//#######################################################
function salida_json($stud_arr){
    
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 15 Jan 2000 07:00:00 GMT');
            header('Content-type: application/json');
            echo json_encode($stud_arr);    
            exit;   
}


function obtener_ip2() {
	$salida="";
	$pieces = explode(".", $_SERVER['REMOTE_ADDR']);
	if (is_array($pieces)){
		foreach ($pieces as $key => $value) {
			$salida.= $value.'.';
		}
	} else {
		$salida= $pieces;
	}
	return $salida;
}

function generate_id()// de 40 caracteres de ancho
{
	return sha1(rand(10000, 30000) . time() . rand(10000, 30000));
}

function leer_permisos_asignados($grupo_id){
	global $conn;	
	$salida=array();	
 
		
		$sql="SELECT  usuario_nivelxgrupo.nivel_id 
				FROM usuario_nivelxgrupo 
				LEFT OUTER JOIN usuario_nivel ON (usuario_nivelxgrupo.nivel_id=usuario_nivel.id)
				where usuario_nivelxgrupo.grupo_id=$grupo_id 
				AND usuario_nivel.activo=1
				order by usuario_nivelxgrupo.nivel_id";

		$result = $conn -> query($sql);
		$i=0;
		if ($result -> num_rows > 0) {
			while ($row = $result -> fetch_assoc()) {
				$salida[$i]=$row["nivel_id"];				
				$i++;
			}
	
		}
	
	 return $salida;	
	
}

//#######################################################

$txt_mensaje="";
	
 


if (isset($_REQUEST['a'])){


 $accion = $_REQUEST['a']; 

// Logout
if ($accion=="logout"){
  

    // borrar session
    session_set_cookie_params([
        'lifetime' => time() - 3600,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => false,
        'httponly' => true,
        'samesite' => 'strict'
    ]);

	session_start();
	session_destroy();

	// borrar cookies
	setcookie("urs", "", ['samesite' => 'Strict']);
	setcookie("sgt", "",  ['samesite' => 'Strict']);
	setcookie("PHPSESSID", "", ['samesite' => 'Strict']);

	$txt_mensaje='Sesion cerrada';
}


// LOGIN
if ($accion=="201") {
  
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="El usuario y/o contraseña son incorrectos";
  	
  	require_once ('include/config.php');

	$conn = new mysqli(db_ip, db_user, db_pw, db_name);
	if (mysqli_connect_errno()) {  exit("Error al conectar a la base de datos");}

	$username=filter_var(trim($_POST['u']), FILTER_SANITIZE_STRING);
	$password=filter_var(trim($_POST['p']), FILTER_SANITIZE_STRING);


	$sql="SELECT usuario.* ,usuario_grupo.nombre AS grupo_nombre 
		,tienda.nombre as tienda_nombre
		,tienda.correo_bodega
		,tienda.correo_compras
		,tienda.correo_orden_servicio_nueva
		,tienda.correo_orden_averia_nueva
		,tienda.correo_cita
		,tienda.sap_almacen
	  	FROM usuario 
		LEFT OUTER JOIN usuario_grupo ON (usuario.grupo_id=usuario_grupo.id)
		LEFT OUTER JOIN tienda ON (usuario.tienda_id=tienda.id)
		 WHERE usuario='$username' and activo=1 limit 1"; 

	$result = $conn->query($sql) ; 

	if ($result->num_rows > 0)  {

		$row_login  = $result->fetch_assoc();

		if ($row_login['acceso_intentos']>=3) {
			$stud_arr[0]["pcode"] = 0;
    	    $stud_arr[0]["pmsg"] ="Su cuenta se encuentra temporalmente deshabilitada, por favor contacte a su proveedor para que active la cuenta";
		} else {

			if (!password_verify($password,$row_login['clave'])) {	
				$conn->query('update usuario set acceso_intentos=acceso_intentos+1 where id='.$row_login['id']. ' limit 1');
			} else {


				// registro de acceso
				$conn->query('update usuario set acceso_ultimo=now(), acceso_intentos=0 where id='.$row_login['id']. ' limit 1');
				
				//######## definir Variables de sesion ##########
	
				session_set_cookie_params([
			            'lifetime' => 0,
			            'path' => '/',
			            'domain' => $_SERVER['HTTP_HOST'],
			            'secure' => false,
			            'httponly' => true,
			            'samesite' => 'strict'
			        ]);
				
				session_start();
		
				$_SESSION['usuario'] = ucfirst(strtolower($row_login['usuario']));
				$_SESSION['usuario_nombre'] = ucfirst(strtolower($row_login['nombre']));
				$_SESSION['usuario_id'] = $row_login['id'];
				$_SESSION['grupo_id'] = $row_login['grupo_id'];
				$_SESSION['tienda_id'] = $row_login['tienda_id'];
				$_SESSION['tienda_nombre'] = $row_login['tienda_nombre'];
				$_SESSION['sap_almacen'] = $row_login['sap_almacen'];
				$_SESSION['grupo_nombre'] = $row_login['grupo_nombre'];
				$_SESSION['correo_bodega'] = trim($row_login['correo_bodega']);
				$_SESSION['correo_compras'] = trim($row_login['correo_compras']);
				$_SESSION['correo_orden_servicio_nueva'] = trim($row_login['correo_orden_servicio_nueva']);
				$_SESSION['correo_orden_averia_nueva'] = trim($row_login['correo_orden_averia_nueva']);
				$_SESSION['correo_cita'] = trim($row_login['correo_cita']);

				$_SESSION['hora_inicio'] = time();
				$_SESSION['hora_ultima_tran'] = time();
				$_SESSION['seg'] = leer_permisos_asignados($row_login['grupo_id']);

				$_SESSION['formato_fecha'] = "dd/mm/yyyy";
				$_SESSION['formato_fecha_php'] = "d/m/Y";
				$_SESSION['formato_fecha_jquery'] = "dd/mm/yy";


				//colocar_cookie
				$randomid = generate_id();
				//$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT'] . obtener_ip2()));
				$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT']));
				setcookie("urs", $cookieid, ['samesite' => 'Strict']);
				setcookie("sgt", $randomid, ['samesite' => 'Strict']);

				$stud_arr[0]["pcode"] = 1;
    			$stud_arr[0]["pmsg"] ="";

    			//LOG
    			if (app_log_logins==true) {
    				$proxyip="";
    				if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {$proxyip=' ('.$_SERVER['HTTP_X_FORWARDED_FOR'].')'; }
					file_put_contents(app_logs_folder.date("Y-m-d")."_logins.log", "$username , ".date("Y-m-d g:i a").", ".$_SERVER['REMOTE_ADDR']." ".$proxyip.", ".$_SERVER['HTTP_USER_AGENT']." \r\n", FILE_APPEND );
				}
  
			} 

		}	

	}



    salida_json($stud_arr);
    exit;


} //login


//Recover password
if ($accion=="301") { 

	$email=filter_var(trim($_POST['m']), FILTER_SANITIZE_STRING);
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$stud_arr[0]["pcode"] = 0;
   		$stud_arr[0]["pmsg"] ="Ingrese una direccion de email valido";

	} else {

	$stud_arr[0]["pcode"] = 1;
    $stud_arr[0]["pmsg"] ="Se ha enviado un correo, revise su email";
  	
  	require_once ('include/config.php');

	$conn = new mysqli(db_ip, db_user, db_pw, db_name);
	if (mysqli_connect_errno()) {  exit("Error al conectar a la base de datos");}

	


	$sql="SELECT id,nombre FROM usuario WHERE email='$email' and activo=1 limit 1"; 

	$result = $conn->query($sql) ; 

	if ($result->num_rows > 0)  {

		$row_login  = $result->fetch_assoc();

				$new_token= md5($email. date("Y-m-d H:i:s")); 
				$new_token_link=app_host."?a=302&t=".$new_token;
				$conn->query("insert into usuario_recover set fecha=NOW() , token='$new_token', usuario_id=".$row_login['id']);
				
				 				
				if (app_enviar_email==true) {
				require_once ('include/correo.php');
				$subject=app_title." - Recuperar contraseña";
				$cuerpo_sinhtml="Para reestablecer su contraseña abra este enlace: $new_token_link";
				$cuerpo_html='<p>Para reestablecer su contraseña abra este enlace:</p> <a href="$new_token_link">Reestablecer su contraseña</a>';
				
				 enviar_correo($email,$subject,$cuerpo_html,$cuerpo_sinhtml);
				}
		 

}

}


    salida_json($stud_arr);
    exit;

} // recover


//Recover password 2
if ($accion=="302") { 

	if (isset($_REQUEST['t'])) {
		 
		require_once ('include/config.php');

		$conn = new mysqli(db_ip, db_user, db_pw, db_name);
		if (mysqli_connect_errno()) {  exit("Error al conectar a la base de datos");}
		$conn->set_charset("utf8");
		$token=filter_var(trim($_REQUEST['t']), FILTER_SANITIZE_STRING);

		$sql="SELECT usuario_recover.id, usuario_recover.usuario_id ,usuario.email,usuario.usuario
				FROM usuario_recover
				LEFT OUTER JOIN usuario ON (usuario_recover.usuario_id=usuario.id)
				WHERE usuario_recover.token='$token'  and usuario_recover.fecha_usado IS NULL  AND DATEDIFF(NOW(),usuario_recover.fecha)=0
				order by usuario_recover.id desc LIMIT 1"; 

		$result = $conn->query($sql) ; 

		if ($result->num_rows > 0)  {

			$row  = $result->fetch_assoc();

			
			$userid=$row['usuario'];
			$email=$row['email'];
			$newpass= substr(md5($email. date("Y-m-d H:i:s")),1, 6);
			$newpassenc=password_hash($newpass, PASSWORD_BCRYPT);
			$conn->query("update usuario set clave='$newpassenc' where id=".$row['usuario_id']."  limit 1");
			$conn->query("update usuario_recover set fecha_usado=now() where id=".$row['id']."  limit 1");
			

			if (app_enviar_email==true) {
					require_once ('include/correo.php');
					$subject=app_title." - Recuperar contraseña";
					$cuerpo_sinhtml="Su contraseña ha sido reestablecida, su nueva contraseña es: $newpass  y su nombre de usuario es: $userid";
					$cuerpo_html=$cuerpo_sinhtml;
					
					 enviar_correo($email,$subject,$cuerpo_html,$cuerpo_sinhtml); 
				}

        	$txt_mensaje='Su contraseña ha sido reestablecida y enviada a su email';


		} else {
			$txt_mensaje='Error.. Este enlace ha expirado';
		}


	} 



	} // recover 2

} 
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,, maximum-scale=1.0, user-scalable=0 shrink-to-fit=no">
    <meta name="description" content="Online System">
    <meta name="author" content="">
    <meta name="robots" content="none" />
    <title>INGLOSA</title>
 
       
    <link rel="icon" href="img/favicon.ico">
    
	<link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">
	<link href="css/app2.css" rel="stylesheet">
	<link href="css/index.css" rel="stylesheet">
  </head>
  <body>


  <form class="form-signin">



  
<div id="login1" class="card">
  <div class="card-body login-card-body">	
  	 <div class="text-center mb-4 login-titulo">
    	<img src="img/logo.png" alt="" class="   mb-2 mt-2"  width="200" >
    	<hr>
  	 </div>
  
	<p class="login-box-msg">Ingrese sus datosss</p>

  <div class="form-label-group">
    <input type="text" id="user" name="user" class="form-control" placeholder="" autocomplete="off" required autofocus>
    <label for="user">Usuario</label>
  </div>

  <div class="form-label-group">
    <input type="password" id="password" name="password" class="form-control" placeholder="" autocomplete="off" required>
    <label for="password">Contraseña</label>
  </div>


  <a href="#" id="login-btn" class="btn btn-primary btn-block" onclick="login();  return false;">Ingresar</a>

	<p>&nbsp;</p>
	
	
<footer class=""> <a href="#" class="" onclick="recover_show();  return false;">Recuperar contraseña</a></footer>
 </div> 
</div> 





<div id="login2" class="card oculto">
  <div class="card-body login-card-body">	
  
	<p class="login-box-msg">Ingrese su correo electronico</p>

  <div class="form-label-group">
    <input type="email" id="email" name="email" class="form-control" placeholder="" required >
    <label for="email">Email</label>
  </div>



  <a href="#" id="email-btn" class="btn btn-primary btn-block" onclick="recover();  return false;">Recuperar Contraseña</a>

 
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	 <a href="#" class="" onclick="login_show(); return false; ">Regresar</a>

 </div> 
</div>





<p class="mt-5 mb-3 text-muted text-center">&copy; 2024</p>

</form>

<script src="plugins/jquery/jquery-3.5.1.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="js/index.js"></script>


<script type="text/javascript">
		
	$(document).ready(function() {

		$.ajaxSetup({
			cache: false
		});

	 
		<?php 
		if ($txt_mensaje!="") {
			echo "mytoast('info','$txt_mensaje',0);";
		}
		?>


		$('#password').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)  
		  {
		  	if ( $("#login-btn").hasClass("disabled")==false) { login(); }
		     
		  }
		});


	});

</script>
	

</body>
</html>