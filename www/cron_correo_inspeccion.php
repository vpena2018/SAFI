<?php
// ============================================================
// cron_correo_inspeccion.php
// 
// Este archivo se ejecuta cada 1-2 minutos via cron job.
// Busca correos pendientes de la tabla cola_correo_inspeccion
// y los procesa (genera PDF + envía correo).
//
// Configuración del cron job en el servidor (cPanel o SSH):
//   */2 * * * * php /ruta/completa/al/proyecto/cron_correo_inspeccion.php
// ============================================================

// Evitar que se ejecute desde el navegador directamente
if (php_sapi_name() !== 'cli') {
    // Permitir también llamada via URL protegida con token (útil si no tienes cron)
    if (!isset($_GET['token']) || $_GET['token'] !== 'e1f2a1b857abfe33b6f63c05159e0c8b53818d523fd674af14342fbcaf8285bb') {
        http_response_code(403);
        die('Acceso denegado');
    }
}

// Tiempo máximo de ejecución: 5 minutos
set_time_limit(300);

define('SAFI_CRON_CONTEXT', true);
require_once(__DIR__ . '/include/framework_cron.php');

$log_file = __DIR__ . '/logs/cron_correo_inspeccion.log';

// Crear carpeta de logs si no existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function escribir_log($mensaje) {
    global $log_file;
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
    file_put_contents($log_file, $linea, FILE_APPEND);
}

function leer_png_como_dataurl($rutaArchivo) {
    if (!is_file($rutaArchivo)) {
        return '';
    }
    $bin = @file_get_contents($rutaArchivo);
    if ($bin === false || strlen($bin) < 64) {
        return '';
    }
    return 'data:image/png;base64,' . base64_encode($bin);
}

function limpiar_tmp_inspeccion($idInspeccion) {
    $tmpDir = app_dir . 'reportes/tmp_inspeccion/' . intval($idInspeccion) . '/';
    if (!is_dir($tmpDir)) {
        return;
    }
    foreach (glob($tmpDir . '*') as $f) {
        if (is_file($f)) {
            @unlink($f);
        }
    }
    @rmdir($tmpDir);
}

escribir_log('---- Inicio cron ----');

// Buscar hasta 5 correos pendientes (para no sobrecargar el servidor)
$pendientes = sql_select("
    SELECT id, id_inspeccion 
    FROM cola_correo_inspeccion 
    WHERE estado = 0 
      AND intentos < 3
    ORDER BY fecha_creado ASC 
    LIMIT 5
");

if ($pendientes == false || $pendientes->num_rows == 0) {
    escribir_log('Sin correos pendientes.');
    exit;
}

escribir_log('Correos a procesar: ' . $pendientes->num_rows);

require_once(__DIR__ . '/include/correo.php');

while ($item = $pendientes->fetch_assoc()) {

    $cola_id      = $item['id'];
    $elcodigo     = $item['id_inspeccion']; // nombre de variable que usa inspeccion_pdf.php

    escribir_log("Procesando cola_id=$cola_id | id_inspeccion=$elcodigo");

    // Marcar como "en proceso" para evitar que otro cron lo tome al mismo tiempo
    sql_update("UPDATE cola_correo_inspeccion SET intentos = intentos + 1 WHERE id = $cola_id");

    try {

        // Obtener datos del correo
        $correo_result = sql_select("
            SELECT inspeccion.id, inspeccion.numero,
                   inspeccion.cliente_email,
                   entidad.nombre AS cliente_nombre,
                   entidad.email
            FROM inspeccion
            LEFT OUTER JOIN entidad ON (inspeccion.cliente_id = entidad.id)
            WHERE inspeccion.id = $elcodigo
            LIMIT 1
        ");

        if ($correo_result == false || $correo_result->num_rows == 0) {
            throw new Exception("No se encontró la inspección id=$elcodigo en la base de datos.");
        }

        $correo_row = $correo_result->fetch_assoc();

        // Determinar el email destino
        $email_enviar = trim($correo_row['email']);
        $email_enviar_adicional = array();

        if (!es_nulo(trim($correo_row['cliente_email']))) {
            if ($email_enviar <> $correo_row['cliente_email']) {
                if (es_nulo($email_enviar)) {
                    $email_enviar = trim($correo_row['cliente_email']);
                } else {
                    array_push($email_enviar_adicional, trim($correo_row['cliente_email']));
                }
            }
        }

        if (es_nulo($email_enviar)) {
            throw new Exception("La inspección id=$elcodigo no tiene email configurado. Se omite.");
        }

        // Cargar snapshots PNG temporales para el PDF en segundo plano
        $tmpDir = app_dir . 'reportes/tmp_inspeccion/' . intval($elcodigo) . '/';
        $_REQUEST['pdfimg1'] = leer_png_como_dataurl($tmpDir . 'Inspeccion_' . $correo_row["numero"] . '_pdfimg1.png');
        $_REQUEST['pdffirma1'] = leer_png_como_dataurl($tmpDir . 'Inspeccion_' . $correo_row["numero"] . '_pdffirma1.png');
        $_REQUEST['pdffirma2'] = leer_png_como_dataurl($tmpDir . 'Inspeccion_' . $correo_row["numero"] . '_pdffirma2.png');

        // Generar el PDF
        $guardar_archivo = app_dir . 'reportes/' . 'Inspeccion_' . $correo_row["numero"] . '.pdf';
        include(__DIR__ . '/inspeccion_pdf.php'); // genera el PDF en $guardar_archivo

        // Armar el correo
        $subject = 'HOJA DE INSPECCION # ' . $correo_row['numero'];

        $cuerpo_html = 'Estimado Cliente, <br><br>
            Se le notifica que la hoja de inspeccion # ' . $correo_row['numero'] . ' fue completada.<br>
            <br><br>
            La hoja ha sido adjuntada a este correo.<br><br>
            Gracias';

        $cuerpo_sinhtml = strip_tags($cuerpo_html);

        // Enviar correo
        enviar_correo($email_enviar, $subject, $cuerpo_html, $cuerpo_sinhtml, $email_enviar_adicional, $guardar_archivo);

        // Marcar como enviado exitosamente
        sql_update("UPDATE cola_correo_inspeccion 
                    SET estado = 1, fecha_enviado = NOW(), mensaje_error = NULL 
                    WHERE id = $cola_id");

        limpiar_tmp_inspeccion($elcodigo);

        escribir_log("OK: Correo enviado para inspección #" . $correo_row['numero'] . " → $email_enviar");

    } catch (Throwable $e) {

        $error_msg = $e->getMessage();

        // Marcar como error
        sql_update("UPDATE cola_correo_inspeccion 
                    SET estado = 2, mensaje_error = '" . addslashes($error_msg) . "' 
                    WHERE id = $cola_id");

        escribir_log("ERROR en cola_id=$cola_id: $error_msg");
    }

} // fin while

escribir_log('---- Fin cron ----');
