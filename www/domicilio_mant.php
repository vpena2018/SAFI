<?php
require_once ('include/framework.php');
pagina_permiso(123);


 
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	if (es_nulo($cid)) {
	 	pagina_permiso(124); //crear nueva orden
	} else {

		$result = sql_select("SELECT orden_domicilio.* 
		,producto.codigo_alterno,producto.nombre,producto.placa
		,orden_domicilio_estado.nombre AS elestado
		,l1.nombre AS motorista1
		,entidad.nombre AS cliente
		,l2.usuario AS solicitante
		FROM orden_domicilio
		LEFT OUTER JOIN producto ON (orden_domicilio.id_producto=producto.id)
		LEFT OUTER JOIN orden_domicilio_estado ON (orden_domicilio.id_estado=orden_domicilio_estado.id)
		LEFT OUTER JOIN usuario l1 ON (orden_domicilio.id_motorista=l1.id)
		LEFT OUTER JOIN entidad ON (orden_domicilio.cliente_id=entidad.id)
		LEFT OUTER JOIN usuario l2 ON (orden_domicilio.id_usuario=l2.id)

		WHERE orden_domicilio.id=$cid 
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
	
	
	if ($nuevoreg==false) {
		if (tiene_permiso(126)) {
			if (  isset($_REQUEST['at'])){
				if (isset($_REQUEST['id_motorista'])){ $verror.=validar("Atendido por",$_REQUEST['id_motorista'], "int", true);}
			else {
				$verror.="el campo atendido por es obligatorio";
			}
			}
		}
	} else {

		if (isset($_REQUEST['id_producto'])) {
			$verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
		} else {$verror.=validar("Vehiculo",' ', "int", true);}

		if (isset($_REQUEST['cliente_id'])) {
			$verror.=validar("Cliente",$_REQUEST['cliente_id'], "int", true);
		} else {$verror.=validar("Cliente",' ', "int", true);}

	}
    
	 if ($verror=="") {

    //Campos
	


    $mov=sanear_int($_REQUEST['mov']);


	
	$sqlcampos="";
	$sqlcampos.=" actualiza = NOW()";
    

	


		if (isset($_REQUEST['at'])){
			$mov_asignar="Atender ";
			$sqlcampos.=", domicilio_inicio = NOW()";
			$sqlcampos.=", id_estado = 2";
		}

		if (isset($_REQUEST['cp'])) {
			$mov_asignar="Completar ";
			$sqlcampos.=", domicilio_final = NOW()";
			$sqlcampos.=", id_estado = 3";
		} 		
		
	

	if (tiene_permiso(126)) {
		if (isset($_REQUEST["id_motorista"])) { $sqlcampos.= " , id_motorista =".GetSQLValue($_REQUEST["id_motorista"],"int"); } 
	
	}

	if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
	if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
	if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
	if (isset($_REQUEST["observaciones2"])) { $sqlcampos.= " , observaciones2 =".GetSQLValue($_REQUEST["observaciones2"],"text"); }

	if ($nuevoreg==true){
        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('orden_domicilio',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
       
		$sql="insert into orden_domicilio set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
    } else {
      //actualizar
	  
	  $sql="update orden_domicilio set ".$sqlcampos." where id=".$cid." limit 1";
         $result = sql_update($sql);
         $cid=$elcodigo;
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


// borrar orden    ############################  
if ($accion=="b") {
	   
	if (!tiene_permiso(128)) {
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
   
   
		$result=sql_delete("delete from orden_domicilio where id=$cid limit 1");
	
	 
	   
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

if (isset($row["id_motorista"])) {$id_motorista= $row["id_motorista"]; } else {$id_motorista= ""; }
if (isset($row["motorista1"])) {$motorista1= $row["motorista1"]; } else {$motorista1= "...";}

if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; } else {$cliente_id= "";}
if (isset($row["cliente"])) {$cliente= $row["cliente"]; } else {$cliente= "";}
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

$domicilio_inicio= '';
$domicilio_final= '';
if (isset($row["domicilio_inicio"])) {if (!es_nulo($row["domicilio_inicio"])) {$domicilio_inicio= formato_fechahora_de_mysql($row["domicilio_inicio"]) ; } }
if (isset($row["domicilio_final"])) {if (!es_nulo($row["domicilio_final"])) {$domicilio_final= formato_fechahora_de_mysql($row["domicilio_final"]) ; } }

if (isset($row["solicitante"])) {$solicitante= $row["solicitante"] ; } else {$solicitante='';}


$acclavar="Atender";
$mov="1";
if (!es_nulo($domicilio_inicio and es_nulo($domicilio_final))) {
    $acclavar="Completar" ;
    $mov="2";
}

if (!es_nulo($domicilio_inicio and !es_nulo($domicilio_final))) {
    $acclavar="" ;
    $mov="";
}




//echo '<h4>'.$acclavar.'</h4>';
echo campo("t",'','hidden',0,'','');
echo campo("cid","cid",'hidden',$id,' ',' ');
echo campo("mov","mov",'hidden',$mov,'','');
//echo campo("id_motorista","Usuario",'label',$nombremotorista ,' ',' ','');

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
            
            <div class="col-md-12">       
                <?php 
				if (es_nulo($cliente_id)) {
					echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ','  required ','get.php?a=2&t=1',$cliente);
				} else {
					echo campo("Cliente","Cliente",'labelb',$cliente,'',' '); 
				}
				
				 ?>              
            </div>

              
</div>
<div class="row mb-2"> 
				 

				
				
            
            <div class="col-md-6">       
                <?php 
				if (!tiene_permiso(126)) {
					echo  campo('id_motorista_lbl', 'Atendido por','labelb',$motorista1,' ','  ','');           
				} else {
					echo  campo('id_motorista', 'Atendido por','select2',valores_combobox_db('usuario',$id_motorista,'nombre',' where activo=1 and grupo_id=3 or perfil_adicional=3','',$motorista1),' ','  ','');           
				}
				
				?>              
            </div>
 			<div class="col-md-6">       
                   <?php
				   if(!es_nulo($solicitante)) {
				   		echo  campo('id_motorista_lbl', 'Solicitado por','labelb',$solicitante,' ','  ','');
					}
				   ?>  
            </div>
              
</div>

<div class="row mb-2"> 
            
            <div class="col-md-12">       
                <?php 
				if ($id_estado<=1) {
					echo campo("observaciones","Comentarios",'textarea',$observaciones,' ',' rows="2"  ');
				} else {
					echo campo("observaciones","Comentarios",'labelb',$observaciones,'',' '); 
				}
				
				 ?>              
            </div>

              
</div>

<div class="row mb-2"> 
            
            <div class="col-md-12">       
                <?php 
				if ($id_estado>1) {
				
					if ($id_estado==2) {
						echo campo("observaciones2","Comentarios de Entrega",'textarea',$observaciones2,' ',' rows="2"  ');
					} else {
						echo campo("observaciones2","Comentarios de Entrega",'labelb',$observaciones2,'',' '); 
					}
				}
				
				 ?>              
            </div>

              
</div>

<div class="row mb-2"> 
            
            <div class="col-md-6">       
                <?php 
				
				echo campo("domicilio_inicio","Inicio de la Entrega",'labelb',$domicilio_inicio ,' ',' ','');
				?>              
            </div>
 			<div class="col-md-6">       
                <?php 
				echo campo("domicilio_final","Finalización de la Entrega",'labelb',$domicilio_final ,' ',' ','');
				 ?>              
            </div>
              
</div>


<?php
  

	?>
	</div>
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <?php if (tiene_permiso(123)) {
            if (($id_estado<3)) {
            ?>
		    <div class="col-sm"><a href="#" onclick="procesar_domicilio('domicilio_mant.php?a=g','forma_wd',''); return false;" class="btn btn-secondary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Guardar'; ?></a></div>
			
        <?php 
		 if (($id_estado==1)) {
			if (!es_nulo($cid)) {
            ?>
		    <?php if (tiene_permiso(125)) { ?><div class="col-sm"><a href="#" onclick="procesar_domicilio('domicilio_mant.php?a=g&at=1','forma_wd',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Atender'; ?></a></div><?php } ?>
			<?php if (tiene_permiso(128)) { ?><div class="col-sm"><a href="#" onclick="borrar_domicilio(); return false;" class="btn btn-danger btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Borrar'; ?></a></div> <?php } ?>
        <?php } }
		 if (($id_estado==2)) {
            ?>
		    <?php if (tiene_permiso(127)) { ?><div class="col-sm"><a href="#" onclick="procesar_domicilio('domicilio_mant.php?a=g&cp=1','forma_wd',''); return false;" class="btn btn-success btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo 'Completar'; ?></a></div><?php } ?>
			
        <?php }
             }
			 } ?>	
			 <div class="col-sm"><a href="domicilio_imprimir.php?pdfcod=<?php echo $id; ?>" target="_blank"  class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir</a></div>	
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
function borrar_domicilio(){

	
Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar la Orden de domicilio?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
		procesar_domicilio('domicilio_mant.php?a=b','forma_wd','');

	  }
	});
	
}

function procesar_domicilio(url,forma,adicional){
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
                    
					procesar_tabla_datatable('tablaver','tabla_domicilio','domicilio_ver.php?a=1','Entregas a Domicilio');
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