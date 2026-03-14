<?php
error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
if (defined('SAFI_CRON_CONTEXT') && SAFI_CRON_CONTEXT === true) {
    require_once('include/framework_cron.php');
    if (!isset($guardar_archivo));
} else {
    require_once('include/framework.php');
    if (!isset($guardar_archivo)) { pagina_permiso(22);}
}
*/

if (defined('SAFI_CRON_CONTEXT') && SAFI_CRON_CONTEXT === true) {
    require_once('include/framework_cron.php');
} elseif (php_sapi_name() === 'cli') {
    // Ejecutando desde linea de comandos o segundo plano
    chdir('/var/www/html');
    require_once('include/framework_cron.php');
    parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);
    $elcodigo = intval($_REQUEST['pdfcod']);
    $guardar_archivo = '/var/www/html/reportes/Inspeccion_' . $elcodigo . '.pdf';
} else {
    require_once('include/framework.php');
    if (!isset($guardar_archivo)) { pagina_permiso(22); }
}



// require_once('include/tcpdf/config/lang/spa.php');
require_once('include/tcpdf/tcpdf.php');  
 

$contenido="" ;
$fecha_hora_pdf_footer = '';


$style="<style>
table {
 

   padding-left: 10px;

  
} 

.table-bordered {
padding-left: 10px;
  
} 
</style> ";

if (!function_exists('get_base64_png_from_request')) {
    function get_base64_png_from_request($key) {
        if (!isset($_REQUEST[$key])) {
            return "";
        }
        $data_url = trim((string)$_REQUEST[$key]);
        if ($data_url === "" || strpos($data_url, "data:image/png;base64,") !== 0) {
            return "";
        }
        $raw = substr($data_url, strlen("data:image/png;base64,"));
        if ($raw === "") {
            return "";
        }
        $decoded = base64_decode($raw, true);
        if ($decoded === false || strlen($decoded) < 64) {
            return "";
        }
        return $decoded;
    }
}

if (!function_exists('formato_fecha_pdf_seguro')) {
    function formato_fecha_pdf_seguro($fecha) {
        if (es_nulo($fecha)) {
            return '';
        }
        $d = date_create($fecha);
        if ($d === false) {
            return '';
        }
        return date_format($d, 'd/m/Y');
    }
}


