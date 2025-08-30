<?php
require_once ('include/framework.php');

pagina_permiso(178);

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}
$disable_sec1=' ';    
$disable_sec2=' ';    

// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT ventas.*    
    ,estado1.nombre AS elestado1
    ,estado2.nombre AS elestado2
    ,estado3.nombre AS elestado3    
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
    ,tienda.nombre AS latienda
        FROM ventas
        LEFT OUTER JOIN tienda ON (ventas.id_tienda=tienda.id)        
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)        
        LEFT OUTER JOIN ventas_estado estado1 ON (ventas.id_estado_pintura=estado1.id)
        LEFT OUTER JOIN ventas_estado estado2 ON (ventas.id_estado_interior=estado2.id)
        LEFT OUTER JOIN ventas_estado estado3 ON (ventas.id_estado_mecanica=estado3.id)
    where ventas.id=$cid limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
			$row = $result -> fetch_assoc(); 
		}
	}

} // fin leer datos

// borrar     ############################  
if ($accion=="del") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Error";
    $stud_arr[0]["pcid"] = 0;
    
if (!tiene_permiso(168)) {
        $stud_arr[0]["pmsg"] ="No tiene privilegios para Borrar";
    } else {
        $cid=0;
        if (isset($_REQUEST['id'])) { $cid = intval($_REQUEST["id"]); }
        $result = sql_select("SELECT id_estado
            FROM ventas
        where id=$cid limit 1");

        if ($result!=false){
            if ($result -> num_rows > 0) { 
                $row = $result -> fetch_assoc(); 
                if ($row['id_estado']==99) {
                    sql_delete("DELETE FROM ventas where tipo_ventas_reparacion=1 and id=$cid limit 1");
                    $stud_arr[0]["pcode"] = 1;
                    $stud_arr[0]["pmsg"] ="Anulada";
                } else {
                    $stud_arr[0]["pmsg"] ="No puede Borrar porque, la orden ya ha sido completada";
                }
            }
        }

    }


	salida_json($stud_arr);
 	exit;

}

