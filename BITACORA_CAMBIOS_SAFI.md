# Bitacora de Cambios SAFI

## Objetivo
Registro cronologico de cambios funcionales y tecnicos aplicados al sistema.

## Formato sugerido para nuevas entradas
- Fecha: YYYY-MM-DD
- Modulo/Archivo
- Cambio realizado
- Motivo
- Validacion

---

## 2026-03-11

### Modulo Inspeccion
- Archivo: www/inspeccion_mant.php
- Cambios realizados (segun historial Git del archivo):
  - Se mejoro el manejo de errores y la actualizacion de interfaz en el procesamiento de inspecciones.
  - Se actualizaron validaciones de marcas de llantas para permitir solo caracteres alfabeticos.
  - Se ajusto la validacion del formato de numeracion de llantas en la inspeccion.
  - Se reforzaron validaciones de marcas y numeracion de llantas en frontend/backend.
- Referencias de commits del dia:
  - 1da2bbb - mejorar manejo de errores y actualizacion de interfaz en el procesamiento de inspecciones.
  - c8468b5 - actualizar validacion de marcas y numeracion de llantas para permitir solo caracteres alfabeticos.
  - ce6f12a - actualizar formato de validacion de numeracion de llantas en la inspeccion.
  - 801b720 - actualizar formato de validacion de numeracion de llantas en la inspeccion.
  - 8ec0b79 - validar marcas y numeracion de llantas en la inspeccion.
- Validacion:
  - Registro agregado desde historial Git del archivo para trazabilidad historica.

---

## 2026-03-12

### Modulo Inspeccion
- Archivo: www/inspeccion_mant.php
- Cambios realizados:
  - Se agrego validacion de marcas de llantas con minimo 5 caracteres en backend y frontend.
  - Se agrego validacion de numeracion de llantas con formato 000/00R00 en backend y frontend.
  - Se creo la seccion Datos de Acta de Recepcion del Vehiculo despues de Servicio de Grua.
  - Se agregaron los campos:
    - actarv_asignado_depto
    - actarv_jefe_depto
    - actarv_celular
    - actarv_foto_licencia
  - Se conectaron esos campos a lectura y guardado en base de datos.
  - Se agrego validacion de celular para Acta de Recepcion (solo numeros y simbolos + - ( ) con longitud 7-35).
  - Se definio obligatoriedad de la seccion al completar cuando:
    - Tipo = Renta y Movimiento = Salida, o
    - El cliente tiene prefijo CCO.
  - Se agrego logica para mostrar/ocultar dinamicamente la seccion segun el cliente seleccionado.
- Motivo:
  - Cumplir nuevos requisitos operativos de recepcion/entrega y mejorar calidad de datos capturados.
- Validacion:
  - Verificacion de errores del archivo sin incidencias.
  - Ajuste adicional: se agrego callback local de upload `insp_guardar_foto(...)` en inspeccion_mant para asegurar que el campo `actarv_foto_licencia` quede seteado al subir archivo.
  - Ajuste adicional: se agrego boton `Ver Foto Licencia` y funcion `mostrar_foto(...)` para abrir imagen en modal (Swal) o documento en nueva pestana.
  - Ajuste adicional: se removio la obligatoriedad de `actarv_asignado_depto` y `actarv_jefe_depto`; ahora pueden quedar en blanco.
  - Se mantiene obligatoria la validacion de `actarv_celular` y `actarv_foto_licencia` cuando aplica la regla de Acta de Recepcion.
  - Ajuste adicional: se elimino el boton `Ver Foto Licencia` de la seccion Acta de Recepcion, manteniendo la carga y validacion del archivo.
  - Ajuste adicional: se bloqueo la carga/cambio de `actarv_foto_licencia` cuando la Hoja de Inspeccion esta completada (`id_estado >= 2`) en frontend y backend.
  - Ajuste adicional: se actualizo la regla de prefijo de cliente para Acta de Recepcion; ahora aplica para prefijos `CCO` y `CCN`.
  - Ajuste adicional: se corrigio la regla final para que la seccion y validaciones de Acta apliquen solo para prefijo `CCO`.
  - Para clientes sin prefijo `CCO`, se bloquea el ingreso de datos del Acta al completar.
  - Refuerzo adicional: en `Guardar Completado`, la validacion de Acta usa fallback por `nombre_cliente` cuando `cliente_id` no viene resuelto en el POST, evitando que se omita la validacion de Foto Licencia para prefijo `CCO`.
  - Refuerzo adicional: se habilito validacion obligatoria de cliente al `Guardar Completado` (frontend y backend).
  - Refuerzo adicional: se blindo la validacion de `actarv_foto_licencia` en el punto de `gg_est=2` (completar), para cliente con prefijo `CCO`, con bloqueo inmediato en backend si falta la foto.

### Modulo Vehiculos/Ventas
- Archivo: www/dashboard_vendedor_negociacion.php
- Cambio realizado:
  - Se creo dashboard por vendedor para estado 11 (Negociacion).
- Archivo: www/ventas_vehiculos_ver.php
- Cambio realizado:
  - Se agrego boton de acceso al dashboard de negociacion.
- Archivo: www/vehiculos_reparacion_ver.php
- Cambio realizado:
  - Se revirtio el boton que habia sido colocado por error en esta pantalla.
- Motivo:
  - Ubicar el dashboard en el modulo correcto de ventas de vehiculos.

---

## Notas
- Esta bitacora es manual y acumulativa.
- No incluir logs de runtime ni cambios automaticos de archivos de logs, salvo que afecten funcionalidad.
