<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect to specific dashboard based on user type
if (isset($_SESSION['tipoUsuario'])) {
    switch ($_SESSION['tipoUsuario']) {
        case 'administrador':
            header("Location: dashboard_admin.php");
            exit;
        case 'cliente':
            header("Location: dashboard_cliente.php");
            exit;
        case 'dueno':
            header("Location: dashboard_dueno.php");
            exit;
        // If tipoUsuario doesn't match any specific type, continue to generic dashboard
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
  <?php include 'layout/navbar.php'; ?>

  <div class="container mt-5">
    <h1>Bienvenido al Panel</h1>
    <p class="lead">Hola, estás logueado como <strong><?= $_SESSION['tipoUsuario'] ?></strong>.</p>
  </div>
</body>
</html>
