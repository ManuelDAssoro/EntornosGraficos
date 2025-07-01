<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requiere estar logueado
function requireLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Requiere rol específico
function requireRole($role) {
    requireLogin();
    if ($_SESSION['tipoUsuario'] !== $role) {
        header("Location: no_autorizado.php");
        exit;
    }
}

// Requiere varios roles
function requireRoles(array $roles) {
    requireLogin();
    if (!in_array($_SESSION['tipoUsuario'], $roles)) {
        header("Location: no_autorizado.php");
        exit;
    }
}
