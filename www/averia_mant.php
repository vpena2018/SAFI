<?php


require_once ('include/framework.php');

if (!tiene_permiso(34) and !tiene_permiso(160)) { 
    echo '<div class="card-body">';
    echo'No tiene privilegios para accesar esta función';
    echo '</div>';
    exit;
}

$puede_modificar=false;
if (tiene_permiso(37)) {$puede_modificar=true;}

function  cargar_detalle($cid,$tipo){
  global $lin;

  $filtro=" and averia_detalle.producto_tipo=$tipo ";
  if ($tipo==3) {
    $filtro=" and (averia_detalle.producto_tipo=$tipo )";
  }

  $averias_result = sql_select("SELECT averia_detalle.* 
  ,entidad.nombre as prov
  FROM averia_detalle 
  LEFT OUTER JOIN entidad ON (averia_detalle.id_proveedor=entidad.id) 
   where averia_detalle.id_maestro=$cid 
   $filtro
   order by averia_detalle.id ");


      if ($averias_result->num_rows > 0) { 
         while ($detalle = $averias_result -> fetch_assoc()) {

          echo agregar_averia_detalle($lin,$detalle["precio_venta"],$detalle["precio_costo"],$detalle["id_producto"],$detalle["producto_codigoalterno"],$detalle["producto_nombre"],$detalle["id"],$detalle["cantidad"],$detalle["producto_nota"],$detalle["estado"],$detalle["id_oc"],$detalle["id_ocobro"],$detalle["prov"]);
                
          $lin++;
        
          }}
  } 


function agregar_averia_detalle($vlin,$data_pv,$data_pc,$data_id,$data_alt,$data_desc,$det_id,$cantidad,$nota,$estado,$ocompra,$ocobro,$prov) {
      $salidatxt="";
      $devueltoclass="";   
      $ocultoLinea="";      
      $usuarioTaller=0; 
      $usuarioTaller = get_dato_sql('usuario',"grupo_id"," where id=".$_SESSION['usuario_id']);  
      if ($estado==4) {$devueltoclass="texto-borrado";}
      //Valida si el usuario son de talleres externos
      if ($usuarioTaller==35) {$ocultoLinea="display:none;";}
      
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
      
      $salidatxt.='<td style="'.$ocultoLinea.'">'.formato_numero($data_pc,2).'</td> ';             
      $salidatxt.='<td style="'.$ocultoLinea.'">'.formato_numero($data_pv,2).'</td> ';	
         
      $salidatxt.='<td class="txt_small">'.$prov.'</td>';	
      $salidatxt.='<td class="txt_small">'.$nota.'</td>';
      $salidatxt.='<td>'.get_servicio_detalle_estado($estado).'</td>';	

      // $salidatxt.='<td><span class="badge badge-secondary dettotal">'+(parseFloat(data.pv)*cantidad)+'</span></td> ';
      $btn_compra="";
      $btn_cobro="";

      if (!es_nulo($ocompra)) { if (tiene_permiso(38)) { $btn_compra='<a href="#" onclick="averia_abrir_ocompra('.$ocompra.'); return false;"><i class="fa fa-shopping-cart mr-1 text-secondary"></i></a>';}}
      if (!es_nulo($ocobro)) { if (tiene_permiso(39)) { $btn_cobro=' <a href="#" onclick="averia_abrir_ocobro('.$ocobro.'); return false;"><i class="fa fa-money-bill-alt mr-1 text-secondary"></i></a>';}}
          
      $btn_modificar=' <a href="#" onclick="averia_modificar_linea('.$det_id.'); return false;"><i class="fa fa-edit mr-1 text-secondary"></i></a>';
          
      $salidatxt.='<td class="text-nowrap d-print-none">'.$btn_modificar.$btn_compra.$btn_cobro.'</td>';	
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


      if ($etiqueta == 'REALIZADO') {
        $descuentos= (int) get_dato_sql("averia_detalle","count(*)"," where (desc_aprob <> 1 or desc_aprob IS NULL) and producto_codigoalterno='DESC AVERIA' and id_maestro=".$sid);

        if($descuentos>0)
          {
            echo "<script>
            $('#ModalWindow').modal('hide');
              popupWeb('Descuento Pendiente de aprobacion','Los descuentos se deben aprobar antes de continuar');
            </script>";
            exit; 

        }
      }




      $estado_actual= get_dato_sql('averia',"id_estado"," where id=$sid"); 
      if (intval($estado_actual)>2 and $etiqueta<>'ATENDER'  and $etiqueta<>'REALIZADO' and $etiqueta<>'AUTORIZAR') {
         if (!tiene_permiso(153)) {
          echo "No puede modificar la orden despues de Aprobada";
          exit;
        }
      }
  ?>
   <form id="forma_cambiarcmp" name="forma_cambiarcmp" class="needs-validation" novalidate>
    <fieldset id="fs_forma"> 
    
    <br><br>
    <?php 
      $salida='';

       // Auditadob
      if ($nombre=='auditado'){    
         $salida.= campo("observaciones_adpc","Observaciones ADPC",'textarea','',' ',' rows="5" ');
      }

      //ESTADO
      if ($nombre=='estado') {
       
        $result = sql_select("SELECT id_estado
          FROM averia
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                 
                 if (isset($_REQUEST['lk'])) {
                    $salida.= ' <input id="id_estado" name="id_estado"  type="hidden" value="'.$valor.'" > ';   
                    $salida.=  campo('etq_estado', 'Marcar estado como: '.$etiqueta,'label','',' ',' ',''); 
                    $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');
                   
                 } else {
                    $salida.= campo('id_estado', $etiqueta,'select2',valores_combobox_db('averia_estado',$row["id_estado"],'nombre',' ','','...'),' ',' required ',''); 
                    $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');

                }
                }
          }
         
   
      } 

      //TIPO
      if ($nombre=='id_tipo') {

          $salida.= campo('id_tipo', $etiqueta,'select2',valores_combobox_db('averia_tipo',$valor,'nombre',' ','','...'),' ',' required ',''); 
     
   
      } 


       //CONTACTO
       if ($nombre=='contacto') {

          $salida.= campo('contacto', $etiqueta,'text',$_REQUEST['val'],' ',' required ',''); 

      } 

      if ($nombre=='id_tecnico1') {

        $salida.= campo('id_tecnico1', $etiqueta,'select2',valores_combobox_db('usuario',$valor,'nombre',' where activo=1 and grupo_id=35 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',' required ',''); 
        

    } 

      //TIPO de causa
      if ($nombre=='id_tipo_causa') {

        $salida.= campo('id_tipo_causa', $etiqueta,'select2',valores_combobox_db('averia_tipo_causa',$valor,'nombre',' ','','...'),' ',' required ',''); 
   
 
      } 


       //OBSERVACIONES
       if ($nombre=='observa') {

        $result = sql_select("SELECT observaciones
        FROM averia
        WHERE id=$sid limit 1");
          
        if ($result!=false){
            if ($result -> num_rows > 0) { 
               $row = $result -> fetch_assoc() ; 
        
             $salida.= campo("observa","Observaciones",'textarea',$row['observaciones'],' ',' rows="8" ');
            }}
      } 


       // kilometraje      
       if ($nombre=='kilometraje') {

        pagina_permiso(116);//Modificar encabezao

        $result = sql_select("SELECT kilometraje
          FROM averia
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                $salida.= campo('kilometraje', $etiqueta,'number',$row["kilometraje"],' ',' required ',''); 
               }
          }        
      } 

       //cliente
       if ($nombre=='cliente_id' ) {

        pagina_permiso(116);//Modificar encabezao
       
        $result = sql_select("SELECT id,nombre
          FROM entidad
          WHERE id=$valor limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                $salida.= campo("clientetmp","Cliente",'select2ajax',$valor,' ','','get.php?a=2&t=1',$row["nombre"]);                 
               }
          }
      } 



       //vehiculo , placa y chasis
       if ($nombre=='id_producto') {
        
        pagina_permiso(116); //Modificar encabezao

        $result = sql_select("SELECT averia.id_producto
       
        ,producto.nombre
                  FROM averia
                  LEFT OUTER JOIN producto ON (averia.id_producto=producto.id)
          WHERE averia.id=$sid limit 1");// ,averia.placa,averia.chasis
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ;                     
                 $salida.= campo("id_producto_tmp", "Vehiculo",'select2ajax',$row["id_producto"],' ',' onchange="serv_mant_actualizar_veh();" required ','get.php?a=3&t=1',$row["nombre"]);
                //  $salida.= campo('placa_tmp', 'Placa','text',$row["placa"],' ',' required ',''); 
                //  $salida.= campo('chasis_tmp', 'Chasis','text',$row["chasis"],' ',' required ',''); 
              }
          }
          } 


      //VALORES
      if ($nombre=='valores') {

        $result = sql_select("SELECT coseguro,deducible,seguro
        FROM averia
        WHERE id=$sid limit 1");
          
        if ($result!=false){
            if ($result -> num_rows > 0) { 
               $row = $result -> fetch_assoc() ; 
    
                $salida.= campo('coseguro', 'Coaseguro','number', $row['coseguro'],' ',' required ',''); 
                $salida.= campo('deducible', 'Deducible','number',$row['deducible'],' ',' required ','');
              //  $salida.= campo('seguro', 'Seguro','number',$row['seguro'],' ',' required ',''); 
                $salida.= campo('seguro', '','hidden',$row['seguro'],' ','  ',''); 

            }
          }


      } 

      echo $salida;
    ?>
    

    <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <a href="#" onclick="procesar_averia_modificar_cmp('averia_mant.php?a=ec2&sid=<?php echo $sid;?>','forma_cambiarcmp',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
           

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
  

  //auditado  
  if (isset($_REQUEST["observaciones_adpc"])){
      $sqlcampos.= "  fecha_auditado = now()";
      $sqlcampos.= ", observaciones_adpc =".GetSQLValue($_REQUEST["observaciones_adpc"],"text");  
      $sqlcampos.= ", id_usuario_auditado =".$_SESSION['usuario_id'];  

      ///Historial 
      $sqladd1="INSERT INTO averia_historial_estado
      (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
      VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Revision ADPC', NOW(), '')";
      array_push($sqladicional, $sqladd1);  

 }


  // ESTADO
  if (isset($_REQUEST["id_estado"]) )
  {
     $sqlcampos.= " id_estado =".GetSQLValue($_REQUEST["id_estado"],"int");  
     $observaciones="";

    if (isset($_REQUEST["observaciones"])) {
      $observaciones=$conn->real_escape_string($_REQUEST["observaciones"]);
    } 

     
  
           $sqladd1="INSERT INTO averia_historial_estado
           (id_maestro, id_estado, id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".intval($_REQUEST["id_estado"]).", ".$_SESSION['usuario_id'].", 0, 'Modificacion de Estado', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
    

  }

// TIPO
  if (isset($_REQUEST["id_tipo"]) )
  {
     $sqlcampos.= "  id_tipo =".GetSQLValue($_REQUEST["id_tipo"],"int");  
     $observaciones="";
     
  
           $sqladd1="INSERT INTO averia_historial_estado
           (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Tipo', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
    

  }

// TIPO CAUSA
if (isset($_REQUEST["id_tipo_causa"]) )
{
   $sqlcampos.= "  id_tipo_causa =".GetSQLValue($_REQUEST["id_tipo_causa"],"int");  
   $observaciones="";
   

         $sqladd1="INSERT INTO averia_historial_estado
         (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
         VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Tipo de Causa', NOW(), '$observaciones')";
         array_push($sqladicional, $sqladd1);  

}

  //CONTACTO
  if (isset($_REQUEST["contacto"]) )
  {
     $sqlcampos.= "  contacto =".GetSQLValue($_REQUEST["contacto"],"text");  
     $observaciones=GetSQLValue($_REQUEST["contacto"],"text");
     
  
           $sqladd1="INSERT INTO averia_historial_estado
           (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Contacto', NOW(), $observaciones)";
           array_push($sqladicional, $sqladd1);  
       
    

  }

  //TECNICO
  if (isset($_REQUEST["id_tecnico1"]) )
  {
    $sqlcampos.= "  id_tecnico1 =".GetSQLValue($_REQUEST["id_tecnico1"],"text");  
    $observaciones=GetSQLValue($_REQUEST["id_tecnico1"],"text");      
  
    $sqladd1="INSERT INTO averia_historial_estado
    (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
    VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Contacto', NOW(), $observaciones)";
    array_push($sqladicional, $sqladd1);  
        
  
  }

  //OBSERVACIONES
   if (isset($_REQUEST["observa"]) )
  {
     $sqlcampos.= "  observaciones =".GetSQLValue($_REQUEST["observa"],"text");  
     $observaciones="";
     
  
           $sqladd1="INSERT INTO averia_historial_estado
           (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Observaciones', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
    

  }

//--
  //Vehiculo
  if (isset($_REQUEST["id_producto_tmp"]) )
  {
     $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["id_producto_tmp"],"int"); 
    //  $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa_tmp"],"text"); 
    //  $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis_tmp"],"text");  
     $observaciones="";

 
           $sqladd1="INSERT INTO averia_historial_estado
           (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Modificar Vehiculo', NOW(), '')";
           array_push($sqladicional, $sqladd1);  

  }

  //kilometraje
  if (isset($_REQUEST["kilometraje"]) )
  {
    $sqlcampos.= "  kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int");  
    $observaciones=intval($_REQUEST["kilometraje"]);

          $sqladd1="INSERT INTO averia_historial_estado
          (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
          VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Modificacion de Kilometraje', NOW(), $observaciones)";
          array_push($sqladicional, $sqladd1);  
  }

    //cliente
    if (isset($_REQUEST["clientetmp"]) )
    {
       $sqlcampos.= "  cliente_id =".GetSQLValue($_REQUEST["clientetmp"],"int");  
       $observaciones="";
  
       $nombrecliente=get_dato_sql('entidad','nombre',' where id='.intval($_REQUEST["clientetmp"]));
    
             $sqladd1="INSERT INTO averia_historial_estado
             (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
             VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", ".intval($_REQUEST["clientetmp"]).", 'Modificar cliente', NOW(), '$nombrecliente')";
             array_push($sqladicional, $sqladd1);  
  
    }
    //--


  // VALORES
  if (isset($_REQUEST["deducible"]) )
  {
     $sqlcampos.= "  deducible =".GetSQLValue($_REQUEST["deducible"],"double");  
     $sqlcampos.= "  ,coseguro =".GetSQLValue($_REQUEST["coseguro"],"double"); 
     $sqlcampos.= "  ,seguro =".GetSQLValue($_REQUEST["seguro"],"double"); 
     $observaciones="";
     
  
           $sqladd1="INSERT INTO averia_historial_estado
           (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Valores Seguro', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
    

  }

  if ($sqlcampos<>''){
  $sql="update averia  set " . $sqlcampos . " where id=".$elcodigo . ' LIMIT 1';
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
      if (isset($_REQUEST["id_tecnico1"])) { $sqlcampos.= " , id_tecnico1 =".GetSQLValue($_REQUEST["id_tecnico1"],"int"); } 
      if (isset($_REQUEST["id_tipo_causa"])) { $sqlcampos.= " , id_tipo_causa =".GetSQLValue($_REQUEST["id_tipo_causa"],"int"); } 
      
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      

      
 

      if ($nuevoreg==true){
        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('averia',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into averia set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
    } else {
      //actualizar
         $sql="update averia  set " . $sqlcampos . " where id=".$elcodigo . " LIMIT 1";
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

      $result = sql_select("SELECT averia.* 
      ,entidad.nombre AS cliente_nombre
      ,producto.nombre AS producto_nombre
      ,averia_estado.nombre AS elestado
      ,averia_tipo.nombre AS eltipo
      ,usuario.nombre AS eltecnico1
      ,averia_tipo_causa.nombre AS tipocausa
      FROM averia
      LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
      LEFT OUTER JOIN producto ON (averia.id_producto =producto.id)
      LEFT OUTER JOIN averia_estado ON (averia.id_estado=averia_estado.id)
      LEFT OUTER JOIN averia_tipo ON (averia.id_tipo=averia_tipo.id)                 
      LEFT OUTER JOIN usuario ON (averia.id_tecnico1=usuario.id)
      LEFT OUTER JOIN averia_tipo_causa ON (averia.id_tipo_causa=averia_tipo_causa.id)
      where averia.id=$cid limit 1");

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

if (isset($row["contacto"])) {$contacto= $row["contacto"]; } else {$contacto= "";}
if (isset($row["id_tecnico1"])) {$id_tecnico1= $row["id_tecnico1"]; $eltecnico1=$row["eltecnico1"]; } else {$eltecnico1="";$id_tecnico1= "";}
if (isset($row["id_tipo_causa"])) {$id_tipo_causa= $row["id_tipo_causa"]; $tipocausa=$row["tipocausa"]; } else {$tipocausa="";$id_tipo_causa= "";}

if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}

if (isset($row["numero_inspeccion"])) {$numero_inspeccion= $row["numero_inspeccion"]; } else {$numero_inspeccion= "";}

if (isset($row["coseguro"])) {$coseguro= $row["coseguro"]; } else {$coseguro= "";}
if (isset($row["deducible"])) {$deducible= $row["deducible"]; } else {$deducible= "";}
if (isset($row["seguro"])) {$seguro= $row["seguro"]; } else {$seguro= "";}

if (isset($row["fecha_auditado"])) {$fecha_auditado= $row["fecha_auditado"]; } else {$fecha_auditado= "";}
if (isset($row["id_usuario_auditado"])) {$id_usuario_auditado= $row["id_usuario_auditado"]; } else {$id_usuario_auditado= "";}
if (isset($row["observaciones_adpc"])) {$observaciones_adpc= $row["observaciones_adpc"]; } else {$observaciones_adpc= "";}

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
                 if (!tiene_permiso(153)) {
                    echo campo("estado","Estado",'label',$elestado,'',' ',""); 
                 } else {
                    echo campo("estado","Estado",'labelb',$elestado,'',' ',"averia_editarcampo('estado','Estado','$id_estado');"); 
                 }                         
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
               echo campo("cliente_nombre","Cliente",'labelb',$cliente_nombre,' ',$disable_sec1,"averia_editarcampo('cliente_id','Cliente','$cliente_id');");  
                
               ?> 
               <input id="cliente_id" name="cliente_id"  type="hidden" value="<?php echo $cliente_id; ?>" >   
            </div>

            <div class="col-md-6">       
                <?php 
                $producto_etiqueta="";
                if (!es_nulo($id_producto)) {$producto_etiqueta=get_dato_sql('producto',"concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,''))"," where id=".$id_producto); }  
                    // echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ',$disable_sec1,'get.php?a=3&t=1',$producto_etiqueta);
                     echo campo("producto","Vehiculo",'labelb',$producto_etiqueta,' ',$disable_sec1,"averia_editarcampo('id_producto','Vehiculo','$id_producto');");
              
                 ?>  
                     <input id="id_producto" name="id_producto"  type="hidden" value="<?php echo $id_producto; ?>" >        
            </div>      
         
      </div>



      <div class="row"> 
           <div class="col-md-6">       
                <?php 
               
               echo campo("contacto1","Contacto",'labelb',$contacto,' ',$disable_sec1,"averia_editarcampo('contacto','Contacto','$contacto');");  
                
               ?> 
              
            </div>

            <div class="col-md-6">       
                 <?php                
    
                  echo campo("id_tecnico2","Taller",'labelb',$eltecnico1,' ',$disable_sec1,"averia_editarcampo('id_tecnico1','Taller','$id_tecnico1');");  
                
                 ?> 
                 <input id="id_tecnico1" name="id_tecnico1"  type="hidden" value="<?php echo $id_tecnico1; ?>" >        
               
            </div>      
         
      </div>
     
      <div class="row"> 
   
            <div class="col-md-6">       
                <?php 
                  
                  echo campo("tipo_mant","Tipo",'labelb',$eltipo,'',$disable_sec1,"averia_editarcampo('id_tipo','Tipo','$id_tipo');");
                   
                   ?>  
                   <input id="id_tipo" name="id_tipo"  type="hidden" value="<?php echo $id_tipo; ?>" >        
            </div>
            <div class="col-md-3">       
                <?php 
                echo campo("kilometraje","Kilometraje",'labelb',$kilometraje,' ',$disable_sec1,"averia_editarcampo('kilometraje','Kilometraje','$kilometraje');");
             
                    ?>         
            </div>   
            <div class="col-md-3">       
                <?php 
                echo campo("numero_inspeccion","Numero Inspeccion",'labelb',$numero_inspeccion,' ',$disable_sec1);
             
                    ?>         
            </div>   
           
      </div>
 <div class="row"> 
   
            <div class="col-md-2">       
                <?php 
                  //coseguro,deducible,seguro
                  echo campo("coseguro","Coaseguro",'labelb',$coseguro,'',$disable_sec1,"averia_editarcampo('valores','Valores','');");
                   
                   ?>  
                   <input id="coseguro" name="coseguro"  type="hidden" value="<?php echo $coseguro; ?>" >        
            </div>
            <div class="col-md-2">       
            <?php 
                  //coseguro,deducible,seguro
                  echo campo("deducible","Deducible",'labelb',$deducible,'',$disable_sec1,"averia_editarcampo('valores','Valores','');");
                   
                   ?>  
                   <input id="deducible" name="deducible"  type="hidden" value="<?php echo $deducible; ?>" >   
            </div>   
            <div class="col-md-2">       
                 <?php 
                  //coseguro,deducible,seguro
                  //  echo campo("seguro","Seguro",'labelb',$seguro,'',$disable_sec1,"averia_editarcampo('valores','Valores','');");                
                 ?>  
                 <input id="seguro" name="seguro"  type="hidden" value="<?php echo $seguro; ?>" >     
            </div>   
           
      </div>
      <hr>
      
      <div class="row"> 
           <div class="col-md-6">       
                <?php                   
                  echo campo("tipo_causa","Tipo de Causa",'labelb',$tipocausa,'',$disable_sec1,"averia_editarcampo('id_tipo_causa','Tipo de Causa','$id_tipo_causa');");
                ?>  
                <input id="id_tipo_causa" name="id_tipo_causa"  type="hidden" value="<?php echo $id_tipo_causa; ?>" >        
            </div>    
      </div>    

      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
             Observaciones
        </div>
        <div class="card-body">   
            <div class="row"> 
                 <div class="col-md">     
                     <?php
                     // echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',' rows="5" '.$disable_sec6);
                     echo campo("observa","Observaciones",'labelb',nl2br($observaciones),'',$disable_sec6,"averia_editarcampo('observa','Observaciones','');");
                    ?>              
                 </div>                       
            </div>
        </div>
    </div>       

      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
              Detalle Actividades           
              <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Compra Realizada','averia_mant_repuesto.php?a=comprea&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Compra Realizada</a>
              <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitud de Compra','averia_mant_repuesto.php?a=solcomp&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Solicitud Compra</a>
              <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Autorizar Servicio','averia_mant_repuesto.php?a=aut&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Autorizar</a>
              <?php if($id_estado<22) { ?>
                 <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Agregar Servicio','averia_mant_repuesto.php?a=agr&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-plus"></i> Agregar</a>           
              <?php } ?>
        </div>
        <div class="card-body"> 

         <?php
            $lin=1;    
         ?>       

          <div class="row"> 
          <div id="averia_detalle_actividad" class="table-responsive">  
              <!-- <ul id="detalleul_averia" class="list-group"> -->
              <table id="detalleul_averia" class="table table-striped table-hover" style="width:100%">
                <thead>
                  <tr>
                   <th><input type="checkbox" class="d-print-none" onchange="averia_marcar_todos(this,'detalleul_averia'); "  ></th>
                   <th>Codigo</th>
                   <th>Descripción</th>
                   <th>Cantidad</th>
                   <th>Costo</th>
                   <th>Precio Venta</th>
                
                   <th>Proveedor</th>
                   <th>Nota</th>
                   <th>Estado</th>
                   <th></th>
                  </tr>
                </thead>
                <tbody id="tbody_averia_det">                 
              
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
              <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Devolver Repuesto','averia_mant_repuesto.php?a=dev&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-minus"></i> Devolver</a>
              <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Repuesto NO Recibido','averia_mant_repuesto.php?a=norec&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-store-slash"></i> No Recibido</a>
              <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Compra Realizada','averia_mant_repuesto.php?a=comprea&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Compra Realizada</a>
              <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitud Compra Repuesto','averia_mant_repuesto.php?a=solcomp&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Solicitud Compra</a>
              <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Recibir Repuesto','averia_mant_repuesto.php?a=rec&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-wrench"></i> Recibir</a>
              <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Autorizar Repuesto','averia_mant_repuesto.php?a=aut&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Autorizar</a>  
              <?php if ($id_estado<22){?>   
                  <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitar Repuesto','averia_mant_repuesto.php?a=agr&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-plus"></i> Solicitar</a>           
              <?php } ?>
         </div>

        <div class="card-body">               
           <div class="row">
           <div id="averia_detalle_repuesto" class="table-responsive">
            <!-- <ul id="detalleul_repuesto" class="list-group"> -->
              
            <table id="detalleul_repuesto" class="table table-striped table-hover" style="width:100%">
            <thead>
                  <tr>
                    <th><input type="checkbox" class="d-print-none"  onchange="averia_marcar_todos(this,'detalleul_repuesto'); "  ></th>
                    <th>Codigo</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Costo</th>
                    <th>Precio Venta</th>                
                    <th>Proveedor</th>
                    <th>Nota</th>
                    <th>Estado</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody  id="tbody_averia_rep">
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
            
            <!-- <a href="#" onclick="averia_modificar_registro('tbody_averia_rep',2); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-pen "></i> Modificar</a> -->
            <a href="#" onclick="averia_procesar_occ('Crear Orden de Compra','ocompra'); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus "></i> Orden de Compra</a>
            <a href="#" onclick="averia_procesar_occ('Crear Orden de Cobro','ocobro'); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus "></i> Orden de Cobro</a>  
            <a href="#" onclick="averia_borrar_registro('tbody_averia_rep',2); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-trash "></i> Borrar</a>

            
          </div>
          </div>
          </div>
            
      
      <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <!-- <a href="#" onclick="procesar_averia('averia_mant.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check <?php echo $visible_guardar; ?>"></i> Guardar</a> -->
           
           <?php
           //BOTONES de ESTADO
           $estado_actualizar="";
           $estado_txt="";
           $addfecha="&lk=1";
           $estado_actualizar2="";
           $estado_txt2="";

           if ($id_estado<3 and tiene_permiso(35)) {
            $estado_actualizar="3";
            $estado_txt="APROBAR";
            $addfecha="&lk=1";
           }

           if (($id_estado==3)  and tiene_permiso(83)) {
            $estado_actualizar="4";
            $estado_txt="ATENDER";
            $addfecha="&lk=1";
           }

           if ($id_estado==4 and tiene_permiso(84)) {
            $estado_actualizar="21";
            $estado_txt="REALIZADO";
            $addfecha="&lk=1";
            // if (tiene_permiso(77)) { //pausar
            //   $estado_actualizar2="7";//paro
            //   $estado_txt2="PAUSAR";
            // }
           }

          if ($id_estado==21  and tiene_permiso(85)) {
              $placas=get_dato_sql("averia_detalle","count(*)"," where producto_codigoalterno='ACC-00096' and id_maestro=".$cid);
              if (!es_nulo($placas)){
                  //enviar correo
                require_once ('correo_averia_nuevo.php');
              }
              $estado_actualizar="22";
              $estado_txt="AUTORIZAR";
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

           if ( $estado_actualizar<>"") { ?>
              <a href="#" onclick="averia_editarcampo('estado','<?php echo $estado_txt; ?>','<?php echo $estado_actualizar; ?>','<?php echo $addfecha; ?>'); return false;" class="btn btn-warning mr-2 mb-2 xfrm" ><i class="fa fa-check <?php echo $visible_guardar; ?>"></i> <?php echo $estado_txt; ?></a>         
              <a href="#" onclick="procesar_get('averia_mant_repuesto.php?a=emlaut&tipo=0&cid=<?php echo $id; ?>'); return false;" class="btn btn-outline-info ml-3 mr-2 mb-2 xfrm" ><i class="fa fa-envelope "></i> Solicitar Autorización</a>
          <?php }    ?>
           
          <?php   if (es_nulo($id_usuario_auditado) and tiene_permiso(163)) {   ?>            
               <a href="#" onclick="averia_editarcampo('auditado','Auditado','23','<?php echo $addfecha; ?>'); return false;" class="btn btn-outline-secondary ml-3 mr-2 mb-2 xfrm" ><i class="fa fa-clock <?php echo $visible_guardar; ?>"></i> Revision ADPC</a>
          <?php }    ?>

          <?php if ($nuevoreg==false) { ?>
              <div class="float-right">
                <?php if (!es_nulo($id_inspeccion)) { ?>
                   <a href="#" onclick="get_page('pagina','inspeccion_mant.php?a=v&cid=<?php echo $id_inspeccion; ?>','Hoja de Inspección',false); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-file-medical-alt"></i> Abrir Inspección</a>
                <?php } ?>
                <a href="#" onclick="get_page('pagina','averia_mant.php?a=v&cid='+$('#id').val(),'Orden de Avería',false); return false;" class="btn btn-success mr-2 mb-2 xfrm" ><i class="fa fa-redo-alt"></i> Actualizar</a>
                <a href="averia_pdf.php?pdfcod=<?php echo $id; ?>&pc=1" target="_blank"  class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir P. Costo</a>
                <?php if ($id_tipo<>2 ) { ?><a href="averia_pdf.php?pdfcod=<?php echo $id; ?>" target="_blank"  class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir P. Venta</a>   <?php } ?>
              </div>
          <?php } ?>

          </div>
          </div>
        </div>

      
        </fieldset>
        </form> 
  
        <div class="row"><div class="col mt-5 px-3 py-2">          
          <a href="#" onclick="get_page_regresar('pagina','averia_ver.php','Ver Ordenes de Avería') ;  return false;" class="btn btn-outline-secondary mr-2 mb-2">Regresar</a> 
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
    procesar_averia_foto('nav_fotos');
  }
  if (eltab=='nav_historial') {
    procesar_averia_historial('nav_historial');
  }
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

}


function procesar_averia(url,forma,adicional){

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

				get_page('pagina','averia_mant.php?a=v&cid='+json[0].pcid,'Orden de Avería',false) ; 
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





function procesar_averia_foto(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var insp=$('#id_inspeccion').val();
  var url='averia_fotos.php?cid='+cid+'&pid='+pid+'&insp='+insp ;
 
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

function procesar_averia_historial(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var url='averia_historial.php?cid='+cid+'&pid='+pid ;
 
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


function averia_procesar_occ(nombre,campo){
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
  var datos={'idet':values};//$("#"+forma).serialize();
  
    if (campo=='ocompra') {
      modalwindow2(nombre,'averia_mant_oc.php?a='+campo+'&tipo=0&cid='+$('#id').val(),datos);
    }
    
    if (campo=='ocobro') {
      modalwindow2(nombre,'averia_mant_ocobro.php?a='+campo+'&tipo=0&cid='+$('#id').val(),datos);
    }
 
}
}


function averia_borrar_registro(objeto,tipo) {

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
      averia_procesar_selec('averia_mant_repuesto.php?a=del',objeto,tipo);
       
	  }
	})

}

function averia_modificar_registro(objeto,tipo) {

    averia_procesar_selec('averia_mant_repuesto.php?a=mod',objeto,tipo);

}

function averia_modificar_linea(detalle) {


    modalwindow('Editar','averia_mant_detalle.php?a=mod&cid='+$('#id').val()+'&did='+detalle);


}



function averia_totlinea(linea){
		var acctmp=$(linea).closest('tr').data("acc");
		if (acctmp!='D' && acctmp!='I') {
		  var nombreli= $(linea).closest('tr').attr('id');
		  $('#'+nombreli+' input[name="det_acc[]"]').val('U');
      $(linea).closest('tr').data("acc",'U');
		}
	  
		  //	calcular_totales();
		  }	

function calcular_pv(linea){
  var nombreli= $(linea).closest('tr').attr('id');
  //console.log(nombreli);
  var cod_producto=$('#'+nombreli+' input[name="det_alt[]"]').val();
  var exento_ganancia = <?php echo json_encode($_SESSION['p_exento_ganancia']); ?>;
  var costo=parseFloat($(linea).val());
  
  if (jQuery.inArray( cod_producto, exento_ganancia )>0) {
    var precio=costo;
  } else {
    var precio=costo+(costo*<?php echo $_SESSION['p_ganancia']; ?>);
  }
  
  
	$('#'+nombreli+' input[name="det_pv[]"]').val(precio);
  
	
}	

function averia_editarcampo(nombre,etiqueta,valor,adicional=''){

  /*if(etiqueta=='REALIZADO')
  {
    popupWeb('Descuento Pendiente de aprobacion','Por favor aprobar descuentos antes de continuar');
    return;
  }*/


  var estado = $('#id_estado').val();  
  if (estado<22){    
     modalwindow('Editar','averia_mant.php?a=ec&nom='+encodeURI(nombre)+'&sid='+$('#id').val()+'&eti='+encodeURI(etiqueta)+'&val='+encodeURI(valor)+adicional);
  }else{
    <?php if(tiene_permiso(162) or tiene_permiso(163)) {?>
          modalwindow('Editar','averia_mant.php?a=ec&nom='+encodeURI(nombre)+'&sid='+$('#id').val()+'&eti='+encodeURI(etiqueta)+'&val='+encodeURI(valor)+adicional);
    <?php } ?>      
  }
}

function procesar_averia_modificar_cmp(url,forma,adicional){
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
				
        
				get_page('pagina','averia_mant.php?a=v&cid='+json[0].pcid,'Orden de Avería',false) ; 
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
				
        
		        get_box('tbody_averia_det','averia_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
                get_box('tbody_averia_rep','averia_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
      
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




function averia_marcar_todos(objeto,tabla){
  $("#"+tabla+" input[type='checkbox']").prop('checked',  $(objeto).prop('checked'));
	// $("#"+tabla+" input[type='checkbox']").checkboxradio();
	// $("#"+tabla+" input[type='checkbox']").checkboxradio("refresh");

}



function averia_procesar_selec(url,objeto,tipo){

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
				
	
			//	get_page('pagina','averia_mant.php?a=v&cid='+json[0].pcid,'Orden de Avería',false) ; 
			get_box('tbody_averia_det','averia_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
      get_box('tbody_averia_rep','averia_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
      
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

function averia_abrir_ocompra(ordenid) {
  modalwindow2('Orden de Compra','compra_mant.php?a=v&cid='+ordenid); 


}

function averia_abrir_ocobro(ordenid) {
  modalwindow2('Orden de Cobro','cobro_mant.php?a=v&cid='+ordenid); 
  
}


      
</script>