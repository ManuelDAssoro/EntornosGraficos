<?php
require '../config/db.php';

// Email confirmation system is not implemented yet
// Required database columns don't exist
echo "<div class='alert alert-info'>El sistema de confirmación por email no está implementado actualmente.</div>";
echo "<div class='alert alert-success'>Las cuentas se activan automáticamente al registrarse.</div>";

/*
// Original confirmation code (disabled until proper database structure is implemented)
$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE token_verificacion = ? AND verificado = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $update = $conn->prepare("UPDATE usuarios SET verificado = 1, token_verificacion = NULL WHERE token_verificacion = ?");
        $update->bind_param("s", $token);
        $update->execute();

        echo "Cuenta confirmada correctamente.";
    } else {
        echo "Token inválido o ya confirmado.";
    }
} else {
    echo "Falta el token.";
}
*/
?>
<a href='index.php' class='btn btn-primary mt-3'>Ir al inicio</a>