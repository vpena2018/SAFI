<?php
require_once ('include/framework.php');
pagina_permiso(140);


 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	if (es_nulo($cid)) {
	 	pagina_permiso(141); //crear nueva orden
	} else {

		$result = sql_select("SELECT orden_traslado.* 
		,producto.codigo_alterno,producto.nombre,producto.placa
		,orden_traslado_estado.nombre AS elestado
		,l1.nombre AS motorista1
		,l2.usuario AS solicitante1
		,p1.nombre AS elproveedor
		,t1.nombre AS tiendasalida
		,t2.nombre AS tiendadestino
		,orden_traslado_tipo.nombre as id_tipo_traslado_lbl

		FROM orden_traslado
		LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
		LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
		LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
		LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
		LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
		LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
		LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)
		LEFT OUTER JOIN orden_traslado_tipo ON (orden_traslado.id_tipo_traslado=orden_traslado_tipo.id)

		WHERE orden_traslado.id=$cid 
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

    $cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$elcodigo= $cid;
    if (es_nulo($elcodigo)) {$nuevoreg=true;} else {$nuevoreg=false;}

    //Validar
	$verror="";
	
	if (isset($_REQUEST['tipo_destino'])) {
		if ($_REQUEST['tipo_destino']==1) {
			$verror.=validar("Destino a",$_REQUEST['id_tienda_destino'], "int", true);
		}
		if ($_REQUEST['tipo_destino']==2) {
			if (isset($_REQUEST['id_proveedor'])) {
				$valprov=$_REQUEST['id_proveedor'];
			} else {$valprov=0;}
			$verror.=validar("Proveedor a",$valprov, "int", true);
		}
	}
	
	
	if ($nuevoreg==false) {
		if (isset($_REQUEST['at'])){ //atender
			$verror.=validar("Kilometraje",$_REQUEST['kilometraje_salida'], "int", true);
			if (!isset($_REQUEST['combustible_salida'])){$verror.='Debe ingresar el Combustible<br>';}
		}

		if (isset($_REQUEST['cp'])) {//completar
			$verror.=validar("Kilometraje",$_REQUEST['kilometraje_entrada'], "int", true);
			if (!isset($_REQUEST['combustible_entrada'])){$verror.='Debe ingresar el Combustible<br>';}
		}

		
	} else {

		$verror.=validar("Razón del Traslado",$_REQUEST['id_tipo_traslado'], "int", true);

		$verror.=validar("Tienda Salida",$_REQUEST['id_tienda_salida'], "int", true);

		if (isset($_REQUEST['id_producto'])) {
			$verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
		} else {$verror.=validar("Vehiculo",' ', "int", true);}		
	


	}

	// if (tiene_permiso(143)) {
		if (  isset($_REQUEST['at'])){
			if (isset($_REQUEST['id_motorista'])){
				 $verror.=validar("Atendido por",$_REQUEST['id_motorista'], "int", true);
				} else {
					$verror.="El campo atendido por es obligatorio<br>";
				}
		}
	// }
    
	 if ($verror=="") {

    //Campos
	


    $mov=sanear_int($_REQUEST['mov']);


	
	$sqlcampos="";
	$sqlcampos.=" actualiza = NOW()";
    

	$mov_atender=0;


		if (isset($_REQUEST['at'])){
			$mov_asignar="Atender ";
			$sqlcampos.=", traslado_inicio = NOW()";
			$sqlcampos.=", id_estado = 2";
			$mov_atender=2;
		}

		if (isset($_REQUEST['cp'])) {
			$mov_asignar="Completar ";
			$sqlcampos.=", traslado_final = NOW()";
			$sqlcampos.=", id_estado = 3";
			$mov_atender=3;
		} 		
		
	

//	if (tiene_permiso(143) or isset($_REQUEST['at'])) {
		if (isset($_REQUEST["id_motorista"])) { $sqlcampos.= " , id_motorista =".GetSQLValue($_REQUEST["id_motorista"],"int"); } 
	
//	}

	if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
	if (isset($_REQUEST["id_tienda_salida"])) { $sqlcampos.= " , id_tienda_salida =".GetSQLValue($_REQUEST["id_tienda_salida"],"int"); } 
	if (isset($_REQUEST["id_tienda_destino"])) { $sqlcampos.= " , id_tienda_destino =".GetSQLValue($_REQUEST["id_tienda_destino"],"int"); } 
	
if (isset($_REQUEST["id_tipo_traslado"])) { $sqlcampos.= " , id_tipo_traslado =".GetSQLValue($_REQUEST["id_tipo_traslado"],"int"); } 
	
	
	if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
	if (isset($_REQUEST["observaciones2"])) { $sqlcampos.= " , observaciones2 =".GetSQLValue($_REQUEST["observaciones2"],"text"); }

	
	


	if (isset($_REQUEST["combustible_salida"])) { $sqlcampos.= " , combustible_salida =".GetSQLValue($_REQUEST["combustible_salida"],"text"); } 
	if (isset($_REQUEST["combustible_entrada"])) { $sqlcampos.= " , combustible_entrada =".GetSQLValue($_REQUEST["combustible_entrada"],"text"); } 
	if (isset($_REQUEST["kilometraje_salida"])) { $sqlcampos.= " , kilometraje_salida =".GetSQLValue($_REQUEST["kilometraje_salida"],"int"); } 
	if (isset($_REQUEST["kilometraje_entrada"])) { $sqlcampos.= " , kilometraje_entrada =".GetSQLValue($_REQUEST["kilometraje_entrada"],"int"); } 
	if (isset($_REQUEST["id_solicitante"])) { $sqlcampos.= " , id_solicitante =".GetSQLValue($_REQUEST["id_solicitante"],"int"); } 
	if (isset($_REQUEST["id_proveedor"])) { $sqlcampos.= " , id_proveedor =".GetSQLValue($_REQUEST["id_proveedor"],"int"); } 

	if ($nuevoreg==true){
        //Crear nuevo     
		if (isset($_REQUEST["tipo_destino"])) { $sqlcampos.= " , tipo_destino =".GetSQLValue($_REQUEST["tipo_destino"],"int"); }        
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('orden_traslado',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
       
		$sql="insert into orden_traslado set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
    } else {
      //actualizar
	  
	  $sql="update orden_traslado set ".$sqlcampos." where id=".$cid." limit 1";
         $result = sql_update($sql);
         $cid=$elcodigo;
    }

 
  
	
	if ($result!=false){   

		$stud_arr[0]["pcode"] = 1;
    	$stud_arr[0]["pmsg"] ="Guardado";
    	$stud_arr[0]["pcid"] = $cid;


		
		if ($mov_atender>0) {
					
			//******** API Rentworks *******/
			if ($mov_atender==2) { 
				require_once ('include/rentworks_api.php');
				$rw_salida=rw_crear_traslado($cid,"");
			}
			if ($mov_atender==3) { 
				require_once ('include/rentworks_api.php');
				$rw_salida=rw_cerrar_traslado($cid,"");
			}
			//******** API Rentworks fin. ******/
	
			//correo
			require_once ('correo_traslado.php');
			
		}


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
	   
	if (!tiene_permiso(145)) {
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
   
   
		$result=sql_delete("delete from orden_traslado where id=$cid limit 1");
	
	 
	   
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


if (isset($row["id"])) {$id= $row["id"]; $nuevoreg=false;} else {$id= ""; $nuevoreg=true;}
if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= $_SESSION['usuario_id'];}
if (isset($row["id_usuario_autoriza"])) {$id_usuario_autoriza= $row["id_usuario_autoriza"]; } else {$id_usuario_autoriza= "";}
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda=$_SESSION['tienda_id'] ;}
if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "1";}
if (isset($row["id_inspeccion"])) {$id_inspeccion= $row["id_inspeccion"]; } else {$id_inspeccion= "";}
if (isset($row["id_servicio"])) {$id_servicio= $row["id_servicio"]; } else {$id_servicio= "";}

if (isset($row["id_motorista"])) {$id_motorista= $row["id_motorista"]; } else {$id_motorista= ""; }
if (isset($row["motorista1"])) {$motorista1= $row["motorista1"]; } else {$motorista1= "...";}

if (isset($row["id_tienda_destino"])) {$id_tienda_destino= $row["id_tienda_destino"]; } else {$id_tienda_destino= "";}
if (isset($row["id_tienda_salida"])) {$id_tienda_salida= $row["id_tienda_salida"]; } else {$id_tienda_salida= "";}

if (isset($row["id_tipo_traslado"])) {$id_tipo_traslado= $row["id_tipo_traslado"]; $id_tipo_traslado_lbl=$row["id_tipo_traslado_lbl"];} else {$id_tipo_traslado= "1"; $id_tipo_traslado_lbl= "Non-Rev";}

if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
if (isset($row["observaciones2"])) {$observaciones2= $row["observaciones2"]; } else {$observaciones2= "";}

// if (!es_nulo($id) and es_nulo($id_motorista)) {
// 	$id_motorista=$_SESSION['usuario_id'];
// 	$motorista1= $_SESSION['usuario_nombre'];
// }

if (isset($row["codigo_alterno"])) {$codigo_alterno= $row["codigo_alterno"]; } else {$codigo_alterno= "";}
if (isset($row["nombre"])) {$nombre= $row["nombre"]; } else {$nombre= "";}
if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}

if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["autorizado"])) {$autorizado= $row["autorizado"]; } else {$autorizado= "";}
if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["elestado"])) {$elestado= $row["elestado"]; } else {$elestado= "Nueva Orden";}

