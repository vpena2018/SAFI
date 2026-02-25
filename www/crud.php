<?php

if (isset($_REQUEST['tbl'])) { $numtabla = $_REQUEST['tbl']; } else	exit ;
require_once ('include/framework.php');

require_once ('crud_tablas.php');

if ($tabla=="" ){exit;}

$nuevo_registro=false;
$verror = "";
$accion="b";

if (isset($_REQUEST['a'])) {$accion=$_REQUEST['a'];}

if ($accion=="r" or $accion=="u" or $accion=="d" ) {

if (!isset($_REQUEST['rid'])) {exit;}	

	$conn = new mysqli(db_ip, db_user, db_pw, db_name);
	if (mysqli_connect_errno()) {	echo '<div class="alert alert-info">'."Error al Conectar a la Base de Datos [DB:101]".'</div>'; exit; } 
	$conn->set_charset("utf8");
	$rid = $conn->real_escape_string($_REQUEST['rid']);
	
		
}

//### create ### 
if ($accion=="c") {

$nuevo_registro=true;
$rid =0;
$accion="r";		

}




//##############
//### read #### 
//##############

if ($accion=="r") {
	

?>
 
<script type="text/javascript">

function guardarforma() {
				$("#botones *").attr("disabled", "disabled");
				$("#form :input").attr('readonly', true);
				$('#salida').hide();
				$('#cargando').show();
				var myTable = '';
				
				var url = "crud.php?tbl=<?php echo $numtabla; ?>&a=u&rid=<?php echo $rid; ?>";
				$.getJSON(url, $("#form").serialize(), function(json) {
					
					i = 1;
					if (json.length > 0) {
						if (json[0].pcode == 0) {
							$("#salida").html( json[0].pmsg );
						}
						if (json[0].pcode == 1) {
							$("#salida").html(json[0].pmsg);
							<?php if ($nuevo_registro==true) {echo "$('#btnsend').hide();";}  ?>
							
						}
					} else {
						$("#salida").html('<div class="alert alert-danger"><?php echo "Se produjo un error en comunicacion JSON:101";  ?></div>');
					}

				}).fail(function() {
					$("#salida").html('<div class="alert alert-danger"><?php echo "Se produjo un error en comunicacion JSON:102";  ?></div>');
				}).done(function() {
					$('#salida').show();
					$('#cargando').hide();
					$("#form :input").attr('readonly', false);
					$("#botones *").removeAttr("disabled");
				//	mostrar_ventana();
				});

			}
			
			
			

			function confirmar_borrar() {

				Swal.fire({
				title: 'Borrar',
				text:  'Los registros borrados no pueden ser recuperados, desea borrarlo?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText:  'Borrar',
				cancelButtonText:  'Cancelar'
				}).then((result) => {
				if (result.value) {
					get_page('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=d&rid=<?php echo $rid; ?>') ; 
				}
				})

			};

			


</script>



<div class="card-body">	
<div class="row" >
	<div class="col-md-12">
	
		
 <form id="form" class="form-horizontal maxancho600">

    <?php
   
   if ($nuevo_registro==true) {
   	
	$i=0;
   		 foreach ($columnas as $campo) {
			$campotipo="text";
			$fecha='';
			if ($tabla=="guardias" and $campo=="fecha_creacion"){
			   $campotipo="datetime-local";
			   $fecha=$now_fechahoraT;			   
			}
   		 	
			$campoclase='form-control';
   		 	if ($campo=='id') {$campoclase=''; $campotipo="label";}
			
			$valor="";
			if (isset($columnas_combo)){
				$key = array_search($campo, $columnas_combo);
				if ($key===false) {
				    if ( ($tabla=="usuario" or $tabla=="cita_taller" or $tabla=="guardias") and (   $campo=="activo" or $campo=="interno" or $campo=="externo")) {
				     
                           if ($tabla=="usuario" and $campo=="activo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,'1'),$campoclase);}
                           						   
						   if ($tabla=="cita_taller" and $campo=="interno") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,'0'),$campoclase);}

						   if ($tabla=="cita_taller" and $campo=="activo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,'1'),$campoclase);}
                          						   
						   if ($tabla=="cita_taller" and $campo=="externo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,'1'),$campoclase);}

						   if ($tabla=="guardias" and $campo=="activo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,'1'),$campoclase);}
              
                    } ELSE {
				    if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
					   echo campo($campo,$columnas_etiquetas[$i],$campotipo,'' ,$campoclase);
					}
				} else {
					$campoid="id";
					if ($tabla=="clientes_vehiculos"){
					   if ($campo=="cliente_id"){
				   	      $where= 'get.php?a=2&t=1';
					   }  
					   if ($campo=="id_producto"){
						  $where= 'get.php?a=3&t=1';
  	  			       }  
					   echo campo($campo,$columnas_etiquetas[$i],'select2ajax',$campo,' ',' required',$where,'');                     						  
					   /*echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ',' required','get.php?a=2&t=1',$cliente_nombre);*/
					}else {
				       echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_db($columnas_combo2[$key],'',$columnas_combo3[$key],'','','Select',$campoid),$campoclase);
					}
				} 
				
				
			} else {
			if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
			    echo campo($campo,$columnas_etiquetas[$i],$campotipo,$fecha ,$campoclase);
			}
			 $i++;
		}
	
	
	
   } else { 
   	$sql = "SELECT ".implode(',', $columnas)." FROM " .$tabla . " where id=".$rid;
	$result = $conn -> query($sql);
	if ($result->num_rows > 0) {
      	while ($row = mysqli_fetch_array($result)) {
			

		// $finfo = $result->fetch_fields();
// 
    	// foreach ($finfo as $val) {
//     		
	        // printf("Name:     %s\n", $val->name);
	        // printf("Table:    %s\n", $val->table);
	        // printf("max. Len: %d\n", $val->max_length);
	        // printf("Flags:    %d\n", $val->flags);
	        // printf("Type:     %d\n\n", $val->type);
   		 // }
   		 $i=0;
   		 foreach ($columnas as $campo) {
   		 	$campotipo="text";
			$campoclase='form-control';
   		 	if ($campo=='id') {$campoclase=''; $campotipo="label";}
			
			$valor="";
			if (isset($columnas_combo)){
				$key = array_search($campo, $columnas_combo);
				if ($key===false) {
					if ( ($tabla=="usuario" or $tabla=="cita_taller" or $tabla=="guardias")and ( $campo=="clave"   or $campo=="activo" or $campo=="interno" or $campo=="externo")) {															
					      // if ( $campo=="clave") { echo	campo("modboton", "Modificar Contrase&ntilde;a",'boton','',"onClick = \"efectuar_proceso(7,'".'Modificar Contrase&ntilde;a'."',3,$rid,''); return false; \"");}
						  if ($tabla=="usuario" and $campo=="clave") { echo	campo($campo,"Modificar Contrase&ntilde;a","password", "",$campoclase);}
                            
                           if ($tabla=="usuario" and $campo=="activo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,$row[$campo]),$campoclase);}
						  
						   if ($tabla=="cita_taller" and $campo=="interno") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,$row[$campo]),$campoclase);}

						   if ($tabla=="cita_taller" and $campo=="activo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,$row[$campo]),$campoclase);}

                           if ($tabla=="cita_taller" and $campo=="externo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,$row[$campo]),$campoclase);}

						   if ($tabla=="guardias" and $campo=="activo") {echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_texto(app_combo_si_no,$row[$campo]),$campoclase);}
						   
					} else {
					    if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
					    echo campo($campo,$columnas_etiquetas[$i],$campotipo, $row[$campo],$campoclase); //htmlentities($row[$campo], ENT_QUOTES, 'UTF-8')
					}
				} else {
					$campoid="id";	
					$producto_etiqueta="";
					if (($tabla=="clientes_vehiculos") and ($campo=="cliente_id" or  $campo=="id_producto")){
						if ($tabla=="clientes_vehiculos" and $campo=="cliente_id"){
						   $producto_etiqueta=get_dato_sql("entidad","concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,''))"," WHERE id=".$row[$campo]); 
						   echo campo($campo,$columnas_etiquetas[$i],'select2ajax',$campo,' ',' ','get.php?a=2&t=1',$producto_etiqueta);                     						 
						}  
				        if ($tabla=="clientes_vehiculos" and $campo=="id_producto"){
						   $producto_etiqueta=get_dato_sql("producto","concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,''))"," WHERE id=".$row[$campo]); 
					       echo campo($campo,$columnas_etiquetas[$i],'select2ajax',$campo,' ',' ','get.php?a=3&t=1',$producto_etiqueta);                     						  
						} 				
						/*echo campo($campo,$columnas_etiquetas[$i],'select2ajax',$campo,' ',' ',$where,$producto_etiqueta);      */               						  
						/*echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ',' required','get.php?a=2&t=1',$cliente_nombre);*/
					 }else {					
      			        echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_db($columnas_combo2[$key],$row[$campo],$columnas_combo3[$key],'','','..',$campoid),$campoclase);
					 }					  
				}
				
				
			} else {
			
				if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
			       echo campo($campo,$columnas_etiquetas[$i],$campotipo, $row[$campo],$campoclase); //htmlentities($row[$campo], ENT_QUOTES, 'UTF-8')
			
			}		
			 
			 $i++;
		}
		
	}
	
	$conn -> close();
	}  else {echo '<div class="alert alert-info">'."No se encontraron Registros".'</div>'; exit; }
	} 
	?>



 	
