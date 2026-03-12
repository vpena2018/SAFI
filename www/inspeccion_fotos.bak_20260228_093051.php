<?php

if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else { exit; }
if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else { exit; }
$accion = isset($_REQUEST['a']) && in_array($_REQUEST['a'], ['g', 'd']) ? $_REQUEST['a'] : '';

require_once ('include/framework.php');

// Helper: renderizar thumbnail de foto o enlace de documento
function render_foto_thumbnail($row, $mostrar_borrar = false,  $puede_borrar_por_permiso = false) {
    $archivo = $row['archivo'];
    $archivo_html = htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8');
    $archivo_js = json_encode($archivo);
    $id = intval($row['id']);
    $fext = strtolower(pathinfo($row['archivo'], PATHINFO_EXTENSION));
    $es_imagen = in_array($fext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

    if ($es_imagen) {
        $src_local_thumb = 'uploa_d/thumbnail/' . $archivo_html;
        echo "<a href=\"#\" onclick='mostrar_foto(" . $archivo_js . "); return false;'>";
        echo '<img class="img img-thumbnail mb-3 mr-3" style="width: 180px; height: auto;" src="' . $src_local_thumb . '" data-cod="' . $id . '">';
        echo '</a> ';
        if ($mostrar_borrar) {
            if ($row["id_estado"] < 2 or ($puede_borrar_por_permiso)) {
                echo '<a href="#" class="mr-5 foto_br' . $id . '" onclick="borrar_fotodb(' . $id . '); return false;"><i class="fa fa-eraser"></i> Borrar</a> ';
            }
        }
    } else {
        echo "<a href=\"#\" onclick='abrir_archivo(" . $archivo_js . "); return false;' class=\"img-thumbnail mb-3 mr-3\">" . $archivo_html . "</a> ";
    }
}

// GUARDAR ARCHIVO
if ($accion == "g") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] = "ERROR DB101";

    if (isset($_REQUEST['arch'])) { $arch = GetSQLValue(urldecode($_REQUEST["arch"]), "text"); } else { $arch = ""; }

    $result = sql_insert("INSERT INTO inspeccion_foto (id_inspeccion, archivo, fecha, id_usuario) VALUES ($cid, $arch, CURDATE(), " . $_SESSION['usuario_id'] . ")");
    $insert_id = $result; // last insert id

    $archivo_nombre = basename(urldecode($_REQUEST["arch"]));
    foto_reducir_tamano(app_dir . "uploa_d/" . $archivo_nombre);

    if ($result != false) {
        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] = "Guardado";
        $stud_arr[0]["pcid"] = $cid; // mantener id_inspeccion
        $stud_arr[0]["pfid"] = $insert_id;
    }

    salida_json($stud_arr);
    exit;
}

// BORRAR ARCHIVO
if ($accion == "d") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] = "ERROR DB101";

    if (isset($_REQUEST['arch'])) { $arch = "and archivo=" . GetSQLValue(urldecode($_REQUEST["arch"]), "text"); } else { $arch = ""; }
    if (isset($_REQUEST['cod'])) { $cod = "and id=" . GetSQLValue(urldecode($_REQUEST["cod"]), "text"); } else { $cod = ""; }

    if ($cod != '' or $arch != '') {
        borrar_foto_directorio($cid, $cod, $arch, "inspeccion");
        $result = sql_delete("DELETE FROM inspeccion_foto
                            WHERE id_inspeccion=$cid
                            $arch
                            $cod
                            LIMIT 1");
    } else {
        $result = false;
    }

    if ($result != false) {
        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] = "Borrado";
    }

    salida_json($stud_arr);
    exit;
}

// Fotos desde hoja de inspección anterior + fotos de orden actual (query combinada)
if (isset($_REQUEST['insp'])) { $insp = intval($_REQUEST['insp']); } else { $insp = 0; }

$tiene_insp_anterior = !es_nulo($insp);

