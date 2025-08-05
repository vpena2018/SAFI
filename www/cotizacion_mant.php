<?php

require_once ('include/framework.php');
pagina_permiso(32);

$puede_modificar=false;
if (tiene_permiso(36)) {$puede_modificar=true;}

function  cargar_detalle($cid,$tipo){
  global $lin;

  $filtro=" and cotizacion_detalle.producto_tipo=$tipo ";
  if ($tipo==3) {
    $filtro=" and (cotizacion_detalle.producto_tipo=$tipo )";
  }

  $cotizacions_result = sql_select("SELECT cotizacion_detalle.* 
  ,entidad.nombre as prov
  FROM cotizacion_detalle 
  LEFT OUTER JOIN entidad ON (cotizacion_detalle.id_proveedor=entidad.id) 
   where cotizacion_detalle.id_maestro=$cid 
   $filtro 
   order by cotizacion_detalle.id ");


      if ($cotizacions_result->num_rows > 0) { 
        while ($detalle = $cotizacions_result -> fetch_assoc()) {

          echo agregar_cotizacion_detalle($lin,$detalle["precio_venta"],$detalle["precio_costo"],$detalle["id_producto"],$detalle["producto_codigoalterno"],$detalle["producto_nombre"],$detalle["id"],$detalle["cantidad"],$detalle["producto_nota"],$detalle["estado"],$detalle["id_oc"],$detalle["id_ocobro"],$detalle["prov"]);
                
          $lin++;
        
          }}
  } 


function agregar_cotizacion_detalle($vlin,$data_pv,$data_pc,$data_id,$data_alt,$data_desc,$det_id,$cantidad,$nota,$estado,$ocompra,$ocobro,$prov) {
  $salidatxt="";
  $devueltoclass="";
  if ($estado==4) {$devueltoclass="texto-borrado";}

  $salidatxt.='<tr id="vdetli_'.$vlin.'" class="'.$devueltoclass.' "  data-cod="'.$data_id.'"  data-detid="'.$det_id.'" data-acc="">';
  // $salidatxt.='<li id="vdetli_'+vlin+'" class="list-group-item list-group-item-action d-sm-flex justify-content-between align-items-center" data-pv="'+data.pv+'" data-pc="'+data.pc+'" data-cod="'+data.id+'">';
  $salidatxt.='<td> <input class="serv_chk d-print-none" type="checkbox" value="'.$det_id.'" name="det_id[]"></td> ';
  
 $salidatxt.='<td><span class="badge badge-secondary">'.$data_alt.'</span>';
 // $salidatxt.='<input name="det_codigo[]"  value="'.$data_id.'"  type="hidden"  />'; 
  // $salidatxt.='<input name="det_tipo[]"  value="'+tipo+'"  type="hidden"  />'; 
  //$salidatxt.='<input name="det_id[]"  value="'.$det_id.'"  type="hidden"  />'; 
  // $salidatxt.='<input name="det_acc[]"  value=""  type="hidden"  />';
  $salidatxt.='</td>';
  $salidatxt.='<td class="txt_small">'.$data_desc.'</td> ';
  $salidatxt.='<td>'.$cantidad.'</td>';	
  
  $salidatxt.='<td>'.formato_numero($data_pc,2).'</td> ';
  $salidatxt.='<td>'.formato_numero($data_pv,2).'</td> ';	
  $salidatxt.='<td class="txt_small">'.$prov.'</td>';	
  $salidatxt.='<td class="txt_small">'.$nota.'</td>';
  

   // $salidatxt.='<td><span class="badge badge-secondary dettotal">'+(parseFloat(data.pv)*cantidad)+'</span></td> ';
   $btn_modificar=' <a href="#" onclick="cotizacion_modificar_linea('.$det_id.'); return false;"><i class="fa fa-edit mr-1 text-secondary"></i></a>';
   
 
   $salidatxt.='<td class="text-nowrap d-print-none">'.$btn_modificar.'</td>';	
   $salidatxt.='</tr>';

   return $salidatxt;
}

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="";}
$cid=0;

$disable_sec1='';
$disable_sec2='';
$disable_sec3='';
$disable_sec32='';
$disable_sec4='';
$disable_sec5='';
$disable_sec6=' readonly';
$disable_sec7='';
$disable_sec8='';

$visible_sec3='';
$visible_sec4='';
$visible_guardar='';



