<?php
require_once ('include/framework.php');
pagina_permiso(130);


 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	if (es_nulo($cid)) {
	 	pagina_permiso(131); //crear nueva cita
	} else {

		$result = sql_select("SELECT  cita.id , cita.id_usuario , cita.id_tienda , cita.id_estado , cita.id_taller , cita.fecha , cita.hora , cita.tipo , cita.numero , cita.numero_alterno , cita.id_producto , cita.cliente_id , cita.cliente_email , cita.cliente_contacto , cita.cliente_contacto_identidad , cita.cliente_contacto_telefono , cita.fecha_cita , cita.hora_cita , cita.kilometraje , cita.placa , cita.chasis , cita.observaciones
		  ,cita.empresa,cita.ciudad
		  ,producto.codigo_alterno AS elcodvehiculo, producto.nombre AS elvehiculo
		 ,entidad.codigo_alterno AS elcodcliente , entidad.nombre AS elcliente
		FROM cita 
		LEFT OUTER JOIN producto ON (cita.id_producto=producto.id)
		LEFT OUTER JOIN entidad ON (cita.cliente_id=entidad.id)
		WHERE cita.id=$cid 
		limit 1");

		if ($result!=false){
			if ($result -> num_rows > 0) { 
				$row = $result -> fetch_assoc(); 
			}
		}

	}

} // fin leer datos

