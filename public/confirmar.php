<?php
require_once '../config/db.php';

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipo = 'error'; 

if (empty($token)) {
    $mensaje = "Token de confirmación inválido o faltante.";
} else {
    try {
        // Buscar usuario con este token
        $stmt = $pdo->prepare("SELECT codusuario, nombreusuario, tipousuario, estado FROM usuarios WHERE token = ? AND estado = 'pendiente'");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Determinar el estado final según el tipo de usuario
            $nuevoEstado = 'activo'; // Para clientes
            if ($usuario['tipousuario'] === 'dueno') {
                $nuevoEstado = 'pendiente_aprobacion'; // Los dueños necesitan aprobación adicional del admin
            }

            // Activar la cuenta
            $stmt = $pdo->prepare("UPDATE usuarios SET estado = ?, token = NULL WHERE codusuario = ?");
            $stmt->execute([$nuevoEstado, $usuario['codusuario']]);

            if ($usuario['tipousuario'] === 'cliente') {
                $mensaje = "¡Cuenta confirmada exitosamente! Ya puedes iniciar sesión y disfrutar de todas las promociones.";
                $tipo = 'success';
            } else {
                $mensaje = "¡Email confirmado! Tu solicitud como dueño de local será revisada por un administrador. Te notificaremos por email cuando sea aprobada.";
                $tipo = 'success';
            }
        } else {
            $mensaje = "El enlace de confirmación no es válido, ya fue usado o la cuenta ya está activada.";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al procesar la confirmación. Intenta nuevamente o contacta al administrador.";
        error_log("Error en confirmación: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cuenta - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .confirmation-card {
            max-width: 600px;
            margin: 50px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .confirmation-header {
            padding: 30px;
            text-align: center;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        .error-header {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
        }
        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .confirmation-body {
            padding: 40px;
            text-align: center;
        }
        .action-buttons {
            margin-top: 30px;
        }
        .action-buttons .btn {
            margin: 0 10px;
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
        <div class="confirmation-card">
            <div class="confirmation-header <?= $tipo === 'success' ? 'success-header' : 'error-header' ?>">
                <div class="confirmation-icon">
                    <?php if ($tipo === 'success'): ?>
                        <i class="bi bi-check-circle-fill"></i>
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill"></i>
                    <?php endif; ?>
                </div>
                <h1 class="mb-0">
                    <?= $tipo === 'success' ? '¡Confirmación Exitosa!' : 'Error de Confirmación' ?>
                </h1>
            </div>
            
            <div class="confirmation-body">
                <div class="alert alert-<?= $tipo === 'success' ? 'success' : 'danger' ?> border-0" role="alert">
                    <p class="mb-0 fs-5"><?= htmlspecialchars($mensaje) ?></p>
                </div>
                
                <?php if ($tipo === 'success'): ?>
                    <div class="mt-4">
                        <h5>¿Qué sigue ahora?</h5>
                        <?php if (isset($usuario) && $usuario['tipousuario'] === 'cliente'): ?>
                            <p class="text-muted">
                                Tu cuenta de cliente está completamente activa. Puedes iniciar sesión y comenzar a explorar todas las promociones disponibles en el shopping.
                            </p>
                        <?php else: ?>
                            <p class="text-muted">
                                Tu email ha sido confirmado correctamente. Ahora un administrador revisará tu solicitud como dueño de local y te notificaremos por email el resultado.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <h5>¿Necesitas ayuda?</h5>
                        <p class="text-muted">
                            Si crees que esto es un error, intenta registrarte nuevamente o contacta al administrador del sistema.
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <?php if ($tipo === 'success' && isset($usuario) && $usuario['tipousuario'] === 'cliente'): ?>
                        <a href="login.php" class="btn btn-success btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-person"></i> Ir a Login
                        </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-house"></i> Volver al Inicio
                    </a>
                </div>
                
                <div class="mt-4">
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i>
                        Tu seguridad es importante para nosotros. Este enlace de confirmación es de un solo uso.
                    </small>
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