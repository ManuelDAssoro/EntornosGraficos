<?php
require_once '../config/db.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo "<div class='alert alert-danger'>Token de validación no proporcionado.</div>";
    exit;
}

$stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE token = ? AND estado = 'pendiente' AND tipoUsuario = 'cliente'");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'validado', token = NULL WHERE codUsuario = ?");
    $stmt->execute([$user['codUsuario']]);
    echo "<div class='alert alert-success'>¡Cuenta validada correctamente! Ya puedes iniciar sesión.</div>";
} else {
    echo "<div class='alert alert-danger'>El enlace no es válido o la cuenta ya fue validada.</div>";
}
?>
<a href='index.php' class='btn btn-primary mt-3'>Ir al inicio</a>