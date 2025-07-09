<?php
session_start();
require_once '../config/db.php';

$errores = [];


$modoDebug = true;

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
        $stmt = $pdo->prepare("SELECT codUsuario, claveUsuario, tipoUsuario, estado FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$nombreUsuario]);
        $usuario = $stmt->fetch();

    logDebug("Resultado de la query:");
    logDebug(print_r($usuario, true)); // mostrar contenido del array

 // Validar existencia del usuario
    if ($usuario && !empty($usuario['claveusuario'])) {
        logDebug("Usuario encontrado, verificando contraseña...");

        // Verificar contraseña
        if (password_verify($claveUsuario, trim($usuario['claveusuario']))) {
            logDebug("Contraseña verificada correctamente.");

            // Verificar estado del usuario
            if ($usuario['estado'] !== 'pendiente') {
                logDebug("Cuenta activa. Redireccionando según tipo de usuario...");

                $_SESSION['usuario_id'] = $usuario['codusuario'];
                $_SESSION['tipoUsuario'] = $usuario['tipousuario'];

                switch ($usuario['tipoUsuario']) {
                    case 'administrador':
                        logDebug("Redireccionando a dashboard_admin.php");
                        header("Location: dashboard_admin.php");
                        break;
                    case 'cliente':
                        logDebug("Redireccionando a dashboard_cliente.php");
                        header("Location: dashboard_cliente.php");
                        break;
                    case 'dueno':
                        logDebug("Redireccionando a dashboard_dueno.php");
                        header("Location: dashboard_dueno.php");
                        break;
                    default:
                        logDebug("Redireccionando a dashboard.php (tipo desconocido)");
                        header("Location: dashboard.php");
                        break;
                }
                exit;
            } else {
                $errores[] = "Tu cuenta aún no está activada.";
                logDebug("Cuenta pendiente de activación.");
            }
        } else {
            $errores[] = "Contraseña incorrecta.";
            logDebug("Fallo en la verificación de contraseña.");
        }
    } else {
        $errores[] = "El usuario no existe o clave vacía.";
        logDebug("No se encontró el usuario en la base de datos.");
    }
}
}

// Mostrar errores si hay alguno
if (!empty($errores)) {
    foreach ($errores as $error) {
        echo "<p style='color:red;'>ERROR: $error</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2>Iniciar sesión</h2>

    <?php foreach ($errores as $error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endforeach; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="nombreUsuario" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Contraseña</label>
        <input type="password" name="claveUsuario" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-primary">Ingresar</button>
    </form>

    <a href="register.php" class="btn btn-link mt-3">¿No tienes cuenta? Regístrate</a>
    
    <form action="index.php" method="get">
      <button type="submit" style="margin-top:20px; padding:10px 20px;">← Volver al inicio</button>
    </form>

  </div>
</body>
</html>
