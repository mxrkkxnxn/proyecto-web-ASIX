<?php
// ⚡️ PRIMERA LÍNEA DEL ARCHIVO: iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar mensaje
$mensaje = '';
if (isset($_GET['compra']) && $_GET['compra'] === 'ok') {
    $pedido_id = htmlspecialchars($_GET['pedido'] ?? 'N/A');
    $mensaje = "✅ ¡Compra realizada! Pedido #{$pedido_id}";
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Usuario';

require_once 'db.php';

// Crear tablas si no existen (idempotente)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS servicios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT NOT NULL,
            precio DECIMAL(10,2) NOT NULL CHECK (precio > 0)
        )
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            servicio_id INT NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            metodo VARCHAR(20) NOT NULL,
            estado VARCHAR(20) DEFAULT 'completado',
            creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = $pdo->query("SELECT COUNT(*) FROM servicios")->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO servicios (nombre, descripcion, precio) VALUES (?, ?, ?)");
        $servicios = [
            ['Diseño Web', 'Página web responsive con CMS', 499.99],
            ['SEO Básico', 'Optimización para motores de búsqueda', 199.50],
            ['Mantenimiento Mensual', 'Soporte técnico y actualizaciones', 79.90]
        ];
        foreach ($servicios as $s) {
            $stmt->execute($s);
        }
    }
} catch (Exception $e) {
    die("<h2>❌ Error al preparar la base de datos</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
}

$servicios = $pdo->query("SELECT * FROM servicios ORDER BY precio ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eComerce - Servicios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🛍️ eComerce</h1>
        <p>Tus servicios profesionales, al instante</p>
        
        <!-- Menú de login/logout -->
        <nav>
            <?php if ($is_logged_in): ?>
                <a href="dashboard/cliente.php">Mi cuenta</a> |
                <a href="logout.php">Salir (<?= htmlspecialchars($user_name) ?>)</a>
            <?php else: ?>
                <a href="login.php">🔐 Iniciar sesión</a>
            <?php endif; ?>
        </nav>
    </header>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje">
            <?= $mensaje ?>
            <br><small>En una versión real, aquí iría un enlace a la factura PDF.</small>
        </div>
    <?php endif; ?>

    <main class="servicios-grid">
        <?php if (empty($servicios)): ?>
            <p>No hay servicios disponibles.</p>
        <?php else: ?>
            <?php foreach ($servicios as $s): ?>
                <div class="servicio">
                    <h2><?= htmlspecialchars($s['nombre']) ?></h2>
                    <p><?= htmlspecialchars($s['descripcion']) ?></p>
                    <p class="precio">€<?= number_format($s['precio'], 2, ',', '.') ?></p>
                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="servicio_id" value="<?= $s['id'] ?>">
                        <button type="submit">Comprar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>eComerce © 2025 — Marc Piñero Macarro</p>
    </footer>
</body>
</html>
