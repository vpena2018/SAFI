# Bitacora de cambios

## 2026-03-19
- En `include/framework.php` se corrigio `get_dato_sql()`: agregado chequeo `$result != false` antes de acceder a `->num_rows`, evitando el Warning "Attempt to read property num_rows on bool" cuando la query MySQL falla.

## 2026-03-15
- En `inspeccion_mant.php` se agrego `window.comprimirSiEsImagen` (mismo enfoque de `combustible.php`) para reducir resolucion/peso antes de subir imagenes, aplicando a `actarv_foto_licencia` durante la carga.
- En `inspeccion_mant.php` se corrigio el error al cambiar cliente a no CCO (ej. CCN): al ocultar Acta de Recepcion, el campo `actarv_celular` ahora se deshabilita y se le retira temporalmente `pattern/title/required` para que no bloquee `Guardar Borrador` por validacion HTML.
- En `inspeccion_mant.php` se ajusto la validacion de Acta de Recepcion para `gg_est=2` cuando no aplica (cliente no CCO o tipo distinto): ya no bloquea con error y ahora limpia esos campos para mantener consistencia de datos.
- Se revierte en `inspeccion_mant.php` el ajuste que movia la validacion del Acta de Recepcion para ejecutarse solo al completar; la pantalla vuelve al comportamiento previo.
- Se aplico en `inspeccion_mant.php` la misma reduccion de resolucion usada en `inspeccion_fotos.php` para la carga de `actarv_foto_licencia`.
- La foto de licencia ahora se procesa con `foto_reducir_tamano(app_dir . "uploa_d/" . archivo)` al guardar, evitando reprocesar cuando el archivo no cambia.

## 2026-03-14
- Se agrego campo `id_adpc_categoria` (combobox select2 filtrado por `tipo_documento=1` de la tabla `categoria`) antes de `observaciones_adpc` en `inspeccion_mant.php`, sección ADPC.
- El campo se muestra y es requerido cuando el usuario tiene permiso 163 y `id_usuario_auditado` es nulo (primera revisión ADPC).
- Cuando el usuario tiene permiso 163 y ya existe `id_usuario_auditado`, el campo se muestra pero sin obligatoriedad.
- Se guarda en el UPDATE general y también en el bloque de guardado "Revision ADPC".
- Se agrego campo `id_adpc_categoria` al modal de "Revision ADPC" en `servicio_mant.php`, usando `select2` con filtro `tipo_documento=2` de la tabla `adpc_categorias`.
- En `servicio_mant.php` la primera revision ADPC ahora exige `id_adpc_categoria` y `observaciones_adpc` tanto en validacion JavaScript como en validacion del servidor.
- Se retiro el boton "Imprimir Acta RV" de la pantalla de inspeccion (`inspeccion_mant.php`).
- Se elimino la funcion JavaScript `insp_imprimir_actarv()` para evitar impresion separada.
- Se actualizo `inspeccion_pdf.php` para agregar al final del PDF una pagina de "Acta de Recepcion de Vehiculo" generada con TCPDF.
- La pagina final del acta usa los campos `actarv_asignado_depto`, `actarv_jefe_depto`, `actarv_celular` y foto de licencia (`actarv_foto_licencia`) cuando existe archivo.
- Se retiro del acta final el texto de declaracion de entrega/recepcion y la seccion de lineas de firma.
- Se ajusto el encabezado del acta para coincidir con el formato solicitado: logo a la izquierda, razon social al centro, bloque `RE-VE-17 / Ver. 02 / Fecha: 21/07/2025` a la derecha y fila de titulo del acta.
- Se agrego un parrafo introductorio debajo del titulo principal del acta y se elimino la fila de "Cliente" (nombre del cliente) en la tabla de datos.
- Se aumento el espacio despues del parrafo introductorio del acta y se cambio la seccion de caracteristicas del vehiculo a formato de tabla con columnas: `MARCA`, `TIPO`, `COLOR`, `PLACA` y `REGISTRO #`.
- Se amplio el tamano de la foto de licencia en el acta para mejorar visibilidad y se ajusto el espaciado vertical posterior.
- Se incremento nuevamente el tamano de la foto de licencia en el acta para que se vea aun mas grande.
- Se agrego la seccion "Firma del Cliente" despues de la foto de licencia en la pagina final del acta, usando la firma capturada en `pdffirma1`.
- Se ajusto la firma del cliente para que aparezca a la par del texto "Firma del Cliente:" con raya inferior de firma.
- Se aumento el tamano de la firma del cliente y se dejo la raya inferior centrada a la mitad del ancho.
- Se ajusto la raya de firma para que quede directamente debajo de la firma del cliente.
- Se acorto la raya de firma del cliente y se centro debajo de la firma para estilo manuscrito.
- Se adapto el estilo de firma al formato de referencia: texto bilingue, firma pequena y raya corta alineada.
- Se incremento el tamano de la imagen de firma del cliente en el acta para mejorar visibilidad.
- Se aumento nuevamente el tamano de la firma del cliente en el acta, manteniendo su alineacion con la raya de firma.
- Se movio la firma del cliente mas abajo de la foto y se acerco al texto "Firma Cliente".
- Se ajusto nuevamente la posicion para pegar mas la imagen de firma y la raya al texto "Firma Cliente".
- Se aplico un microajuste final para dejar la firma y la raya aun mas pegadas al texto.
- Se dividio el texto introductorio del acta en dos parrafos para mejorar lectura del formato.
- Se elimino el espacio adicional entre ambos parrafos del texto introductorio del acta.
