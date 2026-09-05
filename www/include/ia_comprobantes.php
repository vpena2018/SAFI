<?php
// ==============================================================================
// Modulo: Lectura de comprobantes de pago con IA
// ------------------------------------------------------------------------------
// Cuando el usuario sube la foto/PDF de un comprobante de pago (banco,
// financiera o cooperativa), este modulo:
//
//   1) Le pide a la IA (API de OpenAI, modelo gpt-4.1-mini) que "lea" el
//      comprobante y devuelva: banco, fecha, referencia y monto.
//   2) Busca en la tabla `ventas_comprobantes_pago` si ya existe un registro
//      ACTIVO con esos mismos 4 datos, para avisar que es un duplicado.
//   3) Guarda el comprobante (con sus datos) en esa tabla.
//
// Tambien lee el "recibo" (recibo de caja/oficial, un PDF aparte que se puede
// adjuntar despues) con IA (fecha, monto, y banco como referencia informativa)
// y valida que fecha y monto coincidan con los del comprobante ya registrado
// -no se compara referencia: el numero de recibo de caja y la referencia
// bancaria son datos distintos por diseño-. Ver extraer_datos_recibo_ia() y
// verificar_recibo_coincide_comprobante() mas abajo.
//
// Requiere la tabla creada por sql/comprobantes_pago.sql y la constante
// app_openai_api_key definida en include/config.php (mientras este vacia,
// la lectura automatica queda deshabilitada y el usuario llena los datos a
// mano; el resto de la funcionalidad -guardar y validar duplicados- funciona
// igual).
// ==============================================================================


/**
 * Indica si hay una API key de OpenAI configurada, es decir, si la
 * extraccion automatica con IA esta disponible.
 */
function ia_comprobantes_disponible() {
    return defined('app_openai_api_key') && trim(app_openai_api_key) != '';
}


/**
 * Le pide a la IA que lea un comprobante de pago (imagen o PDF) y devuelva
 * banco, fecha, referencia y monto.
 *
 * @param string $ruta_archivo Ruta absoluta del archivo ya subido al servidor.
 * @return array {
 *     success: bool           true si la IA respondio y se pudo interpretar el JSON,
 *     banco: string|null,
 *     fecha: string|null      formato ISO "YYYY-MM-DD",
 *     referencia: string|null,
 *     monto: float|null,
 *     error: string|null      motivo cuando success=false,
 *     raw: string             texto crudo devuelto por la IA (para auditoria),
 * }
 */
function extraer_datos_comprobante_ia($ruta_archivo) {

    $vacio = ['success' => false, 'banco' => null, 'fecha' => null, 'referencia' => null, 'monto' => null, 'error' => '', 'raw' => ''];

    $lista_bancos = implode(', ', ia_comprobantes_lista_bancos_hn());

    $prompt = "Este archivo es un comprobante de pago (deposito, transferencia o pago) emitido por un banco, "
        . "financiera o cooperativa en Honduras. Lee el documento y devuelve UNICAMENTE un JSON con exactamente "
        . "estas claves:\n"
        . "{\n"
        . "  \"banco\": nombre del banco/financiera/cooperativa que emite el comprobante (string, o null si no se lee),\n"
        . "  \"fecha\": fecha del comprobante en formato YYYY-MM-DD (string, o null si no se lee),\n"
        . "  \"referencia\": numero de referencia/transaccion/autorizacion (string, o null si no se lee),\n"
        . "  \"monto\": monto total del comprobante, solo numero con punto decimal, sin simbolo de moneda ni comas (numero, o null si no se lee)\n"
        . "}\n"
        . "Para el campo \"banco\": estos son algunos bancos, financieras y cooperativas conocidos de Honduras "
        . "(lista de referencia, NO es una lista cerrada — pueden existir otros que no esten aqui): "
        . $lista_bancos . ". Si el nombre que aparece en el comprobante corresponde a uno de estos, devuelve el "
        . "texto EXACTO de la lista, letra por letra tal como esta escrito arriba (por ejemplo, si el comprobante "
        . "solo dice \"BAC\" o \"Credomatic\", devuelve exactamente \"BAC Credomatic\"; esto es importante porque "
        . "ese texto se usa para comparar y evitar comprobantes duplicados, asi que debe quedar siempre identico). "
        . "Si el comprobante muestra un banco/financiera/cooperativa que NO esta en esta lista, transcribe el "
        . "nombre tal como aparece en el documento; nunca inventes un nombre que no este escrito en el comprobante.\n"
        . "Si algun dato no aparece claramente en el documento, usa null en esa clave en vez de adivinar. "
        . "Esto es MUY importante para la fecha: si el documento muestra dia y mes pero NO muestra el año en "
        . "ningun lado, no inventes ni asumas el año (ni el actual ni ningun otro) — en ese caso devuelve "
        . "\"fecha\": null. Nunca conviene un año inventado a que quede en null: el usuario lo completa a mano.";

    $r = ia_leer_documento_con_ia($ruta_archivo, $prompt);

    if (!$r['success']) {
        $vacio['error'] = $r['error'];
        $vacio['raw'] = $r['raw'];
        return $vacio;
    }

    $datos = $r['datos'];

    return [
        'success' => true,
        'banco' => isset($datos['banco']) ? trim((string) $datos['banco']) : null,
        'fecha' => isset($datos['fecha']) ? trim((string) $datos['fecha']) : null,
        'referencia' => isset($datos['referencia']) ? trim((string) $datos['referencia']) : null,
        'monto' => (isset($datos['monto']) && $datos['monto'] !== null && $datos['monto'] !== '') ? floatval($datos['monto']) : null,
        'error' => null,
        'raw' => $r['raw'],
    ];
}


