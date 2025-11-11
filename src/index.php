<?php
session_start();
require_once 'includes/security.php';
require_once 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Usuario';

// ✅ SQL seguro
$servicios = $pdo->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY precio ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>eComerce - Servicios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🛍️ eComerce</h1>
        <p><?= e('Tus servicios profesionales, al instante') ?></p>
        
        <nav>
            <?php if ($is_logged_in): ?>
                <a href="dashboard/cliente.php"><?= e('Mi cuenta') ?></a> |
                <a href="logout.php"><?= e('Salir') ?> (<?= e($user_name) ?>)</a>
            <?php else: ?>
                <a href="login.php"><?= e('🔐 Iniciar sesión') ?></a>
            <?php endif; ?>
        </nav>
    </header>

    <?php if (isset($_GET['compra']) && $_GET['compra'] === 'ok'): ?>
        <div class="mensaje">
            ✅ ¡<?= e('Compra realizada') ?>! <?= e('Pedido') ?> #<?= e($_GET['pedido']) ?>
        </div>
    <?php endif; ?>

    <main class="servicios-grid">
        <?php foreach ($servicios as $s): ?>
            <div class="servicio">
                <h2><?= e($s['nombre']) ?></h2>
                <p><?= e($s['descripcion']) ?></p>
                <p class="precio">€<?= number_format($s['precio'], 2, ',', '.') ?></p>
                <form action="checkout.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="servicio_id" value="<?= e($s['id']) ?>">
                    <button type="submit"><?= e('Comprar') ?></button>
                </form>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>
