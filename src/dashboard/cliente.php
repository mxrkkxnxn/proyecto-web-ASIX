<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../db.php';

// Obtener pedidos del cliente (simulado para demo)
$pedidos = [];
try {
    $stmt = $pdo->query("
        SELECT p.id, p.total, p.metodo, p.creado, s.nombre AS servicio
        FROM pedidos p
        JOIN servicios s ON p.servicio_id = s.id
        ORDER BY p.creado DESC
    ");
    $pedidos = $stmt->fetchAll();
} catch (Exception $e) {
    // Ignorar si tabla no existe aún
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta - eComerce</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>📦 Mi Cuenta</h1>
        <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Cliente') ?></strong></p>
    </header>

    <nav>
        <a href="../index.php">Servicios</a> |
        <a href="../logout.php">Salir</a>
    </nav>

    <?php if (isset($_GET['compra']) && $_GET['compra'] === 'ok'): ?>
        <div class="mensaje success">
            ✅ ¡Compra realizada! Pedido #<?= htmlspecialchars($_GET['pedido']) ?>
            <br>
            <a href="../factura.php?pedido=<?= $_GET['pedido'] ?>">📄 Ver factura</a>
        </div>
    <?php endif; ?>

    <h2>Historial de Pedidos</h2>

    <?php if (empty($pedidos)): ?>
        <p>Aún no tienes pedidos.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Servicio</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['servicio']) ?></td>
                        <td>€<?= number_format($p['total'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($p['creado'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <footer>
        <p><a href="../index.php">← Seguir comprando</a></p>
    </footer>
</body>
</html>
