<?php

require 'vendor/autoload.php';

$html = file_get_contents("pdfsrc.html");

try {
    ob_start();
    $html2pdf = new HTML2PDF('L','A4','en', true, 'UTF-8', array(20,5,5,8));
    //$html2pdf->setModeDebug();
    $html2pdf->WriteHTML($html);
    $html2pdf->Output("report.pdf", "D");
    ob_end_flush();
}
catch(HTML2PDF_exception $e) {
    echo $e;
    exit;
}

//$file = 'report.pdf';
//
//if (file_exists($file)) {
//    
//    header("Content-Type: application/pdf");
//    header("Content-Disposition: attachment; filename=\"$file\"");
//    header("Expires: -1");
//    header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
//    header("Pragma: public");
//    header("Content-Length: " . filesize($file));
//    
//    readfile($file);
//    exit;
//}
