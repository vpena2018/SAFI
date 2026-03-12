<?php
require_once ('include/framework.php');

if (!isset($_REQUEST['a'])) { $accion = 'v'; } else { $accion = $_REQUEST['a']; }
$cid = 0;
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); }

if ($accion == 'gfoto') {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] = "Error";
    $stud_arr[0]["pcid"] = 0;

    $is_main = isset($_POST['isMain']) ? intval($_POST['isMain']) : 0;
    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }

    if (isset($_REQUEST['arch'])) {
        $foto_original = urldecode($_REQUEST['arch']);
        $foto = str_replace(' ', '_', urldecode($_REQUEST["arch"]));

        $ruta1 = 'uploa_d_ventas/' . $foto_original;
        $ruta2 = 'uploa_d_ventas/thumbnail/' . $foto_original;
        $nueva1 = 'uploa_d_ventas/' . $foto;
        $nueva2 = 'uploa_d_ventas/thumbnail/' . $foto;

        if (file_exists($ruta1) && !file_exists($nueva1)) { @rename($ruta1, $nueva1); }
        if (file_exists($ruta2) && !file_exists($nueva2)) { @rename($ruta2, $nueva2); }
    } else {
        $foto = "";
    }

    if (!es_nulo($foto) && !es_nulo($cid)) {
        $insert = sql_insert("INSERT INTO ventas_fotos (id_venta, nombre_archivo, principal, fecha)
                              VALUES ($cid, ".GetSQLValue($foto, "text").", $is_main, NOW())");
        if ($insert !== false) {
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] = "Foto guardada";
            $stud_arr[0]["pcid"] = $cid;
            $stud_arr[0]["parch"] = $foto;
        } else {
            $stud_arr[0]["pmsg"] = "Error al guardar en DB";
        }
    } else {
        $stud_arr[0]["pmsg"] = "Error al guardar la foto";
    }

    salida_json($stud_arr);
    exit;
}

if ($accion == 'dfotoventas') {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] = "ERROR DB102";

    if (isset($_REQUEST['arch'])) { $arch = GetSQLValue(urldecode($_REQUEST["arch"]), "text"); } else { $arch = ""; }
    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); } else { $cid = 0; }

    if ($arch !== "" && $cid > 0) {
        borrar_foto_directorio($cid, "", $arch, "foto_ventas");
        $result = sql_delete("DELETE FROM ventas_fotos
                              WHERE id_venta=$cid
                              AND nombre_archivo=$arch
                              LIMIT 1");
        if ($result != false) {
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] = "Borrado";
        }
    }

    salida_json($stud_arr);
    exit;
}

if ($accion == 'ufotoportadaventas') {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] = "ERROR DB102";

    if (isset($_REQUEST['cod'])) { $cod = intval($_REQUEST["cod"]); } else { $cod = 0; }
    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); } else { $cid = 0; }

    if ($cod > 0 && $cid > 0) {
        $sqlReset = "UPDATE ventas_fotos SET principal=0 WHERE id_venta=".$cid;
        $sqlMarcar = "UPDATE ventas_fotos SET principal=1 WHERE id=".$cod." AND id_venta=".$cid." LIMIT 1";
        $resultReset = sql_update($sqlReset);
        $resultMarcar = sql_update($sqlMarcar);
        if ($resultReset != false && $resultMarcar != false) {
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] = "Actualizado";
        }
    }

    salida_json($stud_arr);
    exit;
}

if ($cid <= 0) {
    echo '<div class="alert alert-warning">Debe guardar la venta antes de administrar fotos.</div>';
    exit;
}
?>

<div class="" id="insp_fotos_thumbs_ventas"></div>
<div class="row">
  <div class="col-md-10" id="archivofotoventas">
<?php
$total_filas = 0;
$principal = false;

