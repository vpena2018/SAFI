<?php
require_once ('include/framework.php');
pagina_permiso(80);


 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	if (es_nulo($cid)) {
	 	pagina_permiso(106); //crear nueva orden
	} else {

		$result = sql_select("SELECT orden_lavado.* 
		,producto.codigo_alterno,producto.nombre,producto.placa
		,orden_lavado_estado.nombre AS elestado
		,l1.nombre AS lavador1
		,l2.nombre  AS lavador2

		FROM orden_lavado
		LEFT OUTER JOIN producto ON (orden_lavado.id_producto=producto.id)
		LEFT OUTER JOIN orden_lavado_estado ON (orden_lavado.id_estado=orden_lavado_estado.id)
		LEFT OUTER JOIN usuario l1 ON (orden_lavado.id_lavador=l1.id)
		LEFT OUTER JOIN usuario l2 ON (orden_lavado.id_lavador2=l2.id)

		WHERE orden_lavado.id=$cid 
		limit 1");

		if ($result!=false){
			if ($result -> num_rows > 0) { 
				$row = $result -> fetch_assoc(); 
			}
		}

	}

} // fin leer datos
$hab_tareas3=' readonly';
// guardar Datos    ############################  
if ($accion=="g") {
 //sleep(3);
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

    $cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$elcodigo= $cid;
    if (es_nulo($elcodigo)) {$nuevoreg=true;} else {$nuevoreg=false;}

    //Validar
	$verror="";
	$autorizando=false;
	$completando=false;
	$atendido=false;
	$validaVehiculo="";		
	if ($nuevoreg==false) {
		if (tiene_permiso(108)) {
			if (  isset($_REQUEST['at'])){
				if (isset($_REQUEST['id_lavador'])){ $verror.=validar("Atendido por",$_REQUEST['id_lavador'], "int", true);}
			else {
				$verror.="el campo atendido por, es obligatorio";
				}
			}
		}
		if (isset($_REQUEST['ad'])) {	
			$ad=$_REQUEST['ad'];		
			if ($ad==1){
				if (isset($_REQUEST['observaciones_reproceso'])){
				   $verror.=validar("Observaciones Reproceso",$_REQUEST['observaciones_reproceso'], "text", true);  
				}
			 }else{
				if (isset($_REQUEST['observaciones_inspeccion'])){ 
				   $verror.=validar("Observaciones Inspeccion",$_REQUEST['observaciones_inspeccion'], "text", true);  
				} 
			}
		 }	
	} else {		
		if (!tiene_permiso(172)){			
			$validaVehiculo=get_dato_sql("orden_lavado","count(*)"," where id_estado<=3 and date(fecha)=date(now()) and id_tienda=".$_SESSION['tienda_id']." and id_producto=".$_REQUEST['id_producto']);
			if ($validaVehiculo>=2){
				$verror.='Vehiculo sobrepasa el limite de lavados al dia';
			}
		} 		
	    else{
			$hab_tareas3=' ';
		}
		if (isset($_REQUEST['id_producto'])) {
			$verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
		} else {$verror.=validar("Vehiculo",' ', "int", true);}

	}
    

	if ($verror=="") {

    //Campos
	
	if ($nuevoreg==true) {
		if (isset($_REQUEST["lavado_lavado"])) { $lavado_lavado=1;   } else  {  $lavado_lavado=0;  }
		if (isset($_REQUEST["lavado_aspirado"])) { $lavado_aspirado=1;   } else  {  $lavado_aspirado=0;  }
		if (isset($_REQUEST["lavado_shampoo"])) { $lavado_shampoo=1;   } else  {  $lavado_shampoo=0;  }
		if (isset($_REQUEST["lavado_chasis"])) { $lavado_chasis=1;   } else  {  $lavado_chasis=0;  }
		if (isset($_REQUEST["lavado_detallado"])) { $lavado_detallado=1;   } else  {  $lavado_detallado=0;  }
		if (isset($_REQUEST["lavado_shampuseado_seco"])) { $lavado_shampuseado_seco=1;   } else  {  $lavado_shampuseado_seco=0;  }
		if (isset($_REQUEST["lavado_express"])) { $lavado_express=1;   } else  {  $lavado_express=0;  }
	}
    
	
	$mov=sanear_int($_REQUEST['mov']);


	
	$sqlcampos="";
	$sqlcampos.=" actualiza = NOW()";
    
	if (tiene_permiso(109) and $nuevoreg==true) {
		$sqlcampos.=", lavado_lavado = ".$lavado_lavado;
		$sqlcampos.=", lavado_aspirado = ".$lavado_aspirado;
		$sqlcampos.=", lavado_shampoo = ".$lavado_shampoo;
		$sqlcampos.=", lavado_chasis = ".$lavado_chasis;
		$sqlcampos.=", lavado_detallado = ".$lavado_detallado;
		$sqlcampos.=", lavado_shampuseado_seco = ".$lavado_shampuseado_seco;
		$sqlcampos.=", lavado_express = ".$lavado_express;
	}
	
		if (isset($_REQUEST['at'])){
			$mov_asignar="Atender ";
			$sqlcampos.=", lavado_inicio = NOW()";
			$sqlcampos.=", id_estado = 2";
			//ojo $autorizando=true; 
			$atendido=true;
		}

		if (isset($_REQUEST['cp'])) {
			$mov_asignar="Completar ";
			$sqlcampos.=", lavado_final = NOW()";
			$sqlcampos.=", id_estado = 3";
			$completando=true;
		} 		
		
		if (isset($_REQUEST['ad'])) {
			$ad=intval(isset($_REQUEST['ad']));		
			$sqlcampos.=", fecha_auditado = NOW()";
			$sqlcampos.=", id_usuario_auditado =".$_SESSION["usuario_id"]; 
		    $sqlcampos.= " , observaciones_reproceso =".GetSQLValue($_REQUEST["observaciones_reproceso"],"text");  
			$sqlcampos.= " , observaciones_inspeccion =".GetSQLValue($_REQUEST["observaciones_inspeccion"],"text");  	
		} 
	

	if (tiene_permiso(108)) {
		if (isset($_REQUEST["id_lavador"])) { $sqlcampos.= " , id_lavador =".GetSQLValue($_REQUEST["id_lavador"],"int"); } 
		if (isset($_REQUEST["id_lavador2"])) { $sqlcampos.= " , id_lavador2 =".GetSQLValue($_REQUEST["id_lavador2"],"int"); } 
	}

	if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
	
	

	if ($nuevoreg==true){
        //Crear nuevo   
		$sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST['observaciones'],"text");         
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('orden_lavado',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into orden_lavado set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id
		$autorizando=true; 
    } else {
      //actualizar
	  $sql="update orden_lavado set ".$sqlcampos." where id=".$cid." limit 1";
      $result = sql_update($sql);
      $cid=$elcodigo;
    }

 
  
	
	if ($result!=false){   

		$stud_arr[0]["pcode"] = 1;
    	$stud_arr[0]["pmsg"] ="Guardado";
    	$stud_arr[0]["pcid"] = $cid;

		
		//******** API Rentworks *******/
		/*Desactivado temporalmente
        if ($atendido==true) { 
            require_once ('include/rentworks_api.php');
            $rw_salida=rw_crear_orden(2,$cid,"");
        }
        if ($completando==true) { 
            require_once ('include/rentworks_api.php');
            $rw_salida=rw_cerrar_orden(2,$cid,"");
        }
		*/
        //******** API Rentworks fin. ******/
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
	   
	if (!tiene_permiso(111)) {
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
   
   
		$result=sql_delete("delete from orden_lavado where id=$cid limit 1");
	
	 
	   
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
if (isset($row["id_usuario_autoriza"])) {$id_usuario_autoriza= $row["id_usuario_autoriza"]; } else {$id_usuario_autoriza= "";}
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda=$_SESSION['tienda_id'] ;}
if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "1";}
if (isset($row["id_inspeccion"])) {$id_inspeccion= $row["id_inspeccion"]; } else {$id_inspeccion= "";}
if (isset($row["id_servicio"])) {$id_servicio= $row["id_servicio"]; } else {$id_servicio= "";}

if (isset($row["id_lavador"])) {$id_lavador= $row["id_lavador"]; } else {$id_lavador= ""; }
if (isset($row["id_lavador2"])) {$id_lavador2= $row["id_lavador2"]; } else {$id_lavador2= "";}
if (isset($row["lavador1"])) {$lavador1= $row["lavador1"]; } else {$lavador1= "...";}
if (isset($row["lavador2"])) {$lavador2= $row["lavador2"]; } else {$lavador2= "...";}

// if (!es_nulo($id) and es_nulo($id_lavador)) {
// 	$id_lavador=$_SESSION['usuario_id'];
// 	$lavador1= $_SESSION['usuario_nombre'];
// }

if (isset($row["codigo_alterno"])) {$codigo_alterno= $row["codigo_alterno"]; } else {$codigo_alterno= "";}
if (isset($row["nombre"])) {$nombre= $row["nombre"]; } else {$nombre= "";}
if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}

if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["autorizado"])) {$autorizado= $row["autorizado"]; } else {$autorizado= "";}
if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["elestado"])) {$elestado= $row["elestado"]; } else {$elestado= "Nueva Orden";}

