<?php
require_once ('include/framework.php');
pagina_permiso(176);

$accion = "";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; }

if ($accion=="1") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="No se encontraron Datos";
    $stud_arr[0]["pdata"] ="";
    $stud_arr[0]["pmas"] =0;

    $filtros = " and ventas.id_estado = 11";

    if (isset($_REQUEST['vendedor'])) {
        $tmpval = sanear_int($_REQUEST['vendedor']);
        if (!es_nulo($tmpval)) {
            $filtros .= " and ventas.id_vendedor = ".GetSQLValue($tmpval,'int');
        }
    }

    $result = sql_select("SELECT ventas.id
        ,ventas.numero
        ,ventas.fecha
        ,ventas.fecha_negociacion
        ,producto.codigo_alterno AS codvehiculo
        ,producto.nombre AS vehiculo
        ,ifnull(vendedor.nombre,'Sin vendedor') AS vendedor
        ,DATEDIFF(now(), ifnull(ventas.fecha_negociacion, ventas.fecha)) AS dias_negociacion
    FROM ventas
    LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)
    LEFT OUTER JOIN usuario vendedor ON (ventas.id_vendedor=vendedor.id)
    WHERE 1=1
    and ventas.tipo_ventas_reparacion = 2
    $filtros
    ORDER BY vendedor.nombre, ventas.fecha_negociacion desc, ventas.id desc");

    if ($result!=false) {
        if ($result->num_rows > 0) {
            $resumen = array();
            $datos = '<table id="tabla_dash_negociacion" class="table table-striped table-hover table-sm" style="width:100%">'
            .'<thead class="thead-dark">'
            .'<tr>'
            .'<th>Vendedor</th>'
            .'<th>No.</th>'
            .'<th>Fecha</th>'
            .'<th>Fecha Negociacion</th>'
            .'<th>Dias Negociacion</th>'
            .'<th>Vehiculo</th>'
            .'</tr>'
            .'</thead><tbody style="font-size: 12px;">';

            while ($row = $result->fetch_assoc()) {
                $vendedor = $row['vendedor'];
                if (!isset($resumen[$vendedor])) {
                    $resumen[$vendedor] = 0;
                }
                $resumen[$vendedor]++;

                $datos .= '<tr>'
                .'<td>'.$row['vendedor'].'</td>'
                .'<td><a href="#" onclick="abrir_venta_dash(\''.$row['id'].'\'); return false;" class="btn btn-sm btn-secondary">'.$row['numero'].'</a></td>'
                .'<td>'.formato_fecha_de_mysql($row['fecha']).'</td>'
                .'<td>'.formato_fechahora_de_mysql($row['fecha_negociacion']).'</td>'
                .'<td>'.formato_numero($row['dias_negociacion'],0).'</td>'
                .'<td>'.$row['codvehiculo'].' '.$row['vehiculo'].'</td>'
                .'</tr>';
            }

            $datos .= '</tbody></table>';

            $tarjetas = '<div class="row mb-3">';
            foreach ($resumen as $nomVendedor => $totalVend) {
                $tarjetas .= '<div class="col-12 col-sm-6 col-lg-3">'
                .'<div class="info-box mb-2">'
                .'<span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-tie" style="color:white"></i></span>'
                .'<div class="info-box-content">'
                .'<span class="info-box-text">'.$nomVendedor.'</span>'
                .'<span class="info-box-number">'.formato_numero($totalVend,0).'</span>'
                .'</div></div></div>';
            }
            $tarjetas .= '</div>';

            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ="";
            $stud_arr[0]["pdata"] = $tarjetas.$datos;
            $stud_arr[0]["pmas"] =0;
        }
    }

    salida_json($stud_arr);
    exit;
}
?>

<div class="card-body">
    <form id="forma_dash_negociacion" name="forma_dash_negociacion">
        <fieldset id="fs_forma">
            <div class="row">
                <div class="col-sm">
                    <?php
                    echo campo("vendedor","Vendedor",'select2',valores_combobox_db('usuario','','nombre',' where activo=1 and grupo_id=18 ','','Todos'),' ',' onkeypress="buscarfiltro(event,\'btn-filtro-dash\');"');
                    ?>
                </div>
                <div class="col-sm">
                    <a id="btn-filtro-dash" href="#" onclick="procesar_tabla_datatable('tablaver_dash','tabla_dash_negociacion','dashboard_vendedor_negociacion.php?a=1','Dashboard Vendedor Negociacion','forma_dash_negociacion'); return false;" class="btn btn-info mr-2 mb-2"><i class="fa fa-search"></i> Actualizar</a>
                </div>
            </div>
        </fieldset>
    </form>

    <div id="tablaver_dash" class="table-responsive"></div>

    <div class="botones_accion d-print-none">
        <div id="cargando" class="oculto" align="center"><img src="img/load.gif"/></div>
    </div>
</div>

<script>
function abrir_venta_dash(codigo){
    modalwindow2('Registro Venta de Vehiculo','ventas_mant.php?a=v&r=N&cid='+codigo);
}
</script>
