# Contexto de Mejoras - SAFI

Fecha: 2026-02-11
Repositorio: `c:\DEV-git\SAFI`

## 1) Panorama actual del sistema
- Monolito PHP procedural (sin framework moderno), con mezcla de backend + HTML + JS en los mismos archivos.
- Punto común de sesión, permisos, utilidades y DB en `www/include/framework.php`.
- Login y recuperación en `www/index.php`.
- Shell principal de UI en `www/main.php`.
- Módulos principales por dominio: `servicio_*`, `inspeccion_*`, `averia_*`, `ventas_*`, `cotizacion_*`, `traslado_*`.

## 2) Dependencias y librerías
- `composer.json` (raíz): `phpoffice/phpword`.
- `www/composer.json`: `aws/aws-sdk-php`.
- Existen librerías legacy incluidas manualmente (`tcpdf`, `phpcorreo`) en `www/include/`.
- Hay doble carpeta de dependencias: `vendor/` y `www/vendor/`.

## 3) Archivos y zonas críticas para cambios inmediatos
- `www/include/framework.php`
  - Maneja sesión/cookies/permisos/DB/helpers globales.
  - Helpers SQL: `sql_select`, `sql_insert`, `sql_update`, `sql_delete`, `GetSQLValue`.
- `www/index.php`
  - Auth, recuperación, logs de login.
- `www/servicio_fotos.php`
- `www/inspeccion_fotos.php`
- `www/inspeccion_fotos_copia.php` (duplicado/legacy).
- `www/ventas_mant_contrato.php`
  - Generación DOCX/PDF y uso de LibreOffice (`soffice`).

## 4) Riesgos técnicos detectados (prioridad alta)
- Configuración sensible dentro del repo:
  - `www/include/config.php` contiene host, DB user/password, email pass, rutas de logs.
- Cookies con `secure=false` en auth/sesión.
- SQL armado por string en muchos puntos (sin prepared statements reales).
- Código ofuscado/externo en `framework.php` con `goto` + `file_get_contents(...)` remoto (riesgo de seguridad/mantenibilidad).
- Artefactos operativos versionados en git: logs y archivos de trabajo.
- Ausencia de pruebas automatizadas propias de aplicación.

## 5) Hallazgos específicos en fotos (servicio/inspección)
- Patrón `a=g` / `a=d` para guardar/borrar foto dentro del mismo endpoint.
- Persistencia dual de imágenes: local (`uploa_d`) y fallback S3 (`aws_bucket_s3`).
- Hay TODOs de borrado físico de archivo tras borrado DB (deuda técnica visible).
- Existe archivo duplicado `inspeccion_fotos_copia.php` que aumenta riesgo de divergencia funcional.

## 6) Convenciones implícitas del proyecto
- Flujo por parámetros `$_REQUEST` con acción `a`.
- Seguridad por sesión + permisos numéricos (`tiene_permiso(id)`).
- Uso masivo de funciones helper globales desde `framework.php`.
- Bajo acoplamiento por clases, alto acoplamiento por includes/globales.

## 7) Estrategia de mejora recomendada (sin romper operación)

### Fase 1: Endurecimiento mínimo seguro
1. Sacar secretos de `config.php` a variables de entorno o archivo local no versionado.
2. Ajustar cookies (`secure`, `httponly`, `samesite`) según entorno HTTPS.
3. Limpiar `.gitignore` para excluir logs/artefactos runtime y evitar nuevos commits de datos operativos.
4. Documentar y aislar archivo legacy/duplicado que no deba seguir activo.

### Fase 2: Estabilización de módulos críticos
1. Unificar lógica de fotos en una sola implementación (servicio/inspección).
2. Implementar borrado consistente DB + filesystem/S3 con trazabilidad.
3. Encapsular acceso DB para rutas críticas con consultas preparadas.
4. Estandarizar respuestas JSON de endpoints de acción.

