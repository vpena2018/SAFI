<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');

// GUARDAR ARCHIVO
if ($accion =="g") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB101";

    if (isset($_REQUEST['arch'])) { $arch = GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}

    $result = sql_insert("INSERT INTO cotizacion_foto (id_maestro, archivo,fecha,id_usuario) 	VALUES ($cid, $arch,CURDATE(),".$_SESSION['usuario_id'].")");
    $cid=$result; //last insert id 

    foto_reducir_tamano(app_dir."uploa_d/". urldecode($_REQUEST["arch"]));

  if ($result!=false){

    $stud_arr[0]["pcode"] = 1;
      $stud_arr[0]["pmsg"] ="Guardado";
      $stud_arr[0]["pcid"] = $cid;
  }

  salida_json($stud_arr);
    exit;

}


// borrar ARCHIVO
if ($accion =="d") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB101";

    if (isset($_REQUEST['arch'])) { $arch = GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}

    $result = sql_delete("DELETE FROM cotizacion_foto 
                            WHERE id_maestro=$cid and archivo=$arch
                            and id_usuario=".$_SESSION['usuario_id']."
                            LIMIT 1
                            ");

    if ($result!=false){

        //TODO borrar archivo $arch

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="Borrado";
    }

  salida_json($stud_arr);
    exit;

}


$result = sql_select("SELECT cotizacion_foto.id,cotizacion_foto.id_maestro,cotizacion_foto.archivo,cotizacion_foto.archivo,cotizacion_foto.fecha
FROM cotizacion_foto
LEFT OUTER JOIN cotizacion ON (cotizacion.id=cotizacion_foto.id_maestro)
WHERE cotizacion_foto.id_maestro=$cid and cotizacion.id_producto=$pid 
order by cotizacion_foto.fecha,cotizacion_foto.id");
  
  echo '<hr><div class="row" id="insp_fotos_thumbs">';
if ($result!=false){
    if ($result -> num_rows > 0) { 
     
        while ($row = $result -> fetch_assoc()) {
            $fext = substr($row["archivo"], -3);
            if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
               
                echo '  <a href="#" onclick="mostrar_foto(\''.$row["archivo"].'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$row["archivo"].'" data-cod="'.$row["id"].'"></a> ';
            } else {
                echo '  <a href="uploa_d/'.$row["archivo"].'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$row["archivo"].'</a> ';
            }
           
        }   
       
    }
}
 echo '</div>';    
?>

<hr>
<input id="cid" name="cid" type="hidden" value="<?php echo $cid; ?>" >
<input id="pid" name="pid" type="hidden" value="<?php echo $pid; ?>" >





    <?php
     $a=1;
     while ($a <= 10) {
         echo '<div class="row"><div class="col-12">';
         echo '<div class="ins_foto_div">';
         echo campo_upload("ins_foto".$a,"Adjuntar Foto o Documento",'upload','', '  ','',3,9,'NO',false );
         echo "</div></div></div>";
         echo "<hr>";
         $a++;
     }
      
      ?>    

 


<script> 
    function insp_guardar_foto(arch,campo){

     var datos= { a: "g", cid: $("#cid").val(), pid: $("#pid").val() , arch: encodeURI(arch)} ;
        

	 $.post( 'cotizacion_fotos.php',datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
                $('#'+campo).val(arch);                
                $('#files_'+campo).text('Guardado');
                $('#lk'+campo).html(arch);
                //thumb_agregar(arch);
                thumb_agregar2(arch,campo);
			
			}
		} else {mytoast('error',json[0].pmsg,3000) ; }
		  
	})
	  .done(function() {	  })
	  .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
	  .always(function() {	  });
    
    }


    function mostrar_foto(imagen) {

        $('#ModalWindowTitle').html('');
		$('#ModalWindowBody').html('<img class="img-fluid" src="uploa_d/'+imagen+'">'); 
        $('#ModalWindow').modal('show');


    
     // Swal.fire({
	// 	imageUrl: imagen,	
	// 	imageAlt: '',
	// 	grow: 'fullscreen'
	//   })
//	imageHeight: 1500,
// showCloseButton: false,
//   showCancelButton: false
}

function thumb_agregar(archivo){
    if (archivo!='' && archivo!=undefined) {
        
   
    var fext= archivo.substr(archivo.length - 3);

   if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
    $("#insp_fotos_thumbs").append('<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a>');
   } else {
    $("#insp_fotos_thumbs").append('<a href="uploa_d/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>');
   }
  }
}


function thumb_agregar2(archivo,campo){
    var salida='';
    if (archivo!='' && archivo!=undefined) {
        
   
    var fext= archivo.substr(archivo.length - 3);

   if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
    salida='<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a> ';
   } else {
    salida='<a href="uploa_d/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>';
   }
   $("#"+campo).closest('.ins_foto_div').html(salida +'<a id="del_'+campo+'" href="#" onclick="insp_borrar_foto(\''+archivo+'\',\'del_'+campo+'\'); return false;" class="btn  btn-outline-secondary ml-3 "><i class="fa fa-eraser"></i> Borrar</a>');
  }
}



function insp_borrar_foto(arch,campo){

var datos= { a: "d", cid: $("#cid").val(), pid: $("#pid").val() , arch: encodeURI(arch)} ;
  

Swal.fire({
	  title: 'Borrar Foto',
	  text:  'Desea Borrar la Foto o Documento adjunto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'cotizacion_fotos.php',datos, function(json) {
                
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        console.log('->'+campo);
                        $("#"+campo).closest('.ins_foto_div').html('Eliminado');
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	  })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});




}

</script>