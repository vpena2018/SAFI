<?php
require_once('include/framework.php');

function ocultarRowDiv($permiso = 1)
{
    $css = "";
    if (!tiene_permiso($permiso))
        $css .= "visibility : hidden";
    return $css;
}
function getHtmlMessageRestriction(): string
{
    return '<div class="card-body">
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p ><div class="text-center mb-4 login-titulo">
            <img src="img/logo.png" alt="" class="   mb-2 mt-2"  width="200" >

            </div></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            </div>
            ';
}

function getResumenServicios()
{
    return sql_select("SELECT 
                        count(*) AS total
                        ,SUM(if(id_estado<=2,1,0)) AS pendiente
                        ,SUM(if(id_estado=3,1,0)) AS aprobada
                        ,SUM(if(id_estado=4,1,0)) AS enproceso
                        ,SUM(if(id_estado=7,1,0)) AS paro
                        ,SUM(if(id_estado=8 or id_estado=9 ,1,0)) AS externo
                            FROM servicio
                            WHERE id_tienda=" . $_SESSION['tienda_id'] . " AND id_estado<20
  ");
}
function getResumenRepuestos()
{
    return sql_select("SELECT 
                        count(*) AS total
                        ,SUM(if(servicio_detalle.producto_tipo=3,1,0)) AS actividad
                        ,SUM(if(servicio_detalle.producto_tipo=2,1,0)) AS repuesto
                    
                        FROM servicio_detalle
                        LEFT OUTER JOIN servicio ON (servicio_detalle.id_servicio=servicio.id) 
                        WHERE servicio.id_tienda=" . $_SESSION['tienda_id'] . " AND servicio.id_estado<>20
                        and servicio_detalle.estado <2
  ");
}

function getResumenAverias()
{
    return sql_select("SELECT 
      count(*) AS total
      ,SUM(if(averia_detalle.producto_tipo=3,1,0)) AS actividad
      ,SUM(if(averia_detalle.producto_tipo=2,1,0)) AS repuesto
    
         FROM averia_detalle
         LEFT OUTER JOIN averia ON (averia_detalle.id_maestro=averia.id) 
         WHERE averia.id_tienda=" . $_SESSION['tienda_id'] . " AND averia.id_estado<>20
         and averia_detalle.estado <2
  
    ");
}

function getResumenEstados()
{
    return sql_select("SELECT 
                       t1.id_vendedor,
                       t3.usuario vendedor, 
                       t1.id_estado cod_estado, 
                       upper(t2.nombre) estado, COUNT(*) AS cantidad 
                        FROM ventas t1, ventas_estado t2, usuario t3
                        WHERE t1.id_estado = t2.id
                        AND t1.id_vendedor = t3.id
                        AND t1.id_estado IN(5, 7, 8, 9, 11, 17)
                        GROUP BY t1.id_vendedor,t3.usuario,t1.id_estado, t2.nombre
");
}

function returnTbResEstados()
{
    $html = "";
    $result = getResumenEstados();
    if ($result != false) {
        foreach ($result as $row) {
            $html .= "<tr>";
            $html .= "<td>" . $row["vendedor"] . "</td>";
            $html .= "<td>" . $row["estado"] . "</td>";
            $html .= "<td>" . $row["cantidad"] . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr>";
        $html .= "<td colspan='3' class='text-center'>";
        $html .= "No hay registros disponibles.";
        $html .= "</td>";
        $html .= "</tr>";
    }
    return $html;
}
?>