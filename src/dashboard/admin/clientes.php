<?php
session_start();
if (!isset($_SESSION['user_id']) || !($_SESSION['es_admin'] ?? false)) {
    header('Location: ../../login.php');
    exit;
}

require_once '../../db.php';

// Obtener resumen de clientes y pedidos
$stmt = $pdo->query("
    SELECT 
        COUNT(DISTINCT CASE WHEN es_admin = 0 THEN id END) AS clientes,
        COUNT(*) AS pedidos_totales,
        COALESCE(SUM(CASE WHEN es_admin = 0 THEN 1 ELSE 0 END), 0) AS pedidos_clientes,
        COALESCE(SUM(total), 0) AS ingresos
    FROM (
        SELECT u.id, u.es_admin, p.total
        FROM usuarios u
        LEFT JOIN pedidos p ON 1=1  -- dummy join para demo
    ) AS dummy
");
$resumen = $stmt->fetch();

// Listar pedidos (simulado)
$stmt = $pdo->query("
    SELECT 
        'Cliente Ejemplo' AS cliente,
        s.nombre AS servicio,
        p.total,
        p.metodo,
        p.creado
    FROM pedidos p
    JOIN servicios s ON p.servicio_id = s.id
    LIMIT 10
");
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - eComerce</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header>
        <h1>👑 Panel de Administración</h1>
        <p>Hola, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>
    </header>

    <nav>
        <a href="../cliente.php">Mi cuenta</a> |
        <a href="../../logout.php">Salir</a>
    </nav>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0;">
        <div style="background:white; padding:1rem; border-radius:8px; text-align:center; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
            <h3>👥 Clientes</h3>
            <p style="font-size:1.5rem; margin:0.5rem 0;"><?= $resumen['clientes'] ?></p>
        </div>
        <div style="background:white; padding:1rem; border-radius:8px; text-align:center; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
            <h3>📦 Pedidos</h3>
            <p style="font-size:1.5rem; margin:0.5rem 0;"><?= $resumen['pedidos_clientes'] ?></p>
        </div>
        <div style="background:white; padding:1rem; border-radius:8px; text-align:center; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
            <h3>💶 Ingresos</h3>
            <p style="font-size:1.5rem; margin:0.5rem 0;">€<?= number_format($resumen['ingresos'], 2, ',', '.') ?></p>
        </div>
    </div>

    <h2>Últimos Pedidos</h2>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Total</th>
                <th>Método</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['cliente']) ?></td>
                    <td><?= htmlspecialchars($p['servicio']) ?></td>
                    <td>€<?= number_format($p['total'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['metodo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['creado'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <footer>
        <p><a href="../../index.php">← Volver a inicio</a></p>
    </footer>
</body>
</html>
