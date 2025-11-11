<?php
session_start();

$pdo = new PDO(
    "mysql:host=db;dbname=ecomerce;charset=utf8mb4",
    "admin", "Admin123!",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, total, metodo, creado, id_transaccion
    FROM pedidos
    WHERE id_cliente = 1
    ORDER BY creado DESC
");
$stmt->execute();
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - eComerce</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: white;
            padding: 25px 30px;
        }
        h1 {
            margin: 0;
            font-weight: 600;
            font-size: 28px;
        }
        .user-info {
            opacity: 0.9;
            font-size: 16px;
            margin-top: 8px;
        }
        nav {
            background: #2a3b4d;
            padding: 15px 30px;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 25px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        nav a:hover {
            color: #a8dadc;
        }
        nav a:before {
            margin-right: 8px;
            font-size: 18px;
        }
        nav a[href*="index.php"]:before { content: "←"; }
        nav a[href*="logout.php"]:before { content: "🚪"; }

        .content {
            padding: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
        }
        .alert:before {
            content: "✅";
            font-size: 20px;
            margin-right: 10px;
        }
        h2 {
            color: #1d3557;
            border-bottom: 2px solid #a8dadc;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .orders-table th {
            background: #457b9d;
            color: white;
            text-align: left;
            padding: 14px 20px;
            font-weight: 600;
        }
        .orders-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .orders-table tr:hover {
            background: #f8f9fa;
        }
        .order-id {
            font-weight: 600;
            color: #1d3557;
        }
        .price {
            color: #e63946;
            font-weight: 700;
        }
        .method {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .method-paypal { background: #003087; color: white; }
        .method-redsys { background: #e63946; color: white; }
        .invoice-btn {
            background: #1d3557;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }
        .invoice-btn:hover {
            background: #457b9d;
            transform: scale(1.1);
        }
        @media (max-width: 768px) {
            .orders-table {
                font-size: 14px;
            }
            .orders-table th,
            .orders-table td {
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📦 Mi Cuenta</h1>
            <div class="user-info">
                Bienvenido, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </header>

        <nav>
            <a href="../index.php">Seguir comprando</a>
            <a href="../logout.php">Salir</a>
        </nav>

        <div class="content">
            <?php if (isset($_GET['compra'])): ?>
                <div class="alert">
                    ¡Compra realizada con éxito! Pedido #<?= htmlspecialchars($_GET['pedido'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <h2>Historial de Pedidos</h2>

            <?php if (empty($pedidos)): ?>
                <p style="text-align:center; padding:40px; color:#6c757d;">
                    <span style="font-size:48px;">📭</span><br>
                    Aún no tienes pedidos.
                </p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Total</th>
                            <th>Método</th>
                            <th>Fecha</th>
                            <th>Factura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td class="order-id">#<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="price">€<?= number_format($p['total'], 2, ',', '.') ?></td>
                            <td>
                                <span class="method method-<?= strtolower($p['metodo']) ?>">
                                    <?= htmlspecialchars($p['metodo'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($p['creado'])) ?></td>
                            <td>
                                <?php if ($p['id_transaccion']): ?>
                                    <a href="../factura.php?file=<?= urlencode($p['id_transaccion']) ?>" 
                                       class="invoice-btn" title="Descargar factura">
                                        📄
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>