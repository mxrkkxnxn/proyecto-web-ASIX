<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Acceso denegado.");
}

$file = $_GET['file'] ?? '';
if (!preg_match('/^FAC-[0-9]{8}-[a-f0-9]{8}$/', $file)) {
    die("Archivo no válido.");
}

$filename = "factura_{$file}.pdf";
$path = __DIR__ . "/factura/$filename";

if (!file_exists($path)) {
    die("Factura no encontrada.");
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
readfile($path);