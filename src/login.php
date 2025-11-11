<?php
// ✅ PRIMERA LÍNEA: iniciar sesión
session_start();

// Inicializar intentos solo una vez
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['login_blocked_until'])) {
    $_SESSION['login_blocked_until'] = 0;
}

$error = '';
$now = time();

// Verificar bloqueo
if ($_SESSION['login_blocked_until'] > $now) {
    $remaining = $_SESSION['login_blocked_until'] - $now;
    $error = "🔒 Demasiados intentos. Espera " . ceil($remaining / 60) . " minuto(s).";
}

// Procesar login SOLO si no está bloqueado
if ($_POST && !$error) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // ✅ Validar credenciales (usa las tuyas)
    $valido = false;
    if ($email === 'cliente@example.com' && $password === 'password') {
        $valido = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Cliente';
        $_SESSION['es_admin'] = false;
    } elseif ($email === 'admin@example.com' && $password === 'password') {
        $valido = true;
        $_SESSION['user_id'] = 2;
        $_SESSION['user_name'] = 'Admin';
        $_SESSION['es_admin'] = true;
    }

    if ($valido) {
        // ✅ Login exitoso: resetear intentos
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_blocked_until'] = 0;
        header('Location: dashboard/cliente.php');
        exit;
    } else {
        // ❌ Login fallido
        $_SESSION['login_attempts']++;
        $intentos = $_SESSION['login_attempts'];

        if ($intentos >= 3) {
            // Bloquear 5 minutos
            $_SESSION['login_blocked_until'] = $now + 300;
            $error = "❌ Demasiados intentos ($intentos/3). Bloqueado 5 minutos.";
        } else {
            $error = "❌ Credenciales incorrectas. Intento $intentos/3.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - eComerce</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 2rem auto; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; }
        input, button { width: 100%; padding: 0.5rem; margin: 0.5rem 0; }
        button { background: #1d3557; color: white; border: none; }
    </style>
</head>
<body>
    <h1>🔐 Iniciar Sesión</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ✅ Mostrar intentos actuales -->
    <p><small>Intentos: <?= $_SESSION['login_attempts'] ?>/3</small></p>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>

    <p><small>
        Prueba: <code>cliente@example.com</code> / <code>password</code>
    </small></p>
</body>
</html>