if (isset($_REQUEST['ti'])) { $tipo_insp = $_REQUEST['ti']; } else   {$tipo_insp ="1";}
if (isset($_REQUEST['td'])) { $tipo_doc = $_REQUEST['td']; } else   {$tipo_doc ="1";}
if (isset($_REQUEST['em'])) { $empresa = $_REQUEST['em'];  } else   {$empresa ="0"; }
if (isset($_REQUEST['tv'])) { $tipo_veh = $_REQUEST['tv']; } else   {$tipo_veh ="turismo";}
if (isset($_REQUEST['cv'])) { $codigo_veh = intval($_REQUEST['cv']); } else   {$codigo_veh ="";}
if (isset($_REQUEST['cid'])) { $codigo_insp = intval($_REQUEST['cid']); } else   {$codigo_insp ="0";}
if (isset($_REQUEST['est'])) { $estado_asignar = intval($_REQUEST['est']); } else   {$estado_asignar ="0";}

$elcodigo='';
if (isset($_REQUEST["cid"])) {$elcodigo=intval($_REQUEST["cid"]);}
if (es_nulo($elcodigo)) {$nuevoreg=true;} else {$nuevoreg=false;}

// mostrar detalle de actividades y repuestos    ############################  
if ($accion=="detall") {
  if (isset($_REQUEST["tipo"])) {$eltipo=intval($_REQUEST["tipo"]);}
  cargar_detalle($elcodigo,$eltipo);  
  exit;
}

// Modificar campo    ############################  
if ($accion=="ec") {
      if (isset($_REQUEST['sid'])) { $sid = intval($_REQUEST['sid']); } else	{exit ;}
      if (isset($_REQUEST['nom'])) { $nombre = ($_REQUEST['nom']); } else	{exit ;}
      if (isset($_REQUEST['eti'])) { $etiqueta = ($_REQUEST['eti']); } else	{exit ;}
      if (isset($_REQUEST['val'])) { $valor = intval($_REQUEST['val']); } else	{exit ;}
  ?>
   <form id="forma_cambiarcmp" name="forma_cambiarcmp" class="needs-validation" novalidate>
    <fieldset id="fs_forma"> 
    
    <br><br>
    <?php 
      $salida='';

    

      //ESTADO
      if ($nombre=='estado') {
       
        $result = sql_select("SELECT id_estado
          FROM cotizacion
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                 
                 if (isset($_REQUEST['lk'])) {
                    $salida.= ' <input id="id_estado" name="id_estado"  type="hidden" value="'.$valor.'" > ';   
                    $salida.=  campo('etq_estado', 'Marcar estado como: '.$etiqueta,'label','',' ',' ',''); 
                    $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');

                   
                 } else {
                    $salida.= campo('id_estado', $etiqueta,'select2',valores_combobox_db('cotizacion_estado',$row["id_estado"],'nombre',' ','','...'),' ',' required ',''); 
                    $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');

                }
                }
          }
         
   
      } 





      echo $salida;
    ?>
    

    <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <a href="#" onclick="procesar_cotizacion_modificar_cmp('cotizacion_mant.php?a=ec2&sid=<?php echo $sid;?>','forma_cambiarcmp',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
           

          </div>
          </div>
        </div>

    </fieldset>
     </form>

<?php
exit;
} 

