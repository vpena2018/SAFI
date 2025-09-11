<?php

if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}

if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}

if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}



require_once ('include/framework.php');
//require_once ('GetObjectS3.php');






// GUARDAR ARCHIVO

if ($accion =="g") {

    $stud_arr[0]["pcode"] = 0;

    $stud_arr[0]["pmsg"] ="ERROR DB101";



    //  //  historial

    //  sql_insert("INSERT INTO inspeccion_historial_estado (id_maestro,  id_usuario,  nombre, fecha, observaciones)

    //  VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Guardar FOTO adjunta"."', NOW(), '')");

 



    if (isset($_REQUEST['arch'])) { $arch = GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}



    $result = sql_insert("INSERT INTO inspeccion_foto (id_inspeccion, archivo,fecha,id_usuario) 	VALUES ($cid, $arch,CURDATE(),".$_SESSION['usuario_id'].")");

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



    if (isset($_REQUEST['arch'])) { $arch = "and archivo=".GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}

    if (isset($_REQUEST['cod'])) { $cod = "and id=".GetSQLValue(urldecode($_REQUEST["cod"]),"text"); } else	{$cod ="" ;}

    if ($cod<>'' or $arch<>'') {

    borrar_foto_directorio($cid,$cod,$arch,"inspeccion");

    $result = sql_delete("DELETE FROM inspeccion_foto 
                            WHERE id_inspeccion=$cid 
                            $arch
                            $cod
                            LIMIT 1
                            ");

 } else {$result==false;}

    if ($result!=false){



    //      //  historial

    // sql_insert("INSERT INTO inspeccion_historial_estado (id_maestro,  id_usuario,  nombre, fecha, observaciones)

    // VALUES ( $cid,  ".$_SESSION['usuario_id'].", 'Borrar FOTO adjunta"."', NOW(), '')");



        //TODO borrar archivo $arch



        $stud_arr[0]["pcode"] = 1;

        $stud_arr[0]["pmsg"] ="Borrado";

    }



  salida_json($stud_arr);

    exit;



}









//Fotos desde hoja de inspeccion

if (isset($_REQUEST['insp'])) { $insp = intval($_REQUEST['insp']); } else {$insp =0;}

if (!es_nulo($insp)) {

    $result = sql_select("SELECT inspeccion_foto.id,inspeccion_foto.id_inspeccion,inspeccion_foto.archivo,inspeccion_foto.fecha

    ,inspeccion.id_estado,year(inspeccion_foto.fecha) as ano

    FROM inspeccion_foto

    LEFT OUTER JOIN inspeccion ON (inspeccion.id=inspeccion_foto.id_inspeccion)

    WHERE inspeccion_foto.id_inspeccion=$insp and inspeccion.id_producto=$pid 

    order by inspeccion_foto.fecha,inspeccion_foto.id");

     

   

      

    if ($result!=false){

        if ($result -> num_rows > 0) {

            echo '<div class="row" ><strong> Fotos desde Inspección Anterior</strong></div>';

            echo '<hr><div class="row" >'; 



            // $fecha_actualfoto='';

            while ($row = $result -> fetch_assoc()) {             
                //$url=getUrlImagen($row['archivo']);
                $fext = substr($row["archivo"], -3);
                $ano = intval($row['ano']);                
                $fecha = sanear_date($row['fecha']);
                if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
                    if ($fecha<='2024-12-31'){
                        //$archivo = shell_exec("aws s3 ls s3://inglosa --recursive 2>/dev/null | grep -m 1".$row['archivo']);
                        echo '  <a href="#" onclick="mostrar_foto2(\''.$row["archivo"].'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="aws_bucket_s3/thumbnail/'.$row["archivo"].'" data-cod="'.$row["id"].'"></a> ';
                        //echo '  <a href="#" onclick="mostrar_foto2(\''.$url.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$url.'" data-cod="'.$row["id"].'"></a> ';
                    }else{  
                       echo '  <a href="#" onclick="mostrar_foto(\''.$row["archivo"].'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$row["archivo"].'" data-cod="'.$row["id"].'"></a> ';
                    }
                } else {
                       echo '  <a href="uploa_d/'.$row["archivo"].'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$row["archivo"].'</a> ';                        

                }               

            } 



           echo '</div>';  

        }

    }

    

}



//fotos de orden

$result = sql_select("SELECT inspeccion_foto.id,inspeccion_foto.id_inspeccion,inspeccion_foto.archivo,inspeccion_foto.fecha

,inspeccion.id_estado,year(inspeccion_foto.fecha) as ano

FROM inspeccion_foto

LEFT OUTER JOIN inspeccion ON (inspeccion.id=inspeccion_foto.id_inspeccion)

WHERE inspeccion_foto.id_inspeccion=$cid and inspeccion.id_producto=$pid 

order by inspeccion_foto.fecha,inspeccion_foto.id");

  

  echo '<hr><div class="row" id="insp_fotos_thumbs">';

if ($result!=false){

    if ($result -> num_rows > 0) { 

        // $fecha_actualfoto='';

        while ($row = $result -> fetch_assoc()) {

            // if ($fecha_actualfoto<>$row["fecha"]) {

            //     echo ''.formato_fecha_de_mysql($row["fecha"]).'<hr>';

            //     $fecha_actualfoto=$row["fecha"];

            // }

            $fext = substr($row["archivo"], -3);
            $ano = intval($row['ano']);   
            $fecha = sanear_date($row['fecha']);             
            if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
                   //$url=getUrlImagen($row['archivo']);
                   if ($fecha<='2024-12-31'){ 
                      //$archivo = shell_exec("aws s3 ls s3://inglosa --recursive 2>/dev/null | grep -m 1".$row['archivo']);
                      echo '  <a href="#" class="foto_br'.$row["id"].'" onclick="mostrar_foto2(\''.$row["archivo"].'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" style="width: 180px; height: auto; src="aws_bucket_s3/thumbnail/'.$row["archivo"].'" data-cod="'.$row["id"].'"></a> '; 
                      //echo '  <a href="#" class="foto_br'.$row["id"].'" onclick="mostrar_foto2(\''.$url.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$url.'" data-cod="'.$row["id"].'"></a> '; 
                   }else{
                      echo '  <a href="#" class="foto_br'.$row["id"].'" onclick="mostrar_foto(\''.$row["archivo"].'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" style="width: 180px; height: auto; src="uploa_d/thumbnail/'.$row["archivo"].'" data-cod="'.$row["id"].'"></a> '; 
                   }
                if ($row["id_estado"]<2 or tiene_permiso(152))  { 

                    echo '  <a href="#" class="mr-5 foto_br'.$row["id"].'" onclick="borrar_fotodb('.$row["id"].'); return false;" ><i class="fa fa-eraser"></i> Borrar</a> ';

                }

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



    $elestado= get_dato_sql('inspeccion',"id_estado"," WHERE id=$cid ");
   $puede_agregar_varias=true;
    $puede_agregar_fotos=true;

    if ($elestado>1)  {$puede_agregar_fotos=false; }

    if ($puede_agregar_fotos==true) {     

        if (!tiene_permiso(180)) {
            $puede_agregar_varias=false;
        }

                if ($puede_agregar_varias==true) {
            echo '<div class="row"><div class="col-12">';
            echo '<div class="ins_foto_div">';
            echo campo_upload_varias("ins_foto0","Adjuntar Fotos o Documentos",'upload','', '  ','',3,9,'NO',false );
            echo "</div></div></div>";
            echo "<hr>"; 
        }else{
                 $a=1;

     while ($a <= 10) {

         echo '<div class="row"><div class="col-12">';
         echo '<div class="ins_foto_div">';
         echo campo_upload("ins_foto".$a,"Adjuntar Foto o Documento",'upload','', '  ','',3,9,'NO',false );
         echo "</div></div></div>";
         echo "<hr>";
         $a++;

     }

        }
    }

      ?>    









<script> 

    function insp_guardar_foto(arch,campo){


        var puede_agregar_varias = <?= $puede_agregar_varias ? 'true' : 'false' ?>;
     var datos= { a: "g", cid: $("#cid").val(), pid: $("#pid").val() , arch: encodeURI(arch)} ;

        



	 $.post( 'inspeccion_fotos.php',datos, function(json) {

	 			

		if (json.length > 0) {

			if (json[0].pcode == 0) {

				

				mytoast('error',json[0].pmsg,3000) ;   

			}

			if (json[0].pcode == 1) {

                $('#'+campo).val(arch);                

                $('#files_'+campo).text('Guardado');

                $('#lk'+campo).html(arch);

               // thumb_agregar(arch);

               thumb_agregar2(arch,campo);

			

			}

		} else {mytoast('error',json[0].pmsg,3000) ; }

		  

	})

	  .done(function() { if(puede_agregar_varias){insp_cambiartab('nav_fotos');}	  })

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

function mostrar_foto2(imagen) {

    $('#ModalWindowTitle').html('');

    $('#ModalWindowBody').html('<img class="img-fluid" src="aws_bucket_s3/'+imagen+'">'); 

    $('#ModalWindow').modal('show');

}

function thumb_agregar(archivo){

    if (archivo!='' && archivo!=undefined) {

        

   

    var fext= archivo.substr(archivo.length - 3);



   if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {

    $("#insp_fotos_thumbs").append('<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a> ');

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

	    

            $.post( 'inspeccion_fotos.php',datos, function(json) {

                

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





function borrar_fotodb(codid){



var datos= { a: "d", cid: $("#cid").val(), pid: $("#pid").val() , cod: codid} ;

  



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

	    

            $.post( 'inspeccion_fotos.php',datos, function(json) {

                

                if (json.length > 0) {

                    if (json[0].pcode == 0) {

                        

                        mytoast('error',json[0].pmsg,3000) ;   

                    }

                    if (json[0].pcode == 1) {

                        

                        $(".foto_br"+codid).hide();

                        mytoast('success',json[0].pmsg,3000) ;

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