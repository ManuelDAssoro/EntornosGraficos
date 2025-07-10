<?php
require_once '../config/db.php';

require '../vendor/autoload.php'; // PHPMailer con Composer para envio de correos
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
// Debug: Let's see what we're getting
error_log("Form submission - tipoUsuario received: '" . $tipoUsuario . "'");

if ($tipoUsuario !== 'cliente' && $tipoUsuario !== 'dueno' && $tipoUsuario !== 'dueño de local') {
    $errores[] = "Debes seleccionar un tipo de usuario válido. Recibido: '" . $tipoUsuario . "'";
}

// Normalize the tipoUsuario value
if ($tipoUsuario === 'dueño de local') {
    $tipoUsuario = 'dueno';
}

$exito = false;
$mensajeExito = '';

if (empty($errores)) {
    $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
    $stmt->execute([$nombreUsuario]);
    if ($stmt->fetch()) {
        $errores[] = "El email ya está registrado.";
    }
}

if (empty($errores)) {
    $claveHash = password_hash($claveUsuario, PASSWORD_DEFAULT);
    $categoriaCliente = ($tipoUsuario === 'cliente') ? 'inicial' : null;
    $estado = 'pendiente';

    // set estado to 'activo' since we're not implementing email verification when tipoUsuario is 'cliente'
    if ($tipoUsuario === 'cliente') {
        $estado = 'activo';
    }

    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, categoriaCliente, estado)
         VALUES (?, ?, ?, ?, ?)"
    );
    $resultado = $stmt->execute([
        $nombreUsuario,
        $claveHash,
        $tipoUsuario,
        $categoriaCliente,
        $estado
    ]);

    if ($resultado) {
        $exito = true;
        if ($tipoUsuario === 'cliente') {
            $mensajeExito = "¡Registro exitoso! Tu cuenta ha sido activada y ya puedes iniciar sesión.";
        } else {
            $mensajeExito = "¡Registro exitoso! Tu cuenta será revisada por un administrador.";
        }
    } else {
        $errores[] = "Ocurrió un error al registrar el usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Registro - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/form-validation.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Mi Shopping
            </a>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="mb-3">
                        <i class="bi bi-person-check"></i> Resultado del Registro
                    </h1>
                    <p class="lead mb-0">Estado de tu solicitud de registro</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="result-card">
                    <?php if ($exito): ?>
                        <div class="success-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="alert alert-success border-0" role="alert">
                            <h4 class="alert-heading">¡Excelente!</h4>
                            <p class="mb-0"><?= htmlspecialchars($mensajeExito) ?></p>
                        </div>
                        
                        <div class="next-steps">
                            <h5 class="mb-3">Próximos pasos:</h5>
                            <?php if ($tipoUsuario === 'cliente'): ?>
                                <div class="step-item">
                                    <i class="bi bi-1-circle-fill"></i>
                                    <span>Tu cuenta está lista para usar</span>
                                </div>
                                <div class="step-item">
                                    <i class="bi bi-2-circle-fill"></i>
                                    <span>Inicia sesión para explorar promociones</span>
                                </div>
                                <div class="step-item">
                                    <i class="bi bi-3-circle-fill"></i>
                                    <span>Disfruta de las ofertas del shopping</span>
                                </div>
                            <?php else: ?>
                                <div class="step-item">
                                    <i class="bi bi-1-circle-fill"></i>
                                    <span>Un administrador revisará tu solicitud</span>
                                </div>
                                <div class="step-item">
                                    <i class="bi bi-2-circle-fill"></i>
                                    <span>Recibirás una notificación por email</span>
                                </div>
                                <div class="step-item">
                                    <i class="bi bi-3-circle-fill"></i>
                                    <span>Podrás acceder una vez aprobado</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <?php if ($tipoUsuario === 'cliente'): ?>
                                <a href="login.php" class="btn btn-success btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </a>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-house"></i> Volver al Inicio
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="error-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="alert alert-danger border-0" role="alert">
                            <h4 class="alert-heading">Error en el Registro</h4>
                            <p class="mb-3">Se encontraron los siguientes problemas:</p>
                            <ul class="mb-0">
                                <?php foreach ($errores as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="action-buttons">
                            <a href="register.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-left"></i> Volver a Intentar
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-house"></i> Ir al Inicio
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Mi Shopping. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>