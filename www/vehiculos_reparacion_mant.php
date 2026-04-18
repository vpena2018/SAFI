<?php
require_once ('include/framework.php');
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

$estado_global_nuevo=99;       
$estado_global_negociacion=11;

       $tipos_docu = [
            ['valor' => 'dni', 'texto' => 'DNI'],
            ['valor' => 'pasaporte', 'texto' => 'pasaporte'],
            ['valor' => 'carnet_residente', 'texto' => 'carnet de residente'],
        ];

$nacionalidades = [

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


function appLog(string $message): void
{
    return; // Desactivar logs en producción
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

function validar_campos_obligatorios($data, $campos, $titulo = 'Faltan campos obligatorios')
{
    $faltantes = [];

    foreach ($campos as $campo => $nombre) {
        if (!isset($data[$campo]) || trim($data[$campo]) === '') {
            $faltantes[] = "• " . $nombre;
        }
    }

    if (!empty($faltantes)) {
        $mensaje = $titulo . ":\n" . implode("\n", $faltantes);
        throw new Exception($mensaje);
    }
}


/**
 * @return bool|array
 */
function generarContratoVenta(
    int $id_venta,
    string $nombreUsuario,
    string $apellidoUsuario,
    string $usuarioSistema,
    bool $persona_juridica,
    bool $sologuardar = false
) {
    try {

        $tipo_contrato=0;

        if($persona_juridica)
        {
            $tipo_contrato=1;
        }

         $resContrato = sql_select("
            SELECT id_contrato, id_venta, estado
            FROM ventas_contratos
            WHERE estado = 'ACTIVO'
            and id_venta = $id_venta
        ");

         $resventa = sql_select("
            SELECT id, id_estado
            FROM ventas
            WHERE id = $id_venta
        ");

        if (!$resContrato || $resContrato->num_rows>=1) {


            if ($resventa && $resventa->num_rows > 0) {

                $rowVenta = $resventa->fetch_assoc();
                $estado_venta = $rowVenta['id_estado'];

                $resEstado = sql_select("
                    SELECT generar_contrato
                    FROM ventas_estado
                    WHERE id = $estado_venta
                ");

                if ($resEstado && $resEstado->num_rows > 0) {

                    $rowEstado = $resEstado->fetch_assoc();

                    if ($rowEstado['generar_contrato'] != 1) {
                        throw new Exception("El estado actual no permite generar contrato.");
                    }
                }
            }



            throw new Exception("ya existe un contrato activo para esta venta, favor anular el contrato actual para generar uno nuevo");
        }

        sql_update("START TRANSACTION");

        /* ===============================
           1️⃣ CONSULTA COMPLETA DE LA VENTA
        =============================== */
        $datos_venta = sql_select("
            SELECT
                ventas.id,
                ventas.id_tienda,
                ventas.precio_venta,
                ventas.prima_venta,
                ventas.cilindraje,

                ventas.representante_legal_persona_juridica,
                ventas.representante_legal_identidad,
                ventas.representante_legal_profesion,
                ventas.representante_legal_direccion,

                ventas.nacionalidad_venta,
                ventas.tipo_documento_ident_venta,

                entidad.nombre   AS cliente_nombre,
                entidad.identidad      AS identidad_cliente,
                entidad.direccion AS direccion_cliente,
                entidad.codigo_alterno AS codigo_cliente,
                entidad.telefono AS telefono_cliente,
                entidad.ciudad AS ciudad_venta,

                producto.codigo_alterno AS cod_vehiculo,
                producto.cilindrada,
                producto.placa,
                producto.marca,
                producto.modelo,
                producto.tipo_vehiculo AS tipo,
                producto.chasis,
                producto.motor,
                producto.color,
                producto.anio,
                producto.combustible AS combustible
            FROM ventas
            LEFT JOIN entidad  ON ventas.cliente_id = entidad.id
            LEFT JOIN producto ON ventas.id_producto = producto.id
            WHERE ventas.id = $id_venta
            FOR UPDATE
        ");


        if (!$datos_venta || $datos_venta->num_rows === 0) {
            throw new Exception("La venta no existe");
        }

        $venta = $datos_venta->fetch_assoc();


        //validamos datos del cliente
        validar_campos_obligatorios($venta, [
            'cliente_nombre'    => 'Nombre del cliente',
            'identidad_cliente' => 'Identidad',
            'direccion_cliente' => 'Dirección',
            'codigo_cliente'    => 'Código cliente',
            'telefono_cliente'  => 'Teléfono',
            'ciudad_venta'      => 'Ciudad'
        ],  'Falta informacion de cliente');

        validar_campos_obligatorios($venta, [
            'cod_vehiculo' => 'Código del vehículo',
            'placa'        => 'Placa',
            'marca'        => 'Marca',
            'modelo'       => 'Modelo',
            'tipo'         => 'Tipo de vehículo',
            'chasis'       => 'Chasis',
            'motor'        => 'Motor',
            'color'        => 'Color',
            'anio'         => 'Año',
            'combustible'  => 'Combustible',
            'cilindrada'   => 'Cilindrada'
        ],  'Falta informacion del vehículo');

        /* ===============================
           2️⃣ DATOS DE LA TIENDA
        =============================== */
        $datos_tienda = sql_select("
            SELECT 
                representante_legal, 
                representante_identidad, 
                nombre, 
                departamento,
                abr_ciudad,
                CASE 
                    WHEN abr_ciudad = 'SPS' THEN 'San Pedro Sula'
                    WHEN abr_ciudad = 'TGU' THEN 'Tegucigalpa'
                    ELSE abr_ciudad
                END AS ciudad_nombre
            FROM tienda
            WHERE id = {$venta['id_tienda']}
            LIMIT 1
        ");

        if (!$datos_tienda || $datos_tienda->num_rows === 0) {
            throw new Exception("Tienda no encontrada");
        }

        $tienda = $datos_tienda->fetch_assoc();

        /* ===============================
           3️⃣ ANULAR CONTRATOS ANTERIORES
        =============================== */
        sql_update("
            UPDATE ventas_contratos
            SET estado = 'ANULADO'
            WHERE id_venta = $id_venta
        ");

        sql_update("
            UPDATE ventas_contratos_detalle
            SET estado = 'ANULADO',
                accion = 'ANULADO'
            WHERE id_venta = $id_venta
        ");

        /* ===============================
           4️⃣ CORRELATIVO
        =============================== */
        $datos_corr = sql_select("
            SELECT id, correlativo_actual
            FROM venta_correlativo_contrato
            FOR UPDATE
        ");

        if (!$datos_corr || $datos_corr->num_rows === 0) {
            throw new Exception("No existe correlativo");
        }

        $corr = $datos_corr->fetch_assoc();
        $nuevoCorrelativo = $corr['correlativo_actual'] + 1;

        sql_update("
            UPDATE venta_correlativo_contrato
            SET correlativo_actual = $nuevoCorrelativo
            WHERE id = {$corr['id']}
        ");

        /* ===============================
           5️⃣ NÚMERO DE CONTRATO
        =============================== */
        $anio = date('Y'); 
        //$anio = date('y'); //26
        $correlativo5 = str_pad($nuevoCorrelativo, 5, '0', STR_PAD_LEFT);
        $letrasUsuario =
            strtoupper(substr($nombreUsuario, 0, 1)) .
            strtoupper(substr($apellidoUsuario, 0, 1));

        $numeroContrato = "{$tienda['abr_ciudad']}-{$anio}-{$correlativo5}-{$letrasUsuario}";

        /* ===============================
           6️⃣ INSERTAR CONTRATO
        =============================== */
        sql_insert("
            INSERT INTO ventas_contratos
            (id_venta,tipo_contrato, correlativo, numero_contrato, estado, creado_por)
            VALUES
            ($id_venta, $tipo_contrato, $nuevoCorrelativo, '$numeroContrato', 'ACTIVO', '$usuarioSistema')
        ");

        $resId = sql_select("SELECT LAST_INSERT_ID() AS id");
        $idContrato = $resId->fetch_assoc()['id'];

        /* ===============================
           7️⃣ JSON CONTRACTUAL
        =============================== */
        date_default_timezone_set('America/Tegucigalpa');

        $meses = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'
        ];

        $precioVenta = (float)$venta['precio_venta'];
        $primaVenta  = (float)$venta['prima_venta'];


        try {
    $contratoJson = json_encode([
        // tu array completo
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    echo "OK";
} catch (JsonException $e) {
    echo $e->getMessage();
}


try{
    $contratoJson = json_encode([
            'representante' => [
                'nombre' => $tienda['representante_legal'],
                'identidad' => $tienda['representante_identidad'],
                'ciudad' => $tienda['ciudad_nombre'],
                'departamento' => $tienda['departamento']
            ],
            'cliente' => [
                'nombre' => $venta['cliente_nombre'],
                'identidad' => $venta['identidad_cliente'],
                'codigo' => $venta['codigo_cliente'],
                'direccion' => $venta['direccion_cliente'],
                'telefono' => $venta['telefono_cliente'],
                'nacionalidad' => $venta['nacionalidad_venta'],
                //'ciudad' => $venta['ciudad_venta'],
                //'departamento' => $venta['departamento_venta'],
                'tipo_documento_ident_venta'=> $venta['tipo_documento_ident_venta'],
                'ciudad' => $venta['ciudad_venta']
                
            ],
            'precios' => [
                'precio_venta' => $precioVenta,
                'precio_venta_letras' => numeroALetras($precioVenta),
                'prima_venta' => $primaVenta,
                'prima_venta_letras' => numeroALetras($primaVenta)
            ],
            'vehiculo' => [
                'codigo' => $venta['cod_vehiculo'],
                'placa' => $venta['placa'],
                'marca' => $venta['marca'],
                'modelo' => $venta['modelo'],
                'tipo' => $venta['tipo'],
                'chasis' => $venta['chasis'],
                'motor' => $venta['motor'],
                //'color' => $venta['color'],
                'color' => preg_replace('/\s+/', ' ', trim($venta['color'])),

                'anio' => $venta['anio'],
                'cilindraje' => $venta['cilindrada'],
                'combustible' => $venta['combustible']
            ],
            'fecha' => [
                'dia' => date('j'),
                'mes' => (int)date('n'),
                'anio' => date('Y')
            ],
            'datos_juridicos' => [
                'representante_legal' => $venta['representante_legal_persona_juridica'] ?? '',
                'representante_legal_identidad' => $venta['representante_legal_identidad'] ?? '',
                'representante_legal_profesion' => $venta['representante_legal_profesion'] ?? '',
                'representante_legal_direccion' => $venta['representante_legal_direccion'] ?? ''
            ],
            'meta' => [
                'id_contrato' => $idContrato,
                'id_venta' => $id_venta,
                'numero_contrato' => $numeroContrato,
                'correlativo' => $nuevoCorrelativo,
                'usuario' => $usuarioSistema,
                'creado_en' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

} catch (JsonException $e) {
    $error= "ERROR: " . $e->getMessage();
    $temp=1;
}

/*         sql_insert("
            INSERT INTO ventas_contratos_detalle
            (id_contrato, tipo_contrato, id_venta, accion, usuario, estado, datos_json)
            VALUES
            ($idContrato, $tipo_contrato, $id_venta, 'CREACION', '$usuarioSistema', 'ACTIVO', '$contratoJson')
        "); */

            //$contratoJsonSafe = addslashes($contratoJson);
            //$usuarioSistemaSafe = addslashes($usuarioSistema);

            $contratoJsonSafe = str_replace("'", "''", $contratoJson);
            $usuarioSistemaSafe = str_replace("'", "''", $usuarioSistema);

            sql_insert("
                INSERT INTO ventas_contratos_detalle
                (id_contrato, tipo_contrato, id_venta, accion, usuario, estado, datos_json)
                VALUES
                ($idContrato, $tipo_contrato, $id_venta, 'CREACION', '$usuarioSistemaSafe', 'ACTIVO', '$contratoJsonSafe')
            ");

        sql_update("COMMIT");

        return $sologuardar
            ? true
            : [
                'ok' => true,
                'id_contrato' => $idContrato,
                'numero_contrato' => $numeroContrato
            ];

    } catch (Exception $e) {

        sql_update("ROLLBACK");

        return $sologuardar
            ? false
            : [
                'ok' => false,
                'error' => $e->getMessage()
            ];
    }
}



/**
 * @return bool|array
 */
function generarContratoVentaOld(
    int $id_venta,
    string $nombreUsuario,
    string $apellidoUsuario,
    string $usuarioSistema,
    bool $persona_juridica,
    bool $sologuardar = false
) {
    try {

        $tipo_contrato=0;

        if($persona_juridica)
        {
            $tipo_contrato=1;
        }

         $resContrato = sql_select("
            SELECT id_contrato, id_venta, estado
            FROM ventas_contratos
            WHERE estado = 'ACTIVO'
            and id_venta = $id_venta
        ");

        if (!$resContrato || $resContrato->num_rows>=1) {
            throw new Exception("ya existe un contrato activo para esta venta, favor anular el contrato actual para generar uno nuevo");
        }

        sql_update("START TRANSACTION");

        /* ===============================
           1️⃣ CONSULTA COMPLETA DE LA VENTA
        =============================== */
        $datos_venta = sql_select("
            SELECT
                ventas.id,
                ventas.id_tienda,
                ventas.precio_venta,
                ventas.prima_venta,
                ventas.cilindraje,

                ventas.representante_legal_persona_juridica,
                ventas.representante_legal_identidad,
                ventas.representante_legal_profesion,
                ventas.representante_legal_direccion,

                ventas.nacionalidad_venta,
                ventas.tipo_documento_ident_venta,

                entidad.nombre   AS cliente_nombre,
                entidad.identidad      AS identidad_cliente,
                entidad.direccion AS direccion_cliente,
                entidad.codigo_alterno AS codigo_cliente,
                entidad.telefono AS telefono_cliente,
                entidad.ciudad AS ciudad_venta,

                producto.codigo_alterno AS cod_vehiculo,
                producto.placa,
                producto.marca,
                producto.modelo,
                producto.tipo_vehiculo AS tipo,
                producto.chasis,
                producto.motor,
                producto.color,
                producto.anio,
                producto.combustible AS combustible
            FROM ventas
            LEFT JOIN entidad  ON ventas.cliente_id = entidad.id
            LEFT JOIN producto ON ventas.id_producto = producto.id
            WHERE ventas.id = $id_venta
            FOR UPDATE
        ");


        if (!$datos_venta || $datos_venta->num_rows === 0) {
            throw new Exception("La venta no existe");
        }

        $venta = $datos_venta->fetch_assoc();


        //validamos datos del cliente
        validar_campos_obligatorios($venta, [
            'cliente_nombre'    => 'Nombre del cliente',
            'identidad_cliente' => 'Identidad',
            'direccion_cliente' => 'Dirección',
            'codigo_cliente'    => 'Código cliente',
            'telefono_cliente'  => 'Teléfono',
            'ciudad_venta'      => 'Ciudad'
        ],  'Falta informacion de cliente');

        validar_campos_obligatorios($venta, [
            'cod_vehiculo' => 'Código del vehículo',
            'placa'        => 'Placa',
            'marca'        => 'Marca',
            'modelo'       => 'Modelo',
            'tipo'         => 'Tipo de vehículo',
            'chasis'       => 'Chasis',
            'motor'        => 'Motor',
            'color'        => 'Color',
            'anio'         => 'Año',
            'combustible'  => 'Combustible'
        ],  'Falta informacion del vehículo');

        /* ===============================
           2️⃣ DATOS DE LA TIENDA
        =============================== */
        $datos_tienda = sql_select("
            SELECT 
                representante_legal, 
                representante_identidad, 
                nombre, 
                departamento,
                abr_ciudad,
                CASE 
                    WHEN abr_ciudad = 'SPS' THEN 'San Pedro Sula'
                    WHEN abr_ciudad = 'TGU' THEN 'Tegucigalpa'
                    ELSE abr_ciudad
                END AS ciudad_nombre
            FROM tienda
            WHERE id = {$venta['id_tienda']}
            LIMIT 1
        ");

        if (!$datos_tienda || $datos_tienda->num_rows === 0) {
            throw new Exception("Tienda no encontrada");
        }

        $tienda = $datos_tienda->fetch_assoc();

        /* ===============================
           3️⃣ ANULAR CONTRATOS ANTERIORES
        =============================== */
        sql_update("
            UPDATE ventas_contratos
            SET estado = 'ANULADO'
            WHERE id_venta = $id_venta
        ");

        sql_update("
            UPDATE ventas_contratos_detalle
            SET estado = 'ANULADO',
                accion = 'ANULADO'
            WHERE id_venta = $id_venta
        ");

        /* ===============================
           4️⃣ CORRELATIVO
        =============================== */
        $datos_corr = sql_select("
            SELECT id, correlativo_actual
            FROM venta_correlativo_contrato
            FOR UPDATE
        ");

        if (!$datos_corr || $datos_corr->num_rows === 0) {
            throw new Exception("No existe correlativo");
        }

        $corr = $datos_corr->fetch_assoc();
        $nuevoCorrelativo = $corr['correlativo_actual'] + 1;

        sql_update("
            UPDATE venta_correlativo_contrato
            SET correlativo_actual = $nuevoCorrelativo
            WHERE id = {$corr['id']}
        ");

        /* ===============================
           5️⃣ NÚMERO DE CONTRATO
        =============================== */
        $anio = date('Y'); 
        //$anio = date('y'); //26
        $correlativo5 = str_pad($nuevoCorrelativo, 5, '0', STR_PAD_LEFT);
        $letrasUsuario =
            strtoupper(substr($nombreUsuario, 0, 1)) .
            strtoupper(substr($apellidoUsuario, 0, 1));

        $numeroContrato = "{$tienda['abr_ciudad']}-{$anio}-{$correlativo5}-{$letrasUsuario}";

        /* ===============================
           6️⃣ INSERTAR CONTRATO
        =============================== */
        sql_insert("
            INSERT INTO ventas_contratos
            (id_venta,tipo_contrato, correlativo, numero_contrato, estado, creado_por)
            VALUES
            ($id_venta, $tipo_contrato, $nuevoCorrelativo, '$numeroContrato', 'ACTIVO', '$usuarioSistema')
        ");

        $resId = sql_select("SELECT LAST_INSERT_ID() AS id");
        $idContrato = $resId->fetch_assoc()['id'];

        /* ===============================
           7️⃣ JSON CONTRACTUAL
        =============================== */
        date_default_timezone_set('America/Tegucigalpa');

        $meses = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'
        ];

        $precioVenta = (float)$venta['precio_venta'];
        $primaVenta  = (float)$venta['prima_venta'];

        $contratoJson = json_encode([
            'representante' => [
                'nombre' => $tienda['representante_legal'],
                'identidad' => $tienda['representante_identidad'],
                'ciudad' => $tienda['ciudad_nombre'],
                'departamento' => $tienda['departamento']
            ],
            'cliente' => [
                'nombre' => $venta['cliente_nombre'],
                'identidad' => $venta['identidad_cliente'],
                'codigo' => $venta['codigo_cliente'],
                'direccion' => $venta['direccion_cliente'],
                'telefono' => $venta['telefono_cliente'],
                'nacionalidad' => $venta['nacionalidad_venta'],
                //'ciudad' => $venta['ciudad_venta'],
                //'departamento' => $venta['departamento_venta'],
                'tipo_documento_ident_venta'=> $venta['tipo_documento_ident_venta'],
                'ciudad' => $venta['ciudad_venta']
                
            ],
            'precios' => [
                'precio_venta' => $precioVenta,
                'precio_venta_letras' => numeroALetras($precioVenta),
                'prima_venta' => $primaVenta,
                'prima_venta_letras' => numeroALetras($primaVenta)
            ],
            'vehiculo' => [
                'codigo' => $venta['cod_vehiculo'],
                'placa' => $venta['placa'],
                'marca' => $venta['marca'],
                'modelo' => $venta['modelo'],
                'tipo' => $venta['tipo'],
                'chasis' => $venta['chasis'],
                'motor' => $venta['motor'],
                'color' => $venta['color'],
                'anio' => $venta['anio'],
                'cilindraje' => $venta['cilindraje'],
                'combustible' => $venta['combustible']
            ],
            'fecha' => [
                'dia' => date('j'),
                'mes' => (int)date('n'),
                'anio' => date('Y')
            ],
            'datos_juridicos' => [
                'representante_legal' => $venta['representante_legal_persona_juridica'],
                'representante_legal_identidad' => $venta['representante_legal_identidad'],
                'representante_legal_profesion' => $venta['representante_legal_profesion'],
                'representante_legal_direccion' => $venta['representante_legal_direccion']
            ],
            'meta' => [
                'id_contrato' => $idContrato,
                'id_venta' => $id_venta,
                'numero_contrato' => $numeroContrato,
                'correlativo' => $nuevoCorrelativo,
                'usuario' => $usuarioSistema,
                'creado_en' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);

        sql_insert("
            INSERT INTO ventas_contratos_detalle
            (id_contrato, tipo_contrato, id_venta, accion, usuario, estado, datos_json)
            VALUES
            ($idContrato, $tipo_contrato, $id_venta, 'CREACION', '$usuarioSistema', 'ACTIVO', '$contratoJson')
        ");

        sql_update("COMMIT");

        return $sologuardar
            ? true
            : [
                'ok' => true,
                'id_contrato' => $idContrato,
                'numero_contrato' => $numeroContrato
            ];

    } catch (Exception $e) {

        sql_update("ROLLBACK");

        return $sologuardar
            ? false
            : [
                'ok' => false,
                'error' => $e->getMessage()
            ];
    }
}

function anularContratoVenta(
    int $id_venta,
    string $usuarioSistema
) {
    try {

        sql_update("START TRANSACTION");

        // Validar contrato
        $resContrato = sql_select("
            SELECT id_contrato, id_venta, estado
            FROM ventas_contratos
            WHERE estado = 'ACTIVO'
            and id_venta = $id_venta
            FOR UPDATE
        ");

        if (!$resContrato || $resContrato->num_rows === 0) {
            throw new Exception("no existe contrato para anular");
        }

        $contrato = $resContrato->fetch_assoc();

/*         if ($contrato['estado'] !== 'ACTIVO') {
            throw new Exception("Solo se pueden anular contratos activos");
        } */

        // Anular contrato principal
        sql_update("
            UPDATE ventas_contratos
            SET estado = 'ANULADO', 
                anulado_por = '$usuarioSistema',
                fecha_anulacion = NOW()
            WHERE id_contrato = {$contrato['id_contrato']}
        ");

        // Anular detalle del contrato
        sql_update("
            UPDATE ventas_contratos_detalle
            SET estado = 'ANULADO',
                accion = 'ANULACION',
                anulado_por = '$usuarioSistema',
                fecha_anulacion = NOW()
            WHERE id_contrato = {$contrato['id_contrato']}
              AND estado = 'ACTIVO'
        ");

        sql_update("COMMIT");

        return [
            'ok' => true,
            'message' => 'Contrato anulado correctamente'
        ];

    } catch (Exception $e) {

        sql_update("ROLLBACK");

        return [
            'ok' => false,
            'error' => $e->getMessage()
        ];
    }
}

function convertirDocxAPdf(string $docxPath): string
{
    try {
        appLog('--- convertirDocxAPdf INICIO ---');
        appLog('DOCX: ' . $docxPath);

        if (!file_exists($docxPath)) {
            appLog('ERROR: DOCX no existe');
            throw new RuntimeException('Archivo DOCX no encontrado');
        }

        $tmpDir = sys_get_temp_dir();
        appLog('TMP DIR: ' . $tmpDir);

        //$soffice = getSofficeCommandProd();//descomentar para produccion

        $soffice = getSofficeCommandDev();//comentar para produccion




        appLog('SOFFICE: ' . $soffice);

        $cmd = $soffice .
            ' --headless --convert-to pdf --outdir ' .
            escapeshellarg($tmpDir) . ' ' .
            escapeshellarg($docxPath) . ' 2>&1';

        appLog('CMD: ' . $cmd);

        exec($cmd, $output, $code);

        appLog('RETURN CODE: ' . $code);
        appLog('OUTPUT: ' . implode(' | ', $output));

        if ($code !== 0) {
            throw new RuntimeException('LibreOffice falló al convertir');
        }

        $files = glob($tmpDir . '/*.pdf');
        $pdfPath = end($files);

        appLog('PDF DETECTADO: ' . $pdfPath);

        if (!$pdfPath || !file_exists($pdfPath)) {
            throw new RuntimeException('No se encontró el PDF generado');
        }


        appLog('PDF ESPERADO: ' . $pdfPath);

        if (!file_exists($pdfPath)) {
            throw new RuntimeException('El PDF no fue generado');
        }

        appLog('PDF GENERADO OK');
        appLog('--- convertirDocxAPdf FIN ---');

        return $pdfPath;

    } catch (Throwable $e) {
        appLog('EXCEPTION convertirDocxAPdf: ' . $e->getMessage());
        throw $e; // mantiene el 500 pero ahora con log claro
    }
}

function descargarVentaPDF($id_venta,$juridico, $soloValidar = false)
{
    try {
        appLog('===== descargarVentaPDF INICIO =====');

        if (empty($id_venta)) {
            throw new RuntimeException('ID de venta inválido');
        }

        /* =========================
           1️⃣ CONTRATO ACTIVO
        ========================== */
        $resContrato = sql_select("
            SELECT datos_json
            FROM ventas_contratos_detalle
            WHERE id_venta = $id_venta
              AND estado = 'ACTIVO'
            ORDER BY id DESC
            LIMIT 1
        ");

        if (!$resContrato || $resContrato->num_rows === 0) {
            throw new RuntimeException('Contrato activo no encontrado');
        }

        $data = json_decode(
            $resContrato->fetch_assoc()['datos_json'],
            true
        );

        if (!$data) {
            throw new RuntimeException('JSON del contrato inválido');
        }

        // 🔹 Modo AJAX (solo validar)
        if ($soloValidar === true) {
            return [
                'ok' => true,
                'numero_contrato' => $data['meta']['numero_contrato']
            ];
        }

        /* =========================
           2️⃣ TEMPLATE DOCX
        ========================== */
        //$templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo.docx';

        if($juridico)
        {
            $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PJ_v2.docx';
        }else{
            $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PN_v2.docx';
        }


        if (!file_exists($templatePath)) {
            throw new RuntimeException('Template DOCX no encontrado');
        }

        $template = new TemplateProcessor($templatePath);

        /* =========================
           3️⃣ REEMPLAZOS (DESDE JSON)
        ========================== */

        //correlativo
        $template->setValue('CORRELATIVO', $data['meta']['numero_contrato']);   


        // Representante
        $template->setValue('REPRESENTANTE_LEGAL', $data['representante']['nombre']);
        $template->setValue('R_IDENTIDAD', $data['representante']['identidad']);
        
        //$template->setValue('CIUDAD', $data['representante']['ciudad']);

        //$template->setValue('DEPARTAMENTO', $data['representante']['departamento']);

        

        //datos globales
        $template->setValue('PROFESION_COMPRADOR', $data['datos_juridicos']['representante_legal_profesion']);
        $template->setValue('NACIONALIDAD', $data['cliente']['nacionalidad']);

        //$template->setValue('CIUDAD', $data['cliente']['ciudad']);

        
        $template->setValue('CIUDAD_C', $data['cliente']['ciudad']);


        //$template->setValue('DEPARTAMENTO', $data['cliente']['departamento']);

        $template->setValue('CIUDAD', $data['representante']['ciudad']);
        $template->setValue('DEPARTAMENTO', $data['representante']['departamento']);



        if($juridico)
            {
                    if($data['cliente']['tipo_documento_ident_venta'] == 'dni')
                    {
                        $desc='con documento nacional de identificacion numero '.$data['datos_juridicos']['representante_legal_identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);   
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte')
                    {
                        $desc='con pasaporte numero '.$data['datos_juridicos']['representante_legal_identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente')
                    {
                        $desc='con carnet de residente numero '.$data['datos_juridicos']['representante_legal_identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }

            }else{
                    if($data['cliente']['tipo_documento_ident_venta'] == 'dni')
                    {
                        $desc='con documento nacional de identificacion numero '.$data['cliente']['identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);   
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte')
                    {
                        $desc='con pasaporte numero '.$data['cliente']['identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente')
                    {
                        $desc='con carnet de residente numero '.$data['cliente']['identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }

            }

        //datos juridicos
        $template->setValue('R_LEGAL_J', $data['datos_juridicos']['representante_legal']);
        $template->setValue('R_LEGAL_PROFESION_J', $data['datos_juridicos']['representante_legal_profesion']);
        $template->setValue('R_LEGAL_DENTIDAD_J', $data['datos_juridicos']['representante_legal_identidad']);
        $template->setValue('R_LEGAL_DIR', $data['datos_juridicos']['representante_legal_direccion']);
        //datos cliente juridico
        $template->setValue('NOMBRE_EMPRESA_J', $data['cliente']['nombre']);
        $template->setValue('EMPRESA_RTN_J', $data['cliente']['identidad']);
        $template->setValue('COD_CLIENTE_EMPRESA_J', $data['cliente']['codigo']);
        $template->setValue('EMPRESA_TELEFONO', $data['cliente']['telefono']);


        // Cliente
        $template->setValue('CLIENTE', $data['cliente']['nombre']);
        $template->setValue('IDENTIDAD_CLIENTE', $data['cliente']['identidad']);
        $template->setValue('CODIGO_CLIENTE', $data['cliente']['codigo']);
        $template->setValue('DIRECCION_CLIENTE', $data['cliente']['direccion']);
        $template->setValue('TELEFONO_CLIENTE', $data['cliente']['telefono']);


        

        // Precios
        $template->setValue(
            'PRECIO_VENTA',
            number_format($data['precios']['precio_venta'], 2, '.', ',')
        );
        $template->setValue(
            'PRECIO_VENTA_LETRAS',
            $data['precios']['precio_venta_letras']
        );

        $template->setValue(
            'PRIMA_VENTA',
            number_format($data['precios']['prima_venta'], 2, '.', ',')
        );
        $template->setValue(
            'PRIMA_VENTA_LETRAS',
            $data['precios']['prima_venta_letras']
        );

        // Vehículo
        $template->setValue('CODIGO_VEHICULO', $data['vehiculo']['codigo']);
        $template->setValue('PLACA', $data['vehiculo']['placa']);
        $template->setValue('MARCA', $data['vehiculo']['marca']);
        $template->setValue('MODELO', $data['vehiculo']['modelo']);
        $template->setValue('TIPO', $data['vehiculo']['tipo']);
        $template->setValue('CHASIS', $data['vehiculo']['chasis']);
        $template->setValue('MOTOR', $data['vehiculo']['motor']);
        $template->setValue('COLOR', $data['vehiculo']['color']);
        $template->setValue('ANIO', $data['vehiculo']['anio']);
        $template->setValue('CILINDRAJE', $data['vehiculo']['cilindraje']);
        $template->setValue('COMBUSTIBLE', $data['vehiculo']['combustible']);

        // Fecha contractual (congelada)
        $template->setValue('DIAS', $data['fecha']['dia']);
        $template->setValue('MES', $data['fecha']['mes']);
        $template->setValue('ANIO_ACTUAL', $data['fecha']['anio']);

        $template->setValue('DIAS_LETRAS', FechanumeroALetras((int)$data['fecha']['dia']));
        $template->setValue('MES_LETRAS', mesEnLetras((int)$data['fecha']['mes']));
        $template->setValue('ANIO_LETRAS', FechanumeroALetras((int)$data['fecha']['anio']));

        appLog('Template procesado desde JSON');

        /* =========================
           4️⃣ GENERAR DOCX
        ========================== */
        $tmpDir  = sys_get_temp_dir();
        $tmpDocx = $tmpDir . '/contrato_' . $id_venta . '_' . time() . '.docx';

        $template->saveAs($tmpDocx);

        if (!file_exists($tmpDocx)) {
            throw new RuntimeException('No se pudo generar el DOCX');
        }

        /* =========================
           5️⃣ CONVERTIR A PDF
        ========================== */
        $pdfPath = convertirDocxAPdf($tmpDocx);

        if (!file_exists($pdfPath)) {
            throw new RuntimeException('No se pudo generar el PDF');
        }

        /* =========================
           6️⃣ DESCARGA
        ========================== */
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header(
            'Content-Disposition: attachment; filename="Contrato_' .
            $data['meta']['numero_contrato'] . '.pdf"'
        );
        header('Content-Length: ' . filesize($pdfPath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($pdfPath);

        unlink($tmpDocx);
        unlink($pdfPath);

        exit;

    } catch (Throwable $e) {

        appLog('ERROR descargarVentaPDF: ' . $e->getMessage());

        return [
            'ok' => false,
            'error' => $e->getMessage()
        ];
    }
}

function descargarVentaPDFReimpresion($id_contrato, $reimpresion, $juridico, $soloValidar = false)
{
    try {
        appLog('===== descargarVentaPDF INICIO =====');

        if (empty($id_contrato)) {
            throw new RuntimeException('ID de venta inválido');
        }

        /* =========================
           1️⃣ CONTRATO ACTIVO
        ========================== */
        $resContrato = sql_select("
            SELECT datos_json
            FROM ventas_contratos_detalle
            WHERE id = $id_contrato
            ORDER BY id DESC
            LIMIT 1
        ");

        if (!$resContrato || $resContrato->num_rows === 0) {
            throw new RuntimeException('Contrato activo no encontrado');
        }

        $data = json_decode(
            $resContrato->fetch_assoc()['datos_json'],
            true
        );

        if (!$data) {
            throw new RuntimeException('JSON del contrato inválido');
        }

        // 🔹 Modo AJAX (solo validar)
        if ($soloValidar === true) {
            return [
                'ok' => true,
                'numero_contrato' => $data['meta']['numero_contrato']
            ];
        }

        /* =========================
           2️⃣ TEMPLATE DOCX
        ========================== */
        //$templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo.docx';

        if($reimpresion==1)
            {
                if($juridico)
                {
                    $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PJ_NULO_v2.docx';
                }else{
                    $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PN_NULO_v2.docx';
                }
            }else{
                if($juridico)
                {
                    $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PJ_v2.docx';
                }else{
                    $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PN_v2.docx';
                }
            }




        if (!file_exists($templatePath)) {
            throw new RuntimeException('Template DOCX no encontrado');
        }

        $template = new TemplateProcessor($templatePath);

        /* =========================
           3️⃣ REEMPLAZOS (DESDE JSON)
        ========================== */

        //correlativo
        $template->setValue('CORRELATIVO', $data['meta']['numero_contrato']);   


        // Representante
        $template->setValue('REPRESENTANTE_LEGAL', $data['representante']['nombre']);
        $template->setValue('R_IDENTIDAD', $data['representante']['identidad']);
        
        //$template->setValue('CIUDAD', $data['representante']['ciudad']);

        //$template->setValue('DEPARTAMENTO', $data['representante']['departamento']);

        

        //datos globales
        $template->setValue('PROFESION_COMPRADOR', $data['datos_juridicos']['representante_legal_profesion']);
        $template->setValue('NACIONALIDAD', $data['cliente']['nacionalidad']);

        //$template->setValue('CIUDAD', $data['cliente']['ciudad']);

        
        $template->setValue('CIUDAD_C', $data['cliente']['ciudad']);


        //$template->setValue('DEPARTAMENTO', $data['cliente']['departamento']);

        $template->setValue('CIUDAD', $data['representante']['ciudad']);
        $template->setValue('DEPARTAMENTO', $data['representante']['departamento']);



        if($juridico)
            {
                    if($data['cliente']['tipo_documento_ident_venta'] == 'dni')
                    {
                        $desc='con documento nacional de identificacion numero '.$data['datos_juridicos']['representante_legal_identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);   
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte')
                    {
                        $desc='con pasaporte numero '.$data['datos_juridicos']['representante_legal_identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente')
                    {
                        $desc='con carnet de residente numero '.$data['datos_juridicos']['representante_legal_identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }

            }else{
                    if($data['cliente']['tipo_documento_ident_venta'] == 'dni')
                    {
                        $desc='con documento nacional de identificacion numero '.$data['cliente']['identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);   
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte')
                    {
                        $desc='con pasaporte numero '.$data['cliente']['identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }else if($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente')
                    {
                        $desc='con carnet de residente numero '.$data['cliente']['identidad'];
                        $template->setValue('DESC_DOCUMENTO', $desc);
                    }

            }

        //datos juridicos
        $template->setValue('R_LEGAL_J', $data['datos_juridicos']['representante_legal']);
        $template->setValue('R_LEGAL_PROFESION_J', $data['datos_juridicos']['representante_legal_profesion']);
        $template->setValue('R_LEGAL_DENTIDAD_J', $data['datos_juridicos']['representante_legal_identidad']);
        $template->setValue('R_LEGAL_DIR', $data['datos_juridicos']['representante_legal_direccion']);
        //datos cliente juridico
        $template->setValue('NOMBRE_EMPRESA_J', $data['cliente']['nombre']);
        $template->setValue('EMPRESA_RTN_J', $data['cliente']['identidad']);
        $template->setValue('COD_CLIENTE_EMPRESA_J', $data['cliente']['codigo']);
        $template->setValue('EMPRESA_TELEFONO', $data['cliente']['telefono']);


        // Cliente
        $template->setValue('CLIENTE', $data['cliente']['nombre']);
        $template->setValue('IDENTIDAD_CLIENTE', $data['cliente']['identidad']);
        $template->setValue('CODIGO_CLIENTE', $data['cliente']['codigo']);
        $template->setValue('DIRECCION_CLIENTE', $data['cliente']['direccion']);
        $template->setValue('TELEFONO_CLIENTE', $data['cliente']['telefono']);


        

        // Precios
        $template->setValue(
            'PRECIO_VENTA',
            number_format($data['precios']['precio_venta'], 2, '.', ',')
        );
        $template->setValue(
            'PRECIO_VENTA_LETRAS',
            $data['precios']['precio_venta_letras']
        );

        $template->setValue(
            'PRIMA_VENTA',
            number_format($data['precios']['prima_venta'], 2, '.', ',')
        );
        $template->setValue(
            'PRIMA_VENTA_LETRAS',
            $data['precios']['prima_venta_letras']
        );

        // Vehículo
        $template->setValue('CODIGO_VEHICULO', $data['vehiculo']['codigo']);
        $template->setValue('PLACA', $data['vehiculo']['placa']);
        $template->setValue('MARCA', $data['vehiculo']['marca']);
        $template->setValue('MODELO', $data['vehiculo']['modelo']);
        $template->setValue('TIPO', $data['vehiculo']['tipo']);
        $template->setValue('CHASIS', $data['vehiculo']['chasis']);
        $template->setValue('MOTOR', $data['vehiculo']['motor']);
        $template->setValue('COLOR', $data['vehiculo']['color']);
        $template->setValue('ANIO', $data['vehiculo']['anio']);
        $template->setValue('CILINDRAJE', $data['vehiculo']['cilindraje']);
        $template->setValue('COMBUSTIBLE', $data['vehiculo']['combustible']);

        // Fecha contractual (congelada)
        $template->setValue('DIAS', $data['fecha']['dia']);
        $template->setValue('MES', $data['fecha']['mes']);
        $template->setValue('ANIO_ACTUAL', $data['fecha']['anio']);

        $template->setValue('DIAS_LETRAS', FechanumeroALetras((int)$data['fecha']['dia']));
        $template->setValue('MES_LETRAS', mesEnLetras((int)$data['fecha']['mes']));
        $template->setValue('ANIO_LETRAS', FechanumeroALetras((int)$data['fecha']['anio']));

        appLog('Template procesado desde JSON');

        /* =========================
           4️⃣ GENERAR DOCX
        ========================== */
        $tmpDir  = sys_get_temp_dir();
        $tmpDocx = $tmpDir . '/contrato_' . $id_contrato . '_' . time() . '.docx';

        $template->saveAs($tmpDocx);

        if (!file_exists($tmpDocx)) {
            throw new RuntimeException('No se pudo generar el DOCX');
        }

        /* =========================
           5️⃣ CONVERTIR A PDF
        ========================== */
        $pdfPath = convertirDocxAPdf($tmpDocx);

        if (!file_exists($pdfPath)) {
            throw new RuntimeException('No se pudo generar el PDF');
        }

        /* =========================
           6️⃣ DESCARGA
        ========================== */
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header(
            'Content-Disposition: attachment; filename="Contrato_' .
            $data['meta']['numero_contrato'] . '.pdf"'
        );
        header('Content-Length: ' . filesize($pdfPath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($pdfPath);

        unlink($tmpDocx);
        unlink($pdfPath);

        exit;

    } catch (Throwable $e) {

        appLog('ERROR descargarVentaPDF: ' . $e->getMessage());

        return [
            'ok' => false,
            'error' => $e->getMessage()
        ];
    }
}


pagina_permiso(178);


//contratos

if (isset($_GET['a']) && $_GET['a'] === 'anularcontrato') {
        $id_venta = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        $id_usuario = intval($_SESSION['usuario_id']);

                $resUser = sql_select("
            SELECT
                u.usuario
            FROM usuario u
            WHERE u.id = $id_usuario
            LIMIT 1
        ");

        $user = $resUser->fetch_assoc();

        $usuarioSistema = $user['usuario'];


        $resp = anularContratoVenta($id_venta, $usuarioSistema);

        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;


}

if (isset($_GET['a']) && $_GET['a'] === 'actcontrato') {

        //$id_venta = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        $id=0;
        $id_venta = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $numeroVenta = isset($_GET['numeroVenta']) ? intval($_GET['numeroVenta']) : 0;

        if ($id_venta > 0) {
            $id = $id_venta;

        } elseif ($numeroVenta > 0) {

            $res = sql_select("SELECT id FROM ventas WHERE numero = $numeroVenta LIMIT 1");

            if ($res && $row = $res->fetch_assoc()) {
                $id = intval($row['id']);
            } else {
                $id = 0; // no encontró
            }

        } else {
            $id = 0; // no vino nada
        }


        $persona_juridica = isset($_REQUEST['persona_juridica'])? (bool) $_REQUEST['persona_juridica']: false;
        $id_usuario=$_SESSION['usuario_id'];



        $id_usuario = intval($_SESSION['usuario_id']);

        $resUser = sql_select("
            SELECT
                u.usuario,
                u.nombre,
                u.tienda_id,
                t.rentworks_almacen AS tienda_nombre
            FROM usuario u
            LEFT JOIN tienda_agencia t ON u.tienda_id = t.tienda_id
            WHERE u.id = $id_usuario
            LIMIT 1
        ");

        $user = $resUser->fetch_assoc();

        $partes = explode(' ', trim($user['nombre']), 2);

        $nombreUsuario   = $partes[0];
        $apellidoUsuario = $partes[1] ?? '';
        //$ubicacion = strtoupper(trim($user['tienda_nombre']));
        $usuarioSistema = $user['usuario'];

        $resp = generarContratoVenta(
            $id,
            $nombreUsuario,
            $apellidoUsuario,
            $usuarioSistema,
            $persona_juridica
        );

        header('Content-Type: application/json');
        
        // 🔥 limpia absolutamente todo buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        echo json_encode($resp);
        exit;
}

// VALIDAR (AJAX)
    if ($_GET['a'] === 'print_check') {

        $id=0;
        $id_venta = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $numeroVenta = isset($_GET['numeroVenta']) ? intval($_GET['numeroVenta']) : 0;

        if ($id_venta > 0) {
            $id = $id_venta;

        } elseif ($numeroVenta > 0) {

            $res = sql_select("SELECT id FROM ventas WHERE numero = $numeroVenta LIMIT 1");

            if ($res && $row = $res->fetch_assoc()) {
                $id = intval($row['id']);
            } else {
                $id = 0; // no encontró
            }

        } else {
            $id = 0; // no vino nada
        }


        //$id_contrato = intval($_GET['id_contrato']);
        $id_contrato = intval($_GET['id_contrato'] ?? 0);

        $persona_juridica = isset($_REQUEST['persona_juridica'])? (bool) $_REQUEST['persona_juridica']: false;

         //$reimpresion = isset($_REQUEST['reimpresion'])? (bool) $_REQUEST['reimpresion']: false;

         $reimpresion = isset($_REQUEST['reimpresion']) ? (int)$_REQUEST['reimpresion'] : 0;
         $reimpresion=0;

        if($reimpresion==1){
            echo json_encode(descargarVentaPDFReimpresion($id_contrato,$reimpresion,$persona_juridica, true));
        } else {
            echo json_encode(descargarVentaPDF($id,$persona_juridica, true));
        }


        //echo json_encode(descargarVentaPDF($id,$persona_juridica, true));
        exit;
    }

    // DESCARGAR (NAVEGADOR)
    if ($_GET['a'] === 'print') {
        //$id = intval($_GET['id']);

        $id=0;
        $id_venta = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $numeroVenta = isset($_GET['numeroVenta']) ? intval($_GET['numeroVenta']) : 0;

        if ($id_venta > 0) {
            $id = $id_venta;

        } elseif ($numeroVenta > 0) {

            $res = sql_select("SELECT id FROM ventas WHERE numero = $numeroVenta LIMIT 1");

            if ($res && $row = $res->fetch_assoc()) {
                $id = intval($row['id']);
            } else {
                $id = 0; // no encontró
            }

        } else {
            $id = 0; // no vino nada
        }


        //$id_contrato = intval($_GET['id_contrato']);
        $id_contrato = intval($_GET['id_contrato'] ?? 0);
        $persona_juridica = isset($_REQUEST['persona_juridica'])? (bool) $_REQUEST['persona_juridica']: false;

        //$reimpresion = isset($_REQUEST['reimpresion'])? (bool) $_REQUEST['reimpresion']: false;

        $reimpresion = isset($_REQUEST['reimpresion']) ? (int)$_REQUEST['reimpresion'] : 0;
        $reimpresion=0;

        if($reimpresion==1){
            descargarVentaPDFReimpresion($id_contrato,$reimpresion,$persona_juridica); // descarga real
        } else {
            descargarVentaPDF($id,$persona_juridica); // descarga real
        }
        exit;
    }

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}
$disable_sec1=' ';    
$disable_sec2=' ';    

function registrar_historial_ventas($cid, $id_estado, $nombre, $observaciones='') {
    $cid = intval($cid);
    $id_estado = intval($id_estado);
    $uid = intval($_SESSION['usuario_id']);
    $sql = "INSERT INTO ventas_historial_estado (id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
            VALUES (
                $cid,
                $uid,
                $id_estado,
                ".GetSQLValue($nombre, "text").",
                NOW(),
                ".GetSQLValue($observaciones, "text")."
            )";
    return sql_insert($sql);
}

// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT ventas.*    
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

        FROM ventas
        LEFT OUTER JOIN tienda ON (ventas.id_tienda=tienda.id)        
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)        
        LEFT OUTER JOIN ventas_estado estado1 ON (ventas.id_estado_pintura=estado1.id)
        LEFT OUTER JOIN ventas_estado estado2 ON (ventas.id_estado_interior=estado2.id)
        LEFT OUTER JOIN ventas_estado estado3 ON (ventas.id_estado_mecanica=estado3.id)
        LEFT OUTER JOIN entidad ON (ventas.cliente_id=entidad.id)
    where ventas.id=$cid limit 1");

	if ($result!=false){
		if ($result -> num_rows > 0) { 
			$row = $result -> fetch_assoc(); 
		}
	}

} // fin leer datos

// borrar     ############################  
if ($accion=="del") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Error";
    $stud_arr[0]["pcid"] = 0;
    
if (!tiene_permiso(168)) {
        $stud_arr[0]["pmsg"] ="No tiene privilegios para Borrar";
    } else {
        $cid=0;
        if (isset($_REQUEST['id'])) { $cid = intval($_REQUEST["id"]); }
            $result = sql_select("SELECT id_estado FROM ventas where id=$cid limit 1");

            if ($result!=false){
                if ($result -> num_rows > 0) { 
                    $row = $result -> fetch_assoc(); 
                    if ($row['id_estado']==$estado_global_nuevo) {
                        borrar_foto_directorio($cid,"","","vehiculos_reparacion");
                        borrar_foto_directorio($cid,"","","vehiculos_reparacion_televentas");
                        sql_delete("DELETE FROM ventas where tipo_ventas_reparacion=1 and id=$cid limit 1");                    
                        $stud_arr[0]["pcode"] = 1;
                        $stud_arr[0]["pmsg"] ="Anulada";
                    } else {
                        $stud_arr[0]["pmsg"] ="No puede Borrar porque, la orden ya ha sido completada";
                    }
                }
           }
    }


	salida_json($stud_arr);
 	exit;

}

// guardar Datos    ############################  
if ($accion=="g") {
 //sleep(3);
	$stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR";

    //Validar
	$verror="";
    $cid=intval($_REQUEST["id"]);

    $verror.=validar("Sucursal",$_REQUEST['id_tienda'], "int", true);
    $verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
    $verror.=validar("Kilomatraje",$_REQUEST['kilometraje'], "int", true);
    $verror.=validar("Observaciones",$_REQUEST['observaciones_reparacion'], "text", true);
    $verror.=validar("Trasmision",$_REQUEST['trasmision'], "text", true);   

    if (es_nulo($cid)){
        $id_producto=intval($_REQUEST['id_producto']);
        $vehiculo=get_dato_sql("ventas","count(*)"," where id_producto=".$id_producto);
        if (!es_nulo($vehiculo) && es_nulo($cid)){ $verror.='Vehiculo ya esta registrado'; }   
    }  
     
    if ((tiene_permiso(169))){
       $precio_minimo=intval($_REQUEST['precio_minimo']);
       $precio_maximo=intval($_REQUEST['precio_maximo']);
       if ($precio_minimo>$precio_maximo or $precio_maximo<$precio_minimo){
             $verror.='El Precio Minimo no puede ser mayor al Precio Maximo o viceversa.<br>'; 
       }
    }

            // NUEVAS VALIDACIONES DE FECHAS - Ing. Ricardo Lagos
            $fecha_asignacion = $_REQUEST['fecha_asignacion'];
            $fecha_promesa_taller = $_REQUEST['fecha_promesa_taller'];
            $fecha_promesa = $_REQUEST['fecha_promesa'];

            // Validar que fecha_promesa_taller NO sea menor que fecha_asignacion y fecha_promesa
            if (!es_nulo($fecha_promesa_taller) && !es_nulo($fecha_asignacion)) {
                if (strtotime($fecha_promesa_taller) < strtotime($fecha_asignacion)) {
                    $verror .= 'La Fecha Promesa Taller no puede ser menor que la Fecha de Asignación.<br>';
                }
            }

            // Validar que fecha_promesa NO sea menor que fecha_promesa_taller y fecha_asignacion
            if (!es_nulo($fecha_promesa) && !es_nulo($fecha_promesa_taller)) {
                if (strtotime($fecha_promesa) < strtotime($fecha_promesa_taller)) {
                    $verror .= 'La Fecha Promesa Operaciones no puede ser menor que la Fecha Promesa Taller.<br>';
                }
            }

            if (!es_nulo($fecha_promesa) && !es_nulo($fecha_asignacion)) {
                if (strtotime($fecha_promesa) < strtotime($fecha_asignacion)) {
                    $verror .= 'La Fecha Promesa Operaciones no puede ser menor que la Fecha de Asignación.<br>';
                }
            }

    // Ing. Ricardo Lagos NUEVA VALIDACIÓN: Si hay foto, no permitir cambiar id_vendedor, pero permitir si estaba vacío
        if (!es_nulo($cid)) {
            $foto_actual = get_dato_sql("ventas", "foto", " where id=".$cid);
            $id_vendedor = get_dato_sql("ventas", "id_vendedor", " where id=".$cid);
            
            // Si hay foto y se está intentando cambiar el vendedor (solo si ya tenía un vendedor asignado)
            if (!es_nulo($foto_actual) && !es_nulo($id_vendedor) && $id_vendedor != intval($_REQUEST['id_vendedor'])) {
                $verror .= 'No puede cambiar el vendedor cuando ya existe una foto/documento adjunto.<br>';
            }
        }

    $id_estado = intval($_REQUEST['id_estado'] ?? 0);
    $foto_comprobante=isset($_REQUEST['foto'])? (bool) $_REQUEST['foto']: false;
    $foto_actual = get_dato_sql("ventas", "foto", " where id=".$cid);

    $persona_juridica = intval($_REQUEST['persona_juridica'] ?? 0);
    $precio_venta_raw = $_REQUEST['precio_venta'] ?? '';
    $prima_venta_raw  = $_REQUEST['prima_venta'] ?? '';

    $precio_minimo=intval($_REQUEST['precio_minimo']);     
    $precio_maximo=intval($_REQUEST['precio_maximo']);    
    $precio_venta = intval($precio_venta_raw);

    $id_vendedor=intval($_REQUEST['id_vendedor']);

    $prima_venta  = intval($prima_venta_raw);

    if ($verror == "") {

        if ($id_estado == $estado_global_negociacion || $id_estado == 20) {



            $client_id_val = isset($_REQUEST['cliente_id'])
                ? (int) $_REQUEST['cliente_id']
                : 0;

            if ($client_id_val <= 0) {
                $verror = 'Seleccione un cliente.';
            }
            else if (trim($precio_venta_raw) === '') {
                $verror = 'Ingrese el precio de venta del vehículo.';
            }
            else if (trim($prima_venta_raw) === '') {
                $verror = 'Ingrese la prima de venta del vehículo.';
            }
            else if ($precio_minimo <= 0) {
                $verror = 'Ingrese el precio mínimo.';
            }
            else if ($precio_maximo <= 0) {
                $verror = 'Ingrese el precio máximo.';
            }
            else if ($precio_minimo > $precio_maximo) {
                $verror = 'El precio mínimo no puede ser mayor que el máximo.';
            }
            else if ($precio_venta < $precio_minimo || $precio_venta > $precio_maximo) {
                $verror = 'El precio de venta debe estar entre el mínimo y el máximo.';
            }
            else if (empty(trim($_REQUEST['representante_legal_profesion'] ?? ''))) {
                $verror = 'La profesion u oficio del comprador es obligatoria.';
            }else if($id_vendedor <= 0){
                    $verror .= 'Seleccione un vendedor. ';
            }
            else if ($persona_juridica == 1 && empty(trim($_REQUEST['representante_legal_persona_juridica'] ?? ''))) {
                $verror = 'El Representante Legal es obligatorio.';
            }
            else if ($persona_juridica == 1 && empty(trim($_REQUEST['representante_legal_identidad'] ?? ''))) {
                $verror = 'La Identidad del Representante Legal es obligatoria.';
            }
            else if ($persona_juridica == 1 && empty(trim($_REQUEST['representante_legal_direccion'] ?? ''))) {
                $verror = 'La direccion del Representante Legal es obligatoria.';
            }if (empty($foto_actual) && !$foto_comprobante) {
                $verror = 'Debe adjuntar comprobante cuando el estado es negociación.';
            }

        }
}

        




    
    if ($verror=="") {
        //Campos
        $sqlcampos="";


        $nuevoregistro=false;
        $cid= intval($_REQUEST["id"]);
        if (es_nulo($cid)) {
            $nuevoregistro=true;
        }     

        if (isset($_REQUEST["id_producto"])) { $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
        if (isset($_REQUEST["id_tienda"])) { $sqlcampos.= " , id_tienda =".GetSQLValue($_REQUEST["id_tienda"],"int"); }    
        if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
        if (isset($_REQUEST["id_estado_pintura"])) { $sqlcampos.= " , id_estado_pintura =".GetSQLValue($_REQUEST["id_estado_pintura"],"int"); } 
        if (isset($_REQUEST["id_estado_interior"])) { $sqlcampos.= " , id_estado_interior =".GetSQLValue($_REQUEST["id_estado_interior"],"int"); } 
        if (isset($_REQUEST["id_estado_mecanica"])) { $sqlcampos.= " , id_estado_mecanica =".GetSQLValue($_REQUEST["id_estado_mecanica"],"int"); } 
        if (isset($_REQUEST["observaciones_reparacion"])) { $sqlcampos.= " , observaciones_reparacion =".GetSQLValue($_REQUEST["observaciones_reparacion"],"text"); } 
        if (isset($_REQUEST["fecha_asignacion"])) { $sqlcampos.= " , fecha_asignacion =".GetSQLValue($_REQUEST["fecha_asignacion"],"date"); }      
        if (isset($_REQUEST["fecha_promesa"])) { $sqlcampos.= " , fecha_promesa =".GetSQLValue($_REQUEST["fecha_promesa"],"date"); }                            
        if (isset($_REQUEST["foto"])) { $sqlcampos.= " , foto =".GetSQLValue($_REQUEST["foto"],"text"); } 
        if (isset($_REQUEST["foto_televentas"])) { $sqlcampos.= " , foto_televentas =".GetSQLValue($_REQUEST["foto_televentas"],"text"); } 
        if (isset($_REQUEST["precio_minimo"])) { $sqlcampos.= " , precio_minimo =".GetSQLValue($_REQUEST["precio_minimo"],"int"); } 
        if (isset($_REQUEST["precio_maximo"])) { $sqlcampos.= " , precio_maximo =".GetSQLValue($_REQUEST["precio_maximo"],"int"); } 
        if (isset($_REQUEST["trasmision"])) { $sqlcampos.= " , trasmision =".GetSQLValue($_REQUEST["trasmision"],"text"); } 
        if (isset($_REQUEST["fecha_promesa_taller"])) { $sqlcampos.= " , fecha_promesa_taller =".GetSQLValue($_REQUEST["fecha_promesa_taller"],"date"); }                            
        if (isset($_REQUEST["id_vendedor"])) { $sqlcampos.= " , id_vendedor =".GetSQLValue($_REQUEST["id_vendedor"],"int"); } 





        //info de contrato
        if($id_estado==$estado_global_negociacion || $id_estado==20){
            $rep_profesion   = trim($_REQUEST['representante_legal_profesion'] ?? '');
            $sqlcampos .= " , representante_legal_profesion = ". GetSQLValue($rep_profesion, "text");
        if (isset($_REQUEST["precio_venta"])) { $sqlcampos.= " , precio_venta =".GetSQLValue($_REQUEST["precio_venta"],"int"); } 
        if (isset($_REQUEST["prima_venta"])) { $sqlcampos.= " , prima_venta =".GetSQLValue($_REQUEST["prima_venta"],"int"); }
        if (isset($_REQUEST["tipo_documento_ident_venta"])) { $sqlcampos.= " , tipo_documento_ident_venta =".GetSQLValue($_REQUEST["tipo_documento_ident_venta"],"text"); } 
        if (isset($_REQUEST["nacionalidad_venta"])) { $sqlcampos.= " , nacionalidad_venta =".GetSQLValue($_REQUEST["nacionalidad_venta"],"text"); } 

            if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); }  
        }else{
            $sqlcampos.= " , cliente_id =null, representante_legal_profesion = null,tipo_documento_ident_venta=null, nacionalidad_venta=null, ciudad_venta=null,departamento_venta=null, precio_venta=null, prima_venta=null";
        }
        






        $estado_nuevo = intval($_REQUEST['id_estado']);
        
        if ($persona_juridica == 1 && ($id_estado==11 || $id_estado==20)) {



            if (isset($_REQUEST["persona_juridica"])) { $sqlcampos.= " , persona_juridica =".GetSQLValue($_REQUEST["persona_juridica"],"int"); } 

            $rep_legal = trim($_REQUEST['representante_legal_persona_juridica'] ?? '');
            $rep_id    = trim($_REQUEST['representante_legal_identidad'] ?? '');
            
            $rep_direccion    = trim($_REQUEST['representante_legal_direccion'] ?? '');

            $sqlcampos .= " , representante_legal_persona_juridica = "
                        . GetSQLValue($rep_legal, "text");

            $sqlcampos .= " , representante_legal_identidad = "
                        . GetSQLValue($rep_id, "text");



             $sqlcampos .= " , representante_legal_direccion = "
            . GetSQLValue($rep_direccion, "text");



        } else {

            // Si NO es persona jurídica, limpiamos los campos
            $sqlcampos .= " , persona_juridica =0";
            $sqlcampos .= " , representante_legal_persona_juridica = NULL";
            $sqlcampos .= " , representante_legal_identidad = NULL";
            //$sqlcampos .= " , representante_legal_profesion = NULL";
            $sqlcampos .= " , representante_legal_direccion = NULL";
            
        }




        $estadocompletar="";
        if (isset($_REQUEST['est'])) { $estadocompletar = trim($_REQUEST["est"]); }



        //$estado_nuevo = intval($_REQUEST["id_estado_anterior_reproceso"]);

        //$estado_nuevo = intval($_REQUEST['id_estado']);
        //$estado_nuevo=99;

        if ($estado_nuevo == $estado_global_negociacion || $estado_nuevo == 20) {
                $sqlcampos .= " , id_estado=" . $estado_nuevo; 
            }
            else if (es_nulo($estadocompletar) || $estadocompletar != 'cmp') {
                /*$sqlcampos .= " , id_estado = NULL";*/
                $sqlcampos.=" ,id_estado=".$estado_global_nuevo;
            }

        if (!es_nulo($estadocompletar) && $estadocompletar=='cmp'){
             if (isset($_REQUEST["id_estado_anterior_reproceso"])) {
                $id_estado_anterior_reproceso = intval($_REQUEST["id_estado_anterior_reproceso"]); 
             }else{
                $id_estado_anterior_reproceso = 0; 
             }
             
            if($estado_nuevo==$estado_global_negociacion || $estado_nuevo==20){
                $sqlcampos.= " , id_estado=".$estado_nuevo; 
            }else{
                $sqlcampos.= " , id_estado=".$id_estado_anterior_reproceso; 
            }
             
             $sqlcampos.= ", tipo_ventas_reparacion=2";
             $sqlcampos.= ", reproceso='' ";  
             $sqlcampos.= ", fecha_reparacion_completada=now() ";    		  	 
        }

        if ($nuevoregistro==false) {    
            //si modifica se guarda el registo del cambio
            $venta_actual = array();
            $result_actual = sql_select("SELECT id_tienda, kilometraje, id_estado_pintura, id_estado_interior, id_estado_mecanica, observaciones_reparacion, fecha_promesa, fecha_promesa_taller, precio_minimo, precio_maximo
                                         FROM ventas
                                         WHERE id=".$cid." LIMIT 1");
            if ($result_actual!=false && $result_actual->num_rows > 0) {
                $venta_actual = $result_actual->fetch_assoc();
            }

            $id_tienda = isset($venta_actual['id_tienda']) ? intval($venta_actual['id_tienda']) : 0;
            if ($id_tienda!=intval($_REQUEST['id_tienda'])){   
               $id_tienda_name=get_dato_sql("tienda","nombre"," where id=".$_REQUEST['id_tienda']);
               registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Tienda', $id_tienda_name);
            }

            $kilometraje = isset($venta_actual['kilometraje']) ? intval($venta_actual['kilometraje']) : 0;
            if ($kilometraje!=intval($_REQUEST['kilometraje'])){   
                registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Kilometraje', $_REQUEST['kilometraje']);
            }

             $id_pintura = isset($venta_actual['id_estado_pintura']) ? intval($venta_actual['id_estado_pintura']) : 0;
             if ($id_pintura!=intval($_REQUEST['id_estado_pintura'])){   
                 $id_pintura_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado_pintura']);
                 registrar_historial_ventas($cid, $_REQUEST['id_estado_pintura'], 'Modificacion de Estado de Pintura', $id_pintura_name);
             }

             $id_interior = isset($venta_actual['id_estado_interior']) ? intval($venta_actual['id_estado_interior']) : 0;
             if ($id_interior!=intval($_REQUEST['id_estado_interior'])){   
                 $id_interior_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado_interior']);
                 registrar_historial_ventas($cid, $_REQUEST['id_estado_interior'], 'Modificacion de Estado de Interior', $id_interior_name);
             }            

             $id_mecanica = isset($venta_actual['id_estado_mecanica']) ? intval($venta_actual['id_estado_mecanica']) : 0;
             if ($id_mecanica!=intval($_REQUEST['id_estado_mecanica'])){   
                $id_mecanica_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado_mecanica']);
                 registrar_historial_ventas($cid, $_REQUEST['id_estado_mecanica'], 'Modificacion de Estado de Mecanica', $id_mecanica_name);
             }

             $observaciones = isset($venta_actual['observaciones_reparacion']) ? trim((string)$venta_actual['observaciones_reparacion']) : '';
             if ($observaciones!=trim($_REQUEST['observaciones_reparacion'])){   
                registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Observaciones', $_REQUEST['observaciones_reparacion']);
             }

             $fecha_promesa = isset($venta_actual['fecha_promesa']) ? trim((string)$venta_actual['fecha_promesa']) : '';
             if ($fecha_promesa!=trim($_REQUEST['fecha_promesa'])){   
                registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Fecha de Promesa', $_REQUEST['fecha_promesa']);
             }

             $fecha_promesa_taller = isset($venta_actual['fecha_promesa_taller']) ? trim((string)$venta_actual['fecha_promesa_taller']) : '';
             
             if ($fecha_promesa_taller!=trim($_REQUEST['fecha_promesa_taller'])){   
                registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Fecha de Promesa Taller', $_REQUEST['fecha_promesa_taller']);
             }

             $precio_minimo = isset($venta_actual['precio_minimo']) ? intval($venta_actual['precio_minimo']) : 0;
             if ($precio_minimo!=intval($_REQUEST['precio_minimo'])){   
                 registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Precio Minimo', $_REQUEST['precio_minimo']);
             }

             $precio_maximo = isset($venta_actual['precio_maximo']) ? intval($venta_actual['precio_maximo']) : 0;
             if ($precio_maximo!=intval($_REQUEST['precio_maximo'])){   
                 registrar_historial_ventas($cid, $estado_global_nuevo, 'Modificacion de Precio Maximo', $_REQUEST['precio_maximo']);
             }         
             
            $sql="update ventas set ".$sqlcampos." where id=".$cid." limit 1";           

            $result = sql_update($sql);
        } else {
            //Crear nuevo                       
            $sqlcampos.=" ,id_usuario=".$_SESSION['usuario_id'] ;      
            /*$sqlcampos.=" ,id_estado=".$estado_global_nuevo;*/
            $sqlcampos.=" ,tipo_ventas_reparacion=1";
            $sqlcampos.=" ,numero=".GetSQLValue(get_dato_sql('ventas',"IFNULL((max(numero)+1),1)"," "),"int"); 
            
            $sql="insert into ventas set fecha=NOW(), hora=now(),".$sqlcampos." ";        
            
            $result = sql_insert($sql);
            $cid=$result; //last insert id 

            registrar_historial_ventas($cid, $estado_global_nuevo, 'Nuevo registro de vehiculo', 'Nuevo');

            registrar_historial_ventas($cid, $estado_global_nuevo, 'Nuevo registro de vehiculo', $_REQUEST['observaciones_reparacion']);

            require_once ('correo_reparacion.php');
        }

        
        if ($result!=false){
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ="Guardado";
            $stud_arr[0]["pcid"] = $cid;               
        }
        
        //Correo Completar
        if ($estadocompletar=='cmp'){
            require_once ('correo_reparacion.php');
        }    

    } else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] =$verror;
        $stud_arr[0]["pcid"] = 0;
    }

    salida_json($stud_arr);
    exit;

} // fin guardar datos


// borrar ARCHIVO o foto
if ($accion =="d") {

    $result=false;
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB101";

    if (isset($_REQUEST['arch'])) { $arch = "and archivo=".GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}

    if (isset($_REQUEST['cod'])) { $cod = "and id=".GetSQLValue(urldecode($_REQUEST["cod"]),"text"); } else	{$cod ="" ;}

    if (isset($_REQUEST['cod'])) { $cid =GetSQLValue(urldecode($_REQUEST["cod"]),"text"); } else	{$cid ="" ;}
    if (isset($_REQUEST['tipo_foto'])) { $tipo_foto = trim($_REQUEST["tipo_foto"]); } else {$tipo_foto = "foto";}
    

    if ($cid<>'') {
        if ($tipo_foto==='foto_televentas') {
            $sql="UPDATE ventas SET foto_televentas=null where id=".$cid." limit 1";
            BORRAR_FOTO_DIRECTORIO($cid, $arch, $tipo_foto, "vehiculos_reparacion_televentas");
        } else {
            $sql="UPDATE ventas SET foto=null where id=".$cid." limit 1";
            BORRAR_FOTO_DIRECTORIO($cid, $arch, $tipo_foto, "vehiculos_reparacion");
        }        
        $result = sql_update($sql);

    } else {
        $result==false;
            $stud_arr[0]["pcode"] = 0;
            $stud_arr[0]["pmsg"] ="Error al borrar el archivo";
    }

    if ($result!=false){

        $stud_arr[0]["pcode"] = 1;

        $stud_arr[0]["pmsg"] ="Borrado";

    }
    salida_json($stud_arr);
    exit;
}




?>

<div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      <li class="nav-item">
        <a class="nav-link " id="insp_tabhistorial" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_historial');"   role="tab"  >Historial</a>
      </li> 
    </ul>   
 </div>

<div class="maxancho800 mx-auto">

<div class="tab-content" id="nav-tabContent">
<!-- DETALLE  -->
<div class="tab-pane fade show active" id="nav_detalle" role="tabpanel" >


<div class="row">
<div class="col">
   	<div class="form-group">	   
	<form id="forma_ventas" name="forma_ventas">
	<fieldset id="fs_forma">		 
<?php 

    if (isset($row["elestado1"])) {$elestado1=$row["elestado1"];} else {$elestado1="";}
    if (isset($row["elestado2"])) {$elestado2=$row["elestado2"];} else {$elestado2="";}
    if (isset($row["elestado3"])) {$elestado3=$row["elestado3"];} else {$elestado3="";}
    if (isset($row["codvehiculo"])) {$producto_etiqueta=$row["codvehiculo"]. ' '.$row["vehiculo"];   }else {$producto_etiqueta="";}
    if (isset($row["latienda"])) {$latienda=$row["latienda"];} else {$latienda="";}
    if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
    if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
    if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= "";}
    if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
    if (isset($row["id_estado_pintura"])) {$id_estado_pintura= $row["id_estado_pintura"]; } else {$id_estado_pintura= "";}
    if (isset($row["id_estado_interior"])) {$id_estado_interior= $row["id_estado_interior"]; } else {$id_estado_interior="";}
    if (isset($row["id_estado_mecanica"])) {$id_estado_mecanica= $row["id_estado_mecanica"]; } else {$id_estado_mecanica= "";}
    if (isset($row["fecha"])) {$fecha=$row["fecha"]; } else {$fecha= "";}
    if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
    if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
    if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
    if (isset($row["id_inspeccion"])) {$id_inspeccion=$row["id_inspeccion"]; } else {$id_inspeccion= "";}
    if (isset($row["id_estado"])) {$id_estado=$row["id_estado"]; } else {$id_estado= "";}
    if (isset($row["fecha_asignacion"])) {$fecha_asignacion= $row["fecha_asignacion"]; } else {$fecha_asignacion= "";}
    if (isset($row["fecha_promesa"])) {$fecha_promesa= $row["fecha_promesa"]; } else {$fecha_promesa= "";}
    if (isset($row["fecha_promesa_taller"])) {$fecha_promesa_taller= $row["fecha_promesa_taller"]; } else {$fecha_promesa_taller= "";}
    if (isset($row["reproceso"])) {$reproceso=$row["reproceso"]; } else {$reproceso="";}
    if (isset($row["foto"])) {$foto=$row["foto"]; } else {$foto="";}
    if (isset($row["foto_televentas"])) {$foto_televentas=$row["foto_televentas"]; } else {$foto_televentas="";}
    if (isset($row["observaciones_reparacion"])) {$observaciones_reparacion=$row["observaciones_reparacion"]; } else {$observaciones_reparacion="";}
    if (isset($row["precio_minimo"])) {$precio_minimo= $row["precio_minimo"]; } else {$precio_minimo= "";}
    if (isset($row["precio_maximo"])) {$precio_maximo= $row["precio_maximo"]; } else {$precio_maximo= "";}
    if (isset($row["trasmision"])) {$trasmision= $row["trasmision"]; } else {$trasmision= "";}
    if (isset($row["id_vendedor"])) {$id_vendedor= $row["id_vendedor"]; } else {$id_vendedor= "";}
    if (isset($row["id_estado_anterior_reproceso"])) {$id_estado_anterior_reproceso= $row["id_estado_anterior_reproceso"]; } else {$id_estado_anterior_reproceso= "0";}

    //contrato info
    if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; } else {$cliente_id= "";}
    if (isset($row["cliente_nombre"])) {$cliente_nombre= $row["cliente_nombre"]; } else {$cliente_nombre= "";}

    if (isset($row["precio_venta"])) {$precio_venta= $row["precio_venta"]; } else {$precio_venta= "";}
    if (isset($row["prima_venta"])) {$prima_venta= $row["prima_venta"]; } else {$prima_venta= "";}
    if (isset($row["persona_juridica"])) {$persona_juridica= $row["persona_juridica"]; } else {$persona_juridica= "";}
    if (isset($row["representante_legal_persona_juridica"])) {$representante_legal_persona_juridica= $row["representante_legal_persona_juridica"]; } else {$representante_legal_persona_juridica= "";}
    if (isset($row["representante_legal_identidad"])) {$representante_legal_identidad= $row["representante_legal_identidad"]; } else {$representante_legal_identidad= "";}
    if (isset($row["representante_legal_profesion"])) {$representante_legal_profesion= $row["representante_legal_profesion"]; } else {$representante_legal_profesion= "";}
    if (isset($row["representante_legal_direccion"])) {$representante_legal_direccion= $row["representante_legal_direccion"]; } else {$representante_legal_direccion= "";}
    if (isset($row["tipo_documento_ident_venta"])) {$tipo_documento_ident_venta= $row["tipo_documento_ident_venta"]; } else {$tipo_documento_ident_venta= "";}
    if (isset($row["nacionalidad_venta"])) {$nacionalidad_venta= $row["nacionalidad_venta"]; } else {$nacionalidad_venta= "";}



    
    //$observaciones_reparacion= "";
    if ($id_estado=='' || $id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion){
       $disable_sec1=' ';  
       $disable_sec2=' ';  


       if (!tiene_permiso(169)){         
          $disable_sec2=' readonly="readonly" ';              
       }      
    }else{
       $disable_sec1=' disabled="disabled" ';  
       $disable_sec2=' disabled="disabled" ';  
    }

    if ( $id_estado_pintura==32 && $id_estado_interior==32 && $id_estado_mecanica==32 && ($id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion)){
         $completar="cmp";
         $NombreBotton='Completar';
    }else{
         if ($id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion || $id_estado==''){
            $completar="";
            $NombreBotton='Guardar';
         }
    }

    echo campo("id",("Codigo"),'hidden',$id,' ','');
    echo campo("id_estado_anterior_reproceso","Estado Anterior Reproceso",'hidden',$id_estado_anterior_reproceso,' ','');
?>


<div class="row">
    <div class="col-md">
        <?php echo campo("hora",("Fecha / Hora"),'label',formato_fechahora_de_mysql($hora),' ',' ');   ?>
    </div>
    
    <div class="col-md">
        <?php echo campo("numero","Numero",'label',$numero,' ',' '); ?>        
    </div>    
   

</div>

<div class="row">
    
    <div class="col-md-4">
        <?php echo campo("fecha_asignacion","Fecha de Asignacion",'date',$fecha_asignacion,' ',' required '.$disable_sec1); ?>
    </div>

    <div class="col-md-4">
        <?php echo campo("fecha_promesa_taller","Fecha Promesa Taller",'date',$fecha_promesa_taller,' ',' required '.$disable_sec1); ?>
    </div>
    
    <div class="col-md-4">
        <?php echo campo("fecha_promesa","Fecha Promesa Operaciones",'date',$fecha_promesa,' ',' required '.$disable_sec1); ?>
    </div>
    <div class="col-md">
       <?php echo campo("id_inspeccion","Numero",'hidden',$id_inspeccion,' ',' '); ?>          
    </div>

</div>

<div class="row">
    <div class="col-md-4">                
        <?php 
        if (es_nulo($id_estado) || $id_estado == $estado_global_nuevo || $id_estado == $estado_global_negociacion) {             
            echo campo("id_tienda", "Sucursal", 'select2', valores_combobox_db("tienda", $id_tienda, "nombre", " ", '', '...'), ' ', ' required ' . $disable_sec1, ''); 
        } else {
            echo campo("id_tienda", "sucursal", 'hidden', $id_tienda, '', '', '');
            echo campo("id_tienda_label", "Sucursal", 'label', $latienda, '', '', '');
        }  
        ?> 
    </div>    

    <div class="col-md-8">         
        <?php 
        if ($id_estado == '' || $id_estado == $estado_global_nuevo || $id_estado == $estado_global_negociacion) {             
            echo campo("id_producto", "Vehiculo", 'select2ajax', $id_producto, '  class=" " ', ' onchange="comb_actualizar_veh();"  ', 'get.php?a=3&t=1', $producto_etiqueta); 
        } else {
            echo campo("id_producto", "Vehiculo", 'hidden', $id_producto, '', '', '');
            echo campo("id_producto_label", "Vehiculo", 'label', $producto_etiqueta, '', '', '');
        }      
        ?>            
    </div> 
</div>    


<div class="row">
     
    
    
    <div class="col-md">
        <?php echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ',$disable_sec1); ?>        
    </div>

    <div class="col-md">
         <?php echo campo("id_vendedor","Vendedor",'select2',valores_combobox_db('usuario',$id_vendedor,'nombre',' where activo=1 and grupo_id=18 ','','...'),' ',' required '.$disable_sec2);  ?> 
    </div>
    
    <div class="col-md">
         <?php if (es_nulo($id_estado) || $id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion){            
              echo campo("id_estado_pintura","Pintura",'select2',valores_combobox_db("ventas_estado",$id_estado_pintura,"nombre"," where ventas_reparacion=1 ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_estado_pintura","pintura",'hidden',$id_estado_pintura,'','','');
              echo campo("id_pintura_label","Pintura",'label',$elestado1,'','','');
         }
         ?>         
    </div>
    <div class="col-md">
         <?php if (es_nulo($id_estado) || $id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion){ 
              echo campo("id_estado_interior","Interior",'select2',valores_combobox_db("ventas_estado",$id_estado_interior,"nombre"," where ventas_reparacion=1 ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_estado_interior","interior",'hidden',$id_estado_interior,'','','');
              echo campo("id_interior_label","Interior",'label',$elestado2,'','','');
         }
         ?>         
    </div>
    <div class="col-md">
         <?php if (es_nulo($id_estado) || $id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion){ 
              echo campo("id_estado_mecanica","Mecanica",'select2',valores_combobox_db("ventas_estado",$id_estado_mecanica,"nombre"," where ventas_reparacion=1 ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_estado_mecanica","mecanica",'hidden',$id_estado_mecanica,'','','');
              echo campo("id_mecanica_label","Mecanica",'label',$elestado3,'','','');
         }
         ?>         
    </div>
</div>  
<div class="row">
    <div class="col-md">
         <?php echo campo("trasmision","Trasmision",'select', valores_combobox_texto(app_tipo_trasmision,$trasmision),' ',$disable_sec1); ?>
    </div>
   <div class="col-md">
        <?php echo campo("precio_minimo","Precio Minimo",'number',$precio_minimo,' ',$disable_sec2); ?>        
    </div>
    <div class="col-md">
        <?php echo campo("precio_maximo","Precio Maximo",'number',$precio_maximo,' ',$disable_sec2); ?>          
    </div>    

</div>

<div class="row">
    <div class="col-md">
         <?php echo campo("id_estado","Estado",'select2',valores_combobox_db("ventas_estado",$id_estado,"nombre"," where id=11 ",'','...'),' ',' required '.$disable_sec2)  ?> 
    </div>
                <div class="col-md">            
                <?php echo campo("precio_venta","Precio de Venta",'number',$precio_venta,' ',$disable_sec2); ?>                 
            </div>   
            <div class="col-md">            
                <?php echo campo("prima_venta","Precio de Reserva",'number',$prima_venta,' ',$disable_sec2); ?>                 
            </div> 
</div>

<div class="row">
    <div id="clientediv" style="display:none;" class="col-md-12">



        <?php
        $nombre_cliente='';



        echo campo("nombre_cliente","",'hidden',$nombre_cliente,'','','');
        //echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,'class=" "','" '.$disable_sec1,'get.php?a=2&t=1',$cliente_nombre);
        //echo campo("representante_legal_profesion","Profesión u oficio de comprador",'text',$representante_legal_profesion,' ',$disable_sec2);

        //echo valores_combobox_array($opciones, 'T02', 'Seleccione una opción');
        if($nacionalidad_venta=='')
        {
            $nacionalidad_venta='hondureño';
        }
        
        ?>



        <div class="row">
            <div class="col-md-6">
                <?php echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,'class=" "','" '.$disable_sec1,'get.php?a=2&t=1',$cliente_nombre);  ?>
            </div>
            <div class="col-md-4">
                <?php echo campo("tipo_documento_ident_venta","Documento de identificacion",'select2',valores_combobox_array($tipos_docu, $tipo_documento_ident_venta, ''));  ?>
            </div>

            <div class="col-md-2">
                <?php echo campo("nacionalidad_venta","Nacionalidad",'select2',valores_combobox_array($nacionalidades, $nacionalidad_venta, ''));  ?>
            </div>
         </div>

         <div class="row">
            <div class="col-md-12">
                <?php echo campo("representante_legal_profesion","Profesión u oficio de comprador",'text',$representante_legal_profesion,' ',$disable_sec2); ?>
            </div>
            
         </div>



          <div class="row">
            <div class="col-md-12">
                <?php echo campo("persona_juridica","persona juridica",'checkboxCustom',$persona_juridica,' ',$disable_sec2); ?>
            </div>
         </div>



        <div class="row">
            <div class="col-md-6">
                <?php echo campo(
                    "representante_legal_persona_juridica",
                    "Nombre representante Legal",
                    'text',
                    $representante_legal_persona_juridica,
                    ' ',
                    $disable_sec2
                ); ?>
            </div>  

            <div class="col-md-6">
                <?php echo campo(
                    "representante_legal_identidad",
                    "Numero de identificacion de documento",
                    'text',
                    $representante_legal_identidad,
                    ' ',
                    $disable_sec2
                ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php echo campo(
                    "representante_legal_direccion",
                    "Direccion de Representante Legal",
                    'text',
                    $representante_legal_direccion,
                    ' ',
                    $disable_sec2
                ); ?>
            </div>

        </div>




        <script>
            $(document).ready(function () {
                toggleClientePorEstado();
            });
        </script>

    </div>
</div>



<div class="row">
     <div class="col-md">
         <?php echo campo("observaciones_reparacion","Observaciones",'textarea',$observaciones_reparacion,' ',' required '.$disable_sec1); ?>         
     </div>
</div>



<div class="row">
    <div class="col-md-6" id="bloque_foto_pago">
        <h6>Foto Comprobante de Pago</h6>
        <?php
        if ($foto=='') {
            echo '<div id="archivofoto">';
            echo campo_upload("foto","Adjuntar Comprobante de Pago",'upload','', '  ','',4,8,'NO',false );
            echo '</div>';
        }
        ?>
        <div id="insp_fotos_thumbs">
            <?php
            if ($foto<>'') {
                $fext = substr($foto, -3);
                if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
                    echo '  <a href="#" class="foto_br'.$row["id"].'" onclick="mostrar_foto(\''.$foto.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3 float-left" src="uploa_d/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';                    
                } else {
                    echo '  <a href="uploa_d/'.$foto.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto.'</a> ';
                }
                if (tiene_permiso(183))  {
                   echo '<a href="#" class="mr-5 foto_br'.$row["id"].'" onclick="borrar_fotodb('.$row["id"].',\'foto\'); return false;" ><i class="fa fa-eraser"></i> Borrar</a>';
                }
            }
            ?>
        </div>
    </div>

    <div class="col-md-6" id="bloque_foto_televentas">
        <h6>Foto Comprobante de Recibo de Pago</h6>
        <?php
        if ($foto_televentas=='') {
            echo '<div id="archivofoto_televentas">';
            echo campo_upload("foto_televentas","Adjuntar Recibo de Pago",'upload','', '  ','',4,8,'NO',false );
            echo '</div>';
        }
        ?>
        <div id="insp_fotos_thumbs_televentas">
        <?php
            if ($foto_televentas<>'') {
                $fext = substr($foto_televentas, -3);
                if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {
                    echo '  <a href="#" class="foto_br'.$row["id"].'" onclick="mostrar_foto(\''.$foto_televentas.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3 float-left" src="uploa_d/thumbnail/'.$foto_televentas.'" data-cod="'.$row["id"].'"></a> ';                    
                } else {
                    echo '  <a href="uploa_d/'.$foto_televentas.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto_televentas.'</a> ';
                }
                if (tiene_permiso(183))  {
                    echo '<a href="#" class="mr-5 foto_br'.$row["id"].'" onclick="borrar_fotodb('.$row["id"].',\'foto_televentas\'); return false;" ><i class="fa fa-eraser"></i> Borrar </a>';
                }
            }
        ?>
        </div>
    </div>
</div>

   <div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
    <div class="row">
        <div class="col-sm">     
            <?php if ($id_estado==$estado_global_nuevo || $id_estado==$estado_global_negociacion || $id_estado=='') {?>       
                 <a href="#" onclick="return validarYProcesar('<?php echo $completar; ?>');" class="btn btn-primary btn-block mb-2 xfrm" >
                     <i class="fa fa-check"></i> <?php echo $NombreBotton; ?>
                 </a>                           
            <?php } ?>
        </div>        
      
        <?php if (tiene_permiso(168)){ ?>
              <div class="col-sm"><a id="ventas_anularbtn"  href="#" onclick="ventas_anular(); return false;" class="btn btn-danger  btn-block mr-2 mb-2 xfrm"><i class="fa fa-trash-alt"></i> Borrar</a></div>		                                        
        <?php } ?>  

        <div class="col-sm">
            <a href="javascript:void(0);"
               id="btnContrato"
               target="_blank"
               class="btn w-100 mb-2"
               style="background-color:#e5533d;color:#fff;border:1px solid #e5533d;">
                <i class="fas fa-file-pdf"></i> Descargar contrato
            </a>
        </div>



        <?php if (!es_nulo($id_inspeccion)){ ?>            
            <a href="#" onclick="abrir_hoja(); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-file-medical-alt"></i> Abrir Inspección</a>
        <?php } ?>  

       
    </div>

        <!-- 🔹 FILA 2 -->
    <div class="row">

        <?php if ($id_estado!=20){ ?>
        <div class="col-sm">
            <a href="javascript:void(0);"
               id="btnActualizarContrato"
               class="btn w-100 mb-2"
               style="background-color:#f0ad4e;color:#fff;border:1px solid #f0ad4e;">
                <i class="fas fa-file-pdf"></i> Generar contrato
            </a>
        </div>
        <?php } ?> 

        <?php if (tiene_permiso(189)){ ?>
        <div class="col-sm">
            <a href="javascript:void(0);"
               id="btnanularContrato"
               target="_blank"
               class="btn btn-danger w-100 mb-2"
                <i class="fas fa-file-pdf"></i> Anular contrato
            </a>
        </div>
        <?php } ?> 

        <div class="col-sm">
            <a href="#"
               onclick="$('#ModalWindow2').modal('hide'); return false;"
               class="btn btn-light w-100 mb-2 xfrm">
                <?php echo 'Cerrar'; ?>
            </a>
        </div>

    </div>
  	
   
</div>

	</fieldset>
	</form>

<?php  ?>    
 
</div>

</div>

</div>


</div>

<!-- HISTORIAL -->
<div class="tab-pane fade " id="nav_historial" role="tabpanel" ></div>

<!-- errores -->
<div class="tab-pane fade mt-5 mb-5" id="nav_deshabilitado" role="tabpanel" ><div class="alert alert-warning" role="alert">Debe Guardar el documento para poder continuar con esta sección</div></div>





<script>

        function toggleClientePorEstado() {
        let valor = $('#id_estado option:selected').text().toLowerCase();
        // o si prefieres por value:
        // let valor = $('#id_estado').val();

        if (valor === 'en negociacion' || valor === 'vendido entregado') {
            $('#clientediv').slideDown();
        } else {
            $('#clientediv').slideUp();
        }
    }


$(function () {

    $('#btnContrato').on('click', function (e) {
    e.preventDefault();

    const id = $('#id').val();
    const numeroVenta = $('#numero').val();

    if (!id) {
        mytoast('error', 'No hay ID',3000);
        return;
    }

    //const persona_juridica=$('#persona_juridica').val();
    const persona_juridica = $('#persona_juridica').is(':checked') ? 1 : 0;

            popupconfirmar(
            'Confirmación',
            '¿Deseas descargar el contrato?',
            function () {

                $.ajax({
                    url: 'vehiculos_reparacion_mant.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        a: 'print_check',
                        id: id,
                        numeroVenta: numeroVenta,
                        persona_juridica: persona_juridica,
                        id_contrato: 0,
                        reimpresion: 0
                    },
                    success: function (resp) {
                        if (resp.ok) {

                            mytoast(
                                'success',
                                'Contrato listo: ' + resp.numero_contrato,
                                3000
                            );

                            // 🔥 quitar aviso de salida
                            window.onbeforeunload = null;
                            $(window).off('beforeunload');

                            // 👉 ahora sí descargar
                            window.location.href =
                                'vehiculos_reparacion_mant.php?a=print&id=' +
                                encodeURIComponent(id) +
                                '&persona_juridica=' +encodeURIComponent(persona_juridica)
                                '&id_contrato=' + encodeURIComponent(0) +
                            '&reimpresion=' + encodeURIComponent(0);


                        } else {
                            mytoast(
                                'error',
                                resp.error || 'Error al generar contrato',
                                3000
                            );
                        }
                    },
                    error: function () {
                        mytoast(
                            'error',
                            'Error de comunicación con el servidor',
                            3000
                        );
                    }
                });

            }
        );

});

$('#btnActualizarContrato').on('click', function (e) {
    e.preventDefault();

    const id = $('#id').val();
    let numeroVenta = $('#numero').val();

    const estado = $('#id_estado').val();

    if(estado==20)
    {
       mytoast('error','No puede generar contrato en estado Vendido o entregado');
       return;
    }


    //const persona_juridica=$('#persona_juridica').val();
    const persona_juridica = $('#persona_juridica').is(':checked') ? 1 : 0;

    if (!id) {
        alert('No hay ID');
        return;
    }

            popupconfirmar(
                'Confirmación',
                '¿Seguro desea generar el contrato? Los datos se sustituirán si previamente ya existía un contrato.',
                function () {

                    $.ajax({
                        url: 'vehiculos_reparacion_mant.php',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            a: 'actcontrato',
                            id: id,
                            numeroVenta: numeroVenta,
                            persona_juridica:persona_juridica
                        },
                        success: function (resp) {
                            if (resp.ok) {
                                mytoast(
                                    'success',
                                    'Contrato generado: ' + resp.numero_contrato,
                                    3000
                                );
                            } else {
                                mytoast(
                                    'error',
                                    resp.error || 'Error inesperado',
                                    3000
                                );
                            }
                        },
                        error: function () {
                            mytoast(
                                'error',
                                'Error de comunicación con el servidor',
                                3000
                            );
                        }
                    });

                }
            );

});

$('#btnanularContrato').on('click', function (e) {
    e.preventDefault();

    const id = $('#id').val();
    const numeroVenta = $('#numero').val();

    if (!id) {
        mytoast('error', 'No hay ID', 3000);
        return;
    }

    popupconfirmar(
        'Confirmación',
        '¿Seguro desea anular el contrato activo? Esta acción no se puede deshacer.',
        function () {

            $.ajax({
                url: 'vehiculos_reparacion_mant.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    a: 'anularcontrato', // 👈 acción nueva en tu backend
                    id: id,
                    numeroVenta: numeroVenta
                },
                success: function (resp) {
                    if (resp.ok) {
                        mytoast(
                            'success',
                            'Contrato anulado correctamente',
                            3000
                        );

                        // opcional: refrescar o cambiar estado en pantalla
                        // location.reload();

                    } else {
                        mytoast(
                            'error',
                            resp.error || 'Error al anular contrato',
                            3000
                        );
                    }
                },
                error: function () {
                    mytoast(
                        'error',
                        'Error de comunicación con el servidor',
                        3000
                    );
                }
            });

        }
    );
});

});




function validarYProcesar(completar) {
    // Primero validar todo
    if (!validarFechasParaGuardado()) {
        return false; // Detener el proceso si hay errores
    }
    
    // Si pasa la validación, proceder con el guardado
    procesar('vehiculos_reparacion_mant.php?a=g&est=' + completar, 'forma_ventas', '');
    return false; // Prevenir el comportamiento por defecto del enlace
}

// Validación en tiempo real de fechas (solo para mostrar errores visuales)
$('#fecha_asignacion, #fecha_promesa_taller, #fecha_promesa').on('change', function() {
    validarFechasEnTiempoReal();
});

function validarFechasEnTiempoReal() {
    var fechaAsignacion = $('#fecha_asignacion').val();
    var fechaPromesaTaller = $('#fecha_promesa_taller').val();
    var fechaPromesa = $('#fecha_promesa').val();
    
    // Resetear estilos
    $('#fecha_asignacion, #fecha_promesa_taller, #fecha_promesa').removeClass('is-invalid');
    
    var esValido = true;
    
    // Solo validar si hay Fecha Asignación (obligatoria)
    if (fechaAsignacion) {
        var asignacion = new Date(fechaAsignacion);
        
        // Validar Fecha Promesa Taller si está llena
        if (fechaPromesaTaller) {
            var promesaTaller = new Date(fechaPromesaTaller);
            
            // Aplicar estilos de error si Fecha Promesa Taller es menor que Fecha Asignación
            if (promesaTaller < asignacion) {
                $('#fecha_promesa_taller').addClass('is-invalid');
                esValido = false;
            }
            
            // Validar Fecha Operaciones si también está llena
            if (fechaPromesa) {
                var promesa = new Date(fechaPromesa);
                
                // Aplicar estilos de error si hay problemas
                if (promesa < promesaTaller) {
                    $('#fecha_promesa').addClass('is-invalid');
                    esValido = false;
                }
                if (promesa < asignacion) {
                    $('#fecha_promesa').addClass('is-invalid');
                    esValido = false;
                }
            }
        } 
        // Si solo hay Fecha Operaciones (sin Fecha Promesa Taller)
        else if (fechaPromesa) {
            var promesa = new Date(fechaPromesa);
            
            // Aplicar estilos de error si Fecha Operaciones es menor que Fecha Asignación
            if (promesa < asignacion) {
                $('#fecha_promesa').addClass('is-invalid');
                esValido = false;
            }
        }
    }
    
    return esValido;
}

// Función específica para validación al guardar (con mensajes)
function validarFechasParaGuardado() {
    var fechaAsignacion = $('#fecha_asignacion').val();
    var fechaPromesaTaller = $('#fecha_promesa_taller').val();
    var fechaPromesa = $('#fecha_promesa').val();
    
    // Validar que Fecha Asignación esté completa (es la única obligatoria)
    if (!fechaAsignacion) {
        Swal.fire({
            title: 'Fecha requerida',
            text: 'La Fecha de Asignación es obligatoria',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    var asignacion = new Date(fechaAsignacion);
    var errores = [];
    
    // Validar Fecha Promesa Taller si está llena
    if (fechaPromesaTaller) {
        var promesaTaller = new Date(fechaPromesaTaller);
        
        // Validar que Fecha Promesa Taller no sea menor que Fecha Asignación
        if (promesaTaller < asignacion) {
            errores.push('La Fecha Promesa Taller no puede ser menor que la Fecha de Asignación');
        }
        
        // Si también hay Fecha Operaciones, validar relación entre ellas
        if (fechaPromesa) {
            var promesa = new Date(fechaPromesa);
            
            // Validar que Fecha Operaciones no sea menor que Fecha Promesa Taller
            if (promesa < promesaTaller) {
                errores.push('La Fecha Operaciones no puede ser menor que la Fecha Promesa Taller');
            }
            
            // Validar que Fecha Operaciones no sea menor que Fecha Asignación
            if (promesa < asignacion) {
                errores.push('La Fecha Operaciones no puede ser menor que la Fecha de Asignación');
            }
        }
    } 
    // Si solo hay Fecha Operaciones (sin Fecha Promesa Taller)
    else if (fechaPromesa) {
        var promesa = new Date(fechaPromesa);
        
        // Validar que Fecha Operaciones no sea menor que Fecha Asignación
        if (promesa < asignacion) {
            errores.push('La Fecha Operaciones no puede ser menor que la Fecha de Asignación');
        }
    }
    
    if (errores.length > 0) {
        Swal.fire({
            title: 'Error en fechas',
            html: errores.join('<br>'),
            icon: 'error',
            confirmButtonText: 'Corregir'
        });
        return false;
    }
    
    return true;
}

function abrir_hoja(){    
    hinspeccion = $('#id_inspeccion').val();
    $('#ModalWindow2').modal('hide');
    get_page('pagina','inspeccion_mant.php?a=v&cid='+hinspeccion,'Hoja de Inspección',false);
}

function insp_guardar_foto(arch,campo){

           $('#'+campo).val(arch);                
           $('#files_'+campo).text('Guardado');
           $('#lk'+campo).html(arch);
           thumb_agregar(arch,campo);    
}


function mostrar_foto(imagen,ruta) {
  var imagenurl='uploa_d/'+imagen;
  Swal.fire({
       imageUrl: imagenurl,  
  }); 

}




function thumb_agregar(archivo,campo){
if (archivo!='' && archivo!=undefined) {

     var thumbsDiv = '#insp_fotos_thumbs';
     if (campo==='foto_televentas') {
        thumbsDiv = '#insp_fotos_thumbs_televentas';
     }
  
    var fext= archivo.substr(archivo.length - 3);

    if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
         $(thumbsDiv).append('<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a>');
    } else {
         $(thumbsDiv).append('<a href="uploa_d/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>');
    }
  }
}


function comb_actualizar_veh(){
   
    var datos=$('#id_producto').select2('data')[0];
 
$('#forma_ventas input[id=placa] ').val(datos.placa);


}

function ventas_anular(){
    Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar este vehiculo?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	     ventas_procesar('vehiculos_reparacion_mant.php?a=del','forma_ventas','del');        
	  }
	})

}

function ventas_procesar(url,forma,adicional){
   
	$("#"+forma+" .xfrm").addClass("disabled");		
	
	cargando(true); 
	
		
	var datos=$("#"+forma).serialize();

	 $.post( url,datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				cargando(false);
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
				cargando(false);
				mytoast('success',json[0].pmsg,3000) ;

					$("#"+forma+' #id').val(json[0].pcid);

                    if (adicional=="del") {
                        $('#ModalWindow2').modal('hide');
                        $( "#btn-filtro" ).click();
                    }
                    if (adicional=="dfoto") {     
                       $('#insp_fotos_thumbs').remove();                                                                 
                    }
                    
			
			}
		} else {cargando(false);  mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");}
		  
	})
	  .done(function() {
	   
	  })
	  .fail(function(xhr, status, error) {
	   		cargando(false); mymodal('error',"Error","Se produjo un error. Favor vuelva a intentar");
	  })
	  .always(function() {
	   
		$("#"+forma+" .xfrm").removeClass("disabled");	
	  });		
}

function ventas_cambiartab(eltab) {
  var codigo= $('#id').val();
  var continuar=true;
  $('.tab-pane').hide();


  if (eltab!='nav_detalle') {
    if (codigo=="0" || codigo=="") {
      continuar=false;
      $('#nav_deshabilitado').show();
      $('#nav_deshabilitado').tab('show');
    } 
  }


  if (eltab=='nav_historial') {
     procesar_ventas_historial('nav_historial');
  }
  
  if (continuar==true){
    $('#'+eltab).show();
    $('#'+eltab).tab('show');
  }

//   nav_detalle
// nav_fotos
// nav_doctos 

}

function procesar_ventas_historial(campo){

var cid=$("#id").val();
var pid=$('#id_producto').val();
var url='ventas_historial.php?cid='+cid+'&pid='+pid ;

$(window).scrollTop(0);
$("#"+campo).html('<div class="text-center mt-5 mb-5"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><span class="">'+'Cargando'+'</span></div>');			

$("#"+campo).load(url, function(response, status, xhr) {	
   
  if (status == "error") { 

    //$("#"+campo).html("Error"; // xhr.status + " " + xhr.statusText
    $("#"+campo).html('<p>&nbsp;</p>');
    mytoast('error','Error al cargar la pagina...',6000) ;
  }

});
  
}



function borrar_fotodb(codid, tipoFoto) {
    if (tipoFoto===undefined || tipoFoto==='') {
        tipoFoto = 'foto';
    }

  var datos = {
    a: "d",
    cid: $("#cid").val(),
    pid: $("#pid").val(),
        cod: codid,
        tipo_foto: tipoFoto
  };

    var etiquetaFoto = 'Foto';
    if (tipoFoto==='foto_televentas') {
        etiquetaFoto = 'Foto Televentas';
    }

  Swal.fire({
        title: 'Borrar ' + etiquetaFoto,
        text: 'Desea borrar la ' + etiquetaFoto + ' o documento adjunto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
  }).then((result) => {
    if (result.value) {


$.post( 'vehiculos_reparacion_mant.php',datos, function(response) {

                if (response.length > 0) {
                    if (response[0].pcode == 0) {
                        mytoast('error',response[0].pmsg,3000) ;   
                    }

                    if (response[0].pcode == 1) {
                        //$(".foto_br"+codid).hide();
                        procesar_tabla_datatable('tablaver','tabla','vehiculos_reparacion_ver.php?a=1','Ventas de Vehiculos')
                        mytoast('success',response[0].pmsg,3000) ;
                        abrir_ventas(codid);

                    }

                } else {mytoast('error',response[0].pmsg,3000) ; }
            })

            .done(function() {	  
                
            })

            .fail(function(xhr, status, error) {         mytoast('error',response[0].pmsg,3000) ; 	  })

            .always(function() {	  });
    }
  });
}



</script>