// guardar Datos    ############################  
if ($accion=="g") {
 //sleep(3);
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

    //Validar
	$verror="";
    $cid=intval($_REQUEST["id"]);

    $verror.=validar("Sucursal",$_REQUEST['id_tienda'], "int", true);
    $verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
    $verror.=validar("Kilomatraje",$_REQUEST['kilometraje'], "int", true);
    $verror.=validar("Observaciones",$_REQUEST['observaciones_reparacion'], "text", true);
       
    if (es_nulo($cid)){
        $id_producto=intval($_REQUEST['id_producto']);
        $vehiculo=get_dato_sql("ventas","count(*)"," where id_producto=".$id_producto);
        if (!es_nulo($vehiculo) && es_nulo($cid)){ $verror.='Vehiculo ya esta registrado'; }   
    }  
       
    if ($verror=="") {
        //Campos
        $sqlcampos="";


        $nuevoregistro=false;
        $cid= intval($_REQUEST["id"]);
        if (es_nulo($cid)) {
            $nuevoregistro=true;
        }     

        if (isset($_REQUEST["id_producto"])) { $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
        if (isset($_REQUEST["id_tienda"])) { $sqlcampos.= " , id_tienda =".GetSQLValue($_REQUEST["id_tienda"],"int"); }    
        if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
        if (isset($_REQUEST["id_estado_pintura"])) { $sqlcampos.= " , id_estado_pintura =".GetSQLValue($_REQUEST["id_estado_pintura"],"int"); } 
        if (isset($_REQUEST["id_estado_interior"])) { $sqlcampos.= " , id_estado_interior =".GetSQLValue($_REQUEST["id_estado_interior"],"int"); } 
        if (isset($_REQUEST["id_estado_mecanica"])) { $sqlcampos.= " , id_estado_mecanica =".GetSQLValue($_REQUEST["id_estado_mecanica"],"int"); } 
        if (isset($_REQUEST["observaciones_reparacion"])) { $sqlcampos.= " , observaciones_reparacion =".GetSQLValue($_REQUEST["observaciones_reparacion"],"text"); } 
        if (isset($_REQUEST["fecha_asignacion"])) { $sqlcampos.= " , fecha_asignacion =".GetSQLValue($_REQUEST["fecha_asignacion"],"date"); }      
        if (isset($_REQUEST["fecha_promesa"])) { $sqlcampos.= " , fecha_promesa =".GetSQLValue($_REQUEST["fecha_promesa"],"date"); }                            
        if (isset($_REQUEST["foto"])) { $sqlcampos.= " , foto =".GetSQLValue($_REQUEST["foto"],"text"); } 
         
        $estadocompletar="";
        if (isset($_REQUEST['est'])) { $estadocompletar = trim($_REQUEST["est"]); }
        if (!es_nulo($estadocompletar) && $estadocompletar=='cmp'){
             $sqlcampos.= ", id_estado=0";
             $sqlcampos.= ", tipo_ventas_reparacion=2";
             $sqlcampos.= ", reproceso='' ";  
             $sqlcampos.= ", fecha_reparacion_completada=now() ";    		  	 
        }

        if ($nuevoregistro==false) {    
            //si modifica se guarda el registo del cambio
            $id_tienda=intval(get_dato_sql("ventas","id_tienda"," where id=".$cid));
            if ($id_tienda!=intval($_REQUEST['id_tienda'])){   
               $id_tienda_name=get_dato_sql("tienda","nombre"," where id=".$_REQUEST['id_tienda']);
               sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
               VALUES ( $cid,  ".$_SESSION['usuario_id'].",99,'Modificacion de Tienda', NOW(), '$id_tienda_name')");
            }
            $kilometraje=intval(get_dato_sql("ventas","kilometraje"," where id=".$cid));
            if ($kilometraje!=intval($_REQUEST['kilometraje'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",99,'Modificacion de Kilometraje', NOW(), ".$_REQUEST['kilometraje'].")");
            }

             $id_pintura=intval(get_dato_sql("ventas","id_estado_pintura"," where id=".$cid));
             if ($id_pintura!=intval($_REQUEST['id_estado_pintura'])){   
                 $id_pintura_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado_pintura']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado_pintura'].",'Modificacion de Estado de Pintura', NOW(),'$id_pintura_name')");
             }

             $id_interior=intval(get_dato_sql("ventas","id_estado_interior"," where id=".$cid));
             if ($id_interior!=intval($_REQUEST['id_estado_interior'])){   
                 $id_interior_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado_interior']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado_interior'].",'Modificacion de Estado de Interior', NOW(),'$id_interior_name')");
             }            

             $id_mecanica=intval(get_dato_sql("ventas","id_estado_mecanica"," where id=".$cid));
             if ($id_mecanica!=intval($_REQUEST['id_estado_mecanica'])){   
                $id_mecanica_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado_mecanica']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado_mecanica'].",'Modificacion de Estado de Mecanica', NOW(), '$id_mecanica_name')");
             }

             $observaciones=trim(get_dato_sql("ventas","observaciones_reparacion"," where id=".$cid));
             if ($observaciones!=trim($_REQUEST['observaciones_reparacion'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",99,'Modificacion de Observaciones', NOW(),'".$_REQUEST['observaciones_reparacion']."')"); 
             }

             $fecha_promesa=trim(get_dato_sql("ventas","fecha_promesa"," where id=".$cid));
             if ($fecha_promesa!=trim($_REQUEST['fecha_promesa'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",99,'Modificacion de Fecha de Promesa', NOW(),'".$_REQUEST['fecha_promesa']."')"); 
             }
             
            $sql="update ventas set ".$sqlcampos." where id=".$cid." limit 1";
            $result = sql_update($sql);
        } else {
            //Crear nuevo                       
            $sqlcampos.=" ,id_usuario=".$_SESSION['usuario_id'] ;      
            $sqlcampos.=" ,id_estado=99";
            $sqlcampos.=" ,tipo_ventas_reparacion=1";
            $sqlcampos.=" ,numero=".GetSQLValue(get_dato_sql('ventas',"IFNULL((max(numero)+1),1)"," "),"int"); 
            
            $sql="insert into ventas set fecha=NOW(), hora=now(),".$sqlcampos." ";        
            
            $result = sql_insert($sql);
            $cid=$result; //last insert id 

            sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
            VALUES ( $cid,  ".$_SESSION['usuario_id'].",99,'Nuevo registro de vehiculo', NOW(),'Nuevo')");

            sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
            VALUES ( $cid,  ".$_SESSION['usuario_id'].",99,'Nuevo registro de vehiculo', NOW(),'".$_REQUEST['observaciones_reparacion']."')");
        }

        
        if ($result!=false){
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ="Guardado";
            $stud_arr[0]["pcid"] = $cid;               
        }
        
        //Correo Completar
        if ($estadocompletar=='cmp'){
            require_once ('correo_reparacion.php');
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

<div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      <li class="nav-item">
        <a class="nav-link " id="insp_tabhistorial" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_historial');"   role="tab"  >Historial</a>
      </li> 
    </ul>   
 </div>

<div class="maxancho800 mx-auto">

<div class="tab-content" id="nav-tabContent">
<!-- DETALLE  -->
<div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >


<div class="row">
<div class="col">
   	<div class="form-group">	   
	<form id="forma_ventas" name="forma_ventas">
	<fieldset id="fs_forma">		 
<?php 

    if (isset($row["elestado1"])) {$elestado1=$row["elestado1"];} else {$elestado1="";}
    if (isset($row["elestado2"])) {$elestado2=$row["elestado2"];} else {$elestado2="";}
    if (isset($row["elestado3"])) {$elestado3=$row["elestado3"];} else {$elestado3="";}
    if (isset($row["codvehiculo"])) {$producto_etiqueta=$row["codvehiculo"]. ' '.$row["vehiculo"];   }else {$producto_etiqueta="";}
    if (isset($row["latienda"])) {$latienda=$row["latienda"];} else {$latienda="";}
    if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
    if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
    if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= "";}
    if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
    if (isset($row["id_estado_pintura"])) {$id_estado_pintura= $row["id_estado_pintura"]; } else {$id_estado_pintura= "";}
    if (isset($row["id_estado_interior"])) {$id_estado_interior= $row["id_estado_interior"]; } else {$id_estado_interior="";}
    if (isset($row["id_estado_mecanica"])) {$id_estado_mecanica= $row["id_estado_mecanica"]; } else {$id_estado_mecanica= "";}
    if (isset($row["fecha"])) {$fecha=$row["fecha"]; } else {$fecha= "";}
    if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
    if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
    if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
    if (isset($row["id_inspeccion"])) {$id_inspeccion=$row["id_inspeccion"]; } else {$id_inspeccion= "";}
    if (isset($row["id_estado"])) {$id_estado=$row["id_estado"]; } else {$id_estado= "";}
    if (isset($row["fecha_asignacion"])) {$fecha_asignacion= $row["fecha_asignacion"]; } else {$fecha_asignacion= "";}
    if (isset($row["fecha_promesa"])) {$fecha_promesa= $row["fecha_promesa"]; } else {$fecha_promesa= "";}
    if (isset($row["reproceso"])) {$reproceso=$row["reproceso"]; } else {$reproceso="";}
    if (isset($row["foto"])) {$foto=$row["foto"]; } else {$foto="";}
    
    $observaciones_reparacion= "";
    if ($id_estado=='' || $id_estado==99){
       $disable_sec1=' ';  
       $disable_sec2=' ';  
    }else{
       $disable_sec1=' disabled="disabled" ';  
       $disable_sec2=' disabled="disabled" ';  
    }

    if ( $id_estado_pintura==32 && $id_estado_interior==32 && $id_estado_mecanica==32 && $id_estado==99){
         $completar="cmp";
         $NombreBotton='Completar';
    }else{
         if ($id_estado==99 || $id_estado==''){
            $completar="";
            $NombreBotton='Guardar';
         }
    }

    echo campo("id",("Codigo"),'hidden',$id,' ','');

?>


<div class="row">
    <div class="col-md">
        <?php echo campo("hora",("Fecha / Hora"),'label',formato_fechahora_de_mysql($hora),' ',' ');   ?>
    </div>
    <div class="col-md">
        <?php echo campo("numero","Numero",'label',$numero,' ',' '); ?>        
    </div>    
    <div class="col-md">
        <?php echo campo("fecha_asignacion","Fecha de Asignacion",'date',$fecha_asignacion,' ',' required '.$disable_sec1); ?>
    </div>
    <div class="col-md">
        <?php echo campo("fecha_promesa","Fecha de Promesa",'date',$fecha_promesa,' ',' required '.$disable_sec1); ?>
    </div>
    <div class="col-md">
       <?php echo campo("id_inspeccion","Numero",'hidden',$id_inspeccion,' ',' '); ?>          
    </div>

</div>

<div class="row">
    <div class="col-md-4">                
         <?php if (es_nulo($id_estado) || $id_estado==99) {            
             echo campo("id_tienda","Sucursal",'select2',valores_combobox_db("tienda",$id_tienda,"nombre"," ",'','...'),' ',' required '.$disable_sec1,''); 
         }else{
             echo campo("id_tienda","sucursal",'hidden',$id_tienda,'','','');
             echo campo("id_tienda_label","Sucursal",'label',$latienda,'','','');
         }  
         ?> 
    </div>    
    <div class="col-md-8">         
         <?php if ($id_estado=='' || $id_estado==99) {            
               echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,'  class=" "',' onchange="comb_actualizar_veh();"  ','get.php?a=3&t=1',$producto_etiqueta); 
         }else{
               echo campo("id_producto","Vehiculo",'hidden',$id_producto,'','','');
               echo campo("id_producto_label","Vehiculo",'label',$producto_etiqueta,'','','');
          }      
         ?>            
    </div> 
