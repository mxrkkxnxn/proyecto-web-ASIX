<?php
// src/includes/security.php

// Protección XSS
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Generar y verificar token CSRF
function csrf_field() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_verify() {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die("<h2>⛔ Acceso denegado</h2><p>Token CSRF inválido.</p>");
    }
}

// Validación segura de entrada
function filter_input_str($key, $default = '') {
    $value = $_POST[$key] ?? '';
    return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8') ?: $default;
}
