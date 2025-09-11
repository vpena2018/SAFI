<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
if (isset($_REQUEST['did'])) { $did = intval($_REQUEST['did']); } else	{exit ;}
//if (isset($_REQUEST['tipo'])) { $tipo = intval($_REQUEST['tipo']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');

pagina_permiso(94);

//***********************
//***********************
if ($accion =="mod") {

  ?>
  <form id="forma_cotiza_mod" name="forma_cotiza_mod" class="needs-validation" novalidate>
   <fieldset id="fs_forma"> 
   
   <br><br>
   <?php 
     $salida='';

   

      
       $result = sql_select("SELECT averia_detalle.* ,entidad.nombre as prov 
       FROM averia_detalle 
       LEFT OUTER JOIN entidad ON (averia_detalle.id_proveedor=entidad.id) 
       where averia_detalle.id_maestro=$cid   and averia_detalle.id=$did
       limit 1");
           
         if ($result!=false){
             if ($result -> num_rows > 0) { 
                $row = $result -> fetch_assoc() ; 
                
                     
                   $salida.=  campo("d_cod","Codigo o Descripcion del repuesto",'select2ajax',$row['id_producto'],'class="form-control" style="width: 100%" ','','get.php?a=3&t='.$row['producto_tipo'].'&ff=1',$row['producto_nombre']); 
                   $salida.=  campo("d_cant","Cantidad",'number',$row['cantidad'],'class="form-control" ','','','','');
                   $salida.=  campo("d_pc","Costo",'number',$row['precio_costo'],'class="form-control" ','','','','');
                   $salida.=  campo("d_pv","Precio Venta",'number',$row['precio_venta'],'class="form-control" ','','','','');
                   $salida.=  campo("d_prov","Proveedor",'select2ajax',$row['id_proveedor'],'class="form-control" style="width: 100%" ','','get.php?a=4&t=1',$row['prov']);
                   $salida.=  campo("d_nota","Nota",'text',$row['producto_nota'],'class="form-control" ','','','','');

                  
  
               }
         }
        
  
     


     echo $salida;
   ?>
   

   <div class="row">
         <div class="col">
         <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
         
           <a href="#" onclick="procesar_forma_cotiza_mod('averia_mant_detalle.php?a=mod_g&cid=<?php echo $cid;?>&did=<?php echo $did;?>','forma_cotiza_mod',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
          

         </div>
         </div>
       </div>

   </fieldset>
    </form>

<?php
exit;

}





//***********************
//***********************


if ($accion =="mod_g") {

  $verror="";
  

  $tmpresult = $conn -> query("SELECT * FROM producto
  WHERE habilitado=1 and id=".sanear_int($_REQUEST["d_cod"])."
  LIMIT 1");

   if ($tmpresult->num_rows <= 0) {
      $verror.= "No se encontro el producto  <br>";} 
   else {
     $producto = $tmpresult -> fetch_assoc() ;
   }
 
   
  if ($verror=="") {
  $sqlcampos="";
  if (isset($_REQUEST["d_cod"])) { $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["d_cod"],"int"); }
  
  
  $codigo_alterno = $producto['codigo_alterno'];
  
 { $sqlcampos.= " , producto_codigoalterno =".GetSQLValue($producto['codigo_alterno'],"text"); } 
 { $sqlcampos.= " , producto_nombre =".GetSQLValue($producto['nombre'],"text"); } 
 { $sqlcampos.= " , producto_tipo =".GetSQLValue($producto['tipo'],"int"); } 

  if (isset($_REQUEST["d_nota"])) { $sqlcampos.= " , producto_nota =".GetSQLValue($_REQUEST["d_nota"],"text"); } 

  if($codigo_alterno=="DESC AVERIA"){
      $sqlcampos.= " , cantidad =-1";
  }else{
    if (isset($_REQUEST["d_cant"])) { $sqlcampos.= " , cantidad =".GetSQLValue($_REQUEST["d_cant"],"double"); } 
  }

  if (isset($_REQUEST["d_pc"])) { $sqlcampos.= " , precio_costo =".GetSQLValue($_REQUEST["d_pc"],"double"); } 
  if (isset($_REQUEST["d_pv"])) { $sqlcampos.= " , precio_venta =".GetSQLValue($_REQUEST["d_pv"],"double"); } 
  // if (isset($_REQUEST["cobrable"])) { $sqlcampos.= " , cobrable =".GetSQLValue($_REQUEST["cobrable"],"int"); } 
  // if (isset($_REQUEST["estado"])) { $sqlcampos.= " , estado =".GetSQLValue($_REQUEST["estado"],"int"); } 
  // if (isset($_REQUEST["id_oc"])) { $sqlcampos.= " , id_oc =".GetSQLValue($_REQUEST["id_oc"],"int"); } 
  // if (isset($_REQUEST["id_ocobro"])) { $sqlcampos.= " , id_ocobro =".GetSQLValue($_REQUEST["id_ocobro"],"int"); } 
  if (isset($_REQUEST["d_prov"])) { $sqlcampos.= " , id_proveedor =".GetSQLValue($_REQUEST["d_prov"],"int"); } 
  if (isset($_REQUEST["isv"])) { $sqlcampos.= " , isv =".GetSQLValue($_REQUEST["isv"],"double"); } 
  if (isset($_REQUEST["margen"])) { $sqlcampos.= " , margen =".GetSQLValue($_REQUEST["margen"],"double"); } 
  
  
     
  $sql="update averia_detalle set ".$sqlcampos." where id=".$did." and averia_detalle.id_maestro=$cid  limit 1";
  
       //Guardar
    $result = sql_update($sql);
  
    if ($result!=false){
      recalcular_totales_averia($cid );
      
      $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="Guardado";
    }
  
  } else {
    $stud_arr[0]["pcode"] = 0;
      $stud_arr[0]["pmsg"] =$verror;
  }
  
    salida_json($stud_arr);
     exit;
  
  


}


?>