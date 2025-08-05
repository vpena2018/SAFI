<?php

require_once ('include/framework.php');

$hi='';
$id_inspeccion='';
$id_producto='';
$cliente_id='';
$cliente_nombre='';
$km='';
$placa='';
$chasis='';

if (isset($_REQUEST['ins'])) { $id_inspeccion = intval($_REQUEST['ins']); }
if (isset($_REQUEST['pid'])) { $id_producto = intval($_REQUEST['pid']); }
if (isset($_REQUEST['ccl'])) { $cliente_id = intval($_REQUEST['ccl']); }
if (isset($_REQUEST['cnb'])) { $cliente_nombre = ($_REQUEST['cnb']); }
if (isset($_REQUEST['km'])) { $km = ($_REQUEST['km']); }
if (isset($_REQUEST['ob'])) { $observaciones_asignar = ($_REQUEST['ob']); } else   {$observaciones_asignar ="";}
if (isset($_REQUEST['hi'])) { $hi = ($_REQUEST['hi']); } else {$hi='N';}

$motorista='';
if ($hi=="S"){
  /* pagina_permiso(24);   */
  $motorista=get_dato_sql("usuario","COUNT(*)"," WHERE grupo_id=3 AND id=".$_SESSION["usuario_id"]);        
  if ($motorista>0){
     pagina_permiso(159);   
  }else{
     pagina_permiso(24);
  }
}

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="";}
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
$vehiculo=0;
$kilometraje=0;
$vehiculoCarwash=0;
$vehiculoGenerico=0;
$ordenesborrador=0;     
$ParoPorRepuesto=0; 
  if (isset($_REQUEST['id_producto'])) { $vehiculo = intval($_REQUEST['id_producto']); }
  if (isset($_REQUEST['kilometraje'])) { $kilometraje = intval($_REQUEST['kilometraje']); }
  if (!es_nulo($vehiculo) ){                  
      $vehiculoCarwash=get_dato_sql("producto","COUNT(*)"," WHERE left(codigo_alterno,5)='EA-CW' and id=".$vehiculo);        
      $vehiculoGenerico=get_dato_sql("producto","COUNT(*)"," WHERE left(codigo_alterno,7)='EA-0000' and id=".$vehiculo);        
      if (($vehiculoCarwash+$vehiculoGenerico)>0){
         $verror="";  
      }else {    
        $ParoPorRepuesto=get_dato_sql("servicio","COUNT(*)"," WHERE id_estado=7 AND (estado_paro_por_repuesto='I' or estado_paro_por_repuesto=null)  AND id_producto=".$vehiculo);                 
        $ordenesborrador=get_dato_sql("servicio","COUNT(*)"," WHERE id_estado not in (20,22,7) AND id_producto=".$vehiculo);        
        if (!es_nulo($ordenesborrador) or !es_nulo($ParoPorRepuesto)) {
          $verror.="Vehiculo tiene ".$ordenesborrador." Orden de Servicio pendiente de completar";  
      }  
      /*
      if ($verror==""){  
          $CodigoAlterno=0;
          $CodigoAlterno=get_dato_sql("producto","COUNT(*)"," WHERE left(codigo_alterno,7)='EA-0000' and id=".$vehiculo);
          if ($CodigoAlterno=0){
              $valida_km=0;
              $valida_km=get_dato_sql("configuracion","maximo_kilometraje"," WHERE id=1");   
              if ($kilometraje>$valida_km){
                  $verror.="El Kilometraje es incorrecto";
              }  
          }
      } 
      */
    }    
  }
  if ($verror=="") {
    
      //Campos
      $sqlcampos="";
      $sqlcampos_detalle="";
      if (isset($_REQUEST["id_tipo_mant"])) { $sqlcampos.= "  id_tipo_mant =".GetSQLValue($_REQUEST["id_tipo_mant"],"int"); } 
      if (isset($_REQUEST["id_inspeccion"])) { $sqlcampos.= " , id_inspeccion =".GetSQLValue($_REQUEST["id_inspeccion"],"int"); } 
      if (isset($_REQUEST["id_tipo_revision"])) { $sqlcampos.= " , id_tipo_revision =".GetSQLValue($_REQUEST["id_tipo_revision"],"int"); } 
      if (isset($_REQUEST["tipo_servicio"])) { $sqlcampos.= " , tipo_servicio =".GetSQLValue($_REQUEST["tipo_servicio"],"int"); } 
     
      if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
    //   if (isset($_REQUEST["id_tecnico1"])) { $sqlcampos.= " , id_tecnico1 =".GetSQLValue($_REQUEST["id_tecnico1"],"int"); } 
    //   if (isset($_REQUEST["id_tecnico2"])) { $sqlcampos.= " , id_tecnico2 =".GetSQLValue($_REQUEST["id_tecnico2"],"int"); } 
    //   if (isset($_REQUEST["id_tecnico3"])) { $sqlcampos.= " , id_tecnico3 =".GetSQLValue($_REQUEST["id_tecnico3"],"int"); } 
      if (isset($_REQUEST["fecha_hora_ingreso"])) { $sqlcampos.= " , fecha_hora_ingreso =".GetSQLValue($_REQUEST["fecha_hora_ingreso"],"datetime"); } 
      if (isset($_REQUEST["fecha_hora_promesa"])) { $sqlcampos.= " , fecha_hora_promesa =".GetSQLValue($_REQUEST["fecha_hora_promesa"],"datetime"); } 
      if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
      if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
      if (isset($_REQUEST["placa"])) { $sqlcampos.= " , placa =".GetSQLValue($_REQUEST["placa"],"text"); } 
      if (isset($_REQUEST["chasis"])) { $sqlcampos.= " , chasis =".GetSQLValue($_REQUEST["chasis"],"text"); } 
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
      if (isset($_REQUEST["id_taller"])) { $sqlcampos.= " , id_taller =".GetSQLValue($_REQUEST["id_taller"],"int"); }

        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('servicio',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into servicio set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
   

      if ($result!=false){

        // actualizar kilometraje
        $sqlk5="";
        $tipokm=intval($_REQUEST["id_tipo_revision"]);
        if ($tipokm==1) {$sqlk5=", k5=$cid ";}
        if ($tipokm==2) {$sqlk5=", k10=$cid ";}
        if ($tipokm==3) {$sqlk5=", k20=$cid ";}
        if ($tipokm==4) {$sqlk5=", k40=$cid ";}
        if ($tipokm==5) {$sqlk5=", k100=$cid ";}
        sql_update("UPDATE producto SET km=".GetSQLValue($_REQUEST["kilometraje"],"int")." $sqlk5 WHERE id=".GetSQLValue($_REQUEST["id_producto"],"int"));
        
         //  historial
         sql_insert("INSERT INTO servicio_historial_estado (id_servicio,  id_usuario,  nombre, fecha, observaciones)
         VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Nueva Orden', NOW(), '')");
 
         //******** API Rentworks *******/      
         require_once ('include/rentworks_api.php');
         $rw_salida=rw_crear_orden(1,$cid,"");
      
         //enviar correo
         require_once ('correo_servicio_nuevo.php');
	        
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

//validar
$valerror="";
$ordenesborrador=0;
if (!es_nulo($id_inspeccion) ){
  $ordenesborrador=get_dato_sql("servicio","COUNT(*)"," WHERE id_inspeccion=".$id_inspeccion); //id_estado=1 AND
  if ($ordenesborrador>0) {
    $valerror=mensaje("No puede crear una nueva orden porque actualmente se encontró $ordenesborrador  orden desde esta misma hoja de inspección",'warning');
    $valerror.='<br><br> <a id="btn-filtro" href="#" onclick="get_page(\'pagina\',\'servicio_ver.php\',\'Ver Ordenes de Servicio\') ; return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Buscar Ordenes</a>';
  }
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

?>
 <div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="serv_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      
    </ul>   
 </div>

 
<div class="card-body">

<div class="row mb-2"> 
            
            <div class="col-md-3">       
                <?php echo campo("numero","Numero",'label','',' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                <?php echo campo("fecha","Fecha",'label',formato_fecha_de_mysql($now_fecha),' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                <?php echo campo("id_tienda","Tienda",'label',get_dato_sql('tienda','nombre',' where id='.$_SESSION['tienda_id']),' ',' ');  ?>              
            </div>

            <div class="col-md-3">       
                 <?php echo campo("estado","Estado",'label','Nueva','',' ','');   ?>  
            </div>
</div>

<div class="tab-content" id="nav-tabContent">
 
<!-- DETALLE  -->
  <div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >
    
      <form id="forma" name="forma" class="needs-validation" novalidate>
      <fieldset id="fs_forma">

        
      <input id="id_inspeccion" name="id_inspeccion" type="hidden" value="<?php echo $id_inspeccion; ?>" >
   
    
      <input id="id" name="id"  type="hidden" value="" >


      <div class="row"> 
            <div class="col-md-6">       
                <?php echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,' ',' required ','get.php?a=2&t=1',$cliente_nombre);   ?>    
            </div>

            <div class="col-md-6">       
                <?php 
                $producto_etiqueta="";
                if (!es_nulo($id_producto)) {
                  
                  $result = sql_select("SELECT concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,'')) AS producto_etiqueta
                                      ,placa,chasis   
                                      FROM producto 
                                      WHERE id=$id_producto limit 1");

                  if ($result!=false){
                    if ($result -> num_rows > 0) { 
                      $row = $result -> fetch_assoc(); 
                      $producto_etiqueta=$row['producto_etiqueta'];
                      $placa=$row['placa'];
                      $chasis=$row['chasis'];

                    }
                  }
                }  
                echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ',' onchange="serv_nuevo_actualizar_veh();" required ','get.php?a=3&t=1',$producto_etiqueta);
                ?>                       
            </div>
      </div>

      <div class="row"> 
      <div class="col-md-4">       
              <?php echo campo("placa","Placa",'text',$placa,' ',' required ','','');   ?>      
                  </div>

            <div class="col-md-4">       
                <?php echo campo("chasis","Chasis",'text',$chasis,' ',' required ','','');   ?>    
            </div>

            <div class="col-md-4">       
                <?php echo campo("kilometraje","Kilometraje",'number',$km,' ',' required ','','');   ?>    
            </div>
    
      </div>


      <div class="row"> 
        <div class="col-md-6">       
                   <?php echo campo("id_taller","Taller",'select2ajax','',' ',' required ','get.php?a=4&t=1',''); ?>     
        </div>                  
      </div>

      
      <div class="row"> 
   
            <div class="col-md-6">       
                <?php 
                echo campo("id_tipo_mant","Tipo",'select2',valores_combobox_db('servicio_tipo_mant','','nombre','','','...'),' ',' required ','');
                     ?>          
            </div>

            <div class="col-md-6">       
                  <?php 
                echo campo("id_tipo_revision","Tipo Revisión",'select2',valores_combobox_db('servicio_tipo_revision','','nombre','','','...'),' ',' required ','');
                     ?> 
             </div>
      </div>





      <div class="row"> 
    <div class="col-md-4"> 
        <?php echo campo("fecha_hora_ingreso","Fecha/Hora Ingreso",'datetime-local',$now_fechahoraT,' ',' required '); ?>
    </div>

  

    <div class="col-md-4"> 
        <?php echo campo("fecha_hora_promesa","Fecha/Hora Promesa",'datetime-local','',' ',' required '); ?>
    </div>

    
    </div>



              

      <div class="card mb-3">
        <div class="card-header  bg-secondary text-white ">
        Observaciones
        </div>
        <div class="card-body">   

          <div class="row"> 
            <div class="col-md">     
                <?php echo campo("observaciones","Observaciones",'textarea',$observaciones_asignar,' ',' rows="5" '.'');?>  
              
            </div>
            
            
          </div>

        </div>
      </div>
  
       
     
      <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <a href="#" onclick="procesar_servicio_nuevo('servicio_mant_nuevo.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
           

          </div>
          </div>
        </div>

      
        </fieldset>
        </form> 
  

  </div>



</div>
 






</div><!--  card-body -->


<script>


function procesar_servicio_nuevo(url,forma,adicional){

  var validado=false;
  var forms = document.getElementsByClassName('needs-validation');
  var validation = Array.prototype.filter.call(forms, function(form) {
						       
								 
        if (form.checkValidity() === false) {
                mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
            } else {validado=true;}
            form.classList.add('was-validated');
            
        });

        if (validado==true) {
            if ($("#id_tipo_mant").val()=='' || $("#id_tipo_mant").val()=='0') {
            mytoast('warning','Debe seleccionar el tipo',3000) ;
            validado=false;
            }           
        }

        if (validado==true) {
            if ($("#id_tipo_revision").val()=='' || $("#id_tipo_revision").val()=='0') {
            mytoast('warning','Debe seleccionar el tipo de Revision',3000) ;
            validado=false;
            }           
        }
       

          

if(validado==true)
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
				
	
				get_page('pagina','servicio_mant.php?a=v&cid='+json[0].pcid,'Orden de Servicio') ; 
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


function serv_nuevo_actualizar_veh() {
  var datos=$('#id_producto').select2('data')[0];

  $('#placa').val(datos.placa);
  $('#chasis').val(datos.chasis);
}




</script>