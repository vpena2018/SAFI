<?php
error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
 
require_once ('include/framework.php');  
pagina_permiso(34);

// require_once('include/tcpdf/config/lang/spa.php');
require_once('include/tcpdf/tcpdf.php');  
 

$contenido="" ;
$mensaje_pie="" ;

$style="<style>
table {
 

   padding-left: 10px;

  
} 

.table-bordered {
padding-left: 10px;
  
} 
</style> ";

if (isset($_REQUEST['pdfcod'])) { $cid = intval($_REQUEST['pdfcod']) ; } else	{exit ;}

if (isset($_REQUEST['pc'])) { $alcosto = " (Costo)"; } else	{$alcosto = "";}

 
if (!es_nulo($cid)) {
	
	$sql = "SELECT averia.* 
    ,entidad.nombre AS cliente_nombre
    ,entidad.email AS cliente_email_entidad
    ,producto.nombre AS producto_nombre
    ,producto.codigo_alterno as producto_alterno
    ,producto.placa as producto_placa
    ,producto.chasis as producto_chasis
    ,producto.km as producto_km
    FROM averia
    LEFT OUTER JOIN entidad ON (averia.cliente_id=entidad.id)
    LEFT OUTER JOIN producto ON (averia.id_producto =producto.id)
    
          where averia.id=$cid limit 1  ";
	 

    $result = sql_select($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;
        $numero=$row['numero'];
        $empresa="";//$row['id_empresa'];
        $tipo_averia=$row['id_tipo'];
    
    }
}    else {exit;}
    
   
 $usuario_atender='';
 $usuario_realizado=''; 
    
 $result_2 = sql_select(" SELECT usuario.nombre	
 FROM averia_historial_estado 
 LEFT OUTER JOIN usuario ON (averia_historial_estado.id_usuario=usuario.id)
 WHERE averia_historial_estado.id_maestro=$cid /*AND averia_historial_estado.id_estado=4 */
 ORDER BY averia_historial_estado.id asc LIMIT 1  ");

 if ($result_2->num_rows > 0) {
         
     $row_2 = mysqli_fetch_array($result_2) ;
     $usuario_atender=$row_2['nombre'];
 
 }


 $result_3 = sql_select(" SELECT usuario.nombre	
 FROM averia_historial_estado 
 LEFT OUTER JOIN usuario ON (averia_historial_estado.id_usuario=usuario.id)
 WHERE averia_historial_estado.id_maestro=$cid AND averia_historial_estado.id_estado=21 
 ORDER BY averia_historial_estado.id DESC LIMIT 1  ");

 if ($result_3->num_rows > 0) {
         
     $row_3 = mysqli_fetch_array($result_3) ;
     $usuario_realizado=$row_3['nombre'];
 
 }




// Extend the TCPDF class to create custom Header and Footer
if (!class_exists('MYPDF')){
class MYPDF extends TCPDF {
        
            //Page header
        public function Header() {
                global $empresa,$numero,$alcosto;
                // Logo
                $image_file ="img/logo.png";

                if ($image_file<>"") {$this->Image($image_file, 8, 5, 26, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);}
                
             
                $this->SetY(4); 
                $this->SetTextColor(0,0,0);
                $this->SetFont('helvetica', 'b', 12);
                $this->Cell(0, 6, 'INVERSIONES GLOBALES S.A. DE C.V.', 0, 1, 'C', false, '', 0);
                $this->SetY(9);
                 $this->SetFont('helvetica', 'b', 9);
                 $this->Cell(0, 6, 'ORDEN DE AVERIA'.$alcosto,0, 1, 'C', false, '', 0);
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
$pdf->SetTitle('ORDEN DE AVERIA');
$pdf->SetSubject('ORDEN DE AVERIA');
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
$pdf->Cell(135, 5, $row['cliente_nombre'] .' '. $row['contacto'] , 'LTRB', 0, 'C', false,'',true );


//linea 2
$pdf->Ln();
$pdf->Cell(15, 5, 'Placa' , 'LTRB', 0, 'L', true );
$pdf->Cell(30, 5,$row['producto_placa'] , 'LTRB', 0, 'C', false );
$pdf->Cell(20, 5, 'No. Inventario' , 'LTRB', 0, 'L', true );
$pdf->Cell(135, 5, $row['producto_alterno'] , 'LTRB', 0, 'C', false );


//linea 3
$pdf->Ln();
$pdf->Cell(15, 5, 'km' , 'LTRB', 0, 'L', true );
$pdf->Cell(30, 5, formato_numero($row['kilometraje'],0) , 'LTRB', 0, 'C', false );
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

$subtotal_gravable=0;

$averias_result = sql_select("SELECT averia_detalle.* 
,entidad.nombre as prov
FROM averia_detalle 
LEFT OUTER JOIN entidad ON (averia_detalle.id_proveedor=entidad.id) 
 where averia_detalle.id_maestro=$cid and averia_detalle.producto_tipo=2 
 order by averia_detalle.id ");
$lin=1;
$totalrepuestos=0;

    if ($averias_result->num_rows > 0) { 
      while ($detalle = $averias_result -> fetch_assoc()) { 
        $monto=$detalle['precio_venta'];
        if ($alcosto<>"") {
            $monto=$detalle['precio_costo'];
        }

        $totlinea=floatval($detalle['cantidad'])*floatval($monto);
        $totalrepuestos+= $totlinea ;  
        
        if (!in_array($detalle['producto_codigoalterno'],$_SESSION['p_exento_isv'])) {
            $subtotal_gravable+= $totlinea ;
        }  

        $pdf->Ln();
        $pdf->Cell(135, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false );
        $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false );
        $pdf->Cell(25, 5, formato_numero($monto,2) , 'LTRB', 0, 'R', false );
        $pdf->Cell(25, 5, formato_numero($totlinea,2) , 'LTRB', 0, 'R', false );     
        $lin++;

        if ($detalle['estado']<=1) {
            $mensaje_pie="<strong>Estado:</strong> *** BORRADOR ***" ;
        }
     

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
        unset($averias_result,$detalle);
        $averias_result = sql_select("SELECT averia_detalle.* 
        ,entidad.nombre as prov
        FROM averia_detalle 
        LEFT OUTER JOIN entidad ON (averia_detalle.id_proveedor=entidad.id) 
         where averia_detalle.id_maestro=$cid and (averia_detalle.producto_tipo=3 )
         order by averia_detalle.id ");
        $lin=1;
        $totalobra=0;
        
            if ($averias_result->num_rows > 0) { 
              while ($detalle = $averias_result -> fetch_assoc()) { 
                $monto=$detalle['precio_venta'];
                if ($alcosto<>"") {
                    $monto=$detalle['precio_costo'];
                }
                $totlinea=floatval($detalle['cantidad'])*floatval($monto);
                $totalobra+= $totlinea ;   
                
                if (!in_array($detalle['producto_codigoalterno'],$_SESSION['p_exento_isv'])) {
                    $subtotal_gravable+= $totlinea ;
                }  

                $pdf->Ln();
                $pdf->Cell(135, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false );
                $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false );
                $pdf->Cell(25, 5, formato_numero($monto,2) , 'LTRB', 0, 'R', false );
                $pdf->Cell(25, 5, formato_numero(floatval($detalle['cantidad'])*floatval($monto),2) , 'LTRB', 0, 'R', false );     
                $lin++;

                if ($detalle['estado']<=1) {
                    $mensaje_pie="<strong>Estado:</strong> *** BORRADOR ***" ;
                }
          
                }}

                $pdf->Ln();
                $pdf->SetFont('', 'B');
                $pdf->Cell(175, 5, 'Subtotal Mano de Obra' , 'LTRB', 0, 'R', false );
                $pdf->SetFont('', '');
                $pdf->Cell(25, 5, formato_numero($totalobra,2) , 'LTRB', 0, 'R', false );     
        
                //totales
                $subtotal=$totalobra+$totalrepuestos;
                $gastos_admon=$subtotal*($_SESSION['p_gasto_admon']);        
                $isv= ($subtotal_gravable+$gastos_admon)*($_SESSION['p_isv']);                
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
                    $isv= ($subtotal_gravable)*($_SESSION['p_isv']);                
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
        



 // mensaje pie
  $pdf->Ln();
  $pdf->Ln();
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0);
  $pdf->SetFont('', '',8);
  $pdf->writeHTMLCell('', '', 7, '', $mensaje_pie, 0, 0, 1, false, 'J', true);

  
  //***observaciones  trabajo_realizar*/
  
  $pdf->Ln();
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0);
  $pdf->SetFont('', '',8);
  $pdf->writeHTMLCell('', '', 7, '', '<strong>Observaciones:</strong> '.$row['observaciones'], 0, 0, 1, false, 'J', true);

   

    //****** firmas */
    $pdf->SetY(264);
    $pdf->SetX(8);
    $pdf->Cell(70, 4, 'Elaborada Por: '.$usuario_atender, '', 0, 'L', true ,'',true);
    $pdf->SetX(78);
    $pdf->Cell(70, 4, 'Aprobado Por: '.$usuario_realizado, '', 0, 'L', true,'',true );
    $pdf->SetX(148);
    $pdf->Cell(70, 4, 'Firma Autorizada: __________________', '', 0, 'L', true,'',true );             


    

    //************   PAGINA de FOTOS */
    $result_fotos = sql_select("SELECT averia_foto.id,averia_foto.id_maestro,averia_foto.archivo,averia_foto.fecha
    FROM averia_foto
    WHERE averia_foto.id_maestro=$cid  
    order by averia_foto.fecha,averia_foto.id");
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
                   //$pdf->writeHTMLCell(150, '', 35, '', '<img src="uploa_d/'.$row_fotos["archivo"].'">', 0, 1, 0, true, 'C', true);
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
    
    $pdf->Output(app_dir.'reportes/'.'OrdenAveria_'. $numero .'.pdf', 'F');
} else { 
    $pdf->Output('OrdenAveria_'.$numero.'.pdf', 'I'); //D = descargar
}



 




?>