</div>    


<div class="row">
    <div class="col-md">
        <?php echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ',$disable_sec1); ?>        
    </div>
    
    <div class="col-md">
         <?php if (es_nulo($id_estado) || $id_estado==99){ 
              echo campo("id_estado_pintura","Pintura",'select2',valores_combobox_db("ventas_estado",$id_estado_pintura,"nombre"," where ventas_reparacion=1 ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_estado_pintura","pintura",'hidden',$id_estado_pintura,'','','');
              echo campo("id_pintura_label","Pintura",'label',$elestado1,'','','');
         }
         ?>         
    </div>
    <div class="col-md">
         <?php if (es_nulo($id_estado) || $id_estado==99){ 
              echo campo("id_estado_interior","Interior",'select2',valores_combobox_db("ventas_estado",$id_estado_interior,"nombre"," where ventas_reparacion=1 ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_estado_interior","interior",'hidden',$id_estado_interior,'','','');
              echo campo("id_interior_label","Interior",'label',$elestado2,'','','');
         }
         ?>         
    </div>
    <div class="col-md">
         <?php if (es_nulo($id_estado) || $id_estado==99){ 
              echo campo("id_estado_mecanica","Mecanica",'select2',valores_combobox_db("ventas_estado",$id_estado_mecanica,"nombre"," where ventas_reparacion=1 ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_estado_mecanica","mecanica",'hidden',$id_estado_mecanica,'','','');
              echo campo("id_mecanica_label","Mecanica",'label',$elestado3,'','','');
         }
         ?>         
    </div>
</div>  

<div class="row">
     <div class="col-md">
         <?php echo campo("observaciones_reparacion","Observaciones",'textarea',$observaciones_reparacion,' ',' required '.$disable_sec1); ?>         
     </div>
</div>

<div class="row">
<div class="col-md" id="archivofoto">
  <?php  
    
    if ($foto=='') {  echo campo_upload("foto","Adjuntar comprobante de pago",'upload','', '  ','',4,8,'NO',false ); }                   
  ?>

</div>
<div class="col-md">
<div class="" id="insp_fotos_thumbs">
  <?php
  if ($foto<>'') {
     $fext = substr($foto, -3);
            if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {               
                echo '  <a href="#" onclick="mostrar_foto(\''.$foto.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';
            } else {
                echo '  <a href="uploa_d/'.$foto.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto.'</a> ';
            }
  }
  ?>
</div>
</div>
</div>
				
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
		<div class="col-sm">     
            <?php if ($id_estado==99 || $id_estado=='') {?>       
                 <a href="#" onclick="procesar('vehiculos_reparacion_mant.php?a=g&est=<?php echo $completar; ?>','forma_ventas',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> <?php echo $NombreBotton; ?></a>                           
            <?php } ?>
        </div>        
      

        <?php if (tiene_permiso(168)){ ?>
              <div class="col-sm"><a id="ventas_anularbtn"  href="#" onclick="ventas_anular(); return false;" class="btn btn-danger  btn-block mr-2 mb-2 xfrm"><i class="fa fa-trash-alt"></i> Borrar</a></div>		                                        
        <?php } ?>  

        <?php if (!es_nulo($id_inspeccion)){ ?>            
            <a href="#" onclick="abrir_hoja(); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-file-medical-alt"></i> Abrir Inspección</a>
        <?php } ?>  

        <div class="col-sm"><a href="#" onclick="$('#ModalWindow2').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
		</div>
	</div>

	</fieldset>
	</form>

<?php  ?>    
 
</div>

</div>

</div>


</div>

<!-- HISTORIAL -->
<div class="tab-pane fade " id="nav_historial" role="tabpanel" ></div>

<!-- errores -->
<div class="tab-pane fade mt-5 mb-5" id="nav_deshabilitado" role="tabpanel" ><div class="alert alert-warning" role="alert">Debe Guardar el documento para poder continuar con esta sección</div></div>



<script>

function abrir_hoja(){    
    hinspeccion = $('#id_inspeccion').val();
    $('#ModalWindow2').modal('hide');
    get_page('pagina','inspeccion_mant.php?a=v&cid='+hinspeccion,'Hoja de Inspección',false);
}

function insp_guardar_foto(arch,campo){

           $('#'+campo).val(arch);                
           $('#files_'+campo).text('Guardado');
           $('#lk'+campo).html(arch);
           thumb_agregar(arch);    
}


function mostrar_foto(imagen) {
  Swal.fire({
  imageUrl: 'uploa_d/'+imagen,

}); 

}

function thumb_agregar(archivo){
if (archivo!='' && archivo!=undefined) {
  
    var fext= archivo.substr(archivo.length - 3);

    if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
       $("#insp_fotos_thumbs").append('<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a>');
    } else {
       $("#insp_fotos_thumbs").append('<a href="uploa_d/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>');
    }
  }
}


function comb_actualizar_veh(){
   
    var datos=$('#id_producto').select2('data')[0];
 
$('#forma_ventas input[id=placa] ').val(datos.placa);


}

function ventas_anular(){
    Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar este vehiculo?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	     ventas_procesar('vehiculos_reparacion_mant.php?a=del','forma_ventas','del');        
	  }
	})

}