// Query combinada con UNION para ambos conjuntos de fotos
$sql_fotos_orden = "SELECT inspeccion_foto.id, inspeccion_foto.id_inspeccion, inspeccion_foto.archivo, inspeccion_foto.fecha,
    inspeccion.id_estado, 'actual' as origen
    FROM inspeccion_foto
    LEFT OUTER JOIN inspeccion ON (inspeccion.id = inspeccion_foto.id_inspeccion)
    WHERE inspeccion_foto.id_inspeccion = $cid and inspeccion.id_producto = $pid";

if ($tiene_insp_anterior) {
    $sql = "SELECT inspeccion_foto.id, inspeccion_foto.id_inspeccion, inspeccion_foto.archivo, inspeccion_foto.fecha,
        inspeccion.id_estado, 'anterior' as origen
        FROM inspeccion_foto
        LEFT OUTER JOIN inspeccion ON (inspeccion.id = inspeccion_foto.id_inspeccion)
        WHERE inspeccion_foto.id_inspeccion = $insp and inspeccion.id_producto = $pid
        UNION ALL
        $sql_fotos_orden
        ORDER BY origen DESC, fecha, id";
} else {
    $sql = "$sql_fotos_orden ORDER BY fecha, id";
}

$result = sql_select($sql);

$seccion_actual = '';

if ($result != false && $result->num_rows > 0) {
    // Antes del while
    $puede_borrar_por_permiso = tiene_permiso(152);
    while ($row = $result->fetch_assoc()) {
        // Cambio de sección: anterior -> actual
        if ($row['origen'] != $seccion_actual) {
            if ($seccion_actual != '') {
                echo '</div>';
            }
            $seccion_actual = $row['origen'];
            if ($seccion_actual == 'anterior') {
                echo '<div class="row"><strong> Fotos desde Inspección Anterior</strong></div>';
                echo '<hr><div class="row">';
            } else {
                echo '<hr><div class="row" id="insp_fotos_thumbs">';
            }
        }
        $mostrar_borrar = ($row['origen'] == 'actual');
        render_foto_thumbnail($row, $mostrar_borrar, $puede_borrar_por_permiso);
    }
    if ($seccion_actual != '') {
        echo '</div>';
    }
} else {
    echo '<hr><div class="row" id="insp_fotos_thumbs"></div>';
}

?>

<hr>
<input id="cid" name="cid" type="hidden" value="<?php echo htmlspecialchars($cid, ENT_QUOTES, 'UTF-8'); ?>">
<input id="pid" name="pid" type="hidden" value="<?php echo htmlspecialchars($pid, ENT_QUOTES, 'UTF-8'); ?>">

<?php

$elestado = get_dato_sql('inspeccion', "id_estado", " WHERE id=$cid ");
$puede_agregar_varias = true;
$puede_agregar_fotos = true;

$nuevoBloqueParaVarias = '<div class="row"><div class="col-12"><div class="ins_foto_div">' .
    campo_upload_varias("ins_foto0", "Adjuntar Fotos o Documentos", 'upload', '', '  ', '', 3, 9, 'NO', false) .
    '</div></div></div><hr>';

if ($elestado > 1) { $puede_agregar_fotos = false; }

if ($puede_agregar_fotos == true) {
    if (!tiene_permiso(180)) {
        $puede_agregar_varias = false;
    }

    if ($puede_agregar_varias == true) {
        echo '<div class="row"><div class="col-12">';
        echo '<div class="ins_foto_div">';
        echo campo_upload_varias("ins_foto0", "Adjuntar Fotos o Documentos", 'upload', '', '  ', '', 3, 9, 'NO', false);
        echo "</div></div></div>";
        echo "<hr>";
        echo '<div class="ins_foto_div_nuevo">';
    } else {
        for ($a = 1; $a <= 10; $a++) {
            echo '<div class="row"><div class="col-12">';
            echo '<div class="ins_foto_div">';
            echo campo_upload("ins_foto" . $a, "Adjuntar Foto o Documento", 'upload', '', '  ', '', 3, 9, 'NO', false);
            echo "</div></div></div>";
            echo "<hr>";
        }
    }
}