/**
 * Le pide a la IA que lea un "recibo" (recibo de caja / recibo oficial de la empresa, en PDF o
 * imagen) y devuelva fecha, monto y empresa emisora -los datos objetivos que se pueden cruzar
 * contra el comprobante ya registrado y contra el nombre de la empresa esperada-.
 *
 * El numero de recibo de caja del recibo NO es el mismo dato que la referencia bancaria del
 * comprobante (son numeros distintos por diseño), asi que no se le pide a la IA "el numero de
 * este recibo" para compararlo directo. En cambio, cuando se conoce la referencia del
 * comprobante ($referencia_comprobante), se le da como CONTEXTO a la IA y se le pide que
 * revise si ese numero especifico aparece mencionado en algun lado del recibo (por ejemplo en
 * el campo "Documento", en una descripcion o memo) -algunas empresas si anotan ahi el numero de
 * la transaccion bancaria original, otras no-. El banco se devuelve solo como referencia
 * informativa (puede aparecer mezclado en una linea contable, no siempre es confiable).
 *
 * @param string      $ruta_archivo
 * @param string|null $referencia_comprobante Numero de referencia del comprobante ya registrado,
 *                                             para darselo de contexto a la IA (opcional).
 * @return array{success:bool, banco:?string, fecha:?string, monto:?float, empresa:?string, referencia_encontrada:?string, error:?string, raw:string}
 */
