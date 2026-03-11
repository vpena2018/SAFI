<?php

require_once ('include/framework.php');

$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;

$result = sql_select("

SELECT 
    vc.id_contrato,
    vc.numero_contrato,
    vc.correlativo,
    vc.fecha_contrato,
    vc.estado,
    vc.creado_por,
    vcd.id AS id_detalle

FROM ventas_contratos vc

LEFT JOIN ventas_contratos_detalle vcd 
    ON vcd.id_contrato = vc.id_contrato

WHERE vc.id_venta = $cid

GROUP BY vc.id_contrato

ORDER BY vc.fecha_contrato DESC

");

?>

<table class="table table-striped table-hover table-sm" style="width:100%">
<thead class="thead-dark">
<tr>
    <th>Contrato</th>
    <th>Correlativo</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Creado Por</th>
    <th width="120">Acción</th>
</tr>
</thead>
<tbody>

<?php

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $estado = ($row["estado"] == "ACTIVO") ? "Activo" : "Anulado";

        echo '<tr>

        <td>'.$row["numero_contrato"].'</td>

        <td>'.$row["correlativo"].'</td>

        <td>'.formato_fecha_de_mysql($row["fecha_contrato"]).'</td>

        <td>'.$estado.'</td>

        <td>'.$row["creado_por"].'</td>

        <td>
            <a href="#"
            onclick="generar_contrato_historial(\''.$row["id_detalle"].'\'); return false;"
            class="btn btn-sm btn-primary">
            Generar
            </a>
        </td>

        </tr>';
    }

}
else
{
    echo '<tr><td colspan="6" class="text-center">No hay contratos generados</td></tr>';
}

?>

</tbody>
</table>