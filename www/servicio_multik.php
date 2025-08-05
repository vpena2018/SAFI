<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');

//pagina_permiso(000);
// if (!tiene_permiso(40) and !tiene_permiso(41)) { 
// 	echo '<div class="card-body">';
// 	echo'No tiene privilegios para accesar esta función';
//     echo '</div>';
//     exit;
// 	}



// guardar Datos    ############################  
if ($accion=="g") {

       $stud_arr[0]["pcode"] = 0;
       $stud_arr[0]["pmsg"] ="ERROR";
   
   
       $salida="";
           
   
       //Validar
       $verror="";
   
   
     if ($verror=="") {


   
        if (isset($_REQUEST['idet'])) {
            $det_insp= array();
            foreach ($_REQUEST['idet'] as $key => $value) {
            $det_insp[$key]=$value;
            } 
            $detalles1= json_encode($det_insp);
        }

        if (isset($_REQUEST['idetjt'])) {
            $det_insp_jt= array();
            foreach ($_REQUEST['idetjt'] as $keyjt => $valuejt) {
              $det_insp_jt[$keyjt]=$valuejt;
            } 
            $detalles_jt= json_encode($det_insp_jt);
        }
        

         //Campos
         $sqlcampos="";
    
         $coma="";
         if (isset($detalles1)) { $sqlcampos.= "  multik =".GetSQLValue($detalles1,"text"); $coma=","; } 
         if (isset($detalles_jt)) { $sqlcampos.= " $coma multik_jt =".GetSQLValue($detalles_jt,"text"); } 
         
    
   
         //actualizar
            $sql="update servicio  set " . $sqlcampos . " where id=".$cid. ' limit 1';
            $result = sql_update($sql);
         
   
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

   




$result = sql_select("SELECT servicio.multik,multik_jt ,servicio.id_tipo_revision,servicio.id_estado
                    ,servicio.numero
                    ,producto.nombre AS producto_nombre
                    ,producto.codigo_alterno AS producto_codigo
                    ,producto.placa AS producto_placa
                    ,producto.chasis AS producto_chasis
                    FROM servicio
                    LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
                    WHERE servicio.id=$cid          
                    limit 1");
$visible_guardar="";
$multik="";
$multik_jt ="";
$id_tipo_revision=0;
$estadoservicio=0;
$descripcion_vehiculo='';

if ($result!=false){
    if ($result -> num_rows > 0) { 
        $row = $result -> fetch_assoc(); 
        $multik=$row['multik'];
        $multik_jt=$row['multik_jt'];
        $id_tipo_revision=$row['id_tipo_revision'];
        $estadoservicio=$row['id_estado'];
        $descripcion_vehiculo='<b>'.$row['producto_codigo'].'</b> '.$row['producto_nombre'];
        $descripcion_vehiculo.='<br>Placa: <b>'.$row['producto_placa'].'</b>  Chasis: <b>'.$row['producto_chasis'].'</b>';

    } else {echo "No se encontro el registro"; exit;}
} else {echo "No se encontro el registro"; exit;}


if ($id_tipo_revision<1 or $id_tipo_revision>5) {
    echo "Debe seleccionar el tipo de revision multi-k que corresponda"; exit;
}


$detalle_arr=json_decode($multik,true);
$detalle_arr_jt=json_decode($multik_jt,true);


$det_salida='';
$det_encabezado='';
$detalle_inspeccion = sql_select("SELECT multik_revision.id, multik_revision.nombre, multik_revision_grupo.nombre AS grupo
                                    ,k5,k10,k20,k40,k100,jt
                                    FROM multik_revision
                                    LEFT OUTER JOIN multik_revision_grupo ON (multik_revision.id_grupo=multik_revision_grupo.id )
                                    ORDER BY multik_revision.id_grupo,multik_revision.orden");  
if ($detalle_inspeccion!=false) {
  if ($detalle_inspeccion -> num_rows > 0) {

    switch ($id_tipo_revision) {
        case 1:
            $detalle_k="k5";
            break;
        case 2:
            $detalle_k="k10";
            break;
        case 3:
            $detalle_k="k20";
            break;
        case 4:
            $detalle_k="k40";
            break;
        case 5:
            $detalle_k="k100";
            break;
        
        default:
            $detalle_k="k5";
            break;
    }
        
        $detalle_jt="jt";

      $det_salida.='<ul class="list-group mb-3">' ;

      $titulos='<span class="">
      <div class="form-check form-check-inline ml-3"><label class="form-check-label" ><b>Revisado</b></label></div>
      <div class="form-check form-check-inline"><label class="form-check-label" ><b>No<br>Aplica</b></label></div>
      <div class="form-check form-check-inline "><label class="form-check-label" ><b>Jefe<br>Taller</b></label></div>
      </span>';

    $n=1;
      while ($row_detalle = $detalle_inspeccion -> fetch_assoc()) {
        if ($det_encabezado<>$row_detalle["grupo"]) {
          $det_encabezado=$row_detalle["grupo"];
          $det_salida.='  <li class="list-group-item list-group-item-inspeccion list-group-item-info d-flex justify-content-between align-items-center">'.$row_detalle["grupo"].$titulos.'</li> ' ;
        }
        $valact=''; $valjt='';
        if (isset($detalle_arr[$row_detalle["id"]])) {//$row_detalle[$detalle_k]
           $valact=$detalle_arr[$row_detalle["id"]];
        }
        if (isset($detalle_arr_jt[$row_detalle["id"]])) {
           $valjt=$detalle_arr_jt[$row_detalle["id"]];
        }

        $actenabled='';
        $jtenabled='';
        $actrequired='';
        $jtrequired='';
        $claselinea='';

        if ($row_detalle[$detalle_k]<>1) {
            $claselinea='disabled bg-secondary text-light ';
            $valact=2;//no aplica
            $valjt=1;
        } else {
            $actrequired=' required';
            $jtrequired=' required';
        }

        if (!tiene_permiso(40)) {
            $actenabled=' disabled';
        }

        if (!tiene_permiso(41)) {
            $jtenabled=' disabled';
        }       
        
        
        
        
        $det_salida.='  <li class="'.$claselinea.' list-group-item list-group-item-inspeccion list-group-item-action d-flex justify-content-between align-items-center" >'.$n.'. '.$row_detalle["nombre"].'<span class="text-nowrap">'.multik_sino_radio($row_detalle["id"],$valact,$valjt,$actenabled,$jtenabled,$actrequired,$jtrequired).'</span></li> ' ;
        $n++;
      }
      $det_salida.=' </ul>' ;  
  }
} 

function multik_sino_radio($id,$valor,$valorjt,$actenabled,$jtenabled,$actrequired,$jtrequired) {
    global $id_tipo_revision ;
    $salida='';
    $si="";
    $no="";
    $jt="";
   $requiredjt="";
    $tmpvalor=$valor;

    $clase="";

    $salida='';
    $si="";
    $no="";

    $tmpvalor='';
    $tmpvalor2='';
    $tmpsalida="";

     $tmpvalor=$valor ; 
     $tmpvalor2=$valorjt ;
   
    

    if (intval($tmpvalor)==1) {$no=' checked="checked"';}
    if (intval($tmpvalor)==2) {$si=' checked="checked"';}

    if (intval($tmpvalor2)==1) {$jt=' checked="checked"';}
    
    
    $salida.='<div class="form-check form-check-inline ml-3">
    <input class="form-check-input inspradio" type="radio" name="idet['.$id.']" id="insp'.$id.'rev"   value="1" '.$no.' '.$actenabled.' '.$actrequired .'>
    <label class="form-check-label" for="insp'.$id.'rev">Rev</label>
    </div>';
    $salida.='<div class="form-check form-check-inline">
    <input class="form-check-input inspradio" type="radio" name="idet['.$id.']" id="insp'.$id.'na"  value="2" '.$si.' '.$actenabled.' '.$actrequired.'>
    <label class="form-check-label" for="insp'.$id.'na">NA</label>
    </div>';
    $salida.='<div class="form-check form-check-inline  ml-3">
    <input class="form-check-input inspradio" type="checkbox" name="idetjt['.$id.']" id="insp'.$id.'jt"  value="1" '.$jt.' '.$jtenabled.' '.$jtrequired.'>
    <label class="form-check-label" for="insp'.$id.'jt">JT</label>
    </div>';

   


    return $salida;
  }

  echo $descripcion_vehiculo;
?>

     <form id="forma_multik" name="forma_multik" class="needs-validation" >
    <fieldset > 



 
        <div class="row"> 
        <div class="col-md"> 
            <?php echo $det_salida; ?>
  
   
            </div>
    
        </div>
  


    </fieldset> 
    </form>

    <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
        <div class="col">
        <?php //if ($estadoservicio<22) 
        {
            if (tiene_permiso(40) or tiene_permiso(41)) {  ?>
            <a href="#" onclick="procesar_multik('servicio_multik.php?a=g&cid=<?php echo $cid; ?>','forma_multik',''); return false;" class="btn btn-primary  mb-2 xfrm <?php echo $visible_guardar; ?>" ><i class="fa fa-check "></i> Guardar</a>
        <?php } } ?>
        <div class="float-right">
            <a href="#" onclick="$('#ModalWindow2').modal('hide');  return false;" class="btn btn-secondary      mb-2 xfrm" > Cerrar</a>
        </div>
        </div>
		</div>
	</div>
 
<script>
 
 function procesar_multik(url,forma,adicional){

var validado=false;
// var forms = document.getElementsByClassName('needs-validation');
// var validation = Array.prototype.filter.call(forms, function(form) {
        

//     if (form.checkValidity() === false) {
//         mytoast('warning','Debe ingresar todos los campos requeridos',3000) ;
//       } else {validado=true;}
//       form.classList.add('was-validated');
      
//     });

    validado=true;
    
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
        

      //  get_page('pagina','servicio.php?a=v&cid='+json[0].pcid,'Hoja de Inspección') ; 
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

 