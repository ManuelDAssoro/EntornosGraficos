<?php
require_once '../config/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

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

if ($tipoUsuario !== 'cliente' && $tipoUsuario !== 'dueno' && $tipoUsuario !== 'dueño de local') {
    $errores[] = "Debes seleccionar un tipo de usuario válido.";
}

if ($tipoUsuario === 'dueño de local') {
    $tipoUsuario = 'dueno';
}

$exito = false;
$mensajeExito = '';

if (empty($errores)) {
    $stmt = $pdo->prepare("SELECT codusuario FROM usuarios WHERE nombreusuario = ?");
    $stmt->execute([$nombreUsuario]);
    if ($stmt->fetch()) {
        $errores[] = "El email ya está registrado.";
    }
}

if (empty($errores)) {
    try {
        $claveHash = password_hash($claveUsuario, PASSWORD_DEFAULT);
        $categoriaCliente = ($tipoUsuario === 'cliente') ? 'inicial' : null;
        
        // GENERAR TOKEN ÚNICO
        $token = bin2hex(random_bytes(32));
        
        // ESTADO PENDIENTE PARA TODOS (requiere confirmación por email)
        $estado = 'pendiente';


        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nombreusuario, claveusuario, tipousuario, categoriacliente, estado, token)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $resultado = $stmt->execute([
            $nombreUsuario,
            $claveHash,
            $tipoUsuario,
            $categoriaCliente,
            $estado,
            $token
        ]);

        if ($resultado) {
           //EMAIL DE CONFIRMACIÓN
            $emailEnviado = enviarEmailConfirmacion($nombreUsuario, $token, $tipoUsuario);
            
            $exito = true;
            if ($emailEnviado) {
                $mensajeExito = "¡Registro exitoso! Revisa tu email para confirmar tu cuenta.";
            } else {
                $mensajeExito = "¡Registro exitoso! Sin embargo, hubo un problema al enviar el email de confirmación. Contacta al administrador.";
            }
        } else {
            $errores[] = "Ocurrió un error al registrar el usuario.";
        }
    } catch (Exception $e) {
        $errores[] = "Error en el registro: " . $e->getMessage();
    }
}

//FUNCIÓN PARA ENVIAR EMAIL
function enviarEmailConfirmacion($email, $token, $tipoUsuario) {
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
        $mail->Subject = 'Confirma tu cuenta en Mi Shopping';
        
        // URL de confirmación
        $confirmUrl = "https://" . $_SERVER['HTTP_HOST'] . "/confirmar.php?token=" . $token;
        
        $tipoTexto = $tipoUsuario === 'cliente' ? 'cliente' : 'dueño de local';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; text-align: center; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛍️ Mi Shopping</h1>
                    <h2>¡Bienvenido!</h2>
                </div>
                <div class='content'>
                    <h3>Confirma tu cuenta de $tipoTexto</h3>
                    <p>¡Hola! Gracias por registrarte en Mi Shopping.</p>
                    <p>Para completar tu registro y activar tu cuenta, necesitamos que confirmes tu dirección de email haciendo clic en el siguiente botón:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$confirmUrl' class='button'>✅ Confirmar mi cuenta</a>
                    </div>
                    
                    <p><strong>⚠️ Importante:</strong> Este enlace expirará en 24 horas por seguridad.</p>
                    
                    <p>Si no puedes hacer clic en el botón, copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px;'>$confirmUrl</p>
                    
                    <hr style='margin: 30px 0;'>
                    
                    <p><strong>¿No te registraste en Mi Shopping?</strong></p>
                    <p>Si no solicitaste esta cuenta, puedes ignorar este email de forma segura.</p>
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
        error_log("Error enviando email: " . $e->getMessage());
        return false;
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
                            <i class="bi bi-envelope-check-fill"></i>
                        </div>
                        <div class="alert alert-success border-0" role="alert">
                            <h4 class="alert-heading">¡Registro exitoso!</h4>
                            <p class="mb-0"><?= htmlspecialchars($mensajeExito) ?></p>
                        </div>
                        
                        <div class="next-steps">
                            <h5 class="mb-3">Próximos pasos:</h5>
                            <div class="step-item">
                                <i class="bi bi-1-circle-fill"></i>
                                <span>Revisa tu bandeja de entrada (y spam)</span>
                            </div>
                            <div class="step-item">
                                <i class="bi bi-2-circle-fill"></i>
                                <span>Haz clic en el enlace de confirmación</span>
                            </div>
                            <div class="step-item">
                                <i class="bi bi-3-circle-fill"></i>
                                <span>¡Tu cuenta estará lista para usar!</span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-envelope"></i> Ir al Ingreso
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-house"></i> Volver al Inicio
                            </a>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light border rounded">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                <strong>¿No recibiste el email?</strong> Revisa tu carpeta de spam o contacta al administrador.
                                El enlace de confirmación expira en 24 horas.
                            </small>
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