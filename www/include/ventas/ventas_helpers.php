<?php
require_once('include/framework.php');
use PhpOffice\PhpWord\TemplateProcessor;

function generarContratoVenta(
    int $id_venta,
    string $nombreUsuario,
    string $apellidoUsuario,
    string $usuarioSistema,
    bool $persona_juridica,
    bool $sologuardar = false
) {
    try {

        $tipo_contrato = 0;

        if ($persona_juridica) {
            $tipo_contrato = 1;
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

        if (!$resContrato || $resContrato->num_rows >= 1) {


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
            'cliente_nombre' => 'Nombre del cliente',
            'identidad_cliente' => 'Identidad',
            'direccion_cliente' => 'Dirección',
            'codigo_cliente' => 'Código cliente',
            'telefono_cliente' => 'Teléfono',
            'ciudad_venta' => 'Ciudad'
        ], 'Falta informacion de cliente');

        validar_campos_obligatorios($venta, [
            'cod_vehiculo' => 'Código del vehículo',
            'placa' => 'Placa',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'tipo' => 'Tipo de vehículo',
            'chasis' => 'Chasis',
            'motor' => 'Motor',
            'color' => 'Color',
            'anio' => 'Año',
            'combustible' => 'Combustible',
            'cilindrada' => 'Cilindrada'
        ], 'Falta informacion del vehículo');

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
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ];

        $precioVenta = (float) $venta['precio_venta'];
        $primaVenta = (float) $venta['prima_venta'];


        try {
            $contratoJson = json_encode([
                // tu array completo
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            echo "OK";
        } catch (JsonException $e) {
            echo $e->getMessage();
        }


        try {
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
                    'tipo_documento_ident_venta' => $venta['tipo_documento_ident_venta'],
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
                    'mes' => (int) date('n'),
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
            $error = "ERROR: " . $e->getMessage();
            $temp = 1;
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

function anularContratoVenta(int $id_venta, string $usuarioSistema)
{
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

        $soffice = getSofficeCommandProd();//descomentar para produccion

        //$soffice = getSofficeCommandDev();//comentar para produccion

        appLog('SOFFICE: ' . $soffice);

        // Construir comando para convertir DOCX a PDF y agrego nueva logica de entorno al comando para evitar problemas de permisos en /tmp
        $cmd = 'HOME=/tmp ' . $soffice .
            ' -env:UserInstallation=file:///tmp/lo-profile-' . uniqid() .
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

function descargarVentaPDF($id_venta, $juridico, $soloValidar = false)
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

        if ($juridico) {
            $templatePath = __DIR__ . '/../../../plantillas/venta_contrato_vehiculo_PJ_v2.docx';
        } else {
            $templatePath = __DIR__ . '/../../../plantillas/venta_contrato_vehiculo_PN_v2.docx';
        }

        if (!file_exists($templatePath)) {
            //throw new RuntimeException('Template DOCX no encontrado');
            throw new RuntimeException('Template DOCX no encontrado: ' . $templatePath);
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

        if ($juridico) {
            if ($data['cliente']['tipo_documento_ident_venta'] == 'dni') {
                $desc = 'con documento nacional de identificacion numero ' . $data['datos_juridicos']['representante_legal_identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte') {
                $desc = 'con pasaporte numero ' . $data['datos_juridicos']['representante_legal_identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente') {
                $desc = 'con carnet de residente numero ' . $data['datos_juridicos']['representante_legal_identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            }

        } else {
            if ($data['cliente']['tipo_documento_ident_venta'] == 'dni') {
                $desc = 'con documento nacional de identificacion numero ' . $data['cliente']['identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte') {
                $desc = 'con pasaporte numero ' . $data['cliente']['identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente') {
                $desc = 'con carnet de residente numero ' . $data['cliente']['identidad'];
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

        $template->setValue('DIAS_LETRAS', FechanumeroALetras((int) $data['fecha']['dia']));
        $template->setValue('MES_LETRAS', mesEnLetras((int) $data['fecha']['mes']));
        $template->setValue('ANIO_LETRAS', FechanumeroALetras((int) $data['fecha']['anio']));

        appLog('Template procesado desde JSON');

        /* =========================
           4️⃣ GENERAR DOCX
        ========================== */
        $tmpDir = sys_get_temp_dir();

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

        if ($reimpresion == 1) {
            if ($juridico) {
                $templatePath = __DIR__ . '/../../../plantillas/venta_contrato_vehiculo_PJ_NULO_v2.docx';
            } else {
                $templatePath = __DIR__ . '/../../../plantillas/venta_contrato_vehiculo_PN_NULO_v2.docx';
            }
        } else {
            if ($juridico) {
                $templatePath = __DIR__ . '/../../../plantillas/venta_contrato_vehiculo_PJ_v2.docx';
            } else {
                $templatePath = __DIR__ . '/../../../plantillas/venta_contrato_vehiculo_PN_v2.docx';
            }
        }




        if (!file_exists($templatePath)) {
            throw new RuntimeException('Template DOCX no encontrado: ' . $templatePath);
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



        if ($juridico) {
            if ($data['cliente']['tipo_documento_ident_venta'] == 'dni') {
                $desc = 'con documento nacional de identificacion numero ' . $data['datos_juridicos']['representante_legal_identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte') {
                $desc = 'con pasaporte numero ' . $data['datos_juridicos']['representante_legal_identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente') {
                $desc = 'con carnet de residente numero ' . $data['datos_juridicos']['representante_legal_identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            }

        } else {
            if ($data['cliente']['tipo_documento_ident_venta'] == 'dni') {
                $desc = 'con documento nacional de identificacion numero ' . $data['cliente']['identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'pasaporte') {
                $desc = 'con pasaporte numero ' . $data['cliente']['identidad'];
                $template->setValue('DESC_DOCUMENTO', $desc);
            } else if ($data['cliente']['tipo_documento_ident_venta'] == 'carnet_residente') {
                $desc = 'con carnet de residente numero ' . $data['cliente']['identidad'];
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

        $template->setValue('DIAS_LETRAS', FechanumeroALetras((int) $data['fecha']['dia']));
        $template->setValue('MES_LETRAS', mesEnLetras((int) $data['fecha']['mes']));
        $template->setValue('ANIO_LETRAS', FechanumeroALetras((int) $data['fecha']['anio']));

        appLog('Template procesado desde JSON');

        /* =========================
           4️⃣ GENERAR DOCX
        ========================== */
        $tmpDir = sys_get_temp_dir();
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

?>