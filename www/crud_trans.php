<?php
	if (isset($_REQUEST['tbl'])) { $numtabla = $_REQUEST['tbl']; } else	exit ;
	require_once ('crud_tablas.php');
	if ($tabla=="" ){exit;}
	require_once ('include/config.php');

	 
	  $aColumns =$columnas;
	  array_unshift($aColumns, 'btnaccion');
	 
// $aColumns = array( 'id','nombre', 'btnaccion' );

	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
	
//	if ($tabla=="distribuidor" or $tabla=="bodega") {$sIndexColumn = "codigo";}
	/* DB table to use */
	$sTable = $tabla;
	
	
	/* Database connection information */
	$gaSql['user']       = db_user;
	$gaSql['password']   = db_pw;
	$gaSql['db']         = db_name;
	$gaSql['server']     = str_replace("p:", '', db_ip);
	
		
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* 
	 * MySQL connection
	 */ 
	// $gaSql['link'] =  mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		// die( 'Could not open connection to server' );
// 	
	// mysql_select_db( $gaSql['db'], $gaSql['link'] ) or 
		// die( 'Could not select database '. $gaSql['db'] );
// 	
	// mysql_set_charset('utf8',$gaSql['link']); 
	
	$conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (mysqli_connect_errno()) {   echo '<div class="row-fluid">   <div class="alert alert-info">'."Database Connection Error [DB:101]".'</div></div>'; exit; } 
    $conn->set_charset("utf8");

    
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".$conn->real_escape_string( $_GET['iDisplayStart'] ).", ".
			$conn->real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".$conn->real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ($aColumns[$i]!='btnaccion') {$sWhere .= $aColumns[$i]." LIKE '%".$conn->real_escape_string( $_GET['sSearch'] )."%' OR ";}
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			if ($aColumns[$i]!='btnaccion') {$sWhere .= $aColumns[$i]." LIKE '%".$conn->real_escape_string($_GET['sSearch_'.$i])."%' ";}
		}
	}
	
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	 // ".str_replace(" , ", " ", implode(", ", $aColumns))."
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS  id as btnaccion, ".implode(',', crudcombolookup($columnas))."  
		FROM   $sTable    
		$sWhere
		$sOrder
		$sLimit
	";
	
	
	//$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$rResult = $conn -> query($sQuery);
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	// $rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	// $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
	// $iFilteredTotal = $aResultFilterTotal[0];
	$rResultFilterTotal = $conn -> query($sQuery);
	$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
	$iFilteredTotal= $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	// $rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	// $aResultTotal = mysql_fetch_array($rResultTotal);
	// $iTotal = $aResultTotal[0];
	$rResultTotal = $conn -> query($sQuery);
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal= $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	
	// while ( $aRow = mysql_fetch_array( $rResult ) )
	   while ( $aRow = mysqli_fetch_array( $rResult ) )
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] == "version" )
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
			}
			else if ( $aColumns[$i] != ' ' )
			{
				/* General output */
				if ( $aColumns[$i] <>"clave" ){ $row[] = ($aRow[ $aColumns[$i] ]);} //htmlentities
			}
		}
		$output['aaData'][] = $row;
	}
	
	echo json_encode( $output );
?>