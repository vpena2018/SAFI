<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
//if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}
if (isset($_REQUEST['tipo'])) { $tipo = intval($_REQUEST['tipo']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');


//***********************
//***********************
if ($accion =="del") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB101";

    $sql_detalle="";
    $sql_or="";

    if (isset($_REQUEST['idet'])) {
        foreach ($_REQUEST['idet'] as $key => $value) {          
          $sql_detalle.=$sql_or."id=$value";
          $sql_or=" or ";
        } 
     }

    $tmpsql="DELETE FROM cotizacion_detalle WHERE ($sql_detalle) and id_maestro=$cid ";

    $result = sql_delete($tmpsql);

  if ($result!=false){

    $stud_arr[0]["pcode"] = 1;
      $stud_arr[0]["pmsg"] ="Registros Eliminados";
      $stud_arr[0]["pcid"] = $cid;
  }

  salida_json($stud_arr);
    exit;

}




//***********************
//***********************

if ($accion =="agr_g") {// guardar cotizacion repuesto

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

     //Validar
	$verror="";

    if ($verror=="") {

        $sqldetalle = array();

        $i=0;
    if (isset($_REQUEST['det_codigo'])){
        foreach( $_REQUEST['det_codigo'] as $det_codigo ) {

          unset($tmpresult,$producto,$det_id,$codproducto,$tipoproducto,$cantproducto,$costoproducto,$precioproducto, $totalproducto,$codprov);
          $codproducto=ceroif_nulo(filter_var( $det_codigo, FILTER_SANITIZE_NUMBER_INT));
          $codprov=ceroif_nulo(filter_var( $_REQUEST['det_prov'][$i], FILTER_SANITIZE_NUMBER_INT));
          $cantproducto=ceroif_nulo(filter_var( $_REQUEST['det_cant'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
          $nota=$_REQUEST['det_nota'][$i] ;
          $costoproducto=ceroif_nulo(filter_var( $_REQUEST['det_pc'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
          $precioproducto=ceroif_nulo(filter_var( $_REQUEST['det_pv'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
  


          if (es_nulo($codproducto)) {
            $verror.=  "No se encontro el producto $det_codigo <br>";
          } else {

            $tmpresult = $conn -> query("SELECT * FROM producto
            WHERE habilitado=1 and id=".$codproducto."
            LIMIT 1");

             if ($tmpresult->num_rows <= 0) { $verror.= "No se encontro el producto $det_codigo <br>";} 
             else {
               $producto = $tmpresult -> fetch_assoc() ;


                    
               //**validar inventario?
    
             //Calculos

                 
              $tmpsql="";$tmpsql2="";
   
 
           

                  $tmpsql="INSERT INTO cotizacion_detalle SET id_maestro=$cid ";
                  $tmpsql.=",id_usuario=".GetSQLValue($_SESSION['usuario_id'],"int");
                  $tmpsql.=",fecha=now(),";

               
                $tmpsql.="id_producto=".GetSQLValue($codproducto,"int");

                if(isset($cantproducto)){
                    $tmpsql.=",cantidad=".GetSQLValue($cantproducto,"double");
                }
                if(isset($costoproducto)){
                    $tmpsql.=",precio_costo=".GetSQLValue($costoproducto,"double");
                    }
                if(isset($precioproducto)){
                    $tmpsql.=",precio_venta=".GetSQLValue($precioproducto,"double");
                }

                $tmpsql.=",id_proveedor=".GetSQLValue($codprov,"int");

                $tmpsql.=",producto_nota=".GetSQLValue($nota,"text"); 
         
                $tmpsql.=",producto_tipo=".GetSQLValue($producto['tipo'],"int");
                $tmpsql.=",producto_codigoalterno=".GetSQLValue($producto['codigo_alterno'],"text");
                $tmpsql.=",producto_nombre=".GetSQLValue($producto['nombre'],"text");     



                
        
                array_push($sqldetalle, $tmpsql.$tmpsql2);
             
     
    
            
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


//***********************
//***********************

if ($accion =="agr") { //Agregar servicio repuesto

    ?>
     <form id="formagr_serv" name="forma_cambiarcmp" class="needs-validation" novalidate>
    <fieldset > 
        <input  name="cid"  type="hidden" value="<?php echo $cid; ?>" >
        <input  name="tipo"  type="hidden" value="<?php echo $tipo; ?>" >

    <div class="row"> 
        <div class="table-responsive">  
            <table id="cotizacion_agr_detalle_tabla" class="table table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                <th>Codigo</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Costo</th>
                <th>Precio Venta</th>
                
                <th>Proveedor</th>
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
          <div id="cotizacion_botones_agregar_guardar" class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top oculto ">
          
             <a href="#" onclick="procesar_cotizacion_agregar('cotizacion_mant_repuesto.php?a=agr_g','formagr_serv','',<?php echo $tipo; ?>); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a> 
          

          </div>
          </div>
        </div>

        <hr>
    <?php


//Agregar nuevo repuesto
echo '<div id="agrrepuestos" class=" ">
    <div  class="row bg-light ">
      <div class="col-md-6">'.campo("cotizacion_agr_cotizacion","Codigo o Descripcion del repuesto",'select2ajax','','class="form-control" style="width: 100%" ','','get.php?a=3&t='.$tipo.'&ff=1','').'</div> 
      <div class="col-md-2 mt-2">'.campo("cotizacion_agr_cantidad","Cantidad",'number','1','class="form-control" ','','','','').'</div>
      
    </div>
    <div  class="row bg-light ">
      <div class="col-md-6">'.campo("cotizacion_agr_proveedor","Proveedor",'select2ajax','','class="form-control" style="width: 100%" ','','get.php?a=4&t=1','').'</div> 
      
      <div class="col-md-2 mt-2"><a class="btn btn-md btn-success " href="#" onclick="cotizacion_agregar_solicitud('.$tipo.'); return false;"><i class="fa fa-plus"></i></a></div>
    </div>
    
    </div>
    ';
?>
<script>
 var vlin=1;
    function cotizacion_agregar_solicitud(tipo){
        var continuar=true;
    
    
    
            if ($('#cotizacion_agr_cotizacion').val() == "" || $('#cotizacion_agr_cotizacion').val() == null){
              mymodal('warning','Error','Debe ingresar el servicio o repuesto');
              continuar=false;
            } else {
              var data=$('#cotizacion_agr_cotizacion').select2('data')[0];
              var cantidad=parseFloat($('#cotizacion_agr_cantidad').val() );    
            }
    
            if ($('#cotizacion_agr_proveedor').val() == "" || $('#cotizacion_agr_proveedor').val() == null){
              mymodal('warning','Error','Debe ingresar el Proveedor');
              continuar=false;
              
            } else {
              var data2=$('#cotizacion_agr_proveedor').select2('data')[0];  
            }
    
    
        if (continuar==true) {
              
                vlin=vlin+1;
    
              
                var salidatxt='';
            
                salidatxt+='<tr id="vdetli_'+vlin+'"  data-cod="'+data.id+'" >';              
                salidatxt+='<td><span class="badge badge-secondary">'+data.alt+'</span>';
                salidatxt+='<input name="det_codigo[]"  value="'+data.id+'"  type="hidden"  />'; 
                salidatxt+='<input name="det_id[]"  value=""  type="hidden"  />'; 
                salidatxt+='</td>';
                salidatxt+='<td>'+data.desc+'</td> ';
                salidatxt+='<td><input name="det_cant[]" class="detcampo detancho detcant" onchange="cotizacion_totlinea(this);" value="'+cantidad+'"  type="number"  /></td>';	
                salidatxt+='<td><input name="det_pc[]" class="detcampo detancho detprecio"  value="'+data.pc+'"  type="number"  /></td> ';
                salidatxt+='<td><input name="det_pv[]" class="detcampo detancho detprecio"  value="'+data.pv+'"  type="number"  /></td> ';
                salidatxt+='<td>'+data2.desc+'<input name="det_prov[]"  value="'+data2.id+'"  type="hidden"  /></td> ';
               
                salidatxt+='<td><input name="det_nota[]" class="detcampo detancho150 "   value=""  type="text" autocomplete="off"  /></td> ';
  
             salidatxt+='<td><a class="btn btn-sm btn-default  d-print-none " href="#" onclick="cotizacion_borrar_linea(this); return false;"> <i class="fa fa-trash"></i></a></td> ';
              
              salidatxt+='</tr>';
    
                
    
                if (tipo==2) { //repuesto
    
                }
    
                if (tipo==3) { //servicio
    
                }
    
                $('#cotizacion_agr_detalle_tabla > tbody:last-child').append(salidatxt);
                $('#cotizacion_agr_cantidad').val('1');
                $('#cotizacion_agr_cotizacion').val(null).trigger('change');
                $('#cotizacion_agr_proveedor').val(null).trigger('change');

                $('#cotizacion_botones_agregar_guardar').show();
    
              // calcular_totales();
          }
    }
    
    
    function cotizacion_borrar_linea(linea) {
    
    
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

    function procesar_cotizacion_agregar(url,forma,adicional,tipo){
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
                        get_box('tbody_cotizacion_rep','cotizacion_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
                    }

                    if (tipo==3) { //servicio
                        get_box('tbody_cotizacion_det','cotizacion_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
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

?>