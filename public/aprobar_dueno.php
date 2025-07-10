<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';
require_once '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'aprobado' WHERE codUsuario = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("SELECT nombreUsuario FROM usuarios WHERE codUsuario = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if ($usuario && $usuario['tipousuario'] === 'dueno') {
        $emailDestino = $usuario['nombreusuario'];

        // Enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.tu-servidor.com';     // Cambiar
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tu@correo.com';            // Cambiar
            $mail->Password   = 'tu_contraseña';            // Cambiar
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('noreply@tusitio.com', 'Mi Shopping');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Tu cuenta fue aprobada';
            $mail->Body    = '
                <h2>¡Bienvenido!</h2>
                <p>Tu cuenta de dueño ha sido <strong>aprobada</strong>. Ya puedes iniciar sesión y gestionar tu local.</p>
            ';

            $mail->send();
        } catch (Exception $e) {
        }
    }

    header("Location: admin_duenos.php?mensaje=aprobado");
    exit;
}