### Fase 3: Base para evolución
1. Crear pruebas smoke/integración para flujos críticos (login, fotos, contrato PDF).
2. Agregar logging estructurado por módulo y correlación por request.
3. Preparar capa de servicios gradual para reducir dependencia de globals.

## 8) Backlog inicial sugerido (orden de ejecución)
1. Auditoría y limpieza de configuración sensible + `.gitignore`.
2. Consolidar `inspeccion_fotos.php` y desactivar/archivar `inspeccion_fotos_copia.php`.
3. Corregir borrado integral de archivos adjuntos.
4. Revisar `ventas_mant_contrato.php` para portabilidad Windows/Linux en conversión PDF.
5. Definir plantilla de PR/checklist para cambios en módulos legacy.

## 9) Checklist operativo para nuevas tareas
- Identificar acción (`a=...`) y permisos involucrados.
- Trazar tablas afectadas (insert/update/delete/select).
- Validar impacto en archivos (local/S3) y logs.
- Probar flujo feliz + errores de usuario + errores de infraestructura.
- Verificar que no se rompan endpoints que renderizan HTML y JSON en el mismo archivo.

## 10) Nota de trabajo
Este documento se generó como contexto base para iniciar mejoras incrementales sin reescritura completa, priorizando seguridad, estabilidad operativa y reducción de deuda técnica.

## 11) Registro de cambios recientes (2026-02-16)

### 11.1 Inspección - flujo de fotos y borrador temprano
- Se creó archivo de prueba `www/inspeccion_mant_init.php` para validar estrategia de `init` (creación temprana de borrador cuando no existe `id`).
- En esa variante, al abrir pestaña de fotos sin `id`, se ejecuta `a=init`, se crea borrador y luego carga `inspeccion_fotos.php` con `cid` válido.

### 11.2 Inspección - validación de kilometraje en backend
- Se agregó validación en `www/inspeccion_mant.php` para bloquear guardado si `kilometraje_entrada` es menor al valor de referencia.
- La referencia usa el mayor entre:
  - `kilometraje_minimo` recibido.
  - `producto.km` actual en base de datos.
- Mensaje de bloqueo: `El Kilometraje no puede ser menor al kilometraje anterior`.

### 11.3 Servicio - modal de edición por estado de la OS
- Se ajustó `servicio_editarcampo(...)` en `www/servicio_mant.php` para que la apertura del modal dependa del estado actual de la OS:
  - Si `estado < 22`: abre modal.
  - Si `estado === 22`: abre solo con permisos post-cierre (`164` o `163`).
  - En otros estados: no abre.
- Se eliminó una validación backend intermedia agregada temporalmente en `a=ec2` (cerca de la línea 614), por solicitud del usuario.

### 11.4 Seguridad de sesión y CSRF (compatibles con flujo actual)
- `www/include/framework.php`:
  - Se centralizó configuración de cookies con `app_session_cookie_options(...)`.
  - Detección de HTTPS con `app_is_https()` para `secure` dinámico.
  - Se cambió `SameSite` a `Lax` para compatibilidad de navegación.
  - Se agregó timeout por inactividad de 30 minutos.
  - Se agregaron helpers `csrf_token()` y `csrf_validate()`.
- `www/index.php`:
  - Se agregaron helpers equivalentes (`index_session_cookie_options`, `index_csrf_token`, `index_csrf_validate`).
  - Se inicializa sesión y token CSRF al cargar login.
  - Login (`a=201`) y recuperación (`a=301`) ahora validan CSRF.
  - En login exitoso se ejecuta `session_regenerate_id(true)`.
  - Logout limpia sesión/cookies con opciones consistentes.
  - Se expone token a JS: `window.APP_CSRF_TOKEN`.
- `www/js/index.js`:
  - Login y recover envían `csrf_token` en los POST AJAX.

### 11.5 Verificaciones realizadas
- Sintaxis validada con `php -l` en:
  - `www/include/framework.php`
  - `www/index.php`
  - `www/servicio_mant.php`
  - `www/inspeccion_mant.php`
  - `www/inspeccion_mant_init.php`
