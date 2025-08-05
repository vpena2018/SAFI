<?php
error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
 
require_once ('include/framework.php');  
pagina_permiso(32);

// require_once('include/tcpdf/config/lang/spa.php');
require_once('include/tcpdf/tcpdf.php');  
 

$contenido="" ;


$style="<style>
table {
 

   padding-left: 10px;

  
} 

.table-bordered {
padding-left: 10px;
  
} 
</style> ";

if (isset($_REQUEST['pdfcod'])) { $cid = intval($_REQUEST['pdfcod']) ; } else	{exit ;}


 
if (!es_nulo($cid)) {
	
	$sql = "SELECT cotizacion.* 
    ,entidad.nombre AS cliente_nombre
    ,entidad.email AS cliente_email_entidad
    ,producto.nombre AS producto_nombre
    ,producto.codigo_alterno as producto_alterno
    ,producto.placa as producto_placa
    ,producto.chasis as producto_chasis
    ,producto.km as producto_km
    FROM cotizacion
    LEFT OUTER JOIN entidad ON (cotizacion.cliente_id=entidad.id)
    LEFT OUTER JOIN producto ON (cotizacion.id_producto =producto.id)
    
          where cotizacion.id=$cid limit 1  ";
	 

    $result = sql_select($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;
        $numero=$row['numero'];
        $empresa="";//$row['id_empresa'];
        $tipo_averia=$row['id_tipo'];
    
    }
}    else {exit;}
    
   
  
    
      


