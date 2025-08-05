<?php

require_once ('include/framework.php');
pagina_permiso(35);


$id_inspeccion='';
$id_producto='';
$cliente_id='';
$cliente_nombre='';
$km='';
$num='';
$contacto='';

if (isset($_REQUEST['ins'])) { $id_inspeccion = intval($_REQUEST['ins']); }
if (isset($_REQUEST['pid'])) { $id_producto = intval($_REQUEST['pid']); }
if (isset($_REQUEST['ccl'])) { $cliente_id = intval($_REQUEST['ccl']); }
if (isset($_REQUEST['cnb'])) { $cliente_nombre = ($_REQUEST['cnb']); }
if (isset($_REQUEST['km'])) { $km = ($_REQUEST['km']); }
if (isset($_REQUEST['num'])) { $num = intval($_REQUEST['num']); }
if (isset($_REQUEST['cc'])) { $contacto = ($_REQUEST['cc']); }

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
  if (isset($_REQUEST['id_producto'])) { $vehiculo = intval($_REQUEST['id_producto']); }
  if (isset($_REQUEST['kilometraje'])) { $kilometraje = intval($_REQUEST['kilometraje']); }
  if (!es_nulo($vehiculo) ){          
     $ordenesborrador=0;      
     $ordenesborrador=get_dato_sql("averia","COUNT(*)"," WHERE id_estado not in  (20,22) AND id_producto=".$vehiculo);        
     if ($ordenesborrador>0) {
        $verror.="Vehiculo tiene ".$ordenesborrador." Orden de Averia pendiente de autorizar";  
     }         
  }    

  if ($verror=="") {
  
      //Campos
      $sqlcampos="";
      $sqlcampos_detalle="";
      if (isset($_REQUEST["id_tipo"])) { $sqlcampos.= "  id_tipo =".GetSQLValue($_REQUEST["id_tipo"],"int"); } 
      if (isset($_REQUEST["id_inspeccion"])) { $sqlcampos.= " , id_inspeccion =".GetSQLValue($_REQUEST["id_inspeccion"],"int"); } 
   
      if (isset($_REQUEST["tipo"])) { $sqlcampos.= " , tipo =".GetSQLValue($_REQUEST["tipo"],"int"); } 
      if (isset($_REQUEST["contacto"])) { $sqlcampos.= " , contacto =".GetSQLValue($_REQUEST["contacto"],"text"); } 
      if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
      if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
      if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
      //   if (isset($_REQUEST["id_taller"])) { $sqlcampos.= " , id_taller =".GetSQLValue($_REQUEST["id_taller"],"int"); }
      if (isset($_REQUEST["numero_inspeccion"])) { $sqlcampos.= " , numero_inspeccion =".GetSQLValue($_REQUEST["numero_inspeccion"],"int"); } 
      if (isset($_REQUEST["id_tecnico1"])) { $sqlcampos.= " , id_tecnico1 =".GetSQLValue($_REQUEST["id_tecnico1"],"int"); } 
      if (isset($_REQUEST["id_tipo_causa"])) { $sqlcampos.= " , id_tipo_causa =".GetSQLValue($_REQUEST["id_tipo_causa"],"int"); } 

      //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('averia',"IFNULL((max(numero)+1),1)"," "),"int"); //where id_tienda=".$_SESSION['tienda_id']
        $sql="insert into averia set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
   

      if ($result!=false){
          //  historial
          sql_insert("INSERT INTO averia_historial_estado (id_maestro,  id_usuario,  nombre, fecha, observaciones)
          VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Nueva Orden', NOW(), '')");

          //enviar correo
          require_once ('correo_averia_nuevo.php');
          
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
  $ordenesborrador=get_dato_sql("averia","COUNT(*)"," WHERE  id_inspeccion=".$id_inspeccion); //id_estado=1 AND
  if ($ordenesborrador>0) {
    $valerror=mensaje("No puede crear una nueva orden porque actualmente se encontró $ordenesborrador  orden desde esta misma hoja de inspección",'warning');
    $valerror.='<br><br> <a id="btn-filtro" href="#" onclick="get_page(\'pagina\',\'averia_ver.php\',\'Ver Ordenes de Avería\') ; return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Buscar Ordenes</a>';
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
                if (!es_nulo($id_producto)) {$producto_etiqueta=get_dato_sql('producto',"concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,''))"," where id=".$id_producto); }  
                   echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,' ',' required ','get.php?a=3&t=1',$producto_etiqueta);
                ?>          
            </div>
             
      </div>

      <div class="row"> 
            <div class="col-md-6">       
                <?php echo campo("contacto","Contacto",'text',$contacto,' ','  ','');   ?>    
            </div>

            <div class="col-md-6">       
                <?php 
                echo  campo("id_tecnico1", "Taller",'select2',valores_combobox_db('usuario','','nombre',' where activo=1 and grupo_id=35 and tienda_id='.$_SESSION['tienda_id'],'','Todos'),' ','  ','');       
                ?>  
            </div>
             
      </div>



      
      <div class="row"> 
   
            <div class="col-md-6">       
                <?php 
                echo campo("id_tipo","Tipo de Averia",'select2',valores_combobox_db('averia_tipo','','nombre','','','...'),' ','  ','');
                     ?>          
            </div>

            <div class="col-md-3">       
                <?php echo campo("kilometraje","Kilometraje",'number',$km,' ',' required ','','');   ?>    
            </div>

            <div class="col-md-3">       
                <?php 
                echo campo("numero_inspeccion","Numero Inspeccion",'number',$num,' ');
             
                    ?>         
            </div>  

                   
      </div>

      <div class="row"> 
          <div class="col-md-6">       
              <?php 
                 echo campo("id_tipo_causa","Tipo de Causa",'select2',valores_combobox_db('averia_tipo_causa','','nombre','','','...'),'','','');
               ?>          
          </div>
       </div>



              

      <div class="card mb-3">
          <div class="card-header  bg-secondary text-white ">
               Observaciones
           </div>
           <div class="card-body">   
               <div class="row"> 
                   <div class="col-md">     
                       <?php echo campo("observaciones","Observaciones",'textarea','',' ',' rows="5" '.'');?>  
           
                    </div>
            
            
          </div>

        </div>
      </div>
  
       
     
    




      


      
      <div class="row">
          <div class="col">
          <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
          
            <a href="#" onclick="procesar_averia_nuevo('averia_mant_nuevo.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
           

          </div>
          </div>
        </div>

      
        </fieldset>
        </form> 
  

  </div>



</div>
 






</div><!--  card-body -->


<script>


function procesar_averia_nuevo(url,forma,adicional){

  var validado=false;
  var forms = document.getElementsByClassName('needs-validation');
  var validation = Array.prototype.filter.call(forms, function(form) {
						       
								 
        if (form.checkValidity() === false) {
                mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
            } else {validado=true;}
            form.classList.add('was-validated');
            
        });

        // if (validado==true) {
        //     if ($("#id_tipo").val()=='' || $("#id_tipo").val()=='0') {
        //     mytoast('warning','Debe seleccionar el tipo',3000) ;
        //     validado=false;
        //    }           
        // }

           if (validado==true) {
                if ($("#cliente_id").val()=='' || $("#cliente_id").val()=='0' || $("#cliente_id").val()==null) {
                mytoast('warning','Debe seleccionar el Cliente',3000) ;
                validado=false;
                }           
           }

           if (validado==true) {
                if ($("#id_producto").val()=='' || $("#id_producto").val()=='0' || $("#id_producto").val()==null ) {
                mytoast('warning','Debe seleccionar el Vehiculo',3000) ;
                validado=false;
                }           
           }

           
           if (validado==true) {
                if ($("#id_tipo_causa").val()=='' || $("#id_tipo_causa").val()=='0' || $("#id_tipo_causa").val()==null ) {
                mytoast('warning','Debe seleccionar el tipo de causa',3000) ;
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
				
	
				get_page('pagina','averia_mant.php?a=v&cid='+json[0].pcid,'Orden de Avería') ; 
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