if ($accion=="ec2") { //guardar campo modificado
  $stud_arr[0]["pcode"] = 0;
  $stud_arr[0]["pmsg"] ="ERROR";

 
$elcodigo="";
if (isset($_REQUEST["sid"])) {$elcodigo=intval($_REQUEST["sid"]);}


if ($elcodigo<>"") {
  //Campos
  $sqlcampos=""; 
  $sqladicional = array(); 
  


  // ESTADO
  if (isset($_REQUEST["id_estado"]) )
  {
     $sqlcampos.= "  id_estado =".GetSQLValue($_REQUEST["id_estado"],"int");  
     $observaciones="";

    if (isset($_REQUEST["observaciones"])) {
      $observaciones=$conn->real_escape_string($_REQUEST["observaciones"]);
    } 

     
  
           $sqladd1="INSERT INTO cotizacion_historial_estado
           (id_maestro, id_estado, id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".intval($_REQUEST["id_estado"]).", ".$_SESSION['usuario_id'].", 0, 'Modificacion de Estado', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
    

  }



  if ($sqlcampos<>''){
  $sql="update cotizacion  set " . $sqlcampos . " where id=".$elcodigo . ' LIMIT 1';
  $result = sql_update($sql);
  $cid=$elcodigo;

  if ($result!=false){

      // //adicionales
      foreach( $sqladicional as $sqldetadd ) {
        sql_insert($sqldetadd);
        }	

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


    $salida="";
    $sqldetalle = array();
    $Subtotal=0;
    $Subtotal_e=0;
    $Isv=0;	
    $Descuento=0;
    $Total=0;
        

    //Validar
	$verror="";




  if ($verror=="") {

    
      $elcodigo='';
      if (isset($_REQUEST["id"])) {$elcodigo=intval($_REQUEST["id"]);}
      if (es_nulo($elcodigo)) {$nuevoreg=true;} else {$nuevoreg=false;}

      //Campos
      $sqlcampos="";
      $sqlcampos_detalle="";
      if (isset($_REQUEST["id_tipo"])) { $sqlcampos.= "  id_tipo =".GetSQLValue($_REQUEST["id_tipo"],"int"); } 
      if (isset($_REQUEST["id_inspeccion"])) { $sqlcampos.= " , id_inspeccion =".GetSQLValue($_REQUEST["id_inspeccion"],"int"); } 
      if (isset($_REQUEST["id_tipo_revision"])) { $sqlcampos.= " , id_tipo_revision =".GetSQLValue($_REQUEST["id_tipo_revision"],"int"); } 
      if (isset($_REQUEST["tipo"])) { $sqlcampos.= " , tipo =".GetSQLValue($_REQUEST["tipo"],"int"); } 
     
      if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
      if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
      if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      

      
 

      if ($nuevoreg==true){
        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('cotizacion',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into cotizacion set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
    } else {
      //actualizar
         $sql="update cotizacion  set " . $sqlcampos . " where id=".$elcodigo . " LIMIT 1";
         $result = sql_update($sql);
         $cid=$elcodigo;
    }
      

      if ($result!=false){

        // //Detalle
        // foreach( $sqldetalle as $sqldet ) {
        //   sql_update(str_replace(":codigo", $cid, $sqldet));
        //   }	

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
  
} //  fin guardar datos




//*** Leer datos ****
if ($accion=="v" or !es_nulo($codigo_insp)) {



	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT cotizacion.* 
  ,entidad.nombre AS cliente_nombre
  ,producto.nombre AS producto_nombre
  ,cotizacion_estado.nombre AS elestado
  ,cotizacion_tipo.nombre AS eltipo


  FROM cotizacion
  LEFT OUTER JOIN entidad ON (cotizacion.cliente_id=entidad.id)
  LEFT OUTER JOIN producto ON (cotizacion.id_producto =producto.id)
  LEFT OUTER JOIN cotizacion_estado ON (cotizacion.id_estado=cotizacion_estado.id)
  LEFT OUTER JOIN cotizacion_tipo ON (cotizacion.id_tipo=cotizacion_tipo.id)


  
                  
                        where cotizacion.id=$cid limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
			$row = $result -> fetch_assoc(); 
		}
	}

} // fin leer datos





// inicializar  
if (isset($row["id"])) {$id= $row["id"]; } else {$id= $codigo_insp;}
if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= $_SESSION['usuario_id'];}
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= $_SESSION['tienda_id'];}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; $elestado=$row["elestado"]; } else { $id_estado= "1"; $elestado='';} //if($tipo_doc=='salida') {$id_estado= "2";} else {$id_estado= "1";}



if (isset($row["id_inspeccion"])) {$id_inspeccion= $row["id_inspeccion"]; } else {$id_inspeccion= "";}
if (isset($row["id_tipo_revision"])) {$id_tipo_revision= $row["id_tipo_revision"]; $eltiporevision=$row["eltiporevision"]; } else {$id_tipo_revision= ""; $eltiporevision="";}
if (isset($row["id_tipo"])) {$id_tipo= $row["id_tipo"]; $eltipo=$row["eltipo"];} else {$id_tipo= "";$eltipo="";}
if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["tipo"])) {$tipo= $row["tipo"]; } else {$tipo= "";}
if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["numero_alterno"])) {$numero_alterno= $row["numero_alterno"]; } else {$numero_alterno= "";}
if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; $cliente_nombre=$row["cliente_nombre"];} else {$cliente_id= ""; $cliente_nombre="";}

