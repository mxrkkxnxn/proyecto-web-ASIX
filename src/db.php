<?php
// ~/proyectos/webapp/src/db.php
$host = 'db';
$user = 'admin';          // ← tú lo pusiste así en compose
$pass = 'Admin123!';      // ← contraseña con mayúscula, número y !
$db   = 'ecomerce';       // ← nombre exacto (con 'r', no 'c')

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("<h2>Error de conexión</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
}
