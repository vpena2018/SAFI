<?php
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); } else	{exit ;}
if (isset($_REQUEST['pid'])) { $pid = intval($_REQUEST['pid']); } else	{exit ;}
if (isset($_REQUEST['a'])) { $accion = ($_REQUEST['a']); } else	{$accion ="" ;}

require_once ('include/framework.php');

$result = sql_select("SELECT inspeccion_historial_estado.nombre, inspeccion_historial_estado.fecha, inspeccion_historial_estado.observaciones
,inspeccion_estado.nombre AS elestado
,usuario.nombre AS elusuario
FROM inspeccion_historial_estado
LEFT OUTER JOIN inspeccion_estado ON (inspeccion_historial_estado.id_estado=inspeccion_estado.id)
LEFT OUTER JOIN usuario ON (inspeccion_historial_estado.id_usuario=usuario.id)
WHERE inspeccion_historial_estado.id_maestro=$cid 
order by inspeccion_historial_estado.id");
      
?>
 
<div class="row">
<div class="table-responsive ">
    <table id="tabla" data-url="inspeccion_ver.php?a=1" data-page="0" class="table table-striped   table-sm"  style="width:100%">
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
             
                if ($result!=false){
                    if ($result -> num_rows > 0) { 
                    
                        while ($row = $result -> fetch_assoc()) {
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
<p>&nbsp;</p>
<p>&nbsp;</p>
