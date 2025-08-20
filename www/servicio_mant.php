<?php

require_once ('include/framework.php');


if (!tiene_permiso(25) and !tiene_permiso(59)) { 

	  echo '<div class="card-body">';
	  echo'No tiene privilegios para accesar esta función';
    echo '</div>';
    exit;
	}

$puede_modificar=false;
if (tiene_permiso(27)) {$puede_modificar=true;}

function  cargar_detalle($cid,$tipo){
  global $lin;

  $filtro=" and servicio_detalle.producto_tipo=$tipo ";
  if ($tipo==3) {
    $filtro=" and (servicio_detalle.producto_tipo=$tipo )";
  }
  $servicios_result = sql_select("SELECT servicio_detalle.* 
                            ,producto.horas
                            ,producto.tipo_mant
                            ,user.nombre
                            FROM servicio_detalle 
                            inner join usuario user on (servicio_detalle.id_usuario=user.id)
                            LEFT OUTER JOIN producto ON (servicio_detalle.id_producto=producto.id)
                            where servicio_detalle.id_servicio=$cid 
                            $filtro
                            order by servicio_detalle.id ");


      if ($servicios_result->num_rows > 0) { 
        while ($detalle = $servicios_result -> fetch_assoc()) {

          echo agregar_servicio_detalle($lin,$detalle["precio_venta"],$detalle["precio_costo"],$detalle["id_producto"],$detalle["producto_codigoalterno"],$detalle["producto_nombre"],$detalle["id"],$detalle["cantidad"],$detalle["producto_nota"],$detalle["estado"],$detalle["id_oc"],$detalle["id_ocobro"],$detalle["horas"],$detalle["horas_atender"],$detalle["tipo_mant"],$tipo,$detalle['existencia'],$detalle['nombre']);
                
          $lin++;
        
          }}
  } 


function agregar_servicio_detalle($vlin,$data_pv,$data_pc,$data_id,$data_alt,$data_desc,$det_id,$cantidad,$nota,$estado,$ocompra,$ocobro,$hora,$hora_atendido,$tipo_mant,$tipo,$existencia,$usuario_nombre) {
  $salidatxt="";
  $devueltoclass="";
  if ($estado==4) {$devueltoclass="texto-borrado";}

  $salidatxt.='<tr id="vdetli_'.$vlin.'" class="'.$devueltoclass.'"  data-cod="'.$data_id.'"  data-detid="'.$det_id.'" data-acc="">';
  // $salidatxt.='<li id="vdetli_'+vlin+'" class="list-group-item list-group-item-action d-sm-flex justify-content-between align-items-center" data-pv="'+data.pv+'" data-pc="'+data.pc+'" data-cod="'+data.id+'">';
 
  $chkbox="";
 if ($estado<>4 and $estado<>5) { $chkbox='<input class="serv_chk" type="checkbox" value="'.$det_id.'" name="det_id[]">'; }
  $salidatxt.='<td>'.$chkbox.'</td> ';
  
 $salidatxt.='<td><span class="badge badge-secondary">'.$data_alt.'</span>';
 // $salidatxt.='<input name="det_codigo[]"  value="'.$data_id.'"  type="hidden"  />'; 
  // $salidatxt.='<input name="det_tipo[]"  value="'+tipo+'"  type="hidden"  />'; 
  //$salidatxt.='<input name="det_id[]"  value="'.$det_id.'"  type="hidden"  />'; 
  // $salidatxt.='<input name="det_acc[]"  value=""  type="hidden"  />';
  $salidatxt.='</td>';
  $salidatxt.='<td>'.$data_desc.'</td> ';
  $salidatxt.='<td>'.$cantidad.'</td>';

  if ($tipo==3) {
      $salidatxt.='<td align="center">'.$tipo_mant.'</td>';	
      $salidatxt.='<td align="center">'.$hora.'</td>';	
      $salidatxt.='<td align="center">'.$hora_atendido.'</td>';
  }

  $salidatxt.='<td>'.$nota.'</td>';	
  $salidatxt.='<td>'.get_servicio_detalle_estado($estado).'</td>';
  
  if ($existencia!=null){
      $salidatxt.='<td>'.$existencia.'</td>';
  }

  if($tipo==3) {
       $salidatxt.='<td align="center">'.$usuario_nombre.'</td>';
  }

    /*$salidatxt.='<td align="center">'.$usuario_nombre.'</td>';*/	


  // $salidatxt.='<td>'.formato_numero($data_pv,2).'</td> ';
  // $salidatxt.='<td>'.formato_numero($data_pc,2).'</td> ';

   // $salidatxt.='<td><span class="badge badge-secondary dettotal">'+(parseFloat(data.pv)*cantidad)+'</span></td> ';
   $btn_compra="";
   $btn_cobro="";
   
   if (!es_nulo($ocompra)) { if (tiene_permiso(38)) { $btn_compra='<a href="#" onclick="servicio_abrir_ocompra('.$ocompra.'); return false;"><i class="fa fa-shopping-cart mr-1 text-secondary"></i></a>';}}
   if (!es_nulo($ocobro)) { if (tiene_permiso(39)) { $btn_cobro=' <a href="#" onclick="servicio_abrir_ocobro('.$ocobro.'); return false;"><i class="fa fa-money-bill-alt mr-1 text-secondary"></i></a>';}}
   
   $salidatxt.='<td class="text-nowrap">'.$btn_compra.$btn_cobro.'</td>';	
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
      //Auditado
  
   
  ?>
   <form id="forma_cambiarcmp" name="forma_cambiarcmp" class="needs-validation" novalidate>
    <fieldset id="fs_forma"> 
    
    <br><br>
    <?php 
      $salida='';
      

      // Auditadob
      if ($nombre=='auditado'){    
        $result = sql_select("SELECT observaciones_adpc
        FROM servicio
        WHERE id=$sid limit 1");
          
        if ($result!=false){
            if ($result -> num_rows > 0) { 
               $row = $result -> fetch_assoc() ; 
               //$salida.= campo("observa","Observaciones",'textarea',$row['observaciones'],' ',' rows="8" ');
               $salida.= campo("observaciones_adpc","Observaciones ADPC",'textarea',$row['observaciones_adpc'],' ',' rows="8" ');
            }
        }         
      }

    // Reproceso
    if ($nombre=='reproceso'){      
       $salida.= campo("observaciones_reproceso","Observaciones Reproceso",'textarea','',' ',' rows="4" ');
    }

      //TECNICOS
      if ($nombre=='id_tecnico1' or $nombre=='id_tecnico2' or $nombre=='id_tecnico3' or $nombre=='id_tecnico4') {
        
        pagina_permiso(69);//asignar mecanico

         $result = sql_select("SELECT id_tecnico1,id_tecnico2,id_tecnico3,id_tecnico4
         FROM servicio
         WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ;    
                 $salida.= campo('id_tecnico1', $etiqueta,'select2',valores_combobox_db('usuario',$row["id_tecnico1"],'nombre',' where activo=1 and grupo_id=2 or perfil_adicional=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',' required ',''); 
                 $salida.= campo('id_tecnico2', $etiqueta,'select2',valores_combobox_db('usuario',$row["id_tecnico2"],'nombre',' where activo=1 and grupo_id=2 or perfil_adicional=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',' required ',''); 
                 $salida.= campo('id_tecnico3', $etiqueta,'select2',valores_combobox_db('usuario',$row["id_tecnico3"],'nombre',' where activo=1 and grupo_id=2 or perfil_adicional=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',' required ',''); 
                 $salida.= campo('id_tecnico4', $etiqueta,'select2',valores_combobox_db('usuario',$row["id_tecnico4"],'nombre',' where activo=1 and grupo_id=2 or perfil_adicional=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',' required ','');                      
              }
          }         
      } 


      //ESTADO boton abajo
      if ($nombre=='estado_btn') {

        if ($valor=="3" and !tiene_permiso(60)) { 	echo '<div class="card-body">';   echo'No tiene privilegios para accesar esta función';   echo '</div>';  exit;  }
        if ($valor=="4" and !tiene_permiso(75)) { 	echo '<div class="card-body">';   echo'No tiene privilegios para accesar esta función';   echo '</div>';  exit;  }
        if ($valor=="21" and !tiene_permiso(76)) { 	echo '<div class="card-body">';   echo'No tiene privilegios para accesar esta función';   echo '</div>';  exit;  }
        if ($valor=="22" and !tiene_permiso(61)) { 	echo '<div class="card-body">';   echo'No tiene privilegios para accesar esta función';   echo '</div>';  exit;  }
        if ($valor=="7" and !tiene_permiso(77)) { 	echo '<div class="card-body">';   echo'No tiene privilegios para accesar esta función';   echo '</div>';  exit;  }
        
        $serviciosPendientes="SELECT COUNT(*) servicios_pendientes FROM servicio_detalle WHERE id_servicio=$sid AND producto_tipo=2 and estado!=2";
        $RepuestosPendientes="SELECT COUNT(*) repuestos_pendientes FROM servicio_detalle WHERE id_servicio=$sid AND producto_tipo=3 AND estado!=3;";
        
        $resultserviciosPendientes = sql_select($serviciosPendientes);
        $row1 = mysqli_fetch_assoc($resultserviciosPendientes);
        $serviciosPendientesValor = $row1['servicios_pendientes'];

        $resultRepuestosPendientes = sql_select($RepuestosPendientes);
        $row2 = mysqli_fetch_assoc($resultRepuestosPendientes);
        $repuestosPendientesValor = $row2['repuestos_pendientes'];

        if($serviciosPendientesValor>0)
        {
            echo '<div class="card-body p-2">';
            echo '<span style="font-size:20px; color:red; font-weight:bold;">Actividades pendientes de Autorización: '.$serviciosPendientesValor.'</span>';
            echo '</div>';
        }

        if($repuestosPendientesValor>0){

           echo '<div class="card-body p-2">';
            echo '<span style="font-size:20px; color:red; font-weight:bold;">Repuestos pendientes de Recibir: '.$repuestosPendientesValor.'</span>';
           echo '</div>';
        }

        if($serviciosPendientesValor>0 or $repuestosPendientesValor>0)
        {
          exit;
        }





        $result = sql_select("SELECT id_estado
          FROM servicio
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                 
                    $salida.= ' <input id="id_estado" name="id_estado"  type="hidden" value="'.$valor.'" > ';   
                    $salida.=  campo('etq_estado', 'Marcar estado como: '.$etiqueta,'label','',' ',' ',''); 
                    $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');
                    if ($valor=="3") { //aprobar 
                      //  $salida.=campo('disponibilidad_veh', 'Disponibilidad del Vehiculo','select',valores_combobox_texto('<option value="1">Disponible</option><option value="2">En Renta</option>','','...'),' ',' required ',''); ;
                    }

                    if ($valor=="7"){$salida.= campo("fecha","Fecha Promesa",'date','',' ','  ');}
                    
                }
          }
            
      } 
    

      //ESTADO
      if ($nombre=='estado') {

        pagina_permiso(27);//Modificar

        $result = sql_select("SELECT id_estado
          FROM servicio
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                 
                  if (isset($_REQUEST['lk'])) {
                      $salida.= ' <input id="id_estado" name="id_estado"  type="hidden" value="'.$valor.'" > ';   
                      $salida.=  campo('etq_estado', 'Marcar estado como: '.$etiqueta,'label','',' ',' ',''); 
                      $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');

                    
                  } else {
                      $salida.= campo('id_estado', $etiqueta,'select2',valores_combobox_db('servicio_estado',$row["id_estado"],'nombre',' where estado<2 ','','...'),' ',' required ',''); 
                      $salida.= campo("observaciones","Observaciones",'textarea','',' ',' rows="4" ');
                      $salida.= campo("fecha","Fecha Promesa",'date','',' ','  ');
                  }
              }
          }          
      } 

      if ($nombre=='estadoparo') {

        pagina_permiso(27);//Modificar

        $result = sql_select("SELECT estado_paro_por_repuesto
          FROM servicio
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ;                  
                      $salida.= campo('estado_paro_por_repuesto', 'Estado de Paro por Repuesto','select2',valores_combobox_texto(app_combo_a_i,'I'),' ',' required ',''); 
                  }
          }
      }             

      //taller
       if ($nombre=='id_taller' ) {

        pagina_permiso(27);//Modificar
       
        $result = sql_select("SELECT id,nombre
          FROM entidad
          WHERE id=$valor limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                 $salida.= campo("tallertmp","Taller Asignado",'select2ajax',$valor,' ','','get.php?a=4&t=1',$row["nombre"]);   
                
               }
          }
         
   
      } 

      // tipo de revision      
      if ($nombre=='servicio_tipo_revision') {

        pagina_permiso(27);//Modificar
  
       $salida.= campo('id_tiprevision', $etiqueta,'select2',valores_combobox_db('servicio_tipo_revision',$valor,'nombre',' ','','...'),' ',' required ',''); 
              
      } 

    // tipo de mantenimiento      
      if ($nombre=='id_tipo_mant') {

        pagina_permiso(27);//Modificar
  
       $salida.= campo('id_tipo_mant', $etiqueta,'select2',valores_combobox_db('servicio_tipo_mant',$valor,'nombre',' ','','...'),' ',' required ',''); 
              
      } 


       //OBSERVACIONES
       if ($nombre=='observa') {

        pagina_permiso(27);//Modificar

        $result = sql_select("SELECT observaciones
        FROM servicio
        WHERE id=$sid limit 1");
          
        if ($result!=false){
            if ($result -> num_rows > 0) { 
               $row = $result -> fetch_assoc() ; 
               $salida.= campo("observa","Observaciones",'textarea',$row['observaciones'],' ',' rows="8" ');
            }
        }
      } 


       // nota operaciones      
       if ($nombre=='nota_operaciones') {

        pagina_permiso(115);//Modificar encabezao

        $result = sql_select("SELECT nota_operaciones
          FROM servicio
          WHERE id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ; 
                $salida.= campo('nota_operaciones_tmp', $etiqueta,'text',$row["nota_operaciones"],' ',' required ',''); 
               }
          }
  
      
              
      } 

       // kilometraje      
       if ($nombre=='kilometraje') {

        pagina_permiso(115);//Modificar encabezao

        $result = sql_select("SELECT kilometraje
          FROM servicio
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

        pagina_permiso(115);//Modificar encabezao
       
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
        
        pagina_permiso(115); //Modificar encabezao

        $result = sql_select("SELECT servicio.id_producto,servicio.placa,servicio.chasis
        ,producto.nombre
                  FROM servicio
                  LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id)
          WHERE servicio.id=$sid limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ;                     
                 $salida.= campo("id_producto_tmp", "Vehiculo",'select2ajax',$row["id_producto"],' ',' onchange="serv_mant_actualizar_veh();" required ','get.php?a=3&t=1',$row["nombre"]);
                 $salida.= campo('placa_tmp', 'Placa','text',$row["placa"],' ',' required ',''); 
                 $salida.= campo('chasis_tmp', 'Chasis','text',$row["chasis"],' ',' required ',''); 
              }
          }
          } 



      echo $salida;
    ?>
    

    <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <a href="#" onclick="procesar_modificar_cmp('servicio_mant.php?a=ec2&sid=<?php echo $sid;?>','forma_cambiarcmp',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
           

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
     $sqladd1="INSERT INTO servicio_historial_estado
     (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
     VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Revision ADPC', NOW(), '')";
     array_push($sqladicional, $sqladd1);  

  }
  
  //reproceso
  if (isset($_REQUEST["observaciones_reproceso"])){
    $sqlcampos.= "  reproceso = 'R'";
    $sqlcampos.= ", observaciones_reproceso =".GetSQLValue($_REQUEST["observaciones_reproceso"],"text");    

    ///Historial 
    $sqladd1="INSERT INTO servicio_historial_estado
    (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
    VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'OS en Reproceso', NOW(), '')";
    array_push($sqladicional, $sqladd1);  

  }

  // TECNICOS
  if (isset($_REQUEST["id_tecnico1"]) and isset($_REQUEST["id_tecnico2"]) and isset($_REQUEST["id_tecnico3"]) and isset($_REQUEST["id_tecnico4"]) )
  {
     $sqlcampos.= " id_tecnico1 =".GetSQLValue($_REQUEST["id_tecnico1"],"int");  
     $sqlcampos.= " , id_tecnico2 =".GetSQLValue($_REQUEST["id_tecnico2"],"int");  
     $sqlcampos.= " , id_tecnico3 =".GetSQLValue($_REQUEST["id_tecnico3"],"int");  
     $sqlcampos.= " , id_tecnico4 =".GetSQLValue($_REQUEST["id_tecnico4"],"int");  
     $fecha_asigna="";
     $estado=0;
      
      $result = sql_select("SELECT fecha_hora_asigna,id_estado
          FROM servicio
          WHERE id=$elcodigo limit 1");
            
          if ($result!=false){
              if ($result -> num_rows > 0) { 
                 $row = $result -> fetch_assoc() ;    
                 $fecha_asigna=$row["fecha_hora_asigna"];
                 $estado=$row["id_estado"];
              }
          }
      
      
      
      if (es_nulo($fecha_asigna)) {
         $sqlcampos.= ", fecha_hora_asigna=NOW()";
      }  

        //if ($estado<4) {
         //////  $sqlcampos.= ", id_estado=4"; //en proceso

           $sqladd1="INSERT INTO servicio_historial_estado
           (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Asignacion de Mecanico', NOW(), '')";
           array_push($sqladicional, $sqladd1);  
       // }
      

  }


  // ESTADO
  $enviar_orden_email=false;
  $enviar_orden_sms=false;
  $autorizando=false;
  $completando=false;
  if (isset($_REQUEST["id_estado"]) )  {

    //validar 
    if (intval($_REQUEST["id_estado"])>=3 and intval($_REQUEST["id_estado"])<>7) {

      // if (es_nulo($_REQUEST["disponibilidad_veh"])) {
      
      //   $stud_arr[0]["pcode"] = 0;
      //   $stud_arr[0]["pmsg"] ="Debe seleccionar la Disponibilidad del Vehiculo";
      //   salida_json($stud_arr);
      //   exit;
      // }
      $est_validacion=true;
      if (intval($_REQUEST["id_estado"])==4) {
        $idestado_actual=intval(get_dato_sql("servicio","id_estado","WHERE id=".$elcodigo));
        if ($idestado_actual==7) { //si esta en paso por repuestro permitir regresar a en proceso
          $est_validacion=false;
        }
      }
       
    } //fin validar

    $sqlcampos.= "  id_estado =".GetSQLValue($_REQUEST["id_estado"],"int");      
    $observaciones="";

    if (isset($_REQUEST["observaciones"])) {
      $observaciones=$conn->real_escape_string($_REQUEST["observaciones"]);
      
    } 

    if (isset($_REQUEST["fecha"])) {
      if (!es_nulo($_REQUEST["fecha"])) {

          $sqlcampos.= " , fecha_alerta =".GetSQLValue($_REQUEST["fecha"],"date"); 
          $observaciones.=" Fecha Promesa: ".formato_fecha_de_mysql($_REQUEST["fecha"]);
      }
    }

    if ($_REQUEST["id_estado"]==3 or $_REQUEST["id_estado"]=="3") {//aprobada
     // if (!es_nulo($_REQUEST["disponibilidad_veh"])) {$sqlcampos.= " , disponibilidad =".GetSQLValue($_REQUEST["disponibilidad_veh"],"int");}
      $autorizando=true;
      
   }

  if ($_REQUEST["id_estado"]==21 or $_REQUEST["id_estado"]=="21") {//realizada
    if (isset($_REQUEST["observaciones"])){
       $sqlcampos.= " ,observaciones_realizado =".GetSQLValue(trim($_REQUEST["observaciones"]),"text");}     
   }

    if ($_REQUEST["id_estado"]==22 or $_REQUEST["id_estado"]=="22") {//completada
       $sqlcampos.= " , fecha_hora_final =now()";       
       $completando=true;   
       $enviar_orden_email=true;
       $enviar_orden_sms=true;
    }
  
           $sqladd1="INSERT INTO servicio_historial_estado
           (id_servicio, id_estado, id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".intval($_REQUEST["id_estado"]).", ".$_SESSION['usuario_id'].", 0, 'Modificacion de Estado', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
  }
 

    //Activo y Inactivo en Estado paro por repuesto
  if (isset($_REQUEST["estado_paro_por_repuesto"])){
      $sqlcampos.= "  estado_paro_por_repuesto =".GetSQLValue($_REQUEST["estado_paro_por_repuesto"],"text");     
      if ($_REQUEST["estado_paro_por_repuesto"]=='A'){
         $estado_paroporrepuesto='Activo';
      }else{
         $estado_paroporrepuesto='Inactivo';
      }
      ///Historial       
      $sqladd1="INSERT INTO servicio_historial_estado
      (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
      VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0,'Modificacion de Estado paro por Repuesto', NOW(), '$estado_paroporrepuesto')";
      array_push($sqladicional, $sqladd1);  

  }   

  //TALLER
  if (isset($_REQUEST["tallertmp"]) )
  {
     $sqlcampos.= "  id_taller =".GetSQLValue($_REQUEST["tallertmp"],"int");  
     $observaciones="";

     $nombretaller=get_dato_sql('entidad','nombre',' where id='.intval($_REQUEST["tallertmp"]));
  
           $sqladd1="INSERT INTO servicio_historial_estado
           (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", ".intval($_REQUEST["tallertmp"]).", 'Asignacion de Taller', NOW(), '$nombretaller')";
           array_push($sqladicional, $sqladd1);  

  }
  
  
  

  //Tipo revision
  if (isset($_REQUEST["id_tiprevision"]) )
  {
     $sqlcampos.= "  id_tipo_revision =".GetSQLValue($_REQUEST["id_tiprevision"],"int");  
     $observaciones="";

     $nombretaller=get_dato_sql('servicio_tipo_revision','nombre',' where id='.intval($_REQUEST["id_tiprevision"]));
  
           $sqladd1="INSERT INTO servicio_historial_estado
           (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Asignacion de Tipo de Revision', NOW(), '$nombretaller')";
           array_push($sqladicional, $sqladd1);  

  }

   //Tipo  mantenimiento
   if (isset($_REQUEST["id_tipo_mant"]) )
   {
      $sqlcampos.= "  id_tipo_mant =".GetSQLValue($_REQUEST["id_tipo_mant"],"int");  
      $observaciones="";
 
      $nombretipo=get_dato_sql('servicio_tipo_mant','nombre',' where id='.intval($_REQUEST["id_tipo_mant"]));
   
            $sqladd1="INSERT INTO servicio_historial_estado
            (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
            VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Modificacion de Tipo de Mantenimiento', NOW(), '$nombretipo')";
            array_push($sqladicional, $sqladd1);  
 
   }

   //OBSERVACIONES
   if (isset($_REQUEST["observa"]) )
  {
     $sqlcampos.= "  observaciones =".GetSQLValue($_REQUEST["observa"],"text");  
     $observaciones="";
    
  
           $sqladd1="INSERT INTO servicio_historial_estado
           (id_maestro,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo, ".$_SESSION['usuario_id'].", 0, 'Modificacion de Observaciones', NOW(), '$observaciones')";
           array_push($sqladicional, $sqladd1);  
       
    

  }

  //nota operaciones
  if (isset($_REQUEST["nota_operaciones_tmp"]) )
  {
    $sqlcampos.= "  nota_operaciones =".GetSQLValue($_REQUEST["nota_operaciones_tmp"],"text");  
    $observaciones="";

          $sqladd1="INSERT INTO servicio_historial_estado
          (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
          VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Modificacion de Nota Operaciones', NOW(), '')";
          array_push($sqladicional, $sqladd1);  
  }

  //Vehiculo
  if (isset($_REQUEST["id_producto_tmp"]) )
  {
     $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["id_producto_tmp"],"int"); 
     $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa_tmp"],"text"); 
     $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis_tmp"],"text");  
     $observaciones="";

 
           $sqladd1="INSERT INTO servicio_historial_estado
           (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
           VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Modificar Vehiculo', NOW(), '')";
           array_push($sqladicional, $sqladd1);  

  }

  //kilometraje
  if (isset($_REQUEST["kilometraje"]) )
  {
    $sqlcampos.= "  kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int");  
    $observaciones=intval($_REQUEST["kilometraje"]);

          $sqladd1="INSERT INTO servicio_historial_estado
          (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
          VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", 0, 'Modificacion de Kilometraje', NOW(), $observaciones)";
          array_push($sqladicional, $sqladd1);  
  }

    //cliente
    if (isset($_REQUEST["clientetmp"]) )
    {
       $sqlcampos.= "  cliente_id =".GetSQLValue($_REQUEST["clientetmp"],"int");  
       $observaciones="";
  
       $nombrecliente=get_dato_sql('entidad','nombre',' where id='.intval($_REQUEST["clientetmp"]));
    
             $sqladd1="INSERT INTO servicio_historial_estado
             (id_servicio,  id_usuario, id_proveedor, nombre, fecha, observaciones)
             VALUES ( $elcodigo,  ".$_SESSION['usuario_id'].", ".intval($_REQUEST["clientetmp"]).", 'Modificar cliente', NOW(), '$nombrecliente')";
             array_push($sqladicional, $sqladd1);  
  
    }

  //  pagina_permiso(115);//Modificar encabezao

  //  $result = sql_select("SELECT nota_operaciones
  //    FROM servicio
  //    WHERE id=$sid limit 1");
       
  //    if ($result!=false){
  //        if ($result -> num_rows > 0) { 
  //           $row = $result -> fetch_assoc() ; 
  //          $salida.= campo('nota_operaciones_tmp', $etiqueta,'text',$row["nota_operaciones"],' ',' required ',''); 
  //         }
  //    }
  



  if ($sqlcampos<>''){
      $sql="update servicio  set " . $sqlcampos . " where id=".$elcodigo . ' LIMIT 1';
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

      if ($enviar_orden_sms==true) {

              //obtener codigo de vehiculo y celular de cliente
              $sql="SELECT producto.codigo_alterno
              ,inspeccion.cliente_contacto_telefono        
              FROM servicio
              LEFT OUTER JOIN producto ON (servicio.id_producto=producto.id) 
              LEFT OUTER JOIN inspeccion ON (servicio.id_inspeccion=inspeccion.id)
              WHERE servicio.id=$elcodigo 
              LIMIT 1";
              $resultsms = sql_select($sql);
            
                if ($resultsms->num_rows > 0) {    
                  $rowsms = $resultsms -> fetch_assoc();    
                  
                 
                  $sms_mensaje="HERTZ Estimado cliente. Los trabajos realizados al vehículo ".$rowsms["codigo_alterno"]." han sido finalizados y se encuentra listo para la entrega, te esperamos en el taller para su retiro";          
                  $sms_numero=trim($rowsms["cliente_contacto_telefono"]);
                  $sms_numero=str_replace('-','', $sms_numero);
                  $sms_numero=str_replace(' ','', $sms_numero);
                  $smsenviado=0;  

                  if (strlen($sms_numero)==8) {
                    require_once ('include/sms_api.php');
                  }
                  
                    
                }
                              
      }
        //******** API Rentworks *******/
        /*
        if ($autorizando==true) { 
          require_once ('include/rentworks_api.php');
          $rw_salida=rw_crear_orden(1,$cid,"");
        }
        */
        if ($completando==true) { 
            require_once ('include/rentworks_api.php');
            $rw_salida=rw_cerrar_orden(1,$cid,"");
        }
        //******** API Rentworks fin. ******/

        if ($enviar_orden_email==true) {
          require_once ('correo_servicio_pdf.php');
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
    $stud_arr[0]["pmsg"] ="ERROR ";


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
      if (isset($_REQUEST["id_tipo_mant"])) { $sqlcampos.= "  id_tipo_mant =".GetSQLValue($_REQUEST["id_tipo_mant"],"int"); } 
      if (isset($_REQUEST["id_inspeccion"])) { $sqlcampos.= " , id_inspeccion =".GetSQLValue($_REQUEST["id_inspeccion"],"int"); } 
      if (isset($_REQUEST["id_tipo_revision"])) { $sqlcampos.= " , id_tipo_revision =".GetSQLValue($_REQUEST["id_tipo_revision"],"int"); } 
      if (isset($_REQUEST["tipo_servicio"])) { $sqlcampos.= " , tipo_servicio =".GetSQLValue($_REQUEST["tipo_servicio"],"int"); } 
     
      if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
      if (isset($_REQUEST["id_tecnico1"])) { $sqlcampos.= " , id_tecnico1 =".GetSQLValue($_REQUEST["id_tecnico1"],"int"); } 
      if (isset($_REQUEST["id_tecnico2"])) { $sqlcampos.= " , id_tecnico2 =".GetSQLValue($_REQUEST["id_tecnico2"],"int"); } 
      if (isset($_REQUEST["id_tecnico3"])) { $sqlcampos.= " , id_tecnico3 =".GetSQLValue($_REQUEST["id_tecnico3"],"int"); } 
      if (isset($_REQUEST["id_tecnico4"])) { $sqlcampos.= " , id_tecnico4 =".GetSQLValue($_REQUEST["id_tecnico4"],"int"); } 

      if (isset($_REQUEST["fecha_hora_ingreso"])) { $sqlcampos.= " , fecha_hora_ingreso =".GetSQLValue($_REQUEST["fecha_hora_ingreso"],"text"); } 
      if (isset($_REQUEST["fecha_hora_asigna"])) { $sqlcampos.= " , fecha_hora_asigna =".GetSQLValue($_REQUEST["fecha_hora_asigna"],"text"); } 
      if (isset($_REQUEST["fecha_hora_promesa"])) { $sqlcampos.= " , fecha_hora_promesa =".GetSQLValue($_REQUEST["fecha_hora_promesa"],"text"); } 
      if (isset($_REQUEST["fecha_hora_final"])) { $sqlcampos.= " , fecha_hora_final =".GetSQLValue($_REQUEST["fecha_hora_final"],"text"); } 
      if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
      if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      if (isset($_REQUEST["estado_paro_por_repuesto"])) { $sqlcampos.= " , estado_paro_por_repuesto =".GetSQLValue($_REQUEST["estado_paro_por_repuesto"],"text"); }   

    if ($nuevoreg==true){
        //Crear nuevo     
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('servicio',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into servicio set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
        
    } else {
      //actualizar
         $sql="update servicio  set " . $sqlcampos . " where id=".$elcodigo . " LIMIT 1";
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

	$result = sql_select("SELECT servicio.* 
  ,entidad.nombre AS cliente_nombre
  ,entidad.codigo_alterno AS cliente_codigo
  ,producto.nombre AS producto_nombre

  ,servicio_estado.nombre AS elestado
  ,servicio_tipo_mant.nombre AS eltipo
  ,servicio_tipo_revision.nombre AS eltiporevision
  ,tec1.nombre AS eltecnico1
  ,tec2.nombre AS eltecnico2
  ,tec3.nombre AS eltecnico3
  ,tec4.nombre AS eltecnico4
  ,taller.nombre AS taller_nombre
  ,taller.codigo_alterno AS taller_codigo
  ,case 
   when servicio.estado_paro_por_repuesto='A' then 'Activo'
   when servicio.estado_paro_por_repuesto='I' then 'Inactivo' end
   AS estadoparoporrepuesto
  ,(SELECT inspeccion.numero from inspeccion WHERE inspeccion.id=servicio.id_inspeccion ) AS numero_inspeccion

  FROM servicio
  LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
  LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
  LEFT OUTER JOIN servicio_estado ON (servicio.id_estado=servicio_estado.id)
  LEFT OUTER JOIN servicio_tipo_mant ON (servicio.id_tipo_mant=servicio_tipo_mant.id)
  LEFT OUTER JOIN servicio_tipo_revision ON (servicio.id_tipo_revision=servicio_tipo_revision.id)
  LEFT OUTER JOIN usuario tec1 ON (servicio.id_tecnico1=tec1.id)
  LEFT OUTER JOIN usuario tec2 ON (servicio.id_tecnico2=tec2.id)
  LEFT OUTER JOIN usuario tec3 ON (servicio.id_tecnico3=tec3.id)
  LEFT OUTER JOIN usuario tec4 ON (servicio.id_tecnico4=tec4.id)
  LEFT OUTER JOIN entidad taller ON (servicio.id_taller=taller.id)
                   
  where servicio.id=$cid limit 1");

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
if (isset($row["id_tipo_mant"])) {$id_tipo_mant= $row["id_tipo_mant"]; $eltipo=$row["eltipo"];} else {$id_tipo_mant= "";$eltipo="";}
if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= "";}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
if (isset($row["tipo_servicio"])) {$tipo_servicio= $row["tipo_servicio"]; } else {$tipo_servicio= "";}
if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["numero_alterno"])) {$numero_alterno= $row["numero_alterno"]; } else {$numero_alterno= "";}
if (isset($row["id_tecnico1"])) {$id_tecnico1= $row["id_tecnico1"]; $eltecnico1=$row["eltecnico1"]; } else {$id_tecnico1= ""; $eltecnico1="";}
if (isset($row["id_tecnico2"])) {$id_tecnico2= $row["id_tecnico2"]; $eltecnico2=$row["eltecnico2"];} else {$id_tecnico2= "";$eltecnico2="";}
if (isset($row["id_tecnico3"])) {$id_tecnico3= $row["id_tecnico3"]; $eltecnico3=$row["eltecnico3"];} else {$id_tecnico3= "";$eltecnico3="";}
if (isset($row["id_tecnico4"])) {$id_tecnico4= $row["id_tecnico4"]; $eltecnico4=$row["eltecnico4"];} else {$id_tecnico4= "";$eltecnico4="";}

if (isset($row["fecha_hora_ingreso"])) {$fecha_hora_ingreso= formato_fechahora_de_mysql($row["fecha_hora_ingreso"]) ; } else {$fecha_hora_ingreso= $now_fechahoraT;}
if (isset($row["fecha_hora_asigna"])) {$fecha_hora_asigna= formato_fechahora_de_mysql($row["fecha_hora_asigna"]); } else {$fecha_hora_asigna= "";}
if (isset($row["fecha_hora_promesa"])) {$fecha_hora_promesa= formato_fechahora_de_mysql($row["fecha_hora_promesa"]); } else {$fecha_hora_promesa= "";}
if (isset($row["fecha_hora_final"])) {$fecha_hora_final= formato_fechahora_de_mysql($row["fecha_hora_final"]); } else {$fecha_hora_final= "";}
if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; $cliente_nombre=$row["cliente_codigo"].' '.$row["cliente_nombre"];} else {$cliente_id= ""; $cliente_nombre="";}

if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}
if (isset($row["chasis"])) {$chasis= $row["chasis"]; } else {$chasis= "";}

if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
if (isset($row["nota_operaciones"])) {$nota_operaciones= $row["nota_operaciones"]; } else {$nota_operaciones= "";}

if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
if (isset($row["numero_inspeccion"])) {$numero_inspeccion= $row["numero_inspeccion"]; } else {$numero_inspeccion= "";}

if (isset($row["id_taller"])) {$id_taller= $row["id_taller"]; $taller_nombre=$row["taller_codigo"]. ' '.$row["taller_nombre"]; } else {$id_taller="";$taller_nombre="";}

if (isset($row["fecha_auditado"])) {$fecha_auditado= $row["fecha_auditado"]; } else {$fecha_auditado= "";}
if (isset($row["id_usuario_auditado"])) {$id_usuario_auditado= $row["id_usuario_auditado"]; } else {$id_usuario_auditado= "";}

if (isset($row["reproceso"])) {$reproceso= $row["reproceso"]; } else {$reproceso= "";}
if (isset($row["observaciones_reproceso"])) {$observaciones_reproceso= $row["observaciones_reproceso"]; } else {$observaciones_reproceso= "";}
if (isset($row["estado_paro_por_repuesto"])) {$estado_paro_por_repuesto= $row["estado_paro_por_repuesto"]; $estadoparoporrepuesto= $row["estadoparoporrepuesto"];} else {$estado_paro_por_repuesto= ""; $estadoparoporrepuesto= "";}

$taller_externo="";
if (!es_nulo($id_taller)){
   $taller_externo=get_dato_sql('cita_taller','externo','WHERE id_taller='.$id_taller); 
}
$estado_PPR="";  ///habilita estado para por repuesto
if (!es_nulo($id_estado)){
  $estado_PPR=get_dato_sql('servicio_estado','estado','WHERE id='.$id_estado); 
}

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
            
            <div class="col-md-2">       
                <?php echo campo("numero","Numero",'labelb',$numero,' ',' ');  ?>              
            </div>

            <div class="col-md-2">       
                <?php echo campo("fecha","Fecha",'labelb',formato_fecha_de_mysql($fecha),' ',' ');  ?>              
            </div>

            <div class="col-md-2">       
                <?php echo campo("id_tienda","Tienda",'labelb',get_dato_sql('tienda','nombre',' where id='.$id_tienda),' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                 <?php //echo campo("estado","Estado",'labelb',$elestado,'',' ','');  
                    echo campo("estado","Estado",'labelb',$elestado,'',' ',"servicio_editarcampo('estado','Estado','$id_estado');");                         
                 ?>                   
            </div>
            <?php if (!es_nulo($estado_PPR)) { ?>
            <div class="col-md-3">       
                 <?php //echo campo("estado","Estado",'labelb',$elestado,'',' ','');  
                    echo campo("estadoparo","Estado de Paro por Repuesto",'labelb',$estadoparoporrepuesto,'',' ',"servicio_editarcampo('estadoparo','Estado por Paro de Repuesto','$estado_paro_por_repuesto');");                         
                 ?>                   
            </div>
            <?php }  ?>
</div>


<div class="tab-content" id="nav-tabContent">
 
<!-- DETALLE  -->
  <div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >
    
      <form id="forma" name="forma" class="needs-validation" novalidate>
      <fieldset id="fs_forma">

        
      <input id="id_inspeccion" name="id_inspeccion" type="hidden" value="<?php echo $id_inspeccion; ?>" >
      <input id="id_estado" name="id_estado" type="hidden" value="<?php echo $id_estado; ?>" >
      <input id="taller_externo" name="taller_externo" type="hidden" value="<?php echo $taller_externo; ?>" >

    
      <input id="id" name="id"  type="hidden" value="<?php echo $id; ?>" >

      <div class="row"> 
            <div class="col-md-6">       
                <?php 
               // echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ',$disable_sec1,'get.php?a=2&t=1',$cliente_nombre);  
               echo campo("cliente_nombre","Cliente",'labelb',$cliente_nombre,' ',$disable_sec1,"servicio_editarcampo('cliente_id','Cliente','$cliente_id');");  
                
               ?> 
               <input id="cliente_id" name="cliente_id"  type="hidden" value="<?php echo $cliente_id; ?>" >   
            </div>

            <div class="col-md-6">       
                <?php 
                $producto_etiqueta="";
                if (!es_nulo($id_producto)) {$producto_etiqueta=get_dato_sql('producto',"concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,''))"," where id=".$id_producto); }  
               // echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ',$disable_sec1,'get.php?a=3&t=1',$producto_etiqueta);
               echo campo("producto","Vehiculo",'labelb',$producto_etiqueta,' ',$disable_sec1,"servicio_editarcampo('id_producto','Vehiculo','$id_producto');");
              
                     ?>  
                     <input id="id_producto" name="id_producto"  type="hidden" value="<?php echo $id_producto; ?>" >        
            </div>      
         
      </div>
     
      <div class="row"> 
            <div class="col-md-5"> 
                 <?php                  
                 
                   echo campo("id_taller","Taller",'labelb',$taller_nombre,'',$disable_sec1,"servicio_editarcampo('id_taller','Taller','$id_taller');");
                                    
                 ?>  
                 <input id="id_taller" name="id_taller"  type="hidden" value="<?php echo $id_taller; ?>" > 

            </div>
            <div class="col-md-7">
            <div class="d-inline-block p-3">       
                <?php 
                echo campo("ninspeccion","# Inspeccion",'labelb',$numero_inspeccion,' ',$disable_sec1);
             
                    ?>         
            </div>  
            <div class="d-inline-block p-3">       
                <?php 
                echo campo("kilomentraje","Kilometraje",'labelb',$kilometraje,' ',$disable_sec1,"servicio_editarcampo('kilometraje','Kilometraje','$kilometraje');");
             
                    ?>         
            </div>  

            <div class="d-inline-block p-3">       
                <?php 
                echo campo("placa","Placa",'labelb',$placa,' ',$disable_sec1,"servicio_editarcampo('id_producto','Vehiculo','$id_producto');");
             
                    ?>         
            </div>    
            <div class="d-inline-block p-3">       
                <?php 
                echo campo("chasis","Chasis",'labelb',$chasis,' ',$disable_sec1,"servicio_editarcampo('id_producto','Vehiculo','$id_producto');");
             
                    ?>         
            </div>    
            </div>  
      </div>

      <hr>
 
      
      <div class="row"> 
   
            <div class="col-md-5">       
                <?php 
                  // echo campo("id_tipo_mant","Tipo",'select2',valores_combobox_db('servicio_tipo_mant',$id_tipo_mant,'nombre','','','...'),'',$disable_sec1,'');
                  echo campo("tipo_mant","Tipo Mantenimiento",'labelb',$eltipo,'',$disable_sec1,"servicio_editarcampo('id_tipo_mant','Tipo Mantenimiento','$id_tipo_mant');");
                   
                   ?>  
                   <input id="id_tipo_mant" name="id_tipo_mant"  type="hidden" value="<?php echo $id_tipo_mant; ?>" >        
            </div>

            <div class="col-md-5">       
                 <?php 
                  // echo campo("id_tipo_revision","Tipo Revision",'select2',valores_combobox_db('servicio_tipo_revisin',$id_tipo_revision,'nombre','','','...'),'',$disable_sec1,'');
                  echo campo("tipo_revision","Tipo Revision",'labelb',$eltiporevision,'',$disable_sec1,"servicio_editarcampo('servicio_tipo_revision','Tipo de Revision','$id_tipo_revision');");
                                    
                   ?>  
                   <input id="id_tipo_revision" name="id_tipo_revision"  type="hidden" value="<?php echo $id_tipo_revision; ?>" > 
            </div>

            <div class="col-md-2"> 
                  <a class="btn btn-info btn-sm text-white" onclick="servicio_abrir_multik(); return false;" ><i class="fa fa-clipboard-list"></i> Multi-K</a> 
            </div>
      </div>

      <hr>


     
<?php 
  if (es_nulo($fecha_hora_asigna)) {
    echo '<div class="row">';
    echo ' <a href="#" onclick="'."servicio_editarcampo('id_tecnico1','Tecnico Asignado','');".' return false;" class="btn btn-warning btn-sm ml-3"><i class="fa fa-edit"></i> Asignar Mecanico</a>';
    echo '</div><hr>';

  } else {
  
?>
    <div class="row"> 
    <div class="col-md-4"> 
        <?php 
        // echo campo("id_tecnico1","Mecanico Asignado",'select2',valores_combobox_db('usuario',$id_tecnico1,'nombre',' where activo=1 and grupo_id=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',$disable_sec1); 
        echo campo("eltecnico1","Mecanico Asignado",'labelb',$eltecnico1,'',' ',"servicio_editarcampo('id_tecnico1','Mecanico Asignado','$id_tecnico1');"); 
        ?>
    </div>
    <div class="col-md-4"> 
        <?php 
       // echo campo("id_tecnico2","Mecanico Auxiliar",'select2',valores_combobox_db('usuario',$id_tecnico2,'nombre',' where activo=1 and grupo_id=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',$disable_sec1); 
       echo campo("eltecnico2","Mecanico Asignado",'labelb',$eltecnico2,'',' ',"servicio_editarcampo('id_tecnico2','Mecanico Asignado','$id_tecnico2');"); 
        ?>
    </div>
    <div class="col-md-4"> 
        <?php 
        //echo campo("id_tecnico3","Tecnico Auxiliar",'select2',valores_combobox_db('usuario',$id_tecnico3,'nombre',' where activo=1 and grupo_id=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',$disable_sec1); 
        echo campo("eltecnico3","Mecanico Asignado",'labelb',$eltecnico3,'',' ',"servicio_editarcampo('id_tecnico3','Mecanico Asignado','$id_tecnico3');"); 
        ?>
    </div>
    <div class="col-md-4"> 
        <?php 
        //echo campo("id_tecnico4","Tecnico Auxiliar",'select2',valores_combobox_db('usuario',$id_tecnico3,'nombre',' where activo=1 and grupo_id=2 and tienda_id='.$_SESSION['tienda_id'],'','...'),' ',$disable_sec1); 
        echo campo("eltecnico4","Mecanico Asignado",'labelb',$eltecnico4,'',' ',"servicio_editarcampo('id_tecnico4','Mecanico Asignado','$id_tecnico4');"); 
        ?>
    </div>
   
    </div>

<?php 
  }
?>
   


      <div class="row"> 
    <div class="col-md-3"> 
        <?php echo campo("fecha_hora_ingreso","Fecha/Hora Ingreso",'labelb',$fecha_hora_ingreso,' ',' '); ?>
    </div>

    <div class="col-md-3"> 
        <?php echo campo("fecha_hora_asigna","Fecha/Hora Asignado",'labelb',$fecha_hora_asigna,' ',' '); ?>
    </div>

    <div class="col-md-3"> 
        <?php echo campo("fecha_hora_promesa","Fecha/Hora Promesa",'labelb',$fecha_hora_promesa,' ',' '); ?>
    </div>

    <div class="col-md-3"> 
        <?php echo campo("fecha_hora_final","Fecha/Hora Entrega",'labelb',$fecha_hora_final,' ',' ');  ?>
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
                echo campo("nota_operaciones","Nota Operaciones",'labelb',$nota_operaciones,' ','  ',"servicio_editarcampo('nota_operaciones','Nota Operaciones','$nota_operaciones');");
                     ?>  
              
            </div>
          </div>
          <hr>
          <div class="row"> 
            <div class="col-md">     
                <?php
                echo campo("observa","Observaciones",'labelb',nl2br($observaciones),' ','  '.$disable_sec6,"servicio_editarcampo('observa','Observaciones','');");                
                ?>  
              
            </div>
            
            
          </div> 
          <?php   if ( !es_nulo($reproceso)) {   ?>
            <hr>
            <div class="row"> 
              <div class="col-md">     
                  <?php
                  echo campo("observa","Observaciones Reproceso",'labelb',nl2br($observaciones_reproceso),' ','  '.$disable_sec6,"");                
                  ?>                
              </div>                        
            </div> 
          <?php  }   ?>
        </div>
      </div>      
    




      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
            Detalle Actividades            
            <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Compra Realizada','servicio_mant_repuesto.php?a=comprea&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Compra Realizada</a>
            <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitud de Compra','servicio_mant_repuesto.php?a=solcomp&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Solicitud Compra</a>
            <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Realizado','servicio_mant_repuesto.php?a=realiza&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Realizado</a>
            <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Atender','servicio_mant_repuesto.php?a=atender&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-wrench"></i> Atender</a>
            <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Autorizar Servicio','servicio_mant_repuesto.php?a=aut&tipo=3&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Autorizar</a>
            <a class="mr-3  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Agregar Servicio','servicio_mant_repuesto.php?a=agr&tipo=3&cid='+$('#id').val()+'&estactual='+$('#id_estado').val()+'&taller='+$('#taller_externo').val()); return false;"><i class="fa fa-plus"></i> Agregar</a>
         </div>
        <div class="card-body"> 

         <?php
            $lin=1;
    
              ?>  

     

          <div class="row"> 
          <div id="servicio_detalle_actividad" class="table-responsive">  
              <!-- <ul id="detalleul_servicio" class="list-group"> -->
              <table id="detalleul_servicio" class="table table-striped table-hover" style="width:100%">
                <thead>
                  <tr>
                   <th><input type="checkbox"  onchange="servicio_marcar_todos(this,'detalleul_servicio'); "  ></th>
                    <th>Codigo</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Tipo</th>
                    <th>Horas Planeadas</th>
                    <th>Horas Reales</th>
                    <th>Nota</th>
                    <th>Estado</th>
                    <th>Usuario</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="tbody_servicio_det">
                 
              
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
  
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Devolver Repuesto','servicio_mant_repuesto.php?a=dev&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-minus"></i> Devolver</a>
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Repuesto NO Recibido','servicio_mant_repuesto.php?a=norec&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-store-slash"></i> No Recibido</a>           
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Compra Realizada','servicio_mant_repuesto.php?a=comprea&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Compra Realizada</a>    
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Compra en Proceso','servicio_mant_repuesto.php?a=comppro&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-truck"></i>Compra Extranjero</a>           
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Compra Local','servicio_mant_repuesto.php?a=complocal&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Compra Local</a>    
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitud Compra Repuesto','servicio_mant_repuesto.php?a=solcomp&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-shopping-cart"></i> Solicitud Compra</a>
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Recibir Repuesto','servicio_mant_repuesto.php?a=rec&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-wrench"></i> Recibir</a>
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Autorizar Repuesto','servicio_mant_repuesto.php?a=aut&tipo=2&cid='+$('#id').val()); return false;"><i class="fa fa-check"></i> Autorizar</a>
           <a class="mr-3 mb-1  btn btn-sm btn-info  d-print-none float-right" href="#" onclick="modalwindow2('Solicitar Repuesto','servicio_mant_repuesto.php?a=agr&tipo=2&cid='+$('#id').val()+'&estactual='+$('#id_estado').val()); return false;"><i class="fa fa-plus"></i> Solicitar Repuesto</a>           
        </div>
        <div class="card-body">   

    
        
           <div class="row">
           <div id="servicio_detalle_repuesto" class="table-responsive">
            <!-- <ul id="detalleul_repuesto" class="list-group"> -->
              
            <table id="detalleul_repuesto" class="table table-striped table-hover" style="width:100%">
            <thead>
                  <tr>
                    <th><input type="checkbox"  onchange="servicio_marcar_todos(this,'detalleul_repuesto'); "  ></th>
                    <th>Codigo</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Nota</th>
                    <th>Estado</th>
                    <th>Inventario</th>
                    
                    <th></th>
                  </tr>
                </thead>
                <tbody  id="tbody_servicio_rep">
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
            
            <a href="#" onclick="servicio_procesar_occ('Crear Orden de Compra','ocompra'); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus "></i> Orden de Compra</a>
            <a href="#" onclick="servicio_procesar_occ('Crear Orden de Cobro','ocobro'); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-plus "></i> Orden de Cobro</a>
            <a href="#" onclick="servicio_borrar_registro('tbody_servicio_rep',2); return false;" class="btn btn-sm btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-trash "></i> Borrar</a>

            
          </div>
          </div>
          </div>


           <div class="row">
            <div class="col-md">  
            <?php

                  
         
         
      
            // echo campo("tipo_servicio","Tipo Servicio",'number',$tipo_servicio,' ',' ');
          
            // echo campo("numero_alterno","Numero Alterno",'text',$numero_alterno,' ',' ');
           
                 ?>
            </div>
           
            
          </div>                

      
      
      <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <!-- <a href="#" onclick="procesar_servicio('servicio_mant.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check <?php echo $visible_guardar; ?>"></i> Guardar</a> -->
           
           <?php
           //BOTONES de ESTADO
           $estado_actualizar="";
           $estado_txt="";
           $addfecha="&lk=1";
           $estado_actualizar2="";
           $estado_actualizar3="";
           $estado_txt2="";

           if ($id_estado<3 and tiene_permiso(60)) {
            $estado_actualizar="3";
            $estado_txt="APROBAR";
            $addfecha="&lk=1";
           }

           if (($id_estado==3 )  and tiene_permiso(75)) { //or $id_estado==7
            $estado_actualizar="4";
            $estado_txt="ATENDER";
            $addfecha="&lk=1";
           }

           if ($id_estado==4 and tiene_permiso(76)) {
            $estado_actualizar="21";
            $estado_txt="REALIZADO";
            $addfecha="&lk=1";
            if (tiene_permiso(77)) { //pausar
              $estado_actualizar2="7";//paro
              $estado_txt2="PAUSAR";
            }
           }

           if ($id_estado==21  and tiene_permiso(61)) {
            $estado_actualizar="22";
            $estado_txt="COMPLETADA";
            $addfecha="&lk=1";
           }


           if ( $estado_actualizar<>"") {   
            ?>
            <a href="#" onclick="servicio_editarcampo('estado_btn','<?php echo $estado_txt; ?>','<?php echo $estado_actualizar; ?>','<?php echo $addfecha; ?>'); return false;" class="btn btn-warning mr-2 mb-2 xfrm" ><i class="fa fa-check <?php echo $visible_guardar; ?>"></i> <?php echo $estado_txt; ?></a>
            
            <a href="#" onclick="procesar_get('servicio_mant_repuesto.php?a=emlaut&tipo=0&cid=<?php echo $id; ?>'); return false;" class="btn btn-outline-info ml-3 mr-2 mb-2 xfrm" ><i class="fa fa-envelope "></i> Solicitar Autorización</a>
            
                      
            <?php }    ?>

            <?php   if ( $estado_actualizar2<>"") {   ?>
            
              <a href="#" onclick="servicio_editarcampo('estado_btn','<?php echo $estado_txt2; ?>','<?php echo $estado_actualizar2; ?>','<?php echo $addfecha; ?>'); return false;" class="btn btn-outline-secondary ml-3 mr-2 mb-2 xfrm" ><i class="fa fa-clock <?php echo $visible_guardar; ?>"></i> <?php echo $estado_txt2; ?></a>
        
            <?php }    ?>   
            
            <?php   if ( $id_estado==21 and tiene_permiso(165) and es_nulo($reproceso)) {   ?>
            
                <a href="#" onclick="servicio_editarcampo('reproceso','Reproceso','23','<?php echo $addfecha; ?>'); return false;" class="btn btn-outline-secondary ml-3 mr-2 mb-2 xfrm" ><i class="fa fa-repeat <?php echo $visible_guardar; ?>"></i> Reproceso </a>
           
            <?php }    ?>
                     
            <?php   if (tiene_permiso(163)) {   ?>
            
                   <a href="#" onclick="servicio_editarcampo('auditado','Auditado','23','<?php echo $addfecha; ?>'); return false;" class="btn btn-outline-secondary ml-3 mr-2 mb-2 xfrm" ><i class="fa fa-clock <?php echo $visible_guardar; ?>"></i> Revision ADPC</a>
                   
            <?php }    ?>

            <?php if ($nuevoreg==false) { ?>
              <div class="float-right">

              <?php if (!es_nulo($id_inspeccion)) { ?>
                <a href="#" onclick="get_page('pagina','inspeccion_mant.php?a=v&cid=<?php echo $id_inspeccion; ?>','Hoja de Inspección',false); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-file-medical-alt"></i> Abrir Inspección</a>
              <?php } ?>
                <a href="#" onclick="get_page('pagina','servicio_mant.php?a=v&cid='+$('#id').val(),'Orden de Servicio',false); return false;" class="btn btn-success mr-2 mb-2 xfrm" ><i class="fa fa-redo-alt"></i> Actualizar</a>

                
              <a href="servicio_pdf.php?pdfcod=<?php echo $id; ?>" target="_blank"  class="btn btn-secondary mb-2"><i class="fa fa-print"></i> Imprimir</a>
              <a class="btn btn-secondary text-white mb-2 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"  ></a>                      
                        <div class="dropdown-menu">
                          <a class="dropdown-item" href="servicio_pdf.php?pdfcod=<?php echo $id; ?>&pc=1" target="_blank">Imprimir Costo</a>
                          <div class="dropdown-divider"></div>                        
                        </div>
              
              </div>
            <?php } ?>

          </div>
          </div>
        </div>

      
        </fieldset>
        </form> 
  
        <div class="row"><div class="col mt-5 px-3 py-2">  
          <?php if(isset($_REQUEST['btnseg'])){ ?>  
            <a href="#" onclick="get_page_regresar('pagina','dashboard_seguimiento.php','Dashboard Seguimiento') ;  return false;" class="btn btn-outline-secondary mr-2 mb-2">Regresar</a> 
          <?php } else { ?>  
            <a href="#" onclick="get_page_regresar('pagina','servicio_ver.php','Ver Ordenes de Servicio') ;  return false;" class="btn btn-outline-secondary mr-2 mb-2">Regresar</a> 
          <?php } ?>
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
    procesar_servicio_foto('nav_fotos');
  }
  if (eltab=='nav_historial') {
    procesar_servicio_historial('nav_historial');
  }
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

 


   

}


function procesar_servicio(url,forma,adicional){

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

				get_page('pagina','servicio_mant.php?a=v&cid='+json[0].pcid,'Orden de Servicio',false) ; 
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





function procesar_servicio_foto(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var insp=$('#id_inspeccion').val();
  var url='servicio_fotos.php?cid='+cid+'&pid='+pid+'&insp='+insp ;
 
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

function procesar_servicio_historial(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var url='servicio_historial.php?cid='+cid+'&pid='+pid ;
 
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





function servicio_borrar_registro(objeto,tipo) {

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
      servicio_procesar_selec('servicio_mant_repuesto.php?a=del',objeto,tipo);
       
	  }
	})

}


function servicio_totlinea(linea){
		var acctmp=$(linea).closest('tr').data("acc");
		if (acctmp!='D' && acctmp!='I') {
		  var nombreli= $(linea).closest('tr').attr('id');
		  $('#'+nombreli+' input[name="det_acc[]"]').val('U');
      $(linea).closest('tr').data("acc",'U');
		}
	  
		  //	calcular_totales();
		  }	




function servicio_editarcampo(nombre,etiqueta,valor,adicional=''){
  var estado = $('#id_estado').val();  
  if (estado<22){
     modalwindow('Editar','servicio_mant.php?a=ec&nom='+encodeURI(nombre)+'&sid='+$('#id').val()+'&eti='+encodeURI(etiqueta)+'&val='+encodeURI(valor)+adicional);
   }else{
    <?php if (tiene_permiso(164) or tiene_permiso(163)) { ?>
         modalwindow('Editar','servicio_mant.php?a=ec&nom='+encodeURI(nombre)+'&sid='+$('#id').val()+'&eti='+encodeURI(etiqueta)+'&val='+encodeURI(valor)+adicional);
    <?php } ?>
   } 
}


function procesar_modificar_cmp(url,forma,adicional){
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
				
        
				get_page('pagina','servicio_mant.php?a=v&cid='+json[0].pcid,'Orden de Servicio',false) ; 
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



function servicio_marcar_todos(objeto,tabla){
  $("#"+tabla+" input[type='checkbox']").prop('checked',  $(objeto).prop('checked'));
	// $("#"+tabla+" input[type='checkbox']").checkboxradio();
	// $("#"+tabla+" input[type='checkbox']").checkboxradio("refresh");

}

function servicio_procesar_occ(nombre,campo){
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
      modalwindow2(nombre,'servicio_mant_oc.php?a='+campo+'&tipo=0&cid='+$('#id').val(),datos);
    }
    
    if (campo=='ocobro') {
      modalwindow2(nombre,'servicio_mant_ocobro.php?a='+campo+'&tipo=0&cid='+$('#id').val(),datos);
    }
 
}
}


function servicio_procesar_selec(url,objeto,tipo){

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
				
	
			//	get_page('pagina','servicio_mant.php?a=v&cid='+json[0].pcid,'Orden de Servicio',false) ; 
			get_box('tbody_servicio_det','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=3')	;
      get_box('tbody_servicio_rep','servicio_mant.php?a=detall'+'&cid='+$('#id').val()+'&tipo=2')	;
      
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

function servicio_abrir_ocompra(ordenid) {
  modalwindow2('Orden de Compra','compra_mant.php?a=v&cid='+ordenid); 


}

function servicio_abrir_ocobro(ordenid) {
  modalwindow2('Orden de Cobro','cobro_mant.php?a=v&cid='+ordenid); 
  
}

function servicio_abrir_multik() {
  modalwindow2('Multi-K','servicio_multik.php?a=v&cid='+$('#id').val()); 
  
}


function calcular_pv(linea){
  var nombreli= $(linea).closest('tr').attr('id');
  var cod_producto=$('#'+nombreli+' input[name="det_alt[]"]').val();
  var exento_ganancia = <?php echo json_encode($_SESSION['p_exento_ganancia']); ?>;
  var costo=parseFloat($(linea).val());
  
  if (jQuery.inArray( cod_producto, exento_ganancia )>0) {
    var precio=costo;
  } else {
    var precio=costo+(costo*<?php echo $_SESSION['p_ganancia']; ?>);
  }
  //ATM
  let tmpprod = cod_producto;
  if (tmpprod.substr(0, 3)=='ATM') {
    precio=<?php echo $_SESSION['p_cobro_atm_hora']; ?> ;
  }
 
  
	$('#'+nombreli+' input[name="det_pv[]"]').val(precio);
  

}	

function serv_mant_actualizar_veh() {
  var datos=$('#id_producto_tmp').select2('data')[0];

  $('#placa_tmp').val(datos.placa);
  $('#chasis_tmp').val(datos.chasis);
    
}




 
      
</script>