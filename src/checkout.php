<?php
require_once 'db.php';

$servicio_id = $_POST['servicio_id'] ?? null;
if (!$servicio_id) {
    die("Servicio no especificado.");
}

$stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch();
if (!$servicio) die("Servicio no encontrado.");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagar - <?= htmlspecialchars($servicio['nombre']) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; }
        .opcion { border: 1px solid #ccc; padding: 1rem; margin: 1rem 0; }
        button { background: #457b9d; color: white; border: none; padding: 0.5rem 1rem; }
    </style>
</head>
<body>
    <h1>💳 Pago para: <?= htmlspecialchars($servicio['nombre']) ?></h1>
    <p><b>Precio:</b> €<?= number_format($servicio['precio'], 2) ?></p>

    <div class="opcion">
        <h3>PayPal (Sandbox)</h3>
        <p>Simulación segura. No se envían datos reales.</p>
        <form action="success.php" method="POST">
            <input type="hidden" name="servicio_id" value="<?= $servicio['id'] ?>">
            <input type="hidden" name="metodo" value="paypal">
            <button type="submit">Pagar con PayPal</button>
        </form>
    </div>

    <div class="opcion">
        <h3>Tarjeta (Redsys simulado)</h3>
        <p>Número ficticio: <code>4548 8120 4940 0004</code></p>
        <form action="success.php" method="POST">
            <input type="hidden" name="servicio_id" value="<?= $servicio['id'] ?>">
            <input type="hidden" name="metodo" value="redsys">
            <button type="submit">Pagar con tarjeta</button>
        </form>
        <p style="font-size:0.8em; color:#666;">No se almacenan datos de tarjeta.</p>
    </div>
</body>
</html>
