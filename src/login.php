<?php
session_start();
require_once 'includes/security.php';
require_once 'db.php';

$error = '';

if ($_POST) {
    csrf_verify(); // ✅ CSRF
    
    $email = filter_input_str('email');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        // ✅ SQL Injection protegido
        $stmt = $pdo->prepare("SELECT id, nombre, es_admin FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $password === 'password') { // en prod: usar password_verify()
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['es_admin'] = $user['es_admin'];
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_blocked_until'] = 0;
            session_regenerate_id(true);

            $redirect = $user['es_admin'] ? 'dashboard/admin/clientes.php' : 'dashboard/cliente.php';
            header("Location: $redirect");
            exit;
        } else {
            // Lógica de intentos (simplificada)
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $intentos = $_SESSION['login_attempts'];
            $error = e("❌ Credenciales incorrectas. Intento $intentos/3.");
        }
    }
}

$csrf_field = csrf_field();
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
        <h1>🔐 <?= e('Iniciar Sesión') ?></h1>
    </header>

    <?php if ($error): ?>
        <div class="mensaje error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?= $csrf_field ?>
        <div>
            <label><?= e('Email') ?>:</label><br>
            <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        </div>
        <div>
            <label><?= e('Contraseña') ?>:</label><br>
            <input type="password" name="password" required>
        </div>
        <button type="submit"><?= e('Entrar') ?></button>
    </form>
</body>
</html>