function extraer_datos_recibo_ia($ruta_archivo, $referencia_comprobante = null) {

    $vacio = ['success' => false, 'banco' => null, 'fecha' => null, 'monto' => null, 'empresa' => null, 'referencia_encontrada' => null, 'error' => '', 'raw' => ''];

    $prompt = "Este archivo es un RECIBO DE CAJA / recibo oficial de pago emitido por una empresa en Honduras "
        . "(distinto de un comprobante bancario: es el recibo que la empresa le entrega al cliente). Lee el "
        . "documento y devuelve UNICAMENTE un JSON con exactamente estas claves:\n"
        . "{\n"
        . "  \"fecha\": fecha del recibo en formato YYYY-MM-DD (string, o null si no se lee),\n"
        . "  \"monto\": monto total del recibo, solo numero con punto decimal, sin simbolo de moneda ni comas (numero, o null si no se lee),\n"
        . "  \"banco\": banco o cuenta bancaria mencionada en el recibo, si aparece (string, o null si no aparece),\n"
        . "  \"empresa\": nombre de la empresa que emite el recibo, tal como aparece en el encabezado/membrete del "
        . "documento (string, o null si no se lee claramente),\n"
        . "  \"referencia_encontrada\": ver instruccion especial abajo (string, o null)\n"
        . "}\n"
        . "IMPORTANTE sobre la fecha: este tipo de recibo casi siempre trae DOS fechas distintas: (1) una fecha "
        . "de impresion, generalmente arriba del todo junto a un nombre de usuario (ej. \"KMEJIA 04/09/2026 "
        . "15:12:49\"), y (2) la fecha real del recibo, marcada con la etiqueta \"Fecha:\" en el encabezado del "
        . "documento. Usa SIEMPRE la fecha etiquetada \"Fecha:\" (la fecha del recibo), NUNCA la fecha/hora de "
        . "impresion, aunque la de impresion aparezca primero o mas grande.\n"
        . "Si el documento muestra dia y mes pero NO muestra el año en ningun lado, no inventes el año: devuelve "
        . "\"fecha\": null. Si algun otro dato no aparece claramente, usa null en esa clave en vez de adivinar.";

    if ($referencia_comprobante !== null && trim((string) $referencia_comprobante) !== '') {
        $prompt .= "\n\nCONTEXTO ADICIONAL: el comprobante de pago bancario relacionado con este recibo tiene el "
            . "numero de referencia/transaccion \"" . trim((string) $referencia_comprobante) . "\". Revisa con "
            . "cuidado todo el documento (por ejemplo el campo \"Documento\", alguna descripcion, memo o nota) "
            . "para ver si ese numero de referencia aparece mencionado en algun lado del recibo. Si lo encontras, "
            . "devuelve en \"referencia_encontrada\" exactamente el numero que aparece en el recibo. Si NO "
            . "aparece ese numero (ni ningun otro numero de referencia) en ninguna parte del recibo, devuelve "
            . "\"referencia_encontrada\": null -no es raro que el recibo no mencione la referencia bancaria, "
            . "muchas empresas no la anotan ahi-.";
    }

    $r = ia_leer_documento_con_ia($ruta_archivo, $prompt);

    if (!$r['success']) {
        $vacio['error'] = $r['error'];
        $vacio['raw'] = $r['raw'];
        return $vacio;
    }

    $datos = $r['datos'];

    return [
        'success' => true,
        'banco' => isset($datos['banco']) ? trim((string) $datos['banco']) : null,
        'fecha' => isset($datos['fecha']) ? trim((string) $datos['fecha']) : null,
        'monto' => (isset($datos['monto']) && $datos['monto'] !== null && $datos['monto'] !== '') ? floatval($datos['monto']) : null,
        'empresa' => isset($datos['empresa']) ? trim((string) $datos['empresa']) : null,
        'referencia_encontrada' => (isset($datos['referencia_encontrada']) && trim((string) $datos['referencia_encontrada']) !== '') ? trim((string) $datos['referencia_encontrada']) : null,
        'error' => null,
        'raw' => $r['raw'],
    ];
}


/**
 * Helper interno compartido: le manda un archivo (imagen o PDF) a la IA junto con un prompt ya
 * armado, y devuelve el JSON de respuesta ya parseado (sin todavia interpretar sus claves -eso
 * lo hace cada funcion que llama a este helper, segun lo que le pidio a la IA-).
 */