if (isset($guardar_archivo)) {
    $cid = $elcodigo ;
} else {
    if (isset($_REQUEST['pdfcod'])) { $cid = intval($_REQUEST['pdfcod']) ; } else	{exit ;}
}

 
if (!es_nulo($cid)) {
	
	$sql = "SELECT inspeccion.* 
    ,entidad.nombre AS cliente_nombre
    ,entidad.email AS cliente_email_entidad
    ,entidad.codigo_alterno AS cliente_codigo_alterno
    ,producto.nombre AS producto_nombre
    ,producto.codigo_alterno as producto_alterno
    ,usuario.nombre as elusuario
    ,producto.placa as producto_placa
    ,producto.marca as producto_marca
    ,producto.color as producto_color
    ,producto.modelo as producto_modelo
    ,producto.tipo_vehiculo as producto_tipo_vehiculo
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
        $fecha_ref_pdf = !es_nulo($row['fecha_entrada']) ? $row['fecha_entrada'] : $row['fecha'];
        $hora_ref_pdf = !es_nulo($row['hora_entrada']) ? $row['hora_entrada'] : $row['hora'];
        $fecha_hora_pdf_footer = trim(formato_fecha_pdf_seguro($fecha_ref_pdf).' '.formato_solohora_de_mysql($hora_ref_pdf));

        // En contexto cron no existe POST del navegador; reutilizar la funcion
        // get_base64_png_from_request() precargando $_REQUEST con datos guardados.
        if (!isset($_REQUEST['pdfimg1']) && isset($row['detalles_canvas'])) {
            $tmp = trim((string)$row['detalles_canvas']);
            if (strpos($tmp, 'data:image/png;base64,') === 0) {
                $_REQUEST['pdfimg1'] = $tmp;
            } elseif (preg_match('/data:image\/png;base64,[A-Za-z0-9+\/=]+/', $tmp, $m)) {
                $_REQUEST['pdfimg1'] = $m[0];
            }
        }
        if (!isset($_REQUEST['pdffirma1']) && isset($row['firma1_canvas'])) {
            $tmp = trim((string)$row['firma1_canvas']);
            if (strpos($tmp, 'data:image/png;base64,') === 0) {
                $_REQUEST['pdffirma1'] = $tmp;
            } elseif (preg_match('/data:image\/png;base64,[A-Za-z0-9+\/=]+/', $tmp, $m)) {
                $_REQUEST['pdffirma1'] = $m[0];
            }
        }
        if (!isset($_REQUEST['pdffirma2']) && isset($row['firma2_canvas'])) {
            $tmp = trim((string)$row['firma2_canvas']);
            if (strpos($tmp, 'data:image/png;base64,') === 0) {
                $_REQUEST['pdffirma2'] = $tmp;
            } elseif (preg_match('/data:image\/png;base64,[A-Za-z0-9+\/=]+/', $tmp, $m)) {
                $_REQUEST['pdffirma2'] = $m[0];
            }
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
                if ($empresa==3) { $image_file = 'img/thrifty.jpg';}
                if ($empresa==4) { $image_file = 'img/carshop.jpg';}
                 
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
               global $modificada, $fecha_hora_pdf_footer;
                
                              
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
                $this->Cell(0, 10, '         '.$fecha_hora_pdf_footer, 0, false, 'R', 0, '', 0, false, 'T', 'M');
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
    $right_column = '<b>Datos del Vehiculo / Vehicle Details:</b> <br>'.$row['producto_nombre'].'<br><b>No. </b>'.$row['producto_alterno'].'<b> Placa </b>'.$row['producto_placa'];
        
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
        $fecha_pdf = !es_nulo($row["fecha_entrada"]) ? $row["fecha_entrada"] : $row["fecha"];
        $hora_pdf = !es_nulo($row["hora_entrada"]) ? $row["hora_entrada"] : $row["hora"];
        $pdf->Cell(47, 4, formato_numero($row["kilometraje_entrada"],0) , 'LTRB', 0, 'C', false );
        $pdf->Cell(50, 4, formato_fecha_pdf_seguro($fecha_pdf) , 'LTRB', 0, 'C', false );
        $pdf->Cell(50, 4, formato_solohora_de_mysql($hora_pdf) , 'LTRB', 0, 'C', false );
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
  
    $imgdata = get_base64_png_from_request('pdfimg1');
    if ($imgdata !== "") {
        $pdf->Image('@'.$imgdata,113,$detallespos,100,125);
    }
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
    
    $imgfirma1 = get_base64_png_from_request('pdffirma2');
    if ($imgfirma1 !== "") {
        $pdf->Image('@'.$imgfirma1,60,251,28,12);
    }

    $imgfirma2 = get_base64_png_from_request('pdffirma1');
    if ($imgfirma2 !== "") {
        $pdf->Image('@'.$imgfirma2,145,251,28,12);
    }
   
    $pdf->SetY(264);
    $pdf->SetX(60);
    $pdf->Cell(130, 4, $row['elusuario'], '', 0, 'L', true );
    $pdf->SetX(145);
    $firmanombrecliente=$row['cliente_nombre'];
    if ($row['cliente_contacto']<>'') {$firmanombrecliente=$row['cliente_contacto'];}
    $pdf->Cell(130, 4, $firmanombrecliente, '', 0, 'L', true );
    
 
    $PerfilVendedor="";
    $PerfilVendedor=get_dato_sql("usuario","grupo_id"," WHERE id=".$_SESSION["usuario_id"]);
    
    if ($PerfilVendedor<>7){
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

//************   PAGINA ACTA DE RECEPCION (AL FINAL) */
$cliente_codigo_alterno_pdf = strtoupper(trim((string)$row['cliente_codigo_alterno']));
$aplica_actarv_pdf = (
    intval($row['tipo_inspeccion']) === 1
    && intval($row['tipo_doc']) === 2
    && (strpos($cliente_codigo_alterno_pdf, 'CCO') === 0)
);

if ($aplica_actarv_pdf) {
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();

    $pdf->SetTextColor(0, 0, 0);

    $header_x = 10;
    $header_y = $pdf->GetY();
    $header_w = 190;
    $col_logo = 42;
    $col_right = 42;
    $col_center = $header_w - $col_logo - $col_right;
    $row_top_h = 18;
    $row_bottom_h = 8;

    $pdf->SetLineWidth(0.2);
    $pdf->Rect($header_x, $header_y, $col_logo, $row_top_h);
    $pdf->Rect($header_x + $col_logo, $header_y, $col_center, $row_top_h);
    $pdf->Rect($header_x + $col_logo + $col_center, $header_y, $col_right, $row_top_h);
    $pdf->Rect($header_x, $header_y + $row_top_h, $header_w, $row_bottom_h);

    $logo_acta = 'img/inglosa.jpg';    
    if (file_exists($logo_acta)) {
        $pdf->Image($logo_acta, $header_x + 2, $header_y + 2, $col_logo - 4, $row_top_h - 4, '', '', '', false, 300, '', false, false, 0, false, false, false);
    }

    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetXY($header_x + $col_logo, $header_y + 6);
    $pdf->Cell($col_center, 6, 'INVERSIONES GLOBALES S.A.', 0, 0, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY($header_x + $col_logo + $col_center + 2, $header_y + 2);
    $pdf->Cell($col_right - 4, 4, 'RE-VE-17', 0, 1, 'L');
    $pdf->SetX($header_x + $col_logo + $col_center + 2);
    $pdf->Cell($col_right - 4, 4, 'Ver. 02', 0, 1, 'L');
    $pdf->SetX($header_x + $col_logo + $col_center + 2);
    $pdf->Cell($col_right - 4, 4, 'Fecha: 21/07/2025', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetXY($header_x, $header_y + $row_top_h);
    $pdf->Cell($header_w, $row_bottom_h, 'ACTA DE RECEPCION DE VEHICULO', 0, 1, 'C');

    $pdf->SetY($header_y + $row_top_h + $row_bottom_h + 4);
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 7, 'ACTA DE RECEPCION DE VEHICULO', 0, 1, 'C');
    $pdf->Ln(6);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, 'POR ESTE MEDIO DOY FE DE RECIBIR DE LA EMPRESA INVERSIONES GLOBALES S.A. EN CALIDAD DE ARRENDAMIENTO, EL VEHICULO CON SIGUIENTES CARACTERISTICAS:', 0, 'J', false, 1);
    $pdf->Ln(6);  
  
    $pdf->SetFillColor(243, 243, 243);
    $pdf->SetFont('helvetica', 'B', 10);
    $w_marca = 43;
    $w_tipo = 43;
    $w_color = 34;
    $w_placa = 34;
    $w_registro = 36;

    $pdf->Cell($w_marca, 7, 'MARCA', 1, 0, 'C', true);
    $pdf->Cell($w_tipo, 7, 'TIPO', 1, 0, 'C', true);
    $pdf->Cell($w_color, 7, 'COLOR', 1, 0, 'C', true);
    $pdf->Cell($w_placa, 7, 'PLACA', 1, 0, 'C', true);
    $pdf->Cell($w_registro, 7, 'REGISTRO #', 1, 1, 'C', true);  

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($w_marca, 9, trim((string)$row['producto_marca']), 1, 0, 'C', false);
    $pdf->Cell($w_tipo, 9, trim((string)$row['producto_tipo_vehiculo']), 1, 0, 'C', false);
    $pdf->Cell($w_color, 9, trim((string)$row['producto_color']), 1, 0, 'C', false);
    $pdf->Cell($w_placa, 9, trim((string)$row['producto_placa']), 1, 0, 'C', false);
    $pdf->Cell($w_registro, 9, trim((string)$row['producto_alterno']), 1, 1, 'C', false);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(50, 6, 'Asignado Depto', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(140, 6, trim((string)$row['actarv_asignado_depto']), 1, 1, 'L', false);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(50, 6, 'Jefe Depto', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(140, 6, trim((string)$row['actarv_jefe_depto']), 1, 1, 'L', false);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(50, 6, 'Celular', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(140, 6, trim((string)$row['actarv_celular']), 1, 1, 'L', false);
    $pdf->Ln(2);

    $ruta_foto_licencia = trim((string)$row['actarv_foto_licencia']);
    $foto_licencia_path = '';
    if ($ruta_foto_licencia !== '') {
        $candidatos_foto = array(
            app_dir.'uploa_d/'.$ruta_foto_licencia,
            app_dir.$ruta_foto_licencia,
            'uploa_d/'.$ruta_foto_licencia,
            $ruta_foto_licencia
        );
        foreach ($candidatos_foto as $candidato) {
            if (file_exists($candidato)) {
                $foto_licencia_path = $candidato;
                break;
            }
        }
    }

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 6, 'Foto de Licencia', 1, 1, 'L', true);
    $y_foto_inicio = $pdf->GetY();
    if ($foto_licencia_path !== '') {
        $pdf->Image($foto_licencia_path, 12, $y_foto_inicio + 2, 95, 65, '', '', '', false, 300, '', false, false, 0, true, false, false);
        $pdf->SetY($y_foto_inicio + 70);
    } else {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 10, 'No se adjunto foto de licencia.', 1, 1, 'L', false);
    }

    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetX(12);
    $alto_firma = 16;
    $ancho_label_firma = 40;
    $ancho_linea_firma = 130;
    $pdf->Cell($ancho_label_firma, $alto_firma, 'Firma del Cliente:', 0, 0, 'L', false);
    $x_linea_firma = $pdf->GetX();
    $y_linea_firma = $pdf->GetY();
    $ancho_raya_firma = $ancho_linea_firma / 2;
    $x_raya_firma = $x_linea_firma + (($ancho_linea_firma - $ancho_raya_firma) / 2);
    $pdf->Line($x_raya_firma, $y_linea_firma + $alto_firma - 1.5, $x_raya_firma + $ancho_raya_firma, $y_linea_firma + $alto_firma - 1.5);

    $imgfirma_cliente_acta = get_base64_png_from_request('pdffirma1');
    if ($imgfirma_cliente_acta !== '') {
        $pdf->Image('@'.$imgfirma_cliente_acta, $x_linea_firma + 1, $y_linea_firma + 1, $ancho_linea_firma - 2, $alto_firma - 2, '', '', '', false, 300, '', false, false, 0, true, false, false);
    }
    $pdf->Ln($alto_firma + 3);

}

 

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