if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}




?>
 <div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="serv_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      <li class="nav-item">
        <a class="nav-link" id="insp_tabfotos" data-toggle="tab" href="#" onclick="serv_cambiartab('nav_fotos');"  role="tab" >Fotos y Documentos Adjuntos</a>       
      </li>
      <li class="nav-item">
        <a class="nav-link " id="insp_tabhistorial" data-toggle="tab" href="#" onclick="serv_cambiartab('nav_historial');"   role="tab"  >Historial</a>
      </li>     
    </ul>   
 </div>

 
<div class="card-body">

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
                         echo campo("estado","Estado",'labelb',$elestado,'',' ',"cotizacion_editarcampo('estado','Estado','$id_estado');"); 
                 ?> 
                  
            </div>
</div>


<div class="tab-content" id="nav-tabContent">
 
<!-- DETALLE  -->
  <div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >
    
      <form id="forma" name="forma" class="needs-validation" novalidate>
      <fieldset id="fs_forma">

        
      <input id="id_inspeccion" name="id_inspeccion" type="hidden" value="<?php echo $id_inspeccion; ?>" >
      <input id="id_estado" name="id_estado" type="hidden" value="<?php echo $id_estado; ?>" >

    
      <input id="id" name="id"  type="hidden" value="<?php echo $id; ?>" >


      <div class="row"> 
            <div class="col-md-6">       
                <?php 
               // echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ',$disable_sec1,'get.php?a=2&t=1',$cliente_nombre);  
               echo campo("cliente_nombre","Cliente",'labelb',$cliente_nombre,' ',$disable_sec1);  
                
               ?> 
               <input id="cliente_id" name="cliente_id"  type="hidden" value="<?php echo $cliente_id; ?>" >   
            </div>

            <div class="col-md-6">       
                <?php 
                $producto_etiqueta="";
                if (!es_nulo($id_producto)) {$producto_etiqueta=get_dato_sql('producto',"concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,''))"," where id=".$id_producto); }  
               // echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ',$disable_sec1,'get.php?a=3&t=1',$producto_etiqueta);
               echo campo("producto","Vehiculo",'labelb',$producto_etiqueta,' ',$disable_sec1);
              
                     ?>  
                     <input id="id_producto" name="id_producto"  type="hidden" value="<?php echo $id_producto; ?>" >        
            </div>      
         
      </div>
    


 
      
      <div class="row"> 
   
            <div class="col-md-6">       
                <?php 
                  
                  echo campo("tipo_mant","Tipo",'labelb',$eltipo,'',$disable_sec1,'');
                   
                   ?>  
                   <input id="id_tipo" name="id_tipo"  type="hidden" value="<?php echo $id_tipo; ?>" >        
            </div>
            <div class="col-md-6">       
                <?php 
                echo campo("kilometraje","Kilometraje",'labelb',$kilometraje,' ',$disable_sec1);
             
                    ?>         
            </div>   

           
      </div>

      <hr>


     
 
   

 
            
            
    <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
        Observaciones
        </div>
        <div class="card-body">   

          <div class="row"> 
            <div class="col-md">     
                <?php
                echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',' rows="5" '.$disable_sec6);
               
                ?>  
              
            </div>
            
            
          </div>

        </div>
      </div>      
    




      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
        Detalle Actividades
        
        <!-- <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Autorizar Servicio','cotizacion_mant_repuesto.php?a=aut&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Autorizar</a> -->
           <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Agregar Servicio','cotizacion_mant_repuesto.php?a=agr&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-plus"></i> Agregar</a>
        </div>
        <div class="card-body"> 

         <?php
            $lin=1;
    
              ?>  

     

          <div class="row"> 
          <div id="cotizacion_detalle_actividad" class="table-responsive">  
              <!-- <ul id="detalleul_cotizacion" class="list-group"> -->
              <table id="detalleul_cotizacion" class="table table-striped table-hover" style="width:100%">
                <thead>
                  <tr>
                   <th><input type="checkbox" class="d-print-none" onchange="cotizacion_marcar_todos(this,'detalleul_cotizacion'); "  ></th>
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
                <tbody id="tbody_cotizacion_det">
                 
              
              <?php
               
             
              if ($nuevoreg==false) { 
                 cargar_detalle($cid,3); 
       
              }
              ?>
              <!-- </ul> -->

              </tbody>
         
         </table>

           
            
          </div>
          </div>

             
         


        </div>
      </div>


      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
           Repuestos 
           <!-- <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Autorizar Repuesto','cotizacion_mant_repuesto.php?a=aut&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Autorizar</a> -->
           <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitar Repuesto','cotizacion_mant_repuesto.php?a=agr&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-plus"></i> Agregar</a>


  
                    </div>
        <div class="card-body">   

    
        
           <div class="row">
           <div id="cotizacion_detalle_repuesto" class="table-responsive">
            <!-- <ul id="detalleul_repuesto" class="list-group"> -->
              
            <table id="detalleul_repuesto" class="table table-striped table-hover" style="width:100%">
            <thead>
                  <tr>
                    <th><input type="checkbox" class="d-print-none"  onchange="cotizacion_marcar_todos(this,'detalleul_repuesto'); "  ></th>
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
                <tbody  id="tbody_cotizacion_rep">
              <?php
               
             
              if ($nuevoreg==false) {  
                cargar_detalle($cid,2); //repuestos
              }
              ?>
              <!-- </ul> -->
              </tbody>
         
         </table>
         
            </div>
            </div>

         

         

        </div>
      </div>


    


      <div class="row"> 
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2  border-top ">
            <span class="mr-2 mb-2 text-muted">Con seleccionados:</span>  
            
            <!-- <a href="#" onclick="cotizacion_modificar_registro('tbody_cotizacion_rep',2); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-pen "></i> Modificar</a> -->

            <a href="#" onclick="cotizacion_borrar_registro('tbody_cotizacion_rep',2); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-trash "></i> Borrar</a>

            
          </div>
          </div>
          </div>


         


      


      
      <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <!-- <a href="#" onclick="procesar_cotizacion('cotizacion_mant.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check <?php echo $visible_guardar; ?>"></i> Guardar</a> -->
           
           <?php
           //BOTONES de ESTADO
           $estado_actualizar="";
           $estado_txt="";
           $addfecha="&lk=1";

           if ($id_estado<2) {
            $estado_actualizar="2";
            $estado_txt="APROBAR";
            $addfecha="&lk=1";
           }

        //    if ($id_estado==3) {
        //     $estado_actualizar="4";
        //     $estado_txt="EN PROCESO";
        //     $addfecha="&lk=1";
        //    }

        //    if ($id_estado>3 and $id_estado<20) {
        //     $estado_actualizar="21";
        //     $estado_txt="REALIZADO";
        //     $addfecha="&lk=1";
        //    }

        //    if ($id_estado==21) {
        //     $estado_actualizar="22";
        //     $estado_txt="COMPLETADA";
        //     $addfecha="&lk=1";
        //    }

           if ( $estado_actualizar<>"") {
    
           ?>
            <a href="#" onclick="cotizacion_editarcampo('estado','<?php echo $estado_txt; ?>','<?php echo $estado_actualizar; ?>','<?php echo $addfecha; ?>'); return false;" class="btn btn-warning mr-2 mb-2 xfrm" ><i class="fa fa-check <?php echo $visible_guardar; ?>"></i> <?php echo $estado_txt; ?></a>
         <?php }    ?>

            <?php if ($nuevoreg==false) { ?>
              <div class="float-right">
                <a href="#" onclick="get_page('pagina','cotizacion_mant.php?a=v&cid='+$('#id').val(),'Cotización',false); return false;" class="btn btn-success mr-2 mb-2 xfrm" ><i class="fa fa-redo-alt"></i> Actualizar</a>

              <a href="cotizacion_pdf.php?pdfcod=<?php echo $id; ?>" target="_blank"  class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir</a>
              </div>
            <?php } ?>

          </div>
          </div>
        </div>

      
        </fieldset>
        </form> 

        <div class="row"><div class="col mt-5 px-3 py-2">          
          <a href="#" onclick="get_page_regresar('pagina','cotizacion_ver.php','Ver Cotizaciones') ;  return false;" class="btn btn-outline-secondary mr-2 mb-2">Regresar</a> 
        </div></div>
  

  </div>

  <!-- FOTOS  -->
  <div class="tab-pane fade " id="nav_fotos" role="tabpanel" ></div>

  <!-- HISTORIAL -->
  <div class="tab-pane fade " id="nav_historial" role="tabpanel" ></div>

  <!-- errores -->
  <div class="tab-pane fade mt-5 mb-5" id="nav_deshabilitado" role="tabpanel" ><div class="alert alert-warning" role="alert">Debe Guardar el documento para poder continuar con esta sección</div></div>
