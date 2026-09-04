<?php
// ==============================================================================
// Programa: ventas_comprobantes_pago.php
// ------------------------------------------------------------------------------
// Administra los comprobantes de pago (banco/fecha/referencia/monto) de una
// venta: permite subir hasta MAX_COMPROBANTES archivos (imagen o PDF), los lee
// con IA cuando hay API key configurada (o los recibe digitados a mano), valida
// que no se repita un comprobante ya registrado, y los guarda en la tabla
// ventas_comprobantes_pago.
//
// Sigue el mismo patron que ventas_fotos_web.php: es un programa aparte que se
// carga por AJAX (.load()) dentro de la pestaña "Comprobantes de Pago" de
// ventas_mant_contrato.php, para no cargar de codigo esa pantalla principal.
// Es independiente del campo "foto" de la pestaña "Fotos de Comprobante de
// Pago" (esa sigue funcionando exactamente igual que antes).
// ==============================================================================

require_once ('include/framework.php');
require_once ('include/ia_comprobantes.php');

define('MAX_COMPROBANTES_POR_VENTA', 10);

if (!isset($_REQUEST['a'])) { $accion = 'v'; } else { $accion = $_REQUEST['a']; }
$cid = 0;
if (isset($_REQUEST['cid'])) { $cid = intval($_REQUEST['cid']); }


// Arma el HTML del contador + tabla de comprobantes registrados. Se usa tanto en la
// vista completa como en la accion "tabla" (refresco parcial, sin recargar el widget
// de subida ni el modal, para no perder una cola de archivos que se este subiendo).
function html_tabla_comprobantes($cid) {
    $comprobantes = listar_comprobantes_pago_venta($cid);
    $total = count($comprobantes);

    $html = '<div class="small text-muted mb-2">' . $total . ' de ' . MAX_COMPROBANTES_POR_VENTA . ' comprobantes registrados</div>';

    if ($total > 0) {
        $html .= '<table class="table table-sm table-bordered"><thead><tr>
                    <th>Archivo</th><th>Banco</th><th>Fecha</th><th>Referencia</th><th>Monto</th><th>Origen</th><th>Registrado por</th><th></th>
                  </tr></thead><tbody>';
        foreach ($comprobantes as $c) {
            $html .= '<tr>'
                . '<td><a href="uploa_d_ventas/' . rawurlencode($c['archivo']) . '" target="_blank">' . htmlspecialchars($c['archivo']) . '</a></td>'
                . '<td>' . htmlspecialchars($c['banco']) . '</td>'
                . '<td>' . formato_fecha_de_mysql($c['fecha_comprobante']) . '</td>'
                . '<td>' . htmlspecialchars($c['referencia']) . '</td>'
                . '<td>' . number_format((float) $c['monto'], 2) . '</td>'
                . '<td>' . (($c['origen_datos'] == 'ia') ? 'IA' : 'Manual') . '</td>'
                . '<td>' . htmlspecialchars($c['usuario_nombre'] ?? '') . '</td>'
                // Mismo permiso (168) que se usa para "Borrar" en la pestaña "Fotos de Comprobante de Pago".
                . '<td>' . (tiene_permiso(168) ? '<a href="#" onclick="comprobante_borrar(' . (int) $c['id'] . '); return false;" title="Borrar"><i class="fa fa-eraser"></i></a>' : '') . '</td>'
                . '</tr>';
        }
        $html .= '</tbody></table>';
    } else {
        $html .= '<p class="text-muted">Todavia no hay comprobantes de pago registrados para esta venta.</p>';
    }

    return $html;
}


// ---- Refresco parcial: solo el contador + tabla (no toca el widget de subida ni el modal). ----
if ($accion == 'tabla') {
    if ($cid <= 0) { exit; }
    echo html_tabla_comprobantes($cid);
    exit;
}


