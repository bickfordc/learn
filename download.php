<?php

require 'vendor/autoload.php';

use mikehaertl\wkhtmlto\Pdf;

$isWindows = false;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $isWindows = true;
} 

if ($isWindows) {
    
    $pdf = new Pdf([
        'commandOptions' => [
            'useExec' => true
        ],
    ]);
    
    $pdf->binary = 'c:\program files\wkhtmltopdf\bin\wkhtmltopdf';
    $pdf->addPage('C:\Bitnami\wappstack-5.6.24-0\apache2\htdocs\boosters\pdfsrc.html');
    
    $options = array(
        'orientation' => 'landscape',
        'disable-smart-shrinking',  
    );
    //'user-style-sheet' => 'css/rebateReport.css',
    
    $pdf->setOptions($options);
    $pdf->send('Boosters rebate report.pdf');
} 
else {
    $pdf = new Pdf();
    $pdf->addPage('pdfsrc.html');
    
    $options = array(
        'orientation' => 'landscape',
        'disable-smart-shrinking',
//        'user-style-sheet' => 'css/rebateReport.css',
    );
    
    $pdf->setOptions($options);
    $pdf->send('Boosters rebate report.pdf');
}

//$html = file_get_contents("pdfsrc.html");
//$command = "wkhtmltopdf " . $infile . " " . $outfile;
//$command = "dir";
//$result = system($command, $retval);
//$result = exec($command, $retval);
//$output = array();
//$result = exec("wkhtmltopdf pdfsrc.html report.pdf  2>&1", $output, $retval);
//passthru('c:/program files/wkhtmltopdf/bin/wkhtmltopdf -V');
//if (!$result) {
//    echo "Failed to convert html to pdf. wkhtmltopdf returned " . $retval . "<br>";
//    echo $result;
//}

//try {
//    ob_start();
//    $html2pdf = new HTML2PDF('L','A4','en', true, 'UTF-8', array(20,5,5,8));
//    //$html2pdf->setModeDebug();
//    $html2pdf->WriteHTML($html);
//    $html2pdf->Output("report.pdf", "D");
//    ob_end_flush();
//}
//catch(HTML2PDF_exception $e) {
//    echo $e;
//    exit;
//}

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
