<?php
//error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
 
require_once ('include/framework.php');  
pagina_permiso(10);

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
	
	$sql = "SELECT orden_combustible.*
    ,entidad.nombre AS elproveedor
    ,orden_combustible_estado.nombre AS elestado
    ,producto.nombre AS vehiculo
    ,producto.codigo_alterno AS codvehiculo
        FROM orden_combustible
        LEFT OUTER JOIN producto ON (orden_combustible.id_producto=producto.id)
        LEFT OUTER JOIN entidad ON (orden_combustible.id_entidad=entidad.id)
        LEFT OUTER JOIN orden_combustible_estado ON (orden_combustible.id_estado=orden_combustible_estado.id)
        
    where orden_combustible.id=$cid limit 1 ";
	 

    $result = sql_select($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC ) ;
        $numero=$row['numero'];
    
    }
}    else {exit;}
    
   
//validar
if (es_nulo($row['id_usuario_autoriza'])) {
    echo "Documento debe ser autorizado, posteriormente puede imprimir";
    exit;
}  
    
      


// Extend the TCPDF class to create custom Header and Footer
// if (!class_exists('MYPDF')){
// class MYPDF extends TCPDF {
        
            //Page header
        // public function Header() {
        //         global $numero;
        //         // Logo
        //         $image_file ="img/inglosa.jpg";

        //         if ($image_file<>"") {$this->Image($image_file, 8, 5, 26, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);}
                
             
        //         $this->SetY(4); 
        //         $this->SetTextColor(0,0,0);
        //         $this->SetFont('helvetica', 'b', 9);
        //         $this->Cell(0, 6, 'INVERSIONES GLOBALES S.A. DE C.V.', 0, 1, 'C', false, '', 0);
        //         $this->SetY(9);
        //          $this->SetFont('helvetica', 'b', 8);
        //          $this->Cell(0, 6, 'ORDEN DE COMBUSTIBLE',0, 1, 'C', false, '', 0);
        //          $this->SetY(9);
        //         //  $this->SetX(48);
        //          $this->SetTextColor(182,21,21);
        //          $this->Cell(0, 6, 'No. '.$numero,0, 1, 'R', false, '', 0);
           
        //     }
        
        //     // Page footer
        //     public function Footer() {
                              
        //     }
    //     }
    // }

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('INGLOSA');
$pdf->SetTitle('ORDEN DE COMBUSTIBLE');
$pdf->SetSubject('ORDEN DE COMBUSTIBLE');
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
        <td width="480" align="center"><b>INVERSIONES GLOBALES S.A. DE C.V.<br><br>ORDEN DE COMBUSTIBLE</b></td>
        <td width="110px" align="right" class="numero"><b>No. '.$numero.'</b></td>
    </tr>
   
    </table>
    <br><br><br>
    '   ;

    $html="<table>";
    $html.='
    <tr>
        <td width="86px"><b>Fecha:</b></td>
        <td >'.formato_fecha_de_mysql($row['fecha']).'</td>
    </tr>
    <tr>
        <td><b>Gasolinera:</b></td>
        <td>'.$row['elproveedor'].'</td>
    </tr>
    <tr>
        <td><b>Vehiculo:</b></td>
        <td>'.$row['codvehiculo']. ' '.$row['vehiculo'].'</td>
  
        <td  width="55px"><b>Placa:</b></td>
        <td>'.$row['placa'].'</td>
    </tr>
    <tr>
        <td><b>Conductor:</b></td>
        <td>'.$row['conductor'].'</td>
  
        <td  width="55px"><b>Destino:</b></td>
        <td>'.$row['destino'].'</td>
    </tr>

    <tr>
    <td><b>Observaciones</b></td>
    <td>'.$row['observaciones'].'</td>

</tr>

<tr>
<td><b>Litros:</b></td>
<td>'.vacio_if_nulocero($row['litros']).'</td>

<td width="55px"><b>Lps:</b></td>
<td>'.vacio_if_nulocero($row['lempiras']).'</td>
</tr>

<tr>
<td><b>Combustible:</b></td>
<td>'.$row['tipo_combustible'].'</td>

</tr>

<tr>
    <td><b>Otros:</b></td>
    <td>'.$row['otros'].'</td>

</tr>
<tr>
    <td></td>
    <td>  <br><br><br><br>
</td>

</tr>

    </table>
    
  <table> 
  <tr>
 
  <td> &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; ______________________</td>
  <td>______________________</td>

</tr>
<tr>
  
  <td> &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Autorizado</td>
  <td> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Vo. Bo.</td>

</tr>   
    
   </table>  
    
    ';

$pdf->writeHTML($style.$encabezado.$html, true, false, true, false, '');
    
$pdf->writeHTML('<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>'.$encabezado.$html, true, false, true, false, '');   

ob_end_clean();

if (isset($guardar_archivo)) {
    
    $pdf->Output(app_dir.'reportes/'.'COMBUSTIBLE'. $numero .'.pdf', 'F');
} else { 
    $pdf->Output('document_'.$numero.'.pdf', 'I'); //D = descargar
}



 




?>