// guardar Datos    ############################  
if ($accion=="g") {
 //sleep(3);
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";
	$stud_arr[0]["patd"] =0;

	$atender=0;
    $cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }
	if (isset($_REQUEST['at'])) { $atender=1; }

	$elcodigo= $cid;
    if (es_nulo($elcodigo)) {$nuevoreg=true;} else {$nuevoreg=false;}


	//solo atender
	if (isset($_REQUEST['at']) and !tiene_permiso(138)) { 
		$sql="update cita set id_estado =4 where id=".$cid." limit 1";
		$result = sql_update($sql);
		$cid=$elcodigo;
		if ($result!=false){ 
			$stud_arr[0]["pcode"] = 1;
			$stud_arr[0]["pmsg"] ="Guardado";
		} else {
			$stud_arr[0]["pcode"] = 0;
			$stud_arr[0]["pmsg"] ="Error 101";
		}	
			$stud_arr[0]["pcid"] = $cid;
			$stud_arr[0]["patd"] = $atender;
		
		salida_json($stud_arr);
		exit;
	}


    //Validar
	$verror="";

	// $verror.=validar("Id Tienda",$_REQUEST['id_tienda'], "int", true);
	// $verror.=validar("Id Estado",$_REQUEST['id_estado'], "int", true);
	// $verror.=validar("Id Taller",$_REQUEST['id_taller'], "int", true);

	$verror.=validar("Tipo",$_REQUEST['tipo'], "int", true);

	// $verror.=validar("Cliente Email",$_REQUEST['cliente_email'], "text", true);
	// $verror.=validar("Cliente Contacto",$_REQUEST['cliente_contacto'], "text", true);
	// $verror.=validar("Cliente Contacto Identidad",$_REQUEST['cliente_contacto_identidad'], "text", true);
	// $verror.=validar("Cliente Contacto Telefono",$_REQUEST['cliente_contacto_telefono'], "text", true);
	$verror.=validar("Fecha Cita",$_REQUEST['fecha_cita'], "text", true);
	$verror.=validar("Hora Cita",$_REQUEST['hora_cita'], "int", true);
	// $verror.=validar("Kilometraje",$_REQUEST['kilometraje'], "int", true);
	// $verror.=validar("Placa",$_REQUEST['placa'], "text", true);
	// $verror.=validar("Chasis",$_REQUEST['chasis'], "text", true);
	// $verror.=validar("Observaciones",$_REQUEST['observaciones'], "text", true);
	


		if (isset($_REQUEST['id_producto'])) {
			$verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
		} else {$verror.=validar("Vehiculo",' ', "int", true);}

		if (isset($_REQUEST['cliente_id'])) {
			$verror.=validar("Cliente",$_REQUEST['cliente_id'], "int", true);
		} else {$verror.=validar("Cliente",' ', "int", true);}


    
	 if ($verror=="") {

    //Campos
	


    $mov=sanear_int($_REQUEST['mov']);


	
	$sqlcampos="";


	if (isset($_REQUEST["id_tienda"])) { $sqlcampos.= "  id_tienda =".GetSQLValue($_REQUEST["id_tienda"],"int"); } 
	if (isset($_REQUEST["id_taller"])) { $sqlcampos.= " , id_taller =".GetSQLValue($_REQUEST["id_taller"],"int"); } 
	if (isset($_REQUEST["tipo"])) { $sqlcampos.= " , tipo =".GetSQLValue($_REQUEST["tipo"],"int"); } 
	
	if ($nuevoreg==false){
		if (isset($_REQUEST['at'])) { 
			$sqlcampos.= " , id_estado =4";
		} else {
			if (isset($_REQUEST["id_estado"])) { $sqlcampos.= " , id_estado =".GetSQLValue($_REQUEST["id_estado"],"int"); } 
		}
	}

	if (isset($_REQUEST["fecha_cita"])) { $sqlcampos.= " , fecha_cita =".GetSQLValue($_REQUEST["fecha_cita"],"text"); } 
	if (isset($_REQUEST["hora_cita"])) { $sqlcampos.= " , hora_cita =".GetSQLValue($_REQUEST["hora_cita"],"int"); } 

	// if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
	if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
	if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
	if (isset($_REQUEST["cliente_email"])) { $sqlcampos.= " , cliente_email =".GetSQLValue($_REQUEST["cliente_email"],"text"); } 
	if (isset($_REQUEST["cliente_contacto"])) { $sqlcampos.= " , cliente_contacto =".GetSQLValue($_REQUEST["cliente_contacto"],"text"); } 
	if (isset($_REQUEST["cliente_contacto_identidad"])) { $sqlcampos.= " , cliente_contacto_identidad =".GetSQLValue($_REQUEST["cliente_contacto_identidad"],"text"); } 
	if (isset($_REQUEST["cliente_contacto_telefono"])) { $sqlcampos.= " , cliente_contacto_telefono =".GetSQLValue($_REQUEST["cliente_contacto_telefono"],"text"); } 
	
	if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
	if (isset($_REQUEST["placa"])) { $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa"],"text"); } 
	// if (isset($_REQUEST["chasis"])) { $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis"],"text"); } 
	if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
    if (isset($_REQUEST["empresa"])) { $sqlcampos.= " , empresa =".GetSQLValue($_REQUEST["empresa"],"text"); } 
    if (isset($_REQUEST["ciudad"])) { $sqlcampos.= " , ciudad =".GetSQLValue($_REQUEST["ciudad"],"text"); } 
    



	if ($nuevoreg==true){
        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW(), plataforma=1"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];

        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('cita',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
       
		$sql="insert into cita set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
    } else {
      //actualizar
	  
	  $sql="update cita set ".$sqlcampos." where id=".$cid." limit 1";
         $result = sql_update($sql);
         $cid=$elcodigo;
    }

 
  
	
	if ($result!=false){   

		$stud_arr[0]["pcode"] = 1;
    	$stud_arr[0]["pmsg"] ="Guardado";
    	$stud_arr[0]["pcid"] = $cid;
		$stud_arr[0]["patd"] = $atender;
	}

} else {
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] =$verror;
    $stud_arr[0]["pcid"] = 0;
}

	salida_json($stud_arr);
 	exit;

} // fin guardar datos


