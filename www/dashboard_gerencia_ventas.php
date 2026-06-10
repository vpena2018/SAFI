<?php
require_once ('include/framework.php');
pagina_permiso(166);

$accion = "";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; }

// ── Acción AJAX: retorna los datos del resumen gerencial ──────────────────────
if ($accion == "1") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"]  = "No se encontraron Datos";
    $stud_arr[0]["pdata"] = "";
    $stud_arr[0]["pmas"]  = 0;

    // Filtro de tienda opcional
    $filtro_tienda = "";
    if (isset($_REQUEST['tienda'])) {
        $tmpval = sanear_int($_REQUEST['tienda']);
        if (!es_nulo($tmpval)) {
            $filtro_tienda = " AND ventas.id_tienda = " . GetSQLValue($tmpval, 'int');
        }
    }

    // ── 0. Totales activos por tipo (ventas=2, reparación=1) ─────────────────
    $res_tipo = sql_select("SELECT
        ventas.tipo_ventas_reparacion
        ,COUNT(*) AS total
    FROM ventas
    WHERE (
        (ventas.tipo_ventas_reparacion = 2 AND ventas.id_estado IN (5, 11))
        OR
        (ventas.tipo_ventas_reparacion = 1 AND (ventas.id_estado = 0 OR ventas.id_estado IS NULL OR ventas.id_estado = 11 or ventas.id_estado = 99))
    )
      $filtro_tienda
    GROUP BY ventas.tipo_ventas_reparacion");

    $total_tipo_ventas     = 0;
    $total_tipo_reparacion = 0;
    if ($res_tipo && $res_tipo->num_rows > 0) {
        while ($r = $res_tipo->fetch_assoc()) {
            if (intval($r['tipo_ventas_reparacion']) == 2) $total_tipo_ventas     = intval($r['total']);
            if (intval($r['tipo_ventas_reparacion']) == 1) $total_tipo_reparacion = intval($r['total']);
        }
    }

    // ── 1. Disponibles (estados < 20, excepto negociación 11) ─────────────────
    $res_disp = sql_select("SELECT
        IFNULL(vendedor.nombre, 'Sin vendedor') AS vendedor
        ,COUNT(*) AS total
    FROM ventas
    LEFT OUTER JOIN usuario vendedor ON (ventas.id_vendedor = vendedor.id)
    WHERE ventas.tipo_ventas_reparacion = 2
      AND ventas.id_estado = 5
      $filtro_tienda
    GROUP BY vendedor.id, vendedor.nombre
    ORDER BY vendedor.nombre");

    // ── 1b. Disponibles por tienda (estado 5) ───────────────────────────────────
    $res_disp_tienda = sql_select("SELECT
        IFNULL(tienda.nombre, 'Sin tienda') AS tienda
        ,COUNT(*) AS total
    FROM ventas
    LEFT OUTER JOIN tienda ON (ventas.id_tienda = tienda.id)
    WHERE ventas.tipo_ventas_reparacion = 2
      AND ventas.id_estado = 5
      $filtro_tienda
    GROUP BY tienda.id, tienda.nombre
    ORDER BY tienda.nombre");

    // ── 2. En negociación (estado 11) ─────────────────────────────────────────
    $res_neg = sql_select("SELECT
        IFNULL(vendedor.nombre, 'Sin vendedor') AS vendedor
        ,COUNT(*) AS total
    FROM ventas
    LEFT OUTER JOIN usuario vendedor ON (ventas.id_vendedor = vendedor.id)
    WHERE ventas.tipo_ventas_reparacion = 2
      AND ventas.id_estado = 11
      $filtro_tienda
    GROUP BY vendedor.id, vendedor.nombre
    ORDER BY vendedor.nombre");

    // ── 3. Vendidos este mes (estado 20, fecha_vendido mes actual) ────────────
    $res_vend = sql_select("SELECT
        IFNULL(vendedor.nombre, 'Sin vendedor') AS vendedor
        ,SUM(CASE WHEN DATE(ventas.fecha_vendido) = CURDATE() THEN 1 ELSE 0 END) AS ventas_hoy
        ,SUM(CASE WHEN YEARWEEK(ventas.fecha_vendido,1) = YEARWEEK(CURDATE(),1) THEN 1 ELSE 0 END) AS ventas_semana
        ,COUNT(*) AS ventas_mes
    FROM ventas
    LEFT OUTER JOIN usuario vendedor ON (ventas.id_vendedor = vendedor.id)
    WHERE ventas.tipo_ventas_reparacion = 2
      AND ventas.id_estado = 20
      AND ventas.fecha_vendido IS NOT NULL
      AND YEAR(ventas.fecha_vendido)  = YEAR(CURDATE())
      AND MONTH(ventas.fecha_vendido) = MONTH(CURDATE())
      $filtro_tienda
    GROUP BY vendedor.id, vendedor.nombre
    ORDER BY ventas_mes DESC, vendedor.nombre");

    // Indexar resultados por vendedor
    $map_disp = [];
    if ($res_disp && $res_disp->num_rows > 0) {
        while ($r = $res_disp->fetch_assoc()) {
            $map_disp[$r['vendedor']] = intval($r['total']);
        }
    }

    $map_neg = [];
    if ($res_neg && $res_neg->num_rows > 0) {
        while ($r = $res_neg->fetch_assoc()) {
            $map_neg[$r['vendedor']] = intval($r['total']);
        }
    }

    $map_vend = [];
    $total_hoy = $total_semana = $total_mes_vend = 0;
    if ($res_vend && $res_vend->num_rows > 0) {
        while ($r = $res_vend->fetch_assoc()) {
            $map_vend[$r['vendedor']] = [
                'hoy'    => intval($r['ventas_hoy']),
                'semana' => intval($r['ventas_semana']),
                'mes'    => intval($r['ventas_mes']),
            ];
            $total_hoy        += intval($r['ventas_hoy']);
            $total_semana     += intval($r['ventas_semana']);
            $total_mes_vend   += intval($r['ventas_mes']);
        }
    }

    $map_disp_tienda = [];
    $total_disp_global = 0;
    if ($res_disp_tienda && $res_disp_tienda->num_rows > 0) {
        while ($r = $res_disp_tienda->fetch_assoc()) {
            $map_disp_tienda[$r['tienda']] = intval($r['total']);
            $total_disp_global += intval($r['total']);
        }
    }

    // Unión de vendedores
    $vendedores = array_unique(array_merge(
        array_keys($map_disp),
        array_keys($map_neg),
        array_keys($map_vend)
    ));
    sort($vendedores);

    if (count($vendedores) > 0) {

        // Totales globales
        $total_disp    = array_sum($map_disp);
        $total_neg     = array_sum($map_neg);

        // ── Tarjetas Ventas / Reparación ───────────────────────────────────────
        $html  = '<div class="row mb-3">';
        $html .= '<div class="col-12 col-sm-6 col-lg-3">'
            . '<div class="info-box mb-2">'
            . '<span class="info-box-icon bg-teal elevation-1" style="background-color:#17a2b8"><i class="fas fa-car" style="color:white"></i></span>'
            . '<div class="info-box-content">'
            . '<span class="info-box-text">Modulo Ventas</span>'
            . '<span class="info-box-number">' . formato_numero($total_tipo_ventas, 0) . '</span>'
            . '</div></div></div>';

        $html .= '<div class="col-12 col-sm-6 col-lg-3">'
            . '<div class="info-box mb-2">'
            . '<span class="info-box-icon bg-danger elevation-1"><i class="fas fa-tools" style="color:white"></i></span>'
            . '<div class="info-box-content">'
            . '<span class="info-box-text">Modulo Reparaciones</span>'
            . '<span class="info-box-number">' . formato_numero($total_tipo_reparacion, 0) . '</span>'
            . '</div></div></div>';

        $html .= '</div>';

        // ── Tarjetas de totales ────────────────────────────────────────────────
        $html .= '<div class="row mb-3">';
        $html .= '<div class="col-12 col-sm-6 col-lg-3">'
            . '<div class="info-box mb-2" style="cursor:pointer" onclick="ver_detalle_negociacion()">'
            . '<span class="info-box-icon bg-info elevation-1"><i class="fas fa-handshake" style="color:white"></i></span>'
            . '<div class="info-box-content">'
            . '<span class="info-box-text">En Negociaci&oacute;n <i class="fas fa-search-plus fa-xs"></i></span>'
            . '<span class="info-box-number">' . formato_numero($total_neg, 0) . '</span>'
            . '</div></div></div>';

        $html .= '<div class="col-12 col-sm-6 col-lg-3">'
            . '<div class="info-box mb-2">'
            . '<span class="info-box-icon bg-primary elevation-1"><i class="fas fa-calendar-day" style="color:white"></i></span>'
            . '<div class="info-box-content">'
            . '<span class="info-box-text">Vendidos Hoy</span>'
            . '<span class="info-box-number">' . formato_numero($total_hoy, 0) . '</span>'
            . '</div></div></div>';

        $html .= '<div class="col-12 col-sm-6 col-lg-3">'
            . '<div class="info-box mb-2">'
            . '<span class="info-box-icon bg-warning elevation-1"><i class="fas fa-calendar-week" style="color:white"></i></span>'
            . '<div class="info-box-content">'
            . '<span class="info-box-text">Vendidos Esta Semana</span>'
            . '<span class="info-box-number">' . formato_numero($total_semana, 0) . '</span>'
            . '</div></div></div>';

        $html .= '<div class="col-12 col-sm-6 col-lg-3">'
            . '<div class="info-box mb-2">'
            . '<span class="info-box-icon bg-success elevation-1"><i class="fas fa-trophy" style="color:white"></i></span>'
            . '<div class="info-box-content">'
            . '<span class="info-box-text">Vendidos Este Mes</span>'
            . '<span class="info-box-number">' . formato_numero($total_mes_vend, 0) . '</span>'
            . '</div></div></div>';

        $html .= '</div>'; // fin row tarjetas

        // ── Tarjetas disponibles por tienda ───────────────────────────────────
        if (count($map_disp_tienda) > 0) {
            $html .= '<h6 class="mb-2"><i class="fas fa-car mr-1"></i> Disponibles por Tienda</h6>';
            $html .= '<div class="row mb-3">';
            foreach ($map_disp_tienda as $nombreTienda => $cantTienda) {
                $html .= '<div class="col-12 col-sm-6 col-lg-3">'
                    . '<div class="info-box mb-2">'
                    . '<span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-store" style="color:white"></i></span>'
                    . '<div class="info-box-content">'
                    . '<span class="info-box-text">' . htmlspecialchars($nombreTienda) . '</span>'
                    . '<span class="info-box-number">' . formato_numero($cantTienda, 0) . '</span>'
                    . '</div></div></div>';
            }
            // Tarjeta total
            $html .= '<div class="col-12 col-sm-6 col-lg-3">'
                . '<div class="info-box mb-2">'
                . '<span class="info-box-icon bg-dark elevation-1"><i class="fas fa-car" style="color:white"></i></span>'
                . '<div class="info-box-content">'
                . '<span class="info-box-text">Total Disponibles</span>'
                . '<span class="info-box-number">' . formato_numero($total_disp_global, 0) . '</span>'
                . '</div></div></div>';
            $html .= '</div>';
        }

        // ── Tabla por vendedor ─────────────────────────────────────────────────
        $html .= '<div class="table-responsive">'
            . '<table class="table table-striped table-hover table-sm table-bordered">'
            . '<thead class="thead-dark">'
            . '<tr>'
            . '<th>Vendedor</th>'
            . '<th class="text-center">En Negociaci&oacute;n</th>'
            . '<th class="text-center">Vendidos Hoy</th>'
            . '<th class="text-center">Vendidos Semana</th>'
            . '<th class="text-center">Vendidos Mes</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody style="font-size:12px;">';

        $tot_n = $tot_h = $tot_s = $tot_m = 0;

        foreach ($vendedores as $vend) {
            $n = isset($map_neg[$vend])  ? $map_neg[$vend]  : 0;
            $h = isset($map_vend[$vend]) ? $map_vend[$vend]['hoy']    : 0;
            $s = isset($map_vend[$vend]) ? $map_vend[$vend]['semana'] : 0;
            $m = isset($map_vend[$vend]) ? $map_vend[$vend]['mes']    : 0;
            $tot_n += $n; $tot_h += $h; $tot_s += $s; $tot_m += $m;

            $html .= '<tr>'
                . '<td>' . htmlspecialchars($vend) . '</td>'
                . '<td class="text-center">' . formato_numero($n, 0) . '</td>'
                . '<td class="text-center">' . ($h > 0 ? '<span class="badge badge-success">' . formato_numero($h, 0) . '</span>' : '0') . '</td>'
                . '<td class="text-center">' . formato_numero($s, 0) . '</td>'
                . '<td class="text-center font-weight-bold">' . formato_numero($m, 0) . '</td>'
                . '</tr>';
        }

        // Fila de totales
        $html .= '<tr class="font-weight-bold bg-dark text-white">'
            . '<td>TOTAL</td>'
            . '<td class="text-center">' . formato_numero($tot_n, 0) . '</td>'
            . '<td class="text-center">' . formato_numero($tot_h, 0) . '</td>'
            . '<td class="text-center">' . formato_numero($tot_s, 0) . '</td>'
            . '<td class="text-center">' . formato_numero($tot_m, 0) . '</td>'
            . '</tr>';

        $html .= '</tbody></table></div>';

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"]  = "";
        $stud_arr[0]["pdata"] = $html;
        $stud_arr[0]["pmas"]  = 0;
    }

    salida_json($stud_arr);
    exit;
}

// ── Acción a=2: detalle de vehículos en negociación (HTML puro para modal) ────
if ($accion == "2") {
    $filtro_tienda = "";
    if (isset($_REQUEST['tienda'])) {
        $tmpval = sanear_int($_REQUEST['tienda']);
        if (!es_nulo($tmpval)) {
            $filtro_tienda = " AND ventas.id_tienda = " . GetSQLValue($tmpval, 'int');
        }
    }

    $result = sql_select("SELECT ventas.id
        ,ventas.fecha
        ,ventas.fecha_negociacion
        ,ventas.tipo_ventas_reparacion
        ,producto.codigo_alterno AS codvehiculo
        ,producto.nombre AS vehiculo
        ,IFNULL(vendedor.nombre,'Sin vendedor') AS vendedor
        ,IFNULL(tienda.nombre,'Sin tienda') AS latienda
        ,DATEDIFF(NOW(), IFNULL(ventas.fecha_negociacion, ventas.fecha)) AS dias_negociacion
    FROM ventas
    LEFT OUTER JOIN producto ON (ventas.id_producto = producto.id)
    LEFT OUTER JOIN usuario vendedor ON (ventas.id_vendedor = vendedor.id)
    LEFT OUTER JOIN tienda ON (ventas.id_tienda = tienda.id)
    WHERE ventas.id_estado = 11
      $filtro_tienda
    ORDER BY vendedor.nombre, ventas.fecha_negociacion DESC, ventas.id DESC");

    echo '<div class="card-body p-2">';
    if ($result && $result->num_rows > 0) {
        echo '<table class="table table-striped table-hover table-sm" style="width:100%">'
            .'<thead class="thead-dark">'
            .'<tr>'
            .'<th>Vendedor</th>'
            .'<th>Tienda</th>'
            .'<th>M&oacute;dulo</th>'
            .'<th>Fecha</th>'
            .'<th>Fecha Negociaci&oacute;n</th>'
            .'<th class="text-center">D&iacute;as</th>'
            .'<th>Veh&iacute;culo</th>'
            .'</tr>'
            .'</thead><tbody style="font-size:12px;">';
        while ($row = $result->fetch_assoc()) {
            $dias = intval($row['dias_negociacion']);
            $badge = $dias >= 10 ? 'badge-danger' : ($dias >= 5 ? 'badge-warning' : 'badge-secondary');
            $modulo = intval($row['tipo_ventas_reparacion']) == 1 ? '<span class="badge badge-danger">Reparaci&oacute;n</span>' : '<span class="badge badge-info">Ventas</span>';
            echo '<tr>'
                .'<td>'.htmlspecialchars($row['vendedor']).'</td>'
                .'<td>'.htmlspecialchars($row['latienda']).'</td>'
                .'<td>'.$modulo.'</td>'
                .'<td>'.formato_fecha_de_mysql($row['fecha']).'</td>'
                .'<td>'.formato_fechahora_de_mysql($row['fecha_negociacion']).'</td>'
                .'<td class="text-center"><span class="badge '.$badge.'">'.formato_numero($dias,0).'</span></td>'
                .'<td>'.htmlspecialchars($row['codvehiculo'].' '.$row['vehiculo']).'</td>'
                .'</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning">No hay veh&iacute;culos en negociaci&oacute;n.</div>';
    }
    echo '</div>';
    exit;
}
?>

<div class="card-body">
    <form id="forma_gerencia" name="forma_gerencia">
        <fieldset id="fs_forma">
            <div class="row align-items-end mb-2">
                <div class="col-sm-4">
                    <?php
                    echo campo("tienda", "Tienda", 'select2',
                        valores_combobox_db('tienda', '', 'nombre', '', '', 'Todas'),
                        ' ', ' onkeypress="buscarfiltro(event,\'btn-filtro-gerencia\');"');
                    ?>
                </div>
                <div class="col-sm-4">
                    <a id="btn-filtro-gerencia" href="#"
                       onclick="cargar_dashboard_gerencia(); return false;"
                       class="btn btn-info mr-2 mb-2">
                        <i class="fa fa-sync-alt"></i> Actualizar
                    </a>
                </div>
            </div>
        </fieldset>
    </form>

    <div id="panel_gerencia_ventas">
        <div align="center"><img src="img/load.gif"/></div>
    </div>

    <div class="botones_accion d-print-none">
        <div id="cargando" class="oculto" align="center"><img src="img/load.gif"/></div>
    </div>
</div>

<script>
function cargar_dashboard_gerencia() {
    var params = $('#forma_gerencia').serialize();
    $('#panel_gerencia_ventas').html('<div align="center"><img src="img/load.gif"/></div>');
    $.post('dashboard_gerencia_ventas.php?a=1', params, function(json) {
        if (json.length > 0) {
            if (json[0].pcode == 1) {
                $('#panel_gerencia_ventas').html(json[0].pdata);
            } else {
                $('#panel_gerencia_ventas').html('<div class="alert alert-warning">' + json[0].pmsg + '</div>');
            }
        }
    }).fail(function() {
        $('#panel_gerencia_ventas').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
    });
}

function ver_detalle_negociacion() {
    var params = $('#forma_gerencia').serialize();
    modalwindow2('Veh&iacute;culos en Negociaci&oacute;n', 'dashboard_gerencia_ventas.php?a=2', params);
}

function abrir_venta_gerencia(codigo) {
    modalwindow2('Registro Venta de Veh&iacute;culo', 'ventas_mant.php?a=v&r=N&cid=' + codigo);
}

$(document).ready(function() {
    cargar_dashboard_gerencia();
});
</script>