function ia_leer_documento_con_ia($ruta_archivo, $prompt) {

    $vacio = ['success' => false, 'datos' => null, 'error' => '', 'raw' => ''];

    if (!ia_comprobantes_disponible()) {
        $vacio['error'] = 'No hay una API key de OpenAI configurada (app_openai_api_key).';
        return $vacio;
    }

    if (!file_exists($ruta_archivo)) {
        $vacio['error'] = 'El archivo no existe en el servidor.';
        return $vacio;
    }

    // La IA solo puede "ver" imagenes y PDF. Word/Excel no se pueden leer asi.
    $media_type = ia_comprobantes_media_type($ruta_archivo);
    if ($media_type === null) {
        $vacio['error'] = 'Tipo de archivo no soportado para lectura automatica (solo imagenes y PDF). Complete los datos manualmente.';
        return $vacio;
    }

    $contenido_base64 = base64_encode(file_get_contents($ruta_archivo));

    // Un PDF se manda como bloque "file"; una imagen (jpg/png/gif) como "image_url" en base64.
    // (API de OpenAI, Chat Completions: https://platform.openai.com/docs/guides/pdf-files)
    if ($media_type === 'application/pdf') {
        $bloque_archivo = [
            'type' => 'file',
            'file' => [
                'filename' => basename($ruta_archivo),
                'file_data' => 'data:' . $media_type . ';base64,' . $contenido_base64,
            ],
        ];
    } else {
        $bloque_archivo = [
            'type' => 'image_url',
            'image_url' => [
                'url' => 'data:' . $media_type . ';base64,' . $contenido_base64,
            ],
        ];
    }

    $payload = [
        'model' => defined('app_openai_model') ? app_openai_model : 'gpt-4.1-mini',
        'max_tokens' => 1024,
        // temperature=0: la lectura debe ser lo mas consistente posible entre llamadas, porque
        // el comprobante se vuelve a leer una segunda vez al guardar (ver guardar_comprobante en
        // ventas_comprobantes_pago.php) para verificar que los datos no se hayan modificado.
        'temperature' => 0,
        // Fuerza que la respuesta sea un JSON valido (sin bloques ```json ni texto extra).
        'response_format' => ['type' => 'json_object'],
        'messages' => [[
            'role' => 'user',
            'content' => [
                $bloque_archivo,
                [
                    'type' => 'text',
                    'text' => $prompt,
                ],
            ],
        ]],
    ];

    $respuesta = ia_comprobantes_llamar_api($payload);

    if (!$respuesta['success']) {
        $vacio['error'] = $respuesta['error'];
        $vacio['raw'] = $respuesta['raw'];
        return $vacio;
    }

    $datos = ia_comprobantes_parsear_json($respuesta['texto']);

    if ($datos === null) {
        $vacio['error'] = 'La IA respondio pero no se pudo interpretar como JSON.';
        $vacio['raw'] = $respuesta['texto'];
        return $vacio;
    }

    return ['success' => true, 'datos' => $datos, 'error' => null, 'raw' => $respuesta['texto']];
}


// Nombre de la empresa que debe aparecer en el encabezado/membrete del recibo. Si la IA lee un
// nombre de empresa distinto, se rechaza el recibo (evita que se suba el recibo de otra
// empresa). Comparacion sin importar mayus/minus, y solo verifica que el texto CONTENGA este
// nombre (para tolerar variaciones como "S.A. DE C.V." al final).
define('RECIBO_EMPRESA_ESPERADA', 'INVERSIONES GLOBALES');


/**
 * Compara los datos de un recibo (leidos con IA) contra los del comprobante ya registrado al
 * que pertenece, para confirmar que sean del mismo pago, y contra el nombre de empresa esperado
 * (RECIBO_EMPRESA_ESPERADA). Se comparan fecha y monto directo contra el comprobante. La
 * referencia se le pasa a la IA como contexto (ver extraer_datos_recibo_ia) para que revise si
 * aparece mencionada en el recibo; si el recibo SI menciona una referencia y es distinta a la
 * del comprobante, se rechaza -pero si el recibo simplemente no menciona ninguna referencia (lo
 * mas comun), eso no bloquea nada, porque el numero de recibo de caja y la referencia bancaria
 * son datos distintos por diseño. Si el comprobante no tiene fecha/monto/referencia guardados,
 * o la IA no logra leer algun dato, ese dato en particular simplemente no se puede verificar y
 * se deja pasar.
 *
 * @return array{ok: bool, motivo: string}
 */
function verificar_recibo_coincide_comprobante($ruta_recibo, $fecha_comprobante_mysql, $monto_comprobante, $referencia_comprobante = null) {

    $recibo = extraer_datos_recibo_ia($ruta_recibo, $referencia_comprobante);

    if (!$recibo['success']) {
        // Si la IA no esta disponible o no pudo leer el recibo, no hay con que comparar; se deja
        // pasar (el recibo es un adjunto de referencia, no bloquea el flujo si no se puede leer).
        return ['ok' => true, 'motivo' => ''];
    }

    if ($recibo['empresa'] !== null && stripos($recibo['empresa'], RECIBO_EMPRESA_ESPERADA) === false) {
        return ['ok' => false, 'motivo' => 'El recibo no parece ser de ' . RECIBO_EMPRESA_ESPERADA . ' (el encabezado dice "' . $recibo['empresa'] . '").'];
    }

    if ($fecha_comprobante_mysql && $recibo['fecha'] !== null && $recibo['fecha'] !== $fecha_comprobante_mysql) {
        return ['ok' => false, 'motivo' => 'La fecha del recibo (' . $recibo['fecha'] . ') no coincide con la fecha del comprobante (' . $fecha_comprobante_mysql . ').'];
    }

    if ($referencia_comprobante !== null && trim((string) $referencia_comprobante) !== '' && $recibo['referencia_encontrada'] !== null
        && strtolower(trim($recibo['referencia_encontrada'])) !== strtolower(trim((string) $referencia_comprobante))) {
        return ['ok' => false, 'motivo' => 'El recibo menciona una referencia distinta (' . $recibo['referencia_encontrada'] . ') a la del comprobante (' . $referencia_comprobante . ').'];
    }

    if ($monto_comprobante !== null && $monto_comprobante !== '' && $recibo['monto'] !== null
        && round((float) $recibo['monto'], 2) !== round((float) $monto_comprobante, 2)) {
        return ['ok' => false, 'motivo' => 'El monto del recibo (' . $recibo['monto'] . ') no coincide con el monto del comprobante (' . $monto_comprobante . ').'];
    }

    return ['ok' => true, 'motivo' => ''];
}