?>

<script>
window.cantidadFotosSubidasGlobal = 0;

function comprimirSiEsImagen(file, opts) {
    opts = opts || {};
    var maxLado = opts.maxLado || 1600;
    var calidad = opts.calidad || 0.82;
    var tamanoMinimo = opts.tamanoMinimo || (400 * 1024);

    if (!file || !file.type || file.type.indexOf('image/') !== 0) {
        return Promise.resolve(file);
    }

    if (file.size < tamanoMinimo) {
        return Promise.resolve(file);
    }

    return new Promise(function(resolve) {
        var img = new Image();
        var objectUrl = URL.createObjectURL(file);

        img.onload = function() {
            var w = img.naturalWidth || img.width;
            var h = img.naturalHeight || img.height;
            var ratio = 1;

            if (w > h && w > maxLado) {
                ratio = maxLado / w;
            } else if (h >= w && h > maxLado) {
                ratio = maxLado / h;
            }

            var newW = Math.max(1, Math.round(w * ratio));
            var newH = Math.max(1, Math.round(h * ratio));

            var canvas = document.createElement('canvas');
            canvas.width = newW;
            canvas.height = newH;
            var ctx = canvas.getContext('2d', { alpha: false });
            ctx.drawImage(img, 0, 0, newW, newH);

            var mimeOut = (file.type === 'image/png') ? 'image/png' : 'image/jpeg';
            canvas.toBlob(function(blob) {
                URL.revokeObjectURL(objectUrl);
                if (!blob || blob.size >= file.size) {
                    resolve(file);
                    return;
                }

                var nuevoNombre = file.name;
                if (mimeOut === 'image/jpeg') {
                    nuevoNombre = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                }

                try {
                    resolve(new File([blob], nuevoNombre, { type: mimeOut, lastModified: Date.now() }));
                } catch (e) {
                    blob.name = nuevoNombre;
                    resolve(blob);
                }
            }, mimeOut, calidad);
        };

        img.onerror = function() {
            URL.revokeObjectURL(objectUrl);
            resolve(file);
        };

        img.src = objectUrl;
    });
}

function esImagen(archivo) {
    var ext = archivo.split('.').pop().toLowerCase();
    return ['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1;
}

function abrir_archivo(archivo) {
    var local = 'uploa_d/' + encodeURI(archivo);
    window.open(local, '_blank');
}

function mostrar_foto(imagen) {
    var local = 'uploa_d/' + encodeURI(imagen);
    Swal.fire({
        imageUrl: local
    });
}

function confirmar_borrar_foto(datos, onSuccess) {
    Swal.fire({
        title: 'Borrar Foto',
        text: 'Desea Borrar la Foto o Documento adjunto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
    }).then(function(result) {
        if (result.value) {
            $.post('inspeccion_fotos.php', datos, function(json) {
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        mytoast('error', json[0].pmsg, 3000);
                    }
                    if (json[0].pcode == 1) {
                        onSuccess(json);
                    }
                } else {
                    mytoast('error', 'Error inesperado', 3000);
                }
            }).fail(function(xhr, status, error) {
                mytoast('error', 'Error al procesar la solicitud', 3000);
            });
        }
    });
}

