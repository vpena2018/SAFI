<?php
error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once ('include/framework.php');  
if (!isset($guardar_archivo)) { pagina_permiso(22);}


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
} else {
    if (isset($_REQUEST['pdfcod'])) { $cid = intval($_REQUEST['pdfcod']) ; } else	{exit ;}
}

 
if (!es_nulo($cid)) {
	
	$sql = "SELECT inspeccion.* 
    ,entidad.nombre AS cliente_nombre
    ,entidad.email AS cliente_email_entidad
    ,producto.nombre AS producto_nombre
    ,producto.codigo_alterno as producto_alterno
    ,usuario.nombre as elusuario
    FROM inspeccion
    LEFT OUTER JOIN entidad ON (inspeccion.cliente_id=entidad.id)
    LEFT OUTER JOIN producto ON (inspeccion.id_producto =producto.id)
    LEFT OUTER JOIN usuario ON (inspeccion.id_usuario =usuario.id)
    
          where inspeccion.id=$cid limit 1  ";
	 

    $result = sql_select($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;
        $numero=$row['numero'];
        $empresa=$row['id_empresa'];
        $modificada="";
        if (trim($row['modificada'])<>'') {
            $modificada="Modificada el ".$row['modificada'];
        }
        
    
    }
}    else {exit;}
    
   
  
    
     


