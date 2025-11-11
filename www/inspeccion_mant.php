<?php

require_once ('include/framework.php');
pagina_permiso(22);


if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="";}
$cid=0;

$disable_sec1='';
$disable_sec2='';
$disable_sec3='';
$disable_sec32='';
$disable_sec4='';
$disable_sec5='';
$disable_sec6='';
$disable_sec7='';
$disable_sec8='';
$disable_sec9='';
$disable_firma='';

$visible_sec3='';
$visible_sec4='';
$visible_guardar='';
$visible_modificar=' oculto';
$kilometraje_minimo=0;
$detalles="";
$detalles2="";

if (isset($_REQUEST['ti'])) { $tipo_insp = $_REQUEST['ti']; } else   {$tipo_insp ="1";}
if (isset($_REQUEST['tie'])) { $tipo_insp_especial = $_REQUEST['tie']; } else   {$tipo_insp_especial ="";}
if (isset($_REQUEST['td'])) { $tipo_doc = $_REQUEST['td']; } else   {$tipo_doc ="1";}
if (isset($_REQUEST['em'])) { $empresa = $_REQUEST['em'];  } else   {$empresa ="0"; }
if (isset($_REQUEST['tv'])) { $tipo_veh = $_REQUEST['tv']; } else   {$tipo_veh ="";}
if (isset($_REQUEST['cv'])) { $codigo_veh = intval($_REQUEST['cv']); } else   {$codigo_veh ="";}
if (isset($_REQUEST['idant'])) { $codigo_insp_ant = intval($_REQUEST['idant']); } else   {$codigo_insp_ant ="0";}
if (isset($_REQUEST['retorno'])) { $retorno = true; } else   {$retorno = false; }

if (isset($_REQUEST['cli'])) { $cliente_asignar = intval($_REQUEST['cli']); } else   {$cliente_asignar ="";}
if (isset($_REQUEST['cn'])) { $contacto_asignar = ($_REQUEST['cn']); } else   {$contacto_asignar ="";}
if (isset($_REQUEST['ci'])) { $identidad_asignar = ($_REQUEST['ci']); } else   {$identidad_asignar ="";}
if (isset($_REQUEST['ct'])) { $telefono_asignar = ($_REQUEST['ct']); } else   {$telefono_asignar ="";}
if (isset($_REQUEST['ce'])) { $email_asignar = ($_REQUEST['ce']); } else   {$email_asignar ="";}
if (isset($_REQUEST['km'])) { $km_asignar = intval($_REQUEST['km']); } else   {$km_asignar ="";}
if (isset($_REQUEST['cd'])) { $ciudad_asignar = ($_REQUEST['cd']); } else   {$ciudad_asignar ="";}
if (isset($_REQUEST['ob'])) { $observaciones_asignar = ($_REQUEST['ob']); } else   {$observaciones_asignar ="";}
if (isset($_REQUEST['cit'])) { $id_cita = intval($_REQUEST['cit']); } else   {$id_cita ="";}

$elcodigo='';
if (isset($_REQUEST["id"])) {$elcodigo=intval($_REQUEST["id"]);}
if (isset($_REQUEST["cid"])) {$elcodigo=intval($_REQUEST["cid"]);}
if (es_nulo($elcodigo)) {$nuevoreg=true;} else {$nuevoreg=false;}

