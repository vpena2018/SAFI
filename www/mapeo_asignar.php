<?php
require_once ('include/framework.php');
pagina_permiso(51);


 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }
    if (isset($_REQUEST['pid'])) { $pid = sanear_int($_REQUEST['pid']); }
    if (isset($_REQUEST['mov'])) { $movimiento = $_REQUEST['mov']; }

	$result = sql_select("SELECT * FROM mapeo where id=$cid limit 1");

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

    if (!isset($_REQUEST["id_producto"])) {
        $verror="Debe ingresar el Vehiculo";
    } else {
        $verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
        $verror.=validar("Zona",$_REQUEST['id'], "int", true);
        $verror.=validar("Tipo de Movimiento",$_REQUEST['tipo'], "int", true);
    }


   
	 if ($verror=="") {

    //Campos

	$actualizarkm=false;

	$cid= intval($_REQUEST["id"]);
    $id_producto= intval($_REQUEST["id_producto"]);
    $tipo= intval($_REQUEST["tipo"]);
    $movimiento= ($_REQUEST["mov"]);
    $mov_asignar=2;
    $vehiculo_asignar=$id_producto;
    $hora_asignar="NOW()";
    $usuario_asignar=$_SESSION["usuario_id"];
	$kilometraje=intval($_REQUEST["kilometraje"]);
	$kilometraje_asignar=$kilometraje;
	$combustible_asignar=GetSQLValue($_REQUEST["combustible"],"text");
    if ($movimiento=="Salida") {
        $mov_asignar=1;
        $vehiculo_asignar='NULL';
        $hora_asignar='NULL';
        $usuario_asignar='NULL';
		$kilometraje_asignar='NULL';
		$combustible_asignar='NULL';
    } 
    

	
	$sqlcampos="";
	$sqlcampos2="";
    $sqlcampos.="hora =".$hora_asignar;  
    $sqlcampos.=", id_usuario =".$usuario_asignar;   
    $sqlcampos.=", id_estado = ".$mov_asignar;
    $sqlcampos.=", id_producto = ".$vehiculo_asignar;
	if (isset($_REQUEST["kilometraje"])) {
		$sqlcampos.=", kilometraje = ".$kilometraje_asignar;
		$sqlcampos2.=", kilometraje = ".GetSQLValue($_REQUEST["kilometraje"],"int");
		if ($kilometraje>0 and $id_producto>0) {$actualizarkm=true;}
	}
	if (isset($_REQUEST["combustible"])) {
		$sqlcampos.=", combustible = ".$combustible_asignar;
		$sqlcampos2.=", combustible = ".GetSQLValue($_REQUEST["combustible"],"text");
	}
 
 
    $sql="update mapeo set ".$sqlcampos." where id=".$cid." limit 1";
	$result = sql_update($sql);
 

	
	if ($result!=false){

        //historial entradas y salidas
        sql_insert("INSERT INTO mapeo_historial SET id_mapeo=$cid, id_tipo=$tipo, id_estado=$mov_asignar, id_producto=$id_producto, id_usuario=".$_SESSION["usuario_id"].", hora=NOW()".$sqlcampos2);
		if($actualizarkm==true) {sql_update("UPDATE producto SET km=".GetSQLValue($kilometraje,"int")."  WHERE id=".GetSQLValue($id_producto,"int")." limit 1");}
        

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
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
if (isset($row["zona"])) {$zona= $row["zona"]; } else {$zona= "";}
if (isset($row["ubicacion"])) {$ubicacion= $row["ubicacion"]; } else {$ubicacion= "";}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "";}
if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
if (isset($row["combustible"])) {$combustible= $row["combustible"]; } else {$combustible= "";}

echo '<h4>'.$movimiento.'</h4>';
echo campo("t",'','hidden',0,'','');
echo campo("id","id",'hidden',$id,' ',' ');
echo campo("mov","mov",'hidden',$movimiento,' ',' ');
echo campo("zona","Zona",'text',$zona,' form-control-plaintext',' readonly');
echo campo("ubicacion","Ubicacion",'text',$ubicacion,' form-control-plaintext',' readonly');

echo campo("tipo","Tipo de Movimiento",'select',valores_combobox_db('mapeo_tipo','','nombre','','',' '),' ',' ');

$producto_etiqueta="";

                if (!es_nulo($id_producto) ) {
                  
                  $result = sql_select("SELECT concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,'')) AS producto_etiqueta
                                      ,placa,chasis   
                                      FROM producto 
                                      WHERE id=$id_producto limit 1");

                  if ($result!=false){
                    if ($result -> num_rows > 0) { 
                      $row = $result -> fetch_assoc(); 
                      $producto_etiqueta=$row['producto_etiqueta'];
                      $placa=$row['placa'];
                      $chasis=$row['chasis'];
                    }
                  }
                }  
               // echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,'class=" "',' onchange="insp_actualizar_veh();" '.$disable_sec1,'get.php?a=3&t=1',$producto_etiqueta);
                echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ',' ','get.php?a=3&t=1',$producto_etiqueta);

	
				
				echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ','');
				echo '<span class="outside-label">Combustible</span> '.campo_combustible('combustible',$combustible,'',''); 


	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <?php if (tiene_permiso(45)) { ?>
		    <div class="col-sm"><a href="#" onclick="procesar_mapeo('mapeo_asignar.php?a=g','forma_wd',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div>
        <?php } ?>		
        <div class="col-sm"><a href="#" onclick="$('#ModalWindow').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
		</div>
	</div>

	</fieldset>
	</form>

   
		  
		 </div>


</div>


</div>


</div>
<script>

function procesar_mapeo(url,forma,adicional){
	 {

	$("#"+forma+" .xfrm").addClass("disabled");		
	cargando(true); 
			
	var datos=$("#"+forma).serialize();

	 $.post( url,datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);
				mytoast('success',json[0].pmsg,3000) ;

					// $("#"+forma+' #id').val(json[0].pcid);
			
				if (forma=='forma_wd') {
                    limpiar_tabla('tabla_mapeo');
                    procesar_tabla('tabla_mapeo');
                    $('#ModalWindow').modal('hide');
                }
			
			}
		} else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
		  
	})
	  .done(function() {
	   
	  })
	  .fail(function(xhr, status, error) {
	   		cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	  })
	  .always(function() {
	   
		$("#"+forma+" .xfrm").removeClass("disabled");	
	  });
		
		
	}
		
}
</script>