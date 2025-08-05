<?php

require_once ('include/framework.php');
pagina_permiso(33);


$id_inspeccion='';
$id_producto='';
$cliente_id='';
$cliente_nombre='';
$km='';

if (isset($_REQUEST['ins'])) { $id_inspeccion = intval($_REQUEST['ins']); }
if (isset($_REQUEST['pid'])) { $id_producto = intval($_REQUEST['pid']); }
if (isset($_REQUEST['ccl'])) { $cliente_id = intval($_REQUEST['ccl']); }
if (isset($_REQUEST['cnb'])) { $cliente_nombre = ($_REQUEST['cnb']); }
if (isset($_REQUEST['km'])) { $km = ($_REQUEST['km']); }

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


  if ($verror=="") {


     
      //Campos
      $sqlcampos="";
      $sqlcampos_detalle="";
      if (isset($_REQUEST["id_tipo"])) { $sqlcampos.= "  id_tipo =".GetSQLValue($_REQUEST["id_tipo"],"int"); } 
      if (isset($_REQUEST["id_inspeccion"])) { $sqlcampos.= " , id_inspeccion =".GetSQLValue($_REQUEST["id_inspeccion"],"int"); } 
   
      if (isset($_REQUEST["tipo"])) { $sqlcampos.= " , tipo =".GetSQLValue($_REQUEST["tipo"],"int"); } 
     
      if (isset($_REQUEST["numero_alterno"])) { $sqlcampos.= " , numero_alterno =".GetSQLValue($_REQUEST["numero_alterno"],"text"); } 
       if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); } 
      if (isset($_REQUEST["id_producto"])) { $sqlcampos.= " , id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
      if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); } 
      if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
   //   if (isset($_REQUEST["id_taller"])) { $sqlcampos.= " , id_taller =".GetSQLValue($_REQUEST["id_taller"],"int"); }

        //Crear nuevo            
        $sqlcampos.= " , fecha =NOW(), hora =NOW()"; 
        $sqlcampos.= " , id_usuario =".$_SESSION["usuario_id"];
        $sqlcampos.= " , id_tienda =".$_SESSION['tienda_id'];
        $sqlcampos.= " , id_estado =1";
        $sqlcampos.= " , numero =".GetSQLValue(get_dato_sql('cotizacion',"IFNULL((max(numero)+1),1)"," "),"int");//where id_tienda=".$_SESSION['tienda_id'] 
        $sql="insert into cotizacion set ".$sqlcampos." ";
   
        $result = sql_insert($sql);
        $cid=$result; //last insert id 
   

      if ($result!=false){


        
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
                <?php 
                echo campo("id_tipo","Tipo",'select2',valores_combobox_db('averia_tipo','','nombre','','','...'),' ',' required ','');
                     ?>          
            </div>

            <div class="col-md-4">       
                <?php echo campo("kilometraje","Kilometraje",'number',$km,' ',' required ','','');   ?>    
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
          
            <a href="#" onclick="procesar_cotizacion_nuevo('cotizacion_mant_nuevo.php?a=g','forma',''); return false;" class="btn btn-primary mr-2 mb-2 xfrm" ><i class="fa fa-check "></i> Guardar</a>
           

          </div>
          </div>
        </div>

      
        </fieldset>
        </form> 
  

  </div>



</div>
 






</div><!--  card-body -->


<script>


function procesar_cotizacion_nuevo(url,forma,adicional){

  var validado=false;
  var forms = document.getElementsByClassName('needs-validation');
  var validation = Array.prototype.filter.call(forms, function(form) {
						       
								 
        if (form.checkValidity() === false) {
                mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
            } else {validado=true;}
            form.classList.add('was-validated');
            
        });

        if (validado==true) {
            if ($("#id_tipo").val()=='' || $("#id_tipo").val()=='0') {
            mytoast('warning','Debe seleccionar el tipo',3000) ;
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
				
	
				get_page('pagina','cotizacion_mant.php?a=v&cid='+json[0].pcid,'Cotización') ; 
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