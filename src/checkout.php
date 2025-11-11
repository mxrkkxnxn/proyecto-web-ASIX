<?php
session_start();
require_once 'includes/security.php';
require_once 'db.php';

csrf_verify(); // ✅ CSRF al procesar POST

$servicio_id = filter_input(INPUT_POST, 'servicio_id', FILTER_VALIDATE_INT);
if (!$servicio_id) {
    die(e("Servicio no especificado."));
}

$stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$servicio = $stmt->fetch();
if (!$servicio) die(e("Servicio no encontrado."));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e('Pagar') ?> - <?= e($servicio['nombre']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>💳 <?= e('Simulación de Pago') ?></h1>
        <h2><?= e($servicio['nombre']) ?> — €<?= number_format($servicio['precio'], 2) ?></h2>

        <form action="success.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="servicio_id" value="<?= e($servicio_id) ?>">
            <input type="hidden" name="metodo" value="paypal">
            <button type="submit" class="btn-paypal"><?= e('Pagar con PayPal') ?></button>
        </form>

        <form action="success.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="servicio_id" value="<?= e($servicio_id) ?>">
            <input type="hidden" name="metodo" value="redsys">
            <button type="submit" class="btn-redsys"><?= e('Pagar con tarjeta') ?></button>
        </form>
    </div>
</body>
</html>
