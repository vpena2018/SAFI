<?php
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else	{exit ;}

require_once ('include/framework.php');

// AJAX CLIENTE y PROVEEDOR
// select 2 clientes t=1  , Proveedor t=2
//####################################
if ($accion=="2") {

	
	$row = array();
	$return_arr = array();
	$row_array = array();
	$tp="";
	$combustible="";
	$carShopPerfil="";
	if((isset($_GET['q']) && strlen($_GET['q']) > 0) || (isset($_GET['id']) && is_numeric($_GET['id'])))
	{

	    if(isset($_GET['q']))
	    {
	        $getVar = $conn->real_escape_string($_GET['q']);
	        $porid="";
	        if (is_numeric($getVar)) {
	        	$porid=" or id=".$getVar;
	        }			
			$carShopPerfil=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
            if ($carShopPerfil=='18'){
			   $whereClause =  "( nombre LIKE '%" . $getVar ."%' OR codigo_alterno LIKE '%" . $getVar ."%'" . $porid .") and habilitado=1 and left(codigo_alterno,3)='CVU' ";
			}else{
	           $whereClause =  "( nombre LIKE '%" . $getVar ."%' OR codigo_alterno LIKE '%" . $getVar ."%'" . $porid .") and habilitado=1";
			}
			//$combustible=" and combustible=1";
	    }
	    elseif(isset($_GET['id']))
	    {
	        $whereClause =  " id = ".$conn->real_escape_string($_GET['id']) ;
	    }

	    if (isset($_GET['page_limit'])) {$limit = intval($_GET['page_limit']);} else {$limit=20;}

	    $tabla='entidad';
	    $tipo_entidad=" and tipo=1";
			if ($_GET['t']=="2") {
				$tipo_entidad=" and tipo=2 and codigo_grupo<>111";
			}
	
	    $sql = "SELECT email,id, concat(codigo_alterno,' ',ifnull(nombre,'')) as text FROM $tabla WHERE $whereClause  $tipo_entidad $combustible ORDER BY nombre LIMIT $limit";
 
	    $result = $conn->query($sql);
	
	        if($result->num_rows > 0)
	        {
	            while($row = $result->fetch_array())
	            {
	                $row_array['id'] = $row['id'];
	                $row_array['text'] = ($row['text']);
					$row_array['email'] = ($row['email']);
	                array_push($return_arr,$row_array);
	            }
	
	        }
	}
	else
	{
	    $row_array['id'] = 0;
	    $row_array['text'] = ('...');
	    array_push($return_arr,$row_array);
	
	}
	
	$ret = array();
	if(isset($_GET['id'])){    $ret = $row_array;	}	else	{	    $ret['results'] = $return_arr;	}
	
	$conn -> close();
	echo salida_json($ret);
exit;
} //accion 2


