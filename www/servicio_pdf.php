<?php
error_reporting(0);
if (!isset($guardar_archivo)) {
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
}
 
require_once ('include/framework.php');  
if (!isset($guardar_archivo)) { pagina_permiso(25);}

if (isset($_REQUEST['pc'])) {  pagina_permiso(98); }

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

if (isset($guardar_archivo)) {
    $cid = $elcodigo ;
    $alcosto = "";
} else {

    if (isset($_REQUEST['pdfcod'])) { $cid = intval($_REQUEST['pdfcod']) ; } else	{exit ;}

    if (isset($_REQUEST['pc'])) { $alcosto = " (Costo)";  } else	{$alcosto = "";}
}
 
if (!es_nulo($cid)) {
	
	$sql = "SELECT servicio.* 
    ,entidad.nombre AS cliente_nombre
    ,entidad.email AS cliente_email_entidad
    ,producto.nombre AS producto_nombre
    ,producto.codigo_alterno as producto_alterno
    ,producto.placa as producto_placa
    ,producto.chasis as producto_chasis
    ,producto.km as producto_km
    ,t1.nombre AS tecnico
    ,t2.nombre AS tecnico2
    ,t3.nombre AS tecnico3
    FROM servicio
    LEFT OUTER JOIN entidad ON (servicio.cliente_id=entidad.id)
    LEFT OUTER JOIN producto ON (servicio.id_producto =producto.id)
    LEFT OUTER JOIN usuario t1 ON (servicio.id_tecnico1=t1.id)
    LEFT OUTER JOIN usuario t2 ON (servicio.id_tecnico2=t2.id)
    LEFT OUTER JOIN usuario t3 ON (servicio.id_tecnico3=t3.id) 
    
          where servicio.id=$cid limit 1  ";
	 

    $result = sql_select($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;
        $numero=$row['numero'];
        $empresa="";//$row['id_empresa'];

        $multik=$row['multik'];
        $multik_jt=$row['multik_jt'];
        $id_tipo_revision=$row['id_tipo_revision'];
        $detalle_arr=json_decode($multik,true);
        $detalle_arr_jt=json_decode($multik_jt,true);
    
    }
}    else {exit;}
    
   
  
    
      


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
                 $this->Cell(0, 6, 'ORDEN DE SERVICIO'.$alcosto,0, 1, 'C', false, '', 0);
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
$pdf->SetTitle('ORDEN DE SERVICIO');
$pdf->SetSubject('ORDEN DE SERVICIO');
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
$pdf->Cell(135, 5, $row['cliente_nombre'] , 'LTRB', 0, 'C', false,'',true );


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

//linea 4
$pdf->Ln();
$pdf->Cell(15, 5, 'Tecnico(s)' , 'LTRB', 0, 'L', true );
$pdf->Cell(185, 5, '   '.implode(" ,  ", array_filter([$row['tecnico'],$row['tecnico2'],$row['tecnico3'] ])) , 'LTRB', 0, 'L', false );

$pdf->Ln();
$pdf->Ln();

//*****  detalle */

$pdf->SetFillColor(224, 235, 255);
$pdf->Cell('', 5, 'Detalle de Repuestos' , 'LTRB', 0, 'C', true );
$pdf->Ln();
$pdf->SetFillColor(243, 243, 243 );
$pdf->Cell(135, 5, 'Descripción' , 'LTRB', 0, 'C', true );
$pdf->Cell(15, 5, 'Cantidad' , 'LTRB', 0, 'C', true );
if ($alcosto<>"") {
    $pdf->Cell(25, 5, 'Unitario' , 'LTRB', 0, 'C', true );
    $pdf->Cell(25, 5, 'Total' , 'LTRB', 0, 'C', true );
} else {
    $pdf->Cell(25, 5, 'Nota' , 'LTRB', 0, 'C', true );
    $pdf->Cell(25, 5, 'Estado' , 'LTRB', 0, 'C', true );
}