if (isset($row["fecha_auditado"])) {$fecha_auditado= $row["fecha_auditado"]; } else {$fecha_auditado = date('Y-m-d');}
if (isset($row["id_usuario_auditado"])) {$id_usuario_auditado= $row["id_usuario_auditado"]; } else {$id_usuario_auditado="0";}
if (isset($row["observaciones_reproceso"])) {$observaciones_reproceso= $row["observaciones_reproceso"]; } else {$observaciones_reproceso="";}
if (isset($row["observaciones_inspeccion"])) {$observaciones_inspeccion= $row["observaciones_inspeccion"]; } else {$observaciones_inspeccion="";}
if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones="";}

$motrar_boton=false;
if (!es_nulo($id_usuario_auditado)){
    $motrar_boton=true;     	
}

$lavado_inicio= '';
$lavado_final= '';
if (isset($row["lavado_inicio"])) {if (!es_nulo($row["lavado_inicio"])) {$lavado_inicio= formato_fechahora_de_mysql($row["lavado_inicio"]) ; } }
if (isset($row["lavado_final"])) {if (!es_nulo($row["lavado_final"])) {$lavado_final= formato_fechahora_de_mysql($row["lavado_final"]) ; } }

$lavado_lavado= '0';
$lavado_aspirado= '0';
$lavado_shampoo= '0';
$lavado_chasis= '0';
$lavado_detallado= '0';
$lavado_shampuseado_seco= '0';
$lavado_express= '0';
if (isset($row["lavado_lavado"])) {if (!es_nulo($row["lavado_lavado"])) {$lavado_lavado= $row["lavado_lavado"] ; } }
if (isset($row["lavado_aspirado"])) {if (!es_nulo($row["lavado_aspirado"])) {$lavado_aspirado= $row["lavado_aspirado"] ; } }
if (isset($row["lavado_shampoo"])) {if (!es_nulo($row["lavado_shampoo"])) {$lavado_shampoo= $row["lavado_shampoo"] ; } }
if (isset($row["lavado_chasis"])) {if (!es_nulo($row["lavado_chasis"])) {$lavado_chasis= $row["lavado_chasis"] ; } }