// Extend the TCPDF class to create custom Header and Footer
if (!class_exists('MYPDF')){
class MYPDF extends TCPDF {
        
            //Page header
        public function Header() {
                global $empresa,$numero;
                // Logo
                $image_file = 'img/inglosa.jpg';
                if ($empresa==1) { $image_file = 'img/hertz.jpg';}
                if ($empresa==2) { $image_file = 'img/dollar.jpg';}
                 
                if ($image_file<>"") {$this->Image($image_file, 8, 5, 26, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);}
                
             
                $this->SetY(4); 
                $this->SetTextColor(0,0,0);
                $this->SetFont('helvetica', 'b', 12);
                $this->Cell(0, 6, 'INVERSIONES GLOBALES S.A. DE C.V.', 0, 1, 'C', false, '', 0);
                $this->SetY(9);
                $this->SetFont('helvetica', 'b', 9);
                $this->Cell(0, 6, 'HOJA DE INSPECCION DEL VEHICULO / VEHICLE INSPECTION SHEET',0, 1, 'C', false, '', 0);
                $this->SetY(9);
                $this->SetTextColor(182,21,21);
                $this->Cell(0, 6, 'No. '.$numero,0, 1, 'R', false, '', 0);
           
              
                

            }
        
            // Page footer
            public function Footer() {
               global $modificada;
                
                              
                $this->SetY(-13);
               // $this->Cell(0, 10, 'texto', 0, false, 'C', 0, '', 0, false, 'T', 'M');
               
               $this->SetFont('', '', 8);
               $this->SetTextColor(90, 70, 70 );
               $this->SetX(5);
               $this->Cell(0, 10, $modificada, 0, false, '', 0, '', 0, false, 'T', 'L');
               
               $this->SetFont('', '', 8);
               $this->SetTextColor(128, 139, 150 );

               $this->SetX(20);
                $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->SetX(130);
                $this->Cell(0, 10, '         '.date('d/m/Y H:ia'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
            }
        }
    }
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetCompression(true);
$pdf->SetAuthor('INGLOSA');
$pdf->SetTitle('HOJA DE INSPECCION');
$pdf->SetSubject('HOJA DE INSPECCION');
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

    //*******Encabezado */


    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('', '',8);

    $left_column = '<b>Datos del Cliente / Customer Information:</b> <br>'.$row['cliente_nombre'].'<br>'.$row['cliente_contacto'];
    $right_column = '<b>Datos del Vehiculo / Vehicle Details:</b> <br>'.$row['producto_nombre'].'<br><b>No. </b>'.$row['producto_alterno'];
        
        $pdf->writeHTMLCell(100, '', '', $pdf->getY(), $left_column, 0, 0, 1, true, '', true);
        $pdf->writeHTMLCell(100, '', '', '', $right_column, 0, 1, 1, true, '', true);
        $pdf->Ln(2);

   
 //********************  Detalles del Vehiculo */  
     $pdf->SetFillColor(0, 0, 0);
     $pdf->SetTextColor(255);
     $pdf->SetFont('', 'B',9);

    //  if ($tipo_inspeccion=='1' and $tipo_doc=='2'){ $columnas=1; } //RENTA
    //  if ($tipo_inspeccion=='1' and $tipo_doc=='1'){ $columnas=2; } //RENTA
    //  if ($tipo_inspeccion=='2' and $tipo_doc=='1'){ $columnas=1; } //TALLER
    //  if ($tipo_inspeccion=='2' and $tipo_doc=='2'){ $columnas=2; } //TALLER
     
     if ( $row["tipo_doc"]==1) {
        $movimiento=" al Ingresar"; $movimiento2="at Check-In Time";
    } else {
        $movimiento=" al Salir"; $movimiento2="at Check-Out Time";
    }

     $pdf->Cell(0, 6, "Estatus del Vehiculo $movimiento / Vehicle Status $movimiento2", 1, 1, 'C', true, '', 0);
     $pdf->Ln(2); 

     $pdf->SetFillColor(255, 255, 255);
     $pdf->SetTextColor(0);
     $pdf->SetFont('', '',8);


       //  $pdf->writeHTMLCell('', '', '', $pdf->getY(), 'texto aqui', 0, 0, 1, true, '', true);
       //linea 1
       $pdf->SetFillColor(243, 243, 243 );
       $pdf->Cell(47, 4, 'Combustible / Fuel' , 'LTRB', 0, 'L', true );
       $lineas  = array('E','1/16','1/8','3/16','1/4','5/16','3/8','7/16','1/2','9/16','5/8','11/16','3/4','13/16','7/8','15/16','F');
        $pdf->SetFillColor(139, 188, 255);   
       $i=0;
       foreach ($lineas as  $value) {
           $i++;
           if ($value==$row["combustible_entrada"]) {$selected=true; } else {$selected=false;}
           $pdf->Cell(9, 4, $value, 'LTRB', 0, 'C', $selected);// fraccion($value)          
       }  
      // $pdf->Cell('', 4, '', 'LTRB', 0, 'C', false); 
      

       //linea 2
       $pdf->Ln();
       $pdf->SetFillColor(243, 243, 243 );
       $pdf->Cell(47, 4, 'Kilometraje / Odometer Reading' , 'LTRB', 0, 'L', true );
       $pdf->Cell(50, 4, 'Fecha / Date' , 'LTRB', 0, 'C', true );
       $pdf->Cell(50, 4, 'Hora / Time' , 'LTRB', 0, 'C', true );
       $pdf->Cell(53, 4, 'Tipo Combustible / Fuel Type' , 'LTRB', 0, 'C', true );

        //linea 3
        $pdf->Ln();
        $pdf->SetFillColor(255, 255, 255 );
        $pdf->Cell(47, 4, formato_numero($row["kilometraje_entrada"],0) , 'LTRB', 0, 'C', false );
        $pdf->Cell(50, 4, formato_fecha_de_mysql($row["fecha_entrada"]) , 'LTRB', 0, 'C', false );
        $pdf->Cell(50, 4, formato_solohora_de_mysql($row["hora_entrada"]) , 'LTRB', 0, 'C', false );
        $pdf->Cell(53, 4, $row["combustible_tipo"] , 'LTRB', 0, 'C', false );
                     

        $pdf->Ln(7);


//********************  Detalles del Vehiculo */

     $pdf->SetFillColor(0, 0, 0);
     $pdf->SetTextColor(255);
     $pdf->SetFont('', 'B',9);

     $pdf->Cell(0, 6, 'Detalles del Vehiculo / Vehicle Details', 1, 1, 'C', true, '', 0);
     $pdf->Ln(2); 

     $pdf->SetFillColor(224, 235, 255);
     $pdf->SetTextColor(0);
     $pdf->SetFont('', '',8);
 
      $detalle_arr=json_decode($row["detalles"],true);
    //  $detalle_arr2=json_decode($detalles2,true);
$detallespos=$pdf->getY();
    $w = array(90, 8);
      $det_salida='';
      $det_encabezado='';
     $detalle_inspeccion = sql_select("SELECT inspeccion_revision.id, inspeccion_revision.nombre, inspeccion_revision_grupo.nombre AS grupo
     FROM inspeccion_revision
     LEFT OUTER JOIN inspeccion_revision_grupo ON (inspeccion_revision.id_grupo=inspeccion_revision_grupo.id )
     WHERE inspeccion_revision.tipo_inspeccion=1
     ORDER BY inspeccion_revision.id_grupo,inspeccion_revision.orden"); //$tipo_inspeccion 
        if ($detalle_inspeccion!=false){
            if ($detalle_inspeccion -> num_rows > 0) {
       
            while ($row_detalle = $detalle_inspeccion -> fetch_assoc()) {
                if ($det_encabezado<>$row_detalle["grupo"]) {
                    $pdf->Cell($w[0]+$w[1], 4, $row_detalle["grupo"], 'LTRB', 0, 'L', true );
                 
                    $pdf->Ln(); 
                    $det_encabezado=$row_detalle["grupo"] ;              
                }

                 $valact=''; $valor_anterior='';
                // if (isset($detalle_arr[$row_detalle["id"]])) {
                 
                         $valact=$detalle_arr[$row_detalle["id"]];
                 

           
                
                 if ($valact==0) { $valact='No';                 }                    
                 if ($valact==1) { $valact='Si';          }

                // }

                // if (isset($detalle_arr2[$row_detalle["id"]])) {
                // $valor_anterior=$detalle_arr2[$row_detalle["id"]];
                // }
                $pdf->Cell($w[0], 4,  $row_detalle["nombre"], 'LTRB', 0, 'L', false );
                $pdf->Cell($w[1], 4, $valact, 'LTRB', 0, 'C', false); 
                // $pdf->writeHTMLCell($w[1], 4,0,0,$valact, 'LTRB', 0, 'C', false);          
                $pdf->Ln();
              
               //.insp_sino_radio($row_detalle["id"],$valact,$valor_anterior,'required').'</span></li> ' ;

            }
          
         }
        }




     $pdf->Cell(array_sum($w), 0, '', 'T');
     $numeracionpos0=$pdf->getY()+3;
    
    //***foto del vehiculo */
  
    $imgdata = base64_decode(str_replace('data:image/png;base64,','',($_REQUEST['pdfimg1'])));
    $pdf->Image('@'.$imgdata,113,$detallespos,100,125);
    $leyendapos=$pdf->getY()-12;
    $pdf->Image('img/golpes1.png',115,$leyendapos,40,8);
    $pdf->Image('img/golpes2.png',160,$leyendapos+1,30,4);

   // $observacionespos=$pdf->getY()-12;

    //***observaciones */
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('', '',8);
    $pdf->writeHTMLCell('', '', 7, $numeracionpos0, '<strong>Observaciones / Observations:</strong> '.$row['observaciones'], 0, 0, 1, true, 'J', true);
    $numeracionpos=$pdf->getY()+6;
    if ($numeracionpos0>$numeracionpos) {
        $numeracionpos=$numeracionpos0;
    }



//******** Marca llantas */
    $pdf->SetY($numeracionpos);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255);
    $pdf->SetFont('', 'B',9);

    $pdf->Cell(0, 6, 'Marca y numeración de Llantas y Batería / Brand and Dimension of Tires and Battery', 1, 1, 'C', true, '', 0);
    $pdf->Ln(2); 

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('', '',8);

   // $pdf->writeHTMLCell('', '', '', $pdf->getY(), ' texto aqui  ', 0, 0, 1, true, '', true);
    //linea 1
    $pdf->SetFillColor(243, 243, 243 );
    $pdf->Cell(4, 4, 'R' , 'LTRB', 0, 'C', true );
    $pdf->Cell(36, 4, 'Delantera Izquierda / Front Left' , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'R' , 'LTRB', 0, 'C', true );
    $pdf->Cell(36, 4, 'Trasera Izquierda / Rear Left' , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'R' , 'LTRB', 0, 'C', true );
    $pdf->Cell(36, 4, 'Llanta de Repuesto / Spare Tire' , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'R' , 'LTRB', 0, 'C', true );
    $pdf->Cell(36, 4, 'Trasera Derecha / Rear Right' , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'R' , 'LTRB', 0, 'C', true );
    $pdf->Cell(36, 4, 'Delantera Derecha / Rear Left' , 'LTRB', 0, 'C', true,'',1 );


  //linea 2
  $pdf->Ln();
  $pdf->SetFillColor(255, 255, 255 );
  $pdf->Cell(4, 4, 'M' , 'LTRB', 0, 'C', false );
  $pdf->Cell(36, 4, $row["llanta_delantera_izq"] , 'LTRB', 0, 'C', true,'',1 );
  $pdf->Cell(4, 4, 'M' , 'LTRB', 0, 'C', false );
  $pdf->Cell(36, 4, $row["llanta_trasera_izq"] , 'LTRB', 0, 'C', true,'',1 );
  $pdf->Cell(4, 4, 'M' , 'LTRB', 0, 'C', false );
  $pdf->Cell(36, 4, $row["llanta_repuesto"] , 'LTRB', 0, 'C', true,'',1 );
  $pdf->Cell(4, 4, 'M' , 'LTRB', 0, 'C', false );
  $pdf->Cell(36, 4, $row["llanta_trasera_der"] , 'LTRB', 0, 'C', true,'',1 );
  $pdf->Cell(4, 4, 'M' , 'LTRB', 0, 'C', false );
  $pdf->Cell(36, 4, $row["llanta_delantera_der"] , 'LTRB', 0, 'C', true,'',1 );

    //linea 3
    $pdf->Ln();
    $pdf->SetFillColor(255, 255, 255 );
    $pdf->Cell(4, 4, 'N' , 'LTRB', 0, 'C', false );
    $pdf->Cell(36, 4, $row["llanta_delantera_izq_num"] , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'N' , 'LTRB', 0, 'C', false );
    $pdf->Cell(36, 4, $row["llanta_trasera_izq_num"] , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'N' , 'LTRB', 0, 'C', false );
    $pdf->Cell(36, 4, $row["llanta_repuesto_num"] , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'N' , 'LTRB', 0, 'C', false );
    $pdf->Cell(36, 4, $row["llanta_trasera_der_num"] , 'LTRB', 0, 'C', true,'',1 );
    $pdf->Cell(4, 4, 'N' , 'LTRB', 0, 'C', false );
    $pdf->Cell(36, 4, $row["llanta_delantera_der_num"] , 'LTRB', 0, 'C', true,'',1 );
        
  //linea 4
  $pdf->Ln(8);
  $pdf->SetFillColor(243, 243, 243 );
  $pdf->Cell(50, 4,'Marca Batería / Battery Brand' , 'LTRB', 0, 'L', true,'',1 );
  $pdf->Cell(50, 4,$row["bateria_marca"] , 'LTRB', 0, 'L', false,'',1 );

  $pdf->Cell(50, 4,'Numero Batería / Battery Number' , 'LTRB', 0, 'L', true,'',1 );
  $pdf->Cell(50, 4,$row["bateria_num"] , 'LTRB', 0, 'L', false,'',1 );


  $pdf->Ln(7);

  //***observaciones  trabajo_realizar*/
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0);
  $pdf->SetFont('', '',8);
  $pdf->writeHTMLCell('', '', 7, '', '<strong>Observaciones Adicionales / Final Observations:</strong> '.$row['trabajo_realizar'], 0, 0, 1, false, 'J', true);

  


    //****** firmas */

    $pdf->SetY(260);
    $pdf->SetX(8);
    $pdf->Cell(100, 4, 'Firma Inspector / Inspector Signature: ______________________', '', 0, 'L', true );
    $pdf->SetX(100);
    $pdf->Cell(100, 4, 'Firma Cliente / Client Signature: ______________________', '', 0, 'L', true );             
    
    $imgfirma1 = base64_decode(str_replace('data:image/png;base64,','',($_REQUEST['pdffirma2'])));
    $pdf->Image('@'.$imgfirma1,60,251,28,12);

    $imgfirma2 = base64_decode(str_replace('data:image/png;base64,','',($_REQUEST['pdffirma1'])));
    $pdf->Image('@'.$imgfirma2,145,251,28,12);
   
    $pdf->SetY(264);
    $pdf->SetX(60);
    $pdf->Cell(130, 4, $row['elusuario'], '', 0, 'L', true );
    $pdf->SetX(145);
    $firmanombrecliente=$row['cliente_nombre'];
    if ($row['cliente_contacto']<>'') {$firmanombrecliente=$row['cliente_contacto'];}
    $pdf->Cell(130, 4, $firmanombrecliente, '', 0, 'L', true );
    
 
    //************   PAGINA de FOTOS */
    $result_fotos = sql_select("SELECT inspeccion_foto.id,inspeccion_foto.id_inspeccion,inspeccion_foto.archivo,inspeccion_foto.fecha
    FROM inspeccion_foto
    WHERE inspeccion_foto.id_inspeccion=$cid  
    order by inspeccion_foto.fecha,inspeccion_foto.id");
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

//     <table>
//     <tr>
//      <td>
//        <img src="x.png">
//      </td>
//      <td>
//        <img src="x.png">
//      </td>
//      <td>
//        <img src="x.png">
//      </td>
//      <td>
//        <img src="x.png">
//      </td>
//    </tr>
//   </table>

 

// reset pointer to the last page
//$pdf->lastPage();



//$pdf->writeHTML($html, true, false, true, false, '');//$style.

ob_end_clean();

if (isset($guardar_archivo)) {
    //$pdf->Output(app_dir.'reportes/'.'inspeccion_'. $numero .'.pdf', 'F');    
    $pdf->Output($guardar_archivo, 'F');
} else { 
    $pdf->Output('Inspeccion_'.$numero.'.pdf', 'I'); //D = descargar
}

?>