<?php
require_once ('include/framework.php');
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;



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


/**
 * @return bool|array
 */
function generarContratoVenta(
    int $id_venta,
    string $nombreUsuario,
    string $apellidoUsuario,
    string $usuarioSistema,
    bool $sologuardar = false
) {
    try {

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

                entidad.nombre   AS cliente_nombre,
                entidad.rtn      AS identidad_cliente,
                entidad.direccion AS direccion_cliente,
                entidad.codigo_alterno AS codigo_cliente,
                entidad.telefono AS telefono_cliente,

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

        /* ===============================
           2️⃣ DATOS DE LA TIENDA
        =============================== */
        $datos_tienda = sql_select("
            SELECT representante_legal, representante_identidad, nombre, departamento,abr_ciudad
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
            (id_venta, correlativo, numero_contrato, estado, creado_por)
            VALUES
            ($id_venta, $nuevoCorrelativo, '$numeroContrato', 'ACTIVO', '$usuarioSistema')
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
                'ciudad' => $tienda['nombre'],
                'departamento' => $tienda['departamento']
            ],
            'cliente' => [
                'nombre' => $venta['cliente_nombre'],
                'identidad' => $venta['identidad_cliente'],
                'codigo' => $venta['codigo_cliente'],
                'direccion' => $venta['direccion_cliente'],
                'telefono' => $venta['telefono_cliente']
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
                'mes' => $meses[(int)date('n')],
                'anio' => date('Y')
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
            (id_contrato, id_venta, accion, usuario, estado, datos_json)
            VALUES
            ($idContrato, $id_venta, 'CREACION', '$usuarioSistema', 'ACTIVO', '$contratoJson')
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

        $soffice = getSofficeCommandDev();
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


function descargarVentaPDFOld2($id_venta)
{
    try {
        appLog('===== descargarVentaPDF INICIO =====');
        appLog('ID_VENTA: ' . $id_venta);

        if (empty($id_venta)) {
            throw new RuntimeException('ID de venta inválido');
        }

        /* =========================
           CONSULTA DE LA VENTA
        ========================== */
        $datos_venta = sql_select("
            SELECT ventas.id_tienda,
                   entidad.nombre AS cliente_nombre,
                   entidad.rtn AS identidad_cliente,
                   entidad.direccion AS direccion,
                   entidad.codigo_alterno AS codigo_cliente,
                   entidad.direccion AS direccion_cliente,
                   entidad.telefono AS telefono_cliente,
                   producto.codigo_alterno AS cod_vehiculo,
                   producto.placa AS placa,
                   producto.marca AS marca,
                   producto.modelo AS modelo,
                   producto.tipo_vehiculo AS tipo,
                   producto.chasis AS chasis,
                   producto.motor AS motor,
                   producto.color AS color,
                   producto.anio AS anio,
                   ventas.cilindraje AS cilindraje,
                   '' AS departamento,
                   '' AS combustible,
                   ventas.precio_venta AS precio_venta,
                   ventas.prima_venta AS prima_venta
            FROM ventas
            LEFT JOIN tienda ON ventas.id_tienda = tienda.id
            LEFT JOIN producto ON ventas.id_producto = producto.id
            LEFT JOIN entidad ON ventas.cliente_id = entidad.id
            WHERE ventas.id = $id_venta
            LIMIT 1
        ");

        if (!$datos_venta || $datos_venta->num_rows === 0) {
            throw new RuntimeException('Venta no encontrada');
        }

        $venta = $datos_venta->fetch_assoc();
        appLog('Venta encontrada. ID_TIENDA: ' . $venta['id_tienda']);

        /* =========================
           DATOS DE LA TIENDA
        ========================== */
        $datos_tienda = sql_select("
            SELECT * FROM tienda
            WHERE id = {$venta['id_tienda']}
            LIMIT 1
        ");

        if (!$datos_tienda || $datos_tienda->num_rows === 0) {
            throw new RuntimeException('Tienda no encontrada');
        }

        $tienda = $datos_tienda->fetch_assoc();
        appLog('Tienda cargada: ' . $tienda['nombre']);

        /* =========================
           TEMPLATE DOCX
        ========================== */
        $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PN_v2.docx';

        if (!file_exists($templatePath)) {
            throw new RuntimeException('Template DOCX no encontrado');
        }

        $template = new TemplateProcessor($templatePath);

        // Representante
        $template->setValue('REPRESENTANTE_LEGAL', $tienda['representante_legal']);
        $template->setValue('R_IDENTIDAD', $tienda['representante_identidad']);
        $template->setValue('CIUDAD', $tienda['nombre']);

        // Cliente
        $template->setValue('CLIENTE', $venta['cliente_nombre']);
        $template->setValue('IDENTIDAD_CLIENTE', $venta['identidad_cliente']);
        $template->setValue('CODIGO_CLIENTE', $venta['codigo_cliente']);
        $template->setValue('DIRECCION_CLIENTE', $venta['direccion_cliente']);
        $template->setValue('TELEFONO_CLIENTE', $venta['telefono_cliente']);

        // Precios
        $precioVenta = (float)$venta['precio_venta'];
        $template->setValue('PRECIO_VENTA', number_format($precioVenta, 2, '.', ','));
        $template->setValue('PRECIO_VENTA_LETRAS', numeroALetras($precioVenta));

        $primaVenta = (float)$venta['prima_venta'];
        $template->setValue('PRIMA_VENTA', number_format($primaVenta, 2, '.', ','));
        $template->setValue('PRIMA_VENTA_LETRAS', numeroALetras($primaVenta));

        // Vehículo
        $template->setValue('CODIGO_VEHICULO', $venta['cod_vehiculo']);
        $template->setValue('PLACA', $venta['placa']);
        $template->setValue('MARCA', $venta['marca']);
        $template->setValue('MODELO', $venta['modelo']);
        $template->setValue('TIPO', $venta['tipo']);
        $template->setValue('CHASIS', $venta['chasis']);
        $template->setValue('MOTOR', $venta['motor']);
        $template->setValue('COLOR', $venta['color']);
        $template->setValue('ANIO', $venta['anio']);
        $template->setValue('CILINDRAJE', $venta['cilindraje']);
        $template->setValue('COMBUSTIBLE', $venta['combustible']);

        // Fecha
        date_default_timezone_set('America/Tegucigalpa');
        $meses = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'
        ];
        $template->setValue('DIAS', date('j'));
        $template->setValue('MES', $meses[(int)date('n')]);
        $template->setValue('ANIO_ACTUAL', date('Y'));

        appLog('Template procesado');

        /* =========================
           GUARDAR DOCX TEMPORAL
        ========================== */
        $tmpDir  = sys_get_temp_dir();
        $tmpDocx = $tmpDir . '/venta_' . $id_venta . '_' . time() . '.docx';

        $template->saveAs($tmpDocx);
        appLog('DOCX generado: ' . $tmpDocx);

        if (!file_exists($tmpDocx)) {
            throw new RuntimeException('No se pudo generar el DOCX');
        }

        /* =========================
           CONVERTIR A PDF
        ========================== */
        $pdfPath = convertirDocxAPdf($tmpDocx);
        appLog('PDF generado: ' . $pdfPath);

        if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
            throw new RuntimeException('PDF no disponible');
        }

        /* =========================
           DESCARGA
        ========================== */
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header(
            'Content-Disposition: attachment; filename="Venta_' .
            $id_venta . '_' . date('Ymd_His') . '.pdf"'
        );
        header('Content-Length: ' . filesize($pdfPath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        $bytes = readfile($pdfPath);
        appLog('BYTES ENVIADOS: ' . $bytes);

        /* =========================
           LIMPIEZA
        ========================== */
        unlink($tmpDocx);
        unlink($pdfPath);

        appLog('===== descargarVentaPDF FIN OK =====');
        exit;

    } catch (Throwable $e) {
        appLog('EXCEPTION descargarVentaPDF: ' . $e->getMessage());
        throw $e;
    }
}

function descargarVentaPDF($id_venta, $soloValidar = false)
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

        $templatePath = __DIR__ . '/../plantillas/venta_contrato_vehiculo_PN_v2.docx';


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
        $template->setValue('CIUDAD', $data['representante']['ciudad']);
        $template->setValue('DEPARTAMENTO', $data['representante']['departamento']);

        // Cliente
        $template->setValue('CLIENTE', $data['cliente']['nombre']);
        $template->setValue('IDENTIDAD_CLIENTE', $data['cliente']['identidad']);
        $template->setValue('CODIGO_CLIENTE', $data['cliente']['codigo']);
        $template->setValue('DIRECCION_CLIENTE', $data['cliente']['direccion']);
        $template->setValue('TELEFONO_CLIENTE', $data['cliente']['telefono']);

        $template->setValue('m_f','o');

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



if (isset($_REQUEST['r'])) { $nuevo = $_REQUEST['r']; } else   {$nuevo ="N";}
if ($nuevo=='N'){
   pagina_permiso(170);
}else{
   pagina_permiso(167);
}


if (isset($_GET['a']) && $_GET['a'] === 'actcontrato') {

        $id_venta = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
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
            $id_venta,
            $nombreUsuario,
            $apellidoUsuario,
            $usuarioSistema
        );

        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
}



// VALIDAR (AJAX)
    if ($_GET['a'] === 'print_check') {
        $id = intval($_GET['id']);
        echo json_encode(descargarVentaPDF($id, true));
        exit;
    }

    // DESCARGAR (NAVEGADOR)
    if ($_GET['a'] === 'print') {
        $id = intval($_GET['id']);
        descargarVentaPDF($id); // descarga real
        exit;
    }

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion ="v";}

$disable_sec1=' ';    
$disable_sec2=' ';    

// Leer Datos    ############################  
if ($accion=="v") {
	$cid=0;
	if (isset($_REQUEST['cid'])) { $cid = sanear_int($_REQUEST['cid']); }

	$result = sql_select("SELECT ventas.*    
    ,ventas_estado.nombre AS elestado
    ,ventas_impuestos.nombre AS elimpuesto
    ,ventas_factura.nombre AS lafactura
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
    ,tienda.nombre AS latienda
    ,entidad.nombre AS cliente_nombre
    ,ventas.persona_juridica
    ,ventas.representante_legal_persona_juridica
    ,ventas.representante_legal_identidad
    ,ventas.representante_legal_profesion
    ,ventas.representante_legal_direccion

        FROM ventas
        LEFT OUTER JOIN tienda ON (ventas.id_tienda=tienda.id)        
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)        
        LEFT OUTER JOIN ventas_estado ON (ventas.id_estado=ventas_estado.id)
        LEFT OUTER JOIN ventas_impuestos ON (ventas.id_impuesto=ventas_impuestos.id)
        LEFT OUTER JOIN ventas_factura ON (ventas.id_factura=ventas_factura.id)
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
        $result = sql_select("SELECT id_estado
        FROM ventas
        where id=$cid limit 1");

        if ($result!=false){
            if ($result -> num_rows > 0) { 
                $row = $result -> fetch_assoc(); 
                if ($row['id_estado']<=20) {
                    sql_delete("DELETE FROM ventas where id=$cid limit 1");
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
//Elimina Foto
if ($accion=="dfoto") {
   $cid=0;
   if (isset($_REQUEST['id'])) { $cid = intval($_REQUEST["id"]); }   
   if (isset($_REQUEST['foto'])){ $dfoto = intval($_REQUEST['foto']);} 
   if ($dfoto==1){
      sql_update("UPDATE ventas set foto=null where id=$cid limit 1");   
   }else{
      sql_update("UPDATE ventas set foto_televentas=null where id=$cid limit 1");
   }
   $stud_arr[0]["pcode"] = 1;
   $stud_arr[0]["pmsg"] ="Foto eliminada";     
   salida_json($stud_arr);  
   exit;  
}

if($accion=="gfoto")
{
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="Error";
    $stud_arr[0]["pcid"] = 0;
    
    
    $cid=0;

    $is_main = isset($_POST['isMain']) ? intval($_POST['isMain']) : 0;

    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }   
    if (isset($_REQUEST['arch'])) 
        { 

            $foto_original = urldecode($_REQUEST['arch']);
            $foto = str_replace(' ', '_', urldecode($_REQUEST["arch"]));




            // Rutas originales
            $ruta1 = 'uploa_d_ventas/' . $foto_original;
            $ruta2 = 'uploa_d_ventas/thumbnail/' . $foto_original;

            // Rutas nuevas
            $nueva1 = 'uploa_d_ventas/' . $foto;
            $nueva2 = 'uploa_d_ventas/thumbnail/' . $foto;

            // Renombrar
            if (file_exists($ruta1)) {
                rename($ruta1, $nueva1);
            }
            if (file_exists($ruta2)) {
                rename($ruta2, $nueva2);
            }
        
        } else{$foto ="";}   


    
     if (!es_nulo($foto) && !es_nulo($cid)){ 
         sql_insert("INSERT INTO ventas_fotos (id_venta,  nombre_archivo,  principal,fecha)
         VALUES ( $cid,  '$foto', $is_main, NOW())"); 

         $stud_arr[0]["pcode"] = 1;
         $stud_arr[0]["pmsg"] ="Foto guardada";     
         $stud_arr[0]["pcid"] = $cid;     
    }else{
         $stud_arr[0]["pmsg"] ="Error al guardar la foto";     
    } 
    
    salida_json($stud_arr);  
    exit;  
}

// borrar ARCHIVO de ventas
if ($accion =="dfotoventas") {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB102";

    if (isset($_REQUEST['arch'])) { $arch =GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}
    if (isset($_REQUEST['cod'])) { $cod = "and id=".GetSQLValue(urldecode($_REQUEST["cod"]),"text"); } else	{$cod ="" ;}

    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }  else	{$cid =0 ;}

    if ($cod<>'' or $arch<>'') {

    borrar_foto_directorio($cid,$cod,$arch,"foto_ventas");

    $result = sql_delete("DELETE FROM ventas_fotos 
                            WHERE id_venta=$cid 
                            and nombre_archivo=$arch 
                            LIMIT 1
                            ");


 } else {$result==false;}
    if ($result!=false){

        //TODO borrar archivo $arch

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="Borrado";
    }

  salida_json($stud_arr);
    exit;

}

if ($accion =="ufotoportadaventas") {

    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"] ="ERROR DB102";

    if (isset($_REQUEST['arch'])) { $arch =GetSQLValue(urldecode($_REQUEST["arch"]),"text"); } else	{$arch ="" ;}
    if (isset($_REQUEST['cod'])) { $cod = intval($_REQUEST["cod"]); } else	{$cod ="" ;}

    if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST["cid"]); }  else	{$cid =0 ;}

    if ($cod<>'' or $arch<>'') {

            $sqlReset="update ventas_fotos set principal=0 where id_venta=".$cid."";
            $sqlMarcar="update ventas_fotos set principal=1 where id=".$cod." and id_venta=".$cid."  limit 1";

            $resultReset = sql_update($sqlReset);

            $resulMarcar = sql_update($sqlMarcar);



    }else {$result==false;}
    if ($resultReset!=false and $resulMarcar!=false){

        //TODO borrar archivo $arch

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] ="Actualizado";
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
    $carShopPerfil="";
    $verror.=validar("Sucursal",$_REQUEST['id_tienda'], "int", true);
    $verror.=validar("Vehiculo",$_REQUEST['id_producto'], "int", true);
    $verror.=validar("Kilomatraje",$_REQUEST['kilometraje'], "int", true);
    $verror.=validar("Cilindraje",$_REQUEST['cilindraje'], "int", true);
    $verror.=validar("Precio Minimo",$_REQUEST['precio_minimo'], "int", true);
    $verror.=validar("Precio Maximo",$_REQUEST['precio_maximo'], "int", true);         
    $precio_minimo=intval($_REQUEST['precio_minimo']);     
    $precio_maximo=intval($_REQUEST['precio_maximo']);    
    
    if ($precio_maximo<$precio_minimo){$verror.='Ingrese el precio maximo tiene que ser mayor';}
    if ($precio_minimo>$precio_maximo){$verror.='Ingrese el precio minimo tiene que ser menor';}
    
    if (es_nulo($cid)){
        $id_producto=intval($_REQUEST['id_producto']);
        $vehiculo=get_dato_sql("ventas","count(*)"," where id_producto=".$id_producto);
        if (!es_nulo($vehiculo) && es_nulo($cid)){ $verror.='Vehiculo ya esta registrado'; }   
    }  
    
    $carShopPerfil=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
    $id_estado=intval($_REQUEST['id_estado']);
    $precio_venta=intval($_REQUEST['precio_venta']);
    $prima_venta=intval($_REQUEST['prima_venta']);
    $prima_raw = $_REQUEST['prima_venta'] ?? '';

    $persona_juridica=intval($_REQUEST['persona_juridica']);

    if ($id_estado == 11 || $id_estado == 20) {

            $client_id_val = isset($_REQUEST['cliente_id'])
                ? (int) $_REQUEST['cliente_id']
                : 0;

            if ($client_id_val <= 0) {
                $verror .= 'Seleccione un cliente. ';
            }

            if ($persona_juridica == 1) {

                if (empty(trim($_REQUEST['representante_legal_persona_juridica'] ?? ''))) {
                    $verror .= 'El Representante Legal es obligatorio. ';
                }

                if (empty(trim($_REQUEST['representante_legal_identidad'] ?? ''))) {
                    $verror .= 'La Identidad del Representante Legal es obligatoria. ';
                }

                if (empty(trim($_REQUEST['representante_legal_profesion'] ?? ''))) {
                    $verror .= 'La profesion del Representante Legal es obligatoria. ';
                }

                if (empty(trim($_REQUEST['representante_legal_direccion'] ?? ''))) {
                    $verror .= 'La direccion del Representante Legal es obligatoria. ';
                }
            }

    }
   
if (!es_nulo($cid) && in_array($id_estado, [11, 20], true)) {

    // Precio: obligatorio
    if (es_nulo($precio_venta)) {
        $verror .= 'Ingrese el precio de venta del vehículo. ';
    }

    // Prima: puede ser 0, pero no vacía
    if (trim($prima_raw) === '') {
        $verror .= 'Ingrese la prima de venta del vehículo. ';
    }
}
    
    
    if (!es_nulo($cid) && ($id_estado!=20 && $id_estado!=11)){
       if (!es_nulo($precio_venta)||!es_nulo($prima_venta)) { $verror.='Precio de venta y prima solo se ingresan, estado de vendido entregado'; }    
    }  
    if ($carShopPerfil=='18'){
        $verror.=validar("Estado",$_REQUEST['id_estado'], "int", true);
        $id_vendedor=intval(get_dato_sql("ventas","id_vendedor"," where id=".$cid)); 
        if (!es_nulo($id_vendedor) && $id_vendedor!=intval($_REQUEST['id_vendedor']) && $id_estado==11){ 
            $verror.='No es posible realizar el cambio de vendedor';
        }        
        $id_estado_ant=intval(get_dato_sql("ventas","id_estado"," where id=".$cid));          
        if ($id_estado==5){ 
            $verror.='No es posible realizar el cambio de estado';
        }
    }

    $envioCorreo="";
    $enviar_correo_sin_fotos = "";
    $fotoRegistro=get_dato_sql("ventas_estado","foto"," where foto=1 and id=".$id_estado);
    $envioCorreo=get_dato_sql("ventas_estado","envio_correo"," where envio_correo=1 and id=".$id_estado);
    if (!es_nulo($fotoRegistro)){
        if (isset($_REQUEST['foto'])) {
           $verror.=validar("Foto de comprobante de pago",$_REQUEST['foto'], "text", true);
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

        $foto_original_comp = $_REQUEST['foto'] ?? '';
        $foto_original_tele = $_REQUEST['foto_televentas'] ?? '';



// FOTO NORMAL
if ($foto_original_comp !== '') {

    $foto_original_comp = urldecode($foto_original_comp);
    $foto = str_replace(' ', '_', $foto_original_comp);

    $ruta1  = 'uploa_d/' . $foto_original_comp;
    $ruta2  = 'uploa_d/thumbnail/' . $foto_original_comp;
    $nueva1 = 'uploa_d/' . $foto;
    $nueva2 = 'uploa_d/thumbnail/' . $foto;

    if (file_exists($ruta1)) {
        @rename($ruta1, $nueva1);
    }
    if (file_exists($ruta2)) {
        @rename($ruta2, $nueva2);
    }

} 

// FOTO TELEVENTAS
if ($foto_original_tele !== '') {

    $foto_original_tele = urldecode($foto_original_tele);
    $foto_televentas = str_replace(' ', '_', $foto_original_tele);

    $ruta3  = 'uploa_d/' . $foto_original_tele;
    $ruta4  = 'uploa_d/thumbnail/' . $foto_original_tele;
    $nueva3 = 'uploa_d/' . $foto_televentas;
    $nueva4 = 'uploa_d/thumbnail/' . $foto_televentas;

    if (file_exists($ruta3)) {
        @rename($ruta3, $nueva3);
    }
    if (file_exists($ruta4)) {
        @rename($ruta4, $nueva4);
    }

}

             

        /* if ($foto_original_comp !== '' || $foto_original_tele !== ''){
            $foto_original_comp = urldecode($_REQUEST['foto']);
            $foto_original_tele = urldecode($_REQUEST['foto_televentas']);

            $foto = str_replace(' ', '_', urldecode($_REQUEST["foto"]));
            $foto_televentas = str_replace(' ', '_', urldecode($_REQUEST["foto_televentas"]));

            // Rutas originales
            $ruta1 = 'uploa_d/' . $foto_original_comp;
            $ruta2 = 'uploa_d/thumbnail/' . $foto_original_comp;
            $ruta3 = 'uploa_d/' . $foto_original_tele;
            $ruta4 = 'uploa_d/thumbnail/' . $foto_original_tele;

            // Rutas nuevas
            $nueva1 = 'uploa_d/' . $foto;
            $nueva2 = 'uploa_d/thumbnail/' . $foto;
            $nueva3 = 'uploa_d/' . $foto_televentas;
            $nueva4 = 'uploa_d/thumbnail/' . $foto_televentas;

            // Renombrar
            if (file_exists($ruta1)) {
                rename($ruta1, $nueva1);
            }
            if (file_exists($ruta2)) {
                rename($ruta2, $nueva2);
            }        

            if (file_exists($ruta3)) {
                rename($ruta3, $nueva3);
            }        

            if (file_exists($ruta4)) {
                rename($ruta4, $nueva4);
            }        
        } */ 







        if (isset($_REQUEST["id_producto"])) { $sqlcampos.= "  id_producto =".GetSQLValue($_REQUEST["id_producto"],"int"); } 
        if (isset($_REQUEST["id_tienda"])) { $sqlcampos.= " , id_tienda =".GetSQLValue($_REQUEST["id_tienda"],"int"); }    
        if (isset($_REQUEST["id_estado"])) { $sqlcampos.= " , id_estado =".GetSQLValue($_REQUEST["id_estado"],"int"); }  


        $estado_nuevo = intval($_REQUEST['id_estado']);
        
        
        if (isset($_REQUEST["precio_minimo"])) { $sqlcampos.= " , precio_minimo =".GetSQLValue($_REQUEST["precio_minimo"],"int"); } 
        if (isset($_REQUEST["precio_maximo"])) { $sqlcampos.= " , precio_maximo =".GetSQLValue($_REQUEST["precio_maximo"],"int"); } 
        
        if (isset($_REQUEST["precio_venta"])) { $sqlcampos.= " , precio_venta =".GetSQLValue($_REQUEST["precio_venta"],"int"); } 

        if (isset($_REQUEST["prima_venta"])) { $sqlcampos.= " , prima_venta =".GetSQLValue($_REQUEST["prima_venta"],"int"); } 


        

        if ($persona_juridica == 1 && ($id_estado==11 || $id_estado==20)) {

            if (isset($_REQUEST["persona_juridica"])) { $sqlcampos.= " , persona_juridica =".GetSQLValue($_REQUEST["persona_juridica"],"int"); } 

            $rep_legal = trim($_REQUEST['representante_legal_persona_juridica'] ?? '');
            $rep_id    = trim($_REQUEST['representante_legal_identidad'] ?? '');
            $rep_profesion   = trim($_REQUEST['representante_legal_profesion'] ?? '');
            $rep_direccion    = trim($_REQUEST['representante_legal_direccion'] ?? '');

            $sqlcampos .= " , representante_legal_persona_juridica = "
                        . GetSQLValue($rep_legal, "text");

            $sqlcampos .= " , representante_legal_identidad = "
                        . GetSQLValue($rep_id, "text");

            $sqlcampos .= " , representante_legal_profesion = "
            . GetSQLValue($rep_profesion, "text");

             $sqlcampos .= " , representante_legal_direccion = "
            . GetSQLValue($rep_direccion, "text");



        } else {

            // Si NO es persona jurídica, limpiamos los campos
            $sqlcampos .= " , persona_juridica =0";
            $sqlcampos .= " , representante_legal_persona_juridica = NULL";
            $sqlcampos .= " , representante_legal_identidad = NULL";
            $sqlcampos .= " , representante_legal_profesion = NULL";
            $sqlcampos .= " , representante_legal_direccion = NULL";
            
        }
        


        if (isset($_REQUEST["kilometraje"])) { $sqlcampos.= " , kilometraje =".GetSQLValue($_REQUEST["kilometraje"],"int"); } 
        if (isset($_REQUEST["cilindraje"])) { $sqlcampos.= " , cilindraje =".GetSQLValue($_REQUEST["cilindraje"],"int"); } 
        if (isset($_REQUEST["trasmision"])) { $sqlcampos.= " , trasmision =".GetSQLValue($_REQUEST["trasmision"],"text"); } 
        if (isset($_REQUEST["id_impuesto"])) { $sqlcampos.= " , id_impuesto =".GetSQLValue($_REQUEST["id_impuesto"],"int"); } 
        if (isset($_REQUEST["id_factura"])) { $sqlcampos.= " , id_factura =".GetSQLValue($_REQUEST["id_factura"],"int"); } 
        if (isset($_REQUEST["id_vendedor"])) { $sqlcampos.= " , id_vendedor =".GetSQLValue($_REQUEST["id_vendedor"],"int"); } 
        if (isset($_REQUEST["id_televentas"])) { $sqlcampos.= " , id_televentas =".GetSQLValue($_REQUEST["id_televentas"],"int"); } 
        if (isset($_REQUEST["observaciones"])) { $sqlcampos.= " , observaciones =".GetSQLValue($_REQUEST["observaciones"],"text"); }         
        //if (isset($_REQUEST["foto"])) { $sqlcampos.= " , foto ='$foto'"; } 
        //if (isset($_REQUEST["foto_televentas"])) { $sqlcampos.= " , foto_televentas = '$foto_televentas'"; } 

        if (!empty($_REQUEST["foto"])) {
            $sqlcampos .= " , foto = " . GetSQLValue($foto, "text");

        }

        if (!empty($_REQUEST["foto_televentas"])) {
            $sqlcampos .= " , foto_televentas = " . GetSQLValue($foto_televentas, "text");
        }

        if (isset($_REQUEST["reproceso"])) { $sqlcampos.= " , reproceso =".GetSQLValue($_REQUEST["reproceso"],"text"); } 
        if (isset($_REQUEST["oferta"])) { $sqlcampos.= " , oferta =".GetSQLValue($_REQUEST["oferta"],"int"); } 

        if($id_estado==11 || $id_estado==20){
            if (isset($_REQUEST["cliente_id"])) { $sqlcampos.= " , cliente_id =".GetSQLValue($_REQUEST["cliente_id"],"int"); }  
        }else{
            $sqlcampos.= " , cliente_id =null";
        }


        if ($nuevoregistro==false) {
            $reproceso="";  
            $nombreReproceso="";
            if (isset($_REQUEST["reproceso"])){
                $reproceso=$_REQUEST["reproceso"];
                if (!es_nulo($reproceso)){
                    switch ($reproceso){
                       case "1":
                           $nombreReproceso='Pintura';
                           $sqlcampos.= " , id_estado_pintura = 30";
                            break;
                       case "2":
                           $nombreReproceso='Interior';
                           $sqlcampos.= " , id_estado_interior = 30";   
                            break;
                       case "3":
                           $nombreReproceso='Mecanica';
                           $sqlcampos.= " , id_estado_mecanica = 30";
                            break;
                    }
                    $sqlcampos.= " , tipo_ventas_reparacion = 1";
                    $sqlcampos.= " , id_estado = 99";
                    $sqlcampos.= " , reproceso = 'R'";
                    sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                    VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Vehiculo a Reproceso', NOW(), '$nombreReproceso')");
                }
            }
            if (intval($_REQUEST['id_estado'])==20){                
               // $sqlcampos.=" , fecha_vendido=now()";  
             }            
            //si modifica se guarda el registo del cambio
            $id_tienda=intval(get_dato_sql("ventas","id_tienda"," where id=".$cid));
            if ($id_tienda!=intval($_REQUEST['id_tienda'])){   
               $id_tienda_name=get_dato_sql("tienda","nombre"," where id=".$_REQUEST['id_tienda']);
               sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
               VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Tienda', NOW(), '$id_tienda_name')");
            }
            $kilometraje=intval(get_dato_sql("ventas","kilometraje"," where id=".$cid));
            if ($kilometraje!=intval($_REQUEST['kilometraje'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Kilometraje', NOW(), ".$_REQUEST['kilometraje'].")");
            }

             $precio_minimo=intval(get_dato_sql("ventas","precio_minimo"," where id=".$cid));
             if ($precio_minimo!=intval($_REQUEST['precio_minimo'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Precio Minimo', NOW(), ".$_REQUEST['precio_minimo'].")");
             }

             $precio_maximo=intval(get_dato_sql("ventas","precio_maximo"," where id=".$cid));
             if ($precio_maximo!=intval($_REQUEST['precio_maximo'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Precio Maximo', NOW(), ".$_REQUEST['precio_maximo'].")");
             }

             $precio_venta=intval(get_dato_sql("ventas","precio_venta"," where id=".$cid));
             if ($precio_venta!=intval($_REQUEST['precio_venta'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Precio de Venta', NOW(), ".$_REQUEST['precio_venta'].")");
             }

             $prima_venta=intval(get_dato_sql("ventas","prima_venta"," where id=".$cid));
             if ($prima_venta!=intval($_REQUEST['prima_venta'])){   
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Prima de Venta', NOW(), ".$_REQUEST['prima_venta'].")");
             }



             


             $cliente_viejo = get_dato_sql(
                "ventas",
                "cliente_id",
                " WHERE id = ".$cid
            );

             $estado_viejo = get_dato_sql(
                "ventas",
                "id_estado",
                " WHERE id = ".$cid
            );

            // Normalizar viejo
            $cliente_viejo = ($cliente_viejo == 0 || $cliente_viejo === null)
                ? null
                : intval($cliente_viejo);

            // Normalizar nuevo (lo que viene del form)
            $cliente_nuevo = (
                !isset($_REQUEST['cliente_id']) ||
                $_REQUEST['cliente_id'] === '' ||
                $_REQUEST['cliente_id'] === '0'
            ) ? null : intval($_REQUEST['cliente_id']);

            // 👉 SOLO aquí se detecta el cambio
            if ($cliente_viejo !== $cliente_nuevo) {

                $nombre_viejo = $cliente_viejo
                    ? get_dato_sql("entidad", "nombre", " WHERE id = ".$cliente_viejo)
                    : 'Vacío';

                $nombre_nuevo = $cliente_nuevo
                    ? get_dato_sql("entidad", "nombre", " WHERE id = ".$cliente_nuevo)
                    : 'Vacío';

                $observacion = "'Cliente: {$nombre_viejo} → {$nombre_nuevo}'";

                if($nombre_viejo!=$nombre_nuevo)
                {
                    sql_insert("
                        INSERT INTO ventas_historial_estado
                        (id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
                        VALUES (
                            $cid,
                            ".$_SESSION['usuario_id'].",
                            ".$_REQUEST['id_estado'].",
                            'Modificación de cliente',
                            NOW(),
                            $observacion
                        )
                    ");
                }
            }

             $id_estado=intval(get_dato_sql("ventas","id_estado"," where id=".$cid));             
             $id_estado_vendido=intval(get_dato_sql("ventas_estado","envio_correo"," where id=".$id_estado));             
             $id_estado_modifico=false;
             
             if ($id_estado!=intval($_REQUEST['id_estado']))
                {                  
                 //si cambia el estado a en negociacion  
                 if ($envioCorreo==1){                      
                    $id_estado_modifico=true;
                    $sqlcampos.=" , fecha_vendido=now()";                                                 
                 }
                 if (intval($_REQUEST['id_estado'])==11){                                    
                    $sqlcampos.=" , fecha_negociacion=now()";  
                 }                               
                 if ($id_estado==11){                
                    $sqlcampos.=" , fecha_negociacion=null";  
                 }                                 
                 if ($id_estado_vendido==1){                
                     $sqlcampos.=" , fecha_vendido=null, precio_venta=null";  
                 } 
                 

                /* RL. 20251121  - Cambio para verificar si cuando cambia al estado 5 (Disponiblidad para venta) 
                                   se han adjuntado fotos a la Venta del vehiculo
                */
                if (intval($_REQUEST['id_estado']) == 5) {
                    $tiene_fotos = get_dato_sql("ventas_fotos", "COUNT(*)", " WHERE id_venta = $cid");
                    if ($tiene_fotos == 0 || es_nulo($tiene_fotos)) {
                        // Marcar para enviar correo
                        $enviar_correo_sin_fotos = true;
                    }
                }
                 
                 $fotoRegistro1=get_dato_sql("ventas_estado","foto"," where foto=1 and id=".$id_estado);
                 $id_estado_name=get_dato_sql("ventas_estado","nombre"," where id=".$_REQUEST['id_estado']);

                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",$id_estado,'Modificacion de Estado', NOW(),'$id_estado_name')");

                 if($id_estado_name!='en negociacion')
                 {
                                    sql_insert("
                    INSERT INTO ventas_historial_estado
                    (id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
                    VALUES (
                        $cid,
                        ".$_SESSION['usuario_id'].",
                        ".$_REQUEST['id_estado'].",
                        'Modificación de cliente',
                        NOW(),
                        'Cliente eliminado al quitar estado de en negociacion'
                    )
                ");
                 }
                 
             }


              /* RL. 20251121  - Cambio para verificar si el  estado es 5 (Disponiblidad para venta) 
                                   se han adjuntado fotos a la Venta del vehiculo
                */
             if($id_estado == 5)
             {
                $tiene_fotos = get_dato_sql("ventas_fotos", "COUNT(*)", " WHERE id_venta = $cid");
                    if ($tiene_fotos == 0 || es_nulo($tiene_fotos)) 
                    {          
                        // Marcar para enviar correo
                        $enviar_correo_sin_fotos = true;
                    }
             }

             $id_impuesto=intval(get_dato_sql("ventas","id_impuesto"," where id=".$cid));
             if ($id_impuesto!=intval($_REQUEST['id_impuesto'])){   
                 $id_impuesto_name=get_dato_sql("ventas_impuestos","nombre"," where id=".$_REQUEST['id_impuesto']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Impuestos', NOW(),'$id_impuesto_name')");
             }

             $id_factura=intval(get_dato_sql("ventas","id_factura"," where id=".$cid));
             if ($id_factura!=intval($_REQUEST['id_factura'])){   
                 $id_factura_name=get_dato_sql("ventas_factura","nombre"," where id=".$_REQUEST['id_factura']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Factura', NOW(),'$id_factura_name')");
             }            

             $id_vendedor=intval(get_dato_sql("ventas","id_vendedor"," where id=".$cid));
             if ($id_vendedor!=intval($_REQUEST['id_vendedor'])){   
                $id_vendedor_name=get_dato_sql("usuario","nombre"," where id=".$_REQUEST['id_vendedor']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Vendedor', NOW(), '$id_vendedor_name')");
             }

             $id_televentas=intval(get_dato_sql("ventas","id_televentas"," where id=".$cid));
             if ($id_televentas!=intval($_REQUEST['id_televentas'])){   
                 $id_televentas_name=get_dato_sql("usuario","nombre"," where id=".$_REQUEST['id_televentas']);
                 sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                 VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Televentas', NOW(),'$id_televentas_name')");
             }

             $observaciones=trim(get_dato_sql("ventas","observaciones"," where id=".$cid));
             if ($observaciones!=trim($_REQUEST['observaciones'])){   
                sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
                VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Modificacion de Observaciones', NOW(),'".$_REQUEST['observaciones']."')"); 
             }
            
             

            $sql="update ventas set ".$sqlcampos." where id=".$cid." limit 1";
            $result = sql_update($sql);

        } else 
        {
            //Crear nuevo                       
            $sqlcampos.=" ,id_usuario=".$_SESSION['usuario_id'] ;      
            $sqlcampos.=" ,tipo_ventas_reparacion=2";      
            $sqlcampos.=" ,numero=".GetSQLValue(get_dato_sql('ventas',"IFNULL((max(numero)+1),1)"," "),"int"); 
            $sql="insert into ventas set fecha=NOW(), hora=now(),".$sqlcampos." ";        
            
            $result = sql_insert($sql);
            $cid=$result; //last insert id 

            sql_insert("INSERT INTO ventas_historial_estado (id_maestro,  id_usuario,  id_estado, nombre, fecha, observaciones)
            VALUES ( $cid,  ".$_SESSION['usuario_id'].",".$_REQUEST['id_estado'].",'Nuevo registro de vehiculo', NOW(),'Nuevo')");
            
            // RL. 20251121 - Para nuevo registro en estado 5, verificar fotos
            $id_estado = intval($_REQUEST['id_estado']);
            if ($id_estado == 5) 
            {
                // Para nuevo registro, verificamos si hay fotos (debería ser 0)
                $tiene_fotos = get_dato_sql("ventas_fotos", "COUNT(*)", " WHERE id_venta = $cid");
                if ($tiene_fotos == 0 || es_nulo($tiene_fotos)) {
                    $enviar_correo_sin_fotos = true;
                }
            }
    
    }

        
        if ($result!=false){
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ="Guardado";
            $stud_arr[0]["pcid"] = $cid;    
            

/*         if($estado_viejo!=$estado_nuevo && $estado_nuevo==11)
        {
            $id_venta = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
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
            //ubicacion = strtoupper(trim($user['tienda_nombre']));
            $usuarioSistema = $user['usuario'];

                    $resultado = generarContratoVenta(
                            $id_venta,
                            $nombreUsuario,
                            $apellidoUsuario,
                            $usuarioSistema
                        );

                    if (is_array($resultado) && $resultado['ok']) {

                        $descripcion = "Guardado - contrato generado: " . $resultado['numero_contrato'];
                        $observaciones = "numero de contrato: " . $resultado['numero_contrato'];

                        sql_insert("
                            INSERT INTO ventas_historial_estado 
                            (id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
                            VALUES (
                                ".GetSQLValue($cid, "int").",
                                ".GetSQLValue($_SESSION['usuario_id'], "int").",
                                ".GetSQLValue($_REQUEST['id_estado'], "int").",
                                ".GetSQLValue($descripcion, "text").",
                                NOW(),
                                ".GetSQLValue($observaciones, "text")."
                            )
                        ");


                        $stud_arr[0]["pmsg"] =
                            "Guardado - contrato generado: " . $resultado['numero_contrato'];

                    }



            
        } */
            
            //correo
            if ($envioCorreo==1 and $id_estado_modifico==true){
                //require_once ('correo_ventas.php');
            }

           
        }

         /* RL. 20251121  - Como no hay fotos adjuntas tanto en el ingreso como la actualizacion 
                            procedo a enviar correo para notificar que no se estan adjuntando fotos para la venta 
                            del vehiculo
         */
        if($enviar_correo_sin_fotos  == true)
        {
            require_once ('correo_ventas_verificacion.php');
        }
        

    } else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] =$verror;
        $stud_arr[0]["pcid"] = 0;
    }



    salida_json($stud_arr);
    exit;

} // fin guardar datos


?>

<div class="card-header d-print-none">
    <ul class="nav nav-tabs card-header-tabs" id="insp_tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="insp_tabdetalle" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_detalle');"  role="tab" >Detalle</a>      
      </li>
      <li class="nav-item">
        <a class="nav-link " id="insp_tabhistorial" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_historial');"   role="tab"  >Historial</a>
      </li> 
        <li class="nav-item">
            <a class="nav-link " id="insp_tabFotos" data-toggle="tab" href="#" onclick="ventas_cambiartab('nav_Fotos_venta');"   role="tab"  >Fotos</a>
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

    if (isset($row["elimpuesto"])) {$elimpuesto=$row["elimpuesto"];} else {$elimpuesto="";}
    if (isset($row["elestado"])) {$elestado=$row["elestado"];}else {$elestado="";}
    if (isset($row["codvehiculo"])) {$producto_etiqueta=$row["codvehiculo"]. ' '.$row["vehiculo"];   }else {$producto_etiqueta="";}
    if (isset($row["lafactura"])) {$lafactura=$row["lafactura"];} else {$lafactura="";}
    if (isset($row["latienda"])) {$latienda=$row["latienda"];} else {$latienda="";}

    if (isset($row["id"])) {$id= $row["id"]; } else {$id= "";}
    if (isset($row["id_usuario"])) {$id_usuario= $row["id_usuario"]; } else {$id_usuario= "";}
    if (isset($row["id_tienda"])) {$id_tienda= $row["id_tienda"]; } else {$id_tienda= "";}
    if (isset($row["id_producto"])) {$id_producto= $row["id_producto"]; } else {$id_producto= "";}
    if (isset($row["id_impuesto"])) {$id_impuesto= $row["id_impuesto"]; } else {$id_impuesto= "";}
    if (isset($row["id_factura"])) {$id_factura= $row["id_factura"]; } else {$id_factura= "";}
    if (isset($row["id_estado"])) {$id_estado= $row["id_estado"]; } else {$id_estado= "";}
    if (isset($row["fecha"])) {$fecha=$row["fecha"]; } else {$fecha= "";}
    if (isset($row["hora"])) {$hora= $row["hora"]; } else {$hora= "";}
    if (isset($row["numero"])) {$numero= $row["numero"]; } else {$numero= "";}
    if (isset($row["kilometraje"])) {$kilometraje= $row["kilometraje"]; } else {$kilometraje= "";}
    if (isset($row["precio_minimo"])) {$precio_minimo= $row["precio_minimo"]; } else {$precio_minimo= "";}
    if (isset($row["precio_maximo"])) {$precio_maximo= $row["precio_maximo"]; } else {$precio_maximo= "";}

    if (isset($row["precio_venta"])) {$precio_venta= $row["precio_venta"]; } else {$precio_venta= "";}
    if (isset($row["prima_venta"])) {$prima_venta= $row["prima_venta"]; } else {$prima_venta= "";}

    if (isset($row["persona_juridica"])) {$persona_juridica= $row["persona_juridica"]; } else {$persona_juridica= "";}
    if (isset($row["representante_legal_persona_juridica"])) {$representante_legal_persona_juridica= $row["representante_legal_persona_juridica"]; } else {$representante_legal_persona_juridica= "";}
    if (isset($row["representante_legal_identidad"])) {$representante_legal_identidad= $row["representante_legal_identidad"]; } else {$representante_legal_identidad= "";}

    if (isset($row["representante_legal_profesion"])) {$representante_legal_profesion= $row["representante_legal_profesion"]; } else {$representante_legal_profesion= "";}
    if (isset($row["representante_legal_direccion"])) {$representante_legal_direccion= $row["representante_legal_direccion"]; } else {$representante_legal_direccion= "";}



    if (isset($row["cilindraje"])) {$cilindraje= $row["cilindraje"]; } else {$cilindraje= "";}
    if (isset($row["trasmision"])) {$trasmision= $row["trasmision"]; } else {$trasmision= "";}
    if (isset($row["id_vendedor"])) {$id_vendedor= $row["id_vendedor"]; } else {$id_vendedor= "";}
    if (isset($row["id_televentas"])) {$id_televentas= $row["id_televentas"]; } else {$id_televentas= "";}
    if (isset($row["observaciones"])) {$observaciones= $row["observaciones"]; } else {$observaciones= "";}
    if (isset($row["foto_televentas"])) {$foto_televentas= $row["foto_televentas"]; } else {$foto_televentas= "";}
    if (isset($row["foto"])) {$foto= $row["foto"]; } else {$foto= "";}
    if (isset($row["fecha_negociacion"])) {$fecha_negociacion=$row["fecha_negociacion"]; } else {$fecha_negociacion= "";}
    if (isset($row["id_inspeccion"])) {$id_inspeccion=$row["id_inspeccion"]; } else {$id_inspeccion= "";}
    if (isset($row["reproceso"])) {$reproceso=$row["reproceso"]; } else {$reproceso= "";}
    $oferta = (isset($row["oferta"]) && $row["oferta"] == 1) ? true : false;

    if (isset($row["cliente_id"])) {$cliente_id= $row["cliente_id"]; } else {$cliente_id= "";}
    if (isset($row["cliente_nombre"])) {$cliente_nombre= $row["cliente_nombre"]; } else {$cliente_nombre= "";}


   
    $carShopPerfil="";
    $carShopPerfil=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
    $diff=0;
    if ($id_estado>=1 and $id_estado<20 ){         
        if (!es_nulo($fecha_negociacion)){
            $neg = date_create($fecha_negociacion);
            $hoy = date_create("now"); 
            $diff = date_diff($hoy,$neg);          
        }                 
        if (!tiene_permiso(169)){
            $disable_sec1=' readonly="readonly" ';              
        }     
    }   
    else{
        if ($id_estado==20){
           if (tiene_permiso(167)) {
              $disable_sec1=' ';  
              $disable_sec2=' ';  
           }else{
              $disable_sec1=' disabled="disabled" ';  
              $disable_sec2=' disabled="disabled" ';  
           }
        }
    }

    echo campo("id",("Codigo"),'hidden',$id,' ','');

?>


<div class="row">
    <div class="col-md">
        <?php echo campo("hora",("Fecha / Hora"),'label',formato_fechahora_de_mysql($hora),' ',' ');   ?>
    </div>
    <div class="col-md">
        <?php echo campo("numero","Numero",'label',$numero,' ',' '); ?>        
    </div>    
    <?php if(!es_nulo($diff)) { ?>
        <div class="col-md">
            <?php echo campo("dias","Dias en Negociacion",'label',$diff->days,' ',' '); ?>      
        </div>                
        <div class="col-md">    
            <?php echo campo("fecha_neg","Fecha en Negociacion",'label',formato_fechahora_de_mysql($fecha_negociacion),' ',' '); ?>    
        </div>        
    <?php } ?>        
    <?php echo campo("id_inspeccion","Numero",'hidden',$id_inspeccion,' ',' '); ?>          
</div>

<div class="row">
    <div class="col-md-4">                
         <?php if (tiene_permiso(167) && $id_estado<20 || es_nulo($id_estado)) {            
             echo campo("id_tienda","Sucursal",'select2',valores_combobox_db("tienda",$id_tienda,"nombre"," ",'','...'),' ',' required '.$disable_sec1,''); 
         }else{
             echo campo("id_tienda","sucursal",'hidden',$id_tienda,'','','');
             echo campo("id_tienda_label","Sucursal",'label',$latienda,'','','');
         }  
         ?> 
    </div>    
    <div class="col-md-8">         
         <?php if (tiene_permiso(167) && ($id_estado<20 || es_nulo($id_estado))) {            
               echo campo("id_producto","Vehiculo",'select2ajax',$id_producto,'  class=" "',' onchange="comb_actualizar_veh();"  ','get.php?a=3&t=1',$producto_etiqueta); 
         }else{
               echo campo("id_producto","Vehiculo",'hidden',$id_producto,'','','');
               echo campo("id_producto_label","Vehiculo",'label',$producto_etiqueta,'','','');
          }      
         ?>            
    </div> 
</div>    


<div class="row">
    <div class="col-md">
        <?php echo campo("kilometraje","Kilometraje",'number',$kilometraje,' ',$disable_sec1); ?>        
    </div>
    <div class="col-md">
        <?php echo campo("cilindraje","Cilindraje",'number',$cilindraje,' ',$disable_sec1); ?>        
    </div>    
    <div class="col-md">
        <?php if (tiene_permiso(167) && ($id_estado<20 || es_nulo($id_estado))) { 
              echo campo("trasmision","Trasmision",'select', valores_combobox_texto(app_tipo_trasmision,$trasmision),' ',$disable_sec1); 
        }else{
              echo campo("trasmision","Trasmision",'label',$trasmision,'','','');
        }   
        ?>   
    </div>    
    <div class="col-md">
        <?php echo campo("precio_minimo","Precio Minimo",'number',$precio_minimo,' ',$disable_sec1); ?>        
    </div>
    <div class="col-md">
        <?php echo campo("precio_maximo","Precio Maximo",'number',$precio_maximo,' ',$disable_sec1); ?>          
    </div>    
</div>  


<div class="row">
    <div id="clientediv" style="display:none;" class="col-md-12">

        <?php
        $nombre_cliente='';

        echo campo("nombre_cliente","",'hidden',$nombre_cliente,'','','');
        echo campo("cliente_id","Cliente",'select2ajax',$cliente_id,'class=" "','" '.$disable_sec1,'get.php?a=2&t=1',$cliente_nombre);
        echo campo("persona_juridica","persona juridica",'checkboxCustom',$persona_juridica,' ',$disable_sec2);
        ?>

        <div class="row">
            <div class="col-md-12">
                <?php echo campo(
                    "representante_legal_persona_juridica",
                    "Nombre representante Legal",
                    'text',
                    $representante_legal_persona_juridica,
                    ' ',
                    $disable_sec2
                ); ?>
            </div>
         </div>

        <div class="row">    

            <div class="col-md-6">
                <?php echo campo(
                    "representante_legal_identidad",
                    "Identidad de Representante Legal",
                    'text',
                    $representante_legal_identidad,
                    ' ',
                    $disable_sec2
                ); ?>
            </div>
            <div class="col-md-6">
                <?php echo campo(
                    "representante_legal_profesion",
                    "Profesión de Representante Legal",
                    'text',
                    $representante_legal_profesion,
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
         <?php echo campo("id_estado","Estado",'select2',valores_combobox_db("ventas_estado",$id_estado,"nombre"," where ventas_reparacion=2 ",'','...'),' ',' required '.$disable_sec2)  ?> 
         <?php /*echo campo("id_estado_name","Estado",'label',$elestado,'','','');*/ ?>
    </div>
    <div class="col-md">
         <?php if (tiene_permiso(167)){ 
              echo campo("id_impuesto","Impuesto",'select2',valores_combobox_db("ventas_impuestos",$id_impuesto,"nombre","  ",'','...'),' ',' required '.$disable_sec1);  
         }else{
              echo campo("id_impuesto","impuesto",'hidden',$id_impuesto,'','','');
              echo campo("id_impuesto_label","Impuesto",'label',$elimpuesto,'','','');
         }
         ?>         
    </div>
    <div class="col-md">
         <?php if (tiene_permiso(167)){ 
               echo campo("id_factura","Factura",'select2',valores_combobox_db("ventas_factura",$id_factura,"nombre"," ",'','...'),' ',' required '.$disable_sec1);  
         }else{
               echo campo("id_factura","Factura",'hidden',$id_factura,'','','');
               echo campo("id_factura_label","Factura",'label',$lafactura,'','','');
         }  
         ?>    
    </div>    
</div>

<div class="row">
    <div class="col-md">
         <?php echo campo("id_vendedor","Vendedor",'select2',valores_combobox_db('usuario',$id_vendedor,'nombre',' where activo=1 and grupo_id=18 ','','...'),' ',' required '.$disable_sec2);  ?> 
    </div>
    <div class="col-md">
         <?php echo campo("id_televentas","Tele Ventas",'select2',valores_combobox_db('usuario',$id_televentas,'nombre',' where activo=1 and grupo_id=18 ','','...'),' ',' required '.$disable_sec2);  ?> 
    </div>
    <div class="col-md">            
         <?php echo campo("precio_venta","Precio de Venta",'number',$precio_venta,' ',$disable_sec2); ?>                 
    </div>   
        <div class="col-md">            
         <?php echo campo("prima_venta","Prima de Venta",'number',$prima_venta,' ',$disable_sec2); ?>                 
    </div> 
</div>

<div class="row">
     <div class="col-md">
         <?php echo campo("oferta","Oferta Web",'checkboxCustom',$oferta,' ',$disable_sec2); ?>          
     </div>
</div>

<div class="row">
     <div class="col-md">
         <?php echo campo("observaciones","Observaciones",'textarea',$observaciones,' ',$disable_sec2); ?>         
     </div>
</div>

<div class="row">
     <div class="col-md">
          <?php if (tiene_permiso(177)){ 
                    echo campo("reproceso","Seleccione el Reproceso",'select', valores_combobox_texto(app_reproceso_ventas,$reproceso,'...'),' ',$disable_sec1); 
                 }
          ?>   
    </div>
</div>

<div class="row">
<div class="col-md" id="archivofoto">
<?php  
    
    if ($foto=='') {  echo campo_upload("foto","Adjuntar comprobante de pago",'upload','', '  ','',4,8,'NO',false ); }                   
    if ($foto_televentas=='') {  echo campo_upload("foto_televentas","Adjuntar de Chat TeleVentas",'upload','', '  ','',4,8,'NO',false ); }                   

?>
</div>
<div class="col-md">

<div class="" id="insp_fotos_thumbs">
  <?php
  if ($foto<>'') {
     $fext = substr($foto, -3);
     $fext = strtolower($fext);
            if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {    
                $ruta1 = 'uploa_d/' . $foto;           
                if (file_exists($ruta1)) {
                    $onclick = 'mostrar_foto(\'' . $foto . '\'); return false;';
                    $src= 'uploa_d/thumbnail/'.$foto;
                } else {
                    $onclick = 'mostrar_foto2(\'' . $foto . '\'); return false;';
                    $src= 'aws_bucket_s3/thumbnail/'.$foto;
                }
                echo '  <a href="#" onclick="'.$onclick.'" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$src.'" data-cod="'.$row["id"].'"></a> ';
               // if ($fecha<'2025-10-01'){                   
                    //echo '  <a href="#" onclick="mostrar_foto2(\''.$foto.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="aws_bucket_s3/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';
               // }else{
                //    echo '  <a href="#" onclick="mostrar_foto(\''.$foto.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto.'" data-cod="'.$row["id"].'"></a> ';                   
                //
                if(tiene_permiso(168))  { echo '  <a href="#" class="mr-5 foto_br'.$row["id"].'" onclick="ventas_dfoto(1); return false;" ><i class="fa fa-eraser"></i> Borrar</a> ';}
            } else {                
                echo '  <a href="uploa_d/'.$foto.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto.'</a> ';
            }
  }
   if ($foto_televentas<>'') {
     $fext = substr($foto_televentas, -3);
     $fext = strtolower($fext);
            if ($fext=='jpg' or $fext=='peg' or $fext=='png' or $fext=='gif') {   
                $ruta1 = 'uploa_d/' . $foto_televentas;           
                if (file_exists($ruta1)) {
                    $onclick = 'mostrar_foto(\'' . $foto_televentas . '\'); return false;';
                    $src= 'uploa_d/thumbnail/'.$foto_televentas;
                } else {
                    $onclick = 'mostrar_foto2(\'' . $foto_televentas . '\'); return false;';
                    $src= 'aws_bucket_s3/thumbnail/'.$foto_televentas;
                }
                echo '  <a href="#" onclick="'.$onclick.'" ><img class="img  img-thumbnail mb-3 mr-3" src="'.$src.'" data-cod="'.$row["id"].'"></a> ';
                //if ($fecha<'2025-10-01'){                   
                 //    echo '  <a href="#" onclick="mostrar_foto2(\''.$foto_televentas.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="aws_bucket_s3/thumbnail/'.$foto_televentas.'" data-cod="'.$row["id"].'"></a> ';
                //}else{
                //    echo '  <a href="#" onclick="mostrar_foto(\''.$foto_televentas.'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'.$foto_televentas.'" data-cod="'.$row["id"].'"></a> ';                   
                //}
                if(tiene_permiso(168))  { echo '  <a href="#" class="mr-5 foto_br'.$row["id"].'" onclick="ventas_dfoto(2); return false;" ><i class="fa fa-eraser"></i> Borrar</a> ';}
            } else {                
                echo '  <a href="uploa_d/'.$foto_televentas.'" target="_blank" class="img-thumbnail mb-3 mr-3" >'.$foto_televentas.'</a> ';
              
            }
  }
  ?>
</div>
</div>
</div>					

 
	<div class="botones_accion d-print-none bg-light px-3 py-2 mt-4 border-top ">
		<div class="row">
		<div class="col-sm">            
            <!--a href="#" onclick="procesar('ventas_mant_contrato.php?a=g','forma_ventas',''); return false;" class="btn btn-primary btn-block mb-2 xfrm" ><i class="fa fa-check"></i> Guardar</a-->                           
            <a href="javascript:void(0);" 
            id="btnguardar"
            class="btn btn-primary btn-block mb-2 xfrm" >
            <i class="fa fa-check"></i> Guardar</a> 
        </div>        
        <?php if (tiene_permiso(168)){ ?>
              <div class="col-sm"><a id="ventas_anularbtn"  href="#" onclick="ventas_anular(); return false;" class="btn btn-danger  btn-block mr-2 mb-2 xfrm"><i class="fa fa-trash-alt"></i> Borrar</a></div>		              
          <?php } ?>  

        <?php if (!es_nulo($id_inspeccion)){ ?>            
            <a href="#" onclick="abrir_hoja(); return false;" class="btn btn-outline-secondary mr-2 mb-2 xfrm" ><i class="fa fa-file-medical-alt"></i> Abrir Inspección</a>
        <?php } ?> 

<div style="margin-right:10px;">
    <a href="javascript:void(0);"
       id="btnContrato"
       target="_blank"
       class="btn btn-block mb-2"
       style="
           background-color:#e5533d;
           color:#fff;
           border:1px solid #e5533d;
       ">
        <i class="fas fa-file-pdf"></i> imprimir contrato
    </a>
</div>

<div>
    <a href="javascript:void(0);"
       id="btnActualizarContrato"
       class="btn btn-block mb-1"
       style="background-color:#f0ad4e;color:#fff;border:1px solid #f0ad4e;">
       <i class="fas fa-file-pdf"></i> Generar contrato
    </a>
</div>


<script>
$(function () {

    $('#btnguardar').on('click', function (e) {   
    e.preventDefault();

        const estado = $('#id_estado').val();

        popupconfirmar(
        'Confirmación',
        '¿Seguro desea guardar?',
        function () {
            procesar('ventas_mant_contrato.php?a=g', 'forma_ventas', '');
        }
    );


    });



$('#btnContrato').on('click', function (e) {
    e.preventDefault();

    const id = $('#id').val();
    if (!id) {
        mytoast('error', 'No hay ID',3000);
        return;
    }


            popupconfirmar(
            'Confirmación',
            '¿Deseas descargar el contrato?',
            function () {

                $.ajax({
                    url: 'ventas_mant_contrato.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        a: 'print_check',
                        id: id
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
                                'ventas_mant_contrato.php?a=print&id=' +
                                encodeURIComponent(id);

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
    if (!id) {
        alert('No hay ID');
        return;
    }

            popupconfirmar(
                'Confirmación',
                '¿Seguro desea generar el contrato? Los datos se sustituirán si previamente ya existía un contrato.',
                function () {

                    $.ajax({
                        url: 'ventas_mant_contrato.php',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            a: 'actcontrato',
                            id: id
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

});
</script>



         

        <div class="col-sm"><a href="#" onclick="$('#ModalWindow2').modal('hide');  return false;" class="btn btn-light btn-block mb-2 xfrm" >  <?php echo 'Cerrar'; ?></a></div>
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


<!-- fotos ventas -->
<div class="tab-pane fade " id="nav_Fotos_venta" role="tabpanel" >
    <div class="" id="insp_fotos_thumbs_ventas">
    </div>
    <div class="row">
    <div class="col-md-10" id="archivofotoventas">
    <?php

        $total_filas=0;
        $principal=false;
        $principalEncontrada=false;
 
        $sql="select id,nombre_archivo,fecha,principal from ventas_fotos where id_venta=".GetSQLValue($id,"int")." order by principal desc";
        $result = sql_select($sql);

        if ($result != false) {
    $total_filas = $result->num_rows;
    if ($total_filas > 0) {

        echo '<div style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            justify-items: center;
            align-items: start;
            justify-content: center;
        ">';

        while ($row = $result->fetch_assoc()) {
            $es_principal = (bool)$row["principal"];
            $fext = strtolower(substr($row["nombre_archivo"], -3));

            if (in_array($fext, ['jpg', 'peg', 'png', 'gif'])) {

                echo '<div style="text-align:center;">';

                // Imagen
                echo '<a href="#" class="foto_br' . $row["id"] . '" 
                        onclick="mostrar_foto(\'' . $row["nombre_archivo"] . '\',\'uploa_d_ventas/\'); return false;"
                        style="display:inline-block; transition: transform 0.2s ease-in-out;">
                        <img class="img img-thumbnail mb-2" 
                             src="uploa_d_ventas/thumbnail/' . $row["nombre_archivo"] . '" 
                             data-cod="' . $row["id"] . '" 
                             style="width:100%; max-width:160px; height:auto; border-radius:6px;">
                      </a>';

                // Controles
                if (tiene_permiso(186)){
                    echo '<div style="text-align:center; font-size:13px;">';
                    echo '<a href="#" class="mr-2 foto_br' . $row["id"] . '" 
                            onclick="borrar_fotodb(' . $row["id"] . ',\'' . $row["nombre_archivo"] . '\'); return false;"
                            style="color:#dc3545; text-decoration:none;">
                            <i class="fa fa-eraser"></i> Borrar
                        </a>';
                
                    if ($es_principal) {
                        echo '<i class="fa fa-star" title="Foto de portada" style="color:#f0c651;"> Portada</i>';
                    } else {
                       echo '<a href="#" onclick="marcar_portada(' . $row["id"] . ',\'' . $row["nombre_archivo"] . '\'); return false;"
                                style="color:#6c757d; text-decoration:none;">
                                <i class="far fa-star"></i> Portada
                             </a>';
                    
                    }
                    echo '</div>';
                }
                echo '</div>';
        
            }
        }

        echo '</div>';
    }
}





    if (tiene_permiso(186)){
        $a=$total_filas;
        while ($a < 10) {            
            echo '<div class="row"><div class="col-12">';
            echo '<div class="ins_varias_foto_div">';
            echo campo_upload_foto_ventas("ins_foto".$a,"Adjuntar Fotos",'upload','', '  ','',3,9,'NO',false,$principal );
            echo "</div></div></div>";
            echo "<hr>"; 
            $a++;
            

        }
    }   
    ?>
    </div>
</div>
</div>


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

function abrir_hoja(){    
    hinspeccion = $('#id_inspeccion').val();
    $('#ModalWindow2').modal('hide');
    get_page('pagina','inspeccion_mant.php?a=v&cid='+hinspeccion,'Hoja de Inspección',false);
}

function insp_guardar_foto(arch,campo){

           $('#'+campo).val(arch);                
           $('#files_'+campo).text('Guardado');
           $('#lk'+campo).html(arch);
           thumb_agregar(arch);    
}


function mostrar_foto(imagen,folder="uploa_d/") {

  Swal.fire({
  imageUrl: folder+imagen,

}); 
}

function mostrar_foto2(imagen,folder="aws_bucket_s3/") {

  Swal.fire({
  imageUrl: folder+imagen,

}); 

}


function thumb_agregar(archivo){
if (archivo!='' && archivo!=undefined) {
  
    var fext= archivo.substr(archivo.length - 3);

    if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
       $("#insp_fotos_thumbs").append('<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d/thumbnail/'+archivo+'" ></a>');
    } else {
       $("#insp_fotos_thumbs").append('<a href="uploa_d/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>');
    }
  }
}

function thumb_agregar_foto_venta(archivo,campo){
    var salida='';
    if (archivo!='' && archivo!=undefined) {
        
   
    var fext= archivo.substr(archivo.length - 3);

   if (fext=='jpg' || fext=='peg' || fext=='png' || fext=='gif') {
    salida='<a href="#" onclick="mostrar_foto(\''+archivo+'\'); return false;" ><img class="img  img-thumbnail mb-3 mr-3" src="uploa_d_ventas/thumbnail/'+archivo+'" ></a> ';
   } else {
    salida='<a href="uploa_d_ventas/'+archivo+'" target="_blank" class="img-thumbnail mb-3 mr-3" >'+archivo+'</a>';
   }

   $("#"+campo).closest('.ins_varias_foto_div').html(salida +'<a id="del_'+campo+'" href="#" onclick="insp_borrar_foto_venta(\''+archivo+'\',\'del_'+campo+'\'); return false;" class="btn  btn-outline-secondary ml-3 "><i class="fa fa-eraser"></i> Borrar</a>');
  }
}



function insp_borrar_foto_venta(arch,campo){

    var cid=$("#id").val();
    var datos= { a: "dfotoventas", cid: cid, pid: $("#pid").val() , arch: encodeURI(arch)} ;


Swal.fire({
	  title: 'Borrar Foto',
	  text:  'Desea Borrar la Foto o Documento adjunto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'ventas_mant_contrato.php',datos, function(json) {
                
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        console.log('->'+campo);
                        $("#"+campo).closest('.ins_foto_div').html('Eliminado');
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	abrir_ventas(cid); 
                    setTimeout(function() {
                        ventas_cambiartab('nav_Fotos_venta');
                        $('#insp_tabFotos').tab('show');

                    }, 300);
                    mytoast('success','Borrado',3000) ; 
            })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});
}

function borrar_fotodb(codid,arch){

    var cid=$("#id").val();
    var datos= { a: "dfotoventas", cid: cid, pid: $("#pid").val() , cod: codid, arch: encodeURI(arch)} ;
  

Swal.fire({
	  title: 'Borrar Foto',
	  text:  'Desea Borrar la Foto o Documento adjunto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'ventas_mant_contrato.php',datos, function(json) {
                
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        
                        $(".foto_br"+codid).hide();
                        mytoast('success',json[0].pmsg,3000) ;
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	  
                abrir_ventas(cid); 
                    setTimeout(function() {
                        ventas_cambiartab('nav_Fotos_venta');
                        $('#insp_tabFotos').tab('show');
                    }, 300);

                    mytoast('success','Borrado',3000) ; 
            })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});




}

function marcar_portada(codid,arch){
    var cid=$("#id").val();
    var datos= { a: "ufotoportadaventas", cid: cid, pid: $("#pid").val() , cod: codid, arch: encodeURI(arch)} ;
  
Swal.fire({
	  title: '⭐Foto de portada',
	  text:  'Desea Marcar la foto como portada?',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Si',
	  cancelButtonText:  'No'
	}).then((result) => {
	  if (result.value) {
	    
            $.post( 'ventas_mant_contrato.php',datos, function(json) {
               
                if (json.length > 0) {
                    if (json[0].pcode == 0) {
                        
                        mytoast('error',json[0].pmsg,3000) ;   
                    }
                    if (json[0].pcode == 1) {
                        
                        $(".foto_br"+codid).hide();
                        mytoast('success',json[0].pmsg,3000) ;
                    
                    }
                } else {mytoast('error',json[0].pmsg,3000) ; }
                
            })
            .done(function() {	  
                    abrir_ventas(cid); 
                    setTimeout(function() {
                        ventas_cambiartab('nav_Fotos_venta');
                        $('#insp_tabFotos').tab('show');
                    }, 300);

                    mytoast('success','Actualizado',3000) ; 
            })
            .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
            .always(function() {	  });

	  }
	});




}

    function insp_guardar_foto_ventas(arch,campo,isMain){
     
    var cid=$("#id").val();
    var datos= { a: "gfoto", arch: encodeURI(arch),cid:cid,isMain:isMain}; 


 	 $.post( 'ventas_mant_contrato.php',datos, function(json) {
	 			
		if (json.length > 0) {
			if (json[0].pcode == 0) {
				
				mytoast('error',json[0].pmsg,3000) ;   
			}
			if (json[0].pcode == 1) {
                $('#'+campo).val(arch);                
                $('#files_'+campo).text('Guardado');
                $('#lk'+campo).html(arch);
               // thumb_agregar(arch);
               thumb_agregar_foto_venta(arch,campo);
			
			}
		} else {mytoast('error',json[0].pmsg,3000) ; }
		  
	})
	  .done(function() { 

        abrir_ventas(cid); 
        setTimeout(function() {
            ventas_cambiartab('nav_Fotos_venta');

            $('#insp_tabFotos').tab('show');
        }, 300);

        mytoast('success','Guardado',3000) ;   
    
    })
	  .fail(function(xhr, status, error) {         mytoast('error',json[0].pmsg,3000) ; 	  })
	  .always(function() {	  }); 
    
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
	     ventas_procesar('ventas_mant_contrato.php?a=del','forma_ventas','del');        
	  }
	})

}

function ventas_dfoto(foto){
    Swal.fire({
	  title: 'Borrar',
	  text:  'Desea Borrar este foto?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText:  'Borrar',
	  cancelButtonText:  'Cancelar'
	}).then((result) => {
	  if (result.value) {
	     ventas_procesar('ventas_mant_contrato.php?a=dfoto&foto='+encodeURI(foto),'forma_ventas','dfoto');        
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


</script>