if (isset($row["lavado_detallado"])) {if (!es_nulo($row["lavado_detallado"])) {$lavado_detallado= $row["lavado_detallado"] ; } }
if (isset($row["lavado_shampuseado_seco"])) {if (!es_nulo($row["lavado_shampuseado_seco"])) {$lavado_shampuseado_seco= $row["lavado_shampuseado_seco"] ; } }

if (isset($row["lavado_express"])) {if (!es_nulo($row["lavado_express"])) {$lavado_express= $row["lavado_express"] ; } }


$lavado_lavado_chk="";
if ($lavado_lavado==1) { $lavado_lavado_chk=" checked";}

$lavado_aspirado_chk="";
if ($lavado_aspirado==1) { $lavado_aspirado_chk=" checked";}

$lavado_shampoo_chk="";
if ($lavado_shampoo==1) { $lavado_shampoo_chk=" checked";}

$lavado_chasis_chk="";
if ($lavado_chasis==1) { $lavado_chasis_chk=" checked";} 

$lavado_detallado_chk="";
if ($lavado_detallado==1) { $lavado_detallado_chk=" checked";} 

$lavado_shampuseado_seco_chk="";
if ($lavado_shampuseado_seco==1) { $lavado_shampuseado_seco_chk=" checked";} 

$lavado_express_chk="";
if ($lavado_express==1) { $lavado_express_chk=" checked";} 

