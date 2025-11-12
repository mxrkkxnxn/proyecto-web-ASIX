<?php
session_start();

// CSRF simple
if ($_POST && (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    die("CSRF inválido.");
}

// Conexión directa
$pdo = new PDO(
    "mysql:host=db;dbname=ecomerce;charset=utf8mb4",
    "admin", "Admin123!",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$servicio_id = $_POST['servicio_id'] ?? null;
$metodo = $_POST['metodo'] ?? 'paypal';

if (!$servicio_id) die("Servicio no especificado.");

// Obtener servicio
$stmt = $pdo->prepare("SELECT nombre, precio FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch();
if (!$servicio) die("Servicio no encontrado.");

// ✅ FPDF (ruta correcta)
require_once __DIR__ . '/fpdf/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'FACTURA', 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, "Cliente: " . ($_SESSION['user_name'] ?? 'Cliente'), 0, 1);
$pdf->Cell(0, 6, "Fecha: " . date('d/m/Y'), 0, 1);
$pdf->Cell(0, 6, "Servicio: " . $servicio['nombre'], 0, 1);
$pdf->Cell(0, 6, "Método: " . $metodo, 0, 1);
$pdf->Cell(0, 6, "Total: EUR " . number_format($servicio['precio'], 2, ',', '.'), 0, 1);

// Guardar factura
$factura_id = 'FAC-' . date('Ymd') . '-' . bin2hex(random_bytes(4));
$filename = "factura_{$factura_id}.pdf";
$path = __DIR__ . "/factura/$filename";

if (!is_dir(__DIR__ . '/factura')) {
    mkdir(__DIR__ . '/factura', 0755, true);
    file_put_contents(__DIR__ . '/factura/.htaccess', "Deny from all");
}

$temp_file = tempnam(sys_get_temp_dir(), 'pdf_');
$pdf->Output('F', $temp_file);
rename($temp_file, $path);

// ✅ CORREGIDO: usar ID del usuario logueado
$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $pdo->prepare("
    INSERT INTO pedidos (id_cliente, total, metodo, estado, id_transaccion)
    VALUES (?, ?, ?, 'completado', ?)
");
$stmt->execute([$user_id, $servicio['precio'], $metodo, $factura_id]);
$pedido_id = $pdo->lastInsertId();

header("Location: dashboard/cliente.php?compra=ok&pedido=$pedido_id");
exit;
?>