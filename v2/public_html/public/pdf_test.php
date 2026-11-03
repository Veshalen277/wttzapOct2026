<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Try the most common autoload locations from /public/
$paths = [
  __DIR__ . '/../vendor/autoload.php',
  __DIR__ . '/vendor/autoload.php',
  __DIR__ . '/../../vendor/autoload.php',
];

$autoload = null;
foreach ($paths as $p) {
  if (file_exists($p)) { $autoload = $p; break; }
}

if (!$autoload) {
  http_response_code(500);
  echo "autoload.php NOT FOUND. Tried:\n\n" . implode("\n", $paths);
  exit;
}

require_once $autoload;

if (!class_exists(\Dompdf\Dompdf::class)) {
  http_response_code(500);
  echo "autoload found at:\n$autoload\n\nBUT Dompdf class missing.\nRun composer require dompdf/dompdf in the correct project root.";
  exit;
}

$html = '<h1>PDF Works</h1><p>If you can read this, Dompdf is working on this server.</p>';

$dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="pdf_test.pdf"');

echo $dompdf->output();
exit;
