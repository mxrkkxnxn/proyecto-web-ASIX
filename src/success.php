<?php
require_once 'db.php';

$servicio_id = $_POST['servicio_id'] ?? null;
$metodo = $_POST['metodo'] ?? 'paypal';

if (!$servicio_id) {
    die("Error: servicio no especificado.");
}

try {
    // Obtener servicio
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
    $stmt->execute([$servicio_id]);
    $servicio = $stmt->fetch();
    if (!$servicio) die("Servicio no encontrado.");

    // Insertar pedido
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (servicio_id, total, metodo, estado)
        VALUES (?, ?, ?, 'completado')
    ");
    $stmt->execute([$servicio_id, $servicio['precio'], $metodo]);
    $pedido_id = $pdo->lastInsertId();

    // Redirigir al dashboard con el ID del pedido
    header("Location: dashboard/cliente.php?compra=ok&pedido=$pedido_id");
    exit;

} catch (Exception $e) {
    die("<h2>❌ Error al procesar la compra</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}
