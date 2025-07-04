<?php
session_start();
require_once '../config/db.php';

$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreUsuario = isset($_POST['nombreUsuario']) ? trim($_POST['nombreUsuario']) : '';
    $claveUsuario = isset($_POST['claveUsuario']) ? trim($_POST['claveUsuario']) : '';

    if (empty($nombreUsuario) || empty($claveUsuario)) {
        $errores[] = "Debes completar todos los campos.";
    } else {
        $stmt = $pdo->prepare("SELECT codUsuario, claveUsuario, tipoUsuario, estado FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$nombreUsuario]);
        $usuario = $stmt->fetch();

        if ($usuario && !empty($usuario['claveUsuario']) && password_verify($claveUsuario, trim($usuario['claveUsuario']))) {
            if ($usuario['estado'] !== 'pendiente') {
                $_SESSION['usuario_id'] = $usuario['codUsuario'];
                $_SESSION['tipoUsuario'] = $usuario['tipoUsuario'];
              switch ($usuario['tipoUsuario']) {
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
                $errores[] = "Tu cuenta aún no está activada.";
            }
        } else {
            $errores[] = "Usuario o contraseña incorrectos.";
        }
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
