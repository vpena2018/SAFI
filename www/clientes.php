<?php
require_once ('include/framework.php');
pagina_permiso(5);



$tipo_entidad="1";
if (isset($_REQUEST['t'])) { $tipo_entidad = sanear_int($_REQUEST['t']); } 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT * FROM entidad where id=$cid limit 1");

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

	$verror.=validar(("Nombre"),$_REQUEST['nombre'], "text", true);

	$verror.=validar(("Email"),$_REQUEST['email'], "email", false);



   
	 if ($verror=="") {

    //Campos

	$sqlcampos="";


	$nuevoregistro=false;
	$cid= intval($_REQUEST["id"]);
	if (es_nulo($cid)) {
		$nuevoregistro=true;
	}

	
	$sqlcampos.= "  nombre =".GetSQLValue($_REQUEST["nombre"],"text"); 
	$sqlcampos.= " , direccion =".GetSQLValue($_REQUEST["direccion"],"text"); 
	$sqlcampos.= " , ciudad =".GetSQLValue($_REQUEST["ciudad"],"text"); 
	$sqlcampos.= " , departamento =".GetSQLValue($_REQUEST["departamento"],"text"); 
	$sqlcampos.= " , pais =".GetSQLValue($_REQUEST["pais"],"text"); 
	$sqlcampos.= " , telefono =".GetSQLValue($_REQUEST["telefono"],"text"); 
	$sqlcampos.= " , telefono2 =".GetSQLValue($_REQUEST["telefono2"],"text"); 
	// $sqlcampos.= " , telefono3 =".GetSQLValue($_REQUEST["telefono3"],"text"); 
	//$sqlcampos.= " , contacto =".GetSQLValue($_REQUEST["contacto"],"text"); 
	$sqlcampos.= " , notas =".GetSQLValue($_REQUEST["notas"],"text"); 
	// $sqlcampos.= " , identidad =".GetSQLValue($_REQUEST["identidad"],"text"); 
	$sqlcampos.= " , habilitado =".GetSQLValue($_REQUEST["habilitado"],"int"); 
	$sqlcampos.= " , email =".GetSQLValue($_REQUEST["email"],"text"); 
	// $sqlcampos.= " , email2 =".GetSQLValue($_REQUEST["email2"],"text"); 
	// $sqlcampos.= " , email3 =".GetSQLValue($_REQUEST["email3"],"text"); 
	// $sqlcampos.= " , fecha_alta =".GetSQLValue($_REQUEST["fecha_alta"],"text"); 

	if (isset($_REQUEST["combustible"])){$sqlcampos.= " , combustible =".GetSQLValue($_REQUEST["combustible"],"int"); }

	$sqlcampos.= " , codigo_alterno =".GetSQLValue($_REQUEST["codigo_alterno"],"text"); 
	$sqlcampos.= " , rtn =".GetSQLValue($_REQUEST["rtn"],"text"); 

if ($nuevoregistro==false) {
	//Modificando
    $sql="update entidad set ".$sqlcampos." where id=".$cid." limit 1";
	$result = sql_update($sql);
} else {
	//Crear nuevo
	$sql="insert into entidad set fecha_alta=NOW(),tipo=$tipo_entidad,".$sqlcampos." ";
	$result = sql_insert($sql);
 	$cid=$result; //last insert id 
}

	
	if ($result!=false){
		$stud_arr[0]["pcode"] = 1;
    	$stud_arr[0]["pmsg"] ="Guardado";
    	$stud_arr[0]["pcid"] = $cid;
	}

} else {
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] =$verror;
    $stud_arr[0]["pcid"] = 0;
}

	salida_json($stud_arr);
 	exit;

} // fin guardar datos




?>
<div class="maxancho400 mx-auto">

<div class="row">
<div class="col">
    	<div class="form-group">
		  
	 
	<form id="forma_wd" name="forma_wd">
		<fieldset id="fs_forma">
			<div class="">
	 <?php 


