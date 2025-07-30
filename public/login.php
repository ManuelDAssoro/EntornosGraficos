<?php
ob_start(); 
session_start();
require_once '../config/db.php';

$errores = [];


$modoDebug = false; 

function logDebug($mensaje) {
    global $modoDebug;
    if ($modoDebug) {
        echo "<pre>DEBUG: $mensaje</pre>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreUsuario = isset($_POST['nombreUsuario']) ? trim($_POST['nombreUsuario']) : '';
    $claveUsuario = isset($_POST['claveUsuario']) ? trim($_POST['claveUsuario']) : '';


// Validar entrada
if (!$nombreUsuario ) {
    $errores[] = "Faltan el nombre de usuario ";
    logDebug("Entrada no válida: nombreUsuario faltante.");
}
else {
    logDebug("Datos recibidos: nombreUsuario = '$nombreUsuario'");}
if (!$claveUsuario) {
   $errores[] = "Falta la contraseña.";
    logDebug("Entrada no válida: claveUsuario faltantes.");
} 
else {
    logDebug("Datos recibidos: claveUsuario = '$claveUsuario'");}



    if (empty($nombreUsuario) || empty($claveUsuario)) {
        $errores[] = "Debes completar todos los campos.";

    } else {
        $stmt = $pdo->prepare("SELECT codusuario, claveusuario, tipousuario, estado FROM usuarios WHERE nombreusuario = ?");
        $stmt->execute([$nombreUsuario]);
        $usuario = $stmt->fetch();

    logDebug("Resultado de la query:");
    logDebug(print_r($usuario, true)); // mostrar contenido del array

 // Validar existencia del usuario
    if ($usuario && !empty($usuario['claveusuario']) && password_verify($claveUsuario, trim($usuario['claveusuario']))) {
        // VALIDACIÓN DE CONFIRMACIÓN
        if ($usuario['estado'] === 'pendiente') {
            $errores[] = "Debes confirmar tu cuenta por email antes de poder iniciar sesión. Revisa tu bandeja de entrada.";
            logDebug("Cuenta pendiente de confirmación por email.");
        } elseif ($usuario['estado'] === 'pendiente_aprobacion') {
            $errores[] = "Tu cuenta está siendo revisada por un administrador. Te notificaremos por email cuando sea aprobada.";
            logDebug("Cuenta de dueño pendiente de aprobación por admin.");
        } elseif ($usuario['estado'] === 'rechazado') {
            $errores[] = "Tu cuenta ha sido rechazada. Contacta al administrador para más información.";
            logDebug("Cuenta rechazada.");
        } elseif (in_array($usuario['estado'], ['activo', 'aprobado'])) {
            // Cuenta válida 
            logDebug("Cuenta activa. Redireccionando según tipo de usuario...");

            $_SESSION['usuario_id'] = $usuario['codusuario'];
            $_SESSION['tipoUsuario'] = $usuario['tipousuario'];
            $_SESSION['categoriaCliente'] = $usuario['categoriacliente'] ?? 'inicial';

            switch ($usuario['tipousuario']) {
                case 'administrador':
                    header("Location: dashboard_admin.php");
                    break;
                case 'cliente':
                    header("Location: dashboard_cliente.php");
                    break;
                case 'dueno':
                    header("Location: dashboard_dueno.php");
                    break;
                default:
                    header("Location: dashboard.php");
                    break;
            }
            exit;
        } else {
            $errores[] = "Estado de cuenta no válido. Contacta al administrador.";
            logDebug("Estado de cuenta desconocido: " . $usuario['estado']);
        }
    } else {
        $errores[] = "Email o contraseña incorrectos.";
        logDebug("Fallo en la verificación de contraseña o usuario no encontrado.");
    }
}
}

// Mostrar errores si hay alguno
if (!empty($errores)) {
    foreach ($errores as $error) {
        echo "<p style='color:red;'>ERROR: $error</p>";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2>Iniciar sesión</h2>

    <?php if (!empty($errores)): ?>
      <?php foreach ($errores as $error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="nombreUsuario" class="form-control" required />
      </div>

      <div class="mb-3">
        <label>Contraseña</label>
        <div class="input-group">
          <input type="password" name="claveUsuario" id="claveUsuario" class="form-control" required />
          <button class="btn btn-outline-secondary" type="button" id="togglePassword">
            <i class="bi bi-lock"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Ingresar</button>
    </form>

    <div class="mt-3">
      <a href="forgot_password.php" class="btn btn-link">¿Olvidaste tu contraseña?</a>
    </div>

    <a href="register.php" class="btn btn-link mt-3">¿No tienes cuenta? Regístrate</a>

    <form action="index.php" method="get">
      <button type="submit" style="margin-top:20px; padding:10px 20px;">← Volver al inicio</button>
    </form>
  </div>

  <!-- JavaScript para cambiar entre candado abierto/cerrado -->
  <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordField = document.getElementById('claveUsuario');
      const icon = this.querySelector('i');
      const isPassword = passwordField.type === 'password';
      passwordField.type = isPassword ? 'text' : 'password';

      // Alternar icono entre candado cerrado y abierto
      icon.classList.toggle('bi-lock');
      icon.classList.toggle('bi-unlock');
    });
  </script>
</body>
</html>
