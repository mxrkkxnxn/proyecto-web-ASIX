<?php
session_start();

$pdo = new PDO(
    "mysql:host=db;dbname=ecomerce;charset=utf8mb4",
    "admin", "Admin123!",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (!isset($_SESSION['user_id']) || !($_SESSION['es_admin'] ?? false)) {
    header('Location: ../../login.php');
    exit;
}

// Estadísticas
$stats = $pdo->query("
    SELECT 
        COUNT(DISTINCT CASE WHEN u.es_admin = 0 THEN u.id END) AS clientes,
        COUNT(p.id) AS pedidos,
        COALESCE(SUM(p.total), 0) AS ingresos
    FROM usuarios u
    LEFT JOIN pedidos p ON u.id = p.id_cliente
")->fetch();

// Últimos pedidos
$stmt = $pdo->prepare("
    SELECT 
        p.id, 
        COALESCE(u.nombre, 'Cliente') AS cliente,
        p.total,
        p.metodo,
        p.estado,
        p.creado
    FROM pedidos p
    LEFT JOIN usuarios u ON p.id_cliente = u.id
    ORDER BY p.creado DESC
    LIMIT 20
");
$stmt->execute();
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - eComerce</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        header {
            background: linear-gradient(135deg, #1d3557, #2a3b4d);
            color: white;
            padding: 25px 30px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        h1 {
            margin: 0;
            font-weight: 600;
            font-size: 28px;
        }
        nav {
            background: #1a2835;
            padding: 15px 30px;
            border-radius: 0 0 10px 10px;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 25px;
            font-weight: 500;
        }
        nav a:hover {
            color: #a8dadc;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
        }
        .stat-card h3 {
            color: #6c757d;
            margin: 0 0 10px 0;
            font-weight: 500;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #1d3557;
            margin: 0;
        }
        .stat-card.incomes .value {
            color: #2a9d8f;
        }
        .stat-card.orders .value {
            color: #e76f51;
        }

        .content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .content-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }
        h2 {
            margin: 0;
            color: #1d3557;
            font-size: 22px;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #457b9d;
            color: white;
            text-align: left;
            padding: 16px 20px;
            font-weight: 600;
        }
        td {
            padding: 14px 20px;
            border-bottom: 1px solid #e9ecef;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .status.completado { background: #d4edda; color: #155724; }
        .status.pendiente { background: #fff3cd; color: #856404; }
        .method {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .method-paypal { background: #003087; color: white; }
        .method-redsys { background: #e63946; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>👑 Panel de Administración</h1>
        </header>

        <nav>
            <a href="../../logout.php">Salir</a>
        </nav>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>CLIENTES</h3>
                <p class="value"><?= number_format($stats['clientes']) ?></p>
            </div>
            <div class="stat-card orders">
                <h3>PEDIDOS</h3>
                <p class="value"><?= number_format($stats['pedidos']) ?></p>
            </div>
            <div class="stat-card incomes">
                <h3>INGRESOS</h3>
                <p class="value">€<?= number_format($stats['ingresos'], 2, ',', '.') ?></p>
            </div>
        </div>

        <div class="content">
            <div class="content-header">
                <h2>Últimos Pedidos</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>€<?= number_format($p['total'], 2, ',', '.') ?></td>
                            <td>
                                <span class="method method-<?= strtolower($p['metodo']) ?>">
                                    <?= htmlspecialchars($p['metodo'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="status <?= $p['estado'] ?>">
                                    <?= ucfirst(htmlspecialchars($p['estado'], ENT_QUOTES, 'UTF-8')) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($p['creado'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>