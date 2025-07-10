<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'rechazado' WHERE codUsuario = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("SELECT nombreUsuario FROM usuarios WHERE codUsuario = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if ($usuario && $usuario['tipousuario'] === 'dueno') {
        $emailDestino = $usuario['nombreusuario'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.tu-servidor.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tu@correo.com';
            $mail->Password   = 'tu_contraseña';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('noreply@tusitio.com', 'Mi Shopping');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Tu cuenta fue rechazada';
            $mail->Body    = '
                <h2>Lo sentimos</h2>
                <p>Tu cuenta fue <strong>rechazada</strong> por el administrador. Puedes comunicarte para más información.</p>
            ';

            $mail->send();
        } catch (Exception $e) {
        }
    }

    header("Location: admin_duenos.php?mensaje=rechazado");
    exit;
}