$sql = "SELECT id,nombre_archivo,fecha,principal FROM ventas_fotos WHERE id_venta=".GetSQLValue($cid,"int")." ORDER BY principal DESC,id DESC";
$result = sql_select($sql);
if ($result != false) {
    $total_filas = $result->num_rows;
    if ($total_filas > 0) {
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;justify-items:center;align-items:start;justify-content:center;">';
        while ($row = $result->fetch_assoc()) {
            $es_principal = (bool)$row["principal"];
            $nombre_archivo = $row["nombre_archivo"];
            $fext = strtolower(substr($nombre_archivo, -3));
            if (in_array($fext, array('jpg','peg','png','gif'))) {
                echo '<div style="text-align:center;">';
                echo '<a href="#" onclick="mostrar_foto_vta(\''.$nombre_archivo.'\',\'uploa_d_ventas/\'); return false;" style="display:inline-block;transition:transform .2s ease-in-out;">';
                echo '<img class="img img-thumbnail mb-2" src="uploa_d_ventas/thumbnail/'.$nombre_archivo.'" style="width:100%;max-width:160px;height:auto;border-radius:6px;">';
                echo '</a>';
                if (tiene_permiso(186)) {
                    echo '<div style="text-align:center;font-size:13px;">';
                    echo '<a href="#" onclick="borrar_fotodb_vta('.$row["id"].',\''.$nombre_archivo.'\'); return false;" style="color:#dc3545;text-decoration:none;"><i class="fa fa-eraser"></i> Borrar</a> ';
                    if ($es_principal) {
                        echo '<i class="fa fa-star" title="Foto de portada" style="color:#f0c651;"> Portada</i>';
                    } else {
                        echo '<a href="#" onclick="marcar_portada_vta('.$row["id"].',\''.$nombre_archivo.'\'); return false;" style="color:#6c757d;text-decoration:none;"><i class="far fa-star"></i> Portada</a>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
        }
        echo '</div>';
    }
}

if (tiene_permiso(186)) {
    $a = $total_filas;
    while ($a < 10) {
        echo '<div class="row"><div class="col-12"><div class="ins_varias_foto_div">';
        echo campo_upload_foto_ventas("ins_foto".$a,"Adjuntar Fotos",'upload','', '  ','',3,9,'NO',false,$principal );
        echo '</div></div></div><hr>';
        $a++;
    }
}
?>
  </div>
</div>

<script>
function ventas_fotos_admin_refrescar(){
  var cid = $('#id').val();
  $('#nav_Fotos_venta').load('ventas_fotos_admin.php?cid=' + cid);
}

function mostrar_foto_vta(imagen, folder){
  Swal.fire({ imageUrl: folder + imagen });
}

function borrar_fotodb_vta(codid, arch){
  var cid = $("#id").val();
  var datos = { a: "dfotoventas", cid: cid, cod: codid, arch: encodeURI(arch) };
  Swal.fire({
    title: 'Borrar Foto',
    text: 'Desea Borrar la Foto o Documento adjunto?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.value) {
      $.post('ventas_fotos_admin.php', datos, function(json){
        if (json.length > 0 && json[0].pcode == 1) {
          mytoast('success', json[0].pmsg, 3000);
          ventas_fotos_admin_refrescar();
        } else {
          mytoast('error', (json[0] && json[0].pmsg) ? json[0].pmsg : 'Error', 3000);
        }
      }).fail(function(){ mytoast('error', 'Error al borrar foto', 3000); });
    }
  });
}

function marcar_portada_vta(codid, arch){
  var cid = $("#id").val();
  var datos = { a: "ufotoportadaventas", cid: cid, cod: codid, arch: encodeURI(arch) };
  Swal.fire({
    title: 'Foto de portada',
    text: 'Desea Marcar la foto como portada?',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.value) {
      $.post('ventas_fotos_admin.php', datos, function(json){
        if (json.length > 0 && json[0].pcode == 1) {
          mytoast('success', json[0].pmsg, 3000);
          ventas_fotos_admin_refrescar();
        } else {
          mytoast('error', (json[0] && json[0].pmsg) ? json[0].pmsg : 'Error', 3000);
        }
      }).fail(function(){ mytoast('error', 'Error al actualizar portada', 3000); });
    }
  });
}

function insp_guardar_foto_ventas(arch, campo, isMain){
  var cid = $("#id").val();
  var datos = { a: "gfoto", arch: encodeURI(arch), cid: cid, isMain: isMain };
  $.post('ventas_fotos_admin.php', datos, function(json){
    if (json && json.length > 0 && json[0].pcode == 1) {
      mytoast('success', (json[0].pmsg ? json[0].pmsg : 'Guardado'), 3000);
      ventas_fotos_admin_refrescar();
    } else {
      mytoast('error', (json[0] && json[0].pmsg) ? json[0].pmsg : 'Error al guardar la foto', 3000);
    }
  }).fail(function(){
    mytoast('error', 'Error al guardar la foto', 3000);
  });
}
</script>
