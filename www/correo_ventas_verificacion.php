<?php
require_once ('include/framework.php');

//if(1==1)
if (app_enviar_email==true) 
{
    require_once ('include/correo.php');

    $email_enviar = "";
    $email_enviar_solicitante = "";

    $correo_servicio_result = sql_select("SELECT ventas.* 
        ,producto.codigo_alterno,producto.nombre,producto.placa
        ,ventas_estado.nombre AS elestado        
        ,l1.nombre AS vendedor        
        ,tienda.nombre AS latienda
        ,tienda.correo_ventas_carshop AS correos
        FROM ventas
        LEFT OUTER JOIN producto ON (ventas.id_producto=producto.id)
        LEFT OUTER JOIN ventas_estado ON (ventas.id_estado=ventas_estado.id)
        LEFT OUTER JOIN usuario l1 ON (ventas.id_usuario=l1.id)
        LEFT OUTER JOIN tienda  ON (ventas.id_tienda=tienda.id)       
        WHERE ventas.id=$cid LIMIT 1");

    if ($correo_servicio_result != false) {
        if ($correo_servicio_result->num_rows > 0) { 
            $correo_row = $correo_servicio_result->fetch_assoc(); 
        }
    } 
    
    $email_enviar = 'admon.sps@inglosa.hn, customerservice@inglosa.hn, mercadeo@inglosa.hn';
    $subject = 'Alerta : Vehículo Disponible para Venta - Sin Fotos'; 
    
       $cuerpo_html = "
       <img src='data:image/jpeg;base64,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XZAym9SmD1VSBmSbwSrdcSCBRY0QYtBXMvXoA2RQJuCho7vOCbTNO/8A3tBPIkxxhOasrOTlnXEa32p2lZVCAkE2Fs0yvZSKBeYnGlQiLgTiMMPhQDwLEMWJO720nPgcTU3cXSibk+OSJzhfoKgRe7Kp45oNagNezvHAgCzAdNS03jJPZYz816llfpvCYxpoN+f9xf/aAAwDAQACAAMAAAAQ88888888888888888888888888888888888888888888888888883jQ28888888889d6w+8+w80648cFsB8CTV5nGS+d4fKhjxlhxx7z88888888888888888888888888888888888888888888888888888888888888888//EACYRAQABAwMDAwUAAAAAAAAAAAERACExQWGhEDBRIIHBUGBxsdH/2gAIAQMBAT8Q+qQMFcDL8AarAVJjekOROxQDGzkQ5Mx7NARJHtBHghqxg2DXddWiku4JMNsPclqLYvIPgqwIjAlszhQbdqOEYyuGwQw3kaZ8Rk05uJ/P8pTokeLc05jkyZsW13n069XoMQu530/Vqa2V2J5mOadI2zdPgkWDYV4g2cBYPsv/xAAnEQEAAgECBAUFAAAAAAAAAAABABEhMVEQQWFxIIGR0eEwUGCx8P/aAAgBAgEBPxD7paN0NV0PnoZjdHTUe7FmQdLz7RFU6/SB4dR2693+0i76Fg9dXyxLue2Af2ykr3ijTepXCnwVxEW699rs5vlM0VyBgIg3NJQAKM1vwqc46TlDSawxEqKALgmvo7vxcKUu51g7XlfSIky8bZaS+Fv4J//EACUQAQEAAgICAgICAwEAAAAAAAERACExQVFhcYEQkUCxcKHw0f/aAAgBAQABPxD/ACUcXhQYerr6Mqq1Ks/ZGf0l2mAH+K4n5QIqq8Ad4sd0hPPt+zl+mHmjuD8qmz3Txgp1mWIeQ/RT1juj0o+6H2DCmikFOVAB7fSdjsQnQP8AT0jsRH+GQ/Wfat/ZK9ezj1wrTlJ1M9vL5Grroz1Jf+DwpcALneA8sNHtx8B3w34H6KZMo+kftOa9zXQ1opByeQfN/hFV54aAfPIDkA5GDWGbasNOdVnoNBi1Sbx6J2ezhS8GLNb/AHhOFe7fvOdgi+kKD6MXKXKq4TuKEUbEHZ6xW0jHaAKai38ZeBRg891SrvC88ypSpp2LGxj2OAYosxhpTUeFwAILPYF7XeISURRyNCdgs7xQ8clHiwz2gHbgh2mW2QwKDkcPAgByVoLNrv8AC9BF0XIRTs2OzIokDU8X7sAPOFZeBWKAaRN3Ey4LYcgf8qPeNqSW74dW9E+vwaEIuFIb4AXKOPwqBO6wjo2Awd+2LZCbX1q+QptagCRid/rKAXjUOs5ge0b0rfkPpggydoP2sAhy7JEQcJguBegI+QH8MX9iwhAtxSJ2NcOy5OzntDoNE6RMrlu0YmulvxPuYbySyObIUhhZI7ugpLPo7FWmtE8Oc3YCG4Sak/Ac2ZnJYW6YAeTc2YP8eMMkhKQoJTbpRQP+lVI0BjG5b08wiSyO+kQHC+WFYUgkaHIZsJDk7WsBeHgqgaIxiTHMV0MAJHsfnGO3kCkYi+zekYZGN8EV0Qp6R7y0g4y3FvYieQ5SUqBBI8PeUAigcEoT5H/HAaPydgmptFVPEj5HnFuiB7h+jPaHeAEAAAOvxMZ3PYg58i4HYvcS710Xb94VvCCPctQwtsH6+wOGQ1GLSfyAZwjZStkmtmihLodd5/1vGStB4VzTRzO/67CcESdqIB8LQbIXkxHw2FyAcoUVwF9YKjJUaouotfHsxstqkL6HV6ZndPiNlQxQ3TyhshsWFGYEDlD1OZpDhCkKZvSzdfOHLb16Mhe0qdZFpdfd5PANDHsex/JTwsavQcp0G3F7rMaUbBy80eBuVAKKc/YX5MfYDqqRhIpo9C2j9YXhc1VopLYDjF1PAEshpbrNzIauy96pq9p2RE0AtRzGOgb6SmNof1nxD0ofbzgbsm/xIF+Ujw4osqzRJQ5U6PAEPwAXrBaABITQbnGGpntCpPFo8uHsCbTnQH5SPGHzyEJSL0h5DAkJl3ovxZI/dC+DJGrwzbIgIY2m1KIaAOenhPgeR4wmxqpAMaoHM4lEJTf0z8E0Q3gL3pT1T5MWyfQDzyPrBdNaDT/may2/QK7ku+kGPnCRKe3OfOrzua/H7QfUOniPcxViOX4jwWDYBpxF5wsNglnGcoJILeU4iiKQwA8YtgDaBamnn2oDUpgSaVH0Zyei9MQahSLGs1EO0Lg2NkqreDw1JQSg0w43mhEEoEInYb2DVkAFtTsOjHneEAHJ0lUCl5N5R1lJRLq5kCkjHDXGD6BMQEo0tPxUmQb8CCaGtEBrR1XBURctkFaOZRVVwa61u0Qbjm0h54/jPpiqTiBt0bcDjpUmOHRitHRQ8Ff5h//Z' />
        <div style='font-family: Arial, sans-serif; font-size:14px; color:#333;'>
            <h2 style='color:#b22222;'>Notificación: Vehículo disponible para la venta sin fotos</h2>

            <p><b>Tienda:</b> ".$correo_row["latienda"]."</p>

            <p><b>Fecha Completada:</b> ".formato_fecha_de_mysql($correo_row["fecha_reparacion_completada"])."</p>

            <p><b>Vendedor:</b> ".$correo_row["vendedor"]."</p>

            <p>
                <b>Vehículo:</b><br>
                ".$correo_row["codigo_alterno"]." ".$correo_row["nombre"]."<br>
                <b>Placa:</b> ".$correo_row["placa"]."
            </p>

            <p><b>Estado:</b> ".$correo_row["elestado"]."</p>

            <p><b>Observaciones:</b> ".$correo_row["observaciones"]."</p>

            <div style='margin-top:20px; padding:15px; background:#ffe5e5; border-left:4px solid red;'>
                <b style='color:red;'>ALERTA:</b> Este vehículo está marcado como disponible para la venta pero 
                <span style='color:red; font-weight:bold;'>NO TIENE FOTOS</span> asociadas.
            </div>
        </div>
";       
   
    $cuerpo_sinhtml = strip_tags($cuerpo_html);    

    if ($email_enviar != '') {
       // $debug_file = 'C:/DEV-git/php/sql_debug.log';  
        //file_put_contents($debug_file, date('Y-m-d H:i:s') . " - " . $cuerpo_html . "\n", FILE_APPEND);
        enviar_correo($email_enviar, $subject, $cuerpo_html, $cuerpo_sinhtml);
    }    
}
?>