function ventas_procesar(url,forma,adicional){
   
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

					$("#"+forma+' #id').val(json[0].pcid);

                    if (adicional=="del") {
                        $('#ModalWindow2').modal('hide');
                        $( "#btn-filtro" ).click();
                    }
                    if (adicional=="dfoto") {     
                       $('#insp_fotos_thumbs').remove();                                                                 
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

function ventas_cambiartab(eltab) {
  var codigo= $('#id').val();
  var continuar=true;
  $('.tab-pane').hide();


  if (eltab!='nav_detalle') {
    if (codigo=="0" || codigo=="") {
      continuar=false;
      $('#nav_deshabilitado').show();
      $('#nav_deshabilitado').tab('show');
    } 
  }


  if (eltab=='nav_historial') {
     procesar_ventas_historial('nav_historial');
  }
  
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

//   nav_detalle
// nav_fotos
// nav_doctos 

}

function procesar_ventas_historial(campo){

var cid=$("#id").val();
var pid=$('#id_producto').val();
var url='ventas_historial.php?cid='+cid+'&pid='+pid ;

$(window).scrollTop(0);
$("#"+campo).html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span class="">'+'Cargando'+'</span></div>');			

$("#"+campo).load(url, function(response, status, xhr) {	
   
  if (status == "error") { 

    //$("#"+campo).html("Error"; // xhr.status + " " + xhr.statusText
    $("#"+campo).html('<p>&nbsp;</p>');
    mytoast('error','Error al cargar la pagina...',6000) ;
  }

});
  
}


</script>


