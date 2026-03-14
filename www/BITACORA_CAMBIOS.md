# Bitacora de cambios

## 2026-03-14
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
