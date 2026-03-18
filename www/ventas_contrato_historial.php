<?php

require_once ('include/framework.php');

$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;

$result = sql_select("

SELECT 
    vc.id_contrato,
    vc.numero_contrato,
    vc.correlativo,
    vcd.tipo_contrato,
    vc.fecha_contrato,
    vc.estado,
    vc.creado_por,
    vc.anulado_por,
    vc.fecha_anulacion,

    vcd.id AS id_detalle

FROM ventas_contratos vc
LEFT JOIN ventas_contratos_detalle vcd 
    ON vcd.id_contrato = vc.id_contrato
WHERE vc.id_venta = $cid
ORDER BY vc.fecha_contrato DESC;

");

?>

<table class="table table-striped table-hover table-sm" style="width:100%">
<thead class="thead-dark">
<tr>
    <th>Contrato</th>
    <th>Tipo contrato</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Creado Por</th>
    <th>Anulado Por</th>
    <th>Fecha Anulación</th>
    <th width="120">Acción</th>
</tr>
</thead>
<tbody>

<?php

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

            $estado = ($row["estado"] == "ACTIVO") ? "Activo" : "Anulado";
            $tipo_contrato = ($row["tipo_contrato"] == 1) ? "Persona Jurídica" : "Persona Natural";
            $reimpresion = ($row["estado"] == "ACTIVO") ? "false" : "true";

            echo '<tr>

            <td>'.$row["numero_contrato"].'</td>


            <td>'.$tipo_contrato.'</td>

            <td>'.formato_fechahora_de_mysql($row["fecha_contrato"]).'</td>

            <td>'.$estado.'</td>

            <td>'.$row["creado_por"].'</td>
            <td>'.$row["anulado_por"].'</td>

            <td>'.formato_fechahora_de_mysql($row["fecha_anulacion"]).'</td>

            <td>
                <a href="#"
                onclick="descargar_contrato('.$cid.',\''.$row["id_detalle"].'\',\''.$row["tipo_contrato"].'\','.$reimpresion.'); return false;"
                class="btn btn-sm btn-primary">
                Descargar
                </a>
            </td>

            </tr>';
    }

}
else
{
    echo '<tr><td colspan="7" class="text-center">No hay contratos generados</td></tr>';
}

?>

</tbody>
</table>

<script>

</script>