if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
if (isset($row["nombre"])) {$nombre= $row["nombre"]; } else {$nombre= "";}
if (isset($row["direccion"])) {$direccion= $row["direccion"]; } else {$direccion= "";}
if (isset($row["ciudad"])) {$ciudad= $row["ciudad"]; } else {$ciudad= "";}
if (isset($row["departamento"])) {$departamento= $row["departamento"]; } else {$departamento= "";}
if (isset($row["pais"])) {$pais= $row["pais"]; } else {$pais= "";}
if (isset($row["telefono"])) {$telefono= $row["telefono"]; } else {$telefono= "";}
if (isset($row["telefono2"])) {$telefono2= $row["telefono2"]; } else {$telefono2= "";}
if (isset($row["telefono3"])) {$telefono3= $row["telefono3"]; } else {$telefono3= "";}
if (isset($row["contacto"])) {$contacto= $row["contacto"]; } else {$contacto= "";}
if (isset($row["notas"])) {$notas= $row["notas"]; } else {$notas= "";}
if (isset($row["identidad"])) {$identidad= $row["identidad"]; } else {$identidad= "";}
if (isset($row["habilitado"])) {$habilitado= $row["habilitado"]; } else {$habilitado= "1";}
if (isset($row["combustible"])) {$combustible= $row["combustible"]; } else {$combustible= "0";}
if (isset($row["email"])) {$email= $row["email"]; } else {$email= "";}
if (isset($row["email2"])) {$email2= $row["email2"]; } else {$email2= "";}
if (isset($row["email3"])) {$email3= $row["email3"]; } else {$email3= "";}
if (isset($row["fecha_alta"])) {$fecha_alta= $row["fecha_alta"]; } else {$fecha_alta= "";}
if (isset($row["codigo_alterno"])) {$codigo_alterno= $row["codigo_alterno"]; } else {$codigo_alterno= "";}
if (isset($row["rtn"])) {$rtn= $row["rtn"]; } else {$rtn= "";}

echo campo("t",'','hidden',$tipo_entidad,'','');
echo campo("id",("Codigo"),'number',$id,' form-control-plaintext',' readonly');
echo campo("codigo_alterno",("Codigo Alterno"),'text',$codigo_alterno,' ',' ');

echo campo("nombre",("Nombre"),'text',$nombre,' ',' ');
echo campo("rtn",("RTN"),'text',$rtn,' ',' ');
echo campo("direccion",("Direccion"),'text',$direccion,' ',' ');
echo campo("ciudad",("Ciudad"),'text',$ciudad,' ',' ');
echo campo("departamento",("Departamento"),'text',$departamento,' ',' ');


// require_once ('include/paises.php');
// echo campo("pais",("Pais"),'select2',valores_combobox_texto(app_combo_paises,$pais),' ',' ');
echo campo("pais",("Pais"),'text',$pais,' ',' ');

// echo campo("contacto",("Contacto"),'text',$contacto,' ',' ');
echo campo("telefono",("Telefono"),'text',$telefono,' ',' ');
echo campo("telefono2",("Telefono Alterno"),'text',$telefono2,' ',' ');
// echo campo("telefono3",("Telefono3","Telefono3"),'text',$telefono3,' ',' ');
echo campo("email",("Email"),'email',$email,' ',' ');
// echo campo("email2",("Email2","Email2"),'email',$email2,' ',' ');
// echo campo("email3",("Email3","Email3"),'email',$email3,' ',' ');


echo campo("notas",("Notas"),'textarea',$notas,' ',' ');
// echo campo("identidad",("Identidad","Identidad"),'text',$identidad,' ',' ');

if ($tipo_entidad==2) {
	echo campo("combustible",("Proveedor Combustible"),'select',valores_combobox_texto(app_combo_si_no,$combustible),' ',' ');
}

	echo campo("habilitado",("Habilitado"),'select',valores_combobox_texto(app_combo_si_no,$habilitado),' ',' ');

	echo campo("fecha_alta",("Fecha Alta"),'text',formato_fecha_de_mysql($fecha_alta),' form-control-plaintext',' readonly');  




					

	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
		<div class="col-sm"><a href="#" onclick="procesar('clientes.php?a=g','forma_wd',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div>
		<div class="col-sm"><a href="#" onclick="$('#ModalWindow').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
		</div>
	</div>

	</fieldset>
	</form>

<?php

			
		   ?>

		    
		  
		 </div>




</div>


</div>


 


</div>