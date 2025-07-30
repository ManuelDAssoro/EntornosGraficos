<?php
require_once '../config/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mensaje = '';
$tipo = '';
$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    // Validación del email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Ingresa un email válido.";
    }
    
    if (empty($errores)) {
        try {
            // Verificar si el email existe en la base de datos
            $stmt = $pdo->prepare("SELECT codusuario, nombreusuario, tipousuario FROM usuarios WHERE nombreusuario = ? AND estado IN ('activo', 'aprobado')");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Generar token único para reset
                $resetToken = bin2hex(random_bytes(32));
                $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expira en 1 hora
                
                // Guardar token en la base de datos
                $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expiry = ? WHERE codusuario = ?");
                $stmt->execute([$resetToken, $expiracion, $usuario['codusuario']]);
                
                // Enviar email de recuperación
                $emailEnviado = enviarEmailRecuperacion($email, $resetToken);
                
                if ($emailEnviado) {
                    $mensaje = "Se ha enviado un enlace de recuperación a tu email. Revisa tu bandeja de entrada.";
                    $tipo = 'success';
                } else {
                    $mensaje = "Hubo un problema al enviar el email. Intenta nuevamente más tarde.";
                    $tipo = 'error';
                }
            } 
        } catch (Exception $e) {
            $mensaje = "Error al procesar la solicitud. Intenta nuevamente.";
            $tipo = 'error';
            error_log("Error en forgot_password: " . $e->getMessage());
        }
    }
}

// Función para enviar email de recuperación
function enviarEmailRecuperacion($email, $token) {
    try {
        $mailConfig = include '../config/mail.php';
        
        $mail = new PHPMailer(true);
        
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = $mailConfig['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['smtp_username'];
        $mail->Password = $mailConfig['smtp_password'];
        $mail->SMTPSecure = $mailConfig['smtp_secure'];
        $mail->Port = $mailConfig['smtp_port'];
        $mail->CharSet = 'UTF-8';

        // Remitente y destinatario
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($email);

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Recuperar contraseña - Mi Shopping';
        
        // URL de recuperación
        $resetUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; text-align: center; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛍️ Mi Shopping</h1>
                    <h2>Recuperar Contraseña</h2>
                </div>
                <div class='content'>
                    <h3>Solicitud de recuperación de contraseña</h3>
                    <p>¡Hola! Recibimos una solicitud para restablecer la contraseña de tu cuenta.</p>
                    <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$resetUrl' class='button'>🔒 Restablecer Contraseña</a>
                    </div>
                    
                    <p><strong>⚠️ Importante:</strong> Este enlace expirará en 1 hora por seguridad.</p>
                    
                    <p>Si no puedes hacer clic en el botón, copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px;'>$resetUrl</p>
                    
                    <hr style='margin: 30px 0;'>
                    
                    <p><strong>¿No solicitaste este cambio?</strong></p>
                    <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este email de forma segura. Tu contraseña actual seguirá siendo válida.</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Mi Shopping. Todos los derechos reservados.</p>
                    <p>Este es un email automático, por favor no respondas a esta dirección.</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error enviando email de recuperación: " . $e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .forgot-password-card {
            max-width: 500px;
            margin: 50px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            text-align: center;
            padding: 30px;
        }
        .card-header i {
            font-size: 3rem;
            margin-bottom: 15px;
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
        <div class="forgot-password-card">
            <div class="card-header">
                <i class="bi bi-key"></i>
                <h2 class="mb-0">Recuperar Contraseña</h2>
                <p class="mb-0 mt-2">Ingresa tu email para recibir un enlace de recuperación</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo === 'success' ? 'success' : ($tipo === 'info' ? 'info' : 'danger') ?> mb-4">
                        <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : ($tipo === 'info' ? 'info-circle' : 'exclamation-triangle') ?>"></i>
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

                <?php if ($tipo !== 'success'): ?>
                <form method="POST" action="forgot_password.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="Ingresa tu email registrado"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                               required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Enviar Enlace de Recuperación
                        </button>
                    </div>
                </form>
                <?php endif; ?>

                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">¿Recordaste tu contraseña?</p>
                    <a href="login.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </a>
                    <a href="index.php" class="btn btn-outline-info">
                        <i class="bi bi-house"></i> Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
