<?php
// Version del framework SIN validacion de sesion.
// Solo para uso del cron.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    $_SESSION['usuario']          = 'cron';
    $_SESSION['usuario_id']       = 0;
    $_SESSION['tienda_id']        = 1;
    $_SESSION['seg']              = array();
    $_SESSION['formato_fecha']    = 'dd/mm/yyyy';
    $_SESSION['hora_ultima_tran'] = time();
}

require_once(__DIR__ . '/config.php');

$now_fecha     = date('Y-m-d');
$now_fechahora = date('Y-m-d H:i:s');
$now_hora      = date('H:i:s');

function es_nulo($campo) {
    if ($campo == "" || is_null($campo) || $campo == "0") { return true; }
    return false;
}

function GetSQLValue($theValue, $theType) {
    global $conn;
    switch ($theType) {
        case "text":
            $theValue = $conn->real_escape_string($theValue);
            return "'" . $theValue . "'";
        case "int":   return intval($theValue);
        case "double": return doubleval($theValue);
        default:
            $theValue = $conn->real_escape_string($theValue);
            return "'" . $theValue . "'";
    }
}

function sql_select($sql) {
    global $conn;
    $result = $conn->query($sql);
    return ($result === false) ? false : $result;
}

function sql_insert($sql) {
    global $conn;
    $result = $conn->query($sql);
    return ($result === false) ? false : $conn->insert_id;
}

function sql_update($sql) {
    global $conn;
    $result = $conn->query($sql);
    return ($result === false) ? false : true;
}

function get_dato_sql($tabla, $campo, $condicion) {
    global $conn;
    $result = $conn->query("SELECT $campo AS dato FROM $tabla $condicion LIMIT 1");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['dato'];
    }
    return null;
}