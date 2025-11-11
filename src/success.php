<?php
session_start();

// CSRF
if ($_POST && (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    die("CSRF inválido.");
}

$pdo = new PDO(
    "mysql:host=db;dbname=ecomerce;charset=utf8mb4",
    "admin", "Admin123!",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$servicio_id = $_POST['servicio_id'] ?? null;
$metodo = $_POST['metodo'] ?? 'paypal';

if (!$servicio_id) die("Servicio no especificado.");

$stmt = $pdo->prepare("SELECT nombre, descripcion, precio FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch();
if (!$servicio) die("Servicio no encontrado.");

require_once __DIR__ . '/fpdf/fpdf.php';

// ✅ Clase FPDF mejorada para UTF-8
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'FACTURA', 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'eComerce - Servicios Profesionales', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Documento generado automáticamente. Sin validez fiscal.', 0, 0, 'C');
    }

    // ✅ Conversión UTF-8 → ISO-8859-1 para FPDF
    function utf8encode($str) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Datos del cliente
$pdf->Cell(0, 6, 'Cliente: ' . $pdf->utf8encode($_SESSION['user_name'] ?? 'Cliente'), 0, 1);
$pdf->Cell(0, 6, 'Fecha: ' . date('d/m/Y'), 0, 1);
$pdf->Ln(10);

// Tabla de servicios
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(120, 8, $pdf->utf8encode('Concepto'), 1);
$pdf->Cell(30, 8, $pdf->utf8encode('Importe'), 1, 1, 'R');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(120, 6, $pdf->utf8encode($servicio['nombre']), 1);
$pdf->Cell(30, 6, chr(128) . ' ' . number_format($servicio['precio'], 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(120, 8, $pdf->utf8encode('TOTAL'), 1);
$pdf->Cell(30, 8, chr(128) . ' ' . number_format($servicio['precio'], 2, ',', '.'), 1, 1, 'R');

// Guardar
$factura_id = 'FAC-' . date('Ymd') . '-' . bin2hex(random_bytes(4));
$filename = "factura_{$factura_id}.pdf";
$path = __DIR__ . "/factura/$filename";

if (!is_dir(__DIR__ . '/factura')) {
    mkdir(__DIR__ . '/factura', 0755, true);
    file_put_contents(__DIR__ . '/factura/.htaccess', "Deny from all");
}

// ✅ Usar /tmp para evitar problemas de permisos
$temp_file = tempnam(sys_get_temp_dir(), 'pdf_');
$pdf->Output('F', $temp_file);
rename($temp_file, $path);

// Insertar pedido
$stmt = $pdo->prepare("
    INSERT INTO pedidos (id_cliente, total, metodo, estado, id_transaccion)
    VALUES (1, ?, ?, 'completado', ?)
");
$stmt->execute([$servicio['precio'], $metodo, $factura_id]);
$pedido_id = $pdo->lastInsertId();

header("Location: dashboard/cliente.php?compra=ok&pedido=$pedido_id");
exit;