/**
 * Verifica en el SERVIDOR (no confiando en lo que mande el navegador) que los datos que se
 * van a guardar no hayan sido alterados respecto a lo que dice el comprobante real. El
 * "readonly" de los campos en el modal es solo cosmetico -cualquiera con las herramientas de
 * desarrollador del navegador lo puede saltar- asi que antes de guardar se vuelve a leer el
 * archivo con la IA y se compara: si para algun campo la IA logra leer un valor y ese valor
 * NO coincide con lo que se esta guardando, se rechaza. Los campos que la IA no logra leer
 * (ni en esta segunda lectura) no se pueden verificar automaticamente, asi que se aceptan tal
 * como vienen (son los que el usuario llena a mano cuando la IA no pudo leerlos).
 *
 * @return array{ok: bool, motivo: string} ok=false cuando algun dato no coincide con la relectura.
 */
function verificar_comprobante_no_modificado($ruta_archivo, $banco, $fecha_mysql, $referencia, $monto) {

    $verif = extraer_datos_comprobante_ia($ruta_archivo);

    if (!$verif['success']) {
        // Si la IA no esta disponible (o el archivo ya no existe, etc.) no hay con que comparar;
        // se deja pasar (equivale al modo 100% manual, que nunca tuvo esta verificacion).
        return ['ok' => true, 'motivo' => ''];
    }

    if ($verif['banco'] !== null && strtolower(trim($verif['banco'])) !== strtolower(trim((string) $banco))) {
        return ['ok' => false, 'motivo' => 'El banco no coincide con lo que la IA leyo del comprobante.'];
    }

    if ($verif['fecha'] !== null && $verif['fecha'] !== $fecha_mysql) {
        return ['ok' => false, 'motivo' => 'La fecha no coincide con lo que la IA leyo del comprobante.'];
    }

    if ($verif['referencia'] !== null && strtolower(trim($verif['referencia'])) !== strtolower(trim((string) $referencia))) {
        return ['ok' => false, 'motivo' => 'La referencia no coincide con lo que la IA leyo del comprobante.'];
    }

    if ($verif['monto'] !== null && round((float) $verif['monto'], 2) !== round((float) $monto, 2)) {
        return ['ok' => false, 'motivo' => 'El monto no coincide con lo que la IA leyo del comprobante.'];
    }

    return ['ok' => true, 'motivo' => ''];
}


/**
 * Bancos, financieras y cooperativas conocidos de Honduras, para ayudar a la IA a
 * reconocer y normalizar el nombre (evita que "BAC" y "Credomatic" queden como
 * bancos distintos, por ejemplo). Es solo una lista de referencia -no cerrada-,
 * el prompt le indica a la IA que igual transcriba cualquier otro nombre que no
 * este aqui. Se puede ajustar esta lista sin tocar el resto del codigo.
 */
function ia_comprobantes_lista_bancos_hn() {
    return [
        // Bancos
        'Banco Atlantida',
        'BAC Honduras',
        'Banco de Occidente',
        'Banco Ficohsa',
        'Banpais',
        'Banco Lafise Honduras',
        'Banco Promerica',
        'Banco Azteca Honduras',
        'Banco Davivienda Honduras',
        // Financieras
        'Financiera Solidaria (Finsol)',
        'Financiera Comercial Hondureña (Ficensa)',
        'Financiera Credi Q',        
        'Financiera COFISA',
        // Cooperativas
        'Cooperativa Sagrada Familia',
        'Cooperativa Elga',        
        'Cooperativa Caceenp',
        'Cooperativa Chortega',        
        'Cooperativa Apaguiz',
        'Cooperativa Tocoa',
    ];
}