if (isset($row["tiendasalida"])) {$tiendasalida= $row["tiendasalida"]; } else {$tiendasalida= "";}
if (isset($row["tiendadestino"])) {$tiendadestino= $row["tiendadestino"]; } else {$tiendadestino="";}


if (isset($row["id_solicitante"])) {$id_solicitante= $row["id_solicitante"]; } else {$id_solicitante= $_SESSION['usuario_id'];}
if (isset($row["solicitante1"])) {$solicitante1= $row["solicitante1"]; } else {$solicitante1= $_SESSION['usuario_nombre'];}
if (isset($row["id_proveedor"])) {$id_proveedor= $row["id_proveedor"]; } else {$id_proveedor= "";}
if (isset($row["elproveedor"])) {$elproveedor= $row["elproveedor"]; } else {$elproveedor= "";}
if (isset($row["combustible_salida"])) {$combustible_salida= $row["combustible_salida"]; } else {$combustible_salida= "";}
if (isset($row["combustible_entrada"])) {$combustible_entrada= $row["combustible_entrada"]; } else {$combustible_entrada= "";}
if (isset($row["kilometraje_salida"])) {$kilometraje_salida= $row["kilometraje_salida"]; } else {$kilometraje_salida= "";}
if (isset($row["kilometraje_entrada"])) {$kilometraje_entrada= $row["kilometraje_entrada"]; } else {$kilometraje_entrada= "";}

