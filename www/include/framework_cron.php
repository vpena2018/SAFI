<?php
// framework_cron.php
// NO carga framework.php — evita validacion de sesion/cookies.
// Carga config.php directamente y define las funciones necesarias.
// Subir a: /syncv/include/framework_cron.php

// Conexion directa usando las constantes de config.php
require_once(__DIR__ . '/config.php');

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {
    die('Error BD: ' . mysqli_connect_error());
}
$conn->set_charset('utf8');

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
        case "int":    return intval($theValue);
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