// borrar orden    ############################  
if ($accion=="b") {
	  
	
	if (!tiene_permiso(135)) {
		$stud_arr[0]["pcode"] = 0;
		$stud_arr[0]["pmsg"] ="No tiene permiso para borrar";
		salida_json($stud_arr);
		exit;
	}

	   $stud_arr[0]["pcode"] = 0;
	   $stud_arr[0]["pmsg"] ="ERROR";
   
	   $cid=0;
	   if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }
   
   
	   //Validar
	   $verror="";
	   if (es_nulo($cid)) {$verror="Error al Borrar [101]";}
	   
	   
	   
		if ($verror=="") {
   
   
		$result=sql_delete("delete from cita where id=$cid AND id_estado>=4 limit 1");
	
	 
	   
	   if ($result!=false){   
   
		   $stud_arr[0]["pcode"] = 1;
		   $stud_arr[0]["pmsg"] ="Borrado";
		   $stud_arr[0]["pcid"] = $cid;
	   }
   
   } else {
	   $stud_arr[0]["pcode"] = 0;
	   $stud_arr[0]["pmsg"] =$verror;
	   $stud_arr[0]["pcid"] = 0;
   }
   
	   salida_json($stud_arr);
		exit;
   
   } //fin borrar


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
if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= $_SESSION['usuario_id'];}
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "";}
if (isset($row["id_taller"])) {$id_taller= $row["id_taller"]; } else {$id_taller= "";}
if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["tipo"])) {$tipo= $row["tipo"]; } else {$tipo= "";}
if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["numero_alterno"])) {$numero_alterno= $row["numero_alterno"]; } else {$numero_alterno= "";}
if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; } else {$cliente_id= "";}
if (isset($row["cliente_email"])) {$cliente_email= $row["cliente_email"]; } else {$cliente_email= "";}
if (isset($row["cliente_contacto"])) {$cliente_contacto= $row["cliente_contacto"]; } else {$cliente_contacto= "";}
if (isset($row["cliente_contacto_identidad"])) {$cliente_contacto_identidad= $row["cliente_contacto_identidad"]; } else {$cliente_contacto_identidad= "";}
if (isset($row["cliente_contacto_telefono"])) {$cliente_contacto_telefono= $row["cliente_contacto_telefono"]; } else {$cliente_contacto_telefono= "";}
if (isset($row["fecha_cita"])) {$fecha_cita= $row["fecha_cita"]; } else {$fecha_cita= "";}
if (isset($row["hora_cita"])) {$hora_cita= $row["hora_cita"]; } else {$hora_cita= "";}
if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}
if (isset($row["chasis"])) {$chasis= $row["chasis"]; } else {$chasis= "";}
if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
if (isset($row["empresa"])) {$empresa= $row["empresa"]; } else {$empresa= "";}
if (isset($row["ciudad"])) {$ciudad= $row["ciudad"]; } else {$ciudad= "";}

if (isset($row["elvehiculo"])) {$elvehiculo=$row["elcodvehiculo"] ." ". $row["elvehiculo"]; } else {$elvehiculo= "";}
if (isset($row["elcliente"])) {$elcliente=$row["elcodcliente"] ." ". $row["elcliente"]; } else {$elcliente= "";}

$id_plantilla="";
$taller_externo="";
if (!es_nulo($id_taller)){
   $taller_externo=get_dato_sql('cita_taller','externo','WHERE id_taller='.$id_taller); 
}


$acclavar="Atender";
$mov="1";
// if (!es_nulo($domicilio_inicio and es_nulo($domicilio_final))) {
//     $acclavar="Completar" ;
//     $mov="2";
// }

// if (!es_nulo($domicilio_inicio and !es_nulo($domicilio_final))) {
//     $acclavar="" ;
//     $mov="";
// }

$disable_sec1='';
if (!es_nulo($id) and !tiene_permiso(138)) { 
	$disable_sec1=' disabled="disabled" ';
}

echo campo("t",'','hidden',0,'','');
echo campo("cid","cid",'hidden',$id,' ',' ');
echo campo("mov","mov",'hidden',$mov,'','');

// echo campo("id_usuario","Id Usuario",'number',$id_usuario,' ',' ');
// echo campo("hora","Hora",'date',$hora,' ',' ');
// echo campo("numero_alterno","Numero Alterno",'text',$numero_alterno,' ',' ');
// echo campo("chasis","Chasis",'text',$chasis,' ',' ');

if (!isset($_REQUEST['mm'])) {echo "<p>&nbsp;</p>"; }
?>

<input id="taller_externo" name="taller_externo" type="hidden" value="<?php echo $taller_externo; ?>" >

<div class="row mb-2"> 
            
            <div class="col-sm">       
                <?php echo campo("numero","Numero",'labelb',$numero,' ',' '.$disable_sec1);  ?>              
            </div>

            <div class="col-sm">       
                <?php echo campo("fecha","Fecha",'labelb',formato_fecha_de_mysql($fecha),' ',' '.$disable_sec1);  ?>              
            </div>

			<div class="col-sm">       
                 <?php 
                      echo  campo("id_estado","Estado",'select',valores_combobox_db('cita_estado',$id_estado,'nombre','','',''),' ',' '.$disable_sec1);
                 ?> 
                  
            </div>

      
</div>

<div class="row mb-2"> 
			<div class="col-sm">       
                <?php echo campo("id_tienda","Tienda",'select','',' ',' onchange="citamant_cargar_talleres()" '.$disable_sec1); ?>     						                                                                                                                           			
            </div>

			<div class="col-sm">				       
                <?php echo campo("id_taller","Taller",'select','',' ',' '.$disable_sec1);  ?>  			
            </div>

</div>


