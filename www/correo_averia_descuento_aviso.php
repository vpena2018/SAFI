<?php
require_once ('include/framework.php');

$app_enviar_email=true;//susttituir por app_enviar_email despues

if ($app_enviar_email==true) {
    require_once ('include/correo.php');

    $cuerpohtml="<h1>hola mundo</h1>";
    $cuerpo_sinhtml=strip_tags($cuerpohtml);

    enviar_correo('alexander.v211111@gmail.com','aprobacion descuento',$cuerpohtml,$cuerpo_sinhtml,true);
}

?>