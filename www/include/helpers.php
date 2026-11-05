<?php
//FECHA                 DEVELOPER                 INCIDENTE
// 2026-11-04           Moises Gamez              Se creo libreria de funciones para el sistema de ventas, reparaciones y otras.

require_once('include/framework.php');

function getTipoDocumentos()
{
    return [
        ['valor' => 'dni', 'texto' => 'DNI'],
        ['valor' => 'pasaporte', 'texto' => 'Pasaporte'],
        ['valor' => 'carnet_residente', 'texto' => 'Carnet de Residente'],
    ];
}

function getNacionalidades()
{
    return [
        // ===== AMÉRICA =====
        ['valor' => 'argentino', 'texto' => 'Argentino'],
        ['valor' => 'boliviano', 'texto' => 'Boliviano'],
        ['valor' => 'brasileño', 'texto' => 'Brasileño'],
        ['valor' => 'canadiense', 'texto' => 'Canadiense'],
        ['valor' => 'chileno', 'texto' => 'Chileno'],
        ['valor' => 'colombiano', 'texto' => 'Colombiano'],
        ['valor' => 'costarricense', 'texto' => 'Costarricense'],
        ['valor' => 'cubano', 'texto' => 'Cubano'],
        ['valor' => 'ecuatoriano', 'texto' => 'Ecuatoriano'],
        ['valor' => 'salvadoreño', 'texto' => 'Salvadoreño'],
        ['valor' => 'estadounidense', 'texto' => 'Estadounidense'],
        ['valor' => 'guatemalteco', 'texto' => 'Guatemalteco'],
        ['valor' => 'haitiano', 'texto' => 'Haitiano'],
        ['valor' => 'hondureño', 'texto' => 'Hondureño'],
        ['valor' => 'jamaicano', 'texto' => 'Jamaicano'],
        ['valor' => 'mexicano', 'texto' => 'Mexicano'],
        ['valor' => 'nicaragüense', 'texto' => 'Nicaragüense'],
        ['valor' => 'panameño', 'texto' => 'Panameño'],
        ['valor' => 'paraguayo', 'texto' => 'Paraguayo'],
        ['valor' => 'peruano', 'texto' => 'Peruano'],
        ['valor' => 'dominicano', 'texto' => 'Dominicano'],
        ['valor' => 'uruguayo', 'texto' => 'Uruguayo'],
        ['valor' => 'venezolano', 'texto' => 'Venezolano'],

        // ===== EUROPA =====
        ['valor' => 'alemán', 'texto' => 'Alemán'],
        ['valor' => 'austriaco', 'texto' => 'Austriaco'],
        ['valor' => 'belga', 'texto' => 'Belga'],
        ['valor' => 'búlgaro', 'texto' => 'Búlgaro'],
        ['valor' => 'croata', 'texto' => 'Croata'],
        ['valor' => 'checo', 'texto' => 'Checo'],
        ['valor' => 'danés', 'texto' => 'Danés'],
        ['valor' => 'español', 'texto' => 'Español'],
        ['valor' => 'finlandés', 'texto' => 'Finlandés'],
        ['valor' => 'francés', 'texto' => 'Francés'],
        ['valor' => 'griego', 'texto' => 'Griego'],
        ['valor' => 'húngaro', 'texto' => 'Húngaro'],
        ['valor' => 'irlandés', 'texto' => 'Irlandés'],
        ['valor' => 'islandés', 'texto' => 'Islandés'],
        ['valor' => 'italiano', 'texto' => 'Italiano'],
        ['valor' => 'letón', 'texto' => 'Letón'],
        ['valor' => 'lituano', 'texto' => 'Lituano'],
        ['valor' => 'luxemburgués', 'texto' => 'Luxemburgués'],
        ['valor' => 'neerlandés', 'texto' => 'Neerlandés'],
        ['valor' => 'noruego', 'texto' => 'Noruego'],
        ['valor' => 'polaco', 'texto' => 'Polaco'],
        ['valor' => 'portugués', 'texto' => 'Portugués'],
        ['valor' => 'rumano', 'texto' => 'Rumano'],
        ['valor' => 'ruso', 'texto' => 'Ruso'],
        ['valor' => 'serbio', 'texto' => 'Serbio'],
        ['valor' => 'sueco', 'texto' => 'Sueco'],
        ['valor' => 'suizo', 'texto' => 'Suizo'],
        ['valor' => 'ucraniano', 'texto' => 'Ucraniano'],
        ['valor' => 'británico', 'texto' => 'Británico'],
    ];
}

