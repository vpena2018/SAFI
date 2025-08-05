<?php
require_once ('include/framework.php');
//pagina_permiso(xxx);




if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {

	$result = sql_select("SELECT usuario, nombre, email, telefono FROM usuario where id=".$_SESSION['usuario_id']." limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 

			$row = $result -> fetch_assoc(); 


		}
	}

} // fin leer datos

// guardar Datos    ############################  
if ($accion=="g") {
 //sleep(3);
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

    //Validar
	$verror="";
	$verror.=validar("Email",$_REQUEST['email'], "email", false);
	$verror.=validar("Telefono",$_REQUEST['telefono'], "text", false);
 

	if ($_REQUEST['modc']=="1") {
		$verror.=validar("Contraseña",$_REQUEST['npword'], "text", true);
		$verror.=validar("Contraseña Confirma",$_REQUEST['npword2'], "text", true);
		if ($_REQUEST['npword']<>$_REQUEST['npword2']) {
				$verror.="La contraseña no coincide con la confirmación";
			} else {
		if(strlen($_REQUEST['npword']) < 6) {$verror="La contraseña debe contener al menos 6 letras o numeros";}
		}
	}

   
	 if ($verror=="") {

    //Campos

	$sqlcampos="";

 
	$sqlcampos.= "  email =".GetSQLValue($_REQUEST["email"],"text"); 
	$sqlcampos.= " , telefono =".GetSQLValue($_REQUEST["telefono"],"text"); 
	
	if ($_REQUEST['modc']=="1") { 
		$sqlcampos.= " , clave =".GetSQLValue(password_hash(trim($_REQUEST["npword"]), PASSWORD_BCRYPT),"text"); 
	}

    $sql="update usuario set ".$sqlcampos." where id=".$_SESSION['usuario_id']." limit 1";

    //Guardar
	$result = sql_update($sql);

	if ($result!=false){
		$stud_arr[0]["pcode"] = 1;
    	$stud_arr[0]["pmsg"] ="Guardado";
	}

} else {
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] =$verror;
}

	salida_json($stud_arr);
 	exit;

} // fin guardar datos




?>
<div class="card-body">

<div class="row">
    <div class=" col-sm-2">
    	<img class="img-thumbnail" src="img/user.jpg" alt="">
    </div>

    <div class=" col-sm-10">
    	<div class="form-group">
		  
	 
	<form id="forma" name="forma">
		<fieldset id="fs_forma">
			<div class="maxancho400">
	 <?php 


		if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
		if (isset($row["usuario"])) {$usuario= $row["usuario"]; } else {$usuario= "";}
		if (isset($row["clave"])) {$clave= $row["clave"]; } else {$clave= "";}
		if (isset($row["grupo_id"])) {$grupo_id= $row["grupo_id"]; } else {$grupo_id= "";}
		if (isset($row["nombre"])) {$nombre= $row["nombre"]; } else {$nombre= "";}
		if (isset($row["email"])) {$email= $row["email"]; } else {$email= "";}
		if (isset($row["telefono"])) {$telefono= $row["telefono"]; } else {$telefono= "";}
	 
		if (isset($row["activo"])) {$activo= $row["activo"]; } else {$activo= "";}
		if (isset($row["acceso_ultimo"])) {$acceso_ultimo= $row["acceso_ultimo"]; } else {$acceso_ultimo= "";}
		if (isset($row["acceso_intentos"])) {$acceso_intentos= $row["acceso_intentos"]; } else {$acceso_intentos= "";}

		echo campo("modc",'','hidden','0');
		echo campo("usuario","Usuario",'label',$usuario,'form-control-plaintext','readonly');
		echo campo("nombre","Nombre",'label',$nombre,'form-control-plaintext','readonly');
		echo campo("grupo","Perfil de Usuario",'label',$_SESSION['grupo_nombre'],'form-control-plaintext','readonly');
	?>

	<a  onclick="$('#modc').val('1'); $('#pwdchange').show(); $(this).hide(); return false;" href="#" class="btn btn-light mr-2 mb-2" ><i class="fa fa-unlock"></i> Modificar Contraseña</a>
	<div id="pwdchange" class="bg-warning oculto px-2 py-2 mb-2">
	
	<?php	
		

		echo campo("npword","Nueva Contraseña",'password','','','');
		echo campo("npword2","Confirme Contraseña",'password','','','');
	?>
		
	</div>
	
	<?php
		echo campo("email","Email",'email',$email,'','');
		echo campo("telefono","Telefono",'text',$telefono,'','');
	 			

	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<a href="#" onclick="procesar('mnt_perfil.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check"></i> Guardar</a>
	</div>

	</fieldset>
	</form>

<?php

			
		   ?>

		    
		  
		 </div>


    </div>


</div>


</div>


 


</div>