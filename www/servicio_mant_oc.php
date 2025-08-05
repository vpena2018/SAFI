<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
//if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}
if (isset($_REQUEST['tipo'])) { $tipo = intval($_REQUEST['tipo']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');
pagina_permiso(42);

function  cargar_detalle_oc($cid,$tipo,$sql_detalle){
     $lin=1;
  
    $servicios_result = sql_select("SELECT * FROM servicio_detalle where  ($sql_detalle) and id_servicio=$cid  and  estado<>4 order by producto_tipo desc,id ");
  
  
        if ($servicios_result->num_rows > 0) { 
          while ($detalle = $servicios_result -> fetch_assoc()) {
  
            echo agregar_servicio_detalle_oc($lin,$detalle["precio_venta"],$detalle["precio_costo"],$detalle["id_producto"],$detalle["producto_codigoalterno"],$detalle["producto_nombre"],$detalle["id"],$detalle["cantidad"],$detalle["producto_nota"],$detalle["estado"]);
                  
            $lin++;
          
            }} else {
                echo '<tr><td colspan="5" >No se encontraron registros que puedan ser modificados<td> </tr>';
            }
    } 
  
  
  function agregar_servicio_detalle_oc($vlin,$data_pv,$data_pc,$data_id,$data_alt,$data_desc,$det_id,$cantidad,$nota,$estado) {
    $salidatxt="";
    $devueltoclass="";
    if ($estado==4) {$devueltoclass="texto-borrado";}
  
    $salidatxt.='<tr id="vdetli_'.$vlin.'" class="'.$devueltoclass.'"  data-cod="'.$data_id.'"  data-detid="'.$det_id.'" data-acc="">';
    // $salidatxt.='<li id="vdetli_'+vlin+'" class="list-group-item list-group-item-action d-sm-flex justify-content-between align-items-center" data-pv="'+data.pv+'" data-pc="'+data.pc+'" data-cod="'+data.id+'">';
  //  $salidatxt.='<td> <input class="serv_chk_dlg" type="checkbox" value="'.$det_id.'" name="det_id_chk[]"></td> ';
    
   $salidatxt.='<td><span class="badge badge-secondary">'.$data_alt.'</span>';
   // $salidatxt.='<input name="det_codigo[]"  value="'.$data_id.'"  type="hidden"  />'; 
    // $salidatxt.='<input name="det_tipo[]"  value="'+tipo+'"  type="hidden"  />'; 
    $salidatxt.='<input name="det_id[]"  value="'.$det_id.'"  type="hidden"  />'; 
    // $salidatxt.='<input name="det_acc[]"  value=""  type="hidden"  />';
    $salidatxt.='</td>';
    $salidatxt.='<td>'.$data_desc.'</td> ';

    $salidatxt.='<td><input name="det_cant[]" class="detcampo detancho detcant"  value="'.$cantidad.'" min="1" max="'.$cantidad.'"  type="number" required  /></td>';	
    $salidatxt.='<td><input name="det_pc[]" class="detcampo detancho detprecio"  value="'.$data_pc.'"  type="number"  /></td> ';
    //$salidatxt.='<td><input name="det_pv[]" class="detcampo detancho detprecio"  value="'.$data_pv.'"  type="number"  /></td> ';
    $salidatxt.='<td><input name="det_nota[]" class="detcampo detancho150 "   value="'.$nota.'"  type="text" autocomplete="off"  /></td> ';
    
    // $salidatxt.='<td>'.$cantidad.'</td>';	
    // $salidatxt.='<td>'.formato_numero($data_pc,2).'</td> ';
    //$salidatxt.='<td>'.$nota.'</td>';	

    $salidatxt.='<td>'.get_servicio_detalle_estado($estado).'</td>';	
    // $salidatxt.='<td>'.formato_numero($data_pv,2).'</td> ';
    // $salidatxt.='<td>'.formato_numero($data_pc,2).'</td> ';
  
     // $salidatxt.='<td><span class="badge badge-secondary dettotal">'+(parseFloat(data.pv)*cantidad)+'</span></td> ';
    
     $salidatxt.='</tr>';
  
     return $salidatxt;
  }



if ($accion =="ocompra") { //crear ORDEN COMPRA

    if (isset($_REQUEST['g'])) { //Guardar orden compra

        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] ="ERROR DB101";


        if (isset($_REQUEST['id_entidad'])) { $id_entidad = intval($_REQUEST['id_entidad']); } else	{$id_entidad ="" ;}
    
   //Validar
   $verror="";

   if (es_nulo($id_entidad)) { $verror="Debe seleccionar el proveedor";}

   if ($verror=="") {

       $sqldetalle = array();

       $i=0;
   if (isset($_REQUEST['det_id'])){
       foreach( $_REQUEST['det_id'] as $det_id ) {

         unset($tmpresult,$producto,$codproducto,$tipoproducto,$cantproducto,$costoproducto,$precioproducto, $totalproducto);
         $detalleid=ceroif_nulo(filter_var( $det_id, FILTER_SANITIZE_NUMBER_INT));         
         $cantproducto=ceroif_nulo(filter_var( $_REQUEST['det_cant'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
         $costoproducto=ceroif_nulo(filter_var( $_REQUEST['det_pc'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
         $nota=$_REQUEST['det_nota'][$i] ;


         if (es_nulo($detalleid)) {
           $verror.=  "No se encontro el servicio <br>";
         } else {

           $tmpresult = $conn -> query("SELECT * FROM servicio_detalle
           WHERE id=".$detalleid."
           LIMIT 1");

            if ($tmpresult->num_rows <= 0) { $verror.= "No se encontro el producto  <br>";} 
            else {
              $producto = $tmpresult -> fetch_assoc() ;
  
            //validar
            if ($cantproducto<1 or $cantproducto>$producto['cantidad']) { $verror="La cantidad a solicitar de ".$producto['producto_codigoalterno'].", excede la cantidad autorizada";}

            //Calculos

             
                
             $tmpsql="";$tmpsql2="";
  
                 $tmpsql="INSERT INTO orden_compra_detalle SET id_orden=:codigo ";
                 $tmpsql.=",id_usuario=".GetSQLValue($_SESSION['usuario_id'],"int");
                 $tmpsql.=",fecha=now()";

               //   precio_venta, cobrable, estado, , id_ocobro

               $tmpsql.=",id_producto=".GetSQLValue($producto['id_producto'],"int");
               $tmpsql.=",id_servicio=".GetSQLValue($producto['id_servicio'],"int");

               if(isset($cantproducto)){
               $tmpsql.=",cantidad=".GetSQLValue($cantproducto,"double");
               }
               if(isset($costoproducto)){
                $tmpsql.=",precio_costo=".GetSQLValue($costoproducto,"double");
                }
               $tmpsql.=",producto_nota=".GetSQLValue($nota,"text"); 
        
               $tmpsql.=",producto_tipo=".GetSQLValue($producto['producto_tipo'],"int");
               $tmpsql.=",producto_codigoalterno=".GetSQLValue($producto['producto_codigoalterno'],"text");
               $tmpsql.=",producto_nombre=".GetSQLValue($producto['producto_nombre'],"text");     

               array_push($sqldetalle, $tmpsql.$tmpsql2);

               //actualizar
               $tmpsqlcosto="";
               if(isset($costoproducto)){
                $tmpsqlcosto=",precio_costo=".GetSQLValue($costoproducto,"double");
                }
               $tmpsql="UPDATE servicio_detalle SET id_oc=:codigo $tmpsqlcosto  WHERE id=".$detalleid." LIMIT 1";

               array_push($sqldetalle, $tmpsql);
                    
         }
       }
         
         $i++;

       }
   }


   if ($verror=="") {

    $sqlcampos="";
     //Crear nuevo            
     $sqlcampos.= "  fecha =NOW(), hora =NOW()"; 
     $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
     $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
     $sqlcampos.= " , id_estado =1";
     $numero_oc= get_dato_sql('orden_compra',"IFNULL((max(numero)+1),1)"," ");//where id_tienda=".$_SESSION['tienda_id']
     $sqlcampos.= " , numero =".GetSQLValue($numero_oc,"int"); 

      $sqlcampos.= " , id_entidad =".GetSQLValue($id_entidad,"int");
      $sqlcampos.= " , id_servicio =".GetSQLValue($cid,"int");

      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      
 
    $sql="insert into orden_compra set ".$sqlcampos." ";

    $result = sql_insert($sql);
    $ocid=$result; //last insert id 


    if ($result!=false){

        $hayerrores=false;
        foreach( $sqldetalle as $sqldet ) {
            $result=sql_update(str_replace(":codigo", $ocid, $sqldet));
            
            if ($result==false){ $hayerrores=true;}
            }	

        //  historial
        sql_insert("INSERT INTO servicio_historial_estado
        (id_servicio,  id_usuario,  nombre, fecha, observaciones)
        VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Crear Orden de Compra', NOW(), 'Orden #$numero_oc')");

       
        if ($accion =="ocompra" ) {
            // BUG: SAP 
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

        } 

     }  else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] =$verror;
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




    ?>
     <form id="formagr_oc" name="forma_cambiarcmp" class="needs-validation" >
    <fieldset > 
        <input  name="cid"  type="hidden" value="<?php echo $cid; ?>" >
        <input  name="tipo"  type="hidden" value="<?php echo $tipo; ?>" >


<div class="row">
<div class="col">
     <h6 class="ml-4 text-primary">Seleccione los Repuestos/Servicios para incluir en la orden</h6>
     </div> 
</div>
  <div class="row">
  <div class="col-md-6">
  <?php echo campo("id_entidad","Proveedor",'select2ajax','',' ',' required ','get.php?a=2&t=2','');  ?>
  </div> 
</div>      
        <div class="row"> 
     
     
  <div class="table-responsive">  
      <table id="detalleul_servicio_dlg" class="table table-striped table-hover" style="width:100%">
        <thead>
          <tr>
           <!-- <th><input type="checkbox"  onchange="servicio_marcar_todos(this,'detalleul_servicio_dlg'); "  ></th> -->
            <th>Codigo</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Costo Unitario</th>
            <th>Nota</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody >
         
      
      <?php

        $sql_detalle="";
        $sql_or="";

        if (isset($_REQUEST['idet'])) {
            foreach ($_REQUEST['idet'] as $key => $value) {          
            $sql_detalle.=$sql_or."id=$value";
            $sql_or=" or ";
            } 
        }

      cargar_detalle_oc($cid,$tipo,$sql_detalle); 
      ?>
      <!-- </ul> -->

      </tbody>
 
 </table>

   
    
  </div>
  </div>


    </fieldset> 
    </form>

    <div class="row">
          <div class="col">
          <div id="servicio_botones_agregar_guardar" class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
             <a href="#" onclick="procesar_servicio_oc('servicio_mant_oc.php?a=<?php echo $accion?>&g=1','formagr_oc','',<?php echo $tipo; ?>); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Crear Orden</a> 
          

          </div>
          </div>
        </div>

        <hr>
    <?php



?>
<script>
 

function procesar_servicio_oc(url,forma,adicional,tipo){
{

//   var validado=false;
//   var forms = document.getElementById(forma);
//   var validation = Array.prototype.filter.call(forms, function(form) {
						       					 
//         if (form.checkValidity() === false) {
//             mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
//         } else {validado=true;}
//         form.classList.add('was-validated');
        
//     });
          
// BUG no valida
// if(validado==true)
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

                   get_box('tbody_servicio_rep','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
                   get_box('tbody_servicio_det','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
                             
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
}

</script>

<?php
    exit;

}
?>  