<div class="row">
	<div class="col-md-12">
				<!--HERE WRITE THE RESPONSE DATA -->
				<div id="cargando" style="display: none;" align="center" > <img src="img/load.gif"/></div>
			<div id ="salida"  align="center">	</div> 
			
			<!---END-->
	</div>
</div>
 <div class="row">
 <div class="col-md-12">
	    <div class="form-actions botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top" id="botones">  
	    	
	    	 
            <button id="btnsend" onClick = "guardarforma()" class="btn btn-sm btn-primary mr-3" type="button"><i class="fa fa-check"></i> Guardar</button>   
          <button id="btncerrar" onClick = "get_page('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=b&rid=<?php echo $rid; ?>') ; return false;" class="btn btn-sm btn-outline-secondary ml-3" type="button"> Cerrar</button>
        
          
<?php if ($nuevo_registro==false) { ?>
	<?php if ($numtabla<>28) { ?>
    <br>
    <br>
    <br>
<button id="btnborrar" onClick = "confirmar_borrar()" class="btn btn-sm btn-danger " type="button"><i class="fa fa-trash-alt"></i> Borrar</button>  

<?php } 
	}?>
  </div>
</div>



       
  </div>	
 </form>
       
	</div>
</div>
</div>
<?php	
	
	}








//##############
//### update ### 
//##############

