<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado.");
}

require_once 'fpdf/fpdf.php';
require_once 'db.php';

$pedido_id = $_GET['pedido'] ?? null;
if (!$pedido_id) {
    die("Pedido no especificado.");
}

$stmt = $pdo->prepare("
    SELECT p.id, p.total, s.nombre, s.descripcion
    FROM pedidos p
    JOIN servicios s ON p.servicio_id = s.id
    WHERE p.id = ?
");
$stmt->execute([$pedido_id]);
$factura = $stmt->fetch();

if (!$factura) {
    die("Pedido no encontrado.");
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Cabecera
$pdf->Cell(0, 10, 'FACTURA', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, "Número: #{$factura['id']}", 0, 1);
$pdf->Cell(0, 6, "Fecha: " . date('d/m/Y'), 0, 1);
$pdf->Ln(10);

// Cliente
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'Cliente', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, $_SESSION['user_name'] ?? 'Cliente', 0, 1);
$pdf->Ln(10);

// Servicio
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(120, 8, 'Servicio', 1);
$pdf->Cell(30, 8, 'Precio', 1, 1, 'R');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(120, 6, $factura['nombre'], 1);
$pdf->Cell(30, 6, '€' . number_format($factura['total'], 2, ',', '.'), 1, 1, 'R');

// Total
$pdf->Ln(5);
$pdf->Cell(120, 8, 'TOTAL', 1);
$pdf->Cell(30, 8, '€' . number_format($factura['total'], 2, ',', '.'), 1, 1, 'R');

// Pie
$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 6, 'Documento generado automáticamente. Sin validez fiscal.', 0, 1, 'C');

$pdf->Output();
?>
