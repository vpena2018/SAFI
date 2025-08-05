<?php
require_once ('include/framework.php');


function crudcombolookup($columnas){
        global $columnas_combo,$columnas_combo2,$columnas_combo3,$tabla;
        $salida= array();
    
    if (isset($columnas_combo)){
         $i=0;
         foreach ($columnas as $campo) {

            $key = array_search($campo, $columnas_combo);
            if ($key===false) {
                $salida[$i]=$campo;
            } else {
                $campoid="id";
                
                $latabla1=$columnas_combo2[$key] ;
                $latabla2=$columnas_combo2[$key] ;
                if ($tabla==$columnas_combo2[$key]) {
                   $latabla1=$columnas_combo2[$key]. " tabla" ;
                   $latabla2=" tabla" ;    
                }
                   $salida[$i]="(select ".$columnas_combo3[$key]." from ".$latabla1." where ". $latabla2 .".$campoid=$tabla.$campo) as $campo";
                
                }       
 
             $i++;
        }
    } else {$salida=$columnas;}
    
    return $salida;
}

$tabla="";
    
switch ($numtabla) {
    case 1:
    pagina_permiso(13);
    $tabla="usuario"; 
    $tabla_etiqueta="Usuario";
    $columnas = array('id','usuario','nombre','email','telefono','clave','grupo_id','tienda_id','activo','acceso_ultimo','acceso_intentos','perfil_adicional','perfil_motorista','perfil_tecnico');
    $columnas_etiquetas = array('ID','Usuario','Nombre','Email','Telefono','Clave','Grupo','Tienda','Activo','Acceso Ultimo','Acceso Intentos','Perfil Adicional','Perfil Motorista = 3','Perfil Tecnico = 2' );
    $columnas_tipo = array('int','text','text','text','text','text','int','int','int','text','int','int','int','int' );
    $columnas_mask = array('','','','','','','','','','','','','','');
     
    $columnas_combo  = array('grupo_id','tienda_id');
    $columnas_combo2 = array('usuario_grupo','tienda');
    $columnas_combo3 = array('nombre','nombre');

    break;
        
    case 2:
    pagina_permiso(14);    
    $tabla="usuario_grupo"; 
    $tabla_etiqueta="Perfil de Usuario";
    $columnas = array('id','nombre');
    $columnas_etiquetas = array('Id','Nombre' );
    $columnas_tipo = array('int','text' );
     $columnas_mask = array( '','' );
    break;


    case 3: 
        pagina_permiso(46);
        $tabla="tienda"; 
        $tabla_etiqueta="Tiendas";
        $columnas = array('id','nombre','correo_bodega','correo_compras','correo_orden_servicio_nueva','correo_orden_averia_nueva','correo_cita','sap_almacen','rentworks_almacen','correo_contabilidad');
        $columnas_etiquetas = array('Id','Nombre','Correo de Bodega','Correo de Compras','Correo de Orden Servicio Nueva','Correo de Orden Averia Nueva','Correo Cita','Codigo Almacen SAP','Codigo Almacen Rentworks','Correo Contabilidad' );
        $columnas_tipo = array('int','text' ,'text','text','text','text','text','text','text','text');
        $columnas_mask = array('','','','','' ,'','','','','');
        break;

    case 4: 
        pagina_permiso(47);
        $tabla="averia_tipo"; 
        $tabla_etiqueta="Tipos de Averia";
        $columnas = array('id','nombre');
        $columnas_etiquetas = array('Id','Nombre' );
        $columnas_tipo = array('int','text' );
        $columnas_mask = array('','' );
        break;
 
    case 5: 
        pagina_permiso(48);
        $tabla="servicio_tipo_mant"; 
        $tabla_etiqueta="Tipos de Orden Servicio ";
        $columnas = array('id','nombre');
        $columnas_etiquetas = array('Id','Nombre' );
        $columnas_tipo = array('int','text' );
        $columnas_mask = array('','' );
        break;

    case 6: 
        pagina_permiso(49);
        $tabla="servicio_tipo_revision"; 
        $tabla_etiqueta="Tipos de Orden Servicio Revisión";
        $columnas = array('id','nombre');
        $columnas_etiquetas = array('Id','Nombre' );
        $columnas_tipo = array('int','text' );
        $columnas_mask = array('','' );
        break;

    case 7: 
        pagina_permiso(50);
        $tabla="cotizacion_tipo"; 
        $tabla_etiqueta="Tipos de Cotización";
        $columnas = array('id','nombre');
        $columnas_etiquetas = array('Id','Nombre' );
        $columnas_tipo = array('int','text' );
        $columnas_mask = array('','' );
        break;
 

    case 8: 
        pagina_permiso(52);
        $tabla="mapeo"; 
        $tabla_etiqueta="Zonas de Mapeo";
        $columnas = array('id','id_tienda','zona','ubicacion');
        $columnas_etiquetas = array('Id','Tienda','Zona','Ubicacion' );
        $columnas_tipo = array('int','int','text','text' );
        $columnas_mask = array('','','','' );

        $columnas_combo  = array('id_tienda');
        $columnas_combo2 = array('tienda');
        $columnas_combo3 = array('nombre');
        break;   


    case 9: 
        pagina_permiso(97);
        $tabla="configuracion"; 
        $tabla_etiqueta="Configuracion";
        $columnas = array('id','isv','porcentaje_ganancia','porcentaje_gastos_admon','cobro_precio_atm_x_hora');
        $columnas_etiquetas = array('Id','Porcentaje ISV %','Porcentaje Ganancia %','Porcentaje Gastos Administrativos %','Precio Servicio ATM x Hora' );
        $columnas_tipo = array('int','double','double','double' ,'double');
        $columnas_mask = array('','','','','' );
        break;  
        
        
    case 10: 
        pagina_permiso(99);
        $tabla="producto"; 
        $tabla_etiqueta="Productos";
        $columnas = array('id','codigo_alterno','nombre','codigo_grupo','habilitado','congelado','item_compra','item_venta','item_inventario','codigo_hertz','tipo','tipo_sap','marca','anio','modelo','color','cilindrada','serie','motor','placa','tipo_vehiculo','chasis','precio_costo','precio_venta','km','k5','k10','k20','k40','k100','sincronizado','horas','tipo_mant');
        $columnas_etiquetas = array('Id','Codigo SAP','Nombre','Grupo','Habilitado','Congelado','Item Compra','Item Venta','Item Inventario','Codigo Hertz','Tipo','Tipo Sap','Marca','Año','Modelo','Color','Cilindrada','Serie','Motor','Placa','Tipo Vehiculo','Chasis','Precio Costo','Precio Venta','Km','K5','K10','K20','K40','K100','Sincronizado','Horas','Tipo Mant' );
        $columnas_tipo = array('int','text','text','text','int','int','int','int','int','text','int','int','text','text','text','text','text','text','text','text','text','text','double','double','int','int','int','int','int','int','text','double','text' );
        $columnas_mask = array('','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','' );
        break;


        case 11: 
            pagina_permiso(134);
            $tabla="cita_taller"; 
            $tabla_etiqueta="Citas Talleres Disponibles";
            $columnas = array('id','id_tienda','id_taller','taller_nombre','interno','taller_nombre_abrevia','enlace','activo','externo' ); 
            $columnas_etiquetas = array('Id','Tienda','Taller','Taller Nombre Publico','Uso Interno','Nombre Abreviado','Enlace Ubicacion','Estado','Taller Externo' );
            $columnas_tipo = array('int','int','int','text','int' ,'text','text','int','int');
            $columnas_mask = array('','','','','','','','','');
    
            $columnas_combo  = array('id_tienda','id_taller');
            $columnas_combo2 = array('tienda','entidad');
            $columnas_combo3 = array('nombre','nombre');
            break; 

        case 12: 
            $tabla="cita_horario"; 
            $tabla_etiqueta="Cita - Horarios";
            $columnas = array('id','nombre','hora');
            $columnas_etiquetas = array('Id','Nombre','Hora 24Horas' );
            $columnas_tipo = array('int','text','double' );
            $columnas_mask = array('','','' );
            break; 


            case 13: 
                $tabla="tienda_agencia"; 
                $tabla_etiqueta="Tienda - Agencia";
                $columnas = array('id','tienda_id','nombre','rentworks_almacen','correo_orden_servicio_nueva','correo_orden_averia_nueva','correo_completada_rdp');
                $columnas_etiquetas = array('Id','Tienda','Agencia','Rentworks Almacen','Correo Coordinador Flota','Correo Coordinador Averia','Correo de RDP' );
                $columnas_tipo = array('int','int','text','text','text','text','text' );
                $columnas_mask = array('','','','','','','');

                $columnas_combo  = array('tienda_id');
                $columnas_combo2 = array('tienda');
                $columnas_combo3 = array('nombre');
                break;

            case 14:
                $tabla="orden_traslado_tipo"; 
                $tabla_etiqueta="Orden Traslado - Tipo";
                $columnas = array('id','nombre');
                $columnas_etiquetas = array('Id','Nombre' );
                $columnas_tipo = array('int','text' );
                $columnas_mask = array('','' ); 
                break;

            case 15: 
                pagina_permiso(161);
                $tabla="clientes_vehiculos"; 
                $tabla_etiqueta="Clientes - Vehiculos";
                $columnas = array('id','cliente_id','cliente_email','id_producto');
                $columnas_etiquetas = array('Id','Cliente','Correo','Vehiculo');
                $columnas_tipo = array('int','int','text','int');
                $columnas_mask = array('','','','');                
                $columnas_combo  = array('cliente_id','id_producto');
                $columnas_combo2 = array('entidad','producto');                                
                $columnas_combo3 = array('nombre','codigo_alterno');
                break;

            case 16: 
                pagina_permiso(161);
                $tabla="guardias"; 
                $tabla_etiqueta="Guarias";
                $columnas = array('id','tienda_id','nombre','activo','fecha_creacion');
                $columnas_etiquetas = array('ID','Tienda','Nombre','Estado','Fecha Creacion' );
                $columnas_tipo = array('int','int','text','int','datetime');
                $columnas_mask = array('','','','','');
                 
                $columnas_combo  = array('tienda_id');
                $columnas_combo2 = array('tienda');
                $columnas_combo3 = array('nombre');
                break;
     
// case 12: 
// $tabla="destination_groupxdestination"; 
// $tabla_etiqueta="Destinations in each Group";
// $columnas = array('id','destination_id','destination_group_id');
// $columnas_etiquetas = array('Id','Destination','Destination Group' );
// $columnas_tipo = array('int','int','int' );
// $columnas_mask = array('','','');
// $columnas_combo  = array('destination_id','destination_group_id');
// $columnas_combo2 = array('destination','destination_group');
// $columnas_combo3 = array('nombre','nombre');
// break;
       
       


}   


    




?>