/**
 * Determina el media type que se le manda a la IA segun la extension del
 * archivo. Devuelve null cuando el tipo de archivo no se puede "leer"
 * (Word, Excel, txt, etc.) y por lo tanto la extraccion automatica no aplica.
 */
function ia_comprobantes_media_type($ruta_archivo) {
    $ext = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'png':
            return 'image/png';
        case 'gif':
            return 'image/gif';
        case 'pdf':
            return 'application/pdf';
        default:
            return null; // doc, docx, xls, xlsx, txt, etc.
    }
}


/**
 * Llamada cruda (cURL) a la API de OpenAI (Chat Completions).
 * Aislada en su propia funcion para que extraer_datos_comprobante_ia() no se
 * mezcle con el detalle de HTTP/cURL.
 */
function ia_comprobantes_llamar_api($payload) {

    $ch = curl_init('https://api.openai.com/v1/chat/completions');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'Authorization: Bearer ' . app_openai_api_key,
        ],
        CURLOPT_TIMEOUT => 45,
    ]);

    // En algunos entornos de Windows (XAMPP / servidor embebido de PHP) no hay un paquete de
    // certificados raiz configurado en php.ini, y curl no puede validar el certificado SSL de
    // la API (error "unable to get local issuer certificate"). Para no depender de esa
    // configuracion del servidor, se incluye un paquete de certificados con el proyecto mismo.
    // No se desactiva la verificacion SSL (seria inseguro); solo se le indica a curl donde
    // encontrar los certificados confiables.
    $cacert = __DIR__ . '/cacert.pem';
    if (file_exists($cacert)) {
        curl_setopt($ch, CURLOPT_CAINFO, $cacert);
    }

    $body = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        return ['success' => false, 'error' => 'No se pudo conectar con la API de IA: ' . $curl_error, 'raw' => ''];
    }

    $json = json_decode($body, true);

    if ($http_code !== 200 || !is_array($json)) {
        $msg = is_array($json) && isset($json['error']['message']) ? $json['error']['message'] : ('HTTP ' . $http_code);
        return ['success' => false, 'error' => 'La API de IA respondio con error: ' . $msg, 'raw' => $body];
    }

    // La respuesta de /v1/chat/completions trae el texto en choices[0].message.content
    $texto = $json['choices'][0]['message']['content'] ?? '';

    if ($texto === '') {
        return ['success' => false, 'error' => 'La IA no devolvio texto en la respuesta.', 'raw' => $body];
    }

    return ['success' => true, 'error' => null, 'texto' => $texto];
}


/**
 * Convierte el texto que devuelve la IA en un arreglo. Tolera que venga
 * envuelto en un bloque ```json ... ``` aunque se le pidio que no lo haga.
 */
function ia_comprobantes_parsear_json($texto) {
    $texto = trim($texto);
    $texto = preg_replace('/^```(json)?/i', '', $texto);
    $texto = preg_replace('/```$/', '', $texto);
    $texto = trim($texto);

    $datos = json_decode($texto, true);

    return (json_last_error() === JSON_ERROR_NONE && is_array($datos)) ? $datos : null;
}


// ==============================================================================
// Persistencia (tabla ventas_comprobantes_pago) y validacion de duplicados
// ==============================================================================


/**
 * Busca si ya existe un comprobante ACTIVO registrado con exactamente el
 * mismo banco + fecha + referencia + monto. Esa es la validacion que evita
 * que el mismo comprobante se suba 2 veces.
 *
 * @param string $banco       Texto libre (se compara sin importar mayus/minus ni espacios).
 * @param string $fecha_mysql Fecha en formato "YYYY-MM-DD".
 * @param string $referencia  Texto libre (idem banco).
 * @param mixed  $monto       Numero (se compara con 2 decimales).
 * @return array|false Fila del duplicado encontrado (con id_venta, fecha_registro, etc.) o false si no hay.
 */