$acclavar="Atender";
$mov="1";
if (!es_nulo($lavado_inicio and es_nulo($lavado_final))) {
    $acclavar="Completar" ;
    $mov="2";
}

if (!es_nulo($lavado_inicio and !es_nulo($lavado_final))) {
    $acclavar="" ;
    $mov="";
}




//echo '<h4>'.$acclavar.'</h4>';
echo campo("t",'','hidden',0,'','');
echo campo("cid","cid",'hidden',$id,' ',' ');
echo campo("mov","mov",'hidden',$mov,'','');
//echo campo("id_lavador","Usuario",'label',$nombrelavador ,' ',' ','');

?>

<div class="row mb-2"> 
            
            <div class="col-md-3">       
                <?php echo campo("numero","Numero",'labelb',$numero,' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                <?php echo campo("fecha","Fecha",'labelb',formato_fecha_de_mysql($fecha),' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                <?php echo campo("id_tienda","Tienda",'labelb',get_dato_sql('tienda','nombre',' where id='.$id_tienda),' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                 <?php //echo campo("estado","Estado",'labelb',$elestado,'',' ','');  
                         echo campo("estado","Estado",'labelb',$elestado,'',' '); 
                 ?> 
                  
            </div>
</div>

<div class="row mb-2"> 
            
            <div class="col-md-12">       
                <?php 
				if (es_nulo($id_producto)) {
					echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ','  required ','get.php?a=3&t=1',$codigo_alterno.' '.$nombre.' '.$placa);
				} else {
					echo campo("Vehiculo","Vehiculo",'labelb',$codigo_alterno.' '.$nombre.' '.$placa,'',' '); 
				}
				
				 ?>              
            </div>

              
</div>
<div class="row mb-2"> 
				 

				
				
            
            <div class="col-md-6">       
                <?php 
				if (!tiene_permiso(108)) {
					echo  campo('id_lavador_lbl', 'Atendido por','labelb',$lavador1,' ','  ','');           
				} else {
					echo  campo('id_lavador', 'Atendido por','select2',valores_combobox_db('usuario',$id_lavador,'nombre',' where activo=1 and grupo_id=6 ','',$lavador1),' ','  ','');           
				}
				
				?>              
            </div>
 			<div class="col-md-6">       
                <?php 
				if (!tiene_permiso(108)) {
					echo  campo('id_lavador2_lbl', 'Atendido por','labelb',$lavador2,' ','  ','');           
				} else {
					echo  campo('id_lavador2', 'Atendido por','select2',valores_combobox_db('usuario',$id_lavador2,'nombre',' where activo=1 and grupo_id=6 ','',$lavador2),' ','  ','');           
				}
				
				 ?>              
            </div>
              
</div>

<div class="row mb-2"> 
            
            <div class="col-md-6">       
                <?php 
				
				echo campo("lavado_inicio","Inicio del Lavado",'labelb',$lavado_inicio ,' ',' ','');
				?>              
            </div>
 			<div class="col-md-6">       
                <?php 
				echo campo("lavado_final","Finalización del Lavado",'labelb',$lavado_final ,' ',' ','');
				 ?>              
            </div>
              
</div>
<hr>
<div class="row mb-2"> 
			<?php 
				$hab_tareas='';
				if (!tiene_permiso(109)) {
					$hab_tareas=' disabled';
				}

				//deshabilitar despues de creadas
				if (!es_nulo($id)) {
					$hab_tareas=' disabled';
				}

			?>
            
            <div class="col-md-3">       
                <?php 
				echo campo("lavado_lavado","Lavado",'checkbox',1 ,' ',$lavado_lavado_chk. $hab_tareas,'');				
				?>              
            </div>

			<div class="col-md-3">       
                <?php 
				echo campo("lavado_aspirado","Aspirado",'checkbox',1 ,' ',$lavado_aspirado_chk. $hab_tareas,'');				
				?>              
            </div>

			<div class="col-md-3">       
                <?php 
				echo campo("lavado_shampoo","Champuseados",'checkbox',1 ,' ',$lavado_shampoo_chk .$hab_tareas,'');				
				?>              
            </div>

			<div class="col-md-3">       
                <?php 
				echo campo("lavado_chasis","Chasis",'checkbox',1 ,' ',$lavado_chasis_chk .$hab_tareas,'');				
				?>              
            </div>


			<div class="col-md-3">       
                <?php 
				echo campo("lavado_detallado","Detallado",'checkbox',1 ,' ',$lavado_detallado_chk .$hab_tareas,'');				
				?>              
            </div>

			<div class="col-md-3">       
                <?php 
				echo campo("lavado_shampuseado_seco","Champuseado Seco",'checkbox',1 ,' ',$lavado_shampuseado_seco_chk .$hab_tareas,'');				
				?>              
            </div>
			
			<div class="col-md-3">       
                <?php 
				echo campo("lavado_express","Express",'checkbox',1 ,' ',$lavado_express_chk .$hab_tareas,'');				
				?>              
            </div>	
              
</div>

	<div class="row mb"> 
		<div class="col-md">
		    <?php echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',' rows="2" '.$hab_tareas3); ?>			
		</div>
	</div>	
 
 <?php if (($id_estado==3) && tiene_permiso(163)) {
	 $hab_tareas1='';
	 if (!es_nulo($observaciones_reproceso)) {
		$hab_tareas1=' readonly';
	 }
	 $hab_tareas2='';
	 if (!es_nulo($observaciones_inspeccion)) {
		$hab_tareas2=' readonly';
	 }
	 ?>
	<div class="row mb"> 
		<div class="col-md">
			<?php echo campo("observaciones_reproceso","Observaciones Reproceso",'textarea',$observaciones_reproceso,' ',' rows="3" '.$hab_tareas1); ?>
		</div>
	</div>
	<div class="row mb"> 
		<div class="col-md">
			<?php echo campo("observaciones_inspeccion","Observaciones Inspeccion",'textarea',$observaciones_inspeccion,' ',' rows="3" '.$hab_tareas2); ?>
		</div>
	</div>
 <?php } ?>
<?php
	?>
 </div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <?php if (tiene_permiso(80)) {
              if (($id_estado<3)) { ?>
		            <div class="col-sm"><a href="#" onclick="procesar_lavado('lavado_mant.php?a=g','forma_wd',''); return false;" class="btn btn-secondary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div>
			
					<?php if (($id_estado==1)) {
						if (!es_nulo($cid)) { ?> 
						<?php if (tiene_permiso(107)) {
							?><div class="col-sm"><a href="#" onclick="procesar_lavado('lavado_mant.php?a=g&at=1','forma_wd',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Atender'; ?></a></div><?php } ?>
						<?php if (tiene_permiso(111)) {
							?><div class="col-sm"><a href="#" onclick="borrar_lavado(); return false;" class="btn btn-danger btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Borrar'; ?></a></div> <?php } ?>
					<?php } }
					if (($id_estado==2)) {?>
					   <?php if (tiene_permiso(110)) { ?><div class="col-sm"><a href="#" onclick="procesar_lavado('lavado_mant.php?a=g&cp=1','forma_wd',''); return false;" class="btn btn-success btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Completar'; ?></a></div><?php } ?>
					<?php }
              }
			  if (($id_estado==3)) {?>
					<?php if (tiene_permiso(163) and es_nulo($observaciones_reproceso)) { ?><div class="col-sm"><a href="#" onclick="procesar_lavado('lavado_mant.php?a=g&ad=1','forma_wd',''); return false;" class="btn btn-info btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Reproceso'; ?></a></div><?php } ?>
					<?php if (tiene_permiso(163) and es_nulo($observaciones_inspeccion)) { ?><div class="col-sm"><a href="#" onclick="procesar_lavado('lavado_mant.php?a=g&ad=2','forma_wd',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Inspeccion'; ?></a></div><?php } ?>
			  <?php }
			 } ?>		 		
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

function borrar_lavado(){
Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar la Orden de Lavado?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
		procesar_lavado('lavado_mant.php?a=b','forma_wd','');

	  }
	});
	
}

function procesar_lavado(url,forma,adicional){
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
      
					procesar_tabla_datatable('tablaver','tabla','lavado_ver.php?a=1','Lavado de Vehiculos');
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