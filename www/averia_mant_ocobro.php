<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
//if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}
if (isset($_REQUEST['tipo'])) { $tipo = intval($_REQUEST['tipo']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');
//pagina_permiso(43);

function  cargar_detalle_ocobro($cid,$tipo,$sql_detalle){
     $lin=1;
     $salida="";
     $actividad_noautorizada=false;
  
    $averias_result = sql_select("SELECT * FROM averia_detalle where  ($sql_detalle) and id_maestro=$cid  and  estado<>4 order by producto_tipo desc,id ");
  
  
        if ($averias_result->num_rows > 0) { 
          while ($detalle = $averias_result -> fetch_assoc()) {
  
            $salida.= agregar_averia_detalle_ocobro($lin,$detalle["precio_venta"],$detalle["precio_costo"],$detalle["id_producto"],$detalle["producto_codigoalterno"],$detalle["producto_nombre"],$detalle["id"],$detalle["cantidad"],$detalle["producto_nota"],$detalle["estado"]);
                  
            $lin++;
            if ($detalle["estado"]==1) {$actividad_noautorizada=true;}
            }
            if ($actividad_noautorizada==true) {
              $salida='<tr><td colspan="6" >Algunas actividades/repuestos NO estan Autorizados, El estado debe estar al menos autorizado para poder continuar<td> </tr>';
            }
            echo $salida;

          } else {
                echo '<tr><td colspan="6" >No se encontraron registros que puedan ser modificados<td> </tr>';
            }
    } 
  
  
  function agregar_averia_detalle_ocobro($vlin,$data_pv,$data_pc,$data_id,$data_alt,$data_desc,$det_id,$cantidad,$nota,$estado) {
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
    $salidatxt.='<input name="det_alt[]"  value="'.$data_alt.'"  type="hidden"  />';

    // $salidatxt.='<input name="det_acc[]"  value=""  type="hidden"  />';
    $salidatxt.='</td>';

    $salidatxt.='<td style="min-width:300px;">'.campo("det_codventa[]","Codigo Venta",'select2ajax','','class="form-control" style="width: 100%" ','','get.php?a=3&t=4&ff=1','',$vlin).'</td>';

    $salidatxt.='<td style="min-width:300px;">'.$data_desc.'</td> ';

    $salidatxt.='<td><input name="det_cant[]" class="detcampo detancho detcant"  value="'.$cantidad.'" min="1" max="'.$cantidad.'"  type="number" required  /></td>';	
    $salidatxt.='<td><input name="det_pc[]" class="detcampo detancho detprecio"  value="'.$data_pc.'" onchange="calcular_pv(this);" type="number"  /></td> ';
    $salidatxt.='<td><input name="det_pv[]" class="detcampo detancho detprecio"  value="'.$data_pv.'"  type="number"  /></td> ';
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



if ($accion =="ocobro") { //crear ORDEN COBRO

    if (isset($_REQUEST['g'])) { //Guardar orden cobro

        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] ="ERROR DB101";


        if (isset($_REQUEST['id_entidad'])) { $id_entidad = intval($_REQUEST['id_entidad']); } else	{$id_entidad ="" ;}
    
   //Validar
   $verror="";

   if (es_nulo($id_entidad)) { $verror="Debe seleccionar el proveedor";}
   if (!isset($_REQUEST['det_id'])) { $verror="Debe contener al menos una Actividad o repuesto";}

   if ($verror=="") {

       $sqldetalle = array();

       $i=0;
   if (isset($_REQUEST['det_id'])){
       foreach( $_REQUEST['det_id'] as $det_id ) {

         unset($tmpresult,$producto,$codproducto,$tipoproducto,$cantproducto,$costoproducto,$precioproducto, $totalproducto,$codigoventa);
         $detalleid=ceroif_nulo(filter_var( $det_id, FILTER_SANITIZE_NUMBER_INT));         
         $cantproducto=ceroif_nulo(filter_var( $_REQUEST['det_cant'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
         $costoproducto=ceroif_nulo(filter_var( $_REQUEST['det_pc'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
         $precioproducto=ceroif_nulo(filter_var( $_REQUEST['det_pv'][$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
         $nota=$_REQUEST['det_nota'][$i] ;
         $codigoventa=ceroif_nulo(filter_var( $_REQUEST['det_codventa'][$i], FILTER_SANITIZE_NUMBER_INT));         

         if (es_nulo($detalleid)) {
           $verror.=  "No se encontro la averia <br>";
         } else {

           $tmpresult = $conn -> query("SELECT * FROM averia_detalle
           WHERE id=".$detalleid."
           LIMIT 1");

            if ($tmpresult->num_rows <= 0) { $verror.= "No se encontro el producto  <br>";} 
            else {
              $producto = $tmpresult -> fetch_assoc() ;
  
            //validar
            if ($cantproducto<1 or $cantproducto>$producto['cantidad']) { $verror="La cantidad a solicitar de ".$producto['producto_codigoalterno'].", excede la cantidad autorizada";}

            //Calculos

             
                
             $tmpsql="";$tmpsql2="";
  
                 $tmpsql="INSERT INTO orden_cobro_detalle SET id_orden=:codigo ";
                 $tmpsql.=",id_usuario=".GetSQLValue($_SESSION['usuario_id'],"int");
                 $tmpsql.=",fecha=now()";

               //   precio_venta, cobrable, estado, , id_ocobro

               $tmpsql.=",id_producto=".GetSQLValue( $codigoventa,"int");//.GetSQLValue($producto['id_producto'],"int");
               $tmpsql.=",id_averia=".GetSQLValue($producto['id_maestro'],"int");

               if(isset($cantproducto)){
               $tmpsql.=",cantidad=".GetSQLValue($cantproducto,"double");
               }
               if(isset($costoproducto)){
                $tmpsql.=",precio_costo=".GetSQLValue($costoproducto,"double");
                }
                if(isset($precioproducto)){
                $tmpsql.=",precio_venta=".GetSQLValue($precioproducto,"double");
                }
                
               $tmpsql.=",producto_nota=".GetSQLValue($nota,"text"); 
        
               $tmpsql.=",producto_tipo=".GetSQLValue($producto['producto_tipo'],"int");
               
               $producto_codigoalterno="";
               $producto_nombre="";
               $producto_codventa_result = sql_select("SELECT codigo_alterno,nombre FROM producto where id=$codigoventa limit 1");
               if ($producto_codventa_result->num_rows > 0) { 
                 $producto_codventa = $producto_codventa_result -> fetch_assoc();
                 $producto_codigoalterno=$producto_codventa["codigo_alterno"];
                 // no usar descripcion del codigo a facturar*** $producto_nombre=$producto_codventa["nombre"];
               }
               $producto_nombre=$producto['producto_nombre'];

               $tmpsql.=",producto_codigoalterno=".GetSQLValue($producto_codigoalterno,"text");//.GetSQLValue($producto['producto_codigoalterno'],"text");
               $tmpsql.=",producto_nombre=".GetSQLValue($producto_nombre,"text");  //.GetSQLValue($producto['producto_nombre'],"text");   

               array_push($sqldetalle, $tmpsql.$tmpsql2);

               //actualizar
               $tmpsql="UPDATE averia_detalle SET id_ocobro=:codigo WHERE id=".$detalleid." LIMIT 1";

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
     $numero_oc= get_dato_sql('orden_cobro',"IFNULL((max(numero)+1),1)"," "); //where id_tienda=".$_SESSION['tienda_id']
     $sqlcampos.= " , numero =".GetSQLValue($numero_oc,"int"); 

      $sqlcampos.= " , id_entidad =".GetSQLValue($id_entidad,"int");
      $sqlcampos.= " , id_averia =".GetSQLValue($cid,"int");

      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      if (isset($_REQUEST["codigo_gastos_admon"])) { $sqlcampos.= " , codigo_gastos_admon =".GetSQLValue($_REQUEST["codigo_gastos_admon"],"text"); } 
      
 
    $sql="insert into orden_cobro set ".$sqlcampos." ";

    $result = sql_insert($sql);
    $ocid=$result; //last insert id 


    if ($result!=false){

   


        $hayerrores=false;
        foreach( $sqldetalle as $sqldet ) {
            $result=sql_update(str_replace(":codigo", $ocid, $sqldet));
            
            if ($result==false){ $hayerrores=true;}
            }	


        //  historial
        sql_insert("INSERT INTO averia_historial_estado
        (id_servicio,  id_usuario,  nombre, fecha, observaciones)
        VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Crear Orden de Cobro', NOW(), 'Orden #$numero_oc')");

       
        if ($accion =="ocobro" ) {
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
  <?php 
     $cliente_result =sql_select("SELECT entidad.id,entidad.nombre FROM averia LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id) WHERE averia.id=$cid LIMIT 1");
      
     $clientenombre="";
     $clietecod="";
     if ($cliente_result->num_rows > 0) { 
        $clientedato = $cliente_result -> fetch_assoc();
        $clientenombre=$clientedato["nombre"];
        $clietecod=$clientedato["id"];
        
     }
     echo campo("id_entidad","Cliente",'select2ajax',$clietecod,' ',' required ','get.php?a=2&t=2',$clientenombre); 
 
     ?>
  </div> 
</div>      
        <div class="row"> 
     
     
  <div class="table-responsive">  
      <table id="detalleul_averia_dlg" class="table table-striped table-hover" style="width:100%">
        <thead>
          <tr>
           <!-- <th><input type="checkbox"  onchange="averia_marcar_todos(this,'detalleul_averia_dlg'); "  ></th> -->
            <th>Codigo</th>
            <th>Codigo Venta</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Costo Unitario</th>
            <th>Precio Venta Unitario</th>
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

        cargar_detalle_ocobro($cid,$tipo,$sql_detalle); 
      ?>
      <!-- </ul> -->

      </tbody>
 
 </table>

   
    
  </div>
  </div>


    </fieldset> 
    </form>

    <div class="row">
      <div class="col-6">
        <?php 
        $tipo_av=get_dato_sql("averia","id_tipo"," where id=$cid");
        if ($tipo_av==1 or $tipo_av==5){
        echo campo("codigo_gastos_admon","Codigo Venta: Gastos Administracion",'select2ajax','','class="form-control" style="width: 100%" ','','get.php?a=3&t=4&ff=1','','');
       } else {
         echo '<input id="codigo_gastos_admon" name="codigo_gastos_admon"  value=""  type="hidden"  />';
       }
       ?>
      </div>
    </div>

    <div class="row">
          <div class="col">
          <div id="averia_botones_agregar_guardar" class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
             <a href="#" onclick="procesar_averia_oc('averia_mant_ocobro.php?a=<?php echo $accion?>&g=1','formagr_oc','',<?php echo $tipo; ?>); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Crear Orden</a> 
          

          </div>
          </div>
        </div>

        <hr>
    <?php



?>
<script>
 

function procesar_averia_oc(url,forma,adicional,tipo){
{

   var validado=true;
//   var forms = document.getElementById(forma);
//   var validation = Array.prototype.filter.call(forms, function(form) {
						       					 
//         if (form.checkValidity() === false) {
//             mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
//         } else {validado=true;}
//         form.classList.add('was-validated');
        
//     });
          
$('#formagr_oc *').filter(":input[name='det_codventa[]']").each(function () {

          if ($(this).val()==null || $(this).val()==undefined || $(this).val()=='') {
            validado=false;
          }
        });

        var ga='';
        if ($("#codigo_gastos_admon").attr('type') != 'hidden') {         
        
        var campogastos=$('#codigo_gastos_admon').val();
        if (campogastos==null || campogastos==undefined || campogastos=='') {
            validado=false;
          } else {
            var datos=$('#codigo_gastos_admon').select2('data')[0];
            ga=datos.alt;
          }
        } 



 if(validado==true)
{
   
	$("#"+forma+" .xfrm").addClass("disabled");		
	cargando(true); 
		
	var datos=$("#"+forma).serialize();


	 $.post( url+'&codigo_gastos_admon='+ga, datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);

                   get_box('tbody_averia_rep','averia_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
                   get_box('tbody_averia_det','averia_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
                             
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
		
		
	} else {
    mymodal('warning','Error','Debe ingresar los codigos de Venta');
  }

}		
}

</script>

<?php
    exit;

}
?>  
