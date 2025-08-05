<?php
require_once ('include/framework.php');
pagina_permiso(5);



$tipo_entidad="0";
if (isset($_REQUEST['t'])) { $tipo_entidad = sanear_int($_REQUEST['t']); } 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT * FROM producto where id=$cid limit 1");

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

    $verror.=validar("Codigo Alterno",$_REQUEST['codigo_alterno'], "text", true);
    $verror.=validar("Nombre",$_REQUEST['nombre'], "text", true);



   
	 if ($verror=="") {

    //Campos

	$sqlcampos="";


	$nuevoregistro=false;
	$cid= intval($_REQUEST["id"]);
	if (es_nulo($cid)) {
		$nuevoregistro=true;
	}

	
	$sqlcampos="";
    if (isset($_REQUEST["codigo_alterno"])) { $sqlcampos.= " codigo_alterno =".GetSQLValue($_REQUEST["codigo_alterno"],"text"); } 
    if (isset($_REQUEST["nombre"])) { $sqlcampos.= " , nombre =".GetSQLValue($_REQUEST["nombre"],"text"); } 
    if (isset($_REQUEST["codigo_grupo"])) { $sqlcampos.= " , codigo_grupo =".GetSQLValue($_REQUEST["codigo_grupo"],"text"); } 
    if (isset($_REQUEST["habilitado"])) { $sqlcampos.= " , habilitado =".GetSQLValue($_REQUEST["habilitado"],"int"); } 
    // if (isset($_REQUEST["congelado"])) { $sqlcampos.= " , congelado =".GetSQLValue($_REQUEST["congelado"],"int"); } 
    if (isset($_REQUEST["item_compra"])) { $sqlcampos.= " , item_compra =".GetSQLValue($_REQUEST["item_compra"],"int"); } 
    if (isset($_REQUEST["item_venta"])) { $sqlcampos.= " , item_venta =".GetSQLValue($_REQUEST["item_venta"],"int"); } 
    if (isset($_REQUEST["item_inventario"])) { $sqlcampos.= " , item_inventario =".GetSQLValue($_REQUEST["item_inventario"],"int"); } 
    if (isset($_REQUEST["codigo_hertz"])) { $sqlcampos.= " , codigo_hertz =".GetSQLValue($_REQUEST["codigo_hertz"],"text"); } 
    if (isset($_REQUEST["tipo"])) { $sqlcampos.= " , tipo =".GetSQLValue($_REQUEST["tipo"],"int"); } 
    if (isset($_REQUEST["marca"])) { $sqlcampos.= " , marca =".GetSQLValue($_REQUEST["marca"],"text"); } 
    if (isset($_REQUEST["anio"])) { $sqlcampos.= " , anio =".GetSQLValue($_REQUEST["anio"],"text"); } 
    if (isset($_REQUEST["modelo"])) { $sqlcampos.= " , modelo =".GetSQLValue($_REQUEST["modelo"],"text"); } 
    if (isset($_REQUEST["cilindrada"])) { $sqlcampos.= " , cilindrada =".GetSQLValue($_REQUEST["cilindrada"],"text"); } 
    if (isset($_REQUEST["serie"])) { $sqlcampos.= " , serie =".GetSQLValue($_REQUEST["serie"],"text"); } 
    if (isset($_REQUEST["motor"])) { $sqlcampos.= " , motor =".GetSQLValue($_REQUEST["motor"],"text"); } 
    if (isset($_REQUEST["placa"])) { $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa"],"text"); } 
    if (isset($_REQUEST["tipo_vehiculo"])) { $sqlcampos.= " , tipo_vehiculo =".GetSQLValue($_REQUEST["tipo_vehiculo"],"text"); } 
    if (isset($_REQUEST["chasis"])) { $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis"],"text"); } 
    // if (isset($_REQUEST["precio_costo"])) { $sqlcampos.= " , precio_costo =".GetSQLValue($_REQUEST["precio_costo"],"double"); } 
    // if (isset($_REQUEST["precio_venta"])) { $sqlcampos.= " , precio_venta =".GetSQLValue($_REQUEST["precio_venta"],"double"); } 
    // if (isset($_REQUEST["km"])) { $sqlcampos.= " , km =".GetSQLValue($_REQUEST["km"],"int"); } 
    // if (isset($_REQUEST["k5"])) { $sqlcampos.= " , k5 =".GetSQLValue($_REQUEST["k5"],"int"); } 
    // if (isset($_REQUEST["k10"])) { $sqlcampos.= " , k10 =".GetSQLValue($_REQUEST["k10"],"int"); } 
    // if (isset($_REQUEST["k20"])) { $sqlcampos.= " , k20 =".GetSQLValue($_REQUEST["k20"],"int"); } 
    // if (isset($_REQUEST["k40"])) { $sqlcampos.= " , k40 =".GetSQLValue($_REQUEST["k40"],"int"); } 
    // if (isset($_REQUEST["k100"])) { $sqlcampos.= " , k100 =".GetSQLValue($_REQUEST["k100"],"int"); } 

if ($nuevoregistro==false) {
	//Modificando
    $sql="update producto set ".$sqlcampos." where id=".$cid." limit 1";
	$result = sql_update($sql);
} else {
	//Crear nuevo
	$sql="insert into producto set tipo=0,".$sqlcampos." ";
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
<div class="maxancho600 mx-auto">

<div class="row">
<div class="col">
    	<div class="form-group">
		  
	 
	<form id="forma_wd" name="forma_wd">
		<fieldset id="fs_forma">
			<div class="">
	 <?php 


if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
if (isset($row["codigo_alterno"])) {$codigo_alterno= $row["codigo_alterno"]; } else {$codigo_alterno= "";}
if (isset($row["nombre"])) {$nombre= $row["nombre"]; } else {$nombre= "";}
if (isset($row["codigo_grupo"])) {$codigo_grupo= $row["codigo_grupo"]; } else {$codigo_grupo= "";}
if (isset($row["habilitado"])) {$habilitado= $row["habilitado"]; } else {$habilitado= "";}
if (isset($row["congelado"])) {$congelado= $row["congelado"]; } else {$congelado= "";}
if (isset($row["item_compra"])) {$item_compra= $row["item_compra"]; } else {$item_compra= "";}
if (isset($row["item_venta"])) {$item_venta= $row["item_venta"]; } else {$item_venta= "";}
if (isset($row["item_inventario"])) {$item_inventario= $row["item_inventario"]; } else {$item_inventario= "";}
if (isset($row["codigo_hertz"])) {$codigo_hertz= $row["codigo_hertz"]; } else {$codigo_hertz= "";}
if (isset($row["tipo"])) {$tipo= $row["tipo"]; } else {$tipo= "";}
if (isset($row["marca"])) {$marca= $row["marca"]; } else {$marca= "";}
if (isset($row["anio"])) {$anio= $row["anio"]; } else {$anio= "";}
if (isset($row["modelo"])) {$modelo= $row["modelo"]; } else {$modelo= "";}
if (isset($row["cilindrada"])) {$cilindrada= $row["cilindrada"]; } else {$cilindrada= "";}
if (isset($row["serie"])) {$serie= $row["serie"]; } else {$serie= "";}
if (isset($row["motor"])) {$motor= $row["motor"]; } else {$motor= "";}
if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}
if (isset($row["tipo_vehiculo"])) {$tipo_vehiculo= $row["tipo_vehiculo"]; } else {$tipo_vehiculo= "";}
if (isset($row["chasis"])) {$chasis= $row["chasis"]; } else {$chasis= "";}
if (isset($row["precio_costo"])) {$precio_costo= $row["precio_costo"]; } else {$precio_costo= "";}
if (isset($row["precio_venta"])) {$precio_venta= $row["precio_venta"]; } else {$precio_venta= "";}
if (isset($row["km"])) {$km= $row["km"]; } else {$km= "";}
if (isset($row["k5"])) {$k5= $row["k5"]; } else {$k5= "";}
if (isset($row["k10"])) {$k10= $row["k10"]; } else {$k10= "";}
if (isset($row["k20"])) {$k20= $row["k20"]; } else {$k20= "";}
if (isset($row["k40"])) {$k40= $row["k40"]; } else {$k40= "";}
if (isset($row["k100"])) {$k100= $row["k100"]; } else {$k100= "";}

echo campo("t",'','hidden',0,'','');
echo campo("id",("Codigo"),'number',$id,' form-control-plaintext',' readonly');
echo campo("codigo_alterno",("Codigo Inventario"),'text',$codigo_alterno,' ',' ');

echo campo("nombre","Nombre",'text',$nombre,' ',' ');
// echo campo("codigo_grupo","Codigo Grupo",'text',$codigo_grupo,' ',' ');
// echo campo("congelado","Congelado",'number',$congelado,' ',' ');
// echo campo("item_compra","Item Compra",'number',$item_compra,' ',' ');
// echo campo("item_venta","Item Venta",'number',$item_venta,' ',' ');
// echo campo("item_inventario","Item Inventario",'number',$item_inventario,' ',' ');
// echo campo("codigo_hertz","Codigo Hertz",'text',$codigo_hertz,' ',' ');
// echo campo("tipo","Tipo",'number',$tipo,' ',' ');
echo campo("marca","Marca",'text',$marca,' ',' ');
echo campo("anio","Año",'text',$anio,' ',' ');
echo campo("modelo","Modelo",'text',$modelo,' ',' ');
echo campo("cilindrada","Cilindrada",'text',$cilindrada,' ',' ');
echo campo("serie","Serie",'text',$serie,' ',' ');
echo campo("motor","Motor",'text',$motor,' ',' ');
echo campo("placa","Placa",'text',$placa,' ',' ');
echo campo("tipo_vehiculo","Tipo Vehiculo",'text',$tipo_vehiculo,' ',' ');
echo campo("chasis","Chasis",'text',$chasis,' ',' ');
// echo campo("precio_costo","Precio Costo",'number',$precio_costo,' ',' ');
// echo campo("precio_venta","Precio Venta",'number',$precio_venta,' ',' ');
// echo campo("km","Km",'number',$km,' ',' ');
// echo campo("k5","K5",'number',$k5,' ',' ');
// echo campo("k10","K10",'number',$k10,' ',' ');
// echo campo("k20","K20",'number',$k20,' ',' ');
// echo campo("k40","K40",'number',$k40,' ',' ');
// echo campo("k100","K100",'number',$k100,' ',' ');

echo campo("habilitado",("Habilitado"),'select',valores_combobox_texto(app_combo_si_no,$habilitado),' ',' ');




					

	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <?php if (tiene_permiso(45)) { ?>
		    <div class="col-sm"><a href="#" onclick="procesar('productos.php?a=g','forma_wd',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div>
        <?php } ?>		
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