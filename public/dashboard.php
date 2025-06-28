<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
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
