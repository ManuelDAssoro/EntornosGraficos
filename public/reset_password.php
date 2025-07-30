<?php
require_once '../config/db.php';

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipo = '';
$errores = [];
$tokenValido = false;
$usuario = null;

// Verificar si el token es válido
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("
            SELECT codusuario, nombreusuario, tipousuario 
            FROM usuarios 
            WHERE reset_token = ? 
            AND reset_token_expiry > NOW() 
            AND estado IN ('activo', 'aprobado')
        ");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            $tokenValido = true;
        } else {
            $mensaje = "El enlace de recuperación no es válido, ya expiró o ya fue utilizado.";
            $tipo = 'error';
        }
    } catch (Exception $e) {
        $mensaje = "Error al verificar el token. Intenta nuevamente.";
        $tipo = 'error';
        error_log("Error verificando reset token: " . $e->getMessage());
    }
} else {
    $mensaje = "Token de recuperación faltante.";
    $tipo = 'error';
}

// Procesar el cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && $tokenValido) {
    $nuevaPassword = trim($_POST['nueva_password'] ?? '');
    $confirmarPassword = trim($_POST['confirmar_password'] ?? '');
    
    // Validaciones
    if (empty($nuevaPassword) || strlen($nuevaPassword) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    }
    
    if ($nuevaPassword !== $confirmarPassword) {
        $errores[] = "Las contraseñas no coinciden.";
    }
    
    if (empty($errores)) {
        try {
            $hashPassword = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña y limpiar el token
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET claveusuario = ?, reset_token = NULL, reset_token_expiry = NULL 
                WHERE reset_token = ?
            ");
            $stmt->execute([$hashPassword, $token]);
            
            $mensaje = "¡Contraseña actualizada exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.";
            $tipo = 'success';
            $tokenValido = false; // Evitar que se muestre el formulario nuevamente
            
        } catch (Exception $e) {
            $mensaje = "Error al actualizar la contraseña. Intenta nuevamente.";
            $tipo = 'error';
            error_log("Error actualizando contraseña: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .reset-password-card {
            max-width: 500px;
            margin: 50px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        .card-header-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        .card-header-error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
        }
        .card-header {
            text-align: center;
            padding: 30px;
        }
        .card-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .password-strength {
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Mi Shopping
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="reset-password-card">
            <div class="card-header <?= $tipo === 'success' ? 'card-header-success' : ($tokenValido ? 'card-header-primary' : 'card-header-error') ?>">
                <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : ($tokenValido ? 'shield-lock' : 'exclamation-triangle') ?>"></i>
                <h2 class="mb-0">
                    <?= $tipo === 'success' ? 'Contraseña Actualizada' : ($tokenValido ? 'Nueva Contraseña' : 'Error') ?>
                </h2>
                <p class="mb-0 mt-2">
                    <?= $tipo === 'success' ? 'Tu contraseña ha sido cambiada' : ($tokenValido ? 'Crea una contraseña segura' : 'Enlace no válido') ?>
                </p>
            </div>
            
            <div class="card-body p-4">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo === 'success' ? 'success' : 'danger' ?> mb-4">
                        <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                        <?= $mensaje ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errores)): ?>
                    <?php foreach ($errores as $error): ?>
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($tokenValido): ?>
                    <div class="mb-3">
                        <p class="text-muted">
                            <i class="bi bi-person"></i> 
                            Restableciendo contraseña para: <strong><?= htmlspecialchars($usuario['nombreusuario']) ?></strong>
                        </p>
                    </div>

                    <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label for="nueva_password" class="form-label">
                                <i class="bi bi-lock"></i> Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="nueva_password" 
                                       name="nueva_password" 
                                       placeholder="Mínimo 8 caracteres"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength text-muted">
                                Usa una combinación de letras, números y símbolos
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_password" class="form-label">
                                <i class="bi bi-lock-fill"></i> Confirmar Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="confirmar_password" 
                                       name="confirmar_password" 
                                       placeholder="Repite la contraseña"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center">
                        <p class="mb-3">¿Necesitas ayuda?</p>
                        <a href="forgot_password.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Solicitar Nuevo Enlace
                        </a>
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($tipo === 'success'): ?>
                    <hr class="my-4">
                    <div class="text-center">
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword1').addEventListener('click', function () {
            const passwordField = document.getElementById('nueva_password');
            const icon = this.querySelector('i');
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });

        document.getElementById('togglePassword2').addEventListener('click', function () {
            const passwordField = document.getElementById('confirmar_password');
            const icon = this.querySelector('i');
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });

        document.getElementById('confirmar_password').addEventListener('input', function() {
            const password = document.getElementById('nueva_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirmPassword) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    </script>
</body>
</html>