if (isset($row["tipo_destino"])) {$tipo_destino= $row["tipo_destino"]; } else {$tipo_destino= 1;}

$traslado_inicio= '';
$traslado_final= '';
if (isset($row["traslado_inicio"])) {if (!es_nulo($row["traslado_inicio"])) {$traslado_inicio= formato_fechahora_de_mysql($row["traslado_inicio"]) ; } }
if (isset($row["traslado_final"])) {if (!es_nulo($row["traslado_final"])) {$traslado_final= formato_fechahora_de_mysql($row["traslado_final"]) ; } }


$acclavar="Atender";
$mov="1";
if (!es_nulo($traslado_inicio and es_nulo($traslado_final))) {
    $acclavar="Completar" ;
    $mov="2";
}

if (!es_nulo($traslado_inicio and !es_nulo($traslado_final))) {
    $acclavar="" ;
    $mov="";
}

if ($tipo_destino==1) {$tipo_destino1_class="";$tipo_destino2_class=" oculto";}
if ($tipo_destino==2) {$tipo_destino1_class=" oculto";$tipo_destino2_class="";}

//echo '<h4>'.$acclavar.'</h4>';
echo campo("t",'','hidden',0,'','');
echo campo("cid","cid",'hidden',$id,' ',' ');
echo campo("mov","mov",'hidden',$mov,'','');
//echo campo("id_motorista","Usuario",'label',$nombremotorista ,' ',' ','');

if (!tiene_permiso(142)) {$disable_combsalida=' disabled="disabled"';} else {$disable_combsalida="";}
if (!tiene_permiso(144)) {$disable_combentrada=' disabled="disabled"';} else {$disable_combentrada="";}
if ($id_estado>1) {$disable_combsalida=' disabled="disabled"';}
if ($id_estado>2) {$disable_combentrada=' disabled="disabled"';}

if ($id_estado>1) {$mostrar_entrada=" ";} else {$mostrar_entrada=" oculto";}

$modificar_salida=$nuevoreg;


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
				if ($nuevoreg==true) {
					echo campo("id_tipo_traslado","Razón del Traslado",'select',valores_combobox_db('orden_traslado_tipo',$id_tipo_traslado,'nombre',' ','',''),' ','  required ','','');
				} else {
					echo campo("id_tipo_traslado_lbl","Razón del Traslado",'labelb',$id_tipo_traslado_lbl,'',' '); 
				}
				
				 ?>              
            </div>

              