</div>
 






</div><!--  card-body -->


<script>


function serv_cambiartab(eltab) {
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

  if (eltab=='nav_fotos') {
    procesar_cotizacion_foto('nav_fotos');
  }
  if (eltab=='nav_historial') {
    procesar_cotizacion_historial('nav_historial');
  }
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

 


   

}


function procesar_cotizacion(url,forma,adicional){

//   var validado=false;
//   var forms = document.getElementsByClassName('needs-validation');
//   var validation = Array.prototype.filter.call(forms, function(form) {
						       
								 
// 						        if (form.checkValidity() === false) {
// 						          mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
// 						        } else {validado=true;}
// 						        form.classList.add('was-validated');
						       
// 						    });
          

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
				
				// printJS('<?php echo app_host; ?>impr_label.php?cid='+json[0].pcid);

				get_page('pagina','cotizacion_mant.php?a=v&cid='+json[0].pcid,'Cotización',false) ; 
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





function procesar_cotizacion_foto(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var url='cotizacion_fotos.php?cid='+cid+'&pid='+pid ;
 
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

function procesar_cotizacion_historial(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var url='cotizacion_historial.php?cid='+cid+'&pid='+pid ;
 
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





function cotizacion_borrar_registro(objeto,tipo) {

  Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar los registros seleccionados?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
      cotizacion_procesar_selec('cotizacion_mant_repuesto.php?a=del',objeto,tipo);
       
	  }
	})

}

