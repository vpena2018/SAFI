<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
//if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}
if (isset($_REQUEST['tipo'])) { $tipo = intval($_REQUEST['tipo']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}
if (isset($_REQUEST['estactual'])) { $estactual = $_REQUEST['estactual']; } else	{$estactual=0;}
if (isset($_REQUEST['taller'])) { $taller_externo = $_REQUEST['taller']; } else	{$taller_externo=0;}

require_once ('include/framework.php');

function permiso_serviciotipo($accion,$tipo,$estactual){
  $autorizado=false;
  $AgregarRepuestos=true;
  $agregarActividades=true;

  switch ($tipo) {
    case 2: //repuestos
      if ($accion=="agr" and $estactual<22 and $estactual!=1 and $estactual!=2 and $estactual!=20) 
        {
          if (tiene_permiso(64)){$autorizado=true;}
        }

      if ($accion=="aut") {if (tiene_permiso(65)){$autorizado=true;}}
      if ($accion=="rec") {if (tiene_permiso(66)){$autorizado=true;}}
      if ($accion=="norec") {if (tiene_permiso(67)){$autorizado=true;}}
      if ($accion=="dev") {if (tiene_permiso(68)){$autorizado=true;}}
      if ($accion=="solcomp") {if (tiene_permiso(70)){$autorizado=true;}}
      if ($accion=="comprea") {if (tiene_permiso(81)){$autorizado=true;}}
      if ($accion=="comppro") {if (tiene_permiso(158)){$autorizado=true;}}
      if ($accion=="complocal")  {if (tiene_permiso(158)){$autorizado=true;}}
      break;
    
    case 3: //actividades
      if ($accion=="agr" and $estactual<22 and $estactual!=1 and $estactual!=2 and $estactual!=20) 
        {
          if (tiene_permiso(62)){$autorizado=true;}
        }
      if ($accion=="aut") {if (tiene_permiso(63)){$autorizado=true;}}
      if ($accion=="atender") {if (tiene_permiso(100)){$autorizado=true;}}
      if ($accion=="realiza") {if (tiene_permiso(101)){$autorizado=true;}}
      if ($accion=="solcomp") {if (tiene_permiso(70)){$autorizado=true;}}
      if ($accion=="comprea") {if (tiene_permiso(81)){$autorizado=true;}}      
      break;
  }

  if (!$autorizado) {
    echo '<div class="card-body">';
    echo'No tiene privilegios para accesar esta función, U orden puede estar pendiente de aprobación';
      echo '</div>';
      exit;
      exit;
  }
 
}

//***********************
//***********************
if ($accion =="del") {
  $stud_arr[0]["pcode"] = 0;
  $stud_arr[0]["pmsg"] ="ERROR DB101";

  $sql_detalle="";
  $sql_or="";
  if (tiene_permiso(44)) {

  if (isset($_REQUEST['idet'])) {
      foreach ($_REQUEST['idet'] as $key => $value) {          
        $sql_detalle.=$sql_or."id=$value";
        $sql_or=" or ";
      } 
   }

//BUG ojo agregar condicionantes para verificar si se puede borrar porque si ya fue emitida orden compra o cobro ya no se puedeeliminar
$tmpsql="DELETE FROM servicio_detalle WHERE ($sql_detalle) and id_servicio=$cid ";

  $result = sql_delete($tmpsql);

if ($result!=false){

  $stud_arr[0]["pcode"] = 1;
    $stud_arr[0]["pmsg"] ="Registros Eliminados";
    $stud_arr[0]["pcid"] = $cid;
}
  }else {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="No Tiene Privilegios para Borrar";
  }

salida_json($stud_arr);
  exit;

}



//***********************
//***********************

function  cargar_detalle_dlg($accion,$cid,$tipo, $id_estado_actual,$id_estado_actual2=0){
    global $lin;

    $id_usuario=$_SESSION['usuario_id'];

    $sql_estado=" estado=$id_estado_actual";
    if ($id_estado_actual2>0) {
      $sql_estado=" (estado=$id_estado_actual or estado=$id_estado_actual2)";
    }

    $filtro=" and servicio_detalle.producto_tipo=$tipo ";
    if ($tipo==3) {
      $filtro=" and (servicio_detalle.producto_tipo=$tipo )";
    }

    if($accion=="atender" || $accion=="realiza")
    {
      $servicios_result = sql_select("SELECT * FROM servicio_detalle where id_servicio=$cid and id_usuario=$id_usuario  $filtro and $sql_estado order by id ");
    }else{
      $servicios_result = sql_select("SELECT * FROM servicio_detalle where id_servicio=$cid  $filtro and $sql_estado order by id ");
    }
  
    
    //$servicios_result = sql_select("SELECT * FROM servicio_detalle where id_servicio=$cid  $filtro and $sql_estado order by id ");
  
        if ($servicios_result->num_rows > 0) { 
          while ($detalle = $servicios_result -> fetch_assoc()) {
  
            echo agregar_servicio_detalle_dlg($lin,$detalle["precio_venta"],$detalle["precio_costo"],$detalle["id_producto"],$detalle["producto_codigoalterno"],$detalle["producto_nombre"],$detalle["id"],$detalle["cantidad"],$detalle["producto_nota"],$detalle["estado"]);
                  
            $lin++;
          
            }} else {
                echo '<tr><td colspan="5" >No se encontraron registros que puedan ser modificados<td> </tr>';
            }
    } 
  
  
  function agregar_servicio_detalle_dlg($vlin,$data_pv,$data_pc,$data_id,$data_alt,$data_desc,$det_id,$cantidad,$nota,$estado) {
    $salidatxt="";
    $devueltoclass="";
    if ($estado==4) {$devueltoclass="texto-borrado";}
  
    $salidatxt.='<tr id="vdetli_'.$vlin.'" class="'.$devueltoclass.'"  data-cod="'.$data_id.'"  data-detid="'.$det_id.'" data-acc="">';
    // $salidatxt.='<li id="vdetli_'+vlin+'" class="list-group-item list-group-item-action d-sm-flex justify-content-between align-items-center" data-pv="'+data.pv+'" data-pc="'+data.pc+'" data-cod="'+data.id+'">';
    $salidatxt.='<td> <input class="serv_chk_dlg" type="checkbox" value="'.$det_id.'" name="det_id[]"></td> ';
    
   $salidatxt.='<td><span class="badge badge-secondary">'.$data_alt.'</span>';
   // $salidatxt.='<input name="det_codigo[]"  value="'.$data_id.'"  type="hidden"  />'; 
    // $salidatxt.='<input name="det_tipo[]"  value="'+tipo+'"  type="hidden"  />'; 
    //$salidatxt.='<input name="det_id[]"  value="'.$det_id.'"  type="hidden"  />'; 
    // $salidatxt.='<input name="det_acc[]"  value=""  type="hidden"  />';
    $salidatxt.='</td>';
    $salidatxt.='<td>'.$data_desc.'</td> ';
    $salidatxt.='<td>'.$cantidad.'</td>';	
    $salidatxt.='<td>'.$nota.'</td>';	

    


    $salidatxt.='<td>'.get_servicio_detalle_estado($estado).'</td>';	
    // $salidatxt.='<td>'.formato_numero($data_pv,2).'</td> ';
    // $salidatxt.='<td>'.formato_numero($data_pc,2).'</td> ';
  
     // $salidatxt.='<td><span class="badge badge-secondary dettotal">'+(parseFloat(data.pv)*cantidad)+'</span></td> ';
    
     $salidatxt.='</tr>';
  
     return $salidatxt;
  }



  //***********************
//***********************
if ($accion =="emlaut") {// enviar correo de solicitar autorizacion
  $stud_arr[0]["pcode"] = 0;
  $stud_arr[0]["pmsg"] ="No se encontraron nuevos repuestos y/o actividades";
  $stud_arr[0]["pcid"] = 0;
  

  $sn=0;

  $correotabladetalle="";
  if (isset($_REQUEST['cid'])){ 
    
    $cid=intval($_REQUEST['cid']); 

    $emlaut_result = sql_select("SELECT cantidad,producto_codigoalterno,producto_nombre 
    FROM servicio_detalle 
    where id_servicio=$cid
    AND estado<=1
     ");
     

    if ($emlaut_result->num_rows > 0) { 
      while ($emlaut_row = $emlaut_result -> fetch_assoc()) {  
          //detalle para correo
          $correotabladetalle.='
          <tr>
              <td align="center">'.$emlaut_row["cantidad"].'</td>
              <td>'. $emlaut_row["producto_codigoalterno"].'</td>
              <td>'. $emlaut_row["producto_nombre"].'</td>                      
          </tr>
          ';
     
          $sn++;
      
        }

        require_once ('correo_servicio_repuesto_solicitud.php');

          $stud_arr[0]["pcode"] = 1;
          $stud_arr[0]["pmsg"] ="Solicitado autorización $sn repuestos y/o actividades";
          $stud_arr[0]["pcid"] = 1;

      }
     
  } 

  salida_json($stud_arr);
  exit;
}


  //***********************
//***********************
if ($accion =="agr_g") {// guardar servicio o repuesto

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

     //Validar
	$verror="";
  $correotabladetalle="";
  if (isset($_REQUEST['det_cant'])){
      $val_cantidades=$_REQUEST['det_cant'];
      foreach( $val_cantidades as $val_cantidad ) {
        if (floatval($val_cantidad)<=0) {
          $verror="Algunas lineas tienen cantidades vacias ó en 0 , favor revise y asigne las cantidades";
        }
      }
  }

    if ($verror=="") {

        $sqldetalle = array();

        $i=0;
    if (isset($_REQUEST['det_codigo'])){

        //#### validar inventario ####################
        //desabilitado temporalmente
      //   if($_REQUEST['tipo']==2){ //solo repuestos
      //   $val_det=$_REQUEST['det_codigo'];
      //   foreach( $val_det as $val_det_codigo ) {
      //     unset($tmpresult,$producto);
      //     $val_codproducto=ceroif_nulo(filter_var( $val_det_codigo, FILTER_SANITIZE_NUMBER_INT));
      //     $val_cantproducto=ceroif_nulo(filter_var( $_REQUEST['det_cant'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

      //      $tmpresult = $conn -> query("SELECT producto.codigo_alterno,producto.nombre
      //      ,(SELECT producto_costo.OnHand FROM  producto_costo where producto.codigo_alterno=producto_costo.codigo_alterno 
      //      AND producto_costo.sap_almacen='".$_SESSION['sap_almacen']."') AS existencia           
      //       FROM producto
      //       WHERE producto.habilitado=1 and producto.id=".$val_codproducto."            
      //       LIMIT 1");

      //        if ($tmpresult->num_rows > 0) { 
      //          $producto = $tmpresult -> fetch_assoc() ;
      //          $exist=$producto['existencia'];
      //          if(es_nulo($exist)){$exist=0;}
      //          if($exist<$val_cantproducto){
      //           $verror.="No hay existencia para ".$producto['codigo_alterno']." ".$producto['nombre']." <br>";
      //          }
      //         }
      //   }
      //   if ($verror<>"") {
      //     $stud_arr[0]["pcode"] = 0;
      //     $stud_arr[0]["pmsg"] =$verror;
      //     $stud_arr[0]["pcid"] = 0;
      //     salida_json($stud_arr);
      //     exit;
      //   }
      // }
        // ##### fin validar #####################

        $i=0;
        foreach( $_REQUEST['det_codigo'] as $det_codigo ) {

          unset($tmpresult,$producto,$det_id,$codproducto,$tipoproducto,$cantproducto,$costoproducto,$precioproducto, $totalproducto);
          $codproducto=ceroif_nulo(filter_var( $det_codigo, FILTER_SANITIZE_NUMBER_INT));
          $cantproducto=ceroif_nulo(filter_var( $_REQUEST['det_cant'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
          $nota=$_REQUEST['det_nota'][$i] ;
          $costoproducto=ceroif_nulo(filter_var( $_REQUEST['det_pc'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
          $precioproducto=ceroif_nulo(filter_var( $_REQUEST['det_pv'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
          $horas=0;

          if (es_nulo($codproducto)) {
            $verror.=  "No se encontro el producto $det_codigo <br>";
          } else {

            $tmpresult = $conn -> query("SELECT * FROM producto
            WHERE habilitado=1 and id=".$codproducto."
            LIMIT 1");

             if ($tmpresult->num_rows <= 0) { $verror.= "No se encontro el producto $det_codigo <br>";} 
             else {
                $producto = $tmpresult -> fetch_assoc() ;                   
                //Calculos                
                $tmpsql="";$tmpsql2="";

                $tmpsql="INSERT INTO servicio_detalle SET id_servicio=$cid ";
                $tmpsql.=",id_usuario=".GetSQLValue($_SESSION['usuario_id'],"int");
                $tmpsql.=",fecha=now(),";
               
                $tmpsql.="id_producto=".GetSQLValue($codproducto,"int");
                if(isset($cantproducto)){
                $tmpsql.=",cantidad=".GetSQLValue($cantproducto,"double");
                }
                if(isset($costoproducto)){
                  if (strtoupper(substr($producto['codigo_alterno'], 0, 3))=='ATM') {
                    $costoproducto=$producto['precio_costo'];
                    $horas=$producto['horas'];
                  }
                  $tmpsql.=",precio_costo=".GetSQLValue($costoproducto,"double");                                    
                  $tmpsql.=",horas=".GetSQLValue($horas,"double");                                                         
                }
                if(isset($precioproducto)){
                    $tmpsql.=",precio_venta=".GetSQLValue($precioproducto,"double");
                }
                // $tmpsql.=",precio_costo=".GetSQLValue($producto['precio_costo'],"double"); 
                // $tmpsql.=",precio_venta=".GetSQLValue($producto['precio_venta'],"double");

                $tmpsql.=",producto_nota=".GetSQLValue($nota,"text"); 
         
                $tmpsql.=",producto_tipo=".GetSQLValue($producto['tipo'],"int");
                $tmpsql.=",producto_codigoalterno=".GetSQLValue($producto['codigo_alterno'],"text");
                $tmpsql.=",producto_nombre=".GetSQLValue($producto['nombre'],"text");                                
        
                array_push($sqldetalle, $tmpsql.$tmpsql2);

                //detalle para correo
                $correotabladetalle.='
                    <tr>
                        <td align="center">'.$cantproducto.'</td>
                        <td>'. $producto["codigo_alterno"].'</td>
                        <td>'. $producto["nombre"].'</td>                      
                    </tr>
                    ';
             
     
    
            
          }
        }
          
          $i++;

        }
    }

    $hayerrores=false;
    foreach( $sqldetalle as $sqldet ) {
        $result=sql_insert($sqldet);
        if ($result==false){ $hayerrores=true;}
        }	

          //  historial
          sql_insert("INSERT INTO servicio_historial_estado
          (id_servicio,  id_usuario,  nombre, fecha, observaciones)
          VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Solicitud de Repuestos/servicios', NOW(), '')");
  
         
        //enviar correo
        //DESHABILITADO 4JULIO2022***  require_once ('correo_servicio_repuesto_solicitud.php');



    if ($hayerrores==false){
        $stud_arr[0]["pcode"] = 1;
          $stud_arr[0]["pmsg"] ="Guardado";
          $stud_arr[0]["pcid"] = 1;
      } else {
        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ='Se produjeron Errores al Guardar';
        $stud_arr[0]["pcid"] = 0;
      }


    } else {
          $stud_arr[0]["pcode"] = 0;
          $stud_arr[0]["pmsg"] =$verror;
          $stud_arr[0]["pcid"] = 0;
      }


    salida_json($stud_arr);
    exit;

}

if ($accion =="agr") { //Agregar servicio repuesto

  permiso_serviciotipo($accion,$tipo,$estactual);

    ?>
     <form id="formagr_serv" name="forma_cambiarcmp" class="needs-validation" novalidate>
    <fieldset > 
        <input  name="cid"  type="hidden" value="<?php echo $cid; ?>" >
        <input  name="tipo"  type="hidden" value="<?php echo $tipo; ?>" >

    <div class="row"> 
        <div class="table-responsive">  
            <table id="servicio_agr_detalle_tabla" class="table table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                <th>Codigo</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Nota</th>
                <th></th>
                </tr>
            </thead>
            <tbody >   
     
            </tbody>        
        </table>       
        </div>
    </div>


    </fieldset> 
    </form>

    <div class="row">
          <div class="col">
          <div id="servicio_botones_agregar_guardar" class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top oculto ">
          
             <a href="#" onclick="procesar_servicio_agregar('servicio_mant_repuesto.php?a=agr_g','formagr_serv','',<?php echo $tipo; ?>); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a> 
          

          </div>
          </div>
        </div>

        <hr>
    <?php


//Agregar nuevo repuesto
echo '<div id="agrrepuestos" class=" ">
    <div  class="row bg-light ">
      <div class="col-md-6">'.campo("servicio_agr_servicio","Codigo o Descripcion del repuesto",'select2ajax','','class="form-control" style="width: 100%" ','','get.php?a=3&t='.$tipo.'&ff=1&taller='.$taller_externo,'').'</div> 
      <div class="col-md-2 mt-2">'.campo("servicio_agr_cantidad","Cantidad",'number','1','class="form-control" ','','','','').'</div>
      <div class="col-md-2 mt-2"><a class="btn btn-md btn-success " href="#" onclick="servicio_agregar_solicitud('.$tipo.'); return false;"><i class="fa fa-plus"></i></a></div>
    </div></div>
    ';
?>
<script>
 var vlin=1;
    function servicio_agregar_solicitud(tipo){
        var continuar=true;
    
    
    
            if ($('#servicio_agr_servicio').val() == "" || $('#servicio_agr_servicio').val() == null){
              mymodal('warning','Error','Debe ingresar el servicio o repuesto');
              continuar=false;
            } else {
              var data=$('#servicio_agr_servicio').select2('data')[0];
              var cantidad=parseFloat($('#servicio_agr_cantidad').val() );    
            }
    
    
    
    
        if (continuar==true) {
              
                vlin=vlin+1;
    
              
                var salidatxt='';
            
                salidatxt+='<tr id="vdetli_'+vlin+'"  data-cod="'+data.id+'" >';              
                salidatxt+='<td><span class="badge badge-secondary">'+data.alt+'</span>';
                salidatxt+='<input name="det_codigo[]"  value="'+data.id+'"  type="hidden"  />'; 
                salidatxt+='<input name="det_id[]"  value=""  type="hidden"  />'; 
                salidatxt+='<input name="det_pc[]"  value="'+data.pc+'"  type="hidden"  /> ';
                salidatxt+='<input name="det_pv[]"  value="'+data.pv+'"  type="hidden"  /> ';
         
                salidatxt+='</td>';
                salidatxt+='<td>'+data.desc+'</td> ';
                salidatxt+='<td><input name="det_cant[]" class="detcampo detancho detcant" onchange="servicio_totlinea(this);" value="'+cantidad+'"  type="number"  /></td>';	
                salidatxt+='<td><input name="det_nota[]" class="detcampo detancho150 "   value=""  type="text" autocomplete="off"  /></td> ';
  
                salidatxt+='<td><a class="btn btn-sm btn-default  d-print-none " href="#" onclick="servicio_borrar_linea(this); return false;"> <i class="fa fa-trash"></i></a></td> ';
              
                salidatxt+='</tr>';
    
                
    
                if (tipo==2) { //repuesto
    
                }
    
                if (tipo==3) { //servicio
    
                }
    
                $('#servicio_agr_detalle_tabla > tbody:last-child').append(salidatxt);
                $('#servicio_agr_cantidad').val('1');
                $('#servicio_agr_servicio').val(null).trigger('change');

                $('#servicio_botones_agregar_guardar').show();
    
              // calcular_totales();
          }
    }
    
    
    function servicio_borrar_linea(linea) {
    
    
     Swal.fire({
       title: 'Borrar',
       text:  'Desea Borrar esta linea?',
       icon: 'question',
       showCancelButton: true,
       confirmButtonColor: '#3085d6',
       cancelButtonColor: '#d33',
       confirmButtonText:  'Borrar',
       cancelButtonText:  'Cancelar'
     }).then((result) => {
       if (result.value) {
    
                 $(linea).closest('tr').remove();
    
       }
     })
    
    }

    function procesar_servicio_agregar(url,forma,adicional,tipo){
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
				

					//$("#"+forma+' #id').val(json[0].pcid);

                    if (tipo==2) { //repuesto
                        get_box('tbody_servicio_rep','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
                    }

                    if (tipo==3) { //servicio
                        get_box('tbody_servicio_det','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
                    }

                 
                    $('#ModalWindow2').modal('hide');

                    mytoast('success',json[0].pmsg,3000) ;
			
			
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

<?php
    exit;

}






if ($accion =="aut" or $accion =="rec" or $accion =="norec" or $accion =="dev" or $accion =="solcomp" or $accion =="comprea" or $accion =="atender" or $accion =="realiza" or $accion =="comppro" or $accion =="complocal") { //cambiar estado autorizar

  permiso_serviciotipo($accion,$tipo,$estactual);

  $sql_estado="";
  $id_estado_actual2=0;
  
    if ($accion =="aut" ) { 
        $botontxt="Autorizar";
        $id_estado_actual=1;
        $id_estado_nuevo=2; 
        $sql_estado=" and estado=$id_estado_actual ";
    }

    if ($accion =="rec" ) { 
        $botontxt="Recibir";
        $id_estado_actual=2;
        $id_estado_actual2=7;
        $id_estado_nuevo=3;
        $sql_estado=" and (estado=$id_estado_actual or estado=6 or estado=7)";
    }

    if ($accion =="norec" ) { 
      $botontxt="NO Recibido";
      $id_estado_actual=2;
      $id_estado_actual2=7;
      $id_estado_nuevo=5;
      $sql_estado=" and (estado=$id_estado_actual or estado=6 or estado=7)";
  }

    if ($accion =="dev") { 
        $botontxt="Devolver";
        $id_estado_actual=3;
        $id_estado_nuevo=4;
        $sql_estado=" and estado=$id_estado_actual ";
    }

    if ($accion =="solcomp" ) { 
      $botontxt="Solicitud Compra";
      $id_estado_actual=2;
      $id_estado_nuevo=6;
      $sql_estado=" and estado=$id_estado_actual ";
  }

    if ($accion =="comprea" ) { 
      $botontxt="Compra Realizada";
      $id_estado_actual=10;
      if ($tipo==3){ $id_estado_actual2=6;}else{ $id_estado_actual2=11;}
      $id_estado_nuevo=7;
      $sql_estado=" and (estado=$id_estado_actual or estado=11 or estado=6)";
      
    }

    $sql_atender="";
    if ($accion =="atender" ) { 
      $botontxt="Atender";
      $id_estado_actual=2;
      $id_estado_nuevo=8;
      $sql_estado=" and estado=$id_estado_actual ";
      $sql_atender=",id_usuario_atender=".$_SESSION['usuario_id'];
      $sql_atender.=",fecha_atender=NOW()";

    }

    if ($accion =="realiza" ) { 
      $botontxt="Realizado";
      $id_estado_actual=8;
      $id_estado_nuevo=9;
      $sql_estado=" and estado=$id_estado_actual ";
      $sql_atender=",fecha_atender_fin=NOW()";
      $sql_atender.=",horas_atender=IFNULL((TIMESTAMPDIFF(MINUTE, fecha_atender,NOW()) /60 ),0)";
    }
    
    if ($accion =="comppro") { 
      $botontxt="Compra Extranjero";
      $id_estado_actual=6;
      $id_estado_nuevo=10;
      $sql_estado=" and estado=$id_estado_actual ";
    }

    if ($accion =="complocal") { 
      $botontxt="Compra Local";
      $id_estado_actual=6;
      $id_estado_nuevo=11;
      $sql_estado=" and estado=$id_estado_actual ";      
    }
    
   
    if (isset($_REQUEST['g'])) { //Guardar cambio de estado

        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] ="ERROR DB101";

        $estado_actual=0;
        $estado_nuevo=0;
   
        
    
        $sql_detalle="";
        $sql_or="";
    
        if (isset($_REQUEST['idet'])) {
            foreach ($_REQUEST['idet'] as $key => $value) {          
              $sql_detalle.=$sql_or."id=$value";
              $sql_or=" or ";
            } 
         }

         $sql_inventario="";
         //Salida Inventario
         if ($accion =="aut" and $tipo==2 ) {
            $sql_inventario=", SAP_tipo=60 ";
            $solicitud_conteo=get_dato_sql('servicio_detalle','(ifnull(MAX(solicitud_conteo),0)+1) ','WHERE id_servicio='.$cid);
            $sql_inventario.=",solicitud_conteo=".GetSQLValue($solicitud_conteo,"int");
         }

         // Entrada Inventario
         if ($accion =="dev" and $tipo==2 ) {
            $sql_inventario=", SAP_tipo=59 ";
         }
         
         $tmpsql="update servicio_detalle set estado=$id_estado_nuevo  $sql_inventario $sql_atender  WHERE ($sql_detalle)  $sql_estado and id_servicio=$cid ";
    
         $result = sql_update($tmpsql);

        //  historial
        sql_insert("INSERT INTO servicio_historial_estado
        (id_servicio,  id_usuario,  nombre, fecha, observaciones)
        VALUES ( $cid,  ".$_SESSION['usuario_id'].", '$botontxt Repuestos/servicios', NOW(), '')");

          if ($accion =="atender" ) { 
            $actual_estado=intval(get_dato_sql("servicio","id_estado","where id=$cid"));
            if ($actual_estado<>4) {
              $result_atender = sql_update("UPDATE servicio SET id_estado=4 where id=$cid and id_estado<>4  limit 1");
              sql_insert("INSERT INTO servicio_historial_estado
              (id_servicio, id_estado, id_usuario, id_proveedor, nombre, fecha, observaciones)
              VALUES ( $cid, 4, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Estado', NOW(), 'Atender')");
         
            }
            
  

          }

       
        if ($accion =="aut" and $tipo==2 ) {            
            // enviar correo alerta
            $tipo_movimiento_texto='Salida';
            require_once ('correo_servicio_repuesto_salida.php');
            // si es repuesto crear orden y enviar a SAP
            // BUG: SAP 
        }

        if ($accion =="dev" and $tipo==2 ) {
            // enviar correo alerta
            $tipo_movimiento_texto='DEVOLUCION';
            require_once ('correo_servicio_repuesto_salida.php');
            // si es repuesto devuelve repuesto y enviar a SAP            
            // BUG: SAP 
        }

        if ($accion =="solcomp"  ) {  //and $tipo==2   
          // enviar correo alerta
          $tipo_movimiento_texto='Solicitud Compra';
          require_once ('correo_servicio_repuesto_salida.php');

      }

       
    
      if ($result!=false){
    
        $stud_arr[0]["pcode"] = 1;
          $stud_arr[0]["pmsg"] ="Registros Actualizados";
          $stud_arr[0]["pcid"] = $cid;
      }
    
      salida_json($stud_arr);
        exit;


        
         
    }

    
    
   
    $lin=1;

      ?>  



  <div class="row"> 
      <h6 class="ml-4 text-primary">Seleccione los Repuestos/Servicios para <?php echo $botontxt?></h6>
      <br>
  <div class="table-responsive">  
      <table id="detalleul_servicio_dlg" class="table table-striped table-hover" style="width:100%">
        <thead>
          <tr>
           <th><input type="checkbox"  onchange="servicio_marcar_todos(this,'detalleul_servicio_dlg'); "  ></th>
            <th>Codigo</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Nota</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody >
         
      
      <?php      
     
      cargar_detalle_dlg($accion,$cid,$tipo, $id_estado_actual,$id_estado_actual2); 
      ?>
      <!-- </ul> -->

      </tbody>
 
 </table>

   
    
  </div>
  </div>

  <div class="row">
          <div class="col">
          <div id="servicio_botones_agregar_guardar3" class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top  ">
          
             <a href="#" onclick="procesar_servicio_estado('servicio_mant_repuesto.php?a=<?php echo $accion?>&g=1','formest_serv',<?php echo $tipo; ?>); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> <?php echo $botontxt?></a> 
          

          </div>
          </div>
        </div>


        <script>
            
function procesar_servicio_estado(url,objeto,tipo){

var values=[];
//	$('.'+objeto+' input[type="checkbox"]').each(function() {
  $('.serv_chk_dlg').each(function() {
    if ($(this).is(":checked")) {
      values.push(parseFloat($(this).val()));     
    }
  }); 

if (values === undefined || values.length == 0) {
  mymodal('error',"Error","Debe seleccionar al menos un registro");
} else {


  //$("#"+forma+" .xfrm").addClass("disabled");		
  cargando(true); 
  
var datos={'idet':values};//$("#"+forma).serialize();

var fullurl=url+'&cid='+$('#id').val()+'&tipo='+tipo; //+'&pid='+$('#id').val()
   $.post( fullurl,datos, function(json) {
               
      if (json.length > 0) {
          if (json[0].pcode == 0) {
              cargando(false);
              mytoast('error',json[0].pmsg,3000) ;   
          }
          if (json[0].pcode == 1) {
              cargando(false);
              
  
                if (tipo==2) { //repuesto
                    get_box('tbody_servicio_rep','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
                }

                if (tipo==3) { //servicio
                    get_box('tbody_servicio_det','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
                }

                
                $('#ModalWindow2').modal('hide');
                mytoast('success',json[0].pmsg,3000) ;
              
               
          
          }
      } else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
        
  })
    .done(function() {
     
    })
    .fail(function(xhr, status, error) {
             cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
    })
    .always(function() {
     
  //	$("#"+forma+" .xfrm").removeClass("disabled");	
    });
}

}
</script>

   <?php  
    


}


?>