// AJAX Productos 
// select 2 Vehiculos t=1  , inventario t=2, servicio t=3, cobrables t=4
//####################################
if ($accion=="3") {

	
	$row = array();
	$return_arr = array();
	$row_array = array();
	$tp="";
	
	if((isset($_GET['q']) && strlen($_GET['q']) > 0) || (isset($_GET['id']) && is_numeric($_GET['id'])))
	{
		$getVar="";
	    if(isset($_GET['q']))
	    {
	        $getVar = $conn->real_escape_string($_GET['q']);
	        $porid="";
	        if (is_numeric($getVar)) {
	        	$porid=" or id=".$getVar;
	        }
	        $whereClause =  "( nombre LIKE '%" . $getVar ."%' or placa LIKE '%" . $getVar ."%' or codigo_alterno LIKE '%" . $getVar ."%' or chasis LIKE '%" . $getVar ."%' " . $porid .") and habilitado=1";
			
	    }
	    elseif(isset($_GET['id']))
	    {
	        $whereClause =  " id = ".$conn->real_escape_string($_GET['id']) ;
	    }

	    if (isset($_GET['page_limit'])) {$limit = intval($_GET['page_limit']);} else {$limit=20;}

	    $tabla='producto';

		$tipo_producto=" and ".app_tipo_vehiculo;
			if ($_GET['t']=="2") { // repuesto
				$tipo_producto=" and "." ( ".app_tipo_inventariables."  )";
			}
			
/*			
			if(isset($_GET['taller']))
			{
				if ($_GET['taller']=="1")
				{
				   $tipo_producto=" and left(codigo_alterno,4)='MGV-' and "." ( ". app_tipo_no_inventariables.")";
				}

			}
			else{
				   $tipo_producto=" and "." ( ". app_tipo_no_inventariables.")"; //."  OR ". app_tipo_venta
				} 
*/


			if ($_GET['t']=="3") { // servicio
				if ($_GET['taller']=="1")
				{
				   $tipo_producto=" and left(codigo_alterno,4)='MGV-' and "." ( ". app_tipo_no_inventariables.")";
				}else{
				   $tipo_producto=" and "." ( ". app_tipo_no_inventariables.")"; //."  OR ". app_tipo_venta
				} 
			} 

			if ($_GET['t']=="4") { // cobrable
				$tipo_producto=" and "." ( ".app_tipo_cobrables."  )";
			}

			if(isset($_GET['ff'])) {
				if (strtoupper(substr($getVar, 0, 3))=='ATM'){				
					$sqladd=",  precio_costo , precio_venta ";
				} else {
					$sqladd=", IFNULL((SELECT pc.precio_costo	FROM producto_costo pc WHERE pc.codigo_alterno=producto.codigo_alterno  AND pc.sap_almacen='".$_SESSION['sap_almacen']."' ),0) AS precio_costo, precio_venta ";
				}
				
			
			} else {$sqladd="";}
	    $sql = "SELECT tipo_vehiculo,nombre,codigo_alterno,placa,chasis $sqladd ,id, concat(ifnull(codigo_alterno,''),' ',ifnull(nombre,'')) as text FROM $tabla WHERE $whereClause $tipo_producto ORDER BY tipo desc,nombre LIMIT $limit";
 
	    $result = $conn->query($sql);
	
	        if($result->num_rows > 0)
	        {
	            while($row = $result->fetch_array())
	            {
	                $row_array['id'] = $row['id'];
	                $row_array['text'] = ($row['text']);
					
					$row_array['alt'] = ($row['codigo_alterno']);
					$row_array['desc'] = ($row['nombre']);
					$row_array['placa'] = ($row['placa']);
					$row_array['chasis'] = ($row['chasis']);
					

					if(isset($_GET['ff'])) {
						$row_array['pc'] = ($row['precio_costo']);				
						$row_array['pv'] = ($row['precio_venta']);
					}

					$row_array['tip_veh'] = strtolower($row['tipo_vehiculo']);

	                array_push($return_arr,$row_array);
	            }	
	        }
	}
	else
	{
	    $row_array['id'] = 0;
	    $row_array['text'] = ('...');
	    array_push($return_arr,$row_array);	
	}
	
	$ret = array();
	if(isset($_GET['id'])){    $ret = $row_array;	}	else	{	    $ret['results'] = $return_arr;	}
	
	$conn -> close();
	echo salida_json($ret);
exit;
} //accion 3




// AJAX Productos 
// select 2  Taller
//####################################
if ($accion=="4") {

	
	$row = array();
	$return_arr = array();
	$row_array = array();
	$tp="";
	
	if((isset($_GET['q']) && strlen($_GET['q']) > 0) || (isset($_GET['id']) && is_numeric($_GET['id'])))
	{

	    if(isset($_GET['q']))
	    {
	        $getVar = $conn->real_escape_string($_GET['q']);
	        $porid="";
	        if (is_numeric($getVar)) {
	        	$porid=" or id=".$getVar;
	        }
	        $whereClause =  "( nombre LIKE '%" . $getVar ."%'  OR codigo_alterno LIKE '%" . $getVar ."%' ". $porid .") and habilitado=1 and tipo=2";
			
	    }
	    elseif(isset($_GET['id']))
	    {
	        $whereClause =  " id = ".$conn->real_escape_string($_GET['id']) ;
	    }

	    if (isset($_GET['page_limit'])) {$limit = intval($_GET['page_limit']);} else {$limit=20;}

	    $tabla='entidad';//taller

	    $sql = "SELECT nombre ,id, concat(ifnull(codigo_alterno,''),' - ',ifnull(nombre,'')) as text FROM $tabla WHERE $whereClause  ORDER BY nombre LIMIT $limit";
 
	    $result = $conn->query($sql);
	
	        if($result->num_rows > 0)
	        {
	            while($row = $result->fetch_array())
	            {
	                $row_array['id'] = $row['id'];
	                $row_array['text'] = ($row['text']);
					
					//$row_array['alt'] = ($row['codigo_alterno']);
					$row_array['desc'] = ($row['nombre']);
	                array_push($return_arr,$row_array);
	            }	
	        }
	}
	else
	{
	    $row_array['id'] = 0;
	    $row_array['text'] = ('...');
	    array_push($return_arr,$row_array);	
	}
	
	$ret = array();
	if(isset($_GET['id'])){    $ret = $row_array;	}	else	{	    $ret['results'] = $return_arr;	}
	
	$conn -> close();
	echo salida_json($ret);
exit;
} //accion 4




?>