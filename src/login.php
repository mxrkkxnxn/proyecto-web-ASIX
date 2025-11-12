<?php
session_start();

// Función de escape para XSS
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Protección CSRF
function csrf_field() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_verify() {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF inválido.");
    }
}

$error = '';

if ($_POST) {
    csrf_verify();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        try {
            // Conexión directa
            $pdo = new PDO(
                "mysql:host=db;dbname=ecomerce;charset=utf8mb4",
                "admin", "Admin123!",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // ✅ VALIDACIÓN REAL CON password_verify()
            $stmt = $pdo->prepare("SELECT id, nombre, es_admin, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // ✅ Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['es_admin'] = $user['es_admin'];
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_blocked_until'] = 0;
                
                // ✅ MOVIDO: session_regenerate_id() DESPUÉS de asignar variables
                session_regenerate_id(true);

                $redirect = $user['es_admin'] ? 'dashboard/admin/clientes.php' : 'dashboard/cliente.php';
                header("Location: $redirect");
                exit;
            } else {
                // ❌ Login fallido
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $intentos = $_SESSION['login_attempts'];
                $error = "❌ Credenciales incorrectas. Intento $intentos/3.";
                if ($intentos >= 3) {
                    $error .= " Bloqueado temporalmente.";
                }
            }
        } catch (Exception $e) {
            $error = "Error de conexión: " . e($e->getMessage());
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}

$csrf_field = csrf_field();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - eComerce</title>
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
            max-width: 450px;
            margin: 60px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: white;
            padding: 30px 30px 20px;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-weight: 600;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
        }
        input:focus {
            border-color: #457b9d;
            outline: none;
        }
        button {
            background: #1d3557;
            color: white;
            border: none;
            width: 100%;
            padding: 14px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #457b9d;
        }
        .demo-credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
        }
        .demo-credentials code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .register-link a {
            color: #1d3557;
            font-weight: 500;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔐 Iniciar Sesión</h1>
        </header>
        
        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= $csrf_field ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Entrar</button>
            </form>

            <div class="demo-credentials">
                <strong>Para demostración:</strong><br>
                Cliente: <code>cliente@example.com</code> / <code>password</code><br>
                Admin: <code>admin@example.com</code> / <code>password</code>
            </div>

            <div class="register-link">
                ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
            </div>
        </div>
    </div>
</body>
</html>