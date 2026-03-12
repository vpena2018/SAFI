<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else { exit; }

require_once ('include/framework.php');

$result = sql_select("SELECT orden_traslado_historial_estado.nombre
,orden_traslado_historial_estado.fecha
,orden_traslado_historial_estado.observaciones
,orden_traslado_estado.nombre AS elestado
,usuario.nombre AS elusuario
FROM orden_traslado_historial_estado
LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado_historial_estado.id_estado=orden_traslado_estado.id)
LEFT OUTER JOIN usuario ON (orden_traslado_historial_estado.id_usuario=usuario.id)
WHERE orden_traslado_historial_estado.id_maestro=$cid
ORDER BY orden_traslado_historial_estado.id");
?>

<div class="row">
<div class="table-responsive ">
    <table id="tabla_traslado_historial" class="table table-striped table-sm" style="width:100%">
        <thead class="">
            <tr>
                <th>Fecha / Hora</th>
                <th>Estado</th>
                <th>Descripcion</th>
                <th>Observaciones</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody id="tablabody_traslado_historial">
            <?php
                if ($result!=false){
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>
                                <td>'.formato_fechahora_de_mysql($row["fecha"]).'</td>
                                <td>'.$row["elestado"].'</td>
                                <td>'.$row["nombre"].'</td>
                                <td>'.$row["observaciones"].'</td>
                                <td>'.$row["elusuario"].'</td>
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