function insp_guardar_foto(arch, campo, cantidadFotos) {
    var puede_agregar_varias = <?= $puede_agregar_varias ? 'true' : 'false' ?>;
    var datos = { a: "g", cid: $("#cid").val(), pid: $("#pid").val(), arch: encodeURI(arch) };

    $.post('inspeccion_fotos.php', datos, function(json) {
        if (json.length > 0) {
            if (json[0].pcode == 0) {
                mytoast('error', json[0].pmsg, 3000);
            }
            if (json[0].pcode == 1) {
                if (puede_agregar_varias) {
                    window.cantidadFotosSubidasGlobal++;
                    thumb_agregar2(arch, campo, puede_agregar_varias);
                } else {
                    $('#' + campo).val(arch);
                    $('#files_' + campo).text('Guardado');
                    $('#lk' + campo).html(arch);
                    thumb_agregar2(arch, campo, puede_agregar_varias);
                }
            }
        } else {
            mytoast('error', 'Error inesperado', 3000);
        }
    }).done(function() {
        if (window.cantidadFotosSubidasGlobal == cantidadFotos && puede_agregar_varias) {
            var div = document.getElementById('variasfotosdiv');
            if (div && div.parentNode) {
                div.parentNode.removeChild(div);
                var nuevoBloque = <?php echo json_encode($nuevoBloqueParaVarias); ?>;
                $('.ins_foto_div_nuevo').append(nuevoBloque);
                window.cantidadFotosSubidasGlobal = 0;
            }
        }
    }).fail(function(xhr, status, error) {
        mytoast('error', 'Error al procesar la solicitud', 3000);
    });
}

function thumb_agregar(archivo) {
    if (archivo != '' && archivo != undefined) {
        if (esImagen(archivo)) {
            $("#insp_fotos_thumbs").append('<a href="#" onclick="mostrar_foto(\'' + archivo + '\'); return false;"><img class="img img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/' + archivo + '"></a> ');
        } else {
            $("#insp_fotos_thumbs").append('<a href="#" onclick="abrir_archivo(\'' + archivo + '\'); return false;" class="img-thumbnail mb-3 mr-3">' + archivo + '</a>');
        }
    }
}

function thumb_agregar2(archivo, campo, puede_agregar_varias) {
    if (archivo != '' && archivo != undefined) {
        var fotoId = "foto_" + campo + "_" + archivo.replace(/\W/g, "");
        var salida = '<div class="foto_item mb-2 mr-2" id="' + fotoId + '">';

        if (esImagen(archivo)) {
            salida += '<a href="#" onclick="mostrar_foto(\'' + archivo + '\'); return false;"><img class="img img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/' + archivo + '"></a> ';
        } else {
            salida += '<a href="#" onclick="abrir_archivo(\'' + archivo + '\'); return false;" class="img-thumbnail mb-3 mr-3">' + archivo + '</a>';
        }

        if (puede_agregar_varias) {
            $("#" + campo).closest('.ins_foto_div').append(salida + '<a id="del_' + campo + '" href="#" onclick="insp_borrar_foto(\'' + archivo + '\',\'del_' + campo + '\', \'' + fotoId + '\'); return false;" class="btn btn-outline-secondary ml-3"><i class="fa fa-eraser"></i> Borrar</a>');
        } else {
            $("#" + campo).closest('.ins_foto_div').html(salida + '<a id="del_' + campo + '" href="#" onclick="insp_borrar_foto(\'' + archivo + '\',\'del_' + campo + '\'); return false;" class="btn btn-outline-secondary ml-3"><i class="fa fa-eraser"></i> Borrar</a>');
        }
    }
}

function insp_borrar_foto(arch, campo, fotoId) {
    var datos = { a: "d", cid: $("#cid").val(), pid: $("#pid").val(), arch: encodeURI(arch) };
    var puede_agregar_varias = <?= $puede_agregar_varias ? 'true' : 'false' ?>;

    confirmar_borrar_foto(datos, function() {
        if (puede_agregar_varias) {
            $("#" + fotoId).remove();
        } else {
            $("#" + campo).closest('.ins_foto_div').html('Eliminado');
        }
    });
}

function borrar_fotodb(codid) {
    var datos = { a: "d", cid: $("#cid").val(), pid: $("#pid").val(), cod: codid };

    confirmar_borrar_foto(datos, function(json) {
        $(".foto_br" + codid).hide();
        mytoast('success', json[0].pmsg, 3000);
    });
}
</script>