function buscar_comprobante_pago_duplicado($banco, $fecha_mysql, $referencia, $monto) {

    $sql = "SELECT * FROM ventas_comprobantes_pago
            WHERE activo = 1
              AND LOWER(TRIM(banco)) = LOWER(TRIM(" . GetSQLValue($banco, 'text') . "))
              AND fecha_comprobante = " . GetSQLValue($fecha_mysql, 'date') . "
              AND LOWER(TRIM(referencia)) = LOWER(TRIM(" . GetSQLValue($referencia, 'text') . "))
              AND monto = " . GetSQLValue(round(floatval($monto), 2), 'double') . "
            LIMIT 1";

    $result = sql_select($sql);

    if ($result !== false && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return false;
}


/**
 * Inserta el comprobante de pago ya validado (no duplicado) en la tabla de
 * control.
 *
 * @return int|false Id del nuevo registro, o false si fallo el insert.
 */
function guardar_comprobante_pago($id_venta, $archivo, $banco, $fecha_mysql, $referencia, $monto, $origen_datos, $ia_raw, $id_usuario) {

    $sql = "INSERT INTO ventas_comprobantes_pago
                (id_venta, archivo, banco, fecha_comprobante, referencia, monto, origen_datos, ia_respuesta_raw, id_usuario, fecha_registro, activo)
            VALUES (
                " . intval($id_venta) . ",
                " . GetSQLValue($archivo, 'text') . ",
                " . GetSQLValue($banco, 'text') . ",
                " . GetSQLValue($fecha_mysql, 'date') . ",
                " . GetSQLValue($referencia, 'text') . ",
                " . GetSQLValue(round(floatval($monto), 2), 'double') . ",
                " . GetSQLValue($origen_datos === 'ia' ? 'ia' : 'manual', 'text') . ",
                " . GetSQLValue($ia_raw, 'text') . ",
                " . intval($id_usuario) . ",
                NOW(),
                1
            )";

    return sql_insert($sql);
}


/**
 * Guarda (o reemplaza) el archivo de "recibo" de un comprobante ya registrado.
 * A diferencia del comprobante en si (obligatorio desde el inicio), el recibo
 * se puede agregar en cualquier momento despues, desde la fila del comprobante
 * en la tabla de "Comprobantes de Pago Registrados".
 *
 * @return bool true si se actualizo el registro.
 */
function guardar_archivo_recibo($id_comprobante, $id_venta, $archivo_recibo) {
    $sql = "UPDATE ventas_comprobantes_pago
            SET archivo_recibo = " . GetSQLValue($archivo_recibo, 'text') . "
            WHERE id = " . intval($id_comprobante) . "
              AND id_venta = " . intval($id_venta) . "
            LIMIT 1";

    return sql_update($sql);
}


/**
 * Lista los comprobantes activos ya registrados para una venta (para
 * mostrarlos en pantalla como historial de control).
 */
function listar_comprobantes_pago_venta($id_venta) {
    $filas = [];
    $result = sql_select("SELECT ventas_comprobantes_pago.*, usuario.nombre AS usuario_nombre
                           FROM ventas_comprobantes_pago
                           LEFT OUTER JOIN usuario ON (ventas_comprobantes_pago.id_usuario = usuario.id)
                           WHERE ventas_comprobantes_pago.id_venta = " . intval($id_venta) . "
                             AND ventas_comprobantes_pago.activo = 1
                           ORDER BY ventas_comprobantes_pago.fecha_registro DESC");

    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            $filas[] = $row;
        }
    }

    return $filas;
}


/**
 * Convierte una fecha ISO "YYYY-MM-DD" (la que devuelve la IA) al formato de
 * fecha que usa la sesion del usuario (dd/mm/yyyy o mm/dd/yyyy), que es el
 * que esperan validar() y formato_fecha_a_mysql() en el resto del sistema.
 * Si la fecha no viene en formato ISO valido, se devuelve vacia.
 */
function ia_fecha_iso_a_formato_sesion($fecha_iso) {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim((string) $fecha_iso), $m)) {
        return '';
    }
    list(, $anio, $mes, $dia) = $m;

    if (!checkdate((int) $mes, (int) $dia, (int) $anio)) {
        return '';
    }

    $formato = $_SESSION['formato_fecha'] ?? 'dd/mm/yyyy';

    return ($formato === 'mm/dd/yyyy') ? "$mes/$dia/$anio" : "$dia/$mes/$anio";
}