<div class="row mb-2"> 
            
            <div class="col-sm">       
                <?php  echo campo("fecha_cita","Fecha Cita",'date',$fecha_cita,' ',' '.$disable_sec1); ?>              
            </div>

			<div class="col-sm">       
                <?php echo campo("hora_cita","Hora Cita",'select',' ',' ',' '.$disable_sec1);  ?>              
            </div>

			<div class="col-sm">       
                <?php echo campo("tipo","Tipo",'select',valores_combobox_texto('<option value="">Seleccione...</option>	<option value="1">Preventivo</option><option value="2">Correctivo</option>',$tipo,),' ',' '.$disable_sec1);  ?>              
            </div>

			
</div>

<div class="row mb-2"> 
            
            <div class="col-md-12">       
                <?php 
				// if (es_nulo($id_producto)) {
					echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ','  required '.$disable_sec1,'get.php?a=3&t=1',$elvehiculo);
				// } else {
				// 	echo campo("Vehiculo","Vehiculo",'labelb',$codigo_alterno.' '.$nombre.' '.$placa,'',' '); 
				// }
				
				 ?>              
            </div>

              
</div>

<div class="row mb-2"> 
            
            <div class="col-sm">       
                <?php  echo campo("placa","Placa",'text',$placa,' ',' '.$disable_sec1);  ?>              
            </div>

			<div class="col-sm">       
                <?php echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ',' '.$disable_sec1);  ?>              
            </div>
</div>

<hr>
<div class="row mb-2">            
            <div class="col-md-12">       
                <?php 
					echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ','  required '.$disable_sec1,'get.php?a=2&t=1',$elcliente);
			
				 ?>              
            </div>            
</div>

<div class="row mb-2"> 
            
            <div class="col-sm">       
                <?php  echo campo("cliente_contacto","Cliente Contacto",'text',$cliente_contacto,' ',' '.$disable_sec1);  ?>              
            </div>

			<div class="col-sm">       
                <?php echo campo("cliente_contacto_identidad","No. Identidad",'text',$cliente_contacto_identidad,' ',' '.$disable_sec1);  ?>              
            </div>
</div>

<div class="row mb-2"> 
            
            <div class="col-sm">       
                <?php  echo campo("cliente_contacto_telefono","Telefono",'text',$cliente_contacto_telefono,' ',' '.$disable_sec1);  ?>              
            </div>

			<div class="col-sm">       
                <?php echo campo("cliente_email","Email",'text',$cliente_email,' ',' '.$disable_sec1);  ?>              
            </div>
</div>

<div class="row mb-2"> 
            
            <div class="col-sm">       
                <?php  echo campo("empresa","Nombre de Empresa",'text',$empresa,' ',' '.$disable_sec1);  ?>              
            </div>
			<div class="col-sm">       
                <?php  echo campo("ciudad","Ciudad de Procedencia",'text',$ciudad,' ',' '.$disable_sec1);  ?>              
            </div>
		
</div>
<hr>
<div class="row mb-2"> 

			<div class="col-sm">       
                <?php echo campo("observaciones","Reporte Por Detalles De Unidad",'textarea',$observaciones,' ',' rows="4"'.$disable_sec1);  ?>              
            </div>
</div>



