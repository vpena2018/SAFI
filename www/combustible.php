<?php
require_once ('include/framework.php');
pagina_permiso(10);



if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}


// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT orden_combustible.*
    ,entidad.nombre AS elproveedor
    ,orden_combustible_estado.nombre AS elestado
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
        FROM orden_combustible
        LEFT OUTER JOIN producto ON (orden_combustible.id_producto=producto.id)
        LEFT OUTER JOIN entidad ON (orden_combustible.id_entidad=entidad.id)
        LEFT OUTER JOIN orden_combustible_estado ON (orden_combustible.id_estado=orden_combustible_estado.id)
        
    where orden_combustible.id=$cid limit 1");

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

    if (!tiene_permiso(117)) {
        $stud_arr[0]["pmsg"] ="No tiene privilegios para Borrar";
    } else {

    $cid=0;
	if (isset($_REQUEST['id'])) { $cid = intval($_REQUEST["id"]);; }

	$result = sql_select("SELECT id_estado
        FROM orden_combustible
    where id=$cid limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
			$row = $result -> fetch_assoc(); 
            if ($row['id_estado']<3) {
                //sql_delete("DELETE FROM orden_combustible where id=$cid limit 1");
                sql_update("UPDATE orden_combustible SET id_estado=4 where id=$cid limit 1");
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

///Revision de ADPC
if ($accion=="adpc") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Error";
    $stud_arr[0]["pcid"] = 0;
    $cid=intval($_REQUEST["id"]);  
    $sqlcampos="";  
    $verror="";
	if (!es_nulo($cid)){   
        if (isset($_REQUEST['adpc'])) {    
            //$verror.=validar("Observarciones",$_REQUEST['observaciones_adpc'], "text", true);              
            $sqlcampos.= "  fecha_auditado = now()";
            $sqlcampos.= ", observaciones_adpc =".GetSQLValue($_REQUEST["observaciones_adpc"],"text");  
            $sqlcampos.= ", id_usuario_auditado =".$_SESSION['usuario_id']; 
            $sql="update orden_combustible set ".$sqlcampos." where id=".$cid." limit 1";
            $result = sql_update($sql); 
            if ($result!=false){
                $stud_arr[0]["pcode"] = 1;
                $stud_arr[0]["pmsg"] ="Guardado";
                $stud_arr[0]["pcid"] = $cid;
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

    $verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
    $verror.=validar("Proveedor",$_REQUEST['id_entidad'], "int", true);
    // $verror.=validar("Fecha",$_REQUEST['fecha'], "text", true);
    // $verror.=validar("Hora",$_REQUEST['hora'], "text", true);
    $verror.=validar("Conductor",$_REQUEST['conductor'], "text", true);
    // $verror.=validar("Destino",$_REQUEST['destino'], "text", true);
    // $verror.=validar("Otros",$_REQUEST['otros'], "text", true);
    $verror.=validar("Tipo Combustible",$_REQUEST['tipo_combustible'], "text", true);      
    // $verror.=validar("Litros",$_REQUEST['litros'], "double", true);
    // $verror.=validar("Lempiras",$_REQUEST['lempiras'], "double", true);    
    if (isset($_REQUEST['foto'])) {
       $verror.=validar("Foto del Odometro y Nivel Combustible",$_REQUEST['foto'], "text", true);
    }            
    
    $autorizando=false;
    if (isset($_REQUEST['au'])) {
       if (!tiene_permiso(71)) {
           $verror="No tiene permiso para Autorizar";
       } else {
         $autorizando=true;         
         $verror.=validar("Contrato",$_REQUEST['contrato_renta'], "text", true);         
         $contrato_renta =trim($_REQUEST['contrato_renta']);
         if (es_nulo($contrato_renta)) {$verror.='Ingrese el numero del contrato';}
       }
    }
    $completando=false;
    if (isset($_REQUEST['compl'])) {
       if (!tiene_permiso(72)) {
          $verror="No tiene permiso para Completar";
       } else {         
         $completando=true;         
         $verror.=validar("Litros",$_REQUEST['litros_reales'], "double", true);
         $verror.=validar("Precio x Litro",$_REQUEST['precio_litro'], "double", true);
         $verror.=validar("Factura Proveedor",$_REQUEST['factura_proveedor'], "text", true);      
         $precio_litro=doubleval($_REQUEST["precio_litro"]);
         $factura_proveedor=$_REQUEST['factura_proveedor'];
         $litros=doubleval($_REQUEST["litros_reales"]);         
         if (strlen($factura_proveedor)<>19) {$verror.='Numero de factura incorrecto ' ; }                                             
         if (es_nulo($precio_litro)) {$verror.='Debe ingresar Precio x Litro ';}
         if (es_nulo($litros)) { $verror.='Debe ingresar Litros';       }         
         if (isset($_REQUEST['foto2'])) {
            $verror.=validar("Foto Factura",$_REQUEST['foto2'], "text", true);
         }
         if (isset($_REQUEST['foto3'])) {
             $verror.=validar("Foto Medidor Combustible",$_REQUEST['foto3'], "text", true);
        
         }
    }       
}


    if ( $completando==false) {
        if (!isset($_REQUEST['combustible_salida'])){$verror.='Debe ingresar el Combustible<br>';}
    }
    if ($completando==true) {
        $facturaprov='';        
        $facturaprov=trim($_REQUEST['factura_proveedor']);         
        $cliente=intval($_REQUEST['id_entidad']);
        if (!es_nulo($facturaprov)){                       
           $factura=get_dato_sql("orden_combustible","COUNT(*)"," WHERE id_estado=3 and id_entidad=".$cliente." and factura_proveedor='".$facturaprov."'" );            
           if ($factura>0){
              $verror='El numero de factura ya existe';   
           }
        }        
    }    
    ///Valido que no existan OC pendientes
    $cid1=intval($_REQUEST["id"]);    
	if (es_nulo($cid1)){        
       $vehiculo=0;
       $ordenesborrador=0;
       if (isset($_REQUEST['id_producto'])) {
          $vehiculo=intval($_REQUEST["id_producto"]);
          if ($vehiculo>0){
             $ordenesborrador=get_dato_sql("orden_combustible","COUNT(*)"," WHERE id_estado=1 AND id_producto=".$vehiculo);
             if ($ordenesborrador>0){
                $verror='Existe una orden de combustible en borrador para el vehiculo';
             }
          }
        }    

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
    if (isset($_REQUEST["id_entidad"])) { $sqlcampos.= " , id_entidad =".GetSQLValue($_REQUEST["id_entidad"],"int"); } 
    
    if (isset($_REQUEST["id_inspeccion"])) { $sqlcampos.= " , id_inspeccion =".GetSQLValue($_REQUEST["id_inspeccion"],"int"); } 
   // if (isset($_REQUEST["id_estado"])) { $sqlcampos.= " , id_estado =".GetSQLValue($_REQUEST["id_estado"],"int"); } 
    
    if (isset($_REQUEST["conductor"])) { if(!es_nulo($_REQUEST["conductor"])){ $sqlcampos.= " , conductor =".GetSQLValue($_REQUEST["conductor"],"text"); } }
    if (isset($_REQUEST["destino"])) { $sqlcampos.= " , destino =".GetSQLValue($_REQUEST["destino"],"text"); } 
    if (isset($_REQUEST["otros"])) { $sqlcampos.= " , otros =".GetSQLValue($_REQUEST["otros"],"text"); } 
    if (isset($_REQUEST["tipo_combustible"])) { $sqlcampos.= " , tipo_combustible =".GetSQLValue($_REQUEST["tipo_combustible"],"text"); } 
    if (isset($_REQUEST["litros"])) { $sqlcampos.= " , litros =".GetSQLValue($_REQUEST["litros"],"double"); } 
    if (isset($_REQUEST["litros_reales"])) { $sqlcampos.= " , litros_reales =".GetSQLValue($_REQUEST["litros_reales"],"double"); } 

    if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
    if (isset($_REQUEST["contrato_renta"])) { $sqlcampos.= " , contrato_renta =".GetSQLValue($_REQUEST["contrato_renta"],"text"); } 
  
    if ( $completando==false) {
        if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
        if (isset($_REQUEST["combustible_salida"])) { $sqlcampos.= " , combustible_salida =".GetSQLValue($_REQUEST["combustible_salida"],"text"); } 

    }
  
    if (isset($_REQUEST["placa"])) { $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa"],"text"); } 
    if (isset($_REQUEST["foto"])) { $sqlcampos.= " , foto =".GetSQLValue($_REQUEST["foto"],"text"); } 
    if (isset($_REQUEST["foto2"])) { $sqlcampos.= " , foto2 =".GetSQLValue($_REQUEST["foto2"],"text"); } 
    if (isset($_REQUEST["foto3"])) { $sqlcampos.= " , foto3 =".GetSQLValue($_REQUEST["foto3"],"text"); } 
 
    if ($autorizando==true) {
        $sqlcampos.= " , id_usuario_autoriza =".$_SESSION['usuario_id'];
        $sqlcampos.= " , autorizado =now()";
        $sqlcampos.= " , id_estado =2";
    }

    if ($completando==true) {
        
        $lempiras=$precio_litro*$litros;
        $sqlcampos.= " , id_estado =3";
        $sqlcampos.= " , factura_proveedor =".GetSQLValue($_REQUEST["factura_proveedor"],"text"); 
        $sqlcampos.= " , precio_litro =".GetSQLValue($_REQUEST["precio_litro"],"double");  
        $sqlcampos.= " , lempiras =".GetSQLValue($lempiras,"double"); 

        // BUG enviar a SAP
    } else {
        if (isset($_REQUEST["lempiras"])) { $sqlcampos.= " , lempiras =".GetSQLValue($_REQUEST["lempiras"],"double"); } 
    }
 
 
    if ($nuevoregistro==false) {
        //Modificando
        $sql="update orden_combustible set ".$sqlcampos." where id=".$cid." limit 1";
        $result = sql_update($sql);
    } else {
        //Crear nuevo
        $sqlcampos.=",id_usuario=".$_SESSION['usuario_id'] ;
        $sqlcampos.=",id_tienda=".$_SESSION['tienda_id'] ;
        $sqlcampos.= " , id_estado =1";

        $sqlcampos.=",numero=".GetSQLValue(get_dato_sql('orden_combustible',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into orden_combustible set fecha=NOW(),hora=NOW(),".$sqlcampos." ";
        
        
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
    }

	
	if ($result!=false){
		$stud_arr[0]["pcode"] = 1;
    	$stud_arr[0]["pmsg"] ="Guardado";
    	$stud_arr[0]["pcid"] = $cid;

        //******** API Rentworks *******/
        $odocampo="";
        if (isset($_REQUEST["kilometraje"])) {
            $odotmp=intval($_REQUEST["kilometraje"]);
            if ($odotmp>0) {$odocampo=$odotmp;}
        }
        /*se desabilito porque en los traslado realiza el cambio estado
        if ($autorizando==true) { 
            require_once ('include/rentworks_api.php');
            $rw_salida=rw_crear_orden(3,$cid,"",$odocampo);
        }
        if ($completando==true) { 
            require_once ('include/rentworks_api.php');
            $rw_salida=rw_cerrar_orden(3,$cid,"",$odocampo);
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




?>
<div class="maxancho800 mx-auto">

<div class="row">
<div class="col">
    	<div class="form-group">
		  
	 
	<form id="forma_combustible" name="forma_combustible">
		<fieldset id="fs_forma">
		 
	 <?php 

if (isset($row["elproveedor"])) {$elproveedor=$row["elproveedor"];} else {$elproveedor="";}
if (isset($row["elestado"])) {$elestado=$row["elestado"];}else {$elestado="";}
if (isset($row["codvehiculo"])) {$producto_etiqueta=$row["codvehiculo"]. ' '.$row["vehiculo"];   }else {$producto_etiqueta="";}

if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= "";}
if (isset($row["id_usuario_autoriza"])) {$id_usuario_autoriza= $row["id_usuario_autoriza"]; } else {$id_usuario_autoriza= "";}
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["id_entidad"])) {$id_entidad= $row["id_entidad"]; } else {$id_entidad= "";}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "";}
if (isset($row["id_inspeccion"])) {$id_inspeccion= $row["id_inspeccion"]; } else {$id_inspeccion= "";}
if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["autorizado"])) {$autorizado= $row["autorizado"]; } else {$autorizado= "";}
if (isset($row["SAP_sinc"])) {$SAP_sinc= $row["SAP_sinc"]; } else {$SAP_sinc= "";}
if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["conductor"])) {$conductor= $row["conductor"]; } else {$conductor= "";}
if (isset($row["destino"])) {$destino= $row["destino"]; } else {$destino= "";}
if (isset($row["otros"])) {$otros= $row["otros"]; } else {$otros= "";}
if (isset($row["tipo_combustible"])) {$tipo_combustible= $row["tipo_combustible"]; } else {$tipo_combustible= "";}
if (isset($row["litros"])) {$litros= $row["litros"]; } else {$litros= "";}
if (isset($row["lempiras"])) {$lempiras= $row["lempiras"]; } else {$lempiras= "";}
if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}
if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
if (isset($row["contrato_renta"])) {$contrato_renta= $row["contrato_renta"]; } else {$contrato_renta= "";}

if (isset($row["foto"])) {$foto= $row["foto"]; } else {$foto= "";}
if (isset($row["foto2"])) {$foto2= $row["foto2"]; } else {$foto2= "";}
if (isset($row["foto3"])) {$foto3= $row["foto3"]; } else {$foto3= "";}
if (isset($row["factura_proveedor"])) {$factura_proveedor= $row["factura_proveedor"]; } else {$factura_proveedor= "";}
if (isset($row["precio_litro"])) {$precio_litro= $row["precio_litro"]; } else {$precio_litro= "";}

if (isset($row["combustible_salida"])) {$combustible_salida= $row["combustible_salida"]; } else {$combustible_salida= "";}

if (isset($row["fecha_auditado"])) {$fecha_auditado= $row["fecha_auditado"]; } else {$fecha_auditado= "";}
if (isset($row["id_usuario_auditado"])) {$id_usuario_auditado= $row["id_usuario_auditado"]; } else {$id_usuario_auditado= "";}
if (isset($row["observaciones_adpc"])) {$observaciones_adpc= $row["observaciones_adpc"]; } else {$observaciones_adpc= "";}
if (isset($row["litros_reales"])) {$litros_reales= $row["litros_reales"]; } else {$litros_reales= "";}

if ($id_estado>=3){
   $addreadonly=" readonly";
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
    <?php 
     // echo campo("id_estado","Estado",'select',valores_combobox_db('orden_combustible_estado',$id_estado,'nombre','','',''),' readonly','');
     echo campo("id_estado","Estado",'hidden',$id_estado,' ',' ');
     echo campo("numero","Estado",'label',$elestado,' ',' ');
     ?>
    </div>
    
</div>



<div class="row">
    <div class="col-md-8">
    <?php echo campo("id_entidad","Proveedor",'select2',valores_combobox_db("entidad",$id_entidad,"nombre"," where (tipo=2 and combustible=1) or (id=".GetSQLValue($id_entidad,"int").")",'','...'),' ',' required ','');  ?> 
    </div>
    <div class="col-md-4">
       
    </div>
    
</div>

<div class="row">
    <div class="col-md-8">
    <?php  echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,'  class=" "',' onchange="comb_actualizar_veh();"  ','get.php?a=3&t=1',$producto_etiqueta); ?>
    </div>
    <div class="col-md-4">
    <?php echo campo("placa","Placa",'text',$placa,' ',' '); ?>    
    </div>
    
</div>


    
    <?php // echo campo("id_estado","Id Estado",'number',$id_estado,' ',' '); 
    ?>


<div class="row">
    <div class="col-md">
        <?php //echo campo("conductor","Conductor",'text',$conductor,' ',' ');
        if (es_nulo($conductor)) {
            $primeal='Seleccione';
        } else {  $primeal=$conductor;}
          echo  campo('conductor', 'Conductor','select2',valores_combobox_db('usuario',$conductor,'nombre',' where activo=1 and (grupo_id=3 or perfil_adicional=3) and tienda_id='.$_SESSION['tienda_id'],'',$primeal,'nombre'),' ','  ','');
        ?>
    </div>
    <div class="col-md">
         <?php echo campo("destino","Destino",'text',$destino,' ',' '); ?>
    </div>
    
</div>

  <div class="row">
  <div class="col-md">   
    <?php 
        $addreadonly="";
        if (intval($id_estado)>1) {$addreadonly=" readonly";}
        echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',$addreadonly); 
    ?>
    </div> 
    </div>

  <div class="row">
  <div class="col-md">
    <span class="outside-label">Combustible</span>
    <?php 
        $disable_combsalida="";
        if (intval($id_estado)>1) {$disable_combsalida='  disabled="disabled"';}
        echo campo_combustible("combustible_salida",$combustible_salida,$disable_combsalida); 
    ?>
    	
  
    </div> 
    </div>
    

  <div class="row">
  <div class="col-md">
    <?php echo campo("tipo_combustible","Tipo Combustible",'select', valores_combobox_texto(app_tipo_combustible,$tipo_combustible),' ',' '); ?>
    </div>
    <div class="col-md">
        <?php if (intval($id_estado)>1){
                 echo campo("litros_reales","Litros Reales",'number',$litros_reales,' ',' '); 
            } else {   
                 echo campo("litros","Litros",'number',$litros,' ',' onchange="combustible_totales();"'); 
            }
         ?>
    
    </div>
    <div class="col-md">
        <?php echo campo("lempiras","Lempiras",'number',$lempiras,' ',' '); ?>
    </div>
    
  </div> 
  <div class="row">
        <div class="col-md">    
        <?php echo campo("otros","Otros",'text',$otros,' ',' '); ?>
        </div> 
    <div class="col-md">
        <?php echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ',' '); ?>
    </div>
<div class="col-md">
       
        <?php echo campo("contrato_renta","Contrato Renta",'text',$contrato_renta,' ',' '); ?>
    </div>
        
    </div>

<div class="row">
<div class="col-md">
<?php  
       if ($foto=='') {  echo campo_upload("foto","Adjuntar Foto Odometro y Combustible",'upload','', '  ','',4,8,'NO',false ); }
      
        
       if (!es_nulo($id_usuario_autoriza)) {
            echo campo("precio_litro","Precio x Litro",'number',$precio_litro,' ',' onchange="combustible_totales();"');
            echo campo("factura_proveedor","No. Factura Proveedor",'text',$factura_proveedor,' ',' ');             
        if ($foto2=='') {  
           // echo '<div class="row"><div class="col-12"><div class="ins_foto_div">';
            echo campo_upload("foto2","Foto Factura",'upload','', '  ','',4,8,'NO',false ); 
           // echo '</div></div></div>';
        }
        
        if ($foto3=='') { 
          //  echo '<div class="row"><div class="col-12"><div class="ins_foto_div">';
            echo campo_upload("foto3","Foto Combustible",'upload','', '  ','',4,8,'NO',false ); 
          //  echo '</div></div></div>';
        }
       }    
 ?>
</div>
<div class="col-md">
<div class="" id="insp_fotos_thumbs">
<?php
    if ($foto<>'') {
        $fext = substr($foto, -3);
        if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
            buscar_archivo_s3('uploa_d/'.$foto,$foto);
            echo '  <a href="#" onclick="'.$onclick.'" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$src.'" data-cod="'.$row["id"].'"></a> '; 
            //echo '  <a href="#" onclick="mostrar_foto(\''.$foto.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';
            } else {
                echo '  <a href="uploa_d/'.$foto.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto.'</a> ';
            }
    }

    if ($foto2<>'') {
        $fext = substr($foto2, -3);
        if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
            buscar_archivo_s3('uploa_d/'.$foto2,$foto2);
            echo '  <a href="#" onclick="'.$onclick.'" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$src.'" data-cod="'.$row["id"].'"></a> '; 
            //echo '  <a href="#" onclick="mostrar_foto(\''.$foto2.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto2.'" data-cod="'.$row["id"].'"></a> ';
        } else {
            echo '  <a href="uploa_d/'.$foto2.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto2.'</a> ';
        }
    }   
    if ($foto3<>'') {
       $fext = substr($foto3, -3);
       if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
           buscar_archivo_s3('uploa_d/'.$foto3,$foto3);
           echo '  <a href="#" onclick="'.$onclick.'" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$src.'" data-cod="'.$row["id"].'"></a> '; 
           //echo '  <a href="#" onclick="mostrar_foto(\''.$foto3.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto3.'" data-cod="'.$row["id"].'"></a> ';
        } else {
           echo '  <a href="uploa_d/'.$foto3.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto3.'</a> ';
        }
    } 

    function buscar_archivo_s3($camino,$filename){
        // Implementar la logica para buscar el archivo en S3 si es necesario
        global $onclick,$src;
        if (file_exists($camino)) {    
            $onclick = 'mostrar_foto(\'' . $filename . '\'); return false;';        
            $src= 'uploa_d/thumbnail/'.$filename;
        } else {            
            $onclick = 'mostrar_foto2(\'' . $filename . '\'); return false;';        
            $src= 'aws_bucket_s3/thumbnail/'.$filename;
        }    
        return false;
    }
?>
</div>
</div>
</div>
<?php     
    $oculto='hidden';
    $obadpc_requerido="";
    $disable_sec6="";
    if (tiene_permiso(163) and es_nulo($id_usuario_auditado) and intval($id_estado)==3) { 
        $oculto='textarea';          
        $obadpc_requerido=' required';
        $disable_sec6=' ';
    }else{ 
        if (tiene_permiso(163) and !es_nulo($id_usuario_auditado)){
           $oculto='textarea';               
           $disable_sec6=' disabled="disabled" ';
        }    
    }
?> 
<div class="row">
  <div class="col-md">                     
        <?php echo campo("observaciones_adpc","Observaciones ADPC",$oculto,$observaciones_adpc,' ',' rows="3" '.$disable_sec6 .$obadpc_requerido);?>      
  </div> 
</div>


    <?php //echo campo("id_usuario","Id Usuario",'number',$id_usuario,' ',' '); 
    ?>
    <?php //echo campo("id_usuario_autoriza","Id Usuario Autoriza",'number',$id_usuario_autoriza,' ',' '); 
    ?>
    <?php //echo campo("id_tienda","Id Tienda",'number',$id_tienda,' ',' '); 
    ?>

    <?php //echo campo("id_inspeccion","Id Inspeccion",'number',$id_inspeccion,' ',' '); 
    ?>
    <?php //echo campo("autorizado","Autorizado",'date',$autorizado,' ',' '); 
    ?>
    <?php //echo campo("SAP_sinc","SAP Sinc",'date',$SAP_sinc,' ',' '); 
    ?>


					

 
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
		<div class="col-sm">
            <?php if (es_nulo($id_usuario_autoriza)) { ?>
                 <a href="#" onclick="procesar('combustible.php?a=g','forma_combustible',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> Guardar</a>
            <?php } else {
                if ($id_estado==2) {
                ?>
                
                <a href="#" onclick="procesar('combustible.php?a=g&compl=1','forma_combustible',''); return false;" class="btn btn-success btn-block mb-2 xfrm" ><i class="fa fa-check"></i> Completar</a>   
            <?php }} ?>   
        </div>
        <div class="col-sm">
            <?php if (es_nulo($id_usuario_autoriza)) { ?>
                  <a href="#" onclick="procesar('combustible.php?a=g&au=1','forma_combustible',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-lock"></i> Autorizar</a>                  
            <?php }else{ 
                  if (es_nulo($id_usuario_auditado) and tiene_permiso(163)) {?>      
                     <a href="#" onclick="procesar('combustible.php?a=adpc&adpc=1','forma_combustible',''); return false;" class="btn btn-warning btn-block mb-2 xfrm" ><i class="fa fa-check"></i> Revision ADPC</a>
            <?php }} ?>
        </div>               		
        <?php if ($id_estado<=2) { ?>
             <div class="col-sm"><a href="#" id="combustible_anularbtn" onclick="combustible_anular(); return false;" class="btn btn-danger  btn-block mr-2 mb-2 xfrm"><i class="fa fa-trash-alt"></i> Borrar</a></div>
        <?php } ?>    
        <div class="col-sm"><a id="combustible_imprimir" target="_blank" href="#" onclick="return combustible_generar_pdf(); " class="btn btn-secondary  btn-block mr-2 mb-2 xfrm"><i class="fa fa-print"></i> Imprimir</a></div>
        <div class="col-sm"><a href="#" onclick="$('#ModalWindow2').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
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
<script>

function combustible_generar_pdf(){
    var codcomb=$("#forma_combustible input[name=id]").val();
    if (codcomb!='') {

        $("#combustible_imprimir").attr("href", "combustible_imprimir.php?a=i&pdfcod="+codcomb);
        return true;
    } else {
        mytoast('warning','Debe guardar la orden antes de imprimir',3000) ;
        return false;
    }
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

function mostrar_foto2(imagen) {  
  Swal.fire({
  imageUrl: 'aws_bucket_s3/'+imagen,
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

function combustible_totales(){
    var salida=0;

    var litros = parseFloat($('#litros_reales').val());
    var precio_litro = parseFloat($('#precio_litro').val());

    if (isNaN(litros)) { litros=0;}
    if (isNaN(precio_litro)) { precio_litro=0;}

    salida=litros*precio_litro;

    $("#lempiras").val(salida.toFixed(2));


     
}

function comb_actualizar_veh(){
   
    var datos=$('#id_producto').select2('data')[0];
 
$('#forma_combustible input[id=placa] ').val(datos.placa);


}

function combustible_anular(){
    Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar este documento?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	    combustible_procesar('combustible.php?a=del','forma_combustible','del');
        
	  }
	})

}


function combustible_procesar(url,forma,adicional){



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


</script>