<?php
/**
 * HTML → PDF using Mpdf (already vendored by OpenEMR).
 *
 * Usage: php docs/_html_to_pdf.php <input.html> <output.pdf>
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php _html_to_pdf.php input.html output.pdf\n");
    exit(1);
}

$inputHtml  = $argv[1];
$outputPdf  = $argv[2];

if (!is_file($inputHtml)) {
    fwrite(STDERR, "Input not found: $inputHtml\n");
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

$html = file_get_contents($inputHtml);

$mpdf = new \Mpdf\Mpdf([
    'mode'           => 'utf-8',
    'format'         => 'A4',
    'margin_left'    => 16,
    'margin_right'   => 16,
    'margin_top'     => 18,
    'margin_bottom'  => 18,
    'margin_header'  => 6,
    'margin_footer'  => 8,
    'default_font'   => 'dejavusans',
    'tempDir'        => sys_get_temp_dir(),
]);

$mpdf->SetTitle('OpenCMS Tab Reference');
$mpdf->SetAuthor('OpenCMS');
$mpdf->SetCreator('Matrix Bricks');

$mpdf->SetHTMLFooter('<div style="text-align:center; font-size:8pt; color:#888;">OpenCMS — Confidential — Page {PAGENO} of {nbpg}</div>');

$mpdf->WriteHTML($html);
$mpdf->Output($outputPdf, \Mpdf\Output\Destination::FILE);

echo "PDF: $outputPdf\n";