// ---- Lee el comprobante con IA y avisa si ya fue registrado antes. No guarda nada todavia. ----
if ($accion == 'extraer_comprobante') {
    header('Content-Type: application/json; charset=utf-8');

    $archivo = isset($_REQUEST['archivo']) ? sanear_string($_REQUEST['archivo']) : '';

    $salida = [
        'pcode'         => 0,
        'ia_disponible' => ia_comprobantes_disponible(),
        'banco'         => '',
        'fecha'         => '',   // en el formato de fecha de la sesion (dd/mm/yyyy), listo para el formulario
        'referencia'    => '',
        'monto'         => '',
        'ia_raw'        => '',
        'ia_error'      => '',
        'duplicado'     => false,
        'pmsg'          => '',
    ];

    if ($archivo == '') {
        $salida['pmsg'] = 'No se recibio el nombre del archivo';
        echo json_encode($salida);
        exit;
    }

    // Si todavia no hay API key configurada, se avisa al frontend para que muestre
    // directamente el formulario en blanco y el usuario llene los datos a mano.
    if (!ia_comprobantes_disponible()) {
        $salida['pcode'] = 1;
        $salida['pmsg']  = 'Lectura automatica no disponible todavia. Complete los datos manualmente.';
        echo json_encode($salida);
        exit;
    }

    $ruta_archivo = __DIR__ . '/uploa_d_ventas/' . basename($archivo);

    if (!file_exists($ruta_archivo)) {
        $salida['pmsg'] = 'El archivo no se encontro en el servidor';
        echo json_encode($salida);
        exit;
    }

    $ia = extraer_datos_comprobante_ia($ruta_archivo);

    // Aunque la IA falle o lea datos incompletos, se responde pcode=1 (no es un error del
    // sistema) para que el usuario pueda completar/corregir los campos manualmente.
    $salida['pcode']      = 1;
    $salida['banco']      = $ia['banco'] ?? '';
    $salida['fecha']      = $ia['fecha'] ? ia_fecha_iso_a_formato_sesion($ia['fecha']) : '';
    $salida['referencia'] = $ia['referencia'] ?? '';
    $salida['monto']      = $ia['monto'] ?? '';
    $salida['ia_raw']     = $ia['raw'] ?? '';
    $salida['ia_error']   = $ia['error'] ?? '';

    // Solo se puede validar el duplicado cuando la IA logro leer los 4 datos completos.
    if ($ia['success'] && $ia['banco'] && $ia['fecha'] && $ia['referencia'] && $ia['monto']) {
        $dup = buscar_comprobante_pago_duplicado($ia['banco'], $ia['fecha'], $ia['referencia'], $ia['monto']);
        if ($dup) {
            $salida['duplicado'] = true;
            $salida['pmsg'] = 'Este comprobante ya fue registrado antes en la Venta #' . $dup['id_venta']
                . ' el ' . $dup['fecha_registro'] . '. Verifique antes de guardarlo de nuevo.';
        }
    }

    echo json_encode($salida);
    exit;
}


