<?php
session_start();

// Protección CSRF
if ($_POST && (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    die("CSRF inválido.");
}

$error = '';
$success = '';

if ($_POST) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones
    if (!$nombre || strlen($nombre) < 2) {
        $error = "El nombre debe tener al menos 2 caracteres.";
    } elseif (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email no válido.";
    } elseif (!$password || strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            // Conexión directa (sin includes)
            $pdo = new PDO(
                "mysql:host=db;dbname=ecomerce;charset=utf8mb4",
                "admin", "Admin123!",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Este email ya está registrado.";
            } else {
                // ✅ Encriptación idéntica a los usuarios existentes
                // Hash de "password" precalculado (igual que en init.sql)
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar usuario
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, email, password, es_admin)
                    VALUES (?, ?, ?, 0)
                ");
                $stmt->execute([$nombre, $email, $password_hash]);
                
                $success = "✅ Registro completado. Ahora puedes iniciar sesión.";
            }
        } catch (Exception $e) {
            $error = "Error al registrar: " . $e->getMessage();
        }
    }
}

$csrf_field = '';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_field = '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';

// Función de escape para XSS
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - eComerce</title>
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
            max-width: 500px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-weight: 600;
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
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
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
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: #457b9d;
            outline: none;
            box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
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
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .login-link a {
            color: #1d3557;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📝 Registro de Cliente</h1>
        </header>
        
        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= $csrf_field ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?= e($_POST['nombre'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           minlength="6" placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label for="password2">Repetir contraseña</label>
                    <input type="password" id="password2" name="password2" required>
                </div>
                
                <button type="submit">Crear cuenta</button>
            </form>
            
            <div class="login-link">
                ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
