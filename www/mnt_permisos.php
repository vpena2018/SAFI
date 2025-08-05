<?php
require_once ('include/framework.php');
pagina_permiso(14);

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}

$detalle_permisos='';

// Leer Datos    ############################  
if ($accion=="v") {
    $result = sql_select("SELECT id, nombre,nivel_padre_id,icono,nivel_categoria_id
    FROM usuario_nivel
    WHERE  activo = 1 
    ORDER BY nivel_padre_id,nivel_categoria_id,orden,nombre");
    
    $lospermisos=array();
    $permisos_asignados=  sql_select("SELECT nivel_id
	FROM usuario_nivelxgrupo
	WHERE grupo_id=$cid");
    if ($permisos_asignados!=false){
        while ($rowpermiso = $permisos_asignados -> fetch_assoc()) {
            array_push($lospermisos,$rowpermiso['nivel_id']);
        }
    }
	
    
    if ($result!=false){
        while ($row = $result -> fetch_assoc()) {
            $icono="";
            $clase1="";
            $clase2="";
            $checked="";
            if (in_array($row['id'], $lospermisos)) {$checked=' checked';}
            
            if (trim($row["icono"])<>'') {
                $icono='<i class="fa fa-'.$row["icono"].'"></i>';
                $clase1="<b>";
                $clase2="</b>";
            }
            $detalle_permisos.= '<tr>
            <td><input class="permiso_chk" type="checkbox" value="'.$row["id"].'" name="per_id_chk[]" '.$checked.'></td>
            <td>'.$icono.' '.$clase1.$row["nombre"].$clase2.'</td>
            </tr>';  
        }
    }


}


// guardar Datos    ############################  
if ($accion=="g") {
    //sleep(3);
       $stud_arr[0]["pcode"] = 0;
       $stud_arr[0]["pmsg"] ="ERROR";
    
       //Validar
       $verror="";

       if (!isset($_REQUEST['per_id_chk'])){ $verror.="Debe asignar algun permiso";}
      
        if ($verror=="") {

            sql_delete("delete from usuario_nivelxgrupo where grupo_id=$cid");
            $coma='';
            $sql="INSERT into usuario_nivelxgrupo (grupo_id,nivel_id) values ";
            foreach( $_REQUEST['per_id_chk'] as $det_id ) {
                $sql.=$coma." ($cid,$det_id)";
                $coma=",";
            }
        
       //Guardar
       $result = sql_insert($sql);
   
       if ($result!=false){
           $stud_arr[0]["pcode"] = 1;
           $stud_arr[0]["pmsg"] ="Guardado";
       }
   
   } else {
       $stud_arr[0]["pcode"] = 0;
       $stud_arr[0]["pmsg"] =$verror;
   }
   
       salida_json($stud_arr);
        exit;
   
   } // fin guardar datos

   $elperfil=get_dato_sql("usuario_grupo","nombre"," WHERE id=$cid")

?>

<p>Selecciones los permisos a asignar al perfil: <b><?php echo $elperfil ?></b></p>
	<form id="formaperm" name="formaperm">
		<fieldset id="fs_forma">
        
        <input type="hidden" name="cid" value="<?php echo $cid; ?>" >
        
        <div class="table-responsive ">

<table id="tablaperm" class="table table-striped table-hover table-sm"  style="width:90%">
    <thead class="thead-dark">
        <tr>
            <th style="width: 20px;"><input type="checkbox"  onchange="permisos_marcar_todos(this,'tablaperm'); "  ></th>
            <th>Permiso</th>
       
        </tr>
    </thead>
    <tbody id="tablabody">
    <?php
        echo $detalle_permisos;
    ?>
        
    </tbody>
   
</table>
</div>

</fieldset>
	</form>

<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top " >
    <div id="cargando" class="oculto"  align="center" > <img src="img/load.gif"/></div>
    <a id="" href="#" onclick="procesar('mnt_permisos.php?a=g','formaperm'); return false;" class="btn btn-sm btn-primary mr-4 mb-2 xfrm"><i class="fa fa-check"></i>  Guardar</a>
    <a href="#" onclick="$('#ModalWindow').modal('hide');  return false;" class="btn btn-sm btn-secondary  mr-4 mb-2 xfrm" > Cerrar</a>
</div>

<script>
function permisos_marcar_todos(objeto,tabla){
  $("#"+tabla+" input[type='checkbox']").prop('checked',  $(objeto).prop('checked'));
}


    // var table=$('#tablaperm').dataTable(     	{
	// //		"bAutoWidth": true,
	// 		"bFilter": false,
	// 		"bPaginate": false,
	// 	//	"bSort": false,
    //     	//"bInfo": false,
    //     	"bStateSave": false,

    //     	"responsive": false,   
    //         "pageLength": 10,
   
  	// 		"dom": '<"clear"> frtiplB',

  	// 		"processing": false,
    //         "serverSide": false,

    // 		 buttons: [ ],
 
    //    	//	"bScrollCollapse": true,
	
	// 		"bJQueryUI": false,
			
	//          "language": { "url": "plugins/datatables/spanish.lang" }			

    // });
    


</script>