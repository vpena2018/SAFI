<?php
//error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
 
require_once ('include/framework.php');  
pagina_permiso(140);

// require_once('include/tcpdf/config/lang/spa.php');
require_once('include/tcpdf/tcpdf.php');  
 

$contenido="" ;


$style="<style>
table {
 

   padding-left: 10px;

  
} 

.numero {
    color:#B61515 ;
      
    }

.table-bordered {
padding-left: 10px;
  
} 
</style> ";

if (isset($_REQUEST['pdfcod'])) { $cid = intval($_REQUEST['pdfcod']) ; } else	{exit ;}


 
if (!es_nulo($cid)) {
	
	$sql = "SELECT orden_traslado.* 
    ,producto.codigo_alterno,producto.nombre,producto.placa
    ,orden_traslado_estado.nombre AS elestado
    ,l1.nombre AS motorista1
    ,l2.usuario AS solicitante1
    ,p1.nombre AS elproveedor
    ,t1.nombre AS tiendasalida
    ,t2.nombre AS tiendadestino

    FROM orden_traslado
    LEFT OUTER JOIN producto ON (orden_traslado.id_producto=producto.id)
    LEFT OUTER JOIN orden_traslado_estado ON (orden_traslado.id_estado=orden_traslado_estado.id)
    LEFT OUTER JOIN usuario l1 ON (orden_traslado.id_motorista=l1.id)
    LEFT OUTER JOIN usuario l2 ON (orden_traslado.id_solicitante=l2.id)
    LEFT OUTER JOIN entidad p1 ON (orden_traslado.id_proveedor=p1.id)
    LEFT OUTER JOIN tienda_agencia t1 ON (orden_traslado.id_tienda_salida=t1.id)
    LEFT OUTER JOIN tienda_agencia t2 ON (orden_traslado.id_tienda_destino=t2.id)

    WHERE orden_traslado.id=$cid limit 1 ";
	 

    $result = sql_select($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC ) ;
        $numero=$row['numero'];
    
    }
}    else {exit;}
    
   
//validar
// if (es_nulo($row['id_usuario_autoriza'])) {
//     echo "Documento debe ser autorizado, posteriormente puede imprimir";
//     exit;
// }  
    
      

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('INGLOSA');
$pdf->SetTitle('ORDEN DE TRASLADO');
$pdf->SetSubject('ORDEN DE TRASLADO');
$pdf->SetKeywords('');

 
$pdf->setPrintFooter(false);
$pdf->setPrintHeader(false);


  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

  $pdf->SetMargins(8, 18, 8);


$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



// ---------------------------------------------------------

 

$pdf->SetFont('helvetica', '', 8); //courier     times     helvetica
 
$pdf->AddPage();
//$pdf->AddPage('L', 'A6');

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();


   
    $encabezado='<table>
    <tr>
        <td width="120px"><img src="img/inglosa.jpg" ></td>
        <td width="480" align="center"><b>INVERSIONES GLOBALES S.A. DE C.V.<br><br>Autorización de traslado de vehículo</b></td>
        <td width="110px" align="right" class="numero"><b>No. '.$numero.'</b></td>
    </tr>
   
    </table>
    <br><br><br>
    '   ;

    $html="<table>";
    $html.='
    <tr>
        <td width="90px"><b>Fecha:</b></td>
        <td >'.formato_fecha_de_mysql($row['fecha']).'
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Tienda Origen:</b>
        &nbsp;&nbsp;&nbsp; '.$row['tiendasalida'].'</td>
    </tr>
    
    <tr>
        <td></td>
        <td></td>
    </tr> 

    ';

if ($row['tipo_destino']==2) {
    $html.='<tr>
                <td><b>Proveedor destino:</b></td>
                <td>'.$row['elproveedor'].'</td>
            </tr>';
} else {
    $html.='<tr>
                <td><b>Tienda destino:</b></td>
                <td>'.$row['tiendadestino'].'</td>
            </tr>';
}

 $html.='
 <tr>
        <td></td>
        <td></td>
    </tr> 

 <tr>
    <td><b>Vehiculo:</b></td>
    <td>'.$row['codigo_alterno'].'  '.$row['nombre'].'  &nbsp;&nbsp;&nbsp;  <b>Placa:</b> &nbsp;&nbsp;&nbsp;'.$row['placa'].'</td>
 </tr>	
 
 <tr>
        <td></td>
        <td></td>
    </tr> 

 <tr>
    <td><b>Conductor:</b></td>
    <td>'.$row['motorista1'].'</td>
 </tr>

 <tr>
        <td></td>
        <td></td>
    </tr> 

 <tr>
    <td><b>Observaciones:</b></td>
    <td>'.$row['observaciones'].'</td>
</tr>


<tr>
        <td></td>
        <td></td>
    </tr> 
	
<tr>
    <td ><b>Kilometraje salida:</b></td>
    <td >'.formato_numero($row['kilometraje_salida']).'
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Combustible salida:</b>&nbsp;&nbsp;&nbsp;
    '.$row['combustible_salida'].'</td>
</tr>


 
    <tr>
        <td></td>
        <td>  <br><br><br><br><br><br>
        </td>

    </tr>

    </table>
    
  <table> 
  <tr>
 
  <td> &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; ______________________</td>
  <td>______________________</td>

</tr>
<tr>
  
  <td> &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp;   &nbsp; &nbsp; &nbsp; &nbsp; Firma Autorizado</td>
  <td> &nbsp; &nbsp; &nbsp; &nbsp;  Firma Conductor</td>

</tr>   
    
   </table>  
    
    ';

$pdf->writeHTML($style.$encabezado.$html, true, false, true, false, '');
    
//$pdf->writeHTML('<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>'.$encabezado.$html, true, false, true, false, '');   

ob_end_clean();

if (isset($guardar_archivo)) {
    
    $pdf->Output(app_dir.'reportes/'.'TRASLADO'. $numero .'.pdf', 'F');
} else { 
    $pdf->Output('document_'.$numero.'.pdf', 'I'); //D = descargar
}



 




?>