if ($accion=="u") {

if ($rid==0) {$nuevo_registro=true;} else {$rid=GetSQLValue(($rid),"int");}

if ($tabla=="usuario") {
	$clave_tmp = "";
	if (isset($_REQUEST["clave"])) {
		$clave_tmp = trim($_REQUEST["clave"]);
	}
	if ($nuevo_registro==true || $clave_tmp!="") {
		$verror = validar_politica_password($clave_tmp);
		if ($verror!="") {
			$stud_arr[0]["pcode"] = 0;
			$stud_arr[0]["pmsg"] ='<div class="alert alert-danger">'.$verror.'</div>';
			echo salida_json($stud_arr);
			exit;
		}
	}
}
	
	 	$i=0;
		$sqlcampos="";
   		foreach ($columnas as $campo) {
			
   		 	if ($campo!='id') {
   		 			
				if ($tabla=="usuario" and $campo=="clave") {
					if ($nuevo_registro==true) {
						if ($sqlcampos!="") {$sqlcampos.=" , ";}						
						$sqlcampos.= $campo."=".GetSQLValue(password_hash(trim($_REQUEST[$campo]), PASSWORD_BCRYPT)	,$columnas_tipo[$i]);
					} else {
						if (trim($_REQUEST[$campo]<>"")) {
							if ($sqlcampos!="") {$sqlcampos.=" , ";}						
							$sqlcampos.= $campo."=".GetSQLValue(password_hash(trim($_REQUEST[$campo]), PASSWORD_BCRYPT)	,$columnas_tipo[$i]);
						}
					}	
				} else {
				if ($sqlcampos!="") {$sqlcampos.=" , ";}
   		 		$sqlcampos.= $campo."=".GetSQLValue(($_REQUEST[$campo]),$columnas_tipo[$i]);
				}
			}
			
			$i++;
		}
		
		

	if ($nuevo_registro==true) {
		$sql="insert into ". $tabla . " set " . $sqlcampos;
	} else {
		$sql="update ". $tabla . " set " .$sqlcampos ." where id=".$rid;
	 
	}
		

 if ($conn->query($sql) === TRUE) {
	$stud_arr[0]["pcode"] = 1;
	$stud_arr[0]["pmsg"] ='<div class="alert alert-success">'."El registro ha sido guardado".'</div>';    }


	 
    else {
    $stud_arr[0]["pcode"] = 0;
	$stud_arr[0]["pmsg"] ='<div class="alert alert-danger">'."Se produjo un error al guardar el registro".' <br>'.$conn->error.'</div>';
    }

    $conn->close();
	

echo salida_json($stud_arr);
exit;
	
}