function appLog(string $message): void
{
    //return; // Desactivar logs en producción
    try {
        $logFile = app_logs_folder . 'ventas_contrato.log';
        $date = date('Y-m-d H:i:s');

        file_put_contents(
            $logFile,
            "[$date] $message\n",
            FILE_APPEND | LOCK_EX
        );
    } catch (Throwable $e) {
        // Nunca romper producción por un log
        error_log('LOGGER ERROR: ' . $e->getMessage());
    }
}

//para desarrollo windows
function getSofficeCommandDev()
{
    // Windows
    if (stripos(PHP_OS, 'WIN') === 0) {
        $paths = [
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return '"' . $path . '"';
            }
        }

        throw new Exception('LibreOffice no encontrado en Windows');
    }

    // Linux / Unix
    return 'soffice';
}

//para produccion windows
function getSofficeCommandProd()
{
    try {
        appLog('Entrando a getSofficeCommand');

        $path = '/usr/bin/soffice';

        if (!file_exists($path)) {
            appLog('ERROR: soffice no existe en ' . $path);
            throw new RuntimeException('LibreOffice no encontrado');
        }

        if (!is_executable($path)) {
            appLog('ERROR: soffice no es ejecutable');
            throw new RuntimeException('LibreOffice sin permisos');
        }

        appLog('LibreOffice OK');
        return $path;

    } catch (Throwable $e) {
        appLog('EXCEPTION: ' . $e->getMessage());
        throw $e; // mantiene el 500 pero ahora con info real
    }
}

//Funcion que registra el historial de cambios de registros.
function registrar_historial($cid, $id_estado, $nombre, $observaciones = '', $permiso = 0)
{
    $cid = intval($cid);
    $id_estado = intval($id_estado);
    $uid = intval($_SESSION['usuario_id']);
    $sql = "INSERT INTO ventas_historial_estado (id_maestro, id_usuario, id_estado, nombre, fecha, observaciones, permiso)
            VALUES (
                $cid,
                $uid,
                $id_estado,
                " . GetSQLValue($nombre, "text") . ",
                NOW(),
                " . GetSQLValue($observaciones, "text") . ",
                $permiso
            )";
    return sql_insert($sql);
}

//Funcion obtiene el historial de cambios de registros.
function getDatosHistorial($cid, $permiso = 201)
{
    $sql = "SELECT id, id_maestro, id_usuario, id_estado, nombre, fecha, permiso FROM 
	 ventas_historial_estado WHERE permiso = $permiso and id_maestro = $cid ORDER BY fecha DESC";

    $res = sql_select($sql);

    if ($res && $row = $res->fetch_assoc()) {
        return [
            'id' => $row['id'],
            'id_maestro' => $row['id_maestro'],
            'id_usuario' => $row['id_usuario'],
            'id_estado' => $row['id_estado'],
            'nombre' => $row['nombre'],
            'fecha' => $row['fecha'],
            'permiso' => $row['permiso']
        ];
    }

    return null;
}

//Funcion obtiene las clases basadas en el id de la clase.
function getDatosClase($clase)
{
    $sql = "SELECT id, clase, tipo_vehiculo, clasificacion, monto_reserva 
	 FROM clases_costo_reserva WHERE clase = '$clase' LIMIT 1";

    $res = sql_select($sql);

    if ($res && $row = $res->fetch_assoc()) {
        return [
            'id' => $row['id'],
            'clase' => $row['clase'],
            'tipo_vehiculo' => $row['tipo_vehiculo'],
            'clasificacion' => $row['clasificacion'],
            'monto_reserva' => (float) $row['monto_reserva']
        ];
    }

    return null;
}

//Datos de venta o reparacion pantallas
function getDataVenta($cid)
{

    return sql_select("SELECT ventas.*    
    ,estado1.nombre AS elestado1
    ,estado2.nombre AS elestado2
    ,estado3.nombre AS elestado3    
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
    ,tienda.nombre AS latienda
    ,entidad.nombre AS cliente_nombre
    ,ventas.persona_juridica
    ,ventas.representante_legal_persona_juridica
    ,ventas.representante_legal_identidad
    ,ventas.representante_legal_profesion
    ,ventas.representante_legal_direccion
    ,ventas.tipo_documento_ident_venta
    ,ventas.nacionalidad_venta
    ,producto.clase
        FROM ventas
        LEFT OUTER JOIN tienda ON (ventas.id_tienda=tienda.id)        
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)        
        LEFT OUTER JOIN ventas_estado estado1 ON (ventas.id_estado_pintura=estado1.id)
        LEFT OUTER JOIN ventas_estado estado2 ON (ventas.id_estado_interior=estado2.id)
        LEFT OUTER JOIN ventas_estado estado3 ON (ventas.id_estado_mecanica=estado3.id)
        LEFT OUTER JOIN entidad ON (ventas.cliente_id=entidad.id)
    where ventas.id = $cid limit 1");
}

?>