$servicios_result = sql_select("SELECT servicio_detalle.* 
,entidad.nombre as prov
FROM servicio_detalle 
LEFT OUTER JOIN entidad ON (servicio_detalle.id_proveedor=entidad.id) 
 where servicio_detalle.id_servicio=$cid and servicio_detalle.producto_tipo=2 
 order by servicio_detalle.id ");
$lin=1;
$totalrepuestos=0;

    if ($servicios_result->num_rows > 0) { 
      while ($detalle = $servicios_result -> fetch_assoc()) { 
        $monto=$detalle['precio_venta'];
        if ($alcosto<>"") {
            $monto=$detalle['precio_costo'];
        }

        $totlinea=floatval($detalle['cantidad'])*floatval($monto);
        $totalrepuestos+= $totlinea ;    
        $pdf->Ln();
        //$pdf->Cell(18, 5, $detalle['producto_codigoalterno'] , 'LTRB', 0, 'L', false );
        //$pdf->Cell(117, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false );
        $pdf->Cell(135, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false );
        $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false );
        if ($alcosto<>"") {
            $pdf->Cell(25, 5, formato_numero($monto,2) , 'LTRB', 0, 'R', false );
            $pdf->Cell(25, 5, formato_numero($totlinea,2) , 'LTRB', 0, 'R', false ); 
        }  else {
            $pdf->Cell(25, 5, $detalle['producto_nota'] , 'LTRB', 0, 'L', false,'',true );
            $pdf->Cell(25, 5, get_servicio_detalle_estado($detalle['estado']) , 'LTRB', 0, 'C', false,'',true );  
        }  
        $lin++;
        
        }
    
    }

        if ($alcosto<>"") {
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->Cell(175, 5, 'Subtotal Repuestos' , 'LTRB', 0, 'R', false );
            $pdf->SetFont('', '');
            $pdf->Cell(25, 5, formato_numero($totalrepuestos,2) , 'LTRB', 0, 'R', false );     
        }


        //mano obra
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFillColor(224, 235, 255);
        $pdf->Cell('', 5, 'Detalle de Mano de obra' , 'LTRB', 0, 'C', true );
        $pdf->Ln();
        $pdf->SetFillColor(243, 243, 243 );
        
        if ($alcosto<>"") {
            $pdf->Cell(135, 5, 'Descripción' , 'LTRB', 0, 'C', true );
            $pdf->Cell(15, 5, 'Cantidad' , 'LTRB', 0, 'C', true );
            $pdf->Cell(25, 5, 'Unitario' , 'LTRB', 0, 'C', true );
            $pdf->Cell(25, 5, 'Total' , 'LTRB', 0, 'C', true );
        } else {
            $pdf->Cell(100, 5, 'Descripción' , 'LTRB', 0, 'C', true );
            $pdf->Cell(15, 5, 'Cantidad' , 'LTRB', 0, 'C', true );
            $pdf->Cell(17, 5, 'Tipo' , 'LTRB', 0, 'C', true );
            $pdf->Cell(14, 5, 'Planeado' , 'LTRB', 0, 'C', true );
            $pdf->Cell(14, 5, 'Real' , 'LTRB', 0, 'C', true );
            $pdf->Cell(25, 5, 'Nota' , 'LTRB', 0, 'C', true );
            $pdf->Cell(15, 5, 'Estado' , 'LTRB', 0, 'C', true );
        }
        unset($servicios_result,$detalle);
        $servicios_result = sql_select("SELECT servicio_detalle.* 
        ,entidad.nombre as prov
        ,producto.horas
        ,producto.tipo_mant
        FROM servicio_detalle 
        LEFT OUTER JOIN entidad ON (servicio_detalle.id_proveedor=entidad.id) 
        LEFT OUTER JOIN producto ON (servicio_detalle.id_producto=producto.id)
         where servicio_detalle.id_servicio=$cid and (servicio_detalle.producto_tipo=3 ) 
         order by servicio_detalle.id ");
        $lin=1;
        $totalobra=0;
        $tothoras_planeadas=0;
        $tothoras_reales=0;
            if ($servicios_result->num_rows > 0) { 
              while ($detalle = $servicios_result -> fetch_assoc()) { 
                $monto=$detalle['precio_venta'];
                if ($alcosto<>"") {
                    $monto=$detalle['precio_costo'];
                }
                $totlinea=floatval($detalle['cantidad'])*floatval($monto);
                $totalobra+= $totlinea ;       
                $pdf->Ln();
                
                if ($alcosto<>"") {
                    $pdf->Cell(135, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false ,'',true);
                    $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false );
                    $pdf->Cell(25, 5, formato_numero($monto,2) , 'LTRB', 0, 'R', false );
                    $pdf->Cell(25, 5, formato_numero(floatval($detalle['cantidad'])*floatval($monto),2) , 'LTRB', 0, 'R', false );     
                } else {
                    $pdf->Cell(100, 5, $detalle['producto_nombre'] , 'LTRB', 0, 'L', false,'',true );
                    $pdf->Cell(15, 5, $detalle['cantidad'] , 'LTRB', 0, 'C', false ,'',true);
                    $pdf->Cell(17, 5, $detalle['tipo_mant'] , 'LTRB', 0, 'C', false ,'',true);
                    $pdf->Cell(14, 5, $detalle['horas'] , 'LTRB', 0, 'C', false,'',true );
                    $pdf->Cell(14, 5, $detalle['horas_atender'] , 'LTRB', 0, 'C', false,'',true );
                    $pdf->Cell(25, 5, $detalle['producto_nota'] , 'LTRB', 0, 'L', false,'',true );
                    $pdf->Cell(15, 5, get_servicio_detalle_estado($detalle['estado']) , 'LTRB', 0, 'C', false ,'',true);  
                    $tothoras_planeadas+=floatval($detalle['horas']);
                    $tothoras_reales+=floatval($detalle['horas_atender']);
                }  
                $lin++;
   
                }}

                if ($alcosto<>"") {

                    $pdf->Ln();
                    $pdf->SetFont('', 'B');
                    $pdf->Cell(175, 5, 'Subtotal Mano de Obra' , 'LTRB', 0, 'R', false );
                    $pdf->SetFont('', '');
                    $pdf->Cell(25, 5, formato_numero($totalobra,2) , 'LTRB', 0, 'R', false );     
            
                    //totales
                    $subtotal=$totalobra+$totalrepuestos;
                    $isv= $subtotal*floatval($_SESSION['p_isv']);
                    $total= $subtotal+$isv;
                    
                    $pdf->Ln();

                    $pdf->Ln();
                    $pdf->SetFont('', 'B');
                    $pdf->Cell(175, 5, 'SUBTOTAL' , 'LTRB', 0, 'R', false );
                    $pdf->SetFont('', '');
                    $pdf->Cell(25, 5, formato_numero($subtotal,2) , 'LTRB', 0, 'R', false ); 
                    
                    $pdf->Ln();
                    $pdf->SetFont('', 'B');
                    $pdf->Cell(175, 5, 'ISV '. $_SESSION['p_isv_label']."%"  , 'LTRB', 0, 'R', false );
                    $pdf->SetFont('', '');
                    $pdf->Cell(25, 5, formato_numero($isv,2) , 'LTRB', 0, 'R', false );

                    $pdf->Ln();
                    $pdf->SetFont('', 'B');
                    $pdf->Cell(175, 5, 'TOTAL' , 'LTRB', 0, 'R', false );
                    $pdf->SetFont('', '');
                    $pdf->Cell(25, 5, formato_numero($total,2) , 'LTRB', 0, 'R', false );
        
                } else {
                    $pdf->Ln();
                    $pdf->SetFont('', 'B');
                    $pdf->Cell(132, 5, '' , '', 0, 'R', false );
                    
                    $pdf->Cell(14, 5, formato_numero($tothoras_planeadas,2) , 'LTRB', 0, 'C', false,'',true );
                    $pdf->Cell(14, 5, formato_numero($tothoras_reales,2) , 'LTRB', 0, 'C', false,'',true );
                    $pdf->SetFont('', '');

                }

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
    $result_fotos = sql_select("SELECT servicio_foto.id,servicio_foto.id_servicio,servicio_foto.archivo,servicio_foto.fecha
    FROM servicio_foto
    WHERE servicio_foto.id_servicio=$cid  
    order by servicio_foto.fecha,servicio_foto.id");
    if ($result_fotos!=false){
        if ($result_fotos -> num_rows > 0) {      
            $pdf->AddPage();     
            $ancho_img=66; //66;
            $colx=1;
            $total_columnas=1;
            $pos_x=8;   //8;
            $pn_ln=0;
            $nn=1;
            
            $x = 4;
            $y = 15;
            $w = 50;
            $h = 50;
            while ($row_fotos = $result_fotos -> fetch_assoc()) {
                $fecha=$row_fotos["fecha"];
                $fext = substr($row_fotos["archivo"], -3);
                if ($fext=='jpg' or $fext=='peg' or $fext=='png' ) {                
                    //$image_file= 'uploa_d/thumbnail/'.$row_fotos["archivo"];  
                    $image_file= 'uploa_d/'.$row_fotos["archivo"];                     
                    $pdf->Image($image_file, $x,$y, $w, $h, '', '', '', false, 300, '', false, false, 0, false, false, false);
                    // $pdf->Image($image_file,'', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
                    //$pdf->Image($image_file,'', '', 0, 0, '', '', '', false, 90, '', false, false, 0, false, false, false);
                    
                    /*$pdf->writeHTMLCell($ancho_img, '', $pos_x,'', '<img src="uploa_d/'.$row_fotos["archivo"].'">', 0,$pn_ln, 0, true, 'C', true); 
                    $pos_x+=$ancho_img;
                    */
                    $x+=52; 
                    $colx++;  
                    $total_columnas++;                                       
                    //$pn_ln=0;
                    if ($colx==5) {
                       // $pn_ln=2;
                       $x=4;
                       $y+=52; 
                       $colx=1; 
                    }                                       
                    if ($total_columnas>20) {
                        $pdf->AddPage();
                        $x=4;
                        $y=15;
                        $total_columnas=1;
                        //$pos_x=8;
                        $colx=1; 
                       // $pdf->Ln(7); 
                    }                  
                                                           
                    /*                 
                    if ($pdf->getY()>(230)) {
                        //$pdf->AddPage();
                        $x = 1;
                        $y += 72;
                                    
                        $pos_x=8;
                        $colx=1; 
                        $pn_ln=0;
                        
                    }   */                 
                    $nn++;                         
                }     
                /*   
                $x+=72;
                if ($x>216){
                    $x=1; 
                    $y+=72;  
                } 
                if ($y>=618){                       
                   $x=1; 
                   $y=15;                      
                } 
                */           
            }       
        }
    }
    



    //********* PAGINA MULTI-K */
if ($id_tipo_revision<=5)
//if (!es_nulo($multik))
     {
        $pdf->AddPage();
        
        $det_salida='';
        $det_encabezado='';
        $detalle_inspeccion = sql_select("SELECT multik_revision.id, multik_revision.nombre, multik_revision_grupo.nombre AS grupo
                                            ,k5,k10,k20,k40,k100,jt
                                            FROM multik_revision
                                            LEFT OUTER JOIN multik_revision_grupo ON (multik_revision.id_grupo=multik_revision_grupo.id )
                                            ORDER BY multik_revision.id_grupo,multik_revision.orden");  
        if ($detalle_inspeccion!=false) {
        if ($detalle_inspeccion -> num_rows > 0) {

            switch ($id_tipo_revision) {
                case 1:
                    $detalle_k="k5";
                    break;
                case 2:
                    $detalle_k="k10";
                    break;
                case 3:
                    $detalle_k="k20";
                    break;
                case 4:
                    $detalle_k="k40";
                    break;
                case 5:
                    $detalle_k="k100";
                    break;
                
                default:
                    $detalle_k="k5";
                    break;
            }
                
                $detalle_jt="jt";

                $pdf->SetFillColor(243, 243, 243 );
                $pdf->Cell('', 4, 'MANTENIMIENTO MULTI-K' , '', 0, 'C', false );
                $pdf->SetFont('', '', 7);
 

            $n=1;
            while ($row_detalle = $detalle_inspeccion -> fetch_assoc()) {
                if ($det_encabezado<>$row_detalle["grupo"]) {
                    $det_encabezado=$row_detalle["grupo"];  
                    $pdf->Ln();      
                    $pdf->SetFont('', 'B');       
                    $pdf->Cell(155, 4, $det_encabezado , 'LTRB', 0, '', true );
                    $pdf->Cell(15, 4, 'Rev' , 'LTRB', 0, 'C', true );
                    $pdf->Cell(15, 4, 'NA' , 'LTRB', 0, 'C', true );
                    $pdf->Cell(15, 4, 'JT' , 'LTRB', 0, 'C', true );
                    $pdf->SetFont('', '');
                }
                $valact=''; $valjt='';
                if (isset($detalle_arr[$row_detalle["id"]])) {//$row_detalle[$detalle_k]
                    $valact=$detalle_arr[$row_detalle["id"]];
                }
                if (isset($detalle_arr_jt[$row_detalle["id"]])) {
                    $valjt=$detalle_arr_jt[$row_detalle["id"]];
                }

                $noaplica=false;
                if ($row_detalle[$detalle_k]<>1) {
                    $valact=2;//no aplica
                    $valjt=3;
                   $noaplica=true;
                } 

                 $val_rev="";
                 $val_na="";
                 $val_jt="";

                 if ($valact==1) {$val_rev="x";}
                 if ($valact==2) {$val_na="x";}
                 if ($valjt==1) {$val_jt="x";}
                
                 $pdf->Ln();
                 $pdf->Cell(155, 3, $n.". ".$row_detalle["nombre"] , 'LTRB', 0, '', $noaplica ,'',true);
                 $pdf->Cell(15, 3, $val_rev , 'LTRB', 0, 'C', $noaplica );
                 $pdf->Cell(15, 3, $val_na , 'LTRB', 0, 'C', $noaplica );
                 $pdf->Cell(15, 3, $val_jt , 'LTRB', 0, 'C', $noaplica );
            
                
               
                $n++;
            }
           
        }
        } 

    }


    //********* Fin PAGINA MULTI-K */
 

// reset pointer to the last page
//$pdf->lastPage();



//$pdf->writeHTML($html, true, false, true, false, '');//$style.

ob_end_clean();

if (isset($guardar_archivo)) {    
    $pdf->Output($guardar_archivo, 'F');
} else { 
    $pdf->Output('OrdenServicio_'.$numero.'.pdf', 'I'); //D = descargar
}



 




?>