// Extend the TCPDF class to create custom Header and Footer
if (!class_exists('MYPDF')){
class MYPDF extends TCPDF {
        
            //Page header
        public function Header() {
                global $empresa,$numero;
                // Logo
                $image_file ="img/logo.png";

                if ($image_file<>"") {$this->Image($image_file, 8, 5, 26, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);}
                
             
                $this->SetY(4); 
                $this->SetTextColor(0,0,0);
                $this->SetFont('helvetica', 'b', 12);
                $this->Cell(0, 6, 'INVERSIONES GLOBALES S.A. DE C.V.', 0, 1, 'C', false, '', 0);
                $this->SetY(9);
                $this->SetFont('helvetica', 'b', 9);
                $this->Cell(0, 6, 'COTIZACION',0, 1, 'C', false, '', 0);
                $this->SetY(9);
                $this->SetTextColor(182,21,21);
                $this->Cell(0, 6, 'No. '.$numero,0, 1, 'R', false, '', 0);
           
              
                

            }
        
            // Page footer
            public function Footer() {
               
                $this->SetFont('', '', 8);
                $this->SetTextColor(128, 139, 150 );              
                $this->SetY(-13);
            //   // $this->Cell(0, 10, 'texto', 0, false, 'C', 0, '', 0, false, 'T', 'M');
            //    $this->SetX(20);
            //     $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->SetX(130);
                $this->Cell(0, 10, '         '.date('d/m/Y H:ia'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
            }
        }
    }
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('INGLOSA');
$pdf->SetTitle('COTIZACION');
$pdf->SetSubject('COTIZACION');
$pdf->SetKeywords('');
$pdf->setPrintFooter(true);
$pdf->setPrintHeader(true);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(8, 18, 8);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// ---------------------------------------------------------


$pdf->SetFont('helvetica', '', 8); //courier     times     helvetica
 
$pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

    $pdf->SetFillColor(243, 243, 243 );
    $pdf->SetTextColor(0);
    $pdf->SetFont('', '',8);
   

    //*****  Encabezado */

//linea 1
$pdf->Cell(15, 5, 'Fecha' , 'LTRB', 0, 'L', true );
$pdf->Cell(30, 5, formato_fecha_de_mysql($row['fecha']) , 'LTRB', 0, 'C', false );
$pdf->Cell(20, 5, 'Cliente' , 'LTRB', 0, 'L', true );
$pdf->Cell(135, 5, $row['cliente_nombre'] , 'LTRB', 0, 'C', false );


//linea 2
$pdf->Ln();
$pdf->Cell(15, 5, 'Placa' , 'LTRB', 0, 'L', true );
$pdf->Cell(30, 5,$row['producto_placa'] , 'LTRB', 0, 'C', false );
$pdf->Cell(20, 5, 'No. Inventario' , 'LTRB', 0, 'L', true );
$pdf->Cell(135, 5, $row['producto_alterno'] , 'LTRB', 0, 'C', false );


//linea 3
$pdf->Ln();
$pdf->Cell(15, 5, 'km' , 'LTRB', 0, 'L', true );
$pdf->Cell(30, 5, formato_numero($row['producto_km'],0) , 'LTRB', 0, 'C', false );
$pdf->Cell(20, 5, 'Vehiculo' , 'LTRB', 0, 'L', true );
$pdf->Cell(135, 5, $row['producto_nombre'] , 'LTRB', 0, 'C', false );

$pdf->Ln();
$pdf->Ln();

//*****  detalle */

$pdf->SetFillColor(224, 235, 255);
$pdf->Cell('', 5, 'Detalle de Repuestos' , 'LTRB', 0, 'C', true );
$pdf->Ln();
$pdf->SetFillColor(243, 243, 243 );
$pdf->Cell(135, 5, 'Descripción' , 'LTRB', 0, 'C', true );
$pdf->Cell(15, 5, 'Cantidad' , 'LTRB', 0, 'C', true );
$pdf->Cell(25, 5, 'Unitario' , 'LTRB', 0, 'C', true );
$pdf->Cell(25, 5, 'Total' , 'LTRB', 0, 'C', true );

$cotizacions_result = sql_select("SELECT cotizacion_detalle.* 
,entidad.nombre as prov
FROM cotizacion_detalle 
LEFT OUTER JOIN entidad ON (cotizacion_detalle.id_proveedor=entidad.id) 
 where cotizacion_detalle.id_maestro=$cid and cotizacion_detalle.producto_tipo=2 
 order by cotizacion_detalle.id ");
$lin=1;
$totalrepuestos=0;

    if ($cotizacions_result->num_rows > 0) { 
      while ($detalle = $cotizacions_result -> fetch_assoc()) { 
        $totlinea=floatval($detalle['cantidad'])*floatval($detalle['precio_venta']);
        $totalrepuestos+= $totlinea ;    
        $pdf->Ln();
        $pdf->Cell(135, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false );
        $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false );
        $pdf->Cell(25, 5, formato_numero($detalle['precio_venta'],2) , 'LTRB', 0, 'R', false );
        $pdf->Cell(25, 5, formato_numero($totlinea,2) , 'LTRB', 0, 'R', false );     
        $lin++;
        
        }
    
    }
        $pdf->Ln();
        $pdf->SetFont('', 'B');
        $pdf->Cell(175, 5, 'Subtotal Repuestos' , 'LTRB', 0, 'R', false );
        $pdf->SetFont('', '');
        $pdf->Cell(25, 5, formato_numero($totalrepuestos,2) , 'LTRB', 0, 'R', false );     



        //mano obra
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFillColor(224, 235, 255);
        $pdf->Cell('', 5, 'Detalle de Mano de obra' , 'LTRB', 0, 'C', true );
        $pdf->Ln();
        $pdf->SetFillColor(243, 243, 243 );
        $pdf->Cell(135, 5, 'Descripción' , 'LTRB', 0, 'C', true );
        $pdf->Cell(15, 5, 'Cantidad' , 'LTRB', 0, 'C', true );
        $pdf->Cell(25, 5, 'Unitario' , 'LTRB', 0, 'C', true );
        $pdf->Cell(25, 5, 'Total' , 'LTRB', 0, 'C', true );
        unset($cotizacions_result,$detalle);
        $cotizacions_result = sql_select("SELECT cotizacion_detalle.* 
        ,entidad.nombre as prov
        FROM cotizacion_detalle 
        LEFT OUTER JOIN entidad ON (cotizacion_detalle.id_proveedor=entidad.id) 
         where cotizacion_detalle.id_maestro=$cid and (cotizacion_detalle.producto_tipo=3 )
         order by cotizacion_detalle.id ");
        $lin=1;
        $totalobra=0;
        
            if ($cotizacions_result->num_rows > 0) { 
              while ($detalle = $cotizacions_result -> fetch_assoc()) { 
                $totlinea=floatval($detalle['cantidad'])*floatval($detalle['precio_venta']);
                $totalobra+= $totlinea ;       
                $pdf->Ln();
                $pdf->Cell(135, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false );
                $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false );
                $pdf->Cell(25, 5, formato_numero($detalle['precio_venta'],2) , 'LTRB', 0, 'R', false );
                $pdf->Cell(25, 5, formato_numero(floatval($detalle['cantidad'])*floatval($detalle['precio_venta']),2) , 'LTRB', 0, 'R', false );     
                $lin++;
              
                }}

                $pdf->Ln();
                $pdf->SetFont('', 'B');
                $pdf->Cell(175, 5, 'Subtotal Mano de Obra' , 'LTRB', 0, 'R', false );
                $pdf->SetFont('', '');
                $pdf->Cell(25, 5, formato_numero($totalobra,2) , 'LTRB', 0, 'R', false );     
        
                //totales
                //$subtotal=$totalobra+$totalrepuestos;
                //$isv= $subtotal*floatval($_SESSION['p_isv']);
                //$total= $subtotal+$isv;

                //totales
                $subtotal=$totalobra+$totalrepuestos;
                $gastos_admon=$subtotal*($_SESSION['p_gasto_admon']);        
                //$isv= ($subtotal_gravable+$gastos_admon)*($_SESSION['p_isv']);                
                $isv= ($subtotal+$gastos_admon)*($_SESSION['p_isv']);                
                $total= $subtotal+$isv+$gastos_admon;
                
                //solo averias cobrables pagan ISV y GA
                if ($tipo_averia==2 or $tipo_averia==3) { 
                    $isv=0;
                    $gastos_admon=0; 
                    $total= $subtotal;
                }

                //4 Cobrable sin ISV sin Gastos Administrativos
                if ($tipo_averia==4) { 
                    $isv=0;
                    $gastos_admon=0; 
                    $total= $subtotal;
                }
                //5 Cobrable sin ISV con Gastos Administrativos
                if ($tipo_averia==5) { 
                    $isv=0;
                    $total= $subtotal+$gastos_admon;
                }
                //6 Cobrable sin Gastos Administrativos con ISV
                if ($tipo_averia==6) { 
                    $gastos_admon=0; 
                    //$isv= ($subtotal_gravable)*($_SESSION['p_isv']);                
                    $isv= ($subtotal+$gastos_admon)*($_SESSION['p_isv']);                
                    $total= $subtotal+ $isv;
                }

                //averias al costo no pagan isv gastos admon
                if (isset($_REQUEST['pc'])) {
                    $isv=0;
                    $gastos_admon=0; 
                    $total= $subtotal;
                }
               
                $pdf->Ln();

                $pdf->Ln();
                $pdf->SetFont('', 'B');
                $pdf->Cell(175, 5, 'SUBTOTAL' , 'LTRB', 0, 'R', false );
                $pdf->SetFont('', '');
                $pdf->Cell(25, 5, formato_numero($subtotal,2) , 'LTRB', 0, 'R', false ); 
                
                $pdf->Ln();
                $pdf->SetFont('', 'B');
                $pdf->Cell(175, 5, 'GASTOS ADMINISTRATIVOS' , 'LTRB', 0, 'R', false );
                $pdf->SetFont('', '');
                $pdf->Cell(25, 5, formato_numero($gastos_admon,2) , 'LTRB', 0, 'R', false ); 
                             
                $pdf->Ln();
                $pdf->SetFont('', 'B');
                $pdf->Cell(175, 5, 'ISV '. $_SESSION['p_isv_label']."%" , 'LTRB', 0, 'R', false );
                $pdf->SetFont('', '');
                $pdf->Cell(25, 5, formato_numero($isv,2) , 'LTRB', 0, 'R', false );

                $pdf->Ln();
                $pdf->SetFont('', 'B');
                $pdf->Cell(175, 5, 'TOTAL' , 'LTRB', 0, 'R', false );
                $pdf->SetFont('', '');
                $pdf->Cell(25, 5, formato_numero($total,2) , 'LTRB', 0, 'R', false );     
        
  //***observaciones  trabajo_realizar*/
  $pdf->Ln();
  $pdf->Ln();
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0);
  $pdf->SetFont('', '',8);
  $pdf->writeHTMLCell('', '', 7, '', '<strong>Observaciones:</strong> '.$row['observaciones'], 0, 0, 1, false, 'J', true);


    //****** firmas */
    $pdf->SetY(264);
    $pdf->SetX(8);
    $pdf->Cell(100, 4, 'Firma Autorizada: ______________________', '', 0, 'L', true );
    $pdf->SetX(100);
    $pdf->Cell(100, 4, 'Firma Autorizada: ______________________', '', 0, 'L', true );             


    //************   PAGINA de FOTOS */
    $result_fotos = sql_select("SELECT cotizacion_foto.id,cotizacion_foto.id_maestro,cotizacion_foto.archivo,cotizacion_foto.fecha
    FROM cotizacion_foto
    WHERE cotizacion_foto.id_maestro=$cid  
    order by cotizacion_foto.fecha,cotizacion_foto.id");
    if ($result_fotos!=false){
        if ($result_fotos -> num_rows > 0) { 
            $pdf->AddPage();

            $ancho_img=66;
            $colx=1;
            $total_columnas=3;
            $pos_x=8;
            $pn_ln=0;
            $nn=1;
             
            while ($row_fotos = $result_fotos -> fetch_assoc()) {

                $fext = substr($row_fotos["archivo"], -3);
                if ($fext=='jpg' or $fext=='peg' or $fext=='png' ) {
                
                   $image_file= 'uploa_d/thumbnail/'.$row_fotos["archivo"];  
                   // $pdf->Image($image_file,'', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
                   // $pdf->writeHTMLCell(150, '', 35, '', '<img src="uploa_d/'.$row_fotos["archivo"].'">', 0, 1, 0, true, 'C', true);
                   $pdf->writeHTMLCell($ancho_img, '', $pos_x,'', '<img src="uploa_d/'.$row_fotos["archivo"].'">', 0,$pn_ln, 0, true, 'C', true);
                   $pos_x+=$ancho_img;           
                   $colx++;
                   $pn_ln=0;
                   if ($colx==($total_columnas)) {$pn_ln=2;}
                    if ($colx>$total_columnas) {
                         $pos_x=8;
                         $colx=1; 
                        // $pdf->Ln(7); 
                    }
                    if ($pdf->getY()>(230)) {
                       $pdf->AddPage();
                       $pos_x=8;
                       $colx=1; 
                       $pn_ln=0;
                    }
 
                 $nn++;       
            }        
        }
    }
    }

 

// reset pointer to the last page
//$pdf->lastPage();



//$pdf->writeHTML($html, true, false, true, false, '');//$style.

ob_end_clean();

if (isset($guardar_archivo)) {
    
    $pdf->Output(app_dir.'reportes/'.'Cotizacion_'. $numero .'.pdf', 'F');
} else { 
    $pdf->Output('Cotizacion_'.$numero.'.pdf', 'I'); //D = descargar
}



 




?>