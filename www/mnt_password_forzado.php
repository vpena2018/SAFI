<?php
require_once ('include/framework.php');

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else { $accion = "v"; }

if ($accion=="g") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] = "ERROR";

    $verror = "";
    $npword = isset($_REQUEST["npword"]) ? trim($_REQUEST["npword"]) : "";
    $npword2 = isset($_REQUEST["npword2"]) ? trim($_REQUEST["npword2"]) : "";

    $verror .= validar("Contrasena", $npword, "text", true);
    $verror .= validar("Confirmar Contrasena", $npword2, "text", true);
    if ($npword !== $npword2) {
        $verror .= "La contrasena no coincide con la confirmacion";
    } else {
        $verror .= validar_politica_password($npword);
    }

    if ($verror=="") {
        $sql = "UPDATE usuario
                SET clave=".GetSQLValue(password_hash($npword, PASSWORD_BCRYPT), "text")."
                WHERE id=".$_SESSION['usuario_id']."
                LIMIT 1";
        $result = sql_update($sql);

        if ($result!=false) {
            $_SESSION["force_pwd_change"] = 0;
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] = "Contrasena actualizada";
        } else {
            $stud_arr[0]["pmsg"] = "No se pudo actualizar la contrasena";
        }
    } else {
        $stud_arr[0]["pmsg"] = $verror;
    }

    salida_json($stud_arr);
    exit;
}
?>

<div class="card-body">
    <div class="maxancho400 mx-auto">
        <div class="alert alert-warning">
            Debe cambiar su contrasena para continuar.
        </div>

        <form id="forma_forzar_pwd" name="forma_forzar_pwd">
            <?php
                echo campo("npword","Nueva Contrasena",'password','','','');
                echo campo("npword2","Confirmar Contrasena",'password','','','');
            ?>

            <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top">
                <a href="#" onclick="guardar_password_forzado(); return false;" class="btn btn-primary mr-2 mb-2 xfrm">
                    <i class="fa fa-check"></i> Guardar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function guardar_password_forzado() {
    var datos = $("#forma_forzar_pwd").serialize();
    $.post('mnt_password_forzado.php?a=g', datos, function(json) {
        if (json.length > 0) {
            if (json[0].pcode == 1) {
                mytoast('success', json[0].pmsg, 2000);
                setTimeout(function() {
                    if (typeof salida === 'function') {
                        salida = function () { return undefined; };
                    }
                    window.onbeforeunload = null;
                    window.location.replace('index.php?a=logout');
                }, 900);
            } else {
                mytoast('error', json[0].pmsg, 3500);
            }
        } else {
            mytoast('error', 'Respuesta invalida del servidor', 3000);
        }
    }).fail(function() {
        mytoast('error', 'Error al guardar la contrasena', 3000);
    });
}
</script>