// borrar     ############################  
if ($accion=="del") {

  $stud_arr[0]["pcode"] = 0;
  $stud_arr[0]["pmsg"] ="Error";
  $stud_arr[0]["pcid"] = 0;

  if (!tiene_permiso(114)) {
      $stud_arr[0]["pmsg"] ="No tiene privilegios para Borrar";
  } else {



$result = sql_select("SELECT id_estado
      FROM inspeccion
  where id=$elcodigo limit 1");

if ($result!=false){
  if ($result -> num_rows > 0) { 
    $row = $result -> fetch_assoc(); 
          if ($row['id_estado']<2) {
              sql_delete("DELETE FROM inspeccion where id=$elcodigo limit 1");
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

    /*$nombre_cliente="";
    $cliente=0;*/

    $salida="";
    $verror="";
    if (isset($_REQUEST['modenc'])) {
      $modificando_encabezado=true;
      if (!tiene_permiso(113)) { $verror="No tiene privilegio para modificar una orden de Inspeccion";}
    } else {
      $modificando_encabezado=false;
    }
        

    //Validar

  if ($verror=="") {

     if (isset($_REQUEST['idet'])) {
      $det_insp= array();
      foreach ($_REQUEST['idet'] as $key => $value) {
        $det_insp[$key]=$value;
      } 
      $detalles1= json_encode($det_insp);
     }
     
      //Campos
      $sqlcampos="";
      $sqlcampos_detalle="";

      $lbl_estado="";

      
      if (isset($_REQUEST["id_empresa"])) { $sqlcampos.= "  id_empresa =".GetSQLValue($_REQUEST["id_empresa"],"int"); } 
       
      if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); }
       
      if (isset($_REQUEST["cliente_email"])) { $sqlcampos.= " , cliente_email =".GetSQLValue($_REQUEST["cliente_email"],"text"); } 
      if (isset($_REQUEST["cliente_contacto"])) { $sqlcampos.= " , cliente_contacto =".GetSQLValue($_REQUEST["cliente_contacto"],"text"); } 
      if (isset($_REQUEST["cliente_contacto_identidad"])) { $sqlcampos.= " , cliente_contacto_identidad =".GetSQLValue($_REQUEST["cliente_contacto_identidad"],"text"); } 
      if (isset($_REQUEST["cliente_contacto_telefono"])) { $sqlcampos.= " , cliente_contacto_telefono =".GetSQLValue($_REQUEST["cliente_contacto_telefono"],"text"); } 


      if (isset($_REQUEST["tipo_inspeccion"])) { $sqlcampos.= " , tipo_inspeccion =".GetSQLValue($_REQUEST["tipo_inspeccion"],"int"); } 
      if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
      if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 

      if (isset($_REQUEST["renta_contrato"])) { $sqlcampos.= " , renta_contrato =".GetSQLValue($_REQUEST["renta_contrato"],"text"); } 
      if (isset($_REQUEST["renta_factura"])) { $sqlcampos.= " , renta_factura =".GetSQLValue($_REQUEST["renta_factura"],"text"); } 
      if (isset($_REQUEST["renta_estacion"])) { $sqlcampos.= " , renta_estacion =".GetSQLValue($_REQUEST["renta_estacion"],"text"); } 
  
      if (isset($_REQUEST["kilometraje_entrada"])) { $sqlcampos.= " , kilometraje_entrada =".GetSQLValue($_REQUEST["kilometraje_entrada"],"int"); } 
      if (isset($_REQUEST["kilometraje_minimo"])) { $sqlcampos.= " , kilometraje_minimo =".GetSQLValue($_REQUEST["kilometraje_minimo"],"int"); } 
    
      if (isset($_REQUEST["combustible_entrada"])) { $sqlcampos.= " , combustible_entrada =".GetSQLValue($_REQUEST["combustible_entrada"],"text"); } 
      
      if (isset($_REQUEST["combustible_tipo"])) { $sqlcampos.= " , combustible_tipo =".GetSQLValue($_REQUEST["combustible_tipo"],"text"); } 
      if (isset($_REQUEST["placa"])) { $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa"],"text"); }
      if (isset($_REQUEST["chasis"])) { $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis"],"text"); }
  
     if ($modificando_encabezado==true) {
        $actualizarkm=true;//ojo no quitar por si esta modificando 
        $sqlcampos.= " , modificada ='".date("d/m/Y h:ia")."  " .$_SESSION['usuario'] ."'" ;
     }

  if ($modificando_encabezado==false) {
      $tipodoc="";
      if (isset($_REQUEST["tipo_doc"])){
          $tipodoc=intval($_REQUEST["tipo_doc"]);
      }
      $tipoinsp="";           
      if (isset($_REQUEST["tipo_inspeccion"])){
          $tipoinsp=intval($_REQUEST["tipo_inspeccion"]);          
      }
      if (isset($_REQUEST["tipo_doc"])) { $sqlcampos.= " , tipo_doc =".GetSQLValue($_REQUEST["tipo_doc"],"int"); }    
      // if (isset($_REQUEST["fecha_hora_inicio"])) { $sqlcampos.= " , fecha_hora_inicio =".GetSQLValue($_REQUEST["fecha_hora_inicio"],"text"); } 
      // if (isset($_REQUEST["fecha_hora_final"])) { $sqlcampos.= " , fecha_hora_final =".GetSQLValue($_REQUEST["fecha_hora_final"],"text"); } 
      if (isset($_REQUEST["plantilla_vehiculo"])) { $sqlcampos.= " , plantilla_vehiculo =".GetSQLValue($_REQUEST["plantilla_vehiculo"],"text"); }       
      if (isset($_REQUEST["fecha_entrada"])) { $sqlcampos.= " , fecha_entrada =".GetSQLValue($_REQUEST["fecha_entrada"],"text"); }   
      if (isset($_REQUEST["hora_entrada"])) { $sqlcampos.= " , hora_entrada =".GetSQLValue($_REQUEST["hora_entrada"],"text"); } 
    
  
      if (isset($_REQUEST["llanta_delantera_izq"])) { $sqlcampos.= " , llanta_delantera_izq =".GetSQLValue($_REQUEST["llanta_delantera_izq"],"text"); } 
      if (isset($_REQUEST["llanta_delantera_izq_num"])) { $sqlcampos.= " , llanta_delantera_izq_num =".GetSQLValue($_REQUEST["llanta_delantera_izq_num"],"text"); } 
      if (isset($_REQUEST["llanta_delantera_der"])) { $sqlcampos.= " , llanta_delantera_der =".GetSQLValue($_REQUEST["llanta_delantera_der"],"text"); } 
      if (isset($_REQUEST["llanta_delantera_der_num"])) { $sqlcampos.= " , llanta_delantera_der_num =".GetSQLValue($_REQUEST["llanta_delantera_der_num"],"text"); } 
      if (isset($_REQUEST["llanta_trasera_izq"])) { $sqlcampos.= " , llanta_trasera_izq =".GetSQLValue($_REQUEST["llanta_trasera_izq"],"text"); } 
      if (isset($_REQUEST["llanta_trasera_izq_num"])) { $sqlcampos.= " , llanta_trasera_izq_num =".GetSQLValue($_REQUEST["llanta_trasera_izq_num"],"text"); } 
      if (isset($_REQUEST["llanta_trasera_der"])) { $sqlcampos.= " , llanta_trasera_der =".GetSQLValue($_REQUEST["llanta_trasera_der"],"text"); } 
      if (isset($_REQUEST["llanta_trasera_der_num"])) { $sqlcampos.= " , llanta_trasera_der_num =".GetSQLValue($_REQUEST["llanta_trasera_der_num"],"text"); } 
      if (isset($_REQUEST["llanta_repuesto"])) { $sqlcampos.= " , llanta_repuesto =".GetSQLValue($_REQUEST["llanta_repuesto"],"text"); } 
      if (isset($_REQUEST["llanta_repuesto_num"])) { $sqlcampos.= " , llanta_repuesto_num =".GetSQLValue($_REQUEST["llanta_repuesto_num"],"text"); } 
      if (isset($_REQUEST["llanta_extra1"])) { $sqlcampos.= " , llanta_extra1 =".GetSQLValue($_REQUEST["llanta_extra1"],"text"); } 
      if (isset($_REQUEST["llanta_extra1_num"])) { $sqlcampos.= " , llanta_extra1_num =".GetSQLValue($_REQUEST["llanta_extra1_num"],"text"); } 
      if (isset($_REQUEST["llanta_extra2"])) { $sqlcampos.= " , llanta_extra2 =".GetSQLValue($_REQUEST["llanta_extra2"],"text"); } 
      if (isset($_REQUEST["llanta_extra2_num"])) { $sqlcampos.= " , llanta_extra2_num =".GetSQLValue($_REQUEST["llanta_extra2_num"],"text"); } 
     
      if (isset($_REQUEST["llanta_delantera_izq_cali"])) { $sqlcampos.= " , llanta_delantera_izq_cali =".GetSQLValue($_REQUEST["llanta_delantera_izq_cali"],"int"); } 
      if (isset($_REQUEST["llanta_delantera_der_cali"])) { $sqlcampos.= " , llanta_delantera_der_cali =".GetSQLValue($_REQUEST["llanta_delantera_der_cali"],"int"); } 
      if (isset($_REQUEST["llanta_trasera_der_cali"])) { $sqlcampos.= " , llanta_trasera_der_cali =".GetSQLValue($_REQUEST["llanta_trasera_der_cali"],"int"); } 
      if (isset($_REQUEST["llanta_trasera_izq_cali"])) { $sqlcampos.= " , llanta_trasera_izq_cali =".GetSQLValue($_REQUEST["llanta_trasera_izq_cali"],"int"); } 
     
      if (isset($_REQUEST["bateria_marca"])) { $sqlcampos.= " , bateria_marca =".GetSQLValue($_REQUEST["bateria_marca"],"text"); } 
      if (isset($_REQUEST["bateria_num"])) { $sqlcampos.= " , bateria_num =".GetSQLValue($_REQUEST["bateria_num"],"text"); } 
      if (isset($_REQUEST["grua"])) { $sqlcampos.= " , grua =".GetSQLValue($_REQUEST["grua"],"int"); } 
      if (isset($_REQUEST["grua_orden"])) { $sqlcampos.= " , grua_orden =".GetSQLValue($_REQUEST["grua_orden"],"text"); } 
      if (isset($_REQUEST["grua_factura"])) { $sqlcampos.= " , grua_factura =".GetSQLValue($_REQUEST["grua_factura"],"text"); } 
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
     
      if (isset($_REQUEST["trabajo_realizar"])) { $sqlcampos.= " , trabajo_realizar =".GetSQLValue($_REQUEST["trabajo_realizar"],"text"); } 
      
      if (isset($_REQUEST["detalles_canvas"])) { $sqlcampos.= " , detalles_canvas =".GetSQLValue($_REQUEST["detalles_canvas"],"text"); } 
     
     if (isset($_REQUEST["firma1_canvas"])) { $sqlcampos.= " , firma1_canvas =".GetSQLValue($_REQUEST["firma1_canvas"],"text"); }
     if (isset($_REQUEST["firma2_canvas"])) { $sqlcampos.= " , firma2_canvas =".GetSQLValue($_REQUEST["firma2_canvas"],"text"); }

     if (isset($_REQUEST["tipo_inspeccion_especial"])) { $sqlcampos.= " , tipo_inspeccion_especial =".GetSQLValue($_REQUEST["tipo_inspeccion_especial"],"int"); }

     if (isset($_REQUEST["observaciones_adpc"])) { $sqlcampos.= " , observaciones_adpc =".GetSQLValue($_REQUEST["observaciones_adpc"],"text"); }        
      
     $actualizarkm=false;//ojo no quitar . por si esta modificando 
     $enviar_orden_email=false;
     $lbl_estado="Borrador";
     $trasladosP="";
     $Codigo_Alterno="";
     $Oservicio="";
     $Ocombustible="";
     $valida_fotos="";
     $ParoPorRepuesto=""; 
     $EstadoReparacion="";
     $ListoParaVenta="";
      if (isset($_REQUEST["gg_est"])) { 
        $nuevo_estado=intval($_REQUEST["gg_est"]);        
        if (!es_nulo($nuevo_estado)) {   
           
            $cid=$elcodigo;
            if (!es_nulo($cid)) { 
              $valida_fotos=get_dato_sql("inspeccion_foto","COUNT(*)"," WHERE id_inspeccion=".$cid);
              if (es_nulo($valida_fotos) or $valida_fotos<=9){
                  $stud_arr[0]["pmsg"] ="Son 10 fotos las requeridas para completar la Hoja de Inspeccion, le faltan las ".(10-$valida_fotos)." fotos"; 
                  salida_json($stud_arr);
                  exit; 
              }                          
            }            
            if ($nuevo_estado==2) {
                   
              //Valida que no tenga traslado pendientes
              $Codigo_Alterno=get_dato_sql("producto","COUNT(*)"," WHERE left(codigo_alterno,7)='EA-0000' and id=".intval($_REQUEST['id_producto']));          
              if (es_nulo($Codigo_Alterno)){
                  $trasladosP=get_dato_sql("orden_traslado","COUNT(*)"," WHERE id_estado<3 AND id_producto=".intval($_REQUEST['id_producto']));
                  if ($trasladosP>0) {
                    $stud_arr[0]["pmsg"] =" Tiene orden de traslado sin completar del vehiculo"; 
                    salida_json($stud_arr);
                    exit;            
                  }
              }       
              if ($tipoinsp==1 and $tipodoc==2){  
                 $EstadoReparacion=get_dato_sql("ventas","COUNT(*)"," WHERE id_estado=99 AND id_producto=".intval($_REQUEST['id_producto']));
                  if (!es_nulo($EstadoReparacion)){
                      $stud_arr[0]["pmsg"] =" El Vehiculo esta en proceso de reparacion"; 
                      salida_json($stud_arr);
                      exit;  
                  }                
                
                  $ParoPorRepuesto=get_dato_sql("servicio","COUNT(*)"," WHERE id_estado=7 AND (estado_paro_por_repuesto='I' or estado_paro_por_repuesto=null)  AND id_producto=".intval($_REQUEST['id_producto']));                 
                  $Oservicio=get_dato_sql("servicio","COUNT(*)"," WHERE id_estado not in (20,22,7) AND id_producto=".intval($_REQUEST['id_producto']));                 
                  $Ocombustible=get_dato_sql("orden_combustible","COUNT(*)"," WHERE id_estado<3 AND id_producto=".intval($_REQUEST['id_producto']));                 
                  if (!es_nulo($Oservicio) or !es_nulo($ParoPorRepuesto)){
                      $stud_arr[0]["pmsg"] =" Tiene Orden de Servicio sin completar del vehiculo"; 
                      salida_json($stud_arr);
                      exit;            
                  }

                  if (!es_nulo($Ocombustible)){
                      $stud_arr[0]["pmsg"] =" Tiene Orden de Combustible sin completar del vehiculo"; 
                      salida_json($stud_arr);
                      exit;  
                  }
                }
 
              $lbl_estado="Completado";

              // enviar email a cliente 
              $enviar_orden_email=true;

              // $sqlcampos.= " , fecha_entrada =NOW()"; 
     
              //  $sqlcampos.= " , hora_entrada =NOW()";                
            }
            $sqlcampos.= " , id_estado =".GetSQLValue($nuevo_estado,"int");
            $sqlcampos.= " , id_usuario_completado =".$_SESSION["usuario_id"];
            $actualizarkm=true;
        }else{
            $lbl_estado="Revision ADPC";
            $sqlcampos.= " , fecha_auditado =NOW()";            
            $sqlcampos.= " , id_usuario_auditado =".$_SESSION["usuario_id"]; 
            $sqlcampos.= " , observaciones_adpc =".GetSQLValue($_REQUEST["observaciones_adpc"],"text");  
        }
      } 
     

      if (isset($detalles1)) { $sqlcampos.= " , detalles =".GetSQLValue($detalles1,"text"); } 
      
    }//modificando_encabezado


      if ($nuevoreg==true){
        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('inspeccion',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sqlcampos.= " , id_inspeccion_anterior =".GetSQLValue($codigo_insp_ant,"int");
        $sql="insert into inspeccion set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 

          //  historial
          sql_insert("INSERT INTO inspeccion_historial_estado (id_maestro,  id_usuario,  nombre, fecha, observaciones)
          VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Nueva Hoja de Inspeccion', NOW(), '')");

        // actualizar CITA
        if (!es_nulo($id_cita)) {
          sql_update("UPDATE cita	SET id_inspeccion=$cid WHERE id=$id_cita LIMIT 1");
        }
        
    } else {
        //actualizar
         $sql="update inspeccion  set " . $sqlcampos . " where id=".$elcodigo;
         $result = sql_update($sql);
         $cid=$elcodigo;

         //historial
         sql_insert("INSERT INTO inspeccion_historial_estado (id_maestro,  id_usuario,  nombre, fecha, observaciones)
         VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Guardar ".$lbl_estado."', NOW(), '')");

    }
    

      if ($result!=false){

        if ($modificando_encabezado==false) {
            //actualizar inspeccion anterior
            if (!es_nulo($codigo_insp_ant) and $nuevoreg==true){
                sql_update("update inspeccion set id_estado=3 where id=$codigo_insp_ant LIMIT 1");}
            // //Detalle
            // $sql="INSERT INTO manifiesto_guia_detalle SET guia_id=".GetSQLValue($cid,"int").$sqlcampos_detalle." ";
            // $result_detalle = sql_insert($sql);
            
        }
        //actualizar Km
        if($actualizarkm==true) {sql_update("UPDATE producto SET km=".GetSQLValue($_REQUEST["kilometraje_entrada"],"int")."  WHERE id=".GetSQLValue($_REQUEST["id_producto"],"int"));}
        
          
          $stud_arr[0]["pcode"] = 1;
          $stud_arr[0]["pmsg"] ="Guardado";
          $stud_arr[0]["pcid"] = $cid;

          if ($enviar_orden_email==true) {
              require_once ('correo_inspeccion_pdf.php');
              ///Valido el vehiculo con el cliente de Ventas de carro usado CVU
              if (isset($_REQUEST['cliente_id'])){                
                  $clienteCvu=substr(get_dato_sql("entidad","codigo_alterno"," WHERE id=".GetSQLValue($_REQUEST["cliente_id"],"int")),0,4);    
                  if ($clienteCvu=="CVU-") {
                    sql_update("UPDATE ventas SET id_inspeccion=".$cid."  WHERE id_producto=".GetSQLValue($_REQUEST["id_producto"],"int"));     
                  }
              }                
          }

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
if ($accion=="v") {
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }
	$result = sql_select("SELECT inspeccion.* 
                  ,entidad.nombre AS cliente_nombre
                  ,entidad.codigo_alterno AS cliente_codigo
                  ,entidad.email AS cliente_email_entidad
                  ,producto.nombre AS producto_nombre
                  FROM inspeccion
                  LEFT OUTER JOIN entidad ON (inspeccion.cliente_id=entidad.id)
                  LEFT OUTER JOIN producto ON (inspeccion.id_producto =producto.id)
                  
                        where inspeccion.id=$cid limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
			$row = $result -> fetch_assoc(); 
			// $detalle_result = sql_select("SELECT * FROM manifiesto_guia_detalle where guia_id=$cid");
			// $tracking_result = sql_select("SELECT * FROM manifiesto_guia_tracking where guia_id=$cid");
		}
	}
} // fin leer datos


//Validaciones nuevo registro
if ($nuevoreg==true) {
    $valerror="";
    $ordenesborrador=0;
    if ($codigo_veh<>'' ){
      $ordenesborrador=get_dato_sql("inspeccion","COUNT(*)"," WHERE id_estado=1 AND id_producto=".$codigo_veh);
      if ($ordenesborrador>0) {
        $valerror=mensaje("No puede crear una nueva Hoja de Inspección porque actualmente se encontraron $ordenesborrador  Hojas de Inspección en estado de borrador, para continuar debe completar estas  Hojas de Inspección o borrarlas",'warning');
        $valerror.='<br><br> <a id="btn-filtro" href="#" onclick="get_page(\'pagina\',\'inspeccion_ver.php\',\'Ver Inspecciones\') ; return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Buscar Hojas de Inspección</a>';
      } 
    
    if($tipo_insp_especial<>'1') {// ignorar validacion en especial

        //-- ultima entrada
        $result_ultima_entrada = sql_select("SELECT id,fecha,id_estado,tipo_doc,id_inspeccion_anterior 
        ,tipo_inspeccion
        ,(SELECT i2.id FROM inspeccion i2 WHERE i2.id_inspeccion_anterior=inspeccion.id ORDER BY i2.id DESC LIMIT 1) AS lasalida
        FROM inspeccion 
        WHERE inspeccion.tipo_doc=1 AND (id_estado=2 OR id_estado=3)
        AND id_producto=".$codigo_veh."
        ORDER BY id desc LIMIT 1;");

        if ($result_ultima_entrada!=false){
          if ($result_ultima_entrada -> num_rows > 0) { 
            $row_ult_entrada = $result_ultima_entrada -> fetch_assoc();

            if($tipo_doc=='1') { 
              //Entrada
              if (es_nulo($row_ult_entrada['lasalida'])) {
                //**Deshabilitado temporalmente: Cuando lo reactivemos (yo le avisaría) lo mejor es que el cambio no fuera retroactivo, es decir que considere los cambios desde la fecha de reactivación en adelante, si fuera posible
                // $valerror=mensaje("No puede crear una nueva Hoja de Inspección porque existe una Hoja de Inspección de ENTRADA que aun no ha sido recibida, para continuar debe completar estas  Hojas de Inspección o borrarlas",'warning');
                // $valerror.='<br><br> <a id="btn-filtro" href="#" onclick="get_page(\'pagina\',\'inspeccion_ver.php\',\'Ver Inspecciones\') ; return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Buscar Hojas de Inspección</a>';
              }
          } else { 
              //Salida
              //$codigo_insp_ant
              if (!es_nulo($row_ult_entrada['lasalida']) and $row_ult_entrada['lasalida']==$codigo_insp_ant) {
                //**Deshabilitado temporalmente: Cuando lo reactivemos (yo le avisaría) lo mejor es que el cambio no fuera retroactivo, es decir que considere los cambios desde la fecha de reactivación en adelante, si fuera posible
                // $valerror=mensaje("No puede crear una nueva Hoja de Inspección porque existe una Hoja de Inspección de SALIDA que ya fue completada",'warning');
                // $valerror.='<br><br> <a id="btn-filtro" href="#" onclick="get_page(\'pagina\',\'inspeccion_ver.php\',\'Ver Inspecciones\') ; return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Buscar Hojas de Inspección</a>';
              }

              if(es_nulo($row_ult_entrada['lasalida']) and $row_ult_entrada['tipo_inspeccion']<>$tipo_insp){
                //**Deshabilitado temporalmente: Cuando lo reactivemos (yo le avisaría) lo mejor es que el cambio no fuera retroactivo, es decir que considere los cambios desde la fecha de reactivación en adelante, si fuera posible
                // $valerror=mensaje("No puede crear una nueva Hoja de Inspección porque difiere el tipo de inspeccion RENTA/TALLER",'warning');
                // $valerror.='<br><br> <a id="btn-filtro" href="#" onclick="get_page(\'pagina\',\'inspeccion_ver.php\',\'Ver Inspecciones\') ; return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Buscar Hojas de Inspección</a>';
              }
  
          }

          }
        }

       
      }

   


    // //-- ultima Salida
    // $result_ultima_salida = sql_select("SELECT id,fecha,id_estado,tipo_doc,id_inspeccion_anterior FROM inspeccion 
    // WHERE inspeccion.tipo_doc=2 AND (id_estado=2 OR id_estado=3)
    // ORDER BY id desc LIMIT 1;");

    // if ($result_ultima_salida!=false){
    //   if ($result_ultima_salida -> num_rows > 0) { 
    //     $row_ult_salida = $result_ultima_salida -> fetch_assoc();
    //   }
    // }


}


  


  if ($valerror<>"") {
  
    echo '<div class="card-body">
            <div class="row"> 
              <div class="col">
                  '.$valerror.'
              </div>
            </div>
        </div>
    ';
    exit;
  }
}


// inicializar  
if (isset($row["id"])) {$id= $row["id"]; } else {$id= 0;}
if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= $_SESSION['usuario_id'];}
if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= $_SESSION['tienda_id'];}
if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else { $id_estado= "1"; } 
if (isset($row["id_inspeccion_anterior"])) {$id_inspeccion_anterior= $row["id_inspeccion_anterior"]; } else { $id_inspeccion_anterior= "0"; }
if (isset($row["id_empresa"])) {$id_empresa= $row["id_empresa"]; } else {$id_empresa= $empresa;}
if (isset($row["fecha"])) {$fecha= $row["fecha"]; } else {$fecha= date('Y-m-d');}
if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= date('H:i');}
if (isset($row["tipo_inspeccion"])) {$tipo_inspeccion= $row["tipo_inspeccion"]; } else {if($tipo_insp=='1') {$tipo_inspeccion= "1";} else {$tipo_inspeccion= "2";}} 
if (isset($row["tipo_inspeccion_especial"])) {$tipo_inspeccion_especial= $row["tipo_inspeccion_especial"]; } else {$tipo_inspeccion_especial= $tipo_insp_especial;}

if (isset($row["tipo_doc"])) {$tipo_doc= $row["tipo_doc"]; } else { } 

if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
if (isset($row["numero_alterno"])) {$numero_alterno= $row["numero_alterno"]; } else {$numero_alterno= "";}
if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto=$codigo_veh; }
// if (isset($row["fecha_hora_inicio"])) {$fecha_hora_inicio= $row["fecha_hora_inicio"]; } else {$fecha_hora_inicio= "";}
// if (isset($row["fecha_hora_final"])) {$fecha_hora_final= $row["fecha_hora_final"]; } else {$fecha_hora_final= "";}
if (isset($row["plantilla_vehiculo"])) {$plantilla_vehiculo= $row["plantilla_vehiculo"]; } else {$plantilla_vehiculo= $tipo_veh;}
if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; $cliente_nombre=$row["cliente_codigo"].' '.$row["cliente_nombre"];} else {$cliente_id= ""; $cliente_nombre="";}


if (isset($row["cliente_email"])) { if ($nuevoreg==true) {$cliente_email=$row["cliente_email_entidad"];} else {$cliente_email= $row["cliente_email"];}  } else {$cliente_email= "";}
if (isset($row["cliente_contacto"])) {$cliente_contacto= $row["cliente_contacto"]; } else {$cliente_contacto= "";}
if (isset($row["cliente_contacto_identidad"])) {$cliente_contacto_identidad= $row["cliente_contacto_identidad"]; } else {$cliente_contacto_identidad= "";}
if (isset($row["cliente_contacto_telefono"])) {$cliente_contacto_telefono= $row["cliente_contacto_telefono"]; } else {$cliente_contacto_telefono= "";}


if (isset($row["renta_contrato"])) {$renta_contrato= $row["renta_contrato"]; } else {$renta_contrato= "";}
if (isset($row["renta_factura"])) {$renta_factura= $row["renta_factura"]; } else {$renta_factura= "";}
if (isset($row["renta_estacion"])) {$renta_estacion= $row["renta_estacion"]; } else {$renta_estacion= "";}
if (isset($row["fecha_entrada"])) {$fecha_entrada= $row["fecha_entrada"]; } else {$fecha_entrada= $now_fecha;}
if (isset($row["hora_entrada"])) {$hora_entrada= $row["hora_entrada"]; } else {$hora_entrada= $now_hora;}
if ($id_estado==1){
   $fecha_entrada= $now_fecha;
   $hora_entrada= $now_hora;
}

if (isset($row["kilometraje_entrada"])) {$kilometraje_entrada= $row["kilometraje_entrada"]; } else {$kilometraje_entrada= "";}
if (isset($row["kilometraje_minimo"])) {$kilometraje_minimo= $row["kilometraje_minimo"]; } else {$kilometraje_minimo= "";}


if (isset($row["combustible_entrada"])) {$combustible_entrada= $row["combustible_entrada"]; } else {$combustible_entrada= "";}

if (isset($row["combustible_tipo"])) {$combustible_tipo= $row["combustible_tipo"]; } else {$combustible_tipo= "";}
if (isset($row["llanta_delantera_izq"])) {$llanta_delantera_izq= $row["llanta_delantera_izq"]; } else {$llanta_delantera_izq= "";}
if (isset($row["llanta_delantera_izq_num"])) {$llanta_delantera_izq_num= $row["llanta_delantera_izq_num"]; } else {$llanta_delantera_izq_num= "";}
if (isset($row["llanta_delantera_der"])) {$llanta_delantera_der= $row["llanta_delantera_der"]; } else {$llanta_delantera_der= "";}
if (isset($row["llanta_delantera_der_num"])) {$llanta_delantera_der_num= $row["llanta_delantera_der_num"]; } else {$llanta_delantera_der_num= "";}
if (isset($row["llanta_trasera_izq"])) {$llanta_trasera_izq= $row["llanta_trasera_izq"]; } else {$llanta_trasera_izq= "";}
if (isset($row["llanta_trasera_izq_num"])) {$llanta_trasera_izq_num= $row["llanta_trasera_izq_num"]; } else {$llanta_trasera_izq_num= "";}
if (isset($row["llanta_trasera_der"])) {$llanta_trasera_der= $row["llanta_trasera_der"]; } else {$llanta_trasera_der= "";}
if (isset($row["llanta_trasera_der_num"])) {$llanta_trasera_der_num= $row["llanta_trasera_der_num"]; } else {$llanta_trasera_der_num= "";}
if (isset($row["llanta_repuesto"])) {$llanta_repuesto= $row["llanta_repuesto"]; } else {$llanta_repuesto= "";}
if (isset($row["llanta_repuesto_num"])) {$llanta_repuesto_num= $row["llanta_repuesto_num"]; } else {$llanta_repuesto_num= "";}
if (isset($row["llanta_extra1"])) {$llanta_extra1= $row["llanta_extra1"]; } else {$llanta_extra1= "";}
if (isset($row["llanta_extra1_num"])) {$llanta_extra1_num= $row["llanta_extra1_num"]; } else {$llanta_extra1_num= "";}
if (isset($row["llanta_extra2"])) {$llanta_extra2= $row["llanta_extra2"]; } else {$llanta_extra2= "";}
if (isset($row["llanta_extra2_num"])) {$llanta_extra2_num= $row["llanta_extra2_num"]; } else {$llanta_extra2_num= "";}

if (isset($row["llanta_delantera_izq_cali"])) {$llanta_delantera_izq_cali= $row["llanta_delantera_izq_cali"]; } else {$llanta_delantera_izq_cali= "";}
if (isset($row["llanta_delantera_der_cali"])) {$llanta_delantera_der_cali= $row["llanta_delantera_der_cali"]; } else {$llanta_delantera_der_cali= "";}
if (isset($row["llanta_trasera_der_cali"])) {$llanta_trasera_der_cali= $row["llanta_trasera_der_cali"]; } else {$llanta_trasera_der_cali= "";}
if (isset($row["llanta_trasera_izq_cali"])) {$llanta_trasera_izq_cali= $row["llanta_trasera_izq_cali"]; } else {$llanta_trasera_izq_cali= "";}


if (isset($row["bateria_marca"])) {$bateria_marca= $row["bateria_marca"]; } else {$bateria_marca= "";}
if (isset($row["bateria_num"])) {$bateria_num= $row["bateria_num"]; } else {$bateria_num= "";}

if (isset($row["grua"])) {$grua= $row["grua"]; } else {$grua= "0";}
if (isset($row["grua_orden"])) {$grua_orden= $row["grua_orden"]; } else {$grua_orden= "";}
if (isset($row["grua_factura"])) {$grua_factura= $row["grua_factura"]; } else {$grua_factura= "";}
if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}

if (isset($row["trabajo_realizar"])) {$trabajo_realizar= $row["trabajo_realizar"]; } else {$trabajo_realizar= "";}
if (isset($row["detalles"])) {$detalles= $row["detalles"]; } else {$detalles= "";}

if (isset($row["detalles_canvas"])) {$detalles_canvas= $row["detalles_canvas"]; } else {$detalles_canvas= "";}

if (isset($row["firma1_canvas"])) {$firma1_canvas= $row["firma1_canvas"]; } else {$firma1_canvas= "";}
if (isset($row["firma2_canvas"])) {$firma2_canvas= $row["firma2_canvas"]; } else {$firma2_canvas= "";}

if (isset($row["placa"])) {$placa= $row["placa"]; } else {$placa= "";}
if (isset($row["chasis"])) {$chasis= $row["chasis"]; } else {$chasis= "";}

if (isset($row["fecha_auditado"])) {$fecha_auditado= $row["fecha_auditado"]; } else {$fecha_auditado= date('Y-m-d');}
if (isset($row["id_usuario_auditado"])) {$id_usuario_auditado= $row["id_usuario_auditado"]; } else {$id_usuario_auditado= "0";}
if (isset($row["observaciones_adpc"])) {$observaciones_adpc= $row["observaciones_adpc"]; } else {$observaciones_adpc= "";}

//********* */
if ($nuevoreg==true) {
  if ($cliente_asignar<>'') {
    $cliente_id=$cliente_asignar;
    $cliente_nombre=get_dato_sql('entidad',"CONCAT(codigo_alterno,'  ',nombre)",' where id='.$cliente_asignar);
  }

  if ($contacto_asignar<>'') {
    $cliente_contacto=$contacto_asignar;   
  }

  if ($identidad_asignar<>'') {
    $cliente_contacto_identidad=$identidad_asignar;   
  }

  if ($telefono_asignar<>'') {
    $cliente_contacto_telefono=$telefono_asignar;   
  }

  if ($email_asignar<>'') {
    $cliente_email=$email_asignar;   
  }

  if ($km_asignar<>'') {
    $kilometraje_entrada=$km_asignar;   
  }


  if ($observaciones_asignar<>'' or $ciudad_asignar<>'') {
    if ($observaciones_asignar<>'') {$trabajo_realizar=$observaciones_asignar; }
    if ($ciudad_asignar<>'') {$trabajo_realizar.= ". Ciudad Procedencia: ".$ciudad_asignar;}  
  }
 
}
//********* */

// validar alerta km
$alerta_multik="";
if (!es_nulo($id_producto)) {
    $multik_result= sql_select("SELECT producto.km,producto.k5,producto.k10,producto.k20,producto.k40,producto.k100
    FROM producto
      where producto.habilitado=1
      and id=$id_producto
      and  producto.km IS NOT NULL
      AND (
    (km>=5000 AND k5 IS NULL)
    OR (km>=10000 AND k10 IS NULL)
    OR (km>=20000 AND k20 IS NULL)
    OR (km>=40000 AND k40 IS NULL)
    OR (km>=100000 AND k100 IS NULL)
    ) "
  );

  if ($multik_result!=false){
      if ($multik_result -> num_rows > 0) {
        $alerta_multik='<div class="row"><div class="col-md-12"><div class="alert alert-warning" role="alert">
        Este Vehiculo requiere mantenimiento Multi-K
      </div> </div></div>';
       }
      }
  }

///busco el numero del vehiculo
$valida_km=0;
$km_permitido=0;
$CodigoAlterno=0;
$nombre_cliente="";

if (!es_nulo($id_producto)){  
    $CodigoAlterno=get_dato_sql("producto","COUNT(*)"," WHERE left(codigo_alterno,7)='EA-0000' and id=".$id_producto);
    if (!es_nulo($CodigoAlterno)){
       $valida_km=0;
    }else{
       $valida_km=get_dato_sql("configuracion","maximo_kilometraje"," WHERE id=1");    
       $km_permitido=get_dato_sql("configuracion","minimo_kilometraje"," WHERE id=1");    
    } 
    $cliente=get_dato_sql("clientes_vehiculos","cliente_id"," WHERE id_producto=".$id_producto);  
    if (!es_nulo($cliente)){
       $nombre_cliente=get_dato_sql("entidad","nombre"," WHERE id=".$cliente);                              
    }else{
       $cliente=0;
    }       
}

// cargar orden inspeccion anterior
if ( !es_nulo($id_producto)) {
   $inspanterior=false;
   $sqlant=" inspeccion.id_producto=$id_producto ";
   if (!es_nulo($codigo_insp_ant) or !es_nulo($id_inspeccion_anterior)) {
      if (!es_nulo($id_inspeccion_anterior)) { $sqlant=" inspeccion.id=$id_inspeccion_anterior ";}
      if (!es_nulo($codigo_insp_ant)) { $sqlant=" inspeccion.id=$codigo_insp_ant ";}
         $inspanterior=true;
   }
 
   $result_ant = sql_select("SELECT inspeccion.* 
                  ,entidad.nombre AS cliente_nombre
                 
                  ,entidad.email AS cliente_email_entidad
                  ,producto.nombre AS producto_nombre
                  FROM inspeccion
                  LEFT OUTER JOIN entidad ON (inspeccion.cliente_id=entidad.id)
                  LEFT OUTER JOIN producto ON (inspeccion.id_producto =producto.id)
                     
                  where  $sqlant 
                  and inspeccion.id_estado<20
                  order by inspeccion.id desc
                  limit 1");

  if ($result_ant!=false){
    if ($result_ant -> num_rows > 0) { 
      $row_anterior = $result_ant -> fetch_assoc(); 


if ($nuevoreg==true) {
  if ($inspanterior==true) {
      $cliente_id= $row_anterior["cliente_id"]; 
      $cliente_nombre=$row_anterior["cliente_nombre"];
      $cliente_email= $row_anterior["cliente_email"];
      $cliente_contacto= $row_anterior["cliente_contacto"];
      $cliente_contacto_identidad= $row_anterior["cliente_contacto_identidad"]; 
      $cliente_contacto_telefono= $row_anterior["cliente_contacto_telefono"];
      $id_empresa= $row_anterior["id_empresa"];
      $renta_contrato= $row_anterior["renta_contrato"];
      $renta_factura= $row_anterior["renta_factura"];    
  }
      $kilometraje_minimo= $row_anterior["kilometraje_entrada"];
      $plantilla_vehiculo= $row_anterior["plantilla_vehiculo"]; 
      $combustible_tipo= $row_anterior["combustible_tipo"];
      $llanta_delantera_izq= $row_anterior["llanta_delantera_izq"]; 
      $llanta_delantera_izq_num= $row_anterior["llanta_delantera_izq_num"]; 
      $llanta_delantera_der= $row_anterior["llanta_delantera_der"]; 
      $llanta_delantera_der_num= $row_anterior["llanta_delantera_der_num"];
      $llanta_trasera_izq= $row_anterior["llanta_trasera_izq"]; 
      $llanta_trasera_izq_num= $row_anterior["llanta_trasera_izq_num"];
      $llanta_trasera_der= $row_anterior["llanta_trasera_der"]; 
      $llanta_trasera_der_num= $row_anterior["llanta_trasera_der_num"]; 
      $llanta_repuesto= $row_anterior["llanta_repuesto"]; 
      $llanta_repuesto_num= $row_anterior["llanta_repuesto_num"]; 

      $llanta_extra1= $row_anterior["llanta_extra1"]; 
      $llanta_extra1_num= $row_anterior["llanta_extra1_num"]; 
      $llanta_extra2= $row_anterior["llanta_extra2"]; 
      $llanta_extra2_num= $row_anterior["llanta_extra2_num"]; 

      $llanta_delantera_izq_cali= $row_anterior["llanta_delantera_izq_cali"]; 
      $llanta_delantera_der_cali= $row_anterior["llanta_delantera_der_cali"]; 
      $llanta_trasera_der_cali= $row_anterior["llanta_trasera_der_cali"]; 
      $llanta_trasera_izq_cali= $row_anterior["llanta_trasera_izq_cali"];

      $bateria_marca= $row_anterior["bateria_marca"]; 
      $bateria_num= $row_anterior["bateria_num"]; 

      $observaciones= $row_anterior["observaciones"]; 
      
      $detalles_canvas= str_replace('"red"','"blue"',$row_anterior["detalles_canvas"]) ;
      //$detalles_canvasant= str_replace('"red"','"blue"',$row_anterior["detalles_canvas"])  ;
 }
      $detalles2= $row_anterior["detalles"]; 
 }
}

}
////-----------------------
//validar que tenga plantilla
if (es_nulo($plantilla_vehiculo)) {
  if ( !es_nulo($id_producto)) {
    $asignar_tipo=get_dato_sql('producto','tipo_vehiculo',' WHERE id='.$id_producto);
    $plantilla_vehiculo_asignar='camioneta';
    switch ($asignar_tipo) {
      case 'TURISMO':
        $plantilla_vehiculo_asignar='turismo';
        break;
      case 'CAMIONETA':
        $plantilla_vehiculo_asignar='camioneta';
        break;
     case 'PICKUP':
        $plantilla_vehiculo_asignar='pickup';
        break;
      case 'PICK UP':
          $plantilla_vehiculo_asignar='pickup';
          break;
     case 'MICRO BUS':
        $plantilla_vehiculo_asignar='microbus';
        break; 
     case 'BUS':
        $plantilla_vehiculo_asignar='microbus';
        break;
      case 'CAMION':
        $plantilla_vehiculo_asignar='camion';
        break;  
        case 'CUATRIMOTO':
          $plantilla_vehiculo_asignar='cuatrimoto';
          break;   
    }
    $plantilla_vehiculo=$plantilla_vehiculo_asignar;
  }
}
 
$empresa_logo ="nd";
if ($id_empresa==1) {$empresa_logo ="hertz";}
if ($id_empresa==2) {$empresa_logo ="dollar";}
if ($id_empresa==3) {$empresa_logo ="thrifty";}

if ($tipo_inspeccion==1 and $tipo_doc==2){
   $visible_sec5='hidden'; 
}
else{
   $visible_sec5='hidden';
}

if ($id_estado>=2 ) { //completado solo ver
    $disable_sec1=' disabled="disabled" ';
    $disable_sec2=' disabled="disabled" ';
    $disable_sec3=' disabled="disabled" ';
    $disable_sec32=' disabled="disabled" ';
    $disable_sec4=' disabled="disabled" ';
    $disable_sec5=' disabled="disabled" ';
    $disable_sec6=' disabled="disabled" ';
    $disable_sec7=' disabled="disabled" ';
    $disable_sec8=' disabled="disabled" ';    
    $disable_sec9=' disabled="disabled" ';
    $disable_firma=' oculto ';
    $visible_sec3=' oculto';
    $visible_sec4=' oculto';
    $visible_guardar=' oculto'; 
    $visible_modificar=' '; 
 }else{
    $disable_sec9=' disabled="disabled" ';
    if(es_nulo($id_usuario_auditado)){
       $visible_auditar=' '; 
    }else{
       $visible_auditar=' oculto'; 
    }
 }


?>
 <div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="insp_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      <li class="nav-item">
        <a class="nav-link" id="insp_tabfotos" data-toggle="tab" href="#" onclick="insp_cambiartab('nav_fotos');"  role="tab" >Fotos y Documentos Adjuntos</a>       
      </li>
      <!-- <li class="nav-item">
        <a class="nav-link " id="insp_tabdoctos" data-toggle="tab" href="#" onclick="insp_cambiartab('nav_doctos');"   role="tab"  >Documentos Adjuntos</a>
      </li>      -->
      <li class="nav-item">
        <a class="nav-link " id="insp_tabhistorial" data-toggle="tab" href="#" onclick="insp_cambiartab('nav_historial');"   role="tab"  >Historial</a>
      </li> 
    </ul>   
 </div>

 
<div class="card-body">

<div class="row mb-2"> 
            <div class="col-md-2">       
                <img id="ins_logo_empresa" src="img/<?php echo $empresa_logo; ?>.jpg"> 
               
                        
            </div>

            <div class="col-md-2">       
            <?php
                $tipo_inspeccion_label="";
                if ($tipo_inspeccion==1) {
                  $tipo_inspeccion_label="Renta";
                  echo campo("empresa_sel","Empresa",'select',valores_combobox_texto(app_id_empresa,$id_empresa,'...'),' ',' onchange="insp_cambiar_logo();" '.$disable_sec1,'');
        
                }
                if ($tipo_inspeccion==2) {
                   $tipo_inspeccion_label="Taller";
                   if($tipo_inspeccion_especial==1){$tipo_inspeccion_label="Especial";}
                }


                ?>             
            </div>

            <div class="col-md-2">       
                <?php echo campo("tipo_lb","Tipo",'label',$tipo_inspeccion_label,' ',' ');  ?>              
            </div>

            <div class="col-md-2">       
                <?php echo campo("numero_lb","Numero",'label',$numero,' ',' ');  ?>              
            </div>

            <div class="col-md-2">       
                <?php echo campo("fecha_lb","Fecha",'label',formato_fecha_de_mysql($fecha),' ',' ');  ?>              
            </div>

            <div class="col-md-2">       
                <?php echo campo("id_tienda_lb","Tienda",'label',get_dato_sql('tienda','nombre',' where id='.$id_tienda),' ',' ');  ?>              
            </div>
            <div class="col-md-2">       
                <?php echo campo("tipo_doc_lb","Movimiento",'label',get_tipo_doc($tipo_doc),' ',' '); ?>                              
            </div>
</div>

<div class="tab-content" id="nav-tabContent">
 
<!-- DETALLE  -->
  <div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >
    
      <form id="forma" name="forma" class="needs-validation" novalidate>
      <fieldset id="fs_forma">

      <input id="tipo_inspeccion" name="tipo_inspeccion" type="hidden" value="<?php echo $tipo_inspeccion; ?>" >
      <input id="tipo_inspeccion_especial" name="tipo_inspeccion_especial" type="hidden" value="<?php echo $tipo_inspeccion_especial; ?>" >
      <input id="id_estado" name="id_estado" type="hidden" value="<?php echo $id_estado; ?>" >
      <input id="id_empresa" name="id_empresa" type="hidden" value="<?php echo $id_empresa; ?>" >
      <input id="plantilla_vehiculo" name="plantilla_vehiculo" type="hidden" value="<?php echo $plantilla_vehiculo; ?>" >
  
      <input id="tipo_doc" name="tipo_doc"  type="hidden" value="<?php echo $tipo_doc; ?>" >

      <input id="id" name="id"  type="hidden" value="<?php echo $id; ?>" >

      <input id="idant" name="idant"  type="hidden" value="<?php echo $codigo_insp_ant; ?>" >

      <input id="cit" name="cit"  type="hidden" value="<?php echo $id_cita; ?>" >
 <?php 
    echo $alerta_multik;
    if (isset($_REQUEST['alertar'])) {
      echo mensaje('Recuerde crear la Orden se Servicio 
      <a href="#" onclick="insp_crear_servicio(); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-plus"></i> Crear Orden de Servicio</a>','danger');
    }
  ?>

 <div id="insp_encb1"> 
      <div class="row"> 
            <div class="col-md-6">                     
                <?php   
                  echo campo("nombre_cliente","",'hidden',$nombre_cliente,'','','');         
                  echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,'class=" "',' onchange="insp_actualizar_email_cliente();" '.$disable_sec1  ,'get.php?a=2&t=1',$cliente_nombre);                            
                ?>                    
            </div>

            <div class="col-md-6">       
                <?php 
                $producto_etiqueta="";

                if (!es_nulo($id_producto) ) {
                  
                  $result = sql_select("SELECT concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,'')) AS producto_etiqueta
                                      ,placa,chasis   
                                      FROM producto 
                                      WHERE id=$id_producto limit 1");

                  if ($result!=false){
                    if ($result -> num_rows > 0) { 
                      $row = $result -> fetch_assoc(); 
                      $producto_etiqueta=$row['producto_etiqueta'];
                      if ( $nuevoreg==true) {
                      $placa=$row['placa'];
                      $chasis=$row['chasis'];
                      }

                    }
                  }
                }  

              //  echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,'class=" "',' onchange="insp_actualizar_veh();" '.$disable_sec1,'get.php?a=3&t=1',$producto_etiqueta);
              echo campo("id_producto","",'hidden',$id_producto,'','',''); 
              echo campo("id_producto_label","Vehiculo",'label',$producto_etiqueta,'','','');               
              
              ?>          
            </div>
      </div>

      <div class="row"> 
      <div class="col-md-4">       
            
                  </div>

           

            <div class="col-md-4">       
                <?php 
                $tipcampo="text";
                if (!es_nulo($placa)) { $tipcampo="label";} 
                echo campo("placa","Placa",$tipcampo,$placa,' ',' required '.$disable_sec1,'','');   
                ?>    
            </div>
             <div class="col-md-4">       
                <?php 
                $tipcampo="text";
                if (!es_nulo($chasis)) { $tipcampo="label";} 
                echo campo("chasis","Chasis",$tipcampo,$chasis,' ',' required '.$disable_sec1,'','');   
                ?>    
            </div>

            
      </div>

      <div class="row"> 
            <div class="col-md-3"> 
                <?php echo campo("cliente_email","Cliente Email",'text',$cliente_email,' ',' maxlength="255" '.$disable_sec1); ?>
            </div>
            <div class="col-md-3"> 
              <?php echo campo("cliente_contacto","Nombre del Contacto",'text',$cliente_contacto,' ',' maxlength="255" '.$disable_sec1); ?>
            </div>
            <div class="col-md-3"> 
              <?php echo campo("cliente_contacto_identidad","Identidad del Contacto",'text',$cliente_contacto_identidad,' ',' maxlength="16" '. $disable_sec1); ?>
            </div>
            <div class="col-md-3">               
               <?php echo campo("cliente_contacto_telefono","Telefono del Contacto",'text',$cliente_contacto_telefono,' ',' maxlength="35" '. $disable_sec1); ?>                         
            </div>
          </div>

<?php if ($tipo_inspeccion=='1'){ // RENTA 
  ?>
          <div class="row"> 
            <div class="col-md-4"> 
                <?php echo campo("renta_contrato","Contrato No.",'text',$renta_contrato,' ',$disable_sec1 .' ');  ?>
            </div>
            <div class="col-md-4"> 
              <?php //echo campo("renta_factura","Factura No.",'text',$renta_factura,' ',$disable_sec1 .' ');
              ?>
            </div>
            <div class="col-md-4"> 
              <?php // echo campo("renta_estacion","Estación Dueña",'text',$renta_estacion,' ',$disable_sec1); 
               ?>
            </div>

          </div>
<?php } // RENTA
?>
        
<?php if ($tipo_inspeccion=='2'){ // TALLER 
    
      //$tecnicos=valores_combobox_db('tecnico','','nombre','')
      ?>

       
<?php } // TALLER
?>

</div>


      <div  id="insp_encb2" class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
            Estatus del Vehiculo
        </div>
        <div class="card-body">   
        <span class="outside-label">Combustible</span>
          <?php echo campo_combustible('combustible_entrada',$combustible_entrada,$disable_sec2);        ?>

          <div class="row"> 
            <div class="col-md">       
                <?php 
                $kilometraje_min_add='';
                if (!tiene_permiso(113)) {$kilometraje_min_add=' min="'.$kilometraje_minimo.'" ';}                
                    echo campo("kilometraje_entrada","Kilometraje",'number',$kilometraje_entrada,' ',$disable_sec2 . $kilometraje_min_add .' required');                                                                         
                    //echo campo("kilometraje_minimo","",'hidden',$kilometraje_minimo,' ','');                                                                
                ?>  
            </div>
            <div class="col-md"> 
                 <?php echo campo("kilometraje_minimo","Kilometraje Anterior",'number',$kilometraje_minimo,' ',$disable_sec9 .' required'); ?>                
            </div>
            <div class="col-md"> 
                <?php   echo campo("fecha_entrada","Fecha",'date',$fecha_entrada,' ',$disable_sec2 .' required'); ?>
            </div>
            <div class="col-md"> 
              <?php echo campo("hora_entrada","Hora",'time',$hora_entrada,' ',$disable_sec2 .' required');?>
            </div>
            <div class="col-md"> 
              <?php echo campo("combustible_tipo","Tipo Combustible",'select',valores_combobox_texto(app_tipo_combustible,$combustible_tipo),' ',$disable_sec2 .' required');?>
            </div>
          </div>
          <p class="card-text"></p>

        </div>
      </div>


<?php if (tiene_permiso(113)) { ?>
      <div class="row">
          <div class="col">

            <a id="btn_modificar_insp" href="#" onclick="inspeccion_modificar(true); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm <?php echo $visible_modificar; ?>" ><i class="fa fa-edit"></i> Modificar</a>
            <a id="btn_modificar_insp_guardar" href="#" onclick="inspeccion_modificar(false); return false;" class="btn btn-outline-success mr-2 mb-2 xfrm oculto" ><i class="fa fa-edit"></i> Guardar</a>
          </div>

          <div class="col">
           
          </div>
          <div class="col">
            
          </div>
          <div class="col">
            
          </div>

      </div>

<?php } ?>

      <div class="card mb-3">
        <div class="card-header bg-secondary text-white ">
            Detalle del Vehiculo
        </div>
        <div class="card-body">
        <div class="row"> 

            <div id="insp_panel_izq_detalle" class="col-md"> 
                <?php 
                    //echo mostrar_detalle_inspeccion();
                   //$detalles= '{"27":"0","28":"1","29":"1"}';
                   $detalle_arr=json_decode($detalles,true);
                   $detalle_arr2=json_decode($detalles2,true);

                   
                    $det_salida='';
                    $det_encabezado='';
                    $detalle_inspeccion = sql_select("SELECT inspeccion_revision.id, inspeccion_revision.nombre, inspeccion_revision_grupo.nombre AS grupo
                                                    FROM inspeccion_revision
                                                    LEFT OUTER JOIN inspeccion_revision_grupo ON (inspeccion_revision.id_grupo=inspeccion_revision_grupo.id )
                                                    WHERE inspeccion_revision.tipo_inspeccion=1
                                                    ORDER BY inspeccion_revision.id_grupo,inspeccion_revision.orden"); //$tipo_inspeccion 
                    if ($detalle_inspeccion!=false){
                      if ($detalle_inspeccion -> num_rows > 0) {
                          $det_salida.='<ul class="list-group mb-3">' ;
                          while ($row_detalle = $detalle_inspeccion -> fetch_assoc()) {
                            if ($det_encabezado<>$row_detalle["grupo"]) {
                              $det_encabezado=$row_detalle["grupo"];
                              $det_salida.='  <li class="list-group-item list-group-item-inspeccion list-group-item-info">'.$row_detalle["grupo"].'</li> ' ;
                            }
                            $valact=''; $valor_anterior='';
                            if (isset($detalle_arr[$row_detalle["id"]])) {
                               $valact=$detalle_arr[$row_detalle["id"]];
                            }
                            if (isset($detalle_arr2[$row_detalle["id"]])) {
                               $valor_anterior=$detalle_arr2[$row_detalle["id"]];
                            }
                            
                            $det_salida.='  <li class="list-group-item list-group-item-inspeccion list-group-item-action d-flex justify-content-between align-items-center" >'.$row_detalle["nombre"].'<span class="text-nowrap">'.insp_sino_radio($row_detalle["id"],$valact,$valor_anterior,'required').'</span></li> ' ;

                          }
                          $det_salida.=' </ul>' ;  
                      }
                    } 
                    echo $det_salida;
                    
                  
                  function insp_sino_radio($id,$valor,$valor_anterior,$required) {
                    global $nuevoreg,$retorno,$tipo_doc,$tipo_inspeccion,$id_estado,$codigo_insp_ant,$id_inspeccion_anterior;
                    $salida='';
                    $si="";
                    $no="";
                    $tmpvalor=$valor;
                    $columnas=1;
                    $clase="";
                    if (ceroif_nulo($valor)<>ceroif_nulo($valor_anterior)) {$clase=" enrojo"; }
                   
                    if ($tipo_inspeccion=='1' and $tipo_doc=='2'){ $columnas=1; } //RENTA
                    if ($tipo_inspeccion=='1' and $tipo_doc=='1'){ $columnas=2; } //RENTA
                    if ($tipo_inspeccion=='2' and $tipo_doc=='1'){ $columnas=1; } //TALLER
                    if ($tipo_inspeccion=='2' and $tipo_doc=='2'){ $columnas=2; } //TALLER
                   
                    if ($columnas==1) {
                      if ($id_estado==1) {
                        $salida.=insp_radio_btn($id,$valor,$required);
                      } else {
                        $salida.=insp_radio_label($id,$valor);
                      }
                    }

                    if ($columnas>=2) {
                      if (!es_nulo($codigo_insp_ant) or !es_nulo($id_inspeccion_anterior)) {
                        $salida.=insp_radio_label($id,$valor_anterior,$clase);
                      }
                      if ($id_estado==1) {
                        $salida.=insp_radio_btn($id,$valor,$required,$valor_anterior,$columnas);
                      } else {
                        $salida.=insp_radio_label($id,$valor,$clase);
                      }

                    }
                   
   
              
                    return $salida;
                  }

                  
                  function  insp_radio_btn($id,$valor,$required,$valor_anterior='',$columnas=1){
                    global $nuevoreg;
                    $salida='';
                    $si="";
                    $no="";
                    $tmpvalor=$valor;
                                   
                    $tmpsalida="";
                    
                    if ($id==4 or $id==9 or $id==23 or $id==29  ) { 
                      $salida.=' <input class="detancho" type="number" name="idet['.$id.']" id="insp'.$id.'no" value="'.$tmpvalor.'" '.$required.'>';
                     } else {

                        $salida='';
                        $si="";
                        $no="";

                        $tmpvalor='';
                        $tmpvalor2='';
                        $tmpsalida="";
                        if ($nuevoreg==false and $columnas==1) {  $tmpvalor=$valor ;} 
                        if ($nuevoreg==false and $columnas>=2) {  $tmpvalor=$valor ;  $tmpvalor2=$valor_anterior ;} 
                        if ($nuevoreg==true and $columnas>=2) {  $tmpvalor='' ;  $tmpvalor2=$valor_anterior ;}

                        if (intval($tmpvalor)==0 and $tmpvalor<>'') {$no=' checked="checked"';}
                        if (intval($tmpvalor)==1) {$si=' checked="checked"';}

                        $salida.='<div class="form-check form-check-inline">
                          <input class="form-check-input inspradio" type="radio" name="idet['.$id.']" id="insp'.$id.'no" onchange="insp_validar_det(\'insp'.$id.'no\',\'insp'.$id.'span\')" data-valorg="'.ceroif_nulo($tmpvalor2).'" value="0" '.$no.' '.$required.'>
                          <label class="form-check-label" for="insp'.$id.'no">No</label>
                        </div>';
                        $salida.='<div class="form-check form-check-inline">
                        <input class="form-check-input inspradio" type="radio" name="idet['.$id.']" id="insp'.$id.'si" onchange="insp_validar_det(\'insp'.$id.'si\',\'insp'.$id.'span\')"  data-valorg="'.ceroif_nulo($tmpvalor2).'" value="1" '.$si.' '.$required.'>
                        <label class="form-check-label" for="insp'.$id.'si">Si</label>
                      </div>';
                      } 
                      return $salida;
                  }


                  function  insp_radio_label($id,$valor,$clase=""){
                    $salida='';
                    $tmpvalor=$valor;

                    if (intval($valor)==1){$tmpvalor='Si';} else {$tmpvalor='No';};
                    if ($id==4 or $id==9 or $id==23 or $id==29 ) { $tmpvalor= $valor; }
                    $salida.='<span class="badge badge-secondary '.$clase.'" id="insp'.$id.'span">'.$tmpvalor.'</span> &nbsp;&nbsp;';
          
                      return $salida;
                  }
                    
                
                ?>
            </div>

            <div class="col-md"> 
            <div class="row justify-content-center d-print-none <?php echo $visible_sec3; ?>"> 
                <div class="btn-group btn-group-sm btn-group-toggle mb-2 btntxt2btn" data-toggle="buttons">
                    <label class="btn btn-info btn-sm active pt-2">
                        <i class="fa fa-mouse-pointer "></i>
                        <input type="radio" name="cv_accion" id="cv_mover" value="mover" autocomplete="off" checked><br><span class="btntxt2">Mover</span>           
                    </label>


                   
                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-circle "></i>
                        <input type="radio" name="cv_accion" id="cv_golpe" value="golpe"  autocomplete="off"><br><span class="btntxt2">Abolladura Leve</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-square "></i>
                        <input type="radio" name="cv_accion" id="cv_golpe2" value="golpe2"  autocomplete="off"><br><span class="btntxt2">Abolladura Fuerte</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2 ">
                    <i class="fa fa-slash  "></i>
                        <input type="radio" name="cv_accion" id="cv_rayon2" value="rayon2" autocomplete="off"><br><span class="btntxt2">Rayón Leve</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-wave-square"></i>
                        <input type="radio" name="cv_accion" id="cv_rayon" value="rayon" autocomplete="off"><br><span class="btntxt2">Rayón Fuerte</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-play  fa-rotate-270"></i>
                        <input type="radio" name="cv_accion" id="cv_abolladura" value="abolladura" autocomplete="off"><br><span class="btntxt2">Vidrio Quebrado</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-asterisk  "></i>
                        <input type="radio" name="cv_accion" id="cv_astillado" value="astillado" autocomplete="off"><br><span class="btntxt2">Astillado</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-times  "></i>
                        <input type="radio" name="cv_accion" id="cv_pizca" value="pizca" autocomplete="off"><br><span class="btntxt2">Pizca</span>          
                    </label>

                    <label class="btn btn-info btn-sm pt-2">
                        <i class="fa fa-dot-circle  "></i>
                        <input type="radio" name="cv_accion" id="cv_peladura" value="peladura" autocomplete="off"><br><span class="btntxt2">Peladura</span>          
                    </label>

                    <label class="btn btn-info">
                        
                        <a class="btn btn-info btn-sm" onclick="cv_borrar_objeto(); return false;" id="cv_borrar"><i class="fa fa-trash"></i><br><span class="btntxt2">Borrar</span></a>
                        <!-- <input type="radio" name="cv_accion" id="cv_borrar" value="borrar" class="" autocomplete="off"> Borrar -->
                    </label>

                    <label class="btn btn-info">
                        
                        <a class="btn btn-info btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"  id="cv_borrar_todo"></a>
                        <!-- <input type="radio" name="cv_accion" id="cv_borrar" value="borrar" class="" autocomplete="off"> Borrar -->
                        <div class="dropdown-menu">
                          <a class="dropdown-item" onclick="insp_borrar_prexistencia(); return false;" href="#">Habilitar Borrar Preexistencia</a>
                          <div class="dropdown-divider"></div>                                                
                          <a class="dropdown-item" onclick="insp_modificar_foto(); return false;" href="#">Modificar Foto del Vehiculo</a>       
                        </div>
                      </label>                  

                </div> 
            </div>

            <div class="row text-right  d-print-none <?php echo $visible_sec3; ?>">
                <a href="#" class="btn btn-sm mt-2 mr-4 ml-5" onclick="insp_canvas_zoom('IN',2); return false;" ><i class="fa fa-search-plus"></i>x2 </a>
                <a href="#" class="btn btn-sm mt-2 mr-4" onclick="insp_canvas_zoom('IN',3); return false;" ><i class="fa fa-search-plus"></i>x3 </a>
                <a id="insp_btn_zoomout" href="#" class="btn btn-sm mt-2 mr-4" onclick="insp_canvas_zoom('OUT'); return false;" ><i class="fa fa-search-minus"></i> </a>
            </div>

            <div class="row justify-content-center"> 
                <div class="canvas-responsive">
              <input id="detalles_canvas" name="detalles_canvas"  type="hidden" value="<?php echo $detalles_canvas; ?>" >
                 <canvas id="c" width="450" height="650" style="border:1px solid #ccc"></canvas>
              </div>
               
            </div>
            <div class="row justify-content-center"> 
                 <span class="badge badge-primary"> &nbsp;&nbsp;&nbsp; </span> &nbsp;Preexistente &nbsp;&nbsp; <span class="badge badge-danger"> &nbsp;&nbsp;&nbsp; </span> &nbsp;Nuevo
            </div>
          <hr>
            <div class="row"> 
              <div class="col">
                <?php echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',' rows="5" '.$disable_sec3); ?>
              </div>
            </div>

    
            
            </div>
        </div>

        </div>
      </div>
          
      

      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
            Marca , Numeración y Calibracion de Llantas
        </div>
        <div class="card-body">   

          <div class="row"> 
            <div class="col-md">  
            <u>Delantera Izquierda</u>     
              <p> <?php echo campo("llanta_delantera_izq","Marca",'text',$llanta_delantera_izq,' ',$disable_sec4 .' required');?>  </p>
              <p> <?php echo campo("llanta_delantera_izq_num","Numeración",'text',$llanta_delantera_izq_num,' ',$disable_sec4 .' required');?>  </p>              
              <p> <?php echo campo("llanta_delantera_izq_cali","Calibración",$visible_sec5,$llanta_delantera_izq_cali,' ',$disable_sec4 .' required');?>  </p>
            </div>
            <div class="col-md">  
            <u>Trasera Izquierda</u>     
              <p> <?php echo campo("llanta_trasera_izq","Marca",'text',$llanta_trasera_izq,' ',$disable_sec4 .' required');?>  </p>
              <p> <?php echo campo("llanta_trasera_izq_num","Numeración",'text',$llanta_trasera_izq_num,' ',$disable_sec4 .' required');?>  </p>              
              <p> <?php echo campo("llanta_trasera_izq_cali","Calibración",$visible_sec5,$llanta_trasera_izq_cali,' ',$disable_sec4 .' required');?>  </p>
              <a href="#" class="btn btn-sm <?php echo $visible_sec4; ?>" onclick="insp_copiar_llantas(); return false;" ><i class="fa fa-copy"></i> Copiar Todos</a>
            </div>
            <div class="col-md bg-light">  
            <u>Llanta de Repuesto</u>    
              <p> <?php echo campo("llanta_repuesto","Marca",'text',$llanta_repuesto,' ',$disable_sec4 .' required');?>  </p>
              <p> <?php echo campo("llanta_repuesto_num","Numeración",'text',$llanta_repuesto_num,' ',$disable_sec4 .' required');?>  </p>              
            </div>
            <div class="col-md">  
            <u>Trasera Derecha</u>     
              <p> <?php echo campo("llanta_trasera_der","Marca",'text',$llanta_trasera_der,' ',$disable_sec4 .' required');?>  </p>
              <p> <?php echo campo("llanta_trasera_der_num","Numeración",'text',$llanta_trasera_der_num,' ',$disable_sec4 .' required');?>  </p>              
              <p> <?php echo campo("llanta_trasera_der_cali","Calibración",$visible_sec5,$llanta_trasera_der_cali,' ',$disable_sec4 .' required');?>  </p>
            </div>
            <div class="col-md">  
              <u>Delantera Derecha</u>      
              <p> <?php echo campo("llanta_delantera_der","Marca",'text',$llanta_delantera_der,' ',$disable_sec4 .' required');?>  </p>
              <p> <?php echo campo("llanta_delantera_der_num","Numeración",'text',$llanta_delantera_der_num,' ',$disable_sec4 .' required');?>  </p>
              <p> <?php echo campo("llanta_delantera_der_cali","Calibración",$visible_sec5,$llanta_delantera_der_cali,' ',$disable_sec4 .' required');?>  </p>
              <a href="#" class="btn btn-sm <?php echo $visible_sec4; ?>" onclick="$('#llanta_extra').show(); return false;" ><i class="fa fa-plus"></i> Llantas adicionales</a>
            </div>

          
          </div>
          <div id="llanta_extra" class=" oculto">
          <div  class="row">
            <div class="col-md">  
              <u>Extra 1</u>      
              <p> <?php echo campo("llanta_extra1","Marca",'text',$llanta_extra1,' ',$disable_sec4 .' ');?>  </p>
              <p> <?php echo campo("llanta_extra1_num","Numeración",'text',$llanta_extra1_num,' ',$disable_sec4 .' ');?>  </p>
            </div>

            <div class="col-md">  
              <u>Extra 2</u>      
              <p> <?php echo campo("llanta_extra2","Marca",'text',$llanta_extra2,' ',$disable_sec4 .' ');?>  </p>
              <p> <?php echo campo("llanta_extra2_num","Numeración",'text',$llanta_extra2_num,' ',$disable_sec4 .' ');?>  </p>
            </div>

            <div class="col-md">  
            </div>
            <div class="col-md">  
            </div>
            <div class="col-md">  
            </div>

            </div>
            </div>
            

        </div>
      </div>


      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
            Marca y Numeración de Batería
        </div>
        <div class="card-body">   

          <div class="row"> 
            <div class="col-md-4">  
             <?php echo campo("bateria_marca","Marca",'text',$bateria_marca,' ',$disable_sec5 .' required');?> 
            
            </div>
            <div class="col-md-4">  
             <?php echo campo("bateria_num","Numero",'text',$bateria_num,' ',$disable_sec5 .' required');?>  
            
            </div>
            
            
          </div>

        </div>
      </div>
      
<?php 
$etiqueta_trabajo="Observaciones Adicionales";
$trabajo_realizar_requerido="";
if ($tipo_inspeccion=='2'){ // TALLER 
  $etiqueta_trabajo="Trabajo a Realizar";
  $trabajo_realizar_requerido=' required';
  } // TALLER 

    ?>
      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
          <?php echo $etiqueta_trabajo; ?>
        </div>
        <div class="card-body">   

          <div class="row"> 
            <div class="col-md">     
                <?php 
                echo campo("trabajo_realizar",$etiqueta_trabajo,'textarea',$trabajo_realizar,' ',' rows="5" '.$disable_sec6 .$trabajo_realizar_requerido);?>  
              
            </div>
            
            
          </div>

        </div>
      </div>
  
        

<div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
            Servicio de Grúa 
        </div>
        <div class="card-body">   
          <div class="row"> 
            <div class="col-3">       
                <?php echo campo("grua","Vino en Grúa",'select',valores_combobox_texto(app_combo_si_no,$grua),'',$disable_sec7); ?>  
            </div>
            
            <?php
            // <div class="col-md"> 
            //     <?php echo campo("grua_orden","No. Orden",'text',$grua_orden,' ',$disable_sec7); >
            // </div>
            // <div class="col-md"> 
            //   <?php echo campo("grua_factura","No. Factura",'text',$grua_factura,' ',$disable_sec7); >
            // </div>
 
            ?>
          </div>
          <p class="card-text"></p>

        </div>
      </div>      
   
      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
            Firma
        </div>
        <div class="card-body">   
          
          <div class="row"> 
            <div class="col-md  mb-2"> 
                <h5>Firma Cliente  <a href="#" onclick="insp_limpiar_firma(1); return false;" class="btn btn-sm btn-outline-secondary ml-3 <?php echo $disable_firma; ?>"><i class="fa fa-eraser"></i></a></h5> 
                <input id="firma1_canvas" name="firma1_canvas"  type="hidden"  value="<?php echo $firma1_canvas; ?>" >     
                <canvas id="firma1" width="350" height="150" style="border:1px solid #ccc"></canvas>  
            </div>
            <div class="col-md "> 
                <h5>Firma Inspector  <a href="#" onclick="insp_limpiar_firma(2); return false;" class="btn btn-sm btn-outline-secondary ml-3 <?php echo $disable_firma; ?>"><i class="fa fa-eraser"></i></a></h5>
                <input id="firma2_canvas" name="firma2_canvas"  type="hidden" value="<?php echo $firma2_canvas; ?>" > 
                <canvas id="firma2" width="350" height="150" style="border:1px solid #ccc"></canvas>
            </div>
            
          </div>
          

        </div>
      </div>

        <input type="hidden" name="pdfcod" id="guardar_pdfcod">
        <input type="hidden" name="pdfimg1" id="guardar_pdfimg1">
        <input type="hidden" name="pdflogo" id="guardar_pdflogo">
        <input type="hidden" name="pdffirma1" id="guardar_pdffirma1">
        <input type="hidden" name="pdffirma2" id="guardar_pdffirma2">

        <?php     
         $oculto='hidden';
         $obadpc_requerido="";
         if (tiene_permiso(163) and es_nulo($id_usuario_auditado)) { 
            $oculto='textarea';          
            $obadpc_requerido=' required';
            $disable_sec6=' ';
        }else{ 
            if (tiene_permiso(163) and !es_nulo($id_usuario_auditado)){
               $oculto='textarea';               
               $disable_sec6=' ';
            }    
        }
        ?>        

        <div class="card-body">   
          <div class="row"> 
            <div class="col-md">     
                 <?php echo campo("observaciones_adpc","Observaciones ADPC",$oculto,$observaciones_adpc,' ',' rows="5" '.$disable_sec6 .$obadpc_requerido);?>  
            </div>
          </div>
        </div>     

  </fieldset>
</form> 

      
      <div class="row">
          <div class="col">

          <?php //  ?>
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
            <a href="#" onclick="procesar_inspeccion('inspeccion_mant.php?a=g&gg_est=1','forma','1'); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm <?php echo $visible_guardar; ?>" ><i class="fa fa-check"></i> Guardar Borrador</a>
            <a href="#" onclick="procesar_inspeccion('inspeccion_mant.php?a=g&gg_est=2','forma','2'); return false;" class="btn btn-primary mr-2 mb-2 xfrm <?php echo $visible_guardar; ?>" ><i class="fa fa-check "></i> Guardar Completado</a>
            <?php if (tiene_permiso(163)) { ?>
                 <a href="#" onclick="procesar_inspeccion('inspeccion_mant.php?a=g&gg_est=0','forma','0'); return false;" class="btn btn-primary mr-2 mb-2 xfrm <?php echo $visible_auditar; ?>" ><i class="fa fa-check "></i>Revision ADPC</a>
            <?php } ?>
            <?php if (tiene_permiso(114)) { ?>
                  <a href="#" onclick="borrar_inspeccion(); return false;" class="btn btn-danger mr-2 mb-2 xfrm <?php echo $visible_guardar; ?>" ><i class="fa fa-trash-alt "></i> Borrar</a>
            <?php } ?>
            <div class="float-right">
            <?php if ($nuevoreg==false) {
                if (!es_nulo($id_inspeccion_anterior)) {
                   $insp_anterior_id=$id_inspeccion_anterior;
                   echo ' <a href="#" onclick="insp_abrir(\''.$insp_anterior_id.'\'); return false;" class="btn btn-outline-secondary mr-2 mb-2"><i class="fa fa-link"></i> Inspección Anterior</a>';
               }
            ?>
            <?php if ($id_estado==2 or $id_estado==3) { ?>
                <a href="#" onclick="insp_crear_averia(); return false;" class="btn btn-outline-secondary mr-2 mb-2"><i class="fa fa-plus"></i> Crear Avería</a>
                <?php if ($tipo_inspeccion==2) {?>
                      <a href="#" onclick="insp_crear_servicio(); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-plus"></i> Crear Orden de Servicio</a>
                <?php }
                }
            ?>
           
            <!-- <a href="#" onclick="imprimir(); return false;" class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir</a> -->
              <!-- <a href="#" onclick="get_page('pagina','inspeccion_mant.php?a=v&cid='+$('#id').val(),'Inspeccion',false); return false;" class="btn btn-success mr-2 mb-2 xfrm" ><i class="fa fa-redo-alt"></i> Actualizar</a> -->


            <a href="#" onclick="inspeccion_generar_pdf(); return false;" class="btn btn-secondary mr-2 mb-2"><i class="fa fa-print"></i> Imprimir</a>
            <?php } ?>
            </div>


          </div>
          <form action="inspeccion_pdf.php" method="POST" target="_blank" id="pdfform">
              <input type="hidden" name="pdfcod" id="pdfcod">
              <input type="hidden" name="pdfimg1" id="pdfimg1">
              <input type="hidden" name="pdflogo" id="pdflogo">
              <input type="hidden" name="pdffirma1" id="pdffirma1">
              <input type="hidden" name="pdffirma2" id="pdffirma2">
          </form>
         
          </div>
        </div>           
              
        <div class="row"><div class="col mt-5 px-3 py-2">          
             <a href="#" onclick="get_page_regresar('pagina','inspeccion_ver.php','Ver Inspecciones') ;  return false;" class="btn btn-outline-secondary mr-2 mb-2">Regresar</a> 
        </div></div>
  
  </div>

  <!-- FOTOS  -->
  <div class="tab-pane fade " id="nav_fotos" role="tabpanel" ></div>

  <!-- ADJUNTOS -->
  <div class="tab-pane fade " id="nav_doctos" role="tabpanel" ></div>

  <!-- HISTORIAL -->
  <div class="tab-pane fade " id="nav_historial" role="tabpanel" ></div>

  <!-- errores -->
  <div class="tab-pane fade mt-5 mb-5" id="nav_deshabilitado" role="tabpanel" ><div class="alert alert-warning" role="alert">Debe Guardar el documento para poder continuar con esta sección</div></div>
</div>
 






</div><!--  card-body -->


<script>




var canvas = new fabric.Canvas('c');
var cvzoom =1;

<?php if ($disable_firma==''){  ?>
  var canvas_firma1 = new fabric.Canvas('firma1');
  var canvas_firma2 = new fabric.Canvas('firma2');
  canvas_firma1.isDrawingMode=true;
  canvas_firma1.freeDrawingBrush.width=2;
  canvas_firma2.isDrawingMode=true;
  canvas_firma2.freeDrawingBrush.width=2;

<?php  } else {?>
  // solo lectura
  var canvas_firma1 = new fabric.StaticCanvas('firma1');
  var canvas_firma2 = new fabric.StaticCanvas('firma2');
<?php  } ?>


<?php 
if ($detalles_canvas<>"" and $detalles_canvas<>"[object Object]") {
 echo "cv_cargar(canvas,'$detalles_canvas');";
 if (strpos($detalles_canvas,'.jpg')===false) {
    echo "canvas.setBackgroundImage('img/hoja_inspeccion/$plantilla_vehiculo.jpg', canvas.renderAll.bind(canvas), {  originX: 'left',  originY: 'top'  });";
 }

} else {
  echo "canvas.setBackgroundImage('img/hoja_inspeccion/$plantilla_vehiculo.jpg', canvas.renderAll.bind(canvas), {  originX: 'left',  originY: 'top'  });";
}
if ($firma1_canvas<>"") {
  echo "cv_cargar(canvas_firma1,'$firma1_canvas');"; 
} 


if ($firma2_canvas<>"") {
  echo "cv_cargar(canvas_firma2,'$firma2_canvas');"; 
} 

?>


function inspeccion_generar_pdf(){

  var pdfcanvasimg = document.getElementById("c");
  var pdfimg1    = pdfcanvasimg.toDataURL("image/png");

  var pdfcanvasfirma1 = document.getElementById("firma1");
  var pdffirma1    = pdfcanvasfirma1.toDataURL("image/png");

  var pdfcanvasfirma2 = document.getElementById("firma2");
  var pdffirma2    = pdfcanvasfirma2.toDataURL("image/png");


$('#pdfcod').val($('#id').val());
$('#pdfimg1').val(pdfimg1);
$('#pdffirma1').val(pdffirma1);
$('#pdffirma2').val(pdffirma2);

$('#pdfform').submit();
  // if (fabric.Canvas.supports('toDataURL')) 
  //   window.open(canvas.toDataURL('png'));
  //opupwindow('inspeccion_pdf.php?cid='+$('#id').val(), 'Hoja de Inspeccion', screen.width, screen.height);
}

//****

canvas.on('mouse:down', function(options) {

 var accion=$("input[name='cv_accion']:checked").val();
 var posx=options.pointer.x ;
 var posy=options.pointer.y ;

 var color='red';
 

if (cvzoom>1) {
  posx=posx/cvzoom;
  posy=posy/cvzoom;
}
//console.log('x: '+posx+'  y: '+posy+'  Zoom: '+cvzoom);

 if (accion=='golpe') {
    var cvobj = new fabric.Circle({
        left: posx -10,
        top: posy -10,
        fill: color,
        radius: 10,
        opacity: 0.8
        });
    canvas.add(cvobj);
    $('#cv_mover').click(); 

 }
 if (accion=='rayon') {
    var cvobj = new fabric.Path('M 0 0 L 5 0 L 5 -6 L 10 -6 L 10 4 L 15 4 L 15 -3 L 20 -3  ',  {
        left: posx - 10,
        top: posy -7,
        strokeWidth:3,
        stroke: color,
        fill: false,
         opacity: 0.8
        });
    canvas.add(cvobj);
    $('#cv_mover').click();    
    }

 if (accion=='abolladura') {
    var cvobj = new fabric.Triangle({
        left: posx - 8,
        top: posy -12,
        fill: color,
        width: 18,
        height: 18,
        opacity: 0.8
        });
    canvas.add(cvobj);
    $('#cv_mover').click();
     
 }


 if (accion=='golpe2') {
    var cvobj = new fabric.Rect({
        left: posx - 8,
        top: posy -12,
        fill: color,
        width: 16,
        height: 16,
        opacity: 0.8
        });
    canvas.add(cvobj);
    $('#cv_mover').click();
     
 }


 if (accion=='rayon2') {

    canvas.add(new fabric.Line([ 50, 65, 70, 65], {
      left:posx - 8,
      top: posy -0,
      stroke: color,
      opacity: 0.8,
      strokeWidth: 2
    }));

    $('#cv_mover').click();
     
 }


 if (accion=='astillado') {

    var cvobj = new fabric.Text('*', {
      left: posx - 8,
      top: posy -12,
      fill: color,
      fontFamily: 'helvetica'
    });

    canvas.add(cvobj);
    $('#cv_mover').click();
     
 }


 if (accion=='pizca') {

    var cvobj = new fabric.Text('x', {
      left: posx - 8,
      top: posy -12,
      fill: color,
      fontFamily: 'helvetica',
      scaleX: 0.5,
      scaleY: 0.5,
    });

    canvas.add(cvobj);
    $('#cv_mover').click();
   
}


if (accion=='peladura') {

    var cvobj = new fabric.Text('O', {
      left: posx - 8,
      top: posy -12,
      fill: color,
      fontFamily: 'helvetica',
      scaleX: 0.5,
      scaleY: 0.5,
    });

    canvas.add(cvobj);
    $('#cv_mover').click();
   
}


});

// canvas.on(
//   'selection:created', function(options) {

//    // console.log(options);
// });

// $('#cv_borrar').prop("disabled", true);
 
//*****

function cv_borrar_objeto(){
    var activeObjects = canvas.getActiveObjects();
    canvas.discardActiveObject()
    if (activeObjects.length) {
      canvas.remove.apply(canvas, activeObjects);
    }
}

function cv_agregar_objeto(x,y) {

var rect = new fabric.Rect({
  left: x,
  top: y,
  fill: 'red',
  width: 20,
  height: 20,
  opacity: 0.8
});

// ,
//   selectable: false,
//   evented: false,
//   hoverCursor: 'default'

// lockMovementX: true,
// lockMovementY: true,
// lockScalingX: true,
// lockScalingY: true,
// lockRotation: true

canvas.add(rect);

 
}

function cv_cargar(thecanvas,json) {
  thecanvas.clear();

  thecanvas.loadFromJSON(json, function() {

// and checking if object's "name" is preserved
// console.log(canvas.item(0).name);
//canvas.setActiveObject(canvas.item(0));
//  canvas.getActiveObject().id = your id value;
//  Myid = canvas.getActiveObject().get('id');

thecanvas.selection = false;

thecanvas.forEachObject(function(o) {
  o.selectable = false;
  o.evented= false;
 
  // o.fill='blue';
 o.hoverCursor= 'default';
  // console.log(o);
 
  });


  thecanvas.renderAll(); // making sure to render canvas at the end


});

}

function cv_guardar() {
 /// console.log( JSON.stringify(canvas.toJSON()) );

 // save json
 canvas.includeDefaultValues = false;
 var json = JSON.stringify(canvas.toJSON());
//console.log( json);


}

function insp_limpiar_firma(objeto){
  if (objeto==1) {
     canvas_firma1.clear();
  }
  if (objeto==2) {
     canvas_firma2.clear();
  }
 
}



function cv_lock() {

var activeObject = canvas.getActiveObject();

if(activeObject.type === 'activeSelection'){
  
  activeObject._objects.forEach(function(item) {
      item.selectable = false; 
      item.evented = false
      item.hoverCursor= 'pointer';
  });
  
}else{
  
    activeObject.selectable =  false;
    activeObject.evented = false
    activeObject.hoverCursor= 'default';
  
}

canvas.discardActiveObject().renderAll();

}

function cv_unlockAll() {

var items = canvas.getObjects(); 

if(!items){
  return;
}

items.forEach(function(item) {
  
      if(item.selectable == false){
          item.selectable = true; 
          item.hoverCursor= 'move';
          item.evented = true;
      }   
       
});
  
canvas.discardActiveObject().renderAll();

}

function cv_selectAll(){

var selection = new fabric.ActiveSelection(canvas.getObjects(), {
canvas: canvas
});
canvas.setActiveObject(selection).renderAll();  
}

function cv_selectionImages(){
     canvas.selection=true;     
  }
  

function cv_deselectAll(){

canvas.discardActiveObject().renderAll();

}



function insp_modificar_foto() {
 
 <?php if (!tiene_permiso(29)) {?>
   mymodal('error','Modificar Foto del Vehiculo','No tiene permisos suficientes para efectuar esta acción');
 <?php } else {?>

  Swal.fire({
  title: 'Modificar Foto del Vehiculo',
  input: 'select',
  inputOptions: {
    'turismo': 'turismo',
    'camioneta': 'camioneta',
    'pickup': 'pickup',
    'microbus': 'microbus',
    'camion': 'camion',
    'cuatrimoto': 'cuatrimoto'
  },
  inputPlaceholder: 'required',
  showCancelButton: true,
  inputValidator: function (value) {
    return new Promise(function (resolve, reject) {
      if (value !== '') {
        resolve();
      } else {
        resolve('Debe seleccionar un Vehiculo');
      }
    });
  }
}).then(function (result) {
  if (result.value!='') {
    $('#plantilla_vehiculo').val(result.value);
    canvas.setBackgroundImage('img/hoja_inspeccion/'+result.value+'.jpg', canvas.renderAll.bind(canvas), {  originX: 'left',  originY: 'top'  });

  }
  
  
});
    
    
 
 <?php } ?>
}

function insp_borrar_prexistencia() {
 
  <?php if (!tiene_permiso(29)) {?>
    mymodal('error','Borrar preexistencias','No tiene permisos suficientes para efectuar esta acción');
  <?php } else {?>
  Swal.fire({
	  title: 'Borrar',
	  text:  'Desea habilitar el borrado de preexistencias?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Habilitar Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
     // canvas.clear();
      <?php 
     //echo "canvas.setBackgroundImage('img/hoja_inspeccion/$plantilla_vehiculo.jpg', canvas.renderAll.bind(canvas), {  originX: 'left',  originY: 'top'  });"; 
     ?>
     
     cv_unlockAll();
     cv_selectionImages();
	  }
	});
  <?php } ?>
}


function insp_cambiartab(eltab) {
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
    procesar_inspeccion_foto('nav_fotos');
  }

  if (eltab=='nav_historial') {
    procesar_inspeccion_historial('nav_historial');
  }
  
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

//   nav_detalle
// nav_fotos
// nav_doctos


   

}



function procesar_inspeccion_historial(campo){

var cid=$("#id").val();
var pid=$('#id_producto').val();
var url='inspeccion_historial.php?cid='+cid+'&pid='+pid ;

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



function procesar_inspeccion_foto(campo){

  var cid=$("#id").val();
  var pid=$('#id_producto').val();
  var insp=<?php echo intval($id_inspeccion_anterior); ?>;
  var url='inspeccion_fotos.php?cid='+cid+'&pid='+pid+'&insp='+insp ;
 
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


function insp_copiar_llantas(){
    var nombre=$("#llanta_delantera_izq").val(); 
    var numero=$("#llanta_delantera_izq_num").val();
  	$("#llanta_delantera_der").val(nombre);
    $("#llanta_delantera_der_num").val(numero);
    $("#llanta_trasera_izq").val(nombre);
    $("#llanta_trasera_izq_num").val(numero);
    $("#llanta_trasera_der").val(nombre);
    $("#llanta_trasera_der_num").val(numero);
    $("#llanta_repuesto").val(nombre);
    $("#llanta_repuesto_num").val(numero);
}

function insp_crear_servicio(){
//   if (isset($_REQUEST['ins'])) { $id_inspeccion = intval($_REQUEST['ins']); }
// if (isset($_REQUEST[''])) { $id_producto = intval($_REQUEST['pid']); }
// if (isset($_REQUEST[''])) { $cliente_id = intval($_REQUEST['cll']); }
// if (isset($_REQUEST[''])) { $cliente_nombre = ($_REQUEST['cnb']); }

var datacli = $('#cliente_id').select2('data')
var inspid=$('#id').val();
var inspnum=$('#numero_lb').val();
var pid=$('#id_producto').val();
var ccl=$('#cliente_id').val();
var km=$('#kilometraje_entrada').val();
var hi='S';
if (ccl!=null) {
  var cnb=datacli[0].text;
} else {
  var cnb="";
}

  var addicionales='&ob='+encodeURI($('#trabajo_realizar').val());
  get_page('pagina','servicio_mant_nuevo.php?ins='+inspid+'&pid='+pid+'&ccl='+ccl+'&km='+km+'&cnb='+encodeURI(cnb)+addicionales+'&hi='+hi,'Nueva Orden de Servicio - Inspeccion #'+inspnum) ; 
			
}




function insp_crear_averia(){

var datacli = $('#cliente_id').select2('data')
var inspid=$('#id').val();
var inspnum=$('#numero_lb').val();
var pid=$('#id_producto').val();
var ccl=$('#cliente_id').val();
var km=$('#kilometraje_entrada').val();
var addicionales='&cc='+encodeURI($('#cliente_contacto').val());

if (ccl!=null) {
  var cnb=datacli[0].text;
} else {
  var cnb="";
}
  
  get_page('pagina','averia_mant_nuevo.php?ins='+inspid+'&num='+inspnum+'&pid='+pid+'&ccl='+ccl+'&km='+km+'&cnb='+encodeURI(cnb)+addicionales,'Nueva Orden de Avería - Inspeccion #'+inspnum) ; 
			
}



function insp_actualizar_email_cliente(){

  var emailcliente=$('#cliente_id').select2('data')[0];

  $('#cliente_email').val(emailcliente.email);
}


function insp_validar_det(objeto,objeto2){
  if ($('#'+objeto).val()!=$('#'+objeto).data('valorg')) {
    $('#'+objeto2).addClass('enrojo');
  } else {
    $('#'+objeto2).removeClass('enrojo');
  }
}

function insp_actualizar_veh() {
  var datos=$('#id_producto').select2('data')[0];

  $('#placa').val(datos.placa);
  $('#chasis').val(datos.chasis);
}


function insp_cambiar_logo() {
  var valempresa=parseInt($('#empresa_sel').val());
  var empresa_logo ="nd";
  if (valempresa==1) {empresa_logo ="hertz";}
  if (valempresa==2) {empresa_logo ="dollar";}
  if (valempresa==3) {empresa_logo ="thrifty";}
  $('#id_empresa').val(valempresa);
  $('#ins_logo_empresa').attr("src", "img/"+empresa_logo+".jpg"); 

 
}

function procesar_inspeccion(url,forma,adicional){

var validado=false;
var forms = document.getElementsByClassName('needs-validation');
var validation = Array.prototype.filter.call(forms, function(form) {
 
    if (form.checkValidity() === false) {
        mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
      } else {validado=true;}
      form.classList.add('was-validated');
      
    });

    if ($("#kilometraje_entrada").val()=='') {
        mytoast('warning','Debe ingresar el Kilometraje',3000) ;
        validado=false;
    }else {
      var km=parseInt($("#kilometraje_entrada").val());
      var km_min=parseInt($("#kilometraje_minimo").val());            
      //Valida el maximo permitido de km
      var km_maximo=<?php echo intval($valida_km); ?>;     
      /*var km_permitido=</*?php echo intval($km_permitido); ?>;                  */
      if (km_maximo>0){
         if (km>km_maximo){
            mytoast('warning','El Kilometraje es incorrecto ' ,3000) ;
            validado=false;
         }                        
         if  ( km>0 && km_min>0){
             if  ( km < km_min){                
                  mytoast('warning','El Kilometraje no puede ser menor al kilometraje anterior ' ,3000) ;
                  validado=false;
             }   
            //  if  (km>(km_min+km_permitido)){                
            //       mytoast('warning','El Kilometraje es incorrecto ' ,3000) ;
            //       validado=false;
            //  }                
         }  
         
      }
    }            
    
    if (validado==true) {
        var cliente=<?php echo $cliente; ?>;  
        var clienteid=$("#cliente_id").val();                              
        var nombre=$("#nombre_cliente").val();
        if (adicional!='0'){
            if (cliente>0){
              if(cliente!=clienteid){                     
                  mytoast('warning','Vehiculo seleccionado pertence al cliente '+nombre,3000) ;
                  validado=false;    
              }
            }
        }
    }  
    
    if (validado==true) {
      if ($("#id_producto").val()=='' || $("#id_producto").val()=='0'  || $("#id_producto").val()==null) {
          mytoast('warning','Debe seleccionar el producto',3000) ;
          validado=false;
      }           
    }

   if (validado==true) {
      var combus=$("input[name='combustible_entrada']:checked").val();
      if (combus=='' || combus === undefined) {
         mytoast('warning','Debe ingresar el Combustible',3000) ;
         validado=false;  
      }
    }
    if (validado==true) {    
        var cali_valores = [$("#llanta_delantera_izq_cali").val(),$("#llanta_delantera_der_cali").val(),$("#llanta_trasera_der_cali").val(),$("#llanta_trasera_izq_cali").val()];
        var valor = 0;
        for (var valores of cali_valores){
            if (parseInt(valores)<30 || parseInt(valores)>40) {
              valor = valor + 1;
            }
        }
        if (valor>0) {
            mytoast('warning','Los valores de calibracion de las llantas son incorrectos ',3000) ;
            validado=false;  
        } 
    }    

    if (adicional=='1'){ //borrador

    }
    if (adicional=='0'){

    }
    if (adicional=='2'){ //Completado

      // if (validado==true) {
      //   if ($("#cliente_id").val()=='' || $("#cliente_id").val()=='0' || $("#cliente_id").val()==null) {
      //   mytoast('warning','Debe seleccionar el cliente',3000) ;
      //   validado=false;
      //   }           
      // }
                

   
       if (validado==true) {
          if ($("#cliente_email").val()=='') {
          mytoast('warning','Debe ingresar el Correo del Cliente',3000) ;
          validado=false;
          }    
        }   
        
        if (validado==true) {
          if ($("#cliente_contacto").val()=='') {
          mytoast('warning','Debe ingresar el Nombre del contacto',3000) ;
          validado=false;
          }    
        }

       <?php if ($tipo_inspeccion=='1'){ // RENTA 
       ?>
       if (validado==true) {
          if ($("#renta_contrato").val()=='') {
          mytoast('warning','Debe ingresar el numero del contrato',3000) ;
          validado=false;
          }  
        }

        if (validado==true) {
          if ($("#id_empresa").val()=='0') {
          mytoast('warning','Debe seleccionar la empresa de renta',3000) ;
          validado=false;
          }  
        }

       
       <?php }     
       ?>

        if (validado==true) {
          // imagenes para pdf          
            var pdfcanvasimg = document.getElementById("c");            
            var pdfimg1    = pdfcanvasimg.toDataURL("image/png");

            var pdfcanvasfirma1 = document.getElementById("firma1");
            var pdffirma1    = pdfcanvasfirma1.toDataURL("image/png");

            var pdfcanvasfirma2 = document.getElementById("firma2");
            var pdffirma2    = pdfcanvasfirma2.toDataURL("image/png");           
       
            
            $('#guardar_pdfcod').val($('#id').val());
            $('#guardar_pdfimg1').val(pdfimg1);
            $('#guardar_pdffirma1').val(pdffirma1);
            $('#guardar_pdffirma2').val(pdffirma2);
          
        }

    }

 
 if(validado==true) {
    $("#"+forma+" .xfrm").addClass("disabled");		  
    cargando(true); 
  
    canvas.includeDefaultValues = false;
    var canvasjson = JSON.stringify(canvas.toJSON());   
   
    $("#detalles_canvas").val(canvasjson);            
    $("#firma1_canvas").val(JSON.stringify(canvas_firma1.toJSON()));
    $("#firma2_canvas").val(JSON.stringify(canvas_firma2.toJSON()));

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
        var alertar_taller='';
        <?php 
        if ($nuevoreg==true and $tipo_insp==2 and $tipo_doc==1) {
          echo "alertar_taller='&alertar=1';";
        }
        ?>
        get_page('pagina','inspeccion_mant.php?a=v&cid='+json[0].pcid + alertar_taller,'Hoja de Inspección',false) ; 
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


function borrar_inspeccion(){
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
 
          var forma='forma';
          var url='inspeccion_mant.php?a=del';

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

                  get_page('pagina','inspeccion_ver.php','Ver Inspecciones') ; 
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
	})

}

function inspeccion_modificar(valor){

  if (valor==true) {
    $("#btn_modificar_insp").hide();
    $("#btn_modificar_insp_guardar").show();
    // $(window).scrollTop(0);
  } else {
    procesar_inspeccion('inspeccion_mant.php?a=g&gg_est=1&modenc=1','forma','1');
    $("#btn_modificar_insp").show();
    $("#btn_modificar_insp_guardar").hide();    
  }

  $("#insp_encb1 input[type=text]").prop("disabled", !valor);
  $("#cliente_id").prop("disabled", !valor);
  $("#id_producto").prop("disabled", !valor);

  $("#kilometraje_entrada").prop("disabled", !valor);
  $("#kilometraje_minimo").prop("disabled", !valor);
  $("#combustible_tipo").prop("disabled", !valor);
  $('input[name=combustible_entrada]').prop("disabled", !valor);  
}

function insp_canvas_zoom(inout='IN',valor=2){
  if (inout=='IN') {
      cvzoom=valor;
      $('#insp_panel_izq_detalle').hide();
      $('#c').width(650); 
      $('#c').height(650 * valor);     
      canvas.setZoom(valor);
      canvas.setWidth(650 * valor);
      canvas.setHeight(650 * valor);
       $('#insp_btn_zoomout').addClass('btn-danger');
  } else {
      cvzoom=1;
      canvas.setZoom(1);
      $('#c').width(450);
      $('#c').height(650);
      canvas.setWidth(450);
      canvas.setHeight(650 );
      $('#insp_panel_izq_detalle').show();
      $('#insp_btn_zoomout').removeClass('btn-danger');
  }
}


</script>