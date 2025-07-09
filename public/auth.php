<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function requireLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['tipoUsuario'] !== $role) {
        header("Location: no_autorizado.php");
        exit;
    }
}

function requireRoles(array $roles) {
    requireLogin();
    if (!in_array($_SESSION['tipoUsuario'], $roles)) {
        header("Location: no_autorizado.php");
        exit;
    }
}


function getUserInfo() {
    if (isLoggedIn()) {
        return [
        'codusuario' => $_SESSION['usuario_id'] ?? null,
        'tipousuario' => $_SESSION['tipoUsuario'] ?? null,
        'categoriacliente' => $_SESSION['categoriaCliente'] ?? 'inicial'
    ];
}
    return null;   
    
}
$usuario = getUserInfo();
$tipoUsuario = $_SESSION['tipoUsuario'] ?? null;
