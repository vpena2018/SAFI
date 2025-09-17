<?php
require_once ('include/framework.php');

$solicitud=true;


$app_enviar_email=true;
$id_averia=$cid;
$titulo="<h2 style='color:#004080;'>Solicitud de aprobación de descuento</h2>";
$subtitulo="<p>Se ha generado una solicitud de descuento que requiere aprobación:</p>";
$estiloTabla="<tr style='background-color:#004080; color:#fff; text-align:left;'>";
$footer="    <p>Por favor ingrese al sistema para aprobar o rechazar esta solicitud:</p>
    <p>
        <a href='https://flota.inglosa.hn/' 
           style='display:inline-block;padding:12px 24px;background-color:#004080;
                  color:#fff;text-decoration:none;font-weight:bold;
                  border-radius:5px;'>
            Ingresar al SAFI
        </a>
    </p>";

if($accion=="aprobar")
{
    $id_averia_detalle=$cid;
    $titulo="<h2 style='color:#28a745;'>Descuento Aprobado</h2>";
    $subtitulo="<p></p>";
    $estiloTabla="<tr style='background-color:#28a745; color:#fff; text-align:left;'>";
    $footer="";
    $solicitud=false;
}else if($accion=="anular")
{
    $id_averia_detalle=$cid;
    $titulo="<h2 style='color:#dc3545;'>Descuento Denegado</h2>";
    $subtitulo="<p></p>";
    $estiloTabla="<tr style='background-color:#dc3545; color:#fff; text-align:left;'>";
    $footer="";
    $solicitud=false;
}else{
    $id_averia_detalle=$result;
}


if ($app_enviar_email==true) {

    $correo_servicio_result = sql_select("SELECT ave.id num_averia,prod.nombre vehiculo, cliente.nombre cliente,(ave_detalle.cantidad* ave_detalle.precio_costo) valor,  ave.fecha, tienda.nombre tienda FROM averia ave
INNER JOIN tienda ON tienda.id=ave.id_tienda
INNER JOIN entidad cliente ON cliente.id=ave.cliente_id
INNER JOIN producto prod ON prod.id= ave.id_producto
INNER JOIN averia_detalle ave_detalle ON ave.id=ave_detalle.id_maestro
WHERE ave_detalle.id=$id_averia_detalle;");


            if ($correo_servicio_result!=false){
                if ($correo_servicio_result -> num_rows > 0) { 
                    $correo_row = $correo_servicio_result -> fetch_assoc(); 

                    $fecha=formato_fecha_de_mysql($correo_row['fecha']);


                    $cuerpohtml = "
<html>
<body style='font-family: Arial, sans-serif; font-size:14px; color:#333;'>
    {$titulo}
    {$subtitulo}
    <table cellpadding='8' cellspacing='0' width='100%' 
           style='border-collapse:collapse; border:1px solid #ccc;'>
        {$estiloTabla}
            <th>N° Avería</th>
            <td>{$correo_row['num_averia']}</td>
        </tr>
        <tr style='background-color:#f9f9f9;'>
            <th style='width:150px; text-align:left; color:#004080;'>Vehículo</th>
            <td>{$correo_row['vehiculo']}</td>
        </tr>
        <tr>
            <th style='text-align:left; color:#004080;'>Cliente</th>
            <td>{$correo_row['cliente']}</td>
        </tr>
        <tr style='background-color:#f9f9f9;'>
            <th style='text-align:left; color:#004080;'>Valor</th>
            <td>L ".number_format($correo_row['valor'],2)."</td>
        </tr>
        <tr>
            <th style='text-align:left; color:#004080;'>Fecha</th>
            <td>{$fecha}</td>
        </tr>
        <tr style='background-color:#f9f9f9;'>
            <th style='text-align:left; color:#004080;'>Tienda</th>
            <td>{$correo_row['tienda']}</td>
        </tr>
    </table>
    
    <br>
    {$footer}
</body>
</html>
";



                $cuerpo_sinhtml = strip_tags($cuerpohtml);

                require_once ('include/correo.php');



                enviar_correo_dev(
                    'alexander.v211111@gmail.com',
                    'Descuento Averias',
                    $cuerpohtml,
                    $cuerpo_sinhtml
                );


        }
    }






    

    /*require_once ('include/correo.php');
    $cuerpohtml="<h1>hola mundo</h1>";
    $cuerpo_sinhtml=strip_tags($cuerpohtml);
    enviar_correo('alexander.v211111@gmail.com','aprobacion descuento',$cuerpohtml,$cuerpo_sinhtml,true);*/



}

?>