//##############
//### delete ###
//##############

if ($accion=="d") {
	$sql="delete from ".$tabla . " where id=".$rid; ;
	 if ($conn->query($sql) === TRUE) {
      $salida='<div class="alert alert-info">'."El registro ha sido borrado".'</div>';
    }
    else {
      $salida='<div class="alert alert-danger">'."No se pudo eliminar el registro".'</div>';
    }

    $conn->close();
	
	?>
	<div class="card-body">
	<div class="row" >
	

		
	<div class="row">
		<div class="col-md-12">
		<?php echo $salida ?>
		</div>
	</div>

	<div class="row">
	<div class="col-md-12">
		<button id="btncerrar" onClick = "get_page('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=b&rid=<?php echo $rid; ?>') ; return false;" class="btn btn-sm btn-outline-secondary  " type="button"> Regresar</button>
	</div>
	</div>
	
	</div>
</div>
	<?php
}








//##############
//### Browse ###
//############## 
if ($accion=="b") {
	?>
<script type="text/javascript" charset="utf-8">

			
	
 var oTable;

$(document).ready(function() {


     
 /* Add a click handler to the rows - this could be used as a callback */
    $("#tabla tbody").click(function(event) {
        $(oTable.fnSettings().aoData).each(function (){
            $(this.nTr).removeClass('row_selected');
        });
        $(event.target.parentNode).addClass('row_selected');
    });
	
 

	var oTable = $('#tabla').dataTable( {
	//		"bAutoWidth": true,
			"bFilter": true,
			"bPaginate": true,
		//	"bSort": false,
        	//"bInfo": false,
        	"bStateSave": false,
			"processing": true,
            "serverSide": true,
        	"responsive": false,   
			"sAjaxSource": "crud_trans.php?tbl=<?php echo $numtabla; ?>",
          

  			"dom": '<"clear"> frtiplB',

  			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {	
				        $('td:eq(0)', nRow).html('<button type="button" onclick="get_page(\'pagina\',\'crud.php?tbl=<?php echo $numtabla; ?>&a=r&rid='+aData[0]+'\') ; return false; " class="btn btn-sm btn-secondary mr-2"><i class="fa fa-folder-open"></i> </button> <?php if ($tabla=="usuario_grupo") { ?><button type="button" onclick="asignar_permisos_perfil('+aData[0]+'); return false;" class="btn btn-sm btn-warning text-secondary"><i class="fa fa-lock-open"></i> Permisos</button>  <?php } ?> 	 ' );
						
				  				    },

    		buttons: ['excelHtml5', 'csvHtml5', 'print' ],
 
       		"bScrollCollapse": true,
	
			"bJQueryUI": false,
			
	         "language": { "url": "plugins/datatables/spanish.lang" }			

    });
	 

} );

$('#pagina-botones').html('<a href="#" onclick="get_page(\'pagina\',\'crud.php?tbl=<?php echo $numtabla; ?>&a=c&rid=0\') ; return false; " class="btn btn-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus"></i> <?php echo 'Nuevo'; ?></a>');

function asignar_permisos_perfil(valor){  
	modalwindow('Asignar permisos','mnt_permisos.php?a=v&cid='+valor );
}

		</script>

<div class="card-body">
	<div id="dynamic" class="table-responsive ">
	<!-- class="display nowrap" -->
	<table  class="table table-striped table-hover table-sm nowrap"  style="width:100%" id="tabla" width="100%" cellspacing="0">
		<thead class="thead-dark">
			<tr>
				<th style="max-width:50px" > </th>
				<?php
				foreach ($columnas_etiquetas as $item) {
				if ( $item <>"Clave" and $item <>"Password" ){ echo "<th >".$item."</th>";}
				}
				?>
		
			</tr>

		</thead>
		
		<tbody>

			<tr>
				<td colspan="5" class="dataTables_empty"><?php echo "Cargando datos";  ?></td>
			</tr>
		</tbody>
		

	</table>
	</div>


</div>
	
	
<?php	
}
?>