function cotizacion_modificar_registro(objeto,tipo) {

    cotizacion_procesar_selec('cotizacion_mant_repuesto.php?a=mod',objeto,tipo);

}

function cotizacion_modificar_linea(detalle) {


    modalwindow('Editar','cotizacion_mant_detalle.php?a=mod&cid='+$('#id').val()+'&did='+detalle);


}



function cotizacion_totlinea(linea){
		var acctmp=$(linea).closest('tr').data("acc");
		if (acctmp!='D' && acctmp!='I') {
		  var nombreli= $(linea).closest('tr').attr('id');
		  $('#'+nombreli+' input[name="det_acc[]"]').val('U');
      $(linea).closest('tr').data("acc",'U');
		}
	  
		  //	calcular_totales();
		  }	




function cotizacion_editarcampo(nombre,etiqueta,valor,adicional=''){
  modalwindow('Editar','cotizacion_mant.php?a=ec&nom='+encodeURI(nombre)+'&sid='+$('#id').val()+'&eti='+encodeURI(etiqueta)+'&val='+encodeURI(valor)+adicional);


}

function procesar_cotizacion_modificar_cmp(url,forma,adicional){
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
				
        
				get_page('pagina','cotizacion_mant.php?a=v&cid='+json[0].pcid,'Cotización',false) ; 
				mytoast('success',json[0].pmsg,3000) ;
        $('#ModalWindow').modal('hide');
				
				 
			
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




function procesar_forma_cotiza_mod(url,forma,adicional){
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
				
        
		        get_box('tbody_cotizacion_det','cotizacion_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
                get_box('tbody_cotizacion_rep','cotizacion_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
      
                mytoast('success',json[0].pmsg,3000) ;
                 $('#ModalWindow').modal('hide');
				
				 
			
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




function cotizacion_marcar_todos(objeto,tabla){
  $("#"+tabla+" input[type='checkbox']").prop('checked',  $(objeto).prop('checked'));
	// $("#"+tabla+" input[type='checkbox']").checkboxradio();
	// $("#"+tabla+" input[type='checkbox']").checkboxradio("refresh");

}



function cotizacion_procesar_selec(url,objeto,tipo){

  var values=[];
//	$('.'+objeto+' input[type="checkbox"]').each(function() {
	$('.serv_chk').each(function() {
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
				
	
			//	get_page('pagina','cotizacion_mant.php?a=v&cid='+json[0].pcid,'Cotización',false) ; 
			get_box('tbody_cotizacion_det','cotizacion_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
      get_box('tbody_cotizacion_rep','cotizacion_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
      
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