<?php
  

	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <?php if (tiene_permiso(131) or tiene_permiso(138) or tiene_permiso(139) or tiene_permiso(135)) {
  
            ?>
		  <?php if (es_nulo($id) or tiene_permiso(138)) { ?> <div class="col-sm"><a href="#" onclick="procesar_cita('cita_mant.php?a=g','forma_wd',''); return false;" class="btn btn-secondary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div><?php  }?>
			
        <?php 
		 if (($id_estado>=1)) {
			if (!es_nulo($cid)) {
            ?>
		    <?php if ($id_estado==1) { if (tiene_permiso(139)) { ?><div class="col-sm"><a href="#" onclick="procesar_cita('cita_mant.php?a=g&at=1','forma_wd',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Atender'; ?></a></div><?php } } ?>
			<?php if ($id_estado<4) { if (tiene_permiso(135)) { ?><div class="col-sm"><a href="#" onclick="borrar_cita(); return false;" class="btn btn-danger btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Borrar'; ?></a></div> <?php } }?>
        <?php } }

        
			 } 
			 
			 if (isset($_REQUEST['mm'])) {
			 ?>		
        <div class="col-sm"><a href="#" onclick="$('#ModalWindow').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
		<?php } ?>
		</div>
	</div>

	</fieldset>
	</form>

   
		  
		 </div>


</div>


</div>


</div>
<script>

	

function citamant_cargar_sucursales(cod){
	var $sucursales = $('#id_tienda').empty();
	var seleccionado="";
	//$sucursales.append('<option value = "">Seleccione...</option>');
	for (i in d_tienda) {
		if (d_tienda[i][0]==cod) {seleccionado="selected";}
	       $sucursales.append('<option value = "' + d_tienda[i][0] + '" '+seleccionado+'>' + d_tienda[i][1] + '</option>');
	       seleccionado="";
	}
}

    function citamant_cargar_talleres(cod){
        var $talleres = $('#id_taller').empty();
		var seleccionado="";
			//	$talleres.append('<option value = "">Todos</option>');
				var sucursal_actual= $('#id_tienda').val();							    
				for (i in d_taller) {				    
					if (sucursal_actual==d_taller[i][2]) {
						if (d_taller[i][0]==cod) {seleccionado="selected";}
						$talleres.append('<option value = "' + d_taller[i][0] + '" '+seleccionado+'>' + d_taller[i][1] + '</option>');
						seleccionado="";
					}			
				    
				}
			
    }

	function citamant_cargar_horas(cod){
		//horas
		var $lahora = $('#hora_cita').empty();		
		var seleccionado="";	
		for (i in d_horas) {				    
			    //if (d_horas[i][2]==plantilla) {seleccionado="selected"; }
				$lahora.append('<option value = "' + d_horas[i][0] + '">' + d_horas[i][1] + '</option>');		
				seleccionado="";
		}
		$("#hora_cita").val(cod);

	}


    citamant_cargar_sucursales('<?php echo $id_tienda; ?>');
    citamant_cargar_talleres('<?php echo $id_taller; ?>');
	citamant_cargar_horas('<?php echo $hora_cita; ?>');

function borrar_cita(){

	
Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar la Cita?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
		procesar_cita('cita_mant.php?a=b','forma_wd','');

	  }
	});
	
}

function procesar_cita(url,forma,adicional){
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

					 $("#"+forma+' #cid').val(json[0].pcid);

					if (json[0].patd>0) {
						if (forma=='forma_wd') {$('#ModalWindow').modal('hide');}
						var pid=$('#id_producto').val();
						var datacli = $('#cliente_id').select2('data')
						
						var cv= $("#"+forma+' #id_producto').val();
						var el_veh= $('#id_producto').select2('data')[0];
						var tipo_veh=el_veh.tip_veh;
						var cli= $('#cliente_id').val();
						var taller = $('#taller_externo').val();
						
						if (cli!=null) {
  							var cnb=datacli[0].text;
						} else {
  						    var cnb="";
						}

						switch (tipo_veh) {
							case 'pickup':
								tipo_veh='PICK UP';
								break;
							case 'microbus':
								tipo_veh='BUS';
								break;						
						}
						if (tipo_veh==undefined) {
							tipo_veh='';
						}

						var addicionales="";
						if (taller==2){
							addicionales+='&cn='+encodeURI($('#cliente_contacto').val());
							addicionales+='&ci='+encodeURI($('#cliente_contacto_identidad').val());
							addicionales+='&ct='+encodeURI($('#cliente_contacto_telefono').val());
							addicionales+='&ce='+encodeURI($('#cliente_email').val());
							addicionales+='&km='+encodeURI($('#kilometraje').val());
							addicionales+='&cit='+encodeURI($('#cid').val());
							addicionales+='&cd='+encodeURI($('#ciudad').val());
							addicionales+='&ob='+encodeURI($('#observaciones').val());

							get_page('pagina','inspeccion_mant.php?ti=2&td=1&em=&tv='+tipo_veh+'&cv='+cv+'&cli='+cli+'&idant=&retorno='+addicionales,'Nueva Inspección') ;  
						}else{							
							addicionales+='&km='+encodeURI($('#kilometraje').val());							
							addicionales+='&ob='+encodeURI($('#observaciones').val());
							get_page('pagina','servicio_mant_nuevo.php?pid='+pid+'&ccl='+cli+'&cnb='+encodeURI(cnb)+addicionales,'Nueva Orden de Servicio') ; 
							

						}
					 } else {
						<?php if (isset($_REQUEST['mm'])) { ?>
						if (forma=='forma_wd') {
							
							procesar_tabla_datatable('tablaver','tabla','cita_ver.php?a=1','Citas Programadas');
							$('#ModalWindow').modal('hide');
						}
						<?php } ?>
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