// ---- Guarda en ventas_comprobantes_pago los datos ya confirmados/corregidos por el usuario. ----
if ($accion == 'guardar_comprobante') {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"]  = "ERROR";

    $cid        = intval($_REQUEST['id_venta'] ?? 0);
    $archivo    = sanear_string($_REQUEST['archivo'] ?? '');
    $banco      = trim($_REQUEST['banco'] ?? '');
    $fecha_form = trim($_REQUEST['fecha'] ?? '');       // formato de la sesion (dd/mm/yyyy)
    $referencia = trim($_REQUEST['referencia'] ?? '');
    $monto      = trim($_REQUEST['monto'] ?? '');
    $origen     = ($_REQUEST['origen'] ?? '') === 'ia' ? 'ia' : 'manual';
    $ia_raw     = $_REQUEST['ia_raw'] ?? '';

    $verror  = "";
    $verror .= validar("Venta", $cid, "int", true);
    $verror .= validar("Archivo", $archivo, "text", true);
    $verror .= validar("Banco", $banco, "text", true);
    $verror .= validar("Fecha del comprobante", $fecha_form, "date", true);
    $verror .= validar("Referencia", $referencia, "text", true);
    $verror .= validar("Monto", $monto, "double", true);

    if ($verror != "") {
        $stud_arr[0]["pmsg"] = $verror;
        salida_json($stud_arr);
        exit;
    }

    // Tope de comprobantes por venta.
    if (count(listar_comprobantes_pago_venta($cid)) >= MAX_COMPROBANTES_POR_VENTA) {
        $stud_arr[0]["pmsg"] = "Ya se registraron " . MAX_COMPROBANTES_POR_VENTA . " comprobantes para esta venta (el maximo permitido).";
        salida_json($stud_arr);
        exit;
    }

    $fecha_mysql = formato_fecha_a_mysql($fecha_form);

    // Verificacion anti-manipulacion: el "readonly" del modal solo es una ayuda visual, se puede
    // saltar con las herramientas de desarrollador del navegador. Antes de guardar se vuelve a
    // leer el archivo real con la IA y se compara contra lo que se esta enviando; si algun dato
    // que la IA SI puede leer no coincide, se rechaza el guardado.
    $ruta_verificacion = __DIR__ . '/uploa_d_ventas/' . basename($archivo);
    $verificacion = verificar_comprobante_no_modificado($ruta_verificacion, $banco, $fecha_mysql, $referencia, $monto);
    if (!$verificacion['ok']) {
        $stud_arr[0]["pmsg"] = $verificacion['motivo'] . ' No se pueden modificar los datos que la IA extrajo del comprobante.';
        salida_json($stud_arr);
        exit;
    }

    // Validacion de duplicado en el servidor (no depender solo de lo que ya se aviso en pantalla,
    // por si el usuario cambio algun dato despues de la lectura de la IA).
    $dup = buscar_comprobante_pago_duplicado($banco, $fecha_mysql, $referencia, $monto);
    if ($dup) {
        $stud_arr[0]["pmsg"]       = "Este comprobante ya fue registrado antes (Venta #" . $dup['id_venta']
            . " el " . $dup['fecha_registro'] . "). No se puede subir el mismo comprobante 2 veces.";
        $stud_arr[0]["pduplicado"] = true;
        salida_json($stud_arr);
        exit;
    }

    $id_usuario = intval($_SESSION['usuario_id']);
    $nuevo_id   = guardar_comprobante_pago($cid, $archivo, $banco, $fecha_mysql, $referencia, $monto, $origen, $ia_raw, $id_usuario);

    if ($nuevo_id) {
        // Registro en el historial de la venta (misma tabla/estructura que registrar_historial_ventas()
        // en ventas_mant_contrato.php; se hace inline aqui para no depender de ese archivo).
        $id_estado_hist = intval(get_dato_sql("ventas", "id_estado", " where id=$cid"));
        sql_insert("INSERT INTO ventas_historial_estado (id_maestro, id_usuario, id_estado, nombre, fecha, observaciones)
                    VALUES (
                        $cid,
                        $id_usuario,
                        $id_estado_hist,
                        " . GetSQLValue('Registro de datos de comprobante de pago', 'text') . ",
                        NOW(),
                        " . GetSQLValue("Banco: $banco | Fecha: $fecha_form | Referencia: $referencia | Monto: $monto", 'text') . "
                    )");

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"]  = "Comprobante registrado correctamente";
    } else {
        $stud_arr[0]["pmsg"] = "No se pudo guardar el comprobante";
    }

    salida_json($stud_arr);
    exit;
}


// ---- Da de baja logica un comprobante ya registrado (libera un cupo de los 5). ----
if ($accion == 'borrar_comprobante') {
    $stud_arr[0]["pcode"] = 0;
    $stud_arr[0]["pmsg"]  = "ERROR";

    // Mismo permiso (168) que se usa para "Borrar" en la pestaña "Fotos de Comprobante de Pago".
    if (!tiene_permiso(168)) {
        $stud_arr[0]["pmsg"] = "No tiene privilegios para Borrar";
        salida_json($stud_arr);
        exit;
    }

    $id_comprobante = intval($_REQUEST['id_comprobante'] ?? 0);

    if ($id_comprobante > 0 && $cid > 0) {
        $ok = sql_update("UPDATE ventas_comprobantes_pago SET activo=0 WHERE id=$id_comprobante AND id_venta=$cid LIMIT 1");
        if ($ok) {
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"]  = "Comprobante eliminado";
        }
    }

    salida_json($stud_arr);
    exit;
}


// ---- Vista (fragmento HTML que se inserta dentro de la pestaña "Comprobantes de Pago") ----

if ($cid <= 0) {
    echo '<div class="alert alert-warning">Debe guardar la venta antes de administrar los comprobantes de pago.</div>';
    exit;
}

$total_comprobantes = count(listar_comprobantes_pago_venta($cid));
$cupos_disponibles  = max(0, MAX_COMPROBANTES_POR_VENTA - $total_comprobantes);
?>

<p class="text-muted">
    Suba aqui los comprobantes de pago de esta venta
</p>

<div class="row mb-3">
<div class="col-md" id="archivocomprobante">
<?php if ($cupos_disponibles > 0) { ?>
    <div class="row">
        <div class="col-sm-4" id="colbtn_comprobante">
            <span class="btn btn-secondary fileinput-button">
                <i class="fa fa-cloud-upload-alt"></i>
                <span>Subir Comprobante</span>
                <input id="fileupload_comprobante" type="file" name="files[]" multiple>
            </span>
        </div>
        <div class="col-sm-4">
            <div id="progress_comprobante" class="progress">
                <div class="progress-bar progress-bar-success"></div>
            </div>
            <div id="files_comprobante"></div>
        </div>
    </div>
<?php } else { ?>
    <div class="alert alert-secondary">Ya se registraron los <?php echo MAX_COMPROBANTES_POR_VENTA; ?> comprobantes permitidos para esta venta.</div>
<?php } ?>
</div>
</div>

<!-- Contador + tabla: se refrescan solos (accion "tabla") despues de guardar/borrar, sin
     recargar el widget de subida ni el modal, para no perder una cola de archivos en curso. -->
<div id="tabla_comprobantes_pago"><?php echo html_tabla_comprobantes($cid); ?></div>


<!-- ============================================================================
     Modal: confirmar datos del comprobante de pago (banco/fecha/referencia/monto)
     Los campos vienen precargados por la IA (cuando esta disponible) y el
     usuario los revisa, corrige si hace falta, y guarda. Si el servidor detecta
     que ya existe un comprobante igual, se muestra la alerta de duplicado y no
     deja continuar. Cuando se suben varios archivos de una vez, se procesan uno
     por uno (cola) para no abrir el modal encimado con datos de otro archivo.
     ============================================================================ -->
<div class="modal fade" id="ModalComprobanteIA" data-keyboard="false" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ModalComprobanteIA" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Datos del Comprobante de Pago</h5>
      </div>
      <form id="forma_comprobante_ia" onsubmit="return false;">
        <div class="modal-body">

          <p class="text-muted small" id="comprobante_archivo_label"></p>
          <p class="text-muted small"><i class="fa fa-lock"></i> Los campos que la IA logro leer del comprobante quedan bloqueados (no editables), para que no se puedan alterar los datos reales del documento. Solo quedan editables los campos que no se pudieron leer.</p>

          <div class="alert alert-danger d-none" id="comprobante_aviso_duplicado" role="alert"></div>

          <input type="hidden" id="comprobante_archivo"   name="archivo">
          <input type="hidden" id="comprobante_id_venta"  name="id_venta">
          <input type="hidden" id="comprobante_ia_raw"    name="ia_raw">
          <input type="hidden" id="comprobante_origen"    name="origen" value="manual">

          <div class="form-group">
            <label for="comprobante_banco_select">Banco / Financiera / Cooperativa</label>
            <select class="form-control" id="comprobante_banco_select" onchange="comprobante_banco_cambio()">
                <option value="">Seleccione...</option>
                <?php foreach (ia_comprobantes_lista_bancos_hn() as $b) { ?>
                    <option value="<?php echo htmlspecialchars($b, ENT_QUOTES); ?>"><?php echo htmlspecialchars($b); ?></option>
                <?php } ?>
                <option value="__otro__">Otro (especificar)...</option>
            </select>
            <!-- Solo aparece cuando el banco no esta en el catalogo de arriba. El valor que
                 realmente se guarda siempre es el de #comprobante_banco (hidden), nunca este
                 select/input directamente: asi el banco queda siempre escrito exactamente igual
                 (mismo texto letra por letra) cada vez que se elige de la lista, y la validacion
                 de duplicados no falla por diferencias de formato (ej. "BANPAIS" vs "Banco del Pais"). -->
            <input type="text" class="form-control mt-2 d-none" id="comprobante_banco_otro"
                   placeholder="Escriba el nombre del banco/financiera/cooperativa" maxlength="150"
                   oninput="comprobante_banco_cambio()">
            <input type="hidden" id="comprobante_banco" name="banco">
          </div>

          <div class="form-group">
            <label for="comprobante_fecha">Fecha del Comprobante</label>
            <input type="text" class="form-control" id="comprobante_fecha" name="fecha" required
                   placeholder="<?php echo ($_SESSION['formato_fecha'] ?? 'dd/mm/yyyy'); ?>">
          </div>

          <div class="form-group">
            <label for="comprobante_referencia">No. de Referencia / Transacción</label>
            <input type="text" class="form-control" id="comprobante_referencia" name="referencia" required maxlength="100">
          </div>

          <div class="form-group">
            <label for="comprobante_monto">Monto</label>
            <input type="number" step="0.01" min="0" class="form-control" id="comprobante_monto" name="monto" required>
          </div>

        </div>
        <div class="modal-footer">
          <a href="#" class="btn btn-light" onclick="$('#ModalComprobanteIA').modal('hide'); return false;">Cancelar</a>
          <a href="#" class="btn btn-primary" onclick="comprobante_guardar(); return false;">Guardar Comprobante</a>
        </div>
      </form>
    </div>
  </div>
</div>


<script>

// Refresca solo el contador + tabla de comprobantes ya registrados (accion "tabla"), sin tocar
// el widget de subida ni el modal. Se usa asi (en vez de recargar toda la pestaña) para no perder
// la cola de archivos (comprobante_cola) cuando se suben varios comprobantes de una sola vez.
// Nota: el widget de subida mantiene el limite de archivos que tenia al abrir la pestaña; el
// tope real de <?php echo MAX_COMPROBANTES_POR_VENTA; ?> siempre se valida de nuevo en el servidor al guardar.
function comprobantes_pago_refrescar(){
    var cid = $('#id').val();
    if (!cid || cid <= 0) { return; }
    $.get('ventas_comprobantes_pago.php?a=tabla&cid=' + cid, function (html) {
        $('#tabla_comprobantes_pago').html(html);
    });
}

// ----------------------------------------------------------------------------
// Cola de archivos pendientes de registrar: si el usuario selecciona/sube
// varios comprobantes a la vez, se abre el modal de a uno (al cerrar uno,
// sea guardando o cancelando, se abre el siguiente) para no mezclar datos.
// ----------------------------------------------------------------------------
var comprobante_cola = [];

function comprobante_encolar(archivo){
    comprobante_cola.push(archivo);
    if (!$('#ModalComprobanteIA').hasClass('show')) { comprobante_procesar_cola(); }
}

function comprobante_procesar_cola(){
    if (comprobante_cola.length === 0) { return; }
    comprobante_pedir_datos(comprobante_cola.shift());
}

$('#ModalComprobanteIA').on('hidden.bs.modal', function(){
    comprobante_procesar_cola();
});

// Sincroniza el select/input de banco hacia el hidden #comprobante_banco, que es el unico
// valor que realmente se envia al servidor. Si el select tiene "Otro", usa el texto escrito;
// si no, usa exactamente el texto de la opcion elegida (catalogo fijo -> mismo texto siempre).
function comprobante_banco_cambio(){
    var sel = $('#comprobante_banco_select').val();
    if (sel === '__otro__') {
        $('#comprobante_banco_otro').removeClass('d-none');
        $('#comprobante_banco').val($('#comprobante_banco_otro').val().trim());
    } else {
        $('#comprobante_banco_otro').addClass('d-none').val('');
        $('#comprobante_banco').val(sel);
    }
}

// Ubica un banco leido por la IA (o cargado de un comprobante ya guardado) dentro del select del
// catalogo. Si coincide (sin importar mayus/minus) con una opcion, la selecciona; si no coincide
// con ninguna, deja el select en "Otro" y precarga el texto leido para que el usuario lo revise.
function comprobante_set_banco(valor){
    valor = (valor || '').trim();
    var $select = $('#comprobante_banco_select');
    var coincide = null;

    $select.find('option').each(function(){
        var v = $(this).val();
        if (v !== '' && v !== '__otro__' && valor !== '' && v.toLowerCase() === valor.toLowerCase()) {
            coincide = v;
        }
    });

    if (valor === '') {
        $select.val('');
        $('#comprobante_banco_otro').addClass('d-none').val('');
        $('#comprobante_banco').val('');
    } else if (coincide !== null) {
        $select.val(coincide);
        $('#comprobante_banco_otro').addClass('d-none').val('');
        $('#comprobante_banco').val(coincide);
    } else {
        $select.val('__otro__');
        $('#comprobante_banco_otro').removeClass('d-none').val(valor);
        $('#comprobante_banco').val(valor);
    }
}

// Deja todos los campos editables (se llama al abrir el modal para un archivo nuevo,
// antes de saber que pudo leer la IA de ese archivo en particular).
function comprobante_desbloquear_todo(){
    $('#comprobante_banco_select').prop('disabled', false);
    $('#comprobante_banco_otro').prop('readonly', false);
    $('#comprobante_fecha, #comprobante_referencia, #comprobante_monto').prop('readonly', false);
}

// Bloquea (solo lectura) cada campo que la IA SI pudo leer del comprobante, para que el
// usuario no pueda alterar lo que realmente dice el documento (evita, por ejemplo, cambiar
// el monto o la referencia despues de que la IA ya los leyo). Los campos que la IA no pudo
// leer (null) quedan editables, para que el usuario los complete a mano.
function comprobante_bloquear_extraidos(json){
    var bancoLeido      = !!(json.banco && String(json.banco).trim() !== '');
    var fechaLeida      = !!(json.fecha && String(json.fecha).trim() !== '');
    var referenciaLeida = !!(json.referencia && String(json.referencia).trim() !== '');
    var montoLeido      = (json.monto !== '' && json.monto !== null && json.monto !== undefined);

    $('#comprobante_banco_select').prop('disabled', bancoLeido);
    $('#comprobante_banco_otro').prop('readonly', bancoLeido);
    $('#comprobante_fecha').prop('readonly', fechaLeida);
    $('#comprobante_referencia').prop('readonly', referenciaLeida);
    $('#comprobante_monto').prop('readonly', montoLeido);
}

// Le pide al servidor que lea el comprobante con IA (banco/fecha/referencia/monto) y abre el modal
// de confirmacion con esos datos ya precargados (o vacios si la IA no esta disponible/no pudo
// leerlo, para que el usuario los escriba a mano).
function comprobante_pedir_datos(archivo){

    var cid = $('#id').val();

    $('#comprobante_archivo').val(archivo);
    $('#comprobante_id_venta').val(cid);
    $('#comprobante_archivo_label').text('Archivo: ' + archivo);
    comprobante_desbloquear_todo();
    comprobante_set_banco('');
    $('#comprobante_fecha, #comprobante_referencia, #comprobante_monto').val('');
    $('#comprobante_ia_raw').val('');
    $('#comprobante_origen').val('manual');
    $('#comprobante_aviso_duplicado').addClass('d-none').text('');

    cargando(true);
    $.ajax({
        url: 'ventas_comprobantes_pago.php?a=extraer_comprobante',
        type: 'POST',
        dataType: 'json',
        data: { archivo: archivo },
        success: function (json) {
            cargando(false);

            if (!json || json.pcode != 1) {
                mytoast('error', (json && json.pmsg) ? json.pmsg : 'No se pudo leer el comprobante', 4000);
            } else {
                if (!json.ia_disponible) {
                    mytoast('warning', 'Lectura automatica no configurada. Complete los datos del comprobante manualmente.', 5000);
                } else if (json.ia_error) {
                    mytoast('warning', 'La IA no pudo leer todo el comprobante. Revise/complete los datos manualmente.', 5000);
                }

                comprobante_set_banco(json.banco || '');
                $('#comprobante_fecha').val(json.fecha || '');
                $('#comprobante_referencia').val(json.referencia || '');
                $('#comprobante_monto').val(json.monto || '');
                $('#comprobante_ia_raw').val(json.ia_raw || '');
                $('#comprobante_origen').val(json.ia_disponible && !json.ia_error ? 'ia' : 'manual');
                comprobante_bloquear_extraidos(json);

                if (json.duplicado) {
                    $('#comprobante_aviso_duplicado').removeClass('d-none').text(json.pmsg);
                }
            }

            $('#ModalComprobanteIA').modal('show');
        },
        error: function () {
            cargando(false);
            mytoast('error', 'Error de comunicación al leer el comprobante', 3000);
            // Aun con error de comunicacion se deja abierto el modal para que el usuario
            // pueda llenar los datos manualmente y no se pierda el comprobante ya subido.
            $('#ModalComprobanteIA').modal('show');
        }
    });
}

// Guarda en ventas_comprobantes_pago los datos confirmados/corregidos por el usuario.
// El servidor vuelve a validar el duplicado (y el tope de 5) antes de guardar.
function comprobante_guardar(){

    comprobante_banco_cambio(); // asegura que el hidden #comprobante_banco quede actualizado

    if ($('#comprobante_banco').val().trim() === '') {
        mytoast('error', 'Seleccione el banco/financiera/cooperativa (o "Otro" y escriba el nombre)', 4000);
        return;
    }

    if (!$('#forma_comprobante_ia')[0].reportValidity()) { return; }

    cargando(true);
    $.ajax({
        url: 'ventas_comprobantes_pago.php?a=guardar_comprobante',
        type: 'POST',
        dataType: 'json',
        data: $('#forma_comprobante_ia').serialize(),
        success: function (json_arr) {
            cargando(false);
            var json = json_arr && json_arr[0] ? json_arr[0] : null;

            if (json && json.pcode == 1) {
                mytoast('success', json.pmsg, 3000);
                $('#ModalComprobanteIA').modal('hide');
                comprobantes_pago_refrescar();
            } else if (json && json.pduplicado) {
                $('#comprobante_aviso_duplicado').removeClass('d-none').text(json.pmsg);
            } else {
                mytoast('error', json ? json.pmsg : 'Error al guardar el comprobante', 4000);
            }
        },
        error: function () {
            cargando(false);
            mytoast('error', 'Error de comunicación al guardar el comprobante', 3000);
        }
    });
}

// Borra (baja logica) un comprobante ya registrado, liberando un cupo de los 5.
function comprobante_borrar(idComprobante){
    Swal.fire({
        title: 'Borrar Comprobante',
        text: 'Desea borrar este comprobante de pago?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.value) {
            var cid = $('#id').val();
            cargando(true);
            $.post('ventas_comprobantes_pago.php', { a: 'borrar_comprobante', cid: cid, id_comprobante: idComprobante }, function(json){
                cargando(false);
                if (json && json[0] && json[0].pcode == 1) {
                    mytoast('success', json[0].pmsg, 3000);
                    comprobantes_pago_refrescar();
                } else {
                    mytoast('error', (json && json[0]) ? json[0].pmsg : 'Error', 3000);
                }
            }, 'json').fail(function(){
                cargando(false);
                mytoast('error', 'Error de comunicación al borrar el comprobante', 3000);
            });
        }
    });
}

