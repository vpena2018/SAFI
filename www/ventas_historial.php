<?php
//FECHA                 DEVELOPER                 INCIDENTE
// 2026-11-04           Moises Gamez              INC-20261104 Se agrego scroll vertical a la tabla de historial de ventas y se ordeno de forma descendente por fecha.

if (isset($_REQUEST['cid'])) {
    $cid = intval($_REQUEST['cid']);
} else {
    exit;
}
if (isset($_REQUEST['pid'])) {
    $pid = intval($_REQUEST['pid']);
} else {
    exit;
}
if (isset($_REQUEST['a'])) {
    $accion = ($_REQUEST['a']);
} else {
    $accion = "";
}

require_once('include/framework.php');

$result = sql_select("SELECT ventas_historial_estado.nombre, ventas_historial_estado.fecha, ventas_historial_estado.observaciones
,ventas_estado.nombre AS elestado
,usuario.nombre AS elusuario
FROM ventas_historial_estado
LEFT OUTER JOIN ventas_estado ON (ventas_historial_estado.id_estado=ventas_estado.id)
LEFT OUTER JOIN usuario ON (ventas_historial_estado.id_usuario=usuario.id)
WHERE ventas_historial_estado.id_maestro=$cid 
order by ventas_historial_estado.id desc");

?>

<div class="row">
    <div class="table-responsive ">
        <table id="tabla" data-url="ventas_vehiculos_ver.php?a=1" data-page="0" class="table table-striped   table-sm"
            style="display: block;width:100%;height: 500px; overflow-y: scroll;">
            <thead class="">
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Estado</th>
                    <th>Descripción</th>
                    <th>Observaciones</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody id="tablabody">
                <?php

                if ($result != false) {
                    if ($result->num_rows > 0) {

                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>
                                <td>' . formato_fechahora_de_mysql($row["fecha"]) . '</td>
                                <td>' . $row["elestado"] . '</td>
                                <td>' . $row["nombre"] . '</td>
                                <td>' . $row["observaciones"] . '</td>
                                <td>' . $row["elusuario"] . '</td>
                               </tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">No se encontraron registros</td></tr>';
                    }
                }
                ?>
            </tbody>

        </table>

    </div>
</div>
<p>&nbsp;</p>
<p>&nbsp;</p>