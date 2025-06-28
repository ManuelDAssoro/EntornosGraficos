<?php
require_once '../config/db.php';

require 'vendor/autoload.php'; // PHPMailer con Composer para envio de correos

$nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
$claveUsuario = trim($_POST['claveUsuario'] ?? '');
$tipoUsuario = trim($_POST['tipoUsuario'] ?? '');

// Validación del lado del servidor
$errores = [];
if (empty($nombreUsuario) || !filter_var($nombreUsuario, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El email es obligatorio y debe ser válido.";
}
if (empty($claveUsuario) || strlen($claveUsuario) < 8) {
    $errores[] = "La contraseña debe tener al menos 8 caracteres.";
}
if ($tipoUsuario !== 'cliente' && $tipoUsuario !== 'dueño de local') {
    $errores[] = "Debes seleccionar un tipo de usuario válido.";
}

if (empty($errores)) {
    $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
    $stmt->execute([$nombreUsuario]);
    if ($stmt->fetch()) {
        $errores[] = "El email ya está registrado.";
    }
}

if (empty($errores)) {
    $claveHash = password_hash($claveUsuario, PASSWORD_DEFAULT);
    $categoriaCliente = ($tipoUsuario === 'cliente') ? 'Inicial' : null;
    $estado = 'pendiente';

    $token = null;
    if ($tipoUsuario === 'cliente') {
        $token = bin2hex(random_bytes(16));
    }

    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, categoriaCliente, estado, token)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $exito = $stmt->execute([
        $nombreUsuario,
        $claveHash,
        $tipoUsuario,
        $categoriaCliente,
        $estado,
        $token
    ]);

    if ($exito) {
        if ($tipoUsuario === 'cliente') 
            // Agregar logica para enviar correo de confirmar registro
            // Usando PHPMailer con el token de validacion (agregar con Composer)
            echo "<div class='alert alert-success'>¡Registro exitoso! Revisa tu correo para validar tu cuenta.</div>";
        } else {
            echo "<div class='alert alert-success'>¡Registro exitoso! Tu cuenta será revisada por un administrador.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Ocurrió un error al registrar el usuario.</div>";
    }
} else {
    foreach ($errores as $error) {
        echo "<div class='alert alert-danger'>$error</div>";
    }
}
?>
<a href="index.php" class="btn btn-secondary mt-3">Volver al Inicio</a>