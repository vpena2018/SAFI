<?php
require_once ('include/framework.php');

$app_enviar_email=true;//susttituir por app_enviar_email despues

if ($app_enviar_email==true) {
    require_once ('include/correo.php');

    echo 'email';
}

?>