<?php
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['es_admin'] ? 'dashboard/admin/clientes.php' : 'dashboard/cliente.php';
    header("Location: $redirect");
    exit;
}

require_once 'db.php';

$error = '';

if ($_POST) {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // En una app real, usarías email único y hash, pero para simplicidad:
    // Creamos usuarios de ejemplo si no existen
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                es_admin BOOLEAN DEFAULT 0
            )
        ");

        $count = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($count == 0) {
            // Contraseña: "password" (hash bcrypt)
            $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            $pdo->prepare("
                INSERT INTO usuarios (nombre, email, password, es_admin) VALUES 
                ('Cliente', 'cliente@example.com', ?, 0),
                ('Admin', 'admin@example.com', ?, 1)
            ")->execute([$hash, $hash]);
        }
    } catch (Exception $e) {
        die("Error al preparar usuarios: " . $e->getMessage());
    }

    // Validar credenciales
    $stmt = $pdo->prepare("SELECT id, nombre, es_admin FROM usuarios WHERE email = ? AND password = ?");
    $stmt->execute([$email, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi']); // solo "password" funciona

    $user = $stmt->fetch();
    if ($user && $password === 'password') { // solo para demo
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['es_admin'] = $user['es_admin'];
        session_regenerate_id(true);

        $redirect = $user['es_admin'] ? 'dashboard/admin/clientes.php' : 'dashboard/cliente.php';
        header("Location: $redirect");
        exit;
    } else {
        $error = "Email o contraseña incorrectos. Usa: cliente@example.com / password";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - eComerce</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🔐 Iniciar Sesión</h1>
    </header>

    <?php if ($error): ?>
        <div class="mensaje error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="max-width: 400px; margin: 0 auto;">
        <div style="margin-bottom: 1rem;">
            <label>Email:</label><br>
            <input type="email" name="email" value="cliente@example.com" required style="width:100%; padding:0.5rem;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label>Contraseña:</label><br>
            <input type="password" name="password" value="password" required style="width:100%; padding:0.5rem;">
        </div>
        <button type="submit">Entrar</button>
    </form>

    <p style="text-align:center; margin-top:1rem;">
        <small>
            Prueba con:<br>
            Cliente: <code>cliente@example.com</code> / <code>password</code><br>
            Admin: <code>admin@example.com</code> / <code>password</code>
        </small>
    </p>

    <footer>
        <p><a href="index.php">← Volver a servicios</a></p>
    </footer>
</body>
</html>