</div>

<div class="row mb-2"> 
            
            <div class="col-md-12">       
                <?php 
				if ($modificar_salida) {
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
				if ($modificar_salida) {
					echo campo("id_tienda_salida","Salida de",'select',valores_combobox_db('tienda_agencia',$id_tienda_salida,'nombre',' ','',''),' ','  required ','',"");
				}  else {
					echo campo("ttienda","Salida de",'labelb',$tiendasalida,'',' '); 
				}
				
				
				 ?>              
            </div>

			<div class="col-md-6"> 
				<?php if ($nuevoreg==true) { ?>
					<div class="form-check-inline ">
						<input class="form-check-input" type="radio" name="tipo_destino" id="tipodestino1" value="1" onchange=" cambiartipo_destino(1);" checked>
						<label class="form-check-label" for="tipodestino1">Destino Tienda</label>
					</div> 
					<div class="form-check-inline ">
						<input class="form-check-input" type="radio" name="tipo_destino" id="tipodestino2" value="2" onchange=" cambiartipo_destino(2);">
						<label class="form-check-label" for="tipodestino2">Destino Proveedor</label>
					</div>  
				<?php } ?>
                <?php 


				echo '<div id="tipdestino1" class="'.$tipo_destino1_class.'">';
				if ($modificar_salida) {
					echo campo("id_tienda_destino","Destino a",'select',valores_combobox_db('tienda_agencia',$id_tienda_destino,'nombre',' ','','...'),' ','   ','',"");
				} else {
					echo campo("ttienda_destino","Destino a",'labelb',$tiendadestino,'',' '); 
				}			
				echo '</div>';
				
				echo '<div id="tipdestino2" class="'.$tipo_destino2_class.'">';
				if ($modificar_salida) {
					echo campo("id_proveedor","Proveedor",'select2ajax',$id_proveedor,'class="form-control" style="width: 100%" ','','get.php?a=4&t=1',$elproveedor);
				} else {
					echo campo("pproveedor","Proveedor",'labelb',$elproveedor,'',' '); 
				}				
				echo'</div>';
				 ?>              
            </div>

              
</div>


	<div class="row mb-2"> 
					

				<div class="col-md-6">       
					<?php 
					
					// if (!tiene_permiso(141)) {
						echo  campo('id_solicitante_lbl', 'Solicitado por','labelb',$solicitante1,' ','  ','');  
						echo  campo('id_solicitante', '','hidden',$id_solicitante,' ','  ','');         
					// } else {
					// 	echo  campo('id_solicitante', 'Solicitado por','select2',valores_combobox_db('usuario',$id_solicitante,'nombre',' where activo=1 ','',$solicitante1),' ','  ',''); // and grupo_id=3          
					// }
				
					?>              
				</div>

				<div class="col-md-6 ">       
					<?php 
				 

					if (!tiene_permiso(143)) {
						echo  campo('id_motorista_lbl', 'Atendido por','labelb',$motorista1,' ','  ','');  
						echo  campo('id_motorista', '','hidden',$id_motorista,' ','  ','');           
					} else {
						if ($id_estado==1) {
							echo  campo('id_motorista', 'Atendido por','select2',valores_combobox_db('usuario',$id_motorista,'nombre',' where activo=1 and (grupo_id=3 or grupo_id=20) ','',$motorista1),' ','  ','');           
						} else {
							echo  campo('id_motorista_lbl', 'Atendido por','labelb',$motorista1,' ','  ',''); 
							echo  campo('id_motorista', '','hidden',$id_motorista,' ','  ','');
						}						
					
					}
			 
					
					?>              
				</div>	
					
				
				
		
				
	</div>


<div class="row mb-2"> 

			<div class="col-md-6">       
                <?php 
				//if ($id_estado>1) {

					if ($modificar_salida) {
						echo campo("observaciones","Comentarios",'textarea',$observaciones,' ',' rows="2"  ');
					} else {
						echo campo("observaciones","Comentarios",'labelb',$observaciones,'',' '); 
					}
			//	}
				 ?>              
            </div>	
            
            <div class="col-md-6 <?php echo $mostrar_entrada; ?>">       
                <?php 
			 
				
					if ($id_estado==2) {
						echo campo("observaciones2","Comentarios",'textarea',$observaciones2,' ',' rows="2"  ');
					} else {
						echo campo("observaciones2","Comentarios",'labelb',$observaciones2,'',' '); 
					}
			 
				
				 ?>              
            </div>

              
</div>

<?php if ($nuevoreg==false) { ?>
	<div class="row mb-2"> 
				
				<div class="col-md-6">   
					<?php 
						if ($id_estado==1) {
							echo campo("kilometraje_salida","Kilometraje Salida",'number',$kilometraje_salida,' ',$disable_combsalida  .' ');
						} else {
							echo campo("kkilometraje_salida","Kilometraje Salida",'labelb',$kilometraje_salida,'',' '); 
						}
						
					?>              
				</div>

				<div class="col-md-6 <?php echo $mostrar_entrada; ?>">  
					<?php 
						if ($id_estado==2) {
							echo campo("kilometraje_entrada","Kilometraje Entrada",'number',$kilometraje_entrada,' ',$disable_combentrada  .' ');
						} else {
							echo campo("kilometraje_entrada","Kilometraje Entrada",'labelb',$kilometraje_entrada,'',' '); 
						}
						
					?>              
				</div>

				
	</div>


	<div class="row mb-2"> 
				
				<div class="col-md-6">   
					<span class="outside-label">Combustible Salida</span>
					<?php 					
						echo campo_combustible('combustible_salida',$combustible_salida,$disable_combsalida);       
					?>              
				</div>

				<div class="col-md-6 <?php echo $mostrar_entrada; ?>">  
				<span class="outside-label">Combustible Entrada</span>
					<?php 					
						echo campo_combustible('combustible_entrada',$combustible_entrada,$disable_combentrada);
					?>              
				</div>

				
	</div>

	<?php } ?>


<div class="row mb-2"> 
            
            <div class="col-md-6">       
                <?php 
				if ($nuevoreg==false) {
				echo campo("traslado_inicio","Inicio del Traslado",'labelb',$traslado_inicio ,' ',' ','');
				}
				?>              
            </div>
 			<div class="col-md-6 <?php echo $mostrar_entrada; ?>">       
                <?php 
				
				echo campo("traslado_final","Finalización del Traslado",'labelb',$traslado_final ,' ',' ','');
				
				 ?>              
            </div>
              
</div>


<?php
  

	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <?php if (tiene_permiso(140)) {
            if (($id_estado<3)) {
            ?>
		    <div class="col-sm"><a href="#" onclick="procesar_traslado('traslado_mant.php?a=g','forma_wd',''); return false;" class="btn btn-secondary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div>
			
        <?php 
		 if (($id_estado==1)) {
			if (!es_nulo($cid)) {
            ?>
		    <?php if (tiene_permiso(142)) { ?><div class="col-sm"><a href="#" onclick="procesar_traslado('traslado_mant.php?a=g&at=1','forma_wd',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Atender'; ?></a></div><?php } ?>
			<?php if (tiene_permiso(145)) { ?><div class="col-sm"><a href="#" onclick="borrar_traslado(); return false;" class="btn btn-danger btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Borrar'; ?></a></div> <?php } ?>
        <?php } }
		 if (($id_estado==2)) {
            ?>
		    <?php if (tiene_permiso(144)) { ?><div class="col-sm"><a href="#" onclick="procesar_traslado('traslado_mant.php?a=g&cp=1','forma_wd',''); return false;" class="btn btn-success btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Completar'; ?></a></div><?php } ?>
			
        <?php }
             }
			 } ?>	
		<div class="col-sm"><a href="traslado_imprimir.php?pdfcod=<?php echo $id; ?>" target="_blank"  class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir</a></div>
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
function borrar_traslado(){

	
Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar la Orden de traslado?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
		procesar_traslado('traslado_mant.php?a=b','forma_wd','');

	  }
	});
	
}

function procesar_traslado(url,forma,adicional){
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
                    
					procesar_tabla_datatable('tablaver','tabla_traslado','traslado_ver.php?a=1','Traslado de Vehiculos');
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


function cambiartipo_destino(valor){
						if (valor==1) {
							$("#tipdestino1").show();
							$("#tipdestino2").hide();
							$('#id_proveedor').val(null).trigger('change');
							
						} else {
							$("#tipdestino2").show();
							$("#tipdestino1").hide();
							$("#id_tienda_destino").val('');
						}

					}
</script>