// Widget de subida (hasta los cupos disponibles calculados en el servidor). Mismo backend
// generico de siempre (plugins/fileupload/, carpeta uploa_d_ventas), pero solo para
// imagen/PDF (lo unico que la IA puede leer) y con tope de archivos por seleccion.
$(function () {
    if ($('#fileupload_comprobante').length === 0) { return; }

    $('#fileupload_comprobante').fileupload({
        url: 'plugins/fileupload/',
        dataType: 'json',
        formData: { folder: 'uploa_d_ventas' },
        singleFileUploads: true,
        sequentialUploads: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf)$/i,
        maxFileSize: 20971520,
        maxNumberOfFiles: <?php echo (int) $cupos_disponibles; ?>,
        disableVideoPreview: true,
        disableAudioPreview: true,
        disableImagePreview: true,
        previewThumbnail: false,
        add: function (e, data) {
            if (typeof Promise === 'undefined' || typeof window.comprimirSiEsImagen !== 'function') {
                data.submit();
                return;
            }
            Promise.all($.map(data.files, function (f) { return window.comprimirSiEsImagen(f); }))
                .then(function (filesComprimidos) {
                    data.files = filesComprimidos;
                    data.submit();
                })
                .catch(function () {
                    data.submit();
                });
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                if (file.error) {
                    mytoast('error', file.error, 4000);
                    return;
                }
                comprobante_encolar(file.name);
            });
        },
        progressall: function (e, data) {
            $('#colbtn_comprobante').hide();
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress_comprobante .progress-bar').css('width', progress